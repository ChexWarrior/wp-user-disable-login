const { test: base, expect } = require('@playwright/test');
const { UserAuth } = require('./models/userAuth.js');

exports.test = base.extend({
  userAuth: async ({ page }, use) => {
    const userAuthService = new UserAuth(page);
    await use(userAuthService);
  }
});

exports.expect = expect;
