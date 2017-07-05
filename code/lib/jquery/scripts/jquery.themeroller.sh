#!/bin/bash

rm -rf jquery-ui.custom.blue.light jquery.mobile.custom.blue.light
rm -rf jquery-ui.custom.facebook.light jquery.mobile.custom.facebook.light
rm -rf jquery-ui.custom.gray.light jquery.mobile.custom.gray.light
rm -rf jquery-ui.custom.green.light jquery.mobile.custom.green.light
rm -rf jquery-ui.custom.red.light jquery.mobile.custom.red.light
rm -rf jquery-ui.custom.pink.light jquery.mobile.custom.pink.light
rm -rf jquery-ui.custom.purple.light jquery.mobile.custom.purple.light
rm -rf jquery-ui.custom.orange.light jquery.mobile.custom.orange.light
rm -rf jquery-ui.custom.brown.light jquery.mobile.custom.brown.light

php scripts/jquery.themeroller.php blue light 0054b0 2780e3
php scripts/jquery.themeroller.php facebook light 003069 3b5998
php scripts/jquery.themeroller.php gray light 29434e 546e7a
php scripts/jquery.themeroller.php green light 00701a 43a047
php scripts/jquery.themeroller.php red light ab000d e53935
php scripts/jquery.themeroller.php pink light 9d0056 d33682
php scripts/jquery.themeroller.php purple light 5c007a 8e24aa
php scripts/jquery.themeroller.php orange light c25e00 fb8c00
php scripts/jquery.themeroller.php brown light 40241a 6d4c41

rm -rf jquery-ui.custom.blue.dark jquery.mobile.custom.blue.dark
rm -rf jquery-ui.custom.facebook.dark jquery.mobile.custom.facebook.dark
rm -rf jquery-ui.custom.gray.dark jquery.mobile.custom.gray.dark
rm -rf jquery-ui.custom.green.dark jquery.mobile.custom.green.dark
rm -rf jquery-ui.custom.red.dark jquery.mobile.custom.red.dark
rm -rf jquery-ui.custom.pink.dark jquery.mobile.custom.pink.dark
rm -rf jquery-ui.custom.purple.dark jquery.mobile.custom.purple.dark
rm -rf jquery-ui.custom.orange.dark jquery.mobile.custom.orange.dark
rm -rf jquery-ui.custom.brown.dark jquery.mobile.custom.brown.dark

php scripts/jquery.themeroller.php blue dark 0054b0 2780e3
php scripts/jquery.themeroller.php facebook dark 003069 3b5998
php scripts/jquery.themeroller.php gray dark 29434e 546e7a
php scripts/jquery.themeroller.php green dark 00701a 43a047
php scripts/jquery.themeroller.php red dark ab000d e53935
php scripts/jquery.themeroller.php pink dark 9d0056 d33682
php scripts/jquery.themeroller.php purple dark 5c007a 8e24aa
php scripts/jquery.themeroller.php orange dark c25e00 fb8c00
php scripts/jquery.themeroller.php brown dark 40241a 6d4c41
