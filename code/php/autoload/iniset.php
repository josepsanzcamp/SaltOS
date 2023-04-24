<?php

/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2023 by Josep Sanz Campderrós
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

function eval_iniset($array)
{
    if (is_array($array)) {
        foreach ($array as $key => $val) {
            $key = limpiar_key($key);
            $current = ini_get($key);
            $diff = 0;
            if (strtolower($val) == "on" || strtolower($val) == "off") {
                $current = $current ? "On" : "Off";
                if (strtolower($val) != strtolower($current)) {
                    $diff = 1;
                }
            } else {
                if ($val != $current) {
                    $diff = 1;
                }
            }
            if ($diff) {
                if ($key == "mbstring.internal_encoding") {
                    if (mb_internal_encoding($val) === false) {
                        show_php_error(array("phperror" => "mb_internal_encoding fails to set '$val'"));
                    }
                } elseif ($key == "mbstring.detect_order") {
                    $val = implode(",", array_intersect(explode(",", $val), mb_list_encodings()));
                    if (mb_detect_order($val) === false) {
                        show_php_error(array("phperror" => "mb_detect_order fails to set '$val'"));
                    }
                } elseif (ini_set($key, $val) === false) {
                    show_php_error(array("phperror" => "ini_set fails to set '$key' from '$current' to '$val'"));
                }
            }
        }
    }
}

function eval_putenv($array)
{
    if (is_disabled_function("putenv")) {
        return;
    }
    if (is_array($array)) {
        foreach ($array as $key => $val) {
            $key = limpiar_key($key);
            $current = getenv($key);
            $diff = 0;
            if ($val != $current) {
                $diff = 1;
            }
            if ($diff) {
                if (putenv($key . "=" . $val) === false) {
                    show_php_error(array("phperror" => "putenv fails to set '$key' from '$current' to '$val'"));
                }
            }
        }
    }
}
