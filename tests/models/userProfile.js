const { expect } = require('@playwright/test');
const { UserAuth } = require('./userAuth');

/**
 * Represents a user profile page where they can be enabled or disabled
 */
class UserProfile extends UserAuth {
  constructor(page) {
    super(page);
    this.page = page;

    // Setup class properties based on profile page inputs for later use
    this.disabledCheckbox = null;
    this.updateButton = null;
  }

  async gotoUserProfile(userId) {
    await this.page.goto(`/wp-admin/user-edit.php?user_id=${userId}`);
    this.disabledCheckbox = this.page.locator('#disabled');
    this.updateButton = this.page.locator('#submit');
  }
}

exports.UserProfile = UserProfile;
