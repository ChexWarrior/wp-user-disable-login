const config = {
  use: {
    viewport: { width: 1280, height: 720 },
    ignoreHTTPSErrors: true,
    screenshot: 'only-on-failure',
    baseURL: 'https://user-disable-test.ddev.site:8443',
    testMatch: 'tests/*.spec.js',
  }
};

module.exports = config;
