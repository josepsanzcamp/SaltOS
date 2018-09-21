#!/bin/bash

rm -rf jquery-ui.custom.upc.light
rm -rf jquery-ui.custom.uab.light
rm -rf jquery-ui.custom.url.light
rm -rf jquery-ui.custom.cat.light

php scripts/jquery-ui.themeroller.php upc.light 007BC1 ffffff ffffff 333333 f6f6f6 003d60
php scripts/jquery-ui.themeroller.php uab.light 00856c ffffff ffffff 333333 f6f6f6 004236
php scripts/jquery-ui.themeroller.php url.light 990002 ffffff ffffff 333333 f6f6f6 4c0001
php scripts/jquery-ui.themeroller.php cat.light ffc500 000000 ffffff 333333 f6f6f6 7f6200

rm -rf jquery-ui.custom.upc.dark
rm -rf jquery-ui.custom.uab.dark
rm -rf jquery-ui.custom.url.dark
rm -rf jquery-ui.custom.cat.dark

php scripts/jquery-ui.themeroller.php upc.dark 007BC1 ffffff 000000 ffffff 001e30 f6f6f6
php scripts/jquery-ui.themeroller.php uab.dark 00856c ffffff 000000 ffffff 00211b f6f6f6
php scripts/jquery-ui.themeroller.php url.dark 990002 ffffff 000000 ffffff 260000 f6f6f6
php scripts/jquery-ui.themeroller.php cat.dark ffc500 000000 000000 ffffff 3f3100 f6f6f6

rm -rf jquery.mobile.custom.upc.light
rm -rf jquery.mobile.custom.uab.light
rm -rf jquery.mobile.custom.url.light
rm -rf jquery.mobile.custom.cat.light

php scripts/jquery.mobile.themeroller.php upc.light 007BC1 ffffff ffffff 333333 f6f6f6 003d60
php scripts/jquery.mobile.themeroller.php uab.light 00856c ffffff ffffff 333333 f6f6f6 004236
php scripts/jquery.mobile.themeroller.php url.light 990002 ffffff ffffff 333333 f6f6f6 4c0001
php scripts/jquery.mobile.themeroller.php cat.light ffc500 000000 ffffff 333333 f6f6f6 7f6200

rm -rf jquery.mobile.custom.upc.dark
rm -rf jquery.mobile.custom.uab.dark
rm -rf jquery.mobile.custom.url.dark
rm -rf jquery.mobile.custom.cat.dark

php scripts/jquery.mobile.themeroller.php upc.dark 007BC1 ffffff 000000 ffffff 001e30 f6f6f6
php scripts/jquery.mobile.themeroller.php uab.dark 00856c ffffff 000000 ffffff 00211b f6f6f6
php scripts/jquery.mobile.themeroller.php url.dark 990002 ffffff 000000 ffffff 260000 f6f6f6
php scripts/jquery.mobile.themeroller.php cat.dark ffc500 000000 000000 ffffff 3f3100 f6f6f6
