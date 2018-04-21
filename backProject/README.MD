# Performance Food CRM Backend

Core of Internal CRM

# Getting Started
==================
##If something wont install of you got strange errors try to use SUDO
This solution solve 99% of problems

### Prerequisites

Technologies and Tools:
What things you need to install the software and how to install them

```
mysql 5.7
php7.1
composer
xdebug
apache2.4
node 9.8
npm 5.6
rabbitmq (zmq)
```
==================
##1.PHP
```
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php7.1-opcache php7.1-zip php7.1-xmlrpc php7.1-xsl php7.1-mbstring
php7.1-mcrypt php7.1-mysql php7.1-odbc php7.1-pgsql php7.1-pspell php7.1-readline php7.1-recode php7.1-snmp php7.1-soap php7.1-sqlite3 php7.1-sybase php7.1-tidy php7.1-xml php7.1-json php7.1-ldap php7.1-intl php7.1-interbase php7.1-imap php7.1-gmp php7.1-gd php7.1-enchant php7.1-dba php7.1-bz2 php7.1-bcmath php7.1-phpdbg php7.1-cgi php7.1-cli php7.1-fpm php7.1-dev php7.1
```
check it 
```php -v```
output
```
PHP 7.2.3-1+ubuntu16.04.1+deb.sury.org+1 (cli) (built: Mar  6 2018 11:18:25) ( NTS )
Copyright (c) 1997-2018 The PHP Group
Zend Engine v3.2.0, Copyright (c) 1998-2018 Zend Technologies
    with Xdebug v2.6.0, Copyright (c) 2002-2018, by Derick Rethans
    with Zend OPcache v7.2.3-1+ubuntu16.04.1+deb.sury.org+1, Copyright (c) 1999-2018, by Zend Technologies
```

#####if you see this, go to the next item

==================
##2.Composer
Save this script as file named composer-install.sh or use [Xdebug docs](https://xdebug.org/docs/install)
```
#!/bin/sh

EXPECTED_SIGNATURE=$(wget -q -O - https://composer.github.io/installer.sig)
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
ACTUAL_SIGNATURE=$(php -r "echo hash_file('SHA384', 'composer-setup.php');")

if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ]
then
    >&2 echo 'ERROR: Invalid installer signature'
    rm composer-setup.php
    exit 1
fi

php composer-setup.php --quiet
RESULT=$?
rm composer-setup.php
exit $RESULT
```
set permissions
```
sudo chmod a+x composer-install.sh
```
and run it

Check it
```composer -V```
output
```
Composer version 1.6.2 2018-01-05 15:28:41
```
#####if you see this, go to the next item
==================
##3.Mysql
```
sudo apt-get update
sudo apt-get install mysql-server
```

check it
```
mysql -V (and see mysql Ver 14.14 Distrib 5.7.21, for Linux (x86_64) using EditLine wrapper) or later
```
and service running
```
systemctl status mysql.service

● mysql.service - MySQL Community Server
   Loaded: loaded (/lib/systemd/system/mysql.service; enabled; vendor preset: en
   Active: active (running) since Чт 2018-03-08 11:23:08 MSK; 5h 41min ago
 Main PID: 1926 (mysqld)
    Tasks: 32
   Memory: 217.6M
      CPU: 10.089s
   CGroup: /system.slice/mysql.service
           └─1926 /usr/sbin/mysqld

мар 08 11:23:07 lp systemd[1]: Starting MySQL Community Server...
мар 08 11:23:08 lp systemd[1]: Started MySQL Community Server.
```
#####if you see this, evethis ok, go to the next item

To connect application to localhost Mysql you need overwrite `$config` in `/app/Database.php`

------------------

##4.Nodejs
```
curl -sL https://deb.nodesource.com/setup_9.x | sudo -E bash -
sudo apt-get install -y nodejs
```
check it
```node -v```
(and see v9.8.0) or later
```npm -v``` 
(and see v5.6.0) or later

------------------

##5.Hosts setup
add line to /etc/hosts
```
127.0.0.1	crmserver.loc
``` 

------------------

##6.Apache2
Install apache
```sudo apt-get install apache2```

disable default apache host (important) 
```sudo a2dissite 000-default.conf```

create file for new host /etc/apache2/sites/available/crmserver.loc.conf
```
<VirtualHost *:8888>
        #ServerAdmin webmaster@localhost
	ServerName crmserver.loc
        DocumentRoot /var/www/html/crmserver
    <Directory /var/www/html/crmserver>
        #Разрешение на перезапись всех директив при помощи .htaccess
        AllowOverride All
    </Directory>
</VirtualHost>
```

enable rewriting module
```sudo a2enmod rewrite```

enable this host
```sudo a2ensite crmserver.loc.conf```

restart apache sercive
```sudo service apache2 restart```
```sudo apacheclt restart```

------------------
#Zeromq
##Save this script as file named zeromq.sh
```
# run in sudo
# Before installing, make sure you have installed all the needed packages
sudo apt-get install libtool pkg-config build-essential autoconf automake
sudo apt-get install libzmq-dev

# Install libsodium
git clone git://github.com/jedisct1/libsodium.git
cd libsodium
./autogen.sh
./configure && make check
sudo make install
sudo ldconfig

cd /opt

# Install zeromq
# latest version as of this post is 4.1.2
wget http://download.zeromq.org/zeromq-4.1.2.tar.gz
tar -xvf zeromq-4.1.2.tar.gz
cd zeromq-4.1.2
./configure
make
sudo make install

sudo apt-get install php7.1-dev php-pear
sudo pecl install zmq-beta
```
set permissions
```
sudo chmod a+x zeromq.sh
```
and run it
------------------

##Connecting xdebug remote debug to Apache2 as server througth PHPstorm


------------------
Configure apache at first
Find file /eta/apache2/ports.conf and add this line
```
Listen 8888
```

Find file /eta/apache2/apache2.conf and add this section

change path to the project before adding!
```
<Directory /var/www/html/crmserver/>
	Options Indexes FollowSymLinks
	AllowOverride All
	Require all granted
</Directory>
```

add to `/etc/php/7.1/apache2/php.ini` and `/etc/php/7.1/apache2/php.ini` 
```
	zend_extension="/usr/lib/php/20160303/xdebug.so"
  xdebug.remote_autostart=on
  xdebug.profiler_enable = 0
  xdebug.remote_enable=1
  xdebug.remote_handler=dbgp 
  xdebug.remote_mode=req
  xdebug.remote_host=127.0.0.1
  xdebug.remote_port=9000
  xdebug.idekey="PHPSTORM"
```


##Configuring PHPstorm
1. Run PHPstorm
2. And then create running congiguration
3. Open Run/Debug Configurations
4. Click Add (+) , choose PHP Remote Debug 
5. Create server in Servers, and set ide key(session id): PHPSTORM
use folowing variables
```
	Name: CRM-SERVER
	Host: http://crmserver.loc
	Port: 8888
	Debugger: Xdebug
```

6. Install [xdebug-helper](https://chrome.google.com/webstore/detail/xdebug-helper/eadndfjplgieldjbigjakmdgkmoaaaoc/)  chrome extension
7. Go to Setting and set IDE key to PHPSTORM
8. Open http://crmserver.loc
9. Press "Listening debug for PHP connections"  and Debug key
10. Try to login in CRM frontend
11. Debug It
12. ???
13. PROFIT


# Note: Application port mapping 
1. :8080 - SPA frontend
2. :8888 - backend
3. :3306 - SQL Server
3. :9000 - Xdebug port
4. :5000 - Project Documentation

# Run Order
1. Run Frontend
2. Run Apache
3. Run push-server
4. Copy .env.example to .env
5. Write code

#composer scripts
`
composer phpdoc
` - build project documentation 

`
composer serve-doc
` - serve project documentation 

`
composer phpcs
`  - check project coding style

`
composer phpcbf
`  - fix project coding style to --@PSR2

## Authors
* **Vladimir Pavlov** - project creator
* **Daniil Gorelov** - backend developer