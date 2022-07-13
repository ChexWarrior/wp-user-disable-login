const { UserAuth } = require('./models/userAuth.js');
const { test, expect } = require('./fixture.js');

// Test that a disabled user cannot login


// Test that a disabled user loses their current session

// Test that a disabled user who is enabled can login

// Test that admins cannot be disabled

test('basic test', async({ userAuth, page }) => {
  await userAuth.login('admin');
  await userAuth.logout();
});
