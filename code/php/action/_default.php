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

$_RESULT["default"] = array();

if (check_user()) {
    if (getParam("page")) {
        $page = getParam("page");
    } else {
        $page = lastpage("");
    }
} else {
    $page = "login";
}

$_CONFIG[$page] = xml2array("xml/${page}.xml");
$action = "list";
if (getDefault("$page/default")) {
    $config = getDefault("$page/default");
    $config = eval_attr($config);
    $_RESULT["default"] = $config;
}

if (!isset($_RESULT["default"]["page"])) {
    $_RESULT["default"]["page"] = $page;
}
if (!isset($_RESULT["default"]["action"])) {
    $_RESULT["default"]["action"] = "list";
}
if (!isset($_RESULT["default"]["id"])) {
    $_RESULT["default"]["id"] = "0";
}

$json = json_encode($_RESULT);
output_handler(array(
    "data" => $json,
    "type" => "application/json",
    "cache" => false
));
