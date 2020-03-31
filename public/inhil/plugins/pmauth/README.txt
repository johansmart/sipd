Pmauth plugin: simple authentication plugin for pmapper with siple ACL system
Is necessary to use a database, sqlite o postgresql
  
It contains:
- config.inc: include the other files in pmapper
- pmauth.css: styles authentication login page and form admin windows interface
- pmauth.js: main javascript plugin code 
- auth.phtml: main authentication login page
- auth.php: main authentication checkin script
- pmauth.phtml: 
- install subdirectory: files needed for installation / configuration

Dependancies:
- Pmapper UI fluid layout template
- PDO http://php.net/manual/en/book.pdo.php
- Postgresql (optional)

HOW TO USE
===========================================================

- Enable the plugin by adding a line in config_XXXXX.xml file:
<pmapper>
    <ini>
        <pmapper>
....
            <plugins>pmauth</plugins>
....
        </pmapper>
	</ini>
</pmapper>
-  Make sure enable optional/ui in the config section
<config>
....
    <pm_javascript_optional>optional/ui</pm_javascript_optional>
....
</config>


INSTALLATION
===========================================================

For SQLITE(defult):
---------------------

1) For Unix/Linux SO (for ubuntu in the example) install php sqlite binding:
- sudo apt-get install php5-sqlite

2) Change the host parameter in confing/config.ini file for your pmapper installation (absolute path required)

3) Make pmauth/db writeable by webserver user (for ubuntu):
- sudo chown -R pamuth www-data:www-data pmauth/db


For Postgresql(optional):
-------------------------

1) Create a Database

2) Run install/pg_install.sql script into database

3) Change parameters in config/config.ini file adding the following parameters
    host = your_db_host
    dbname = your_db_name
    username = your_db_username
    password = your_db_password


- Add the appropriate icons from plugins/pmauth/install/images  to images/menus

- (Only for italian uers) Add content of plugins/pmauth/install/locale/language_it.php at the end of incphp/locale/language_it.php file


OPTIONAL
===========================================================

The pmauth plugin works with mapselect plugins, if enabled pmauth show in mapselect list the config without pmauth enabled and  those user logged can see.


The authenticazion plugin just as two user-role:
- admin, this role can manage other users
- login, this role only can login to pmapper

After installation you can login with:
- username, admin
- password, admin


