To use this script with a Synology router:

1. Save the script on your Synology device (e.g., as `/usr/syno/bin/ddns/porkbun.php`).
        scp porkbun.php admin_user@synology_hostname:/home/ 
        then ssh and mv it to final destination
2. Update attributes
```
        chown root /usr/syno/bin/ddns/porkbun.php
        chgrp root /usr/syno/bin/ddns/porkbun.php
        chmod 755  /usr/syno/bin/ddns/porkbun.php
```
3. Add an entry to the `/etc.defaults/ddns_provider.conf` file (you may need to create this file if it doesn't exist):
```
[Porkbun]
        modulepath=/usr/syno/bin/ddns/porkbun.php
        queryurl=Porkbun
```
4. In the Synology router's DDNS settings, you should now be able to select "Porkbun" as a provider and enter your API key as the username and your Secret key as the password.

5. Record will be updated or added if not exist 
