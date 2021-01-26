// in this file you can append custom step methods to 'I' object

module.exports = function() {
  return actor({
    loginAsAdmin: function() {
     this.amOnPage('https://erpqa.ajaira.website/wp-admin')
     this.fillField('Username or Email Address','mehedi')
     this.fillField('Password','hoe)6ULjBvW8*C2P#T')
     this.checkOption('Remember Me')
     this.click('Log In')
     this.see('WP ERP')
    },
    loginAsEmployee: function() {
      this.amOnPage('https://erpqa.ajaira.website/wp-admin')
      this.fillField('Username or Email Address','rinkychowdhury@wedevs.com')
      this.fillField('Password','rinkychowdhury')
      this.checkOption('Remember Me')
      this.click('Log In')
      this.see('Profile')
     }
    
    

    // Define custom steps here, use 'this' to access default methods of I.
    // It is recommended to place a general 'login' function here.

  });

}
