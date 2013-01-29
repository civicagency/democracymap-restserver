## Overview

The DemocracyMap API aims to provide normalized structured data for all of the contact details and other primary information for every government body and government official that represents you. Currently this API is more of a meta-API that aggregates, normalizes, and caches other data sources including geospatial boundary queries, but ultimately it aims to help provide standardized geospatial queries and merge with similar efforts like those based on the Boundary Services API. 

More documentation can be found in `application/views/docs.php`

## Requirements

1. PHP 5.2 or greater
2. MySQL


## Codebase
This is a basic PHP app using the [CodeIgniter framework](http://www.codeigniter.com/) with [Phil Sturgeon's Rest Server](https://github.com/philsturgeon/codeigniter-restserver) for the API.


## Installation and Configuration

1. Copy **application/config/config.sample.php** to **application/config/config.php** and edit with the appropriate values.  
2. Copy **application/config/database.sample.php** to **application/config/database.php** and edit with the appropriate values for your MySQL connection. 
3. Create a local database and import the SQL database found in /sql/democracymap.sql into this local database. Fore example:
    a. `mysql> CREATE DATABASE democracymap;`
    b. `$> mysql -u root -p democracymap < democracymap.sql`
4. Copy **sample.htaccess** to **.htaccess**. In most cases you won't need to edit this file, but in some cases .htaccess configurations need to be tweaked for different environments. 



## Deploy

As a PHP app, there's nothing special needed for deployment. The files can be placed in an Apache virtual host directory just as any other PHP app would be. Other than setting up the right values in the config.php and database.php files there shouldn't be any other setup or deployment needed. You will need to ensure that Apache is set to accept the .htaccess file (eg 'AllowOverride All') and in some cases, you may need to make adjustments to your .htaccess, but in most cases the standard .htaccess file from CodeIgniter packaged here should work just fine. 