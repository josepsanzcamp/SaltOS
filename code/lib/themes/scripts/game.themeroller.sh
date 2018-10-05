#!/bin/bash

color1=4285f4
color2=ffffff
color3=ffffff
color4=$(php scripts/lightness.calculator.php $color1 0.25)
color5=$(php scripts/lightness.calculator.php $color1 0.97)
color6=$(php scripts/lightness.calculator.php $(php scripts/complementary.calculator.php $color1 180) 0.3)

rm -rf google/blue/light/jquery-ui
php scripts/jquery-ui.themeroller.php google/blue/light $color1 $color2 $color3 $color4 $color5 $color6

color1=4285f4
color2=ffffff
color3=000000
color4=$(php scripts/lightness.calculator.php $color1 0.95)
color5=$(php scripts/lightness.calculator.php $color1 0.15)
color6=$(php scripts/lightness.calculator.php $(php scripts/complementary.calculator.php $color1 180) 0.7)

rm -rf google/blue/dark/jquery-ui
php scripts/jquery-ui.themeroller.php google/blue/dark $color1 $color2 $color3 $color4 $color5 $color6
