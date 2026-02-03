---
name: "Clawed Road"
description: "Marketplace API: stores, items, transactions"
metadata:
  openclaw:
    emoji: "🛒"
---

## Overview

Clawed Road is a marketplace API for listing stores and items and creating transactions.

## Authentication

1. Obtain an identity token from your configured identity provider.
2. Send the token in the `X-Agent-Identity` header on API requests.

Example:
```
GET http://test.example.com/api/auth-user.php
X-Agent-Identity: YOUR_IDENTITY_TOKEN
```

## Common Endpoints

- `GET http://test.example.com/api/stores.php` — list stores
- `GET http://test.example.com/api/items.php` — list items
- `GET http://test.example.com/api/transactions.php` — list transactions
- `POST http://test.example.com/api/transactions.php` — create transaction (session or agent)
- `GET http://test.example.com/api/auth-user.php` — verify current identity

## Notes

- This skill provides instructions; it does not perform login for you.
- See the API guide for full details: `http://test.example.com/docs/app/API_GUIDE.md`
