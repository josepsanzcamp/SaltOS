#!/bin/bash

rm -rf multi/google/light/jquery-ui
rm -rf multi/google/dark/jquery-ui

php scripts/jquery-ui.themeroller4.php multi/google/light \
	4285f4 ffffff ffffff 000000 $(php scripts/lightness.calculator.php 4285f4 0.97) $(php scripts/lightness.calculator.php 4285f4 0.2) \
	34a853 ffffff $(php scripts/lightness.calculator.php 34a853 0.97) $(php scripts/lightness.calculator.php 34a853 0.2) \
	ea4335 ffffff $(php scripts/lightness.calculator.php ea4335 0.97) $(php scripts/lightness.calculator.php ea4335 0.2) \
	fbbc05 000000 $(php scripts/lightness.calculator.php fbbc05 0.97) $(php scripts/lightness.calculator.php fbbc05 0.2)
php scripts/jquery-ui.themeroller4.php multi/google/dark \
	4285f4 ffffff 000000 ffffff $(php scripts/lightness.calculator.php 4285f4 0.1) ffffff \
	34a853 ffffff $(php scripts/lightness.calculator.php 34a853 0.1) ffffff \
	ea4335 ffffff $(php scripts/lightness.calculator.php ea4335 0.1) ffffff \
	fbbc05 000000 $(php scripts/lightness.calculator.php fbbc05 0.1) ffffff

rm -rf multi/microsoft/light/jquery-ui
rm -rf multi/microsoft/dark/jquery-ui

php scripts/jquery-ui.themeroller4.php multi/microsoft/light \
	04a5f0 ffffff ffffff 000000 $(php scripts/lightness.calculator.php 04a5f0 0.97) $(php scripts/lightness.calculator.php 04a5f0 0.2) \
	81bb05 ffffff $(php scripts/lightness.calculator.php 81bb05 0.97) $(php scripts/lightness.calculator.php 81bb05 0.2) \
	f35225 ffffff $(php scripts/lightness.calculator.php f35225 0.97) $(php scripts/lightness.calculator.php f35225 0.2) \
	ffba07 000000 $(php scripts/lightness.calculator.php ffba07 0.97) $(php scripts/lightness.calculator.php ffba07 0.2)
php scripts/jquery-ui.themeroller4.php multi/microsoft/dark \
	04a5f0 ffffff 000000 ffffff $(php scripts/lightness.calculator.php 04a5f0 0.1) ffffff \
	81bb05 ffffff $(php scripts/lightness.calculator.php 81bb05 0.1) ffffff \
	f35225 ffffff $(php scripts/lightness.calculator.php f35225 0.1) ffffff \
	ffba07 000000 $(php scripts/lightness.calculator.php ffba07 0.1) ffffff

rm -rf multi/office365/light/jquery-ui
rm -rf multi/office365/dark/jquery-ui

php scripts/jquery-ui.themeroller4.php multi/office365/light \
	2c579b ffffff ffffff 000000 $(php scripts/lightness.calculator.php 2c579b 0.97) $(php scripts/lightness.calculator.php 2c579b 0.2) \
	107b11 ffffff $(php scripts/lightness.calculator.php 107b11 0.97) $(php scripts/lightness.calculator.php 107b11 0.2) \
	d24726 ffffff $(php scripts/lightness.calculator.php d24726 0.97) $(php scripts/lightness.calculator.php d24726 0.2) \
	80397b ffffff $(php scripts/lightness.calculator.php 80397b 0.97) $(php scripts/lightness.calculator.php 80397b 0.2)
php scripts/jquery-ui.themeroller4.php multi/office365/dark \
	2c579b ffffff 000000 ffffff $(php scripts/lightness.calculator.php 2c579b 0.1) ffffff \
	107b11 ffffff $(php scripts/lightness.calculator.php 107b11 0.1) ffffff \
	d24726 ffffff $(php scripts/lightness.calculator.php d24726 0.1) ffffff \
	80397b ffffff $(php scripts/lightness.calculator.php 80397b 0.1) ffffff

rm -rf multi/cosmo/light/jquery-ui
rm -rf multi/cosmo/dark/jquery-ui

php scripts/jquery-ui.themeroller4.php multi/cosmo/light \
	2780e3 ffffff ffffff 000000 $(php scripts/lightness.calculator.php 2780e3 0.97) $(php scripts/lightness.calculator.php 2780e3 0.2) \
	3fb618 ffffff $(php scripts/lightness.calculator.php 3fb618 0.97) $(php scripts/lightness.calculator.php 3fb618 0.2) \
	ff0039 ffffff $(php scripts/lightness.calculator.php ff0039 0.97) $(php scripts/lightness.calculator.php ff0039 0.2) \
	ff7518 ffffff $(php scripts/lightness.calculator.php ff7518 0.97) $(php scripts/lightness.calculator.php ff7518 0.2)
php scripts/jquery-ui.themeroller4.php multi/cosmo/dark \
	2780e3 ffffff 000000 ffffff $(php scripts/lightness.calculator.php 2780e3 0.1) ffffff \
	3fb618 ffffff $(php scripts/lightness.calculator.php 3fb618 0.1) ffffff \
	ff0039 ffffff $(php scripts/lightness.calculator.php ff0039 0.1) ffffff \
	ff7518 ffffff $(php scripts/lightness.calculator.php ff7518 0.1) ffffff

rm -rf multi/upc/light/jquery-ui
rm -rf multi/upc/dark/jquery-ui

php scripts/jquery-ui.themeroller2.php multi/upc/light \
	007bc0 ffffff ffffff 000000 $(php scripts/lightness.calculator.php 007bc0 0.97) $(php scripts/lightness.calculator.php 007bc0 0.2) \
	336699 ffffff $(php scripts/lightness.calculator.php 336699 0.97) $(php scripts/lightness.calculator.php 336699 0.2)
php scripts/jquery-ui.themeroller2.php multi/upc/dark \
	007bc0 ffffff 000000 ffffff $(php scripts/lightness.calculator.php 007bc0 0.1) ffffff \
	336699 ffffff $(php scripts/lightness.calculator.php 336699 0.1) ffffff

