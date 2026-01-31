#!/usr/bin/env python3
"""
Marketplace SMCP Plugin

Provides MCP tools for the Marketplace REST API (stores, items, transactions,
API keys, deposits, disputes). Uses the marketplace SDK.

Copyright (c) 2026
"""

import argparse
import json
import os
import sys
import traceback
from pathlib import Path
from typing import Any, Dict, Optional

# Prefer installed marketplace-sdk; fallback to repo sdk
try:
    from sdk import (
        MarketplaceClient,
        MarketplaceAPIError,
        NotFoundError,
        RateLimitError,
        UnauthorizedError,
        ValidationError,
    )
except ImportError:
    repo_root = Path(__file__).resolve().parent.parent.parent
    sys.path.insert(0, str(repo_root))
    from sdk import (
        MarketplaceClient,
        MarketplaceAPIError,
        NotFoundError,
        RateLimitError,
        UnauthorizedError,
        ValidationError,
    )


def get_base_url(args: Dict[str, Any]) -> str:
    """Resolve base URL from args or env."""
    return (args.get("base_url") or os.getenv("MARKETPLACE_BASE_URL") or "http://localhost").rstrip("/")


def get_client_api_key(base_url: str, api_key: str) -> MarketplaceClient:
    """Build client with API key auth."""
    if not api_key:
        raise ValueError("API key is required for this command")
    return MarketplaceClient(base_url=base_url, api_key=api_key)


def get_client_session(base_url: str, username: str, password: str) -> MarketplaceClient:
    """Build client and log in with session."""
    if not username or not password:
        raise ValueError("Username and password are required for this command")
    client = MarketplaceClient(base_url=base_url)
    client.login(username, password)
    return client


def _err(status: str, error: str, error_type: str = "api_error", **extra: Any) -> Dict[str, Any]:
    return {"status": status, "error": error, "error_type": error_type, **extra}


def _ok(**kwargs: Any) -> Dict[str, Any]:
    return {"status": "success", **kwargs}


# --- No auth ---

def health(args: Dict[str, Any]) -> Dict[str, Any]:
    """Health check."""
    try:
        base_url = get_base_url(args)
        client = MarketplaceClient(base_url=base_url)
        msg = client.health()
        return _ok(message=msg)
    except Exception as e:
        return _err("error", str(e), "unknown_error", traceback=traceback.format_exc())


def list_stores(args: Dict[str, Any]) -> Dict[str, Any]:
    """List all stores."""
    try:
        base_url = get_base_url(args)
        client = MarketplaceClient(base_url=base_url)
        out = client.list_stores()
        return _ok(stores=out.get("stores", []))
    except Exception as e:
        return _err("error", str(e), "unknown_error", traceback=traceback.format_exc())


def list_items(args: Dict[str, Any]) -> Dict[str, Any]:
    """List items, optionally by store_uuid."""
    try:
        base_url = get_base_url(args)
        client = MarketplaceClient(base_url=base_url)
        out = client.list_items(store_uuid=args.get("store_uuid"))
        return _ok(items=out.get("items", []))
    except Exception as e:
        return _err("error", str(e), "unknown_error", traceback=traceback.format_exc())


# --- API key auth ---

def get_auth_user(args: Dict[str, Any]) -> Dict[str, Any]:
    """Get current user for API key."""
    try:
        base_url = get_base_url(args)
        client = get_client_api_key(base_url, args.get("api_key") or "")
        out = client.get_auth_user()
        return _ok(user=out)
    except UnauthorizedError as e:
        return _err("error", str(e.message), "unauthorized")
    except RateLimitError as e:
        return _err("error", str(e.message), "rate_limit")
    except Exception as e:
        return _err("error", str(e), "unknown_error", traceback=traceback.format_exc())


def list_transactions(args: Dict[str, Any]) -> Dict[str, Any]:
    """List transactions (API key or session)."""
    try:
        base_url = get_base_url(args)
        client = get_client_api_key(base_url, args.get("api_key") or "")
        out = client.list_transactions()
        return _ok(transactions=out.get("transactions", []))
    except UnauthorizedError as e:
        return _err("error", str(e.message), "unauthorized")
    except RateLimitError as e:
        return _err("error", str(e.message), "rate_limit")
    except Exception as e:
        return _err("error", str(e), "unknown_error", traceback=traceback.format_exc())


# --- Session auth ---

def create_store(args: Dict[str, Any]) -> Dict[str, Any]:
    """Create a store (session)."""
    try:
        base_url = get_base_url(args)
        client = get_client_session(
            base_url,
            args.get("username") or "",
            args.get("password") or "",
        )
        out = client.create_store(
            storename=args.get("storename") or "",
            description=args.get("description") or "",
            vendorship_agree=args.get("vendorship_agree") not in (False, "0", 0),
        )
        client.logout()
        return _ok(uuid=out.get("uuid"), message=f"Store created: {out.get('uuid')}")
    except ValidationError as e:
        return _err("error", str(e.message), "validation_error")
    except UnauthorizedError as e:
        return _err("error", str(e.message), "unauthorized")
    except Exception as e:
        return _err("error", str(e), "unknown_error", traceback=traceback.format_exc())


def create_item(args: Dict[str, Any]) -> Dict[str, Any]:
    """Create an item (session)."""
    try:
        base_url = get_base_url(args)
        client = get_client_session(
            base_url,
            args.get("username") or "",
            args.get("password") or "",
        )
        out = client.create_item(
            name=args.get("name") or "",
            store_uuid=args.get("store_uuid") or "",
            description=args.get("description") or "",
        )
        client.logout()
        return _ok(uuid=out.get("uuid"), message=f"Item created: {out.get('uuid')}")
    except ValidationError as e:
        return _err("error", str(e.message), "validation_error")
    except UnauthorizedError as e:
        return _err("error", str(e.message), "unauthorized")
    except Exception as e:
        return _err("error", str(e), "unknown_error", traceback=traceback.format_exc())


def create_transaction(args: Dict[str, Any]) -> Dict[str, Any]:
    """Create a transaction (session)."""
    try:
        base_url = get_base_url(args)
        client = get_client_session(
            base_url,
            args.get("username") or "",
            args.get("password") or "",
        )
        out = client.create_transaction(
            package_uuid=args.get("package_uuid") or "",
            required_amount=float(args.get("required_amount", 0)),
            chain_id=int(args.get("chain_id", 1)),
            currency=(args.get("currency") or "ETH").strip(),
            refund_address=args.get("refund_address") or None,
        )
        client.logout()
        return _ok(
            uuid=out.get("uuid"),
            escrow_address_pending=out.get("escrow_address_pending"),
            message=f"Transaction created: {out.get('uuid')}",
        )
    except ValidationError as e:
        return _err("error", str(e.message), "validation_error")
    except NotFoundError as e:
        return _err("error", str(e.message), "not_found")
    except UnauthorizedError as e:
        return _err("error", str(e.message), "unauthorized")
    except Exception as e:
        return _err("error", str(e), "unknown_error", traceback=traceback.format_exc())


def list_keys(args: Dict[str, Any]) -> Dict[str, Any]:
    """List API keys (session)."""
    try:
        base_url = get_base_url(args)
        client = get_client_session(
            base_url,
            args.get("username") or "",
            args.get("password") or "",
        )
        out = client.list_keys()
        client.logout()
        return _ok(keys=out.get("keys", []))
    except UnauthorizedError as e:
        return _err("error", str(e.message), "unauthorized")
    except Exception as e:
        return _err("error", str(e), "unknown_error", traceback=traceback.format_exc())


def create_key(args: Dict[str, Any]) -> Dict[str, Any]:
    """Create API key (session). Returns api_key — save it; it cannot be retrieved later."""
    try:
        base_url = get_base_url(args)
        client = get_client_session(
            base_url,
            args.get("username") or "",
            args.get("password") or "",
        )
        out = client.create_key(name=args.get("name") or "")
        client.logout()
        return _ok(
            id=out.get("id"),
            name=out.get("name"),
            key_prefix=out.get("key_prefix"),
            api_key=out.get("api_key"),
            created_at=out.get("created_at"),
            message="Save api_key; it cannot be retrieved later.",
        )
    except UnauthorizedError as e:
        return _err("error", str(e.message), "unauthorized")
    except Exception as e:
        return _err("error", str(e), "unknown_error", traceback=traceback.format_exc())


def revoke_key(args: Dict[str, Any]) -> Dict[str, Any]:
    """Revoke an API key (session)."""
    try:
        base_url = get_base_url(args)
        client = get_client_session(
            base_url,
            args.get("username") or "",
            args.get("password") or "",
        )
        client.revoke_key(key_id=int(args.get("key_id", 0)))
        client.logout()
        return _ok(message="API key revoked")
    except UnauthorizedError as e:
        return _err("error", str(e.message), "unauthorized")
    except Exception as e:
        return _err("error", str(e), "unknown_error", traceback=traceback.format_exc())


def list_deposits(args: Dict[str, Any]) -> Dict[str, Any]:
    """List deposits for current user's stores (session)."""
    try:
        base_url = get_base_url(args)
        client = get_client_session(
            base_url,
            args.get("username") or "",
            args.get("password") or "",
        )
        out = client.list_deposits()
        client.logout()
        return _ok(deposits=out.get("deposits", []))
    except UnauthorizedError as e:
        return _err("error", str(e.message), "unauthorized")
    except Exception as e:
        return _err("error", str(e), "unknown_error", traceback=traceback.format_exc())


def list_disputes(args: Dict[str, Any]) -> Dict[str, Any]:
    """List disputes (session)."""
    try:
        base_url = get_base_url(args)
        client = get_client_session(
            base_url,
            args.get("username") or "",
            args.get("password") or "",
        )
        out = client.list_disputes()
        client.logout()
        return _ok(disputes=out.get("disputes", []))
    except UnauthorizedError as e:
        return _err("error", str(e.message), "unauthorized")
    except Exception as e:
        return _err("error", str(e), "unknown_error", traceback=traceback.format_exc())


def get_plugin_description() -> Dict[str, Any]:
    """Return structured plugin description for --describe."""
    def param(name: str, type_: str, description: str, required: bool = False, default: Any = None) -> Dict[str, Any]:
        return {"name": name, "type": type_, "description": description, "required": required, "default": default}

    return {
        "plugin": {
            "name": "marketplace",
            "version": "0.1.0",
            "description": "Marketplace REST API: stores, items, transactions, API keys, deposits, disputes",
        },
        "commands": [
            {
                "name": "health",
                "description": "Health check (GET /)",
                "parameters": [
                    param("base_url", "string", "API base URL (optional; env MARKETPLACE_BASE_URL or http://localhost)", False, None),
                ],
            },
            {
                "name": "list-stores",
                "description": "List all stores (public)",
                "parameters": [
                    param("base_url", "string", "API base URL (optional)", False, None),
                ],
            },
            {
                "name": "list-items",
                "description": "List items, optionally filtered by store UUID",
                "parameters": [
                    param("base_url", "string", "API base URL (optional)", False, None),
                    param("store_uuid", "string", "Filter by store UUID", False, None),
                ],
            },
            {
                "name": "get-auth-user",
                "description": "Get current user for API key",
                "parameters": [
                    param("base_url", "string", "API base URL (optional)", False, None),
                    param("api_key", "string", "API key (required)", True, None),
                ],
            },
            {
                "name": "list-transactions",
                "description": "List transactions (API key)",
                "parameters": [
                    param("base_url", "string", "API base URL (optional)", False, None),
                    param("api_key", "string", "API key (required)", True, None),
                ],
            },
            {
                "name": "create-store",
                "description": "Create a store (session: username + password)",
                "parameters": [
                    param("base_url", "string", "API base URL (optional)", False, None),
                    param("username", "string", "Login username (required)", True, None),
                    param("password", "string", "Login password (required)", True, None),
                    param("storename", "string", "Store name (max 16 chars)", True, None),
                    param("description", "string", "Store description", False, ""),
                    param("vendorship_agree", "string", "Set to 1 to agree to terms", False, "1"),
                ],
            },
            {
                "name": "create-item",
                "description": "Create an item (session)",
                "parameters": [
                    param("base_url", "string", "API base URL (optional)", False, None),
                    param("username", "string", "Login username (required)", True, None),
                    param("password", "string", "Login password (required)", True, None),
                    param("name", "string", "Item name", True, None),
                    param("store_uuid", "string", "Store UUID", True, None),
                    param("description", "string", "Item description", False, ""),
                ],
            },
            {
                "name": "create-transaction",
                "description": "Create a transaction (session)",
                "parameters": [
                    param("base_url", "string", "API base URL (optional)", False, None),
                    param("username", "string", "Login username (required)", True, None),
                    param("password", "string", "Login password (required)", True, None),
                    param("package_uuid", "string", "Package UUID", True, None),
                    param("required_amount", "number", "Amount in crypto", True, None),
                    param("chain_id", "integer", "EVM chain ID (default 1)", False, 1),
                    param("currency", "string", "Currency symbol (default ETH)", False, "ETH"),
                    param("refund_address", "string", "EVM refund address", False, None),
                ],
            },
            {
                "name": "list-keys",
                "description": "List API keys for current user (session)",
                "parameters": [
                    param("base_url", "string", "API base URL (optional)", False, None),
                    param("username", "string", "Login username (required)", True, None),
                    param("password", "string", "Login password (required)", True, None),
                ],
            },
            {
                "name": "create-key",
                "description": "Create API key (session). Save returned api_key; it cannot be retrieved later.",
                "parameters": [
                    param("base_url", "string", "API base URL (optional)", False, None),
                    param("username", "string", "Login username (required)", True, None),
                    param("password", "string", "Login password (required)", True, None),
                    param("name", "string", "Key label", False, ""),
                ],
            },
            {
                "name": "revoke-key",
                "description": "Revoke an API key (session)",
                "parameters": [
                    param("base_url", "string", "API base URL (optional)", False, None),
                    param("username", "string", "Login username (required)", True, None),
                    param("password", "string", "Login password (required)", True, None),
                    param("key_id", "integer", "Key ID from list-keys", True, None),
                ],
            },
            {
                "name": "list-deposits",
                "description": "List deposits for current user's stores (session)",
                "parameters": [
                    param("base_url", "string", "API base URL (optional)", False, None),
                    param("username", "string", "Login username (required)", True, None),
                    param("password", "string", "Login password (required)", True, None),
                ],
            },
            {
                "name": "list-disputes",
                "description": "List disputes (session)",
                "parameters": [
                    param("base_url", "string", "API base URL (optional)", False, None),
                    param("username", "string", "Login username (required)", True, None),
                    param("password", "string", "Login password (required)", True, None),
                ],
            },
        ],
    }


def main() -> None:
    parser = argparse.ArgumentParser(
        description="Marketplace API integration for SMCP / MCP",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
Available commands:
  health             Health check
  list-stores        List all stores
  list-items         List items (optional store_uuid)
  get-auth-user      Current user (API key)
  list-transactions  List transactions (API key)
  create-store       Create store (session)
  create-item        Create item (session)
  create-transaction Create transaction (session)
  list-keys          List API keys (session)
  create-key         Create API key (session)
  revoke-key         Revoke API key (session)
  list-deposits      List deposits (session)
  list-disputes      List disputes (session)

Auth: Use --api-key for key-authed commands; use --username and --password for session commands.
Base URL: --base-url or env MARKETPLACE_BASE_URL (default http://localhost)
        """,
    )
    parser.add_argument("--describe", action="store_true", help="Output plugin description JSON")
    subparsers = parser.add_subparsers(dest="command", help="Commands")

    def add_common_no_auth(p: argparse.ArgumentParser) -> None:
        p.add_argument("--base-url", "--base_url", dest="base_url", help="API base URL")

    def add_common_api_key(p: argparse.ArgumentParser) -> None:
        p.add_argument("--base-url", "--base_url", dest="base_url", help="API base URL")
        p.add_argument("--api-key", "--api_key", dest="api_key", required=True, help="API key")

    def add_common_session(p: argparse.ArgumentParser) -> None:
        p.add_argument("--base-url", "--base_url", dest="base_url", help="API base URL")
        p.add_argument("--username", required=True, help="Login username")
        p.add_argument("--password", required=True, help="Login password")

    for name in ("health", "list-stores", "list-items"):
        sp = subparsers.add_parser(name, help=name)
        add_common_no_auth(sp)

    sp = subparsers.add_parser("get-auth-user", help="Get current user (API key)")
    add_common_api_key(sp)
    sp = subparsers.add_parser("list-transactions", help="List transactions (API key)")
    add_common_api_key(sp)

    sp = subparsers.add_parser("create-store", help="Create store (session)")
    add_common_session(sp)
    sp.add_argument("--storename", required=True, help="Store name")
    sp.add_argument("--description", default="", help="Description")
    sp.add_argument("--vendorship-agree", "--vendorship_agree", dest="vendorship_agree", default="1", help="1 to agree")

    sp = subparsers.add_parser("create-item", help="Create item (session)")
    add_common_session(sp)
    sp.add_argument("--name", required=True, help="Item name")
    sp.add_argument("--store-uuid", "--store_uuid", dest="store_uuid", required=True, help="Store UUID")
    sp.add_argument("--description", default="", help="Description")

    sp = subparsers.add_parser("create-transaction", help="Create transaction (session)")
    add_common_session(sp)
    sp.add_argument("--package-uuid", "--package_uuid", dest="package_uuid", required=True, help="Package UUID")
    sp.add_argument("--required-amount", "--required_amount", dest="required_amount", type=float, required=True, help="Amount")
    sp.add_argument("--chain-id", "--chain_id", dest="chain_id", type=int, default=1, help="Chain ID")
    sp.add_argument("--currency", default="ETH", help="Currency")
    sp.add_argument("--refund-address", "--refund_address", dest="refund_address", help="Refund address")

    for name in ("list-keys", "list-deposits", "list-disputes"):
        sp = subparsers.add_parser(name, help=name)
        add_common_session(sp)

    sp = subparsers.add_parser("create-key", help="Create API key (session)")
    add_common_session(sp)
    sp.add_argument("--name", default="", help="Key label")

    sp = subparsers.add_parser("revoke-key", help="Revoke API key (session)")
    add_common_session(sp)
    sp.add_argument("--key-id", "--key_id", dest="key_id", type=int, required=True, help="Key ID")

    try:
        args = parser.parse_args()
    except SystemExit as e:
        out = _err("error", "Invalid arguments. Check command syntax.", "argument_error")
        print(json.dumps(out, indent=2), file=sys.stderr)
        print(json.dumps(out, indent=2))
        sys.exit(2)
    if args.describe:
        print(json.dumps(get_plugin_description(), indent=2))
        sys.exit(0)
    if not args.command:
        parser.print_help()
        sys.exit(1)

    # Command name as passed (SMCP uses e.g. list-stores)
    cmd = args.command
    args_dict = {k: v for k, v in vars(args).items() if k not in ("command", "describe")}

    handlers = {
        "health": health,
        "list-stores": list_stores,
        "list-items": list_items,
        "get-auth-user": get_auth_user,
        "list-transactions": list_transactions,
        "create-store": create_store,
        "create-item": create_item,
        "create-transaction": create_transaction,
        "list-keys": list_keys,
        "create-key": create_key,
        "revoke-key": revoke_key,
        "list-deposits": list_deposits,
        "list-disputes": list_disputes,
    }
    handler = handlers.get(cmd)
    if not handler:
        out = _err("error", f"Unknown command: {cmd}")
        print(json.dumps(out, indent=2))
        sys.exit(1)
    try:
        result = handler(args_dict)
    except Exception as e:
        result = _err("error", str(e), "unknown_error", traceback=traceback.format_exc())
        print(json.dumps(result, indent=2), file=sys.stderr)
        print(json.dumps(result, indent=2))
        sys.exit(1)
    print(json.dumps(result, indent=2))
    sys.exit(0 if result.get("status") == "success" else 1)


if __name__ == "__main__":
    main()
