const { UserProfile } = require('./models/userProfile.js');
const { test, expect } = require('./fixture.js');

// Test user information
const adminInfo = { username: 'admin', id: 1 };
const authorInfo = { username: 'author1', id: 2 };
const admin2Info = { username: 'admin2', id: 3 };
const author2Info = { username: 'author2', id: 4 };

test('Admins can disable and enable another user', async ({ userProfile }) => {
  await userProfile.login(adminInfo.username);
  await userProfile.disableUser(authorInfo.id);
  let checked = await userProfile.disabledCheckbox.isChecked();
  await expect(checked).toBeTruthy();
  await userProfile.enableUser(authorInfo.id);
  checked = await userProfile.disabledCheckbox.isChecked();
  await expect(checked).toBeFalsy();
});

test('Admins cannot be disabled', async({ userProfile }) => {
  await userProfile.login(adminInfo.username);
  await userProfile.gotoUserProfile(adminInfo.id);
  const exists = await userProfile.disabledCheckbox.count() > 0;

  await expect(exists).toBeFalsy();
});

test.only('A disabled user cannot login', async({ userProfile }) => {
  await userProfile.login(adminInfo.username);
  await userProfile.disableUser(authorInfo.id);
  await userProfile.logout();
  await userProfile.login(authorInfo.username);
  const messageDiv = userProfile.page.locator('#login_error');
  const errorMsg = await messageDiv.innerText();

  await expect(errorMsg.includes('User is disabled')).toBeTruthy();

  // Clean up
  await userProfile.login(adminInfo.username);
  await userProfile.enableUser(authorInfo.id);
});

test('Disabled users lose active login sessions', async ({ userProfile, otherUserProfile }) => {
  // Login as user who will be disabled
  await userProfile.login(authorInfo.username);

  // Login as admin via a new session and disable user
  await otherUserProfile.login(admin2Info.username);
  await otherUserProfile.disableUser(authorInfo.id);

  // Verify that disabled user is now logged out
  await userProfile.gotoUserProfile(authorInfo.id);
  const authorUrl = userProfile.page.url();

  expect(authorUrl.includes('wp-login.php')).toBeTruthy();

  // Clean up
  await otherUserProfile.enableUser(authorInfo.id);
});
