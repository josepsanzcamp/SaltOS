#!/bin/bash

rm -rf jquery-ui.custom.blue jquery.mobile.custom.blue
rm -rf jquery-ui.custom.facebook jquery.mobile.custom.facebook
rm -rf jquery-ui.custom.gray jquery.mobile.custom.gray
rm -rf jquery-ui.custom.green jquery.mobile.custom.green
rm -rf jquery-ui.custom.red jquery.mobile.custom.red
rm -rf jquery-ui.custom.pink jquery.mobile.custom.pink
rm -rf jquery-ui.custom.purple jquery.mobile.custom.purple
rm -rf jquery-ui.custom.orange jquery.mobile.custom.orange
rm -rf jquery-ui.custom.brown jquery.mobile.custom.brown
rm -rf jquery-ui.custom.yellow jquery.mobile.custom.yellow

php scripts/jquery.themeroller.php blue 0054b0 2780e3
php scripts/jquery.themeroller.php facebook 003069 3b5998
php scripts/jquery.themeroller.php gray 29434e 546e7a
php scripts/jquery.themeroller.php green 00701a 43a047
php scripts/jquery.themeroller.php red ab000d e53935
php scripts/jquery.themeroller.php pink 9d0056 d33682
php scripts/jquery.themeroller.php purple 5c007a 8e24aa
php scripts/jquery.themeroller.php orange c25e00 fb8c00
php scripts/jquery.themeroller.php brown 40241a 6d4c41
php scripts/jquery.themeroller.php yellow c49000 fbc02d

