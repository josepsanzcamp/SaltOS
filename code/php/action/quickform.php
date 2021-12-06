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

if (!check_user()) {
    action_denied();
}

require_once "php/listsim.php";
$pagesim = $page;
if (getParam("is_fichero")) {
    $pagesim = "ficheros";
}
if (getParam("is_buscador")) {
    $pagesim = "buscador";
}
if (getParam("id_folder")) {
    $pagesim = "folders";
}
if (getParam("id_folder")) {
    lastfolder(getParam("id_folder"));
}
$ids = list_simulator($pagesim);
if ($ids === null) {
    action_denied();
}
// FIND IF ID EXISTS IN THE LIST
$id_abs = abs($id);
if (strpos($ids, "_") !== false) {
    $pos = strpos(",${ids},", "_${page}_${id_abs},");
    if ($pos !== false) {
        $pos = strrpos(",${ids},", ",", $pos);
    }
} else {
    $pos = strpos(",${ids}, ", ",${id_abs},");
}
if ($pos !== false) {
    $index = substr_count($ids, ",", 0, $pos);
    $count = substr_count($ids, ",") + 1;
    // PREPARE THE LIST OF REGISTERS
    $minindex = max($index - intval($limit / 2), 0);
    $maxindex = min($minindex + $limit, $count - 1);
    $minindex = max($maxindex - $limit, 0);
    // PREPARAR LISTA IDS2 PARA BUSCAR EL TITLE
    $ids2 = array();
    if ($minindex > 0) {
        $pos2 = strpos($ids, ",");
        $ids2[0] = substr($ids, 0, $pos2);
    }
    $pos--;
    $pos2 = strlen($ids);
    for ($i = $minindex; $i < $index; $i++) {
        $pos = strrpos($ids, ",", $pos - $pos2 - 1);
    }
    if ($pos === false) {
        $pos = -1;
    }
    for ($i = $minindex; $i <= $maxindex; $i++) {
        $pos2 = strpos($ids, ",", $pos + 1);
        if ($pos2 === false) {
            $pos2 = strlen($ids);
        }
        $ids2[$i] = substr($ids, $pos + 1, $pos2 - 1 - $pos);
        $pos = $pos2;
    }
    if ($maxindex < $count - 1) {
        $pos2 = strrpos($ids, ",");
        $ids2[$count - 1] = substr($ids, $pos2 + 1);
    }
    $ids = array_values($ids2);
    //~ echo "<pre>".sprintr($ids)."</pre>";
    //~ echo "<pre>".sprintr(array($minindex,$index,$maxindex))."</pre>";
    //~ echo "<pre>".sprintr($ids2)."</pre>";
    //~ die();
} else {
    $index = 0;
    $count = 1;
    $ids2 = array($id_abs);
    $ids = array($id_abs);
}
// RETRIEVE THE ACTION_TITLE
$titles = list_simulator($pagesim, $ids);
//~ echo "<pre>".sprintr($ids2)."</pre>";
//~ echo "<pre>".sprintr($titles)."</pre>";
//~ die();
// PREPARE THE RESULT
$_RESULT = array("rows" => array());
foreach ($ids as $key => $value) {
    $label = isset($titles[$key]) ? $titles[$key] : $value;
    $row = array("label" => $label,"value" => $value);
    set_array($_RESULT["rows"], "row", $row);
}
// PREPARE THE VALUE
$_RESULT["value"] = $ids2[$index];
// PREPARE THE DISABLED BUTTONS
$_RESULT["first"] = ($index > 0);
$_RESULT["previous"] = ($index > 0);
$_RESULT["next"] = ($index < $count - 1);
$_RESULT["last"] = ($index < $count - 1);
// PREPARE THE OUTPUT
$_RESULT["rows"] = array_values($_RESULT["rows"]);
$buffer = json_encode($_RESULT);
// CONTINUE
output_handler(array(
    "data" => $buffer,
    "type" => "application/json",
    "cache" => false
));
