const { expect } = require('@playwright/test');

class LoginPage {
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

  login() {
    this.page.goto('/wp-login.php');
    this.usernameInput.fill(this.username);
    this.passwordInput.fill(this.password);
    this.loginSubmit.click();
    const accountMenu = this.page.locator('#wp-admin-bar-my-account');
    expect(accountMenu).toBeVisible();
  }
}

exports.LoginPage = LoginPage;
