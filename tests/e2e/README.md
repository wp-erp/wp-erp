# CodeceptJS END 2 END Testing #

## Step 1
### JAVA Installation
```
Go: https://java.com/en/download/
```
```
Click 'Agree and Start Free download' and install java globally
```
## Step 2
### Selenium Server Configuration

```
npm install selenium-standalone@latest -g
```
```
selenium-standalone install
```
```
selenium-standalone start
```

## Step 3
### Getting started

Create a test folder and under this run :

```
git clone https://github.com/wp-erp/wp-erp.git
```
```
cd wp-erp
```
Switch branch 
```
git checkout test/automation
```
```
composer install
```
```
composer dumpautoload -o
```

## Step 4
## Configuration
 
Open that test folder on visual studio code or your favourite Code Editor!

Create a file named codecept.conf.js & copy code from example.codecept.conf.js

In codecept.conf.js file 
Give your test Url at line `12` [Must be a complete url eg:https://rinky.test]
Give your test site Username at line `54` [YourUsername]
Give your test site Password at line `55` [YourPassword]
Then rename example.codecept.conf.js file as codecept.conf.js

```
cd tests/e2e
```

Your root will seem like : wp-erp/tests/e2e 

```
npm install
```

## Step 5

## Running Test

Here you go !

For HRM module run the following command:
`npx codeceptjs run tests/e2e/HRM/employee_01_addEmployee_test.js` [Copy any scenario you want to test under HRM module]

For CRM module run the following command:
`npx codeceptjs run core-tests/CRM/contact_01_CreateContact_test.js` [Copy any scenario you want to test under CRM module]

For Accounting module run the following command:
`npx codeceptjs run core-tests/Accounting/Customer_01_addCustomer_test.js` [Copy any scenario you want to test under Accounting module]

Run Test Suit:
`npx codeceptjs run --grep @Leave`  [Run multiple test cases at a time by using --grep function]


   
