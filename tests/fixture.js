const { test: base, expect } = require('@playwright/test');
const { UserProfile } = require('./models/userProfile.js');

exports.test = base.extend({
  userProfile: async({ page }, use) => {
    const userProfile = new UserProfile(page);
    await use(userProfile);
  },
});

exports.expect = expect;
