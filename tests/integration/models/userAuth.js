/**
 * Class for logging user into and out of WordPress site
 */
class UserAuth {
  /**
   * @param  {import('@playwright/test').Page} page
   */
  constructor(page) {
    this.page = page;
  }

  async login(username, password = 'password') {
    await this.page.goto('/wp-login.php');
    await this.page.locator('#user_login').fill(username);
    await this.page.locator('#user_pass').fill(password);
    await this.page.locator('#wp-submit').click();
  }

  async logout() {
    const logoutLink = this.page.locator('#wp-admin-bar-logout a.ab-item');
    const logoutUrl = await logoutLink.getAttribute('href');
    await this.page.goto(logoutUrl);
    const url = this.page.url();
  }
}

exports.UserAuth = UserAuth;
