sainsbury console application

Web server setup
----------------

### PHP CLI server

cd /sainsbury/public folder

php index.php user generatejson
php index.php user generatejson  -v (for verbose)

open hosts file and make an entry
sudo nano /etc/hosts

127.0.0.1 sainsburys.local


### Apache setup

To setup apache, setup a virtual host to point to the public/ directory of the
project and you should be ready to go! It should look something like below:

  <VirtualHost *:80>
    ServerName sainsburys.local
    DocumentRoot path-to/sainsburys/public
    SetEnv APPLICATION_ENV "development"
    <Directory  path-to/sainsburys/public>
        DirectoryIndex index.php
        AllowOverride All
        Order allow,deny
        Allow from all
    </Directory>
</VirtualHost>


