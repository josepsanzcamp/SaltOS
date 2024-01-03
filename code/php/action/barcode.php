<?php

/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2024 by Josep Sanz Campderrós
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

if (!check_user()) {
    action_denied();
}

// OBTAIN THE MSG
if (getParam("msg")) {
    $msg = getParam("msg");
} else {
    action_denied();
}
// DEFAULT PARAMETERS
$w = intval(getParam("w", 1));
$h = intval(getParam("h", 30));
$m = intval(getParam("m", 10));
$s = intval(getParam("s", 8));
$t = getParam("t", "C39");
// BEGIN THE BARCODE WRAPPER
$cache = get_cache_file(array($msg,$w,$h,$m,$s,$t), ".png");
//~ if(file_exists($cache)) unlink($cache);
if (!file_exists($cache)) {
    require_once "php/libaction.php";
    $buffer = __barcode($msg, $w, $h, $m, $s, $t);
    if ($buffer == "") {
        action_denied();
    }
    file_put_contents($cache, $buffer);
    chmod($cache, 0666);
}
output_handler(array(
    "file" => $cache,
    "cache" => false
));
