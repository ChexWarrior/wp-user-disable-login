/**
 * These tests check that enable/disable user functionality
 * works as expected when disabling/enabling a user from their
 * profile page
 */
const { test, expect } = require('./fixture.js');

// Test user information
const admin1Info = { username: 'admin1', id: 1 };
const author1Info = { username: 'author1', id: 2 };
const admin2Info = { username: 'admin2', id: 3 };
const author2Info = { username: 'author2', id: 4 };

const appPassword = process.env.APP_PASSWORD;

test('Admins can disable and enable another user', async ({ userProfile }) => {
  await userProfile.login(admin1Info.username);
  await userProfile.disableUser(author1Info.id);
  let checked = await userProfile.disabledCheckbox.isChecked();
  await expect(checked).toBeTruthy();
  await userProfile.enableUser(author1Info.id);
  checked = await userProfile.disabledCheckbox.isChecked();
  await expect(checked).toBeFalsy();
});

test('Admins cannot be disabled', async({ userProfile }) => {
  await userProfile.login(admin1Info.username);
  await userProfile.gotoUserProfile(admin1Info.id);
  const exists = await userProfile.disabledCheckbox.count() > 0;

  await expect(exists).toBeFalsy();
});

test('Non-admins cannot disable users', async ({ userProfile }) => {
  await userProfile.login(author1Info.username);
  await userProfile.gotoUserProfile(author2Info.id);

  const exists = await userProfile.disabledCheckbox.count() > 0;
  await expect(exists).toBeFalsy();
});

test('A disabled user cannot login', async({ userProfile }) => {
  await userProfile.login(admin1Info.username);
  await userProfile.disableUser(author1Info.id);
  await userProfile.logout();
  await userProfile.login(author1Info.username);
  const messageDiv = userProfile.page.locator('#login_error');
  const errorMsg = await messageDiv.innerText();

  await expect(errorMsg.includes('User is disabled')).toBeTruthy();

  // Clean up
  await userProfile.login(admin1Info.username);
  await userProfile.enableUser(author1Info.id);
});

test("A disabled user's app passwords cannot be used with the WP API", async ({ userProfile, request }) => {
  await userProfile.login(admin1Info.username);
  await userProfile.disableUser(author1Info.id);
  const auth = `${author1Info.username}:${appPassword}`;

  const response = await request.get('/wp-json/wp/v2/users', {
    'headers': {
      'Authorization': `Basic ${btoa(auth)}`
    }
  });

  const body = await response.json();
  expect(body).toHaveProperty('code');
  expect(body.code).toStrictEqual('user_disabled');

  // Clean up
  await userProfile.login(admin1Info.username);
  await userProfile.enableUser(author1Info.id);
});

test('Disabled users lose active login sessions', async ({ userProfile, otherUserProfile }) => {
  // Login as user who will be disabled
  await userProfile.login(author1Info.username);

  // Login as admin via a new session and disable user
  await otherUserProfile.login(admin2Info.username);
  await otherUserProfile.disableUser(author1Info.id);

  // Verify that disabled user is now logged out
  await userProfile.gotoUserProfile(author1Info.id);
  const authorUrl = userProfile.page.url();

  expect(authorUrl.includes('wp-login.php')).toBeTruthy();

  // Clean up
  await otherUserProfile.enableUser(author1Info.id);
});
