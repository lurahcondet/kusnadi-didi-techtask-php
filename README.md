
# Simple Recipes App

Simple web app to display recipes and return as json response based on availability of ingredients

@version     1.0.0, last modified April 2020

@author      Didi Kusnadi <jalapro08@gmail.com>

---

## System Requirements

1. Linux OS

2. Composer, see installation instruction here https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos

3. Docker, see installation instruction here:

	https://docs.docker.com/engine/install/ubuntu/

	https://docs.docker.com/engine/install/linux-postinstall/

	https://docs.docker.com/compose/install/ 


## How To Install

1. Clone this repo your machine

2. Run installation command:

	`sh ./install.sh`


## How To Run Application

Please make sure your installation complete. Open your browser and enter url http://simple-recipe.web:8080/lunch or http://simple-recipe.web:8080/lunch/{requested-date}. Allowed format for `requested-date` param is **Y-m-d**. Current date will be used if `requested-date` is empty.

Check container if application not running by typing command:

	`docker ps`

Make sure container `mysite` in the list. If not run container by typing command:

	`docker run -p 8080:80 -d mysite`


## How To Run Code Inspection

1. Run unit testing

	`php bin/phpunit`

2. Run code sniffer

	`php vendor/squizlabs/php_codesniffer/bin/phpcs --standard=PSR1 src/`

	`php vendor/squizlabs/php_codesniffer/bin/phpcs --standard=PSR2 src/`

3. Run PHP Mess Detector

	`vendor/phpmd/phpmd/src/bin/phpmd src/ text codesize,unusedcode,naming,controversial,design`


##### Version 1.0

Initial Release