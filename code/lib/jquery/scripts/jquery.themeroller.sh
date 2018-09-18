#!/bin/bash

rm -rf jquery-ui.custom.blue.light
rm -rf jquery-ui.custom.green.light
rm -rf jquery-ui.custom.purple.light
rm -rf jquery-ui.custom.red.light

php scripts/jquery-ui.themeroller.php blue.light 2c579b ffffff ffffff 333333 f6f6f6 2c579b
php scripts/jquery-ui.themeroller.php green.light 107c0f ffffff ffffff 333333 f6f6f6 107c0f
php scripts/jquery-ui.themeroller.php purple.light 80397b ffffff ffffff 333333 f6f6f6 80397b
php scripts/jquery-ui.themeroller.php red.light d24726 ffffff ffffff 333333 f6f6f6 d24726

rm -rf jquery-ui.custom.blue.dark
rm -rf jquery-ui.custom.green.dark
rm -rf jquery-ui.custom.purple.dark
rm -rf jquery-ui.custom.red.dark

php scripts/jquery-ui.themeroller.php blue.dark 2c579b ffffff 000000 ffffff 333333 f6f6f6
php scripts/jquery-ui.themeroller.php green.dark 107c0f ffffff 000000 ffffff 333333 f6f6f6
php scripts/jquery-ui.themeroller.php purple.dark 80397b ffffff 000000 ffffff 333333 f6f6f6
php scripts/jquery-ui.themeroller.php red.dark d24726 ffffff 000000 ffffff 333333 f6f6f6

rm -rf jquery.mobile.custom.blue.light
rm -rf jquery.mobile.custom.green.light
rm -rf jquery.mobile.custom.purple.light
rm -rf jquery.mobile.custom.red.light

php scripts/jquery.mobile.themeroller.php blue.light 2c579b ffffff ffffff 333333 f6f6f6 2c579b
php scripts/jquery.mobile.themeroller.php green.light 107c0f ffffff ffffff 333333 f6f6f6 107c0f
php scripts/jquery.mobile.themeroller.php purple.light 80397b ffffff ffffff 333333 f6f6f6 80397b
php scripts/jquery.mobile.themeroller.php red.light d24726 ffffff ffffff 333333 f6f6f6 d24726

rm -rf jquery.mobile.custom.blue.dark
rm -rf jquery.mobile.custom.green.dark
rm -rf jquery.mobile.custom.purple.dark
rm -rf jquery.mobile.custom.red.dark

php scripts/jquery.mobile.themeroller.php blue.dark 2c579b ffffff 000000 ffffff 333333 f6f6f6
php scripts/jquery.mobile.themeroller.php green.dark 107c0f ffffff 000000 ffffff 333333 f6f6f6
php scripts/jquery.mobile.themeroller.php purple.dark 80397b ffffff 000000 ffffff 333333 f6f6f6
php scripts/jquery.mobile.themeroller.php red.dark d24726 ffffff 000000 ffffff 333333 f6f6f6
