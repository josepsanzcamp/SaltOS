#!/bin/bash

rm -rf office365/blue/light/jquery-ui
rm -rf office365/green/light/jquery-ui
rm -rf office365/red/light/jquery-ui
rm -rf office365/purple/light/jquery-ui

php scripts/jquery-ui.themeroller.php office365/blue/light 2c579b ffffff ffffff 000000 $(php scripts/lightness.calculator.php 2c579b 0.97) $(php scripts/lightness.calculator.php 2c579b 0.2)
php scripts/jquery-ui.themeroller.php office365/green/light 107b11 ffffff ffffff 000000 $(php scripts/lightness.calculator.php 107b11 0.97) $(php scripts/lightness.calculator.php 107b11 0.2)
php scripts/jquery-ui.themeroller.php office365/red/light d24726 ffffff ffffff 000000 $(php scripts/lightness.calculator.php d24726 0.97) $(php scripts/lightness.calculator.php d24726 0.2)
php scripts/jquery-ui.themeroller.php office365/purple/light 80397b ffffff ffffff 000000 $(php scripts/lightness.calculator.php 80397b 0.97) $(php scripts/lightness.calculator.php 80397b 0.2)

rm -rf office365/blue/dark/jquery-ui
rm -rf office365/green/dark/jquery-ui
rm -rf office365/red/dark/jquery-ui
rm -rf office365/purple/dark/jquery-ui

php scripts/jquery-ui.themeroller.php office365/blue/dark 2c579b ffffff 000000 ffffff $(php scripts/lightness.calculator.php 2c579b 0.1) ffffff
php scripts/jquery-ui.themeroller.php office365/green/dark 107b11 ffffff 000000 ffffff $(php scripts/lightness.calculator.php 107b11 0.1) ffffff
php scripts/jquery-ui.themeroller.php office365/red/dark d24726 ffffff 000000 ffffff $(php scripts/lightness.calculator.php d24726 0.1) ffffff
php scripts/jquery-ui.themeroller.php office365/purple/dark 80397b ffffff 000000 ffffff $(php scripts/lightness.calculator.php 80397b 0.1) ffffff

rm -rf office365/blue/light/jquery.mobile
rm -rf office365/green/light/jquery.mobile
rm -rf office365/red/light/jquery.mobile
rm -rf office365/purple/light/jquery.mobile

php scripts/jquery.mobile.themeroller.php office365/blue/light 2c579b ffffff ffffff 000000 $(php scripts/lightness.calculator.php 2c579b 0.97) $(php scripts/lightness.calculator.php 2c579b 0.2)
php scripts/jquery.mobile.themeroller.php office365/green/light 107b11 ffffff ffffff 000000 $(php scripts/lightness.calculator.php 107b11 0.97) $(php scripts/lightness.calculator.php 107b11 0.2)
php scripts/jquery.mobile.themeroller.php office365/red/light d24726 ffffff ffffff 000000 $(php scripts/lightness.calculator.php d24726 0.97) $(php scripts/lightness.calculator.php d24726 0.2)
php scripts/jquery.mobile.themeroller.php office365/purple/light 80397b ffffff ffffff 000000 $(php scripts/lightness.calculator.php 80397b 0.97) $(php scripts/lightness.calculator.php 80397b 0.2)

rm -rf office365/blue/dark/jquery.mobile
rm -rf office365/green/dark/jquery.mobile
rm -rf office365/red/dark/jquery.mobile
rm -rf office365/purple/dark/jquery.mobile

php scripts/jquery.mobile.themeroller.php office365/blue/dark 2c579b ffffff 000000 ffffff $(php scripts/lightness.calculator.php 2c579b 0.1) ffffff
php scripts/jquery.mobile.themeroller.php office365/green/dark 107b11 ffffff 000000 ffffff $(php scripts/lightness.calculator.php 107b11 0.1) ffffff
php scripts/jquery.mobile.themeroller.php office365/red/dark d24726 ffffff 000000 ffffff $(php scripts/lightness.calculator.php d24726 0.1) ffffff
php scripts/jquery.mobile.themeroller.php office365/purple/dark 80397b ffffff 000000 ffffff $(php scripts/lightness.calculator.php 80397b 0.1) ffffff

