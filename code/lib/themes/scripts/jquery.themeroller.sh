#!/bin/bash

# THE FOUR GOOGLE COLORS

rm -rf google/blue/light/jquery-ui
rm -rf google/green/light/jquery-ui
rm -rf google/red/light/jquery-ui
rm -rf google/yellow/light/jquery-ui

php scripts/jquery-ui.themeroller.php google/blue/light 4285f4 ffffff ffffff 000000 f0f6ff 1a3561
php scripts/jquery-ui.themeroller.php google/green/light 34a853 ffffff ffffff 000000 f0fff8 144321
php scripts/jquery-ui.themeroller.php google/red/light ea4335 ffffff ffffff 000000 fff0f0 5d1a15
php scripts/jquery-ui.themeroller.php google/yellow/light fbbc05 000000 ffffff 000000 fffff0 644b02

rm -rf google/blue/dark/jquery-ui
rm -rf google/green/dark/jquery-ui
rm -rf google/red/dark/jquery-ui
rm -rf google/yellow/dark/jquery-ui

php scripts/jquery-ui.themeroller.php google/blue/dark 4285f4 ffffff 000000 ffffff 0d1a30 ffffff
php scripts/jquery-ui.themeroller.php google/green/dark 34a853 ffffff 000000 ffffff 0a2110 ffffff
php scripts/jquery-ui.themeroller.php google/red/dark ea4335 ffffff 000000 ffffff 2e0d0a ffffff
php scripts/jquery-ui.themeroller.php google/yellow/dark fbbc05 000000 000000 ffffff 322501 ffffff

rm -rf google/blue/light/jquery.mobile
rm -rf google/green/light/jquery.mobile
rm -rf google/red/light/jquery.mobile
rm -rf google/yellow/light/jquery.mobile

php scripts/jquery.mobile.themeroller.php google/blue/light 4285f4 ffffff ffffff 000000 f0f6ff 1a3561
php scripts/jquery.mobile.themeroller.php google/green/light 34a853 ffffff ffffff 000000 f0fff8 144321
php scripts/jquery.mobile.themeroller.php google/red/light ea4335 ffffff ffffff 000000 fff0f0 5d1a15
php scripts/jquery.mobile.themeroller.php google/yellow/light fbbc05 000000 ffffff 000000 fffff0 644b02

rm -rf google/blue/dark/jquery.mobile
rm -rf google/green/dark/jquery.mobile
rm -rf google/red/dark/jquery.mobile
rm -rf google/yellow/dark/jquery.mobile

php scripts/jquery.mobile.themeroller.php google/blue/dark 4285f4 ffffff 000000 ffffff 0d1a30 ffffff
php scripts/jquery.mobile.themeroller.php google/green/dark 34a853 ffffff 000000 ffffff 0a2110 ffffff
php scripts/jquery.mobile.themeroller.php google/red/dark ea4335 ffffff 000000 ffffff 2e0d0a ffffff
php scripts/jquery.mobile.themeroller.php google/yellow/dark fbbc05 000000 000000 ffffff 322501 ffffff

# UPC OLD AND NEW BLUE COLOR

rm -rf upc/old/light/jquery-ui
rm -rf upc/new/light/jquery-ui

php scripts/jquery-ui.themeroller.php upc/old/light 336699 ffffff ffffff 000000 f0f6ff 19334c
php scripts/jquery-ui.themeroller.php upc/new/light 007bc0 ffffff ffffff 000000 f0f6ff 003d60

rm -rf upc/old/dark/jquery-ui
rm -rf upc/new/dark/jquery-ui

php scripts/jquery-ui.themeroller.php upc/old/dark 336699 ffffff 000000 ffffff 0c1926 ffffff
php scripts/jquery-ui.themeroller.php upc/new/dark 007bc0 ffffff 000000 ffffff 001e30 ffffff

rm -rf upc/old/light/jquery.mobile
rm -rf upc/new/light/jquery.mobile

php scripts/jquery.mobile.themeroller.php upc/old/light 336699 ffffff ffffff 000000 f0f6ff 19334c
php scripts/jquery.mobile.themeroller.php upc/new/light 007bc0 ffffff ffffff 000000 f0f6ff 003d60

rm -rf upc/old/dark/jquery.mobile
rm -rf upc/new/dark/jquery.mobile

php scripts/jquery.mobile.themeroller.php upc/old/dark 336699 ffffff 000000 ffffff 0c1926 ffffff
php scripts/jquery.mobile.themeroller.php upc/new/dark 007bc0 ffffff 000000 ffffff 001e30 ffffff

# THE SIX COSMO COLORS

rm -rf cosmo/blue/light/jquery-ui
rm -rf cosmo/gray/light/jquery-ui
rm -rf cosmo/green/light/jquery-ui
rm -rf cosmo/purple/light/jquery-ui
rm -rf cosmo/orange/light/jquery-ui
rm -rf cosmo/red/light/jquery-ui

php scripts/jquery-ui.themeroller.php cosmo/blue/light 2780e3 ffffff ffffff 000000 f0f6ff 134071
php scripts/jquery-ui.themeroller.php cosmo/gray/light 373a3c ffffff ffffff 000000 f0f0f0 1b1d1e
php scripts/jquery-ui.themeroller.php cosmo/green/light 3fb618 ffffff ffffff 000000 f0fff8 1f5b0c
php scripts/jquery-ui.themeroller.php cosmo/purple/light 9954bb ffffff ffffff 000000 fff6ff 4c2a5d
php scripts/jquery-ui.themeroller.php cosmo/orange/light ff7518 ffffff ffffff 000000 fff6f0 7f3a0c
php scripts/jquery-ui.themeroller.php cosmo/red/light ff0039 ffffff ffffff 000000 fff0f0 7f001c

rm -rf cosmo/blue/dark/jquery-ui
rm -rf cosmo/gray/dark/jquery-ui
rm -rf cosmo/green/dark/jquery-ui
rm -rf cosmo/purple/dark/jquery-ui
rm -rf cosmo/orange/dark/jquery-ui
rm -rf cosmo/red/dark/jquery-ui

php scripts/jquery-ui.themeroller.php cosmo/blue/dark 2780e3 ffffff 000000 ffffff 092038 ffffff
php scripts/jquery-ui.themeroller.php cosmo/gray/dark 373a3c ffffff 000000 ffffff 222222 ffffff
php scripts/jquery-ui.themeroller.php cosmo/green/dark 3fb618 ffffff 000000 ffffff 0f2d06 ffffff
php scripts/jquery-ui.themeroller.php cosmo/purple/dark 9954bb ffffff 000000 ffffff 26152e ffffff
php scripts/jquery-ui.themeroller.php cosmo/orange/dark ff7518 ffffff 000000 ffffff 3f1d06 ffffff
php scripts/jquery-ui.themeroller.php cosmo/red/dark ff0039 ffffff 000000 ffffff 3f000e ffffff

rm -rf cosmo/blue/light/jquery.mobile
rm -rf cosmo/gray/light/jquery.mobile
rm -rf cosmo/green/light/jquery.mobile
rm -rf cosmo/purple/light/jquery.mobile
rm -rf cosmo/orange/light/jquery.mobile
rm -rf cosmo/red/light/jquery.mobile

php scripts/jquery.mobile.themeroller.php cosmo/blue/light 2780e3 ffffff ffffff 000000 f0f6ff 134071
php scripts/jquery.mobile.themeroller.php cosmo/gray/light 373a3c ffffff ffffff 000000 f0f0f0 1b1d1e
php scripts/jquery.mobile.themeroller.php cosmo/green/light 3fb618 ffffff ffffff 000000 f0fff8 1f5b0c
php scripts/jquery.mobile.themeroller.php cosmo/purple/light 9954bb ffffff ffffff 000000 fff6ff 4c2a5d
php scripts/jquery.mobile.themeroller.php cosmo/orange/light ff7518 ffffff ffffff 000000 fff6f0 7f3a0c
php scripts/jquery.mobile.themeroller.php cosmo/red/light ff0039 ffffff ffffff 000000 fff0f0 7f001c

rm -rf cosmo/blue/dark/jquery.mobile
rm -rf cosmo/gray/dark/jquery.mobile
rm -rf cosmo/green/dark/jquery.mobile
rm -rf cosmo/purple/dark/jquery.mobile
rm -rf cosmo/orange/dark/jquery.mobile
rm -rf cosmo/red/dark/jquery.mobile

php scripts/jquery.mobile.themeroller.php cosmo/blue/dark 2780e3 ffffff 000000 ffffff 092038 ffffff
php scripts/jquery.mobile.themeroller.php cosmo/gray/dark 373a3c ffffff 000000 ffffff 222222 ffffff
php scripts/jquery.mobile.themeroller.php cosmo/green/dark 3fb618 ffffff 000000 ffffff 0f2d06 ffffff
php scripts/jquery.mobile.themeroller.php cosmo/purple/dark 9954bb ffffff 000000 ffffff 26152e ffffff
php scripts/jquery.mobile.themeroller.php cosmo/orange/dark ff7518 ffffff 000000 ffffff 3f1d06 ffffff
php scripts/jquery.mobile.themeroller.php cosmo/red/dark ff0039 ffffff 000000 ffffff 3f000e ffffff

