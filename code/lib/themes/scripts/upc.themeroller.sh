#!/bin/bash

rm -rf upc/old/light/jquery-ui
rm -rf upc/new/light/jquery-ui

php scripts/jquery-ui.themeroller.php upc/old/light 336699 ffffff ffffff 000000 $(php scripts/lightness.calculator.php 336699 0.97) $(php scripts/lightness.calculator.php 336699 0.2)
php scripts/jquery-ui.themeroller.php upc/new/light 007bc0 ffffff ffffff 000000 $(php scripts/lightness.calculator.php 007bc0 0.97) $(php scripts/lightness.calculator.php 007bc0 0.2)

rm -rf upc/old/dark/jquery-ui
rm -rf upc/new/dark/jquery-ui

php scripts/jquery-ui.themeroller.php upc/old/dark 336699 ffffff 000000 ffffff $(php scripts/lightness.calculator.php 336699 0.1) ffffff
php scripts/jquery-ui.themeroller.php upc/new/dark 007bc0 ffffff 000000 ffffff $(php scripts/lightness.calculator.php 007bc0 0.1) ffffff

rm -rf upc/old/light/jquery.mobile
rm -rf upc/new/light/jquery.mobile

php scripts/jquery.mobile.themeroller.php upc/old/light 336699 ffffff ffffff 000000 $(php scripts/lightness.calculator.php 336699 0.97) $(php scripts/lightness.calculator.php 336699 0.2)
php scripts/jquery.mobile.themeroller.php upc/new/light 007bc0 ffffff ffffff 000000 $(php scripts/lightness.calculator.php 007bc0 0.97) $(php scripts/lightness.calculator.php 007bc0 0.2)

rm -rf upc/old/dark/jquery.mobile
rm -rf upc/new/dark/jquery.mobile

php scripts/jquery.mobile.themeroller.php upc/old/dark 336699 ffffff 000000 ffffff $(php scripts/lightness.calculator.php 336699 0.1) ffffff
php scripts/jquery.mobile.themeroller.php upc/new/dark 007bc0 ffffff 000000 ffffff $(php scripts/lightness.calculator.php 007bc0 0.1) ffffff

