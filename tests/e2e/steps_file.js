// in this file you can append custom step methods to 'I' object
var Factory= require('rosie');
var faker =require('faker');
module.exports = function() {
return actor({
 checkError: function () {
  this.dontSee('Warning');
  this.dontSee('Fatal error');
  this.dontSee('Notice:');
},


// Define custom steps here, use 'this' to access default methods of I.
// It is recommended to place a general 'login' function here.

});

}
