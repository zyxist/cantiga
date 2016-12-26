Installation
============

Here you will learn how to install Cantiga.


------------
Requirements
------------

To install Cantiga you need:

 * any web server,
 * PHP 7.0 or newer,
 * MariaDB or MySQL database,
 * any mail server with mail account,
 * composer tool.

Required PHP extensions:

 * calendar
 * gd
 * intl
 * PDO
 * openssl

------------
Installation
------------

Download Cantiga from the project website or clone the Git repository::

    git clone https://github.com/zyxist/cantiga.git .

The ``web/`` directory must be the root directory for your webserver. Do not make accessible any other directories via the web browser.

The rest of the installation is done by Composer::

    composer install

Composer will download the necessary dependencies and ask you several questions about the configuration of the system, such as database and mail configuration.

The final step is installing the database structure::

    php bin/console cantiga:install:db --env=prod -i --type=mysql
    
Now you can run the web application in your browser. The login screen should appear. The default credentials are:

 * login: **administrator**
 * password: **Admin56789**
