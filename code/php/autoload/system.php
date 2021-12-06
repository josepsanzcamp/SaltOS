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

function check_system()
{
    // GENERAL CHECK
    if (headers_sent()) {
        show_php_error(array("phperror" => "Has been detected previous headers sent"));
    }
    // PACKAGE CHECKS
    $array = array(
        array("class_exists","DomElement","Class","php-xml"),
        array("function_exists","imagecreatetruecolor","Function","php-gd"),
        array("function_exists","imagecreatefrompng","Function","php-gd"),
        array("function_exists","mb_check_encoding","Function","php-mbstring"),
        array("function_exists","mb_convert_encoding","Function","php-mbstring"),
        array("function_exists","mb_strlen","Function","php-mbstring"),
        array("function_exists","mb_substr","Function","php-mbstring"),
        array("function_exists","mb_strpos","Function","php-mbstring"));
    foreach ($array as $a) {
        if (!$a[0]($a[1])) {
            show_php_error(array("phperror" => "$a[2] $a[1] not found","details" => "Try to install $a[3] package"));
        }
    }
    // INSTALL CHECK
    if (!file_exists("files/config.xml")) {
        require "install/install.php";
        die();
    }
}

function check_postlimit()
{
    $content_length = getServer("CONTENT_LENGTH");
    if ($content_length) {
        $post_max_size = ini_get("post_max_size");
        if (!$post_max_size && ishhvm()) {
            $post_max_size = ini_get("hhvm.server.max_post_size");
        }
        $post_max_size = normalize_value($post_max_size);
        if ($content_length > $post_max_size) {
            session_error(LANG("postlimiterror"));
        }
    }
}

function fix_input_vars()
{
    if (intval(ini_get("max_input_vars")) > 0) {
        $temp = getParam("fix_input_vars");
        if ($temp != "") {
            $temp = querystring2array(base64_decode($temp));
            if (isset($_GET["fix_input_vars"])) {
                unset($_GET["fix_input_vars"]);
                $_GET = array_merge($_GET, $temp);
            }
            if (isset($_POST["fix_input_vars"])) {
                unset($_POST["fix_input_vars"]);
                $_POST = array_merge($_POST, $temp);
            }
        }
    }
}
