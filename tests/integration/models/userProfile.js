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
    this.userId = null;
  }

  async gotoUserProfile(userId) {
    this.userId = userId;
    await this.page.goto(`/wp-admin/user-edit.php?user_id=${userId}`);
    this.disabledCheckbox = this.page.locator('#disabled');
    this.updateButton = this.page.locator('#submit');
  }

  async disableUser(userId) {
    await this.gotoUserProfile(userId);
    await this.disabledCheckbox.check();
    await this.updateButton.click();
  }

  async enableUser(userId) {
    await this.gotoUserProfile(userId);
    await this.disabledCheckbox.setChecked(false);
    await this.updateButton.click();
  }
}

exports.UserProfile = UserProfile;
