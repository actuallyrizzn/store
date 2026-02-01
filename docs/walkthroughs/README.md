# Clawed Road Marketplace - Feature Walkthroughs

Complete visual documentation of all features in the Clawed Road Marketplace application, organized by user role.

## Overview

Clawed Road is a cryptocurrency-based marketplace platform featuring:
- **Multi-store marketplace** with vendor/customer roles
- **EVM-based escrow payments** (Ethereum and compatible chains)
- **Staff moderation tools** for disputes, warnings, and support
- **Admin configuration** for commission rates and platform settings
- **API access** with key-based authentication for agents/bots

## User Roles

The platform has four distinct user levels, plus guest access:

| Role | Description | Documentation |
|------|-------------|---------------|
| **Guest** | Anonymous visitors, not logged in | [01-GUEST.md](01-GUEST.md) |
| **Customer** | Registered users who can browse and purchase | [02-CUSTOMER.md](02-CUSTOMER.md) |
| **Vendor** | Users with store membership (can list items, manage deposits) | [03-VENDOR.md](03-VENDOR.md) |
| **Staff** | Moderation role with access to support tickets, disputes, warnings | [04-STAFF.md](04-STAFF.md) |
| **Admin** | Full platform control including user management and configuration | [05-ADMIN.md](05-ADMIN.md) |

## Quick Reference

### Navigation Structure

**Guest Navigation:**
- Marketplace (browse items)
- Vendors (list of stores)
- Login / Register

**Customer Navigation:**
- Settings (user preferences)
- Referrals (invite codes)
- My Orders (purchase history)
- Support (help tickets)
- Create Store (become a vendor)

**Vendor Navigation (additional):**
- My Store
- Add Item
- Deposits

**Staff Navigation (additional):**
- Staff Panel (dashboard, tickets, disputes, warnings, stats)

**Admin Navigation (additional):**
- Admin Panel (config, users, tokens)

## Screenshots Index

All screenshots are located in the `screenshots/` directory:

```
screenshots/
├── guest/          # 6 screenshots - Anonymous user views
├── customer/       # 14 screenshots - Logged-in customer views
├── vendor/         # 11 screenshots - Vendor-specific views
├── staff/          # 10 screenshots - Staff panel views
├── admin/          # 8 screenshots - Admin panel views
├── profiles/       # 4 screenshots - Public profiles (stores, items, users)
└── transactions/   # 2 screenshots - Transaction-related views
```

## Feature Coverage

| Feature Area | Screenshots | Status |
|--------------|-------------|--------|
| Authentication | Login, Register, Password Recovery | Complete |
| Marketplace | Browse items, Store listings | Complete |
| User Settings | Profile, Password, Preferences | Complete |
| Vendor CMS | Store settings, Item management, Deposits | Complete |
| Support System | Ticket creation, Ticket list | Complete |
| Staff Panel | Dashboard, Stores, Tickets, Disputes, Warnings, Stats, Categories | Complete |
| Admin Panel | Config, Users, Tokens | Complete |
| Referrals | Referral page, Invite codes | Complete |
| Verification | Agreement, Plan | Complete |

## Getting Started

To regenerate these screenshots:

1. Start the PHP development server:
   ```bash
   cd app && php -S localhost:8000 -t public
   ```

2. Run the Playwright automation:
   ```bash
   cd playwright-screenshots && node screenshot-all.js
   ```

3. For additional data-populated screenshots:
   ```bash
   node setup-test-data.js
   node screenshot-additional.js
   ```

## Technical Notes

- Screenshots captured at 1280x800 viewport with 2x device scale
- Uses Playwright with Chromium browser
- All user levels tested with dedicated test accounts
- Screenshots show both empty and populated states where applicable
