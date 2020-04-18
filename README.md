
# Simple Recipes App

Simple web app to display recipes and return as json response based on availability of ingredients

@version     1.0.0, last modified April 2020

@author      Didi Kusnadi <jalapro08@gmail.com>

---

## How To Install
1. Clone this repo
2. Update dependecies components by running command:

	`composer update`

3. Run unit testing

	`php bin/phpunit`

4. Run code sniffer

	`php vendor/squizlabs/php_codesniffer/bin/phpcs --standard=PSR1 src/`

	`php vendor/squizlabs/php_codesniffer/bin/phpcs --standard=PSR2 src/`

4. Run PHP Mess Detector

	`vendor/phpmd/phpmd/src/bin/phpmd src/ text codesize,unusedcode,naming,controversial,design`

3. docker-compose build
4. docker-compose up -d

## How To Use API

Call API by visit url http://your-domain.com/lunch or http://your-domain.com/{requested-date}

Allowed format for `requested-date` param is **Y-m-d**. Current date will be used if `requested-date` is empty.


##### Version 1.0

Initial Release