#!/bin/bash

rm -rf cosmo/blue/light/jquery-ui
rm -rf cosmo/green/light/jquery-ui
rm -rf cosmo/red/light/jquery-ui
rm -rf cosmo/orange/light/jquery-ui
rm -rf cosmo/purple/light/jquery-ui
rm -rf cosmo/gray/light/jquery-ui

php scripts/jquery-ui.themeroller.php cosmo/blue/light 2780e3 ffffff ffffff 000000 $(php scripts/lightness.calculator.php 2780e3 0.97) $(php scripts/lightness.calculator.php 2780e3 0.2)
php scripts/jquery-ui.themeroller.php cosmo/green/light 3fb618 ffffff ffffff 000000 $(php scripts/lightness.calculator.php 3fb618 0.97) $(php scripts/lightness.calculator.php 3fb618 0.2)
php scripts/jquery-ui.themeroller.php cosmo/red/light ff0039 ffffff ffffff 000000 $(php scripts/lightness.calculator.php ff0039 0.97) $(php scripts/lightness.calculator.php ff0039 0.2)
php scripts/jquery-ui.themeroller.php cosmo/orange/light ff7518 ffffff ffffff 000000 $(php scripts/lightness.calculator.php ff7518 0.97) $(php scripts/lightness.calculator.php ff7518 0.2)
php scripts/jquery-ui.themeroller.php cosmo/purple/light 9954bb ffffff ffffff 000000 $(php scripts/lightness.calculator.php 9954bb 0.97) $(php scripts/lightness.calculator.php 9954bb 0.2)
php scripts/jquery-ui.themeroller.php cosmo/gray/light 373a3c ffffff ffffff 000000 $(php scripts/lightness.calculator.php 373a3c 0.97) $(php scripts/lightness.calculator.php 373a3c 0.2)

rm -rf cosmo/blue/dark/jquery-ui
rm -rf cosmo/green/dark/jquery-ui
rm -rf cosmo/red/dark/jquery-ui
rm -rf cosmo/orange/dark/jquery-ui
rm -rf cosmo/purple/dark/jquery-ui
rm -rf cosmo/gray/dark/jquery-ui

php scripts/jquery-ui.themeroller.php cosmo/blue/dark 2780e3 ffffff 000000 ffffff $(php scripts/lightness.calculator.php 2780e3 0.1) ffffff
php scripts/jquery-ui.themeroller.php cosmo/green/dark 3fb618 ffffff 000000 ffffff $(php scripts/lightness.calculator.php 3fb618 0.1) ffffff
php scripts/jquery-ui.themeroller.php cosmo/red/dark ff0039 ffffff 000000 ffffff $(php scripts/lightness.calculator.php ff0039 0.1) ffffff
php scripts/jquery-ui.themeroller.php cosmo/orange/dark ff7518 ffffff 000000 ffffff $(php scripts/lightness.calculator.php ff7518 0.1) ffffff
php scripts/jquery-ui.themeroller.php cosmo/purple/dark 9954bb ffffff 000000 ffffff $(php scripts/lightness.calculator.php 9954bb 0.1) ffffff
php scripts/jquery-ui.themeroller.php cosmo/gray/dark 373a3c ffffff 000000 ffffff $(php scripts/lightness.calculator.php 373a3c 0.1) ffffff

rm -rf cosmo/blue/light/jquery.mobile
rm -rf cosmo/green/light/jquery.mobile
rm -rf cosmo/red/light/jquery.mobile
rm -rf cosmo/orange/light/jquery.mobile
rm -rf cosmo/purple/light/jquery.mobile
rm -rf cosmo/gray/light/jquery.mobile

php scripts/jquery.mobile.themeroller.php cosmo/blue/light 2780e3 ffffff ffffff 000000 $(php scripts/lightness.calculator.php 2780e3 0.97) $(php scripts/lightness.calculator.php 2780e3 0.2)
php scripts/jquery.mobile.themeroller.php cosmo/green/light 3fb618 ffffff ffffff 000000 $(php scripts/lightness.calculator.php 3fb618 0.97) $(php scripts/lightness.calculator.php 3fb618 0.2)
php scripts/jquery.mobile.themeroller.php cosmo/red/light ff0039 ffffff ffffff 000000 $(php scripts/lightness.calculator.php ff0039 0.97) $(php scripts/lightness.calculator.php ff0039 0.2)
php scripts/jquery.mobile.themeroller.php cosmo/orange/light ff7518 ffffff ffffff 000000 $(php scripts/lightness.calculator.php ff7518 0.97) $(php scripts/lightness.calculator.php ff7518 0.2)
php scripts/jquery.mobile.themeroller.php cosmo/purple/light 9954bb ffffff ffffff 000000 $(php scripts/lightness.calculator.php 9954bb 0.97) $(php scripts/lightness.calculator.php 9954bb 0.2)
php scripts/jquery.mobile.themeroller.php cosmo/gray/light 373a3c ffffff ffffff 000000 $(php scripts/lightness.calculator.php 373a3c 0.97) $(php scripts/lightness.calculator.php 373a3c 0.2)

rm -rf cosmo/blue/dark/jquery.mobile
rm -rf cosmo/green/dark/jquery.mobile
rm -rf cosmo/red/dark/jquery.mobile
rm -rf cosmo/orange/dark/jquery.mobile
rm -rf cosmo/purple/dark/jquery.mobile
rm -rf cosmo/gray/dark/jquery.mobile

php scripts/jquery.mobile.themeroller.php cosmo/blue/dark 2780e3 ffffff 000000 ffffff $(php scripts/lightness.calculator.php 2780e3 0.1) ffffff
php scripts/jquery.mobile.themeroller.php cosmo/green/dark 3fb618 ffffff 000000 ffffff $(php scripts/lightness.calculator.php 3fb618 0.1) ffffff
php scripts/jquery.mobile.themeroller.php cosmo/red/dark ff0039 ffffff 000000 ffffff $(php scripts/lightness.calculator.php ff0039 0.1) ffffff
php scripts/jquery.mobile.themeroller.php cosmo/orange/dark ff7518 ffffff 000000 ffffff $(php scripts/lightness.calculator.php ff7518 0.1) ffffff
php scripts/jquery.mobile.themeroller.php cosmo/purple/dark 9954bb ffffff 000000 ffffff $(php scripts/lightness.calculator.php 9954bb 0.1) ffffff
php scripts/jquery.mobile.themeroller.php cosmo/gray/dark 373a3c ffffff 000000 ffffff $(php scripts/lightness.calculator.php 373a3c 0.1) ffffff

rm -rf cosmo/multi/light/jquery-ui
rm -rf cosmo/multi/dark/jquery-ui

php scripts/jquery-ui.themeroller4.php cosmo/multi/light \
	2780e3 ffffff ffffff 000000 $(php scripts/lightness.calculator.php 2780e3 0.97) $(php scripts/lightness.calculator.php 2780e3 0.2) \
	3fb618 ffffff $(php scripts/lightness.calculator.php 3fb618 0.97) $(php scripts/lightness.calculator.php 3fb618 0.2) \
	ff0039 ffffff $(php scripts/lightness.calculator.php ff0039 0.97) $(php scripts/lightness.calculator.php ff0039 0.2) \
	ff7518 ffffff $(php scripts/lightness.calculator.php ff7518 0.97) $(php scripts/lightness.calculator.php ff7518 0.2)
php scripts/jquery-ui.themeroller4.php cosmo/multi/dark \
	2780e3 ffffff 000000 ffffff $(php scripts/lightness.calculator.php 2780e3 0.1) ffffff \
	3fb618 ffffff $(php scripts/lightness.calculator.php 3fb618 0.1) ffffff \
	ff0039 ffffff $(php scripts/lightness.calculator.php ff0039 0.1) ffffff \
	ff7518 ffffff $(php scripts/lightness.calculator.php ff7518 0.1) ffffff

