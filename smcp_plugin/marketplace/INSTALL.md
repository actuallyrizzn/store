# Marketplace SMCP Plugin — Installation

## 1. Copy plugin into SMCP

```bash
cp -r smcp_plugin/marketplace /path/to/smcp/plugins/
```

(From the store repo root, `smcp_plugin/marketplace` is the plugin directory.)

## 2. Install Python dependencies

**Option A — SDK from this repo (recommended when developing):**

From the store repo root:

```bash
pip install -e sdk
```

Then ensure the plugin can import `sdk` (e.g. run from store repo or install marketplace-sdk in the same env as SMCP).

**Option B — SDK next to plugin:**

If you copied the plugin to `smcp/plugins/marketplace/`, you can install the SDK from the store repo:

```bash
pip install -e /path/to/store/sdk
```

**Option C — requirements.txt in plugin dir:**

```bash
cd /path/to/smcp/plugins/marketplace
pip install -r requirements.txt
```

(`requirements.txt` lists `marketplace-sdk>=1.0.0`; you must have the package available, e.g. `pip install -e /path/to/store/sdk`.)

## 3. Make CLI executable (optional)

```bash
chmod +x /path/to/smcp/plugins/marketplace/cli.py
```

## 4. Restart SMCP

Restart the SMCP server so it discovers the `marketplace` plugin.

## Configuration

- **Base URL:** `--base-url` per call or env `MARKETPLACE_BASE_URL` (default `http://localhost`).
- **API key:** Required for `get-auth-user` and `list-transactions`. Create via web UI or `create-key` (session).
- **Session:** Use `--username` and `--password` for create-store, create-item, create-transaction, list-keys, create-key, revoke-key, list-deposits, list-disputes.

## Testing

```bash
# Plugin description
python /path/to/smcp/plugins/marketplace/cli.py --describe

# Health check
python /path/to/smcp/plugins/marketplace/cli.py health --base-url http://localhost

# List stores
python /path/to/smcp/plugins/marketplace/cli.py list-stores --base-url http://localhost
```

## SMCP tool names

When SMCP loads the plugin, tools are named `marketplace__<command>`, e.g.:

- `marketplace__health`
- `marketplace__list-stores`
- `marketplace__list-items`
- `marketplace__get-auth-user`
- `marketplace__list-transactions`
- `marketplace__create-store`
- `marketplace__create-item`
- `marketplace__create-transaction`
- `marketplace__list-keys`
- `marketplace__create-key`
- `marketplace__revoke-key`
- `marketplace__list-deposits`
- `marketplace__list-disputes`

## Uninstall

```bash
rm -rf /path/to/smcp/plugins/marketplace
```

Restart SMCP after removal.
