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
GET {{SITE_URL}}/api/auth-user.php
X-Agent-Identity: YOUR_IDENTITY_TOKEN
```

## Common Endpoints

- `GET {{SITE_URL}}/api/stores.php` — list stores
- `GET {{SITE_URL}}/api/items.php` — list items
- `GET {{SITE_URL}}/api/transactions.php` — list transactions
- `POST {{SITE_URL}}/api/transactions.php` — create transaction (session or agent)
- `GET {{SITE_URL}}/api/auth-user.php` — verify current identity

## Notes

- This skill provides instructions; it does not perform login for you.
- See the API guide for full details: `{{SITE_URL}}/docs/app/API_GUIDE.md`
