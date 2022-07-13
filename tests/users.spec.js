const { UserAuth } = require('./models/userAuth.js');
const { test, expect } = require('./fixture.js');

// Test that a disabled user cannot login

// Test that a disabled user loses their current session

// Test that a disabled user who is enabled can login

// Test that admins cannot be disabled

// Test that only admins can disable a user
test('Only Admins can disable another user', async ({ userProfile, page }) => {
  await userProfile.login('admin');

  // Go to author user profile page
  await userProfile.gotoUserProfile(2);
  await userProfile.disabledCheckbox.check();
  await userProfile.updateButton.click();
  const checked = await userProfile.disabledCheckbox.isChecked();
  const message = await userProfile.page.locator('#message').innerText();

  await expect(message.includes('User updated')).toBeTruthy();
  await expect(checked).toBeTruthy();
});

// Test that only admins can enable a user
