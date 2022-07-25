<?php

/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2022 by Josep Sanz Campderrós
More information in https://www.saltos.org or info@saltos.org

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once "php/libaction.php";
$id = getDefault("captcha/id", "captcha");
$type = getDefault("captcha/type", "number");
$length = getDefault("captcha/length", 5);

init_random();
// DEFINE THE CODE AND REAL CAPTCHA
if ($type == "number") {
    do {
        $code = str_pad(rand(0, pow(10, $length) - 1), $length, "0", STR_PAD_LEFT);
    } while (!__captcha_isprime($code));
    sess_init();
    setSession($id, $code);
    sess_close();
} elseif ($type == "math") {
    $max = pow(10, round($length / 2)) - 1;
    do {
        do {
            $num1 = rand(0, $max);
        } while (!__captcha_isprime($num1));
        $oper = rand(0, 1) ? "+" : "-";
        do {
            $num2 = rand(0, $max);
            $code = $num1 . $oper . $num2;
        } while (!__captcha_isprime($num2) || substr($num2, 0, 1) == "7" || strlen($code) != $length);
    } while ($oper == "-" && $num1 < $num2);
    $real = eval("return $code;");
    sess_init();
    setSession($id, $real);
    sess_close();
} else {
    action_denied();
}
$buffer = __captcha_image($code);
// OUTPUT IMAGE
output_handler(array(
    "data" => $buffer,
    "type" => "image/png",
    "cache" => false
));
