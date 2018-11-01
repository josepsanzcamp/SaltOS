#!/bin/bash

rm -rf google/blue/light/jquery-ui
rm -rf google/green/light/jquery-ui
rm -rf google/red/light/jquery-ui
rm -rf google/yellow/light/jquery-ui

php scripts/jquery-ui.themeroller.php google/blue/light 4285f4 ffffff ffffff 000000 $(php scripts/lightness.calculator.php 4285f4 0.97) $(php scripts/lightness.calculator.php 4285f4 0.2)
php scripts/jquery-ui.themeroller.php google/green/light 34a853 ffffff ffffff 000000 $(php scripts/lightness.calculator.php 34a853 0.97) $(php scripts/lightness.calculator.php 34a853 0.2)
php scripts/jquery-ui.themeroller.php google/red/light ea4335 ffffff ffffff 000000 $(php scripts/lightness.calculator.php ea4335 0.97) $(php scripts/lightness.calculator.php ea4335 0.2)
php scripts/jquery-ui.themeroller.php google/yellow/light fbbc05 000000 ffffff 000000 $(php scripts/lightness.calculator.php fbbc05 0.97) $(php scripts/lightness.calculator.php fbbc05 0.2)

rm -rf google/blue/dark/jquery-ui
rm -rf google/green/dark/jquery-ui
rm -rf google/red/dark/jquery-ui
rm -rf google/yellow/dark/jquery-ui

php scripts/jquery-ui.themeroller.php google/blue/dark 4285f4 ffffff 000000 ffffff $(php scripts/lightness.calculator.php 4285f4 0.1) ffffff
php scripts/jquery-ui.themeroller.php google/green/dark 34a853 ffffff 000000 ffffff $(php scripts/lightness.calculator.php 34a853 0.1) ffffff
php scripts/jquery-ui.themeroller.php google/red/dark ea4335 ffffff 000000 ffffff $(php scripts/lightness.calculator.php ea4335 0.1) ffffff
php scripts/jquery-ui.themeroller.php google/yellow/dark fbbc05 000000 000000 ffffff $(php scripts/lightness.calculator.php fbbc05 0.1) ffffff

rm -rf google/blue/light/jquery.mobile
rm -rf google/green/light/jquery.mobile
rm -rf google/red/light/jquery.mobile
rm -rf google/yellow/light/jquery.mobile

php scripts/jquery.mobile.themeroller.php google/blue/light 4285f4 ffffff ffffff 000000 $(php scripts/lightness.calculator.php 4285f4 0.97) $(php scripts/lightness.calculator.php 4285f4 0.2)
php scripts/jquery.mobile.themeroller.php google/green/light 34a853 ffffff ffffff 000000 $(php scripts/lightness.calculator.php 34a853 0.97) $(php scripts/lightness.calculator.php 34a853 0.2)
php scripts/jquery.mobile.themeroller.php google/red/light ea4335 ffffff ffffff 000000 $(php scripts/lightness.calculator.php ea4335 0.97) $(php scripts/lightness.calculator.php ea4335 0.2)
php scripts/jquery.mobile.themeroller.php google/yellow/light fbbc05 000000 ffffff 000000 $(php scripts/lightness.calculator.php fbbc05 0.97) $(php scripts/lightness.calculator.php fbbc05 0.2)

rm -rf google/blue/dark/jquery.mobile
rm -rf google/green/dark/jquery.mobile
rm -rf google/red/dark/jquery.mobile
rm -rf google/yellow/dark/jquery.mobile

php scripts/jquery.mobile.themeroller.php google/blue/dark 4285f4 ffffff 000000 ffffff $(php scripts/lightness.calculator.php 4285f4 0.1) ffffff
php scripts/jquery.mobile.themeroller.php google/green/dark 34a853 ffffff 000000 ffffff $(php scripts/lightness.calculator.php 34a853 0.1) ffffff
php scripts/jquery.mobile.themeroller.php google/red/dark ea4335 ffffff 000000 ffffff $(php scripts/lightness.calculator.php ea4335 0.1) ffffff
php scripts/jquery.mobile.themeroller.php google/yellow/dark fbbc05 000000 000000 ffffff $(php scripts/lightness.calculator.php fbbc05 0.1) ffffff

rm -rf google/multi/light/jquery-ui
rm -rf google/multi/dark/jquery-ui

php scripts/jquery-ui.themeroller4.php google/multi/light \
	4285f4 ffffff ffffff 000000 $(php scripts/lightness.calculator.php 4285f4 0.97) $(php scripts/lightness.calculator.php 4285f4 0.2) \
	34a853 ffffff $(php scripts/lightness.calculator.php 34a853 0.97) $(php scripts/lightness.calculator.php 34a853 0.2) \
	ea4335 ffffff $(php scripts/lightness.calculator.php ea4335 0.97) $(php scripts/lightness.calculator.php ea4335 0.2) \
	fbbc05 000000 $(php scripts/lightness.calculator.php fbbc05 0.97) $(php scripts/lightness.calculator.php fbbc05 0.2)
php scripts/jquery-ui.themeroller4.php google/multi/dark \
	4285f4 ffffff 000000 ffffff $(php scripts/lightness.calculator.php 4285f4 0.1) ffffff \
	34a853 ffffff $(php scripts/lightness.calculator.php 34a853 0.1) ffffff \
	ea4335 ffffff $(php scripts/lightness.calculator.php ea4335 0.1) ffffff \
	fbbc05 000000 $(php scripts/lightness.calculator.php fbbc05 0.1) ffffff

