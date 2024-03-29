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

// phpcs:disable Generic.Files.LineLength
// phpcs:disable PSR1.Files.SideEffects

if (!check_user()) {
    action_denied();
}

if (getParam("page") && !getParam("id") && !getParam("cid")) {
    // CREATE REPORT FROM HELP
    $file = detect_app_file("doc/{$lang}/{$page}.pdf");
    if (!file_exists($file)) {
        $files = detect_apps_files("doc/*/{$page}.pdf");
        if (isset($files[0])) {
            $file = $files[0];
        }
    }
    if (!file_exists($file)) {
        $files = detect_apps_files("doc/{$lang}/404.pdf");
        if (isset($files[0])) {
            $file = $files[0];
        }
    }
    if (!file_exists($file)) {
        $files = detect_apps_files("doc/*/404.pdf");
        if (isset($files[0])) {
            $file = $files[0];
        }
    }
    $_RESULT = array(
        "title" => basename($file),
        "data" => file_get_contents($file)
    );
    require_once "php/libaction.php";
    __pdfview_output_handler($_RESULT);
} elseif (getParam("page") && getParam("id") && !getParam("cid")) {
    require_once "php/libpdf.php";
    $_LANG["default"] = "$page,menu,common";
    $config = xml2array(detect_app_file("xml/{$page}.xml"));
    $config = $config["pdf"];
    $config = eval_attr($config);
    $pdf = __pdf_eval_pdftag($config);
    // PREPARAR REPORT
    $_RESULT = array(
        "title" => $pdf["name"],
        "data" => $pdf["data"]
    );
    require_once "php/libaction.php";
    __pdfview_output_handler($_RESULT);
} elseif (getParam("page") && getParam("id") && getParam("cid")) {
    // CREATE REPORT FROM DOWNLOAD
    $id_aplicacion = page2id(getParam("page"));
    $id_registro = (getParam("id") == "session") ? getParam("id") : abs(intval(getParam("id")));
    $cid = getParam("cid");
    require_once "php/libaction.php";
    $result = __download($id_aplicacion, $id_registro, $cid);
    // CREAR THUMBS SI ES NECESARIO
    $cache = get_cache_file($result["file"], "pdf");
    if (!file_exists($cache)) {
        require "php/unoconv.php";
        file_put_contents($cache, unoconv2pdf($result["file"]));
    }
    if (!file_exists($cache)) {
        action_denied();
    }
    // PREPARAR REPORT
    $_RESULT = array(
        "title" => $result["name"],
        "data" => file_get_contents($cache)
    );
    require_once "php/libaction.php";
    __pdfview_output_handler($_RESULT);
}
die();
