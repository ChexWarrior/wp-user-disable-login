const { UserAuth } = require('./models/userAuth.js');
const { test, expect } = require('./fixture.js');

const adminUsername = 'admin';
const authorUserId = 2;

// Test that a disabled user cannot login

// Test that a disabled user loses their current session

// Test that a disabled user who is enabled can login

// Test that admins cannot be disabled

test('Admins can disable and enable another user', async ({ userProfile, page }) => {
  await userProfile.login(adminUsername);
  await userProfile.disableUser(authorUserId);
  let checked = await userProfile.disabledCheckbox.isChecked();
  await expect(checked).toBeTruthy();
  await userProfile.enableUser(authorUserId);
  checked = await userProfile.disabledCheckbox.isChecked();
  await expect(checked).toBeFalsy();
});


