const { test, expect } = require('@playwright/test');

test('basic test', async({ page }) => {
  await page.goto('https://user-disable-test.ddev.site:8443');
  const title = page.locator('h1.wp-block-site-title');
  await expect(title).toHaveText('Test');
});
