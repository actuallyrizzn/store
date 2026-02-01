/**
 * Clawed Road Marketplace - Complete Feature Walkthrough Screenshot Automation
 * 
 * This script walks through all features at each user level and takes screenshots
 * for documentation purposes.
 */

const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const BASE_URL = 'http://localhost:8000';
const SCREENSHOT_DIR = path.join(__dirname, '../docs/walkthroughs/screenshots');

// Ensure screenshot directory exists
if (!fs.existsSync(SCREENSHOT_DIR)) {
    fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });
}

// Test user credentials
const users = {
    admin: { username: 'admin', password: 'admin123' },
    staff: { username: 'staffuser', password: 'staffuser123' },
    vendor: { username: 'vendoruser', password: 'vendoruser123' },
    customer: { username: 'testcustomer', password: 'testcustomer123' }
};

// Store UUIDs discovered during runtime
const runtimeData = {
    storeUuid: null,
    itemUuid: null,
    transactionUuid: null,
    ticketUuid: null,
    disputeUuid: null,
    vendorUsername: null
};

async function screenshot(page, name, subdir = '') {
    const dir = subdir ? path.join(SCREENSHOT_DIR, subdir) : SCREENSHOT_DIR;
    if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir, { recursive: true });
    }
    const filepath = path.join(dir, `${name}.png`);
    await page.screenshot({ path: filepath, fullPage: true });
    console.log(`  Screenshot: ${subdir ? subdir + '/' : ''}${name}.png`);
    return filepath;
}

async function login(page, username, password) {
    await page.goto(`${BASE_URL}/login.php`);
    await page.fill('input[name="username"]', username);
    await page.fill('input[name="password"]', password);
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');
}

async function logout(page) {
    await page.goto(`${BASE_URL}/logout.php`);
    await page.waitForLoadState('networkidle');
}

async function registerUser(page, username, password) {
    await page.goto(`${BASE_URL}/register.php`);
    await page.fill('input[name="username"]', username);
    await page.fill('input[name="password"]', password);
    
    // Get CSRF token if present
    const csrfInput = await page.$('input[name="csrf_token"]');
    
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');
    
    // Check if registration was successful by looking for marketplace redirect
    const url = page.url();
    if (url.includes('marketplace.php')) {
        console.log(`  User ${username} registered successfully`);
        return true;
    }
    console.log(`  User ${username} may already exist or registration failed`);
    return false;
}

// ============================================================================
// GUEST PAGES (Not logged in)
// ============================================================================
async function screenshotGuestPages(page) {
    console.log('\n=== GUEST PAGES (Not logged in) ===');
    
    // Home/Marketplace
    await page.goto(`${BASE_URL}/`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '01-home-marketplace', 'guest');
    
    // Marketplace listing
    await page.goto(`${BASE_URL}/marketplace.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '02-marketplace', 'guest');
    
    // Vendors list
    await page.goto(`${BASE_URL}/vendors.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '03-vendors-list', 'guest');
    
    // Login page
    await page.goto(`${BASE_URL}/login.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '04-login', 'guest');
    
    // Register page
    await page.goto(`${BASE_URL}/register.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '05-register', 'guest');
    
    // Password recovery page
    await page.goto(`${BASE_URL}/recover.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '06-password-recovery', 'guest');
}

// ============================================================================
// CUSTOMER PAGES (Logged in regular user)
// ============================================================================
async function screenshotCustomerPages(page) {
    console.log('\n=== CUSTOMER PAGES (Logged in customer) ===');
    
    // Login as customer
    await login(page, users.customer.username, users.customer.password);
    await screenshot(page, '01-after-login-marketplace', 'customer');
    
    // User settings
    await page.goto(`${BASE_URL}/settings/user.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '02-settings-user', 'customer');
    
    // Referrals
    await page.goto(`${BASE_URL}/referrals.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '03-referrals', 'customer');
    
    // My orders (payments)
    await page.goto(`${BASE_URL}/payments.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '04-my-orders', 'customer');
    
    // Support tickets list
    await page.goto(`${BASE_URL}/support.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '05-support-list', 'customer');
    
    // New support ticket
    await page.goto(`${BASE_URL}/support/new.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '06-support-new', 'customer');
    
    // Messages
    await page.goto(`${BASE_URL}/messages.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '07-messages', 'customer');
    
    // Create store page
    await page.goto(`${BASE_URL}/create-store.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '08-create-store', 'customer');
    
    // Verification agreement
    await page.goto(`${BASE_URL}/verification/agreement.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '09-verification-agreement', 'customer');
    
    // Verification plan
    await page.goto(`${BASE_URL}/verification/plan.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '10-verification-plan', 'customer');
    
    // Browse marketplace as customer
    await page.goto(`${BASE_URL}/marketplace.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '11-marketplace-logged-in', 'customer');
    
    // Vendors page
    await page.goto(`${BASE_URL}/vendors.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '12-vendors-logged-in', 'customer');
    
    await logout(page);
}

// ============================================================================
// VENDOR PAGES (Customer with store)
// ============================================================================
async function screenshotVendorPages(page) {
    console.log('\n=== VENDOR PAGES (User with store) ===');
    
    // Login as vendor
    await login(page, users.vendor.username, users.vendor.password);
    
    // Marketplace with vendor nav
    await page.goto(`${BASE_URL}/marketplace.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '01-marketplace-vendor-nav', 'vendor');
    
    // Try to get store UUID from nav link
    const myStoreLink = await page.$('a[href*="store.php?uuid="]');
    if (myStoreLink) {
        const href = await myStoreLink.getAttribute('href');
        const match = href.match(/uuid=([^&]+)/);
        if (match) {
            runtimeData.storeUuid = match[1];
            console.log(`  Found store UUID: ${runtimeData.storeUuid}`);
        }
    }
    
    // My store page
    if (runtimeData.storeUuid) {
        await page.goto(`${BASE_URL}/store.php?uuid=${runtimeData.storeUuid}`);
        await page.waitForLoadState('networkidle');
        await screenshot(page, '02-my-store', 'vendor');
    }
    
    // Store settings
    await page.goto(`${BASE_URL}/settings/store.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '03-store-settings', 'vendor');
    
    // Deposits list
    await page.goto(`${BASE_URL}/deposits.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '04-deposits-list', 'vendor');
    
    // Add deposit
    await page.goto(`${BASE_URL}/deposits/add.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '05-deposits-add', 'vendor');
    
    // Withdraw deposit
    await page.goto(`${BASE_URL}/deposits/withdraw.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '06-deposits-withdraw', 'vendor');
    
    // Add new item
    if (runtimeData.storeUuid) {
        await page.goto(`${BASE_URL}/item/new.php?store_uuid=${runtimeData.storeUuid}`);
        await page.waitForLoadState('networkidle');
        await screenshot(page, '07-item-new', 'vendor');
    }
    
    // Try to find an existing item to edit
    if (runtimeData.storeUuid) {
        await page.goto(`${BASE_URL}/store.php?uuid=${runtimeData.storeUuid}`);
        await page.waitForLoadState('networkidle');
        const itemLink = await page.$('a[href*="item.php?uuid="]');
        if (itemLink) {
            const href = await itemLink.getAttribute('href');
            const match = href.match(/uuid=([^&]+)/);
            if (match) {
                runtimeData.itemUuid = match[1];
                console.log(`  Found item UUID: ${runtimeData.itemUuid}`);
                
                // Item view page
                await page.goto(`${BASE_URL}/item.php?uuid=${runtimeData.itemUuid}`);
                await page.waitForLoadState('networkidle');
                await screenshot(page, '08-item-view', 'vendor');
                
                // Item edit page
                await page.goto(`${BASE_URL}/item/edit.php?uuid=${runtimeData.itemUuid}`);
                await page.waitForLoadState('networkidle');
                await screenshot(page, '09-item-edit', 'vendor');
            }
        }
    }
    
    // User settings as vendor
    await page.goto(`${BASE_URL}/settings/user.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '10-vendor-user-settings', 'vendor');
    
    // Referrals as vendor
    await page.goto(`${BASE_URL}/referrals.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '11-vendor-referrals', 'vendor');
    
    // My orders as vendor
    await page.goto(`${BASE_URL}/payments.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '12-vendor-my-orders', 'vendor');
    
    // Support as vendor
    await page.goto(`${BASE_URL}/support.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '13-vendor-support', 'vendor');
    
    await logout(page);
}

// ============================================================================
// STAFF PAGES
// ============================================================================
async function screenshotStaffPages(page) {
    console.log('\n=== STAFF PAGES ===');
    
    // Login as staff
    await login(page, users.staff.username, users.staff.password);
    
    // Staff dashboard
    await page.goto(`${BASE_URL}/staff/index.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '01-staff-dashboard', 'staff');
    
    // Staff - Stores
    await page.goto(`${BASE_URL}/staff/stores.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '02-staff-stores', 'staff');
    
    // Staff - Support tickets
    await page.goto(`${BASE_URL}/staff/tickets.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '03-staff-tickets', 'staff');
    
    // Staff - Disputes
    await page.goto(`${BASE_URL}/staff/disputes.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '04-staff-disputes', 'staff');
    
    // Staff - Warnings
    await page.goto(`${BASE_URL}/staff/warnings.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '05-staff-warnings', 'staff');
    
    // Staff - Deposits
    await page.goto(`${BASE_URL}/staff/deposits.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '06-staff-deposits', 'staff');
    
    // Staff - Stats
    await page.goto(`${BASE_URL}/staff/stats.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '07-staff-stats', 'staff');
    
    // Staff - Categories
    await page.goto(`${BASE_URL}/staff/categories.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '08-staff-categories', 'staff');
    
    // Staff member marketplace view
    await page.goto(`${BASE_URL}/marketplace.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '09-staff-marketplace-view', 'staff');
    
    await logout(page);
}

// ============================================================================
// ADMIN PAGES
// ============================================================================
async function screenshotAdminPages(page) {
    console.log('\n=== ADMIN PAGES ===');
    
    // Login as admin
    await login(page, users.admin.username, users.admin.password);
    
    // Admin main/index
    await page.goto(`${BASE_URL}/admin/index.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '01-admin-dashboard', 'admin');
    
    // Admin config
    await page.goto(`${BASE_URL}/admin/config.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '02-admin-config', 'admin');
    
    // Admin users list
    await page.goto(`${BASE_URL}/admin/users.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '03-admin-users-list', 'admin');
    
    // Admin user detail (find a user to view)
    const userLink = await page.$('a[href*="admin/users.php?uuid="]');
    if (userLink) {
        await userLink.click();
        await page.waitForLoadState('networkidle');
        await screenshot(page, '04-admin-user-detail', 'admin');
    }
    
    // Admin API tokens
    await page.goto(`${BASE_URL}/admin/tokens.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '05-admin-tokens', 'admin');
    
    // Admin - can also access staff pages
    await page.goto(`${BASE_URL}/staff/index.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '06-admin-staff-access', 'admin');
    
    // Admin marketplace view
    await page.goto(`${BASE_URL}/marketplace.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '07-admin-marketplace-view', 'admin');
    
    // Admin vendors view
    await page.goto(`${BASE_URL}/vendors.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '08-admin-vendors-view', 'admin');
    
    await logout(page);
}

// ============================================================================
// ADDITIONAL PAGES - Store/Item/User profiles
// ============================================================================
async function screenshotPublicProfilePages(page) {
    console.log('\n=== PUBLIC PROFILE PAGES ===');
    
    // First, let's browse stores and items as a guest
    await page.goto(`${BASE_URL}/vendors.php`);
    await page.waitForLoadState('networkidle');
    
    // Try to find a store link
    const storeLink = await page.$('a[href*="store.php?uuid="]');
    if (storeLink) {
        await storeLink.click();
        await page.waitForLoadState('networkidle');
        await screenshot(page, '01-store-profile', 'profiles');
        
        // Try to find an item link on the store page
        const itemLink = await page.$('a[href*="item.php?uuid="]');
        if (itemLink) {
            await itemLink.click();
            await page.waitForLoadState('networkidle');
            await screenshot(page, '02-item-detail', 'profiles');
        }
    }
    
    // User profile page
    await page.goto(`${BASE_URL}/user.php?username=${users.vendor.username}`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '03-user-profile', 'profiles');
    
    // Book page (if exists)
    await page.goto(`${BASE_URL}/book.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '04-book', 'profiles');
}

// ============================================================================
// TRANSACTION FLOW PAGES
// ============================================================================
async function screenshotTransactionPages(page) {
    console.log('\n=== TRANSACTION FLOW PAGES ===');
    
    // Login as customer to see transaction pages
    await login(page, users.customer.username, users.customer.password);
    
    // My payments/orders
    await page.goto(`${BASE_URL}/payments.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '01-payments-list', 'transactions');
    
    // Look for a transaction link
    const paymentLink = await page.$('a[href*="payment.php?uuid="]');
    if (paymentLink) {
        await paymentLink.click();
        await page.waitForLoadState('networkidle');
        await screenshot(page, '02-payment-detail', 'transactions');
    }
    
    // Dispute pages
    await page.goto(`${BASE_URL}/dispute.php`);
    await page.waitForLoadState('networkidle');
    await screenshot(page, '03-disputes-list', 'transactions');
    
    await logout(page);
}

// ============================================================================
// SETUP: Create test users and data
// ============================================================================
async function setupTestData(page) {
    console.log('\n=== SETTING UP TEST DATA ===');
    
    // Register customer
    await registerUser(page, users.customer.username, users.customer.password);
    await logout(page);
    
    // Register vendor user (will be granted seller status by admin)
    await registerUser(page, users.vendor.username, users.vendor.password);
    await logout(page);
    
    // Register staff user
    await registerUser(page, users.staff.username, users.staff.password);
    await logout(page);
    
    // Login as admin to set up roles
    console.log('  Setting up user roles via admin...');
    await login(page, users.admin.username, users.admin.password);
    
    // Grant staff role
    await page.goto(`${BASE_URL}/admin/users.php?username=${users.staff.username}`);
    await page.waitForLoadState('networkidle');
    
    const staffButton = await page.$('button[name="submit"][value="1"]:has-text("Set role to staff"), form:has(input[value="staff"]) button[type="submit"]');
    if (staffButton) {
        // Find the form with action=staff
        const forms = await page.$$('form');
        for (const form of forms) {
            const actionInput = await form.$('input[name="action"][value="staff"]');
            if (actionInput) {
                const submitBtn = await form.$('button[type="submit"]');
                if (submitBtn) {
                    await submitBtn.click();
                    await page.waitForLoadState('networkidle');
                    console.log('  Staff role granted');
                    break;
                }
            }
        }
    }
    
    // Grant seller status to vendor user
    await page.goto(`${BASE_URL}/admin/users.php?username=${users.vendor.username}`);
    await page.waitForLoadState('networkidle');
    
    const forms = await page.$$('form');
    for (const form of forms) {
        const actionInput = await form.$('input[name="action"][value="seller"]');
        if (actionInput) {
            const submitBtn = await form.$('button[type="submit"]');
            if (submitBtn) {
                await submitBtn.click();
                await page.waitForLoadState('networkidle');
                console.log('  Seller role granted');
                break;
            }
        }
    }
    
    await logout(page);
    
    // Login as vendor and create a sample item
    console.log('  Creating sample store item...');
    await login(page, users.vendor.username, users.vendor.password);
    
    // Get store UUID
    await page.goto(`${BASE_URL}/marketplace.php`);
    const myStoreLink = await page.$('a[href*="store.php?uuid="]');
    if (myStoreLink) {
        const href = await myStoreLink.getAttribute('href');
        const match = href.match(/uuid=([^&]+)/);
        if (match) {
            runtimeData.storeUuid = match[1];
            
            // Create an item
            await page.goto(`${BASE_URL}/item/new.php?store_uuid=${runtimeData.storeUuid}`);
            await page.waitForLoadState('networkidle');
            
            // Fill in item form if it exists
            const nameInput = await page.$('input[name="name"]');
            if (nameInput) {
                await page.fill('input[name="name"]', 'Sample Product');
                const descInput = await page.$('textarea[name="description"]');
                if (descInput) {
                    await page.fill('textarea[name="description"]', 'This is a sample product for demonstration purposes.');
                }
                const priceInput = await page.$('input[name="price"]');
                if (priceInput) {
                    await page.fill('input[name="price"]', '0.1');
                }
                
                const submitBtn = await page.$('button[type="submit"]');
                if (submitBtn) {
                    await submitBtn.click();
                    await page.waitForLoadState('networkidle');
                    console.log('  Sample item created');
                }
            }
        }
    }
    
    await logout(page);
}

// ============================================================================
// MAIN
// ============================================================================
async function main() {
    console.log('Clawed Road Marketplace - Feature Walkthrough Screenshot Generator');
    console.log('==================================================================\n');
    
    const browser = await chromium.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    const context = await browser.newContext({
        viewport: { width: 1280, height: 800 },
        deviceScaleFactor: 2
    });
    
    const page = await context.newPage();
    
    try {
        // Check if server is running
        try {
            await page.goto(BASE_URL, { timeout: 5000 });
        } catch (e) {
            console.error('ERROR: PHP server not running at ' + BASE_URL);
            console.error('Please start the server with: php -S localhost:8000 -t public');
            process.exit(1);
        }
        
        // Setup test data
        await setupTestData(page);
        
        // Take screenshots for each user level
        await screenshotGuestPages(page);
        await screenshotCustomerPages(page);
        await screenshotVendorPages(page);
        await screenshotStaffPages(page);
        await screenshotAdminPages(page);
        await screenshotPublicProfilePages(page);
        await screenshotTransactionPages(page);
        
        console.log('\n=== COMPLETE ===');
        console.log(`Screenshots saved to: ${SCREENSHOT_DIR}`);
        
    } catch (error) {
        console.error('Error during walkthrough:', error);
        await screenshot(page, 'error-state', 'errors');
    } finally {
        await browser.close();
    }
}

main().catch(console.error);
