const { UserAuth } = require('./userAuth.js');

/**
 * Represents the admin user list page
 */
class UserList extends UserAuth {
  constructor(page) {
    super(page);
    this.page = page;

    // Setup class properties
    this.applyBtn = null;
    this.bulkActionsSelect = null;
  }

  async gotoUserList() {
    await this.goto('/wp-admin/users.php');
    this.applyBtn = this.page.locator('input[value="Apply"]');
    this.bulkActionsSelect = this.page.locator('#bulk-action-selector-top');
  }
}

exports.UserList = UserList;
