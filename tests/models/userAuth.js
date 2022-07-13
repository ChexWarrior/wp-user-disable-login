const { expect } = require('@playwright/test');

/**
 * Class for logging user into and out of WordPress site
 */
class UserAuth {
  /**
   * @param  {import('@playwright/test').Page} page
   * @param {string} username
   * @param {string} password
   */
  constructor(page, username, password = 'password') {
    this.page = page;
    this.username = username;
    this.password = password;
    this.usernameInput = this.page.locator('#user_login');
    this.passwordInput = this.page.locator('#user_pass');
    this.loginSubmit = this.page.locator('#wp-submit');
  }

  async login() {
    await this.page.goto('/wp-login.php');
    this.usernameInput.fill(this.username);
    this.passwordInput.fill(this.password);
    this.loginSubmit.click();
    const accountMenu = this.page.locator('#wp-admin-bar-my-account');
    await expect(accountMenu).toBeVisible();
  }

  async logout() {
    const logoutLink = this.page.locator('#wp-admin-bar-logout');
    logoutLink.click();
    const url = this.page.url();
    await expect(/\?loggedout\=true/.test(url)).toBeTruthy();
  }
}

exports.UserAuth = UserAuth;
