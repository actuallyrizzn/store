"""
Marketplace API client.

Supports API key authentication (recommended for server/script use) and
session authentication (login + cookies for endpoints that require session).
"""

from typing import Any, Dict, List, Optional
import requests

from .exceptions import (
    ConflictError,
    ForbiddenError,
    MarketplaceAPIError,
    NotFoundError,
    RateLimitError,
    ServerError,
    UnauthorizedError,
    ValidationError,
)

# Default base URL; override with base_url in constructor
DEFAULT_BASE_URL = "http://localhost"


class MarketplaceClient:
    """
    Client for the Marketplace REST API.

    Use API key for programmatic access (most endpoints). Use login() for
    session-only endpoints (create store, create item, create transaction,
    keys management, deposits, disputes, admin).
    """

    def __init__(
        self,
        base_url: str = DEFAULT_BASE_URL,
        api_key: Optional[str] = None,
        session: Optional[requests.Session] = None,
        timeout: float = 30.0,
    ) -> None:
        """
        Args:
            base_url: API base URL (e.g. https://market.example.com).
            api_key: Optional API key (Bearer). Use for API key–authenticated endpoints.
            session: Optional requests.Session (e.g. with cookies after login).
            timeout: Request timeout in seconds.
        """
        self._base = base_url.rstrip("/")
        self._api_key = api_key
        self._session = session or requests.Session()
        self._timeout = timeout

    def _headers(self, auth: bool = True) -> Dict[str, str]:
        h: Dict[str, str] = {}
        if auth and self._api_key:
            h["Authorization"] = f"Bearer {self._api_key}"
            h["X-API-Key"] = self._api_key
        return h

    def _request(
        self,
        method: str,
        path: str,
        *,
        params: Optional[Dict[str, Any]] = None,
        data: Optional[Dict[str, Any]] = None,
        auth: bool = True,
    ) -> Any:
        url = f"{self._base}{path}"
        headers = self._headers(auth=auth)
        if data is not None:
            # API expects form-encoded for POST
            resp = self._session.request(
                method,
                url,
                params=params,
                data=data,
                headers=headers,
                timeout=self._timeout,
            )
        else:
            resp = self._session.request(
                method,
                url,
                params=params,
                headers=headers,
                timeout=self._timeout,
            )

        # Plain text responses (health, login, register, logout)
        if "application/json" not in (resp.headers.get("Content-Type") or ""):
            if resp.ok:
                return resp.text.strip()
            raise MarketplaceAPIError(
                resp.text or f"HTTP {resp.status_code}",
                status_code=resp.status_code,
                response_body=resp.text,
            )

        try:
            body = resp.json()
        except Exception:
            body = None

        if resp.ok:
            return body

        msg = (body or {}).get("error", resp.text or f"HTTP {resp.status_code}")
        if resp.status_code == 400:
            raise ValidationError(msg, resp.status_code, body)
        if resp.status_code == 401:
            raise UnauthorizedError(msg, resp.status_code, body)
        if resp.status_code == 403:
            raise ForbiddenError(msg, resp.status_code, body)
        if resp.status_code == 404:
            raise NotFoundError(msg, resp.status_code, body)
        if resp.status_code == 409:
            raise ConflictError(msg, resp.status_code, body)
        if resp.status_code == 429:
            raise RateLimitError(msg, resp.status_code, body)
        if 500 <= resp.status_code < 600:
            raise ServerError(msg, resp.status_code, body)
        raise MarketplaceAPIError(msg, resp.status_code, body)

    # --- Health & Auth (no API key required for login/register) ---

    def health(self) -> str:
        """GET / — Health check. Returns 'OK'."""
        return self._request("GET", "/", auth=False)  # type: ignore

    def register(self, username: str, password: str) -> str:
        """POST /register.php — Register a new user. Returns plain text message."""
        return self._request(  # type: ignore
            "POST",
            "/register.php",
            data={"username": username, "password": password},
            auth=False,
        )

    def login(self, username: str, password: str) -> str:
        """
        POST /login.php — Log in; session cookie is stored in the client's session.
        Returns plain text message (e.g. 'Logged in as alice').
        """
        return self._request(  # type: ignore
            "POST",
            "/login.php",
            data={"username": username, "password": password},
            auth=False,
        )

    def logout(self) -> str:
        """GET /logout.php — Log out. Returns 'Logged out'."""
        return self._request("GET", "/logout.php", auth=False)  # type: ignore

    # --- Public API (no auth) ---

    def list_stores(self) -> Dict[str, List[Dict[str, Any]]]:
        """GET /api/stores.php — List all stores."""
        return self._request("GET", "/api/stores.php", auth=False)  # type: ignore

    def list_items(self, store_uuid: Optional[str] = None) -> Dict[str, List[Dict[str, Any]]]:
        """GET /api/items.php — List items, optionally filtered by store_uuid."""
        params = {}
        if store_uuid is not None:
            params["store_uuid"] = store_uuid
        return self._request("GET", "/api/items.php", params=params or None, auth=False)  # type: ignore

    # --- Session or API key authenticated ---

    def create_store(
        self,
        storename: str,
        description: str = "",
        vendorship_agree: bool = True,
    ) -> Dict[str, Any]:
        """POST /api/stores.php — Create a store (requires session)."""
        return self._request(  # type: ignore
            "POST",
            "/api/stores.php",
            data={
                "storename": storename,
                "description": description,
                "vendorship_agree": "1" if vendorship_agree else "0",
            },
        )

    def create_item(
        self,
        name: str,
        store_uuid: str,
        description: str = "",
    ) -> Dict[str, Any]:
        """POST /api/items.php — Create an item (requires session)."""
        return self._request(  # type: ignore
            "POST",
            "/api/items.php",
            data={
                "name": name,
                "store_uuid": store_uuid,
                "description": description,
            },
        )

    def list_transactions(self) -> Dict[str, List[Dict[str, Any]]]:
        """GET /api/transactions.php — List transactions (API key or session)."""
        return self._request("GET", "/api/transactions.php")  # type: ignore

    def create_transaction(
        self,
        package_uuid: str,
        required_amount: float,
        chain_id: int = 1,
        currency: str = "ETH",
        refund_address: Optional[str] = None,
    ) -> Dict[str, Any]:
        """POST /api/transactions.php — Create a transaction (requires session)."""
        data: Dict[str, Any] = {
            "package_uuid": package_uuid,
            "required_amount": required_amount,
            "chain_id": chain_id,
            "currency": currency,
        }
        if refund_address:
            data["refund_address"] = refund_address
        return self._request("POST", "/api/transactions.php", data=data)  # type: ignore

    def list_keys(self) -> Dict[str, List[Dict[str, Any]]]:
        """GET /api/keys.php — List API keys for current user (requires session)."""
        return self._request("GET", "/api/keys.php")  # type: ignore

    def create_key(self, name: str = "") -> Dict[str, Any]:
        """
        POST /api/keys.php — Create an API key (requires session).
        Returns dict with 'api_key' — save it; it cannot be retrieved later.
        """
        return self._request(  # type: ignore
            "POST",
            "/api/keys.php",
            data={"name": name} if name else {},
        )

    def revoke_key(self, key_id: int) -> Dict[str, Any]:
        """POST /api/keys-revoke.php — Revoke an API key (requires session)."""
        return self._request("POST", "/api/keys-revoke.php", data={"id": key_id})  # type: ignore

    def get_auth_user(self) -> Dict[str, Any]:
        """GET /api/auth-user.php — Current user for API key (requires API key)."""
        return self._request("GET", "/api/auth-user.php")  # type: ignore

    def list_deposits(self) -> Dict[str, List[Dict[str, Any]]]:
        """GET /api/deposits.php — List deposits for current user's stores (requires session)."""
        return self._request("GET", "/api/deposits.php")  # type: ignore

    def list_disputes(self) -> Dict[str, List[Dict[str, Any]]]:
        """GET /api/disputes.php — List disputes (requires session)."""
        return self._request("GET", "/api/disputes.php")  # type: ignore

    # --- Admin (session, admin role) ---

    def get_config(self) -> Dict[str, Any]:
        """GET /admin/config.php — Get system config (admin)."""
        return self._request("GET", "/admin/config.php")  # type: ignore

    def update_config(self, **kwargs: str) -> Dict[str, Any]:
        """POST /admin/config.php — Update config (admin). Pass keys as keyword args."""
        return self._request("POST", "/admin/config.php", data=kwargs)  # type: ignore

    def list_tokens(self) -> Dict[str, List[Dict[str, Any]]]:
        """GET /admin/tokens.php — List accepted tokens (admin)."""
        return self._request("GET", "/admin/tokens.php")  # type: ignore

    def add_token(
        self,
        chain_id: int,
        symbol: str,
        contract_address: Optional[str] = None,
    ) -> Dict[str, Any]:
        """POST /admin/tokens.php — Add accepted token (admin)."""
        data: Dict[str, Any] = {"chain_id": chain_id, "symbol": symbol}
        if contract_address:
            data["contract_address"] = contract_address
        return self._request("POST", "/admin/tokens.php", data=data)  # type: ignore

    def remove_token(self, token_id: int) -> Dict[str, Any]:
        """POST /admin/tokens-remove.php — Remove accepted token (admin)."""
        return self._request("POST", "/admin/tokens-remove.php", data={"id": token_id})  # type: ignore
