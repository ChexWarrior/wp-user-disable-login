const { UserAuth } = require('./models/userAuth.js');
const { test, expect } = require('./fixture.js');

const adminUsername = 'admin';
const authorUsername = 'author1';
const authorUserId = 2;
const adminUserId = 3;

test('Admins can disable and enable another user', async ({ userProfile }) => {
  await userProfile.login(adminUsername);
  await userProfile.disableUser(authorUserId);
  let checked = await userProfile.disabledCheckbox.isChecked();
  await expect(checked).toBeTruthy();
  await userProfile.enableUser(authorUserId);
  checked = await userProfile.disabledCheckbox.isChecked();
  await expect(checked).toBeFalsy();
});

test('Admins cannot be disabled', async({ userProfile }) => {
  await userProfile.login(adminUsername);
  await userProfile.gotoUserProfile(adminUserId);
  const exists = await userProfile.disabledCheckbox.count() > 0;

  await expect(exists).toBeFalsy();
});

test('A disabled user cannot login', async({ userProfile }) => {
  await userProfile.login(adminUsername);
  await userProfile.disableUser(authorUserId);
  await userProfile.logout();
  await userProfile.login(authorUsername);
  const messageDiv = userProfile.page.locator('#login_error');
  const errorMsg = await messageDiv.innerText();

  await expect(errorMsg.includes('User is disabled')).toBeTruthy();

  // Clean up
  await userProfile.login(adminUsername);
  await userProfile.enableUser(authorUserId);
});

// Test that a disabled user loses their current session

// Test that a disabled user who is enabled can login
