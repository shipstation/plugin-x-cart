# plugin-x-cart 5.4 and lower
How to update the X-Cart Plugin for ShipStation:

1. X-Cart Installation guide: https://kb.x-cart.com/general_setup/installation/installation_guide.html
    - Link to download X-Cart: https://raw.githubusercontent.com/xcart/jobs/master/assets/x-cart-downloadable.tgz

2. Download MySQL and install it: https://www.mysql.com/downloads/

3. Set up a local web server, I found this article particularly helpful: https://www.maketecheasier.com/setup-local-web-server-all-platforms/

4. If you’re on a Mac, go into System Preferences> MySQL> and start the mysql server

5. While in the System Preferences> MySQL screen, hit Initialize Database to set up a password for the root user

6. Copy and Paste the updated plugin into the <Web Server directory>/xcart/classes/Module/ folder. Ensure that the folder structure of our plugin looks like /ShipStation/API/main.php

7. Go into the <xcart installation directory>/xcart/etc/config.php and set developer_mode to On

8. In the admin of your local installation, you should now be able to go into the System Tools > Cache Management and “start” the Re-deploy the store action

9. Now you should be able to go into “My Addons” and find ShipStation (based on the default apps, it was on page 3 for me)

10. It should now show the “pack it” link as shown in this X-Cart doc:  https://devs.x-cart.com/getting_started/creating-module.html#packing-up-your-module
    - You might need to switch to "List View" to see the "pack it" icon

11. When you click the Pack It link, a .tgz file should download.

12. Log into the vender account of X-Cart: https://my.x-cart.com/stores/3936

You should now be able to upload the module without errors!

# plugin-x-cart 5.5 and higher
How to set up xcart 5.5 locally (mac) and update x-cart plugin for ShipStation

0. X-Cart Installation Guide: https://developer.x-cart.com/getting_started/requirements

1. Download X-Cart 5.5.x.x: https://market.x-cart.com/admin.php?target=dev_info

2. Download and install MySQL: https://www.mysql.com/downloads/

3. Go into System Preferences> MySQL > and start the mysql server

4. While in the System Preferences> MySQL screen, hit Initialize Database to set up a password for the root user

5. Download and install php@8.0: `brew install php@8.0`

6. Start php@8.0: `brew services start php@8.0`

7. Install httpd: `brew install httpd`

8. Edit apache.conf

```
## X-Cart 5 apache 2.4 sample configuration
#
# Replace placeholders:
# {{public-full-path}} - real full path of public dir
#
# Example
#
# Xcart installation path: /var/www/xcart
# Expected URL: https://localhost/
#
# public-full-path: /var/www/xcart
#

LoadModule proxy_module lib/httpd/modules/mod_proxy.so
LoadModule proxy_fcgi_module lib/httpd/modules/mod_proxy_fcgi.so
LoadModule rewrite_module lib/httpd/modules/mod_rewrite.so

ServerName xcart.test

<VirtualHost *:80>
    ServerAdmin admin@example.com

    DocumentRoot "/var/www/xcart/public"

    <Directory "/var/www/xcart/public">
        DirectoryIndex index.php

        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
        FallbackResource /index.php

        <IfModule mod_rewrite.c>
            Options -MultiViews

            RewriteEngine On

            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteRule ^ index.php [QSA,L]
        </IfModule>

    </Directory>

    <FilesMatch ".php$">
        SetHandler "proxy:fcgi://127.0.0.1:9000"
    </FilesMatch>

</VirtualHost>
```

9. Edit your `/etc/hosts` file to include: `127.0.0.1 xcart.test`

10. Restart httpd: `brew services restart httpd`

11. cd into your xcart installation folder and edit `.env` to have values:
    -   ```
        DATABASE_URL="//root:password@127.0.0.1:3306/xcart?serverVersion=5.7"
        ```
    -   ```
        XCART_HOST_DETAILS_HTTP_HOST=xcart.test
        XCART_HOST_DETAILS_HTTPS_HOST=xcart.test
        XCART_HOST_DETAILS_ADMIN_HOST=xcart.test
        ```

12. Install X-Cart 5.5.x.x: `./bin/install.sh -a xuser@auctane.com:password` (this user will be used to access admin page)

13. Access `http://xcart.test/admin/` and log into the admin page.

14. Copy the ShipStation folder to these directories
    - <xcart installation directory>/xcart/modules
    - <xcart installation directory>/xcart/var/packs/xcart/modules

15. Rebuild X-Cart: `./bin/service xcst:rebuild --enable ShipStation-Api`

16. Pack the module: `./bin/service xcst:pack-module --source=git --modules=ShipStation-Api`

17. Go to `https://market.x-cart.com/admin.php?target=product&product_id=3634&page=module_versions` and upload the module in the `XC5 Module Files` tab