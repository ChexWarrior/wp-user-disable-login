const { UserProfile } = require('./models/userProfile.js');
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

test('Disabled users lose active login sessions', async ({ userProfile, otherUserProfile }) => {
  // Login as user who will be disabled
  await userProfile.login(authorUsername);

  // Login as admin via a new session and disable user
  await otherUserProfile.login(adminUsername);
  await otherUserProfile.disableUser(authorUserId);

  // Verify that disabled user is now logged out
  await userProfile.gotoUserProfile(authorUserId);
  const authorUrl = userProfile.page.url();

  expect(authorUrl.includes('wp-login.php')).toBeTruthy();

  // Clean up
  await otherUserProfile.enableUser(authorUserId);
});

// Test that a disabled user who is enabled can login
