const { setHeadlessWhen } = require('@codeceptjs/configure');

// turn on headless mode when running with HEADLESS=true environment variable
// export HEADLESS=true && npx codeceptjs run
setHeadlessWhen(process.env.HEADLESS);

exports.config = {
  tests: 'core-tests/**/*_test.js',
  output: './output',
  helpers: {
    WebDriver: { 
     url: 'https://demo.site/',
      browser: 'chrome',
      windowSize: "maximize",
      keepCookies: true,
      keepBrowserState: true,
      restart: false,
      // smartWait: 5000,
      // waitForAction:3000,	
      // keepCookies: true,
      // desiredCapabilities: {
      // chromeOptions: {
      // args: ["--headless", "--disable-gpu", "--no-sandbox"]
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
    allure: {
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
              I.fillField('Username', 'yourUserName');
              I.fillField('Password', secret('yourPassword'));
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
              I.fillField('Username', 'employeeUserName');
              I.fillField('Password', secret('employeePassword'));
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