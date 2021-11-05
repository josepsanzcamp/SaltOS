<?php

/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2021 by Josep Sanz Campderrós
More information in http://www.saltos.org or info@saltos.org

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// phpcs:disable PSR1.Files.SideEffects


// OBTAIN THE MSG
if (getParam("msg")) {
    $msg = getParam("msg");
} elseif (getParam("page") && getParam("id")) {
    $id = abs(intval(getParam("id")));
    require_once "php/libaction.php";
    $vcard = __vcard($page, $id, "small");
    $msg = $vcard["data"];
} else {
    action_denied();
}
// DEFAULT PARAMETERS
$s = intval(getParam("s", 6));
$m = intval(getParam("m", 10));
// BEGIN THE QRCODE WRAPPER
$cache = get_cache_file($msg, ".png");
//~ if(file_exists($cache)) unlink($cache);
if (!file_exists($cache)) {
    require_once "php/libaction.php";
    $buffer = __qrcode($msg, $s, $m);
    if ($buffer == "") {
        action_denied();
    }
    file_put_contents($cache, $buffer);
    chmod_protected($cache, 0666);
}
output_handler(array(
    "file" => $cache,
    "cache" => false
));
