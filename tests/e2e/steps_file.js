// in this file you can append custom step methods to 'I' object
var Factory= require('rosie');
var faker =require('faker');
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
 },
 checkError: function () {
  this.dontSee('Warning');
  this.dontSee('Fatal error');
  this.dontSee('Notice:');
},
previewUsers: function () {
  this.amOnPage('/wp-admin/admin.php?page=erp');
  this.amOnPage('/wp-admin/admin.php?page=erp-accounting#/');
  this.wait(5);
  this.moveCursorTo('//div[2]/div/div[2]/div/div/ul/li[2]/a');
},
previewProducts: function () {
  this.amOnPage('/wp-admin/admin.php?page=erp');
  this.amOnPage('/wp-admin/admin.php?page=erp-accounting#/');
  this.wait(5);
  this.moveCursorTo('//div[2]/div/div[2]/div/div/ul/li[6]/a');   
},
previewTransactions: function () {
  this.amOnPage('/wp-admin/admin.php?page=erp');
  this.amOnPage('/wp-admin/admin.php?page=erp-accounting#/');
  this.wait(5);
  this.moveCursorTo('//div[2]/div/div[2]/div/div/ul/li[3]/a');
}



// Define custom steps here, use 'this' to access default methods of I.
// It is recommended to place a general 'login' function here.

});

}
