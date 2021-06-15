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
if (!check_user($page, "import")) {
    action_denied();
}
if ($page == "importaciones") {
    require "php/import.php";
    $id_importacion = abs(intval(getParam("id")));
    // GET FILE DATA
    $query = "SELECT *
        FROM tbl_ficheros
        WHERE id_aplicacion='" . page2id("importaciones") . "'
            AND id_registro='${id_importacion}'";
    $row = execute_query($query);
    if ($row === null) {
        show_php_error(array("phperror" => "Unknown fichero (id_importacion='${id_importacion}')"));
    }
    // GET SPECIFIC DATA
    $query = "SELECT *
        FROM tbl_aplicaciones
        WHERE id=(
            SELECT id_aplicacion
            FROM tbl_importaciones
            WHERE id='${id_importacion}')";
    $row2 = execute_query($query);
    if ($row2 === null) {
        show_php_error(array("phperror" => "Unknown aplicacion (id_importacion='${id_importacion}')"));
    }
    // CREATE THE NODE0 AND NODE1 LIST
    $node0 = get_fields_from_dbschema($row2["tabla"]);
    foreach ($node0 as $key => $val) {
        $node0[$key] = $val["name"];
    }
    $node1 = get_fields_from_dbschema(strtok($row2["subtablas"], "("));
    foreach ($node1 as $key => $val) {
        $node1[$key] = $val["name"];
    }
    // CALL IMPORT FILE
    $array = import_file(array(
        "file" => get_directory("dirs/filesdir") . $row["fichero_file"],
        "type" => $row["fichero_type"],
        "nodes" => array($node0,$node1)
    ));
    // DISPLAY OUTPUT
    if (is_array($array)) {
        $buscar = getParam("buscar");
        if ($buscar != "") {
            $array = __import_filter($array, $buscar);
        }
    }
    $offset = getParam("offset", 0);
    $limit = getParam("limit", getDefault("regspagerdef"));
    $count = is_array($array) ? count($array) : 0;
    $select = explode(",", implode(",", array_merge($node0, $node1)));
    $buffer = __import_make_table(array(
        "auto" => true,
        "select" => $select,
        "head" => true,
        "data" => $array,
        "limit" => $limit,
        "offset" => $offset,
        "width" => 120
    ));
    $currentpage = intval($offset / $limit) + 1;
    $totalpages = intval(($count - 1) / $limit) + 1;
    $currentregini = min($offset + 1, $count);
    $currentregend = min($offset + $limit, $count);
    $first = ($currentpage > 1) ? 1 : 0;
    $previous = ($currentpage > 1) ? 1 : 0;
    $next = ($currentpage < $totalpages) ? 1 : 0;
    $last = ($currentpage < $totalpages) ? 1 : 0;
    $buffer .= javascript_template(
        "import_pager('" . LANG("paginaspc") . $currentpage . LANG("spcdespc") . $totalpages .
            " (" . LANG("regsfrom", $page) . " " . $currentregini . LANG("spcalspc") . $currentregend .
            LANG("spcdespc") . $count . ")." . "'," .
            $currentpage . "," . $totalpages . "," . $first . "," . $previous . "," . $next . "," . $last . ")"
    );
    output_handler(array(
        "data" => $buffer,
        "type" => "text/html",
        "cache" => false
    ));
}
if ($page == "datacfg") {
    require "php/import.php";
    // GET THE FILE
    $ok = 0;
    foreach ($_FILES as $file) {
        if (isset($file["tmp_name"]) && $file["tmp_name"] != "" && file_exists($file["tmp_name"])) {
            $ok = 1;
            break;
        } else {
            if (isset($file["name"]) && $file["name"] != "") {
                session_error(LANG("fileuploaderror") . $file["name"]);
            }
            if (isset($file["error"]) && $file["error"] != "") {
                session_error(
                    LANG("fileuploaderror") .
                    upload_error2string($file["error"]) .
                    " (code " . $file["error"] . ")"
                );
            }
            javascript_history(-1);
            die();
        }
    }
    if (!$ok) {
        session_error(LANG("filenotfound", "datacfg"));
        javascript_history(-1);
        die();
    }
    // OPEN FILE
    $fp = gzopen($file["tmp_name"], "r");
    // IMPORT QUERYES
    $limit = 1000000; // 1MB aprox.
    $data = gzread($fp, $limit * 2);
    $len = strlen($data);
    $pos = 0;
    while ($pos < $len) {
        $count = __import_find_query($data, $pos);
        if ($count) {
            $query = substr($data, $pos, $count);
            capture_next_error();
            db_query($query);
            $error = get_clear_error();
            if ($error != "") {
                gzclose($fp);
                session_error(LANG("fileimporterror", "datacfg") . $file["name"]);
                javascript_history(-1);
                die();
            }
            $pos = $pos + $count + 1;
        }
        if (($len - $pos < $limit || !$count) && $limit) {
            $temp = gzread($fp, $limit);
            if (strlen($temp) < $limit) {
                $limit = 0;
            }
            $data = substr($data, $pos) . $temp;
            unset($temp);
            $pos = 0;
            $len = strlen($data);
        } elseif (!$count && !$limit) {
            break;
        }
    }
    gzclose($fp);
    // RETURN
    session_alert(LANG("filefoundok", "datacfg") . $file["name"]);
    javascript_history(-1);
    die();
}
