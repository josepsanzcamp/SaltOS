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

// phpcs:disable PSR1.Files.SideEffects

if (!check_user()) {
    action_denied();
}

// GET PARAMETERS
$pass = getParam("pass");
$format = getParam("format");
if (!in_array($format, array("png","json"))) {
    action_denied();
}
$width = intval(getParam("width", 60));
$height = intval(getParam("width", 16));
$size = intval(getParam("font", 8));
$minscore = intval(getDefault("security/minscore"));
// PREPARE CACHE FILENAME
$temp = get_directory("dirs/cachedir");
$hash = md5(serialize(array($pass,$format,$width,$height,$size,$minscore)));
$cache = "{$temp}{$hash}.{$format}";
// FOR DEBUG PURPOSES
//if(file_exists($cache)) unlink($cache);
// CREATE IF NOT EXISTS
if (!file_exists($cache)) {
    // PROCESS FORMATS
    if ($format == "png") {
        $score = password_strength($pass);
        require_once "php/libaction.php";
        $buffer = __score_image($score, $width, $height, $size);
        file_put_contents($cache, $buffer);
        chmod($cache, 0666);
    }
    if ($format == "json") {
        $score = password_strength($pass);
        require_once "php/libaction.php";
        $buffer = __score_image($score, $width, $height, $size);
        $data = base64_encode($buffer);
        $data = "data:image/png;base64,{$data}";
        $valid = ($score >= $minscore) ? 1 : 0;
        $_RESULT = array("image" => $data,"score" => $score . "%","valid" => $valid);
        $buffer = json_encode($_RESULT);
        file_put_contents($cache, $buffer);
        chmod($cache, 0666);
    }
}
output_handler(array(
    "file" => $cache,
    "cache" => true
));
