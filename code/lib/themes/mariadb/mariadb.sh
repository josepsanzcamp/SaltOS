#!/bin/bash

rm -rf mariadb/blue/light/jquery-ui
rm -rf mariadb/gray/light/jquery-ui
rm -rf mariadb/purple/light/jquery-ui
rm -rf mariadb/orange/light/jquery-ui

php scripts/jquery-ui.themeroller.php mariadb/blue/light 002b64 ffffff ffffff 000000 $(php scripts/lightness.calculator.php 002b64 0.97) $(php scripts/lightness.calculator.php 002b64 0.2)
php scripts/jquery-ui.themeroller.php mariadb/gray/light 333333 ffffff ffffff 000000 $(php scripts/lightness.calculator.php 333333 0.97) $(php scripts/lightness.calculator.php 333333 0.2)
php scripts/jquery-ui.themeroller.php mariadb/purple/light 8e126d ffffff ffffff 000000 $(php scripts/lightness.calculator.php 8e126d 0.97) $(php scripts/lightness.calculator.php 8e126d 0.2)
php scripts/jquery-ui.themeroller.php mariadb/orange/light e26e34 ffffff ffffff 000000 $(php scripts/lightness.calculator.php e26e34 0.97) $(php scripts/lightness.calculator.php e26e34 0.2)

rm -rf mariadb/blue/dark/jquery-ui
rm -rf mariadb/gray/dark/jquery-ui
rm -rf mariadb/purple/dark/jquery-ui
rm -rf mariadb/orange/dark/jquery-ui

php scripts/jquery-ui.themeroller.php mariadb/blue/dark 002b64 ffffff 000000 ffffff $(php scripts/lightness.calculator.php 002b64 0.1) ffffff
php scripts/jquery-ui.themeroller.php mariadb/gray/dark 333333 ffffff 000000 ffffff $(php scripts/lightness.calculator.php 333333 0.1) ffffff
php scripts/jquery-ui.themeroller.php mariadb/purple/dark 8e126d ffffff 000000 ffffff $(php scripts/lightness.calculator.php 8e126d 0.1) ffffff
php scripts/jquery-ui.themeroller.php mariadb/orange/dark e26e34 ffffff 000000 ffffff $(php scripts/lightness.calculator.php e26e34 0.1) ffffff

rm -rf mariadb/blue/light/jquery.mobile
rm -rf mariadb/gray/light/jquery.mobile
rm -rf mariadb/purple/light/jquery.mobile
rm -rf mariadb/orange/light/jquery.mobile

php scripts/jquery.mobile.themeroller.php mariadb/blue/light 002b64 ffffff ffffff 000000 $(php scripts/lightness.calculator.php 002b64 0.97) $(php scripts/lightness.calculator.php 002b64 0.2)
php scripts/jquery.mobile.themeroller.php mariadb/gray/light 333333 ffffff ffffff 000000 $(php scripts/lightness.calculator.php 333333 0.97) $(php scripts/lightness.calculator.php 333333 0.2)
php scripts/jquery.mobile.themeroller.php mariadb/purple/light 8e126d ffffff ffffff 000000 $(php scripts/lightness.calculator.php 8e126d 0.97) $(php scripts/lightness.calculator.php 8e126d 0.2)
php scripts/jquery.mobile.themeroller.php mariadb/orange/light e26e34 ffffff ffffff 000000 $(php scripts/lightness.calculator.php e26e34 0.97) $(php scripts/lightness.calculator.php e26e34 0.2)

rm -rf mariadb/blue/dark/jquery.mobile
rm -rf mariadb/gray/dark/jquery.mobile
rm -rf mariadb/purple/dark/jquery.mobile
rm -rf mariadb/orange/dark/jquery.mobile

php scripts/jquery.mobile.themeroller.php mariadb/blue/dark 002b64 ffffff 000000 ffffff $(php scripts/lightness.calculator.php 002b64 0.1) ffffff
php scripts/jquery.mobile.themeroller.php mariadb/gray/dark 333333 ffffff 000000 ffffff $(php scripts/lightness.calculator.php 333333 0.1) ffffff
php scripts/jquery.mobile.themeroller.php mariadb/purple/dark 8e126d ffffff 000000 ffffff $(php scripts/lightness.calculator.php 8e126d 0.1) ffffff
php scripts/jquery.mobile.themeroller.php mariadb/orange/dark e26e34 ffffff 000000 ffffff $(php scripts/lightness.calculator.php e26e34 0.1) ffffff

rm -rf mariadb/multi/light/jquery-ui
rm -rf mariadb/multi/dark/jquery-ui

php scripts/jquery-ui.themeroller4.php mariadb/multi/light \
	002b64 ffffff ffffff 000000 $(php scripts/lightness.calculator.php 002b64 0.97) $(php scripts/lightness.calculator.php 002b64 0.2) \
	333333 ffffff $(php scripts/lightness.calculator.php 333333 0.97) $(php scripts/lightness.calculator.php 333333 0.2) \
	8e126d ffffff $(php scripts/lightness.calculator.php 8e126d 0.97) $(php scripts/lightness.calculator.php 8e126d 0.2) \
	e26e34 ffffff $(php scripts/lightness.calculator.php e26e34 0.97) $(php scripts/lightness.calculator.php e26e34 0.2)
php scripts/jquery-ui.themeroller4.php mariadb/multi/dark \
	002b64 ffffff 000000 ffffff $(php scripts/lightness.calculator.php 002b64 0.1) ffffff \
	333333 ffffff $(php scripts/lightness.calculator.php 333333 0.1) ffffff \
	8e126d ffffff $(php scripts/lightness.calculator.php 8e126d 0.1) ffffff \
	e26e34 ffffff $(php scripts/lightness.calculator.php e26e34 0.1) ffffff

