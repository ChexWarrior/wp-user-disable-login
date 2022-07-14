/**
 * These tests check that the enable/disable user functionality
 * works as expected from the admin user list page when using the
 * Bulk Actions functionality
 */
const { test, expect } = require('./fixture.js');

// Test user information
const admin1Info = { username: 'admin1', id: 1 };
const author1Info = { username: 'author1', id: 2 };
const admin2Info = { username: 'admin2', id: 3 };
const author2Info = { username: 'author2', id: 4 };

// Test enabling and disabling a user via bulk actions
test.only('Users can be enabled and disabled via Bulk Actions', async ({ userList, userProfile }) => {
  await userList.login(admin1Info.username);
  await userList.disableUsers([author1Info.id, author2Info.id]);
  await userList.logout();

  // Try logging in as each disabled user
  await userList.login(author1Info.username);
  let messageDiv = userProfile.page.locator('#login_error');
  let errorMsg = await messageDiv.innerText();
  await expect(errorMsg.includes('User is disabled')).toBeTruthy();

  await userList.login(author2Info.username);
  messageDiv = userProfile.page.locator('#login_error');
  errorMsg = await messageDiv.innerText();
  await expect(errorMsg.includes('User is disabled')).toBeTruthy();

  await userList.login(admin1Info.username);
  await userList.enableUsers([author1Info.id, author2Info.id ]);
  await userList.logout();

  // Try logging in as enabled users
  await userList.login(author1Info.username);
  await expect(userList.page.url().includes('wp-admin')).toBeTruthy();
  await userList.logout();

  await userList.login(author2Info.username);
  await expect(userList.page.url().includes('wp-admin')).toBeTruthy();
  await userList.logout();
});

// Test that you cannot disable an admin
