<?php

/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2021 by Josep Sanz Campderrós
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

function _action_denied()
{
    global $_LANG,$_CONFIG,$_RESULT;
    $_LANG["default"] = "denied,common";
    $_CONFIG["denied"] = eval_attr(xml2array("xml/denied.xml"));
    $_RESULT["form"] = getDefault("denied/form");
    add_css_js_page($_RESULT["form"], "denied");
    $json = json_encode($_RESULT);
    output_handler(array(
        "data" => $json,
        "type" => "application/json",
        "cache" => false
    ));
}

function _action_login()
{
    global $_LANG,$_CONFIG,$_RESULT;
    $_LANG["default"] = "login,common";
    $_CONFIG["login"] = eval_attr(xml2array("xml/login.xml"));
    $_RESULT["form"] = getDefault("login/form");
    add_css_js_page($_RESULT["form"], "login");
    $json = json_encode($_RESULT);
    output_handler(array(
        "data" => $json,
        "type" => "application/json",
        "cache" => false
    ));
    die();
}
