# Electronics - Enterprise Resource Planning
## First of all: This is an early alpha release!
Do not use it in any production environment. It will change, and things will break. You have been warned.

# Why?

## There are already so many ERP systems out there. Some of them are entirely open-source. Why make a new one?

Most ERP systems are focused on finance and accounting. However, I did not find anything that would be suited for a middle-sized production or engineering company or even an advanced home user. 

The E-ERP project was started with the following requirements:

* Web-Base front end
* Parametric search and filter for components
* Stock tracking of individual packaging Units
* Barcode / QR-Code / RFID driven workflow
* Component order and cost tracking
* BOM and simple change mangement
* Inventory tracking

# Is E-Erp a real ERP System?
Maybe, someday. However, If you are mainly interested in accounting, then this is not for you.

# Technology
Vue.js in the front, PHP / MariaDB in the back.

## Used projects

The front end of E-ERP is based on the vue-element-admin project:
https://github.com/PanJiaChen/vue-element-admin

PHP-Barcode rendering is done using:
https://github.com/davidscotttufts/php-barcode

PHP Bon-Printer driver:
https://github.com/mike42/escpos-php

Swagger for API documentation (as an external tool not  part of the project):
https://github.com/swagger-api/swagger-editor


