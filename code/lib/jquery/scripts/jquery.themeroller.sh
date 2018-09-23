#!/bin/bash

rm -rf jquery-ui.custom.blue.light
rm -rf jquery-ui.custom.green.light
rm -rf jquery-ui.custom.red.light
rm -rf jquery-ui.custom.yellow.light

php scripts/jquery-ui.themeroller.php blue.light 4285f4 ffffff ffffff 000000 f0f6ff 1a3561
php scripts/jquery-ui.themeroller.php green.light 34a853 ffffff ffffff 000000 f0fff8 144321
php scripts/jquery-ui.themeroller.php red.light ea4335 ffffff ffffff 000000 fff0f0 5d1a15
php scripts/jquery-ui.themeroller.php yellow.light fbbc05 000000 ffffff 000000 fffff0 644b02

rm -rf jquery-ui.custom.blue.dark
rm -rf jquery-ui.custom.green.dark
rm -rf jquery-ui.custom.red.dark
rm -rf jquery-ui.custom.yellow.dark

php scripts/jquery-ui.themeroller.php blue.dark 4285f4 ffffff 000000 ffffff 0d1a30 ffffff
php scripts/jquery-ui.themeroller.php green.dark 34a853 ffffff 000000 ffffff 0a2110 ffffff
php scripts/jquery-ui.themeroller.php red.dark ea4335 ffffff 000000 ffffff 2e0d0a ffffff
php scripts/jquery-ui.themeroller.php yellow.dark fbbc05 000000 000000 ffffff 322501 ffffff

rm -rf jquery.mobile.custom.blue.light
rm -rf jquery.mobile.custom.green.light
rm -rf jquery.mobile.custom.red.light
rm -rf jquery.mobile.custom.yellow.light

php scripts/jquery.mobile.themeroller.php blue.light 4285f4 ffffff ffffff 000000 f0f6ff 1a3561
php scripts/jquery.mobile.themeroller.php green.light 34a853 ffffff ffffff 000000 f0fff8 144321
php scripts/jquery.mobile.themeroller.php red.light ea4335 ffffff ffffff 000000 fff0f0 5d1a15
php scripts/jquery.mobile.themeroller.php yellow.light fbbc05 000000 ffffff 000000 fffff0 644b02

rm -rf jquery.mobile.custom.blue.dark
rm -rf jquery.mobile.custom.green.dark
rm -rf jquery.mobile.custom.red.dark
rm -rf jquery.mobile.custom.yellow.dark

php scripts/jquery.mobile.themeroller.php blue.dark 4285f4 ffffff 000000 ffffff 0d1a30 ffffff
php scripts/jquery.mobile.themeroller.php green.dark 34a853 ffffff 000000 ffffff 0a2110 ffffff
php scripts/jquery.mobile.themeroller.php red.dark ea4335 ffffff 000000 ffffff 2e0d0a ffffff
php scripts/jquery.mobile.themeroller.php yellow.dark fbbc05 000000 000000 ffffff 322501 ffffff
