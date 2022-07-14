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
    await this.page.goto('/wp-admin/users.php');
    this.applyBtn = this.page.locator('input[id="doaction"]');
    this.bulkActionsSelect = this.page.locator('#bulk-action-selector-top');
  }

  async selectUsers(userIds) {
    for (const id of userIds) {
      await this.page.locator(`input[id="user_${id}"]`).check();
    };
  }

  async disableUsers(userIds) {
    await this.gotoUserList();
    await this.selectUsers(userIds);
    await this.bulkActionsSelect.selectOption('disable_user');
    await this.applyBtn.click();
  }

  async enableUsers(userIds) {
    await this.gotoUserList();
    await this.selectUsers(userIds);
    await this.bulkActionsSelect.selectOption('enable_user');
    await this.applyBtn.click();
  }
}

exports.UserList = UserList;
