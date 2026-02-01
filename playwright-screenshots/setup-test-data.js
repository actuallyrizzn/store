/**
 * Setup test data for Clawed Road screenshots
 * Creates items, packages, transactions, support tickets, etc.
 */

const { chromium } = require('playwright');

const BASE_URL = 'http://localhost:8000';

const users = {
    admin: { username: 'admin', password: 'admin123' },
    staff: { username: 'staffuser', password: 'staffuser123' },
    vendor: { username: 'vendoruser', password: 'vendoruser123' },
    customer: { username: 'testcustomer', password: 'testcustomer123' }
};

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

async function getCsrfToken(page) {
    const csrfInput = await page.$('input[name="csrf_token"]');
    if (csrfInput) {
        return await csrfInput.getAttribute('value');
    }
    return '';
}

async function getStoreUuid(page) {
    const myStoreLink = await page.$('a[href*="store.php?uuid="]');
    if (myStoreLink) {
        const href = await myStoreLink.getAttribute('href');
        const match = href.match(/uuid=([^&]+)/);
        if (match) {
            return match[1];
        }
    }
    return null;
}

async function main() {
    console.log('Setting up comprehensive test data...\n');
    
    const browser = await chromium.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    const context = await browser.newContext();
    const page = await context.newPage();
    
    try {
        // Login as vendor to create items
        console.log('1. Logging in as vendor...');
        await login(page, users.vendor.username, users.vendor.password);
        
        // Get store UUID
        await page.goto(`${BASE_URL}/marketplace.php`);
        const storeUuid = await getStoreUuid(page);
        
        if (!storeUuid) {
            console.log('   No store found. Please run setup first.');
            await browser.close();
            return;
        }
        console.log(`   Store UUID: ${storeUuid}`);
        
        // Create items via API
        console.log('2. Creating test items via API...');
        
        const items = [
            { name: 'Digital Art Package', description: 'A collection of digital art assets for your projects. Includes PSD files, PNG exports, and vector graphics.' },
            { name: 'Web Development Course', description: 'Complete course on modern web development including HTML5, CSS3, JavaScript, React, and Node.js.' },
            { name: 'Music Production Pack', description: 'Professional music production samples, loops, and presets for your next project.' },
            { name: 'Photography Lightroom Presets', description: 'Curated collection of 50+ Lightroom presets for portrait, landscape, and street photography.' },
            { name: 'E-commerce Template', description: 'Fully responsive e-commerce website template with shopping cart integration.' }
        ];
        
        // Get cookies for API request
        const cookies = await context.cookies();
        const cookieHeader = cookies.map(c => `${c.name}=${c.value}`).join('; ');
        
        for (const item of items) {
            // Use fetch-like approach via page.evaluate
            const result = await page.evaluate(async ({ item, storeUuid, baseUrl }) => {
                const formData = new FormData();
                formData.append('name', item.name);
                formData.append('description', item.description);
                formData.append('store_uuid', storeUuid);
                
                const response = await fetch(`${baseUrl}/api/items.php`, {
                    method: 'POST',
                    body: formData,
                    credentials: 'include'
                });
                return response.json();
            }, { item, storeUuid, baseUrl: BASE_URL });
            
            if (result.ok) {
                console.log(`   Created item: ${item.name} (${result.uuid})`);
            } else {
                console.log(`   Failed to create item: ${item.name}`, result);
            }
        }
        
        // Create a support ticket as customer
        console.log('3. Creating support ticket as customer...');
        await logout(page);
        await login(page, users.customer.username, users.customer.password);
        
        await page.goto(`${BASE_URL}/support/new.php`);
        await page.waitForLoadState('networkidle');
        
        const subjectInput = await page.$('input[name="subject"]');
        if (subjectInput) {
            await page.fill('input[name="subject"]', 'Question about purchasing');
            const messageInput = await page.$('textarea[name="message"]');
            if (messageInput) {
                await page.fill('textarea[name="message"]', 'I have a question about the checkout process. How do I complete a purchase using cryptocurrency?');
            }
            const submitBtn = await page.$('button[type="submit"]');
            if (submitBtn) {
                await submitBtn.click();
                await page.waitForLoadState('networkidle');
                console.log('   Support ticket created');
            }
        } else {
            console.log('   Support ticket form not found');
        }
        
        // Create a message to the vendor
        console.log('4. Setting up complete...');
        
        await logout(page);
        console.log('\nTest data setup complete!');
        
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
}

main().catch(console.error);
