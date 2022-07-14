const { test: base, expect } = require('@playwright/test');
const { UserList } = require('./models/userList.js');
const { UserProfile } = require('./models/userProfile.js');

exports.test = base.extend({
  userProfile: async({ page }, use) => {
    const userProfile = new UserProfile(page);
    await use(userProfile);
  },
  // User Profile using a new session from userProfile
  otherUserProfile: async({ browser }, use) => {
    const ctx = await browser.newContext();
    const newPage = await ctx.newPage();
    const otherUserProfile = new UserProfile(newPage);
    await use(otherUserProfile);
  },
  userList: async({ page }, use) => {
    const userList = new UserList(page);
    await use(userList);
  },
});

exports.expect = expect;
