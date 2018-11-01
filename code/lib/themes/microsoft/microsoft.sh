#!/bin/bash

rm -rf microsoft/blue/light/jquery-ui
rm -rf microsoft/green/light/jquery-ui
rm -rf microsoft/red/light/jquery-ui
rm -rf microsoft/yellow/light/jquery-ui

php scripts/jquery-ui.themeroller.php microsoft/blue/light 04a5f0 ffffff ffffff 000000 $(php scripts/lightness.calculator.php 04a5f0 0.97) $(php scripts/lightness.calculator.php 04a5f0 0.2)
php scripts/jquery-ui.themeroller.php microsoft/green/light 81bb05 ffffff ffffff 000000 $(php scripts/lightness.calculator.php 81bb05 0.97) $(php scripts/lightness.calculator.php 81bb05 0.2)
php scripts/jquery-ui.themeroller.php microsoft/red/light f35225 ffffff ffffff 000000 $(php scripts/lightness.calculator.php f35225 0.97) $(php scripts/lightness.calculator.php f35225 0.2)
php scripts/jquery-ui.themeroller.php microsoft/yellow/light ffba07 000000 ffffff 000000 $(php scripts/lightness.calculator.php ffba07 0.97) $(php scripts/lightness.calculator.php ffba07 0.2)

rm -rf microsoft/blue/dark/jquery-ui
rm -rf microsoft/green/dark/jquery-ui
rm -rf microsoft/red/dark/jquery-ui
rm -rf microsoft/yellow/dark/jquery-ui

php scripts/jquery-ui.themeroller.php microsoft/blue/dark 04a5f0 ffffff 000000 ffffff $(php scripts/lightness.calculator.php 04a5f0 0.1) ffffff
php scripts/jquery-ui.themeroller.php microsoft/green/dark 81bb05 ffffff 000000 ffffff $(php scripts/lightness.calculator.php 81bb05 0.1) ffffff
php scripts/jquery-ui.themeroller.php microsoft/red/dark f35225 ffffff 000000 ffffff $(php scripts/lightness.calculator.php f35225 0.1) ffffff
php scripts/jquery-ui.themeroller.php microsoft/yellow/dark ffba07 000000 000000 ffffff $(php scripts/lightness.calculator.php ffba07 0.1) ffffff

rm -rf microsoft/blue/light/jquery.mobile
rm -rf microsoft/green/light/jquery.mobile
rm -rf microsoft/red/light/jquery.mobile
rm -rf microsoft/yellow/light/jquery.mobile

php scripts/jquery.mobile.themeroller.php microsoft/blue/light 04a5f0 ffffff ffffff 000000 $(php scripts/lightness.calculator.php 04a5f0 0.97) $(php scripts/lightness.calculator.php 04a5f0 0.2)
php scripts/jquery.mobile.themeroller.php microsoft/green/light 81bb05 ffffff ffffff 000000 $(php scripts/lightness.calculator.php 81bb05 0.97) $(php scripts/lightness.calculator.php 81bb05 0.2)
php scripts/jquery.mobile.themeroller.php microsoft/red/light f35225 ffffff ffffff 000000 $(php scripts/lightness.calculator.php f35225 0.97) $(php scripts/lightness.calculator.php f35225 0.2)
php scripts/jquery.mobile.themeroller.php microsoft/yellow/light ffba07 000000 ffffff 000000 $(php scripts/lightness.calculator.php ffba07 0.97) $(php scripts/lightness.calculator.php ffba07 0.2)

rm -rf microsoft/blue/dark/jquery.mobile
rm -rf microsoft/green/dark/jquery.mobile
rm -rf microsoft/red/dark/jquery.mobile
rm -rf microsoft/yellow/dark/jquery.mobile

php scripts/jquery.mobile.themeroller.php microsoft/blue/dark 04a5f0 ffffff 000000 ffffff $(php scripts/lightness.calculator.php 04a5f0 0.1) ffffff
php scripts/jquery.mobile.themeroller.php microsoft/green/dark 81bb05 ffffff 000000 ffffff $(php scripts/lightness.calculator.php 81bb05 0.1) ffffff
php scripts/jquery.mobile.themeroller.php microsoft/red/dark f35225 ffffff 000000 ffffff $(php scripts/lightness.calculator.php f35225 0.1) ffffff
php scripts/jquery.mobile.themeroller.php microsoft/yellow/dark ffba07 000000 000000 ffffff $(php scripts/lightness.calculator.php ffba07 0.1) ffffff

rm -rf microsoft/multi/light/jquery-ui
rm -rf microsoft/multi/dark/jquery-ui

php scripts/jquery-ui.themeroller4.php microsoft/multi/light \
	04a5f0 ffffff ffffff 000000 $(php scripts/lightness.calculator.php 04a5f0 0.97) $(php scripts/lightness.calculator.php 04a5f0 0.2) \
	81bb05 ffffff $(php scripts/lightness.calculator.php 81bb05 0.97) $(php scripts/lightness.calculator.php 81bb05 0.2) \
	f35225 ffffff $(php scripts/lightness.calculator.php f35225 0.97) $(php scripts/lightness.calculator.php f35225 0.2) \
	ffba07 000000 $(php scripts/lightness.calculator.php ffba07 0.97) $(php scripts/lightness.calculator.php ffba07 0.2)
php scripts/jquery-ui.themeroller4.php microsoft/multi/dark \
	04a5f0 ffffff 000000 ffffff $(php scripts/lightness.calculator.php 04a5f0 0.1) ffffff \
	81bb05 ffffff $(php scripts/lightness.calculator.php 81bb05 0.1) ffffff \
	f35225 ffffff $(php scripts/lightness.calculator.php f35225 0.1) ffffff \
	ffba07 000000 $(php scripts/lightness.calculator.php ffba07 0.1) ffffff

