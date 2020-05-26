# plugin-x-cart
How to update the X-Cart Plugin for ShipStation:

1. Downloaded XCart installation package https://kb.x-cart.com/general_setup/installation/installation_guide.html

2. Download MySQL and install it: https://www.mysql.com/downloads/

3. Set up a local web server, I found this article particularly helpful: https://www.maketecheasier.com/setup-local-web-server-all-platforms/

4. If you’re on a Mac, go into System Preferences> MySQL> and start the mysql server

5. While in the System Preferences> MySQL screen, hit Initialize Database to set up a password for the root user

6. Copy and Paste the updated plugin into the <Web Server directory>/xcart/classes/Module/ folder. Ensure that the folder structure of our plugin looks like /ShipStation/API/main.php

7. Go into the <xcart installation directory>/xcart/etc/config.php and set developer_mode to On

8. In the admin of your local installation, you should now be able to go into the System Tools > Cache Management and “start” the Re-deploy the store action

9. Now you should be able to go into “My Addons” and find ShipStation (based on the default apps, it was on page 3 for me)

10. It should now show the “pack it” link as shown in this xcart doc:  https://devs.x-cart.com/getting_started/creating-module.html#packing-up-your-module

11. When you click the Pack It link, a .tgz file should download.

12. Log into the vender account of xcart: https://my.x-cart.com/stores/3936

You should now be able to upload the module without errors!
