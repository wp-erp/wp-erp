const { setHeadlessWhen } = require('@codeceptjs/configure');

// turn on headless mode when running with HEADLESS=true environment variable
// export HEADLESS=true && npx codeceptjs run
setHeadlessWhen(process.env.HEADLESS);

exports.config = {
  tests: 'core-tests/**/*_test.js',
  output: './output',
  helpers: {
    WebDriver: { 
     url: 'http://localhost:10018',
      browser: 'chrome',
      windowSize: "maximize",
      // smartWait: 5000,
      // waitForAction:3000,	
      //keepCookies: true,
      // desiredCapabilities: {
      //   chromeOptions: {
      //       args: ["--headless", "--disable-gpu", "--no-sandbox"]
      //   }
      // },
    }
  },
  include: {
    I: './steps_file.js',
    // helper: './pages/helper.js',
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
    },
    autoDelay: {
      enabled: true
    },
    autoLogin: {
      enabled: true,
      saveToFile: false,
      inject: 'loginAs',
      users: {
          admin: {
            login: (I) => {
              I.amOnPage('/login');
              I.fillField('Username', 'YourUsername');
              I.fillField('Password', secret('YourPassword'));
              I.checkOption('Remember Me');
	            I.click('Log In');
            },
            check: (I) => {
              I.seeCurrentUrlEquals('/wp-admin/');
            },
          },
          employee: {
            login: (I) => {
              I.amOnPage('/login');
              I.fillField('Username', 'Username');
              I.fillField('Password', secret('Password'));
              I.checkOption('Remember Me');
	            I.click('Log In');
            },
            check: (I) => {
              I.seeCurrentUrlEquals('/wp-admin/profile.php');
            },
          }
      },
    }
  }
}