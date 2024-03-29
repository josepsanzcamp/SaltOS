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
    _action_denied();
}

$_LANG["default"] = "menu,common";
$_RESULT[$action] = eval_attr(xml2array(detect_apps_files("xml/menu.xml")));

require_once "php/libaction.php";
$_RESULT[$action] = __default_eval_querytag($_RESULT[$action]);
$_RESULT[$action] = __remove_temp_nodes($_RESULT[$action]);

$json = json_encode($_RESULT);
output_handler(array(
    "data" => $json,
    "type" => "application/json",
    "cache" => false
));
