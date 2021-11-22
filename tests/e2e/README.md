# WPERP END 2 END Testing by using CodeceptJS and WebDriver

## Step 1
### JAVA Installation 
Install Java through [Download](https://java.com/en/download/)

> Click  **Agree and Start Free download and install java globally**
## Step 2
### Selenium Server Configuration
Configure selenium server by following the steps!
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

Create a **test folder** and under this run :

```
git clone https://github.com/wp-erp/wp-erp.git
```
```
cd wp-erp
```
Switch branch :
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
 
> Open test folder on visual studio or your favourite Code Editor!

 - Open **`example.codecept.conf.js`** file.
 - Provide your test Url at  line `12` [Must be a complete url eg: https://rinky.test]
 - Provide your test site Username at line `61` [YourUsername]
 - Give your test site Password at line `62` [YourPassword]
 
 
> Then **Delete** **`example`**  from **`example.codecept.conf.js`** and hit save! 

Back to terminal! Run following steps!
```
cd tests/e2e
```

>Your root will seem like :  `wp-erp/tests/e2e `

```
npm install
```

## Step 5

## Running Tests!

**`Here you go !`**
>**Run all the test cases in single command!**
>**`npx codeceptjs run --grep "\@HRM|\@CRM|\@Accounting" `**


>For **HRM** module run:
**`npx codeceptjs run --grep @01_HRM`** 

> For **CRM** module run:
**`npx codeceptjs run --grep @02_CRM`** 

>For **Accounting** module run:
**`npx codeceptjs run --grep @03_Accounting`** 

## Generating Allure Report!

Run:

**`npx codeceptjs run --grep "\@HRM|\@CRM|\@Accounting" --plugins allure`**

**`allure serve output`**

![Allure_02.png](https://i.postimg.cc/SxHF8xLL/graph.png)
## Scenario Dependencies

Some scenarios may require some prerequisite in order to validate Such as:

1. Financial year, Balance Equity in opening balance in accounting should be set as per requirement.
    
2. As we have used ``faker.js`` to implement and generate random date's.If there is already taken same date, ``Leave`` extension may show fail cases in this kind of scenarios.