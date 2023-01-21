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

// phpcs:disable PSR1.Files.SideEffects

if (!check_user()) {
    action_denied();
}

$id_aplicacion = page2id($page);
if (!$id_aplicacion) {
    show_php_error(array("phperror" => "Unknown page"));
}
$id_registro = abs($id);
if (!$id_registro) {
    setParam("id", "session");
    $info = array(LANG("temporalfiles") . " " . current_datetime());
} else {
    require "php/listsim.php";
    $info = list_simulator($page, $id_registro);
    if (!isset($info[0])) {
        $info = array($id_registro . " " . LANG($page, "menu"));
    }
    if (substr($info[0], -3, 3) == "...") {
        $info[0] = substr($info[0], 0, -3);
    }
}
$cids = getParam("cid");
if (!$cids) {
    show_php_error(array("phperror" => "Unknown files"));
}
$cids = explode(",", $cids);
$files = array();
require_once "php/libaction.php";
foreach ($cids as $cid) {
    $result = __download($id_aplicacion, $id_registro, $cid);
    if (getParam("format") == "zip") {
        // FIX ONLY FOR ZIP FORMAT
        $result["name"] = mb_convert_encoding($result["name"], "ISO-8859-1", "UTF-8");
    }
    $files[] = array(
        "file" => $result["file"],
        "name" => $result["name"]
    );
}
require_once "lib/phpclasses/archive/archive.php";
$format = getParam("format");
switch ($format) {
    case "zip":
        $archive = new zip_file($info[0] . ".zip");
        $type = "application/zip";
        break;
    case "tar":
        $archive = new tar_file($info[0] . ".tar");
        $type = "application/x-tar";
        break;
    case "gzip":
        $archive = new gzip_file($info[0] . ".tgz");
        $type = "application/x-gzip";
        break;
    case "bzip":
        $archive = new bzip_file($info[0] . ".tbz");
        $type = "application/x-bzip2";
        break;
    default:
        show_php_error(array("phperror" => "Unknown format"));
}
$archive->set_options(array(
    "inmemory" => 1,
    "storepaths" => 0,
    "followlinks" => 1
));
foreach ($files as $key => $val) {
    $archive->add_files($val["file"]);
    $archive->files[$key]["name2"] = $val["name"];
}
$archive->create_archive();
ob_start();
$archive->download_file();
$buffer = ob_get_clean();
output_handler(array(
    "data" => $buffer,
    "type" => $type,
    "cache" => false,
    "die" => false
));
die();
