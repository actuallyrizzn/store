# Python Cron (EVM)

Runs on schedule (e.g. every 1–5 min). Loads `.env` from repo root; uses MNEMONIC, ALCHEMY_*, COMMISSION_WALLET_*, and DB_*.

- **Fill escrow addresses**: Finds `evm_transactions` where `escrow_address` IS NULL, derives address (BIP-32/44), updates row, inserts first `transaction_status` (PENDING).
- **Full cron** (phase 4): Poll PENDING escrow balance, set COMPLETED; release old COMPLETED; fail/freeze/reconcile; deposit withdraw.

Run from repo root: `python cron/cron.py` (or `python -m cron.cron` from repo root with `cron` on PYTHONPATH).

Install: `pip install -r cron/requirements.txt`
