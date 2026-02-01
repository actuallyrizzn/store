/**
 * Capture additional screenshots with populated data
 */

const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const BASE_URL = 'http://localhost:8000';
const SCREENSHOT_DIR = path.join(__dirname, '../docs/walkthroughs/screenshots');

const users = {
    admin: { username: 'admin', password: 'admin123' },
    staff: { username: 'staffuser', password: 'staffuser123' },
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
    console.log('Capturing additional screenshots with data...\n');
    
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
        // Marketplace with items (guest)
        console.log('=== MARKETPLACE WITH ITEMS ===');
        await page.goto(`${BASE_URL}/marketplace.php`);
        await page.waitForLoadState('networkidle');
        await screenshot(page, '02-marketplace-with-items', 'guest');
        
        // Vendors with stores
        await page.goto(`${BASE_URL}/vendors.php`);
        await page.waitForLoadState('networkidle');
        await screenshot(page, '03-vendors-with-stores', 'guest');
        
        // Store page with items
        const storeLink = await page.$('a[href*="store.php?uuid="]');
        if (storeLink) {
            await storeLink.click();
            await page.waitForLoadState('networkidle');
            await screenshot(page, '01-store-with-items', 'profiles');
            
            // Item detail page
            const itemLink = await page.$('a[href*="item.php?uuid="]');
            if (itemLink) {
                await itemLink.click();
                await page.waitForLoadState('networkidle');
                await screenshot(page, '02-item-detail-page', 'profiles');
                
                // Store the item UUID for later
                const url = page.url();
                const match = url.match(/uuid=([^&]+)/);
                const itemUuid = match ? match[1] : null;
                
                if (itemUuid) {
                    // Item edit page (as vendor)
                    await login(page, users.vendor.username, users.vendor.password);
                    await page.goto(`${BASE_URL}/item/edit.php?uuid=${itemUuid}`);
                    await page.waitForLoadState('networkidle');
                    await screenshot(page, '08-item-edit-page', 'vendor');
                    
                    // Item view as vendor
                    await page.goto(`${BASE_URL}/item.php?uuid=${itemUuid}`);
                    await page.waitForLoadState('networkidle');
                    await screenshot(page, '09-item-view-as-vendor', 'vendor');
                    
                    await logout(page);
                }
            }
        }
        
        // Support ticket list with ticket (customer)
        console.log('\n=== SUPPORT WITH TICKET ===');
        await login(page, users.customer.username, users.customer.password);
        
        await page.goto(`${BASE_URL}/support.php`);
        await page.waitForLoadState('networkidle');
        await screenshot(page, '05-support-with-tickets', 'customer');
        
        // Individual ticket view
        const ticketLink = await page.$('a[href*="support/ticket.php?uuid="]');
        if (ticketLink) {
            await ticketLink.click();
            await page.waitForLoadState('networkidle');
            await screenshot(page, '06-support-ticket-detail', 'customer');
        }
        
        await logout(page);
        
        // Staff view of tickets
        console.log('\n=== STAFF TICKETS VIEW ===');
        await login(page, users.staff.username, users.staff.password);
        
        await page.goto(`${BASE_URL}/staff/tickets.php`);
        await page.waitForLoadState('networkidle');
        await screenshot(page, '03-staff-tickets-with-data', 'staff');
        
        // Staff ticket detail
        const staffTicketLink = await page.$('a[href*="support/ticket.php"]');
        if (staffTicketLink) {
            await staffTicketLink.click();
            await page.waitForLoadState('networkidle');
            await screenshot(page, '10-staff-ticket-detail', 'staff');
        }
        
        await logout(page);
        
        // Vendor store with items
        console.log('\n=== VENDOR STORE WITH ITEMS ===');
        await login(page, users.vendor.username, users.vendor.password);
        
        await page.goto(`${BASE_URL}/marketplace.php`);
        const myStoreLink = await page.$('a[href*="store.php?uuid="]');
        if (myStoreLink) {
            await myStoreLink.click();
            await page.waitForLoadState('networkidle');
            await screenshot(page, '02-my-store-with-items', 'vendor');
        }
        
        await logout(page);
        
        // Book page (buying flow) - as customer
        console.log('\n=== BOOK/BUY PAGE ===');
        await login(page, users.customer.username, users.customer.password);
        
        await page.goto(`${BASE_URL}/marketplace.php`);
        await page.waitForLoadState('networkidle');
        
        // Find an item to buy
        const marketItemLink = await page.$('a[href*="item.php?uuid="]');
        if (marketItemLink) {
            await marketItemLink.click();
            await page.waitForLoadState('networkidle');
            await screenshot(page, '13-item-view-as-customer', 'customer');
        }
        
        // Book page
        await page.goto(`${BASE_URL}/book.php`);
        await page.waitForLoadState('networkidle');
        await screenshot(page, '14-book-page', 'customer');
        
        await logout(page);
        
        console.log('\n=== COMPLETE ===');
        
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
}

main().catch(console.error);
