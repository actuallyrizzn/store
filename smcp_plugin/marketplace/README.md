# Marketplace SMCP Plugin

SMCP / MCP plugin for the Marketplace REST API. Exposes tools for stores, items, transactions, API keys, deposits, and disputes. Built on the [marketplace SDK](../../sdk/).

## Commands

| Command | Auth | Description |
|--------|------|-------------|
| `health` | — | Health check |
| `list-stores` | — | List all stores (public) |
| `list-items` | — | List items (optional `store_uuid`) |
| `get-auth-user` | API key | Current user for API key |
| `list-transactions` | API key | List transactions |
| `create-store` | Session | Create store |
| `create-item` | Session | Create item |
| `create-transaction` | Session | Create transaction |
| `list-keys` | Session | List API keys |
| `create-key` | Session | Create API key |
| `revoke-key` | Session | Revoke API key |
| `list-deposits` | Session | List deposits |
| `list-disputes` | Session | List disputes |

**Auth:** Commands that need an API key use `--api-key`. Commands that need a session use `--username` and `--password` (the plugin logs in, runs the command, then logs out).

**Base URL:** Optional `--base-url` or env `MARKETPLACE_BASE_URL` (default `http://localhost`).

## Examples

```bash
# Health check
python cli.py health --base-url https://market.example.com

# List stores (no auth)
python cli.py list-stores

# List items for a store
python cli.py list-items --store-uuid abc123

# Get current user (API key)
python cli.py get-auth-user --api-key YOUR_API_KEY

# List transactions (API key)
python cli.py list-transactions --api-key YOUR_API_KEY

# Create store (session)
python cli.py create-store --username alice --password secret --storename MyStore --description "My shop"

# Create API key (session) — save returned api_key
python cli.py create-key --username alice --password secret --name "MCP Agent"
```

## Plugin description (for SMCP)

```bash
python cli.py --describe
```

Outputs JSON with plugin name, version, and command/parameter definitions for MCP tool registration.

## Installation

See [INSTALL.md](INSTALL.md).

## Dependencies

- Python 3.8+
- [marketplace-sdk](https://github.com/...) (or `pip install -e ../../sdk` from this repo)
