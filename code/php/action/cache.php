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
$files = trim(getParam("files", getParam("amp;files")));
if (substr($files, -1, 1) == ",") {
    $files = substr($files, 0, -1);
}
$files = explode(",", $files);
foreach ($files as $key => $val) {
    $file = trim($val);
    $ext = strtolower(extension($file));
    if (in_array($ext, array("js","css")) && file_exists($file)) {
        $files[$key] = $file;
    } else {
        unset($files[$key]);
    }
}
if (!count($files)) {
    die();
}
$files = array_values($files);
$ext = strtolower(extension($files[0]));
$useimginline = eval_bool(getDefault("cache/useimginline"));
$cache = get_cache_file(array("cache",$useimginline,$files), $ext);
//if(file_exists($cache)) unlink($cache);
if (!cache_exists($cache, $files)) {
    $buffer = "";
    foreach ($files as $file) {
        $ext = strtolower(extension($file));
        if ($ext == "css") {
            $temp = file_get_contents($file);
            $temp = __cache_resolve_path($temp, $file);
            if ($useimginline) {
                $temp = inline_images($temp);
            }
            $buffer .= $temp;
        } elseif ($ext == "js") {
            $temp = file_get_contents($file);
            if (substr(trim($temp), -1, 1) != ";") {
                $temp .= ";";
            }
            $buffer .= $temp;
        }
    }
    file_put_contents($cache, $buffer);
    chmod_protected($cache, 0666);
}
output_handler(array(
    "file" => $cache,
    "cache" => true
));
