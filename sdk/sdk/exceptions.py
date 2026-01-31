"""Exceptions raised by the Marketplace API client."""

from typing import Any, Optional


class MarketplaceAPIError(Exception):
    """Base exception for API errors."""

    def __init__(
        self,
        message: str,
        status_code: Optional[int] = None,
        response_body: Optional[Any] = None,
    ) -> None:
        super().__init__(message)
        self.message = message
        self.status_code = status_code
        self.response_body = response_body


class UnauthorizedError(MarketplaceAPIError):
    """401 - Missing or invalid authentication."""

    pass


class ForbiddenError(MarketplaceAPIError):
    """403 - Insufficient permissions."""

    pass


class NotFoundError(MarketplaceAPIError):
    """404 - Resource not found."""

    pass


class ConflictError(MarketplaceAPIError):
    """409 - Resource already exists or conflict."""

    pass


class RateLimitError(MarketplaceAPIError):
    """429 - Too many requests."""

    pass


class ValidationError(MarketplaceAPIError):
    """400 - Invalid parameters."""

    pass


class ServerError(MarketplaceAPIError):
    """5xx - Server error."""

    pass
