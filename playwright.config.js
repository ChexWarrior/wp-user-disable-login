const config = {
  use: {
    viewport: { width: 1280, height: 720 },
    ignoreHTTPSErrors: true,
    screenshot: 'only-on-failure',
    baseURL: process.env.BASE_URL,
    testMatch: 'tests/*.spec.js',
    maxFailures: 1,
  }
};

module.exports = config;
