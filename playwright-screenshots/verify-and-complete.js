/**
 * Verify screenshots and capture any missing pages
 */

const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const BASE_URL = 'http://localhost:8000';
const SCREENSHOT_DIR = path.join(__dirname, '../docs/walkthroughs/screenshots');

const users = {
    admin: { username: 'admin', password: 'admin123' },
    vendor: { username: 'vendoruser', password: 'vendoruser123' },
    customer: { username: 'testcustomer', password: 'testcustomer123' }
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

async function main() {
    console.log('Verifying and capturing missing pages...\n');
    
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
        // Verify item/new.php redirect
        console.log('=== VERIFY ITEM NEW PAGE ===');
        await login(page, users.vendor.username, users.vendor.password);
        
        // Get store UUID
        await page.goto(`${BASE_URL}/marketplace.php`);
        const myStoreLink = await page.$('a[href*="store.php?uuid="]');
        let storeUuid = null;
        if (myStoreLink) {
            const href = await myStoreLink.getAttribute('href');
            const match = href.match(/uuid=([^&]+)/);
            if (match) {
                storeUuid = match[1];
            }
        }
        
        // Item new doesn't exist as web page, but let's verify the API-only message
        if (storeUuid) {
            // Try to navigate to item/new.php (will redirect/404)
            try {
                await page.goto(`${BASE_URL}/item/new.php?store_uuid=${storeUuid}`, { waitUntil: 'networkidle' });
                const content = await page.content();
                console.log('  item/new.php redirects to: ' + page.url());
                await screenshot(page, '07-item-new-redirect', 'vendor');
            } catch (e) {
                console.log('  item/new.php not accessible (expected)');
            }
        }
        
        // Find an item to view/edit
        await page.goto(`${BASE_URL}/store.php?uuid=${storeUuid}`);
        await page.waitForLoadState('networkidle');
        
        const itemLink = await page.$('a[href*="item.php?uuid="]');
        if (itemLink) {
            await itemLink.click();
            await page.waitForLoadState('networkidle');
            const url = page.url();
            const match = url.match(/uuid=([^&]+)/);
            if (match) {
                const itemUuid = match[1];
                
                // Item edit
                await page.goto(`${BASE_URL}/item/edit.php?uuid=${itemUuid}`);
                await page.waitForLoadState('networkidle');
                await screenshot(page, '08-item-edit-form', 'vendor');
            }
        }
        
        await logout(page);
        
        // Review page (as customer after a transaction would be released)
        console.log('\n=== REVIEW PAGE ===');
        await login(page, users.customer.username, users.customer.password);
        
        await page.goto(`${BASE_URL}/review/add.php`);
        await page.waitForLoadState('networkidle');
        await screenshot(page, '15-review-add', 'customer');
        
        await logout(page);
        
        // Admin tokens page
        console.log('\n=== ADMIN TOKENS DETAIL ===');
        await login(page, users.admin.username, users.admin.password);
        
        await page.goto(`${BASE_URL}/admin/tokens.php`);
        await page.waitForLoadState('networkidle');
        await screenshot(page, '05-admin-tokens-list', 'admin');
        
        // Try to create a token
        const createBtn = await page.$('button[type="submit"]:has-text("Create"), button:has-text("Create token")');
        if (createBtn) {
            await createBtn.click();
            await page.waitForLoadState('networkidle');
            await screenshot(page, '06-admin-tokens-after-create', 'admin');
        }
        
        await logout(page);
        
        console.log('\n=== COMPLETE ===');
        
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
}

main().catch(console.error);
