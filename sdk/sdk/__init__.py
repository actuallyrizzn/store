"""
Marketplace API Python SDK.

Usage:

    from sdk import MarketplaceClient

    client = MarketplaceClient(base_url="https://market.example.com", api_key="your-key")
    stores = client.list_stores()
    transactions = client.list_transactions()

Session auth (for creating stores, items, transactions, keys):

    client = MarketplaceClient(base_url="https://market.example.com")
    client.login("alice", "secret123")
    client.create_store("MyStore", description="My shop", vendorship_agree=True)
"""

from .client import DEFAULT_BASE_URL, MarketplaceClient
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

__all__ = [
    "DEFAULT_BASE_URL",
    "MarketplaceClient",
    "MarketplaceAPIError",
    "UnauthorizedError",
    "ForbiddenError",
    "NotFoundError",
    "ConflictError",
    "RateLimitError",
    "ValidationError",
    "ServerError",
]
__version__ = "1.0.0"
