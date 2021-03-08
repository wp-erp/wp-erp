const { setHeadlessWhen } = require('@codeceptjs/configure');

// turn on headless mode when running with HEADLESS=true environment variable
// export HEADLESS=true && npx codeceptjs run
setHeadlessWhen(process.env.HEADLESS);

exports.config = {
  tests: 'core-tests/**/*_test.js',
  output: './output',
  helpers: {
    WebDriver: {
      url: 'https://erpqa.ajaira.website',
      browser: 'chrome',
      windowSize: "maximize",
      smartWait: 5000,
      waitForAction:3000,	
      //keepCookies: true,
    }
  },
  include: {
    I: './steps_file.js'
  },
  bootstrap: null,
  mocha: {},
  name: 'e2e',
  plugins: {
    pauseOnFail: {},
    retryFailedStep: {
      enabled: true
    },
    tryTo: {
      enabled: true
    },
    screenshotOnFail: {
      enabled: true
    }
  }
}