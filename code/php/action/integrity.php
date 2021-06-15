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

if (!check_user()) {
    action_denied();
}
if (getParam("action") == "integrity") {
    if (!eval_bool(getDefault("enableintegrity"))) {
        die();
    }
    // CHECK THE SEMAPHORE
    if (!semaphore_acquire(getParam("action"), getDefault("semaphoretimeout", 100000))) {
        die();
    }
    // FIXING INTEGRITY PROBLEMS
    $query = "SELECT id,tabla FROM tbl_aplicaciones WHERE tabla!=''";
    $result = db_query($query);
    $total = 0;
    while ($row = db_fetch_row($result)) {
        if (time_get_usage() > getDefault("server/percentstop")) {
            break;
        }
        $id_aplicacion = $row["id"];
        $tabla = $row["tabla"];
        $range = execute_query("SELECT MAX(id) maxim, MIN(id) minim FROM ${tabla}");
        for ($i = $range["minim"]; $i < $range["maxim"]; $i += 100000) {
            for (;;) {
                if (time_get_usage() > getDefault("server/percentstop")) {
                    break;
                }
                // SEARCH IDS OF THE MAIN APPLICATION TABLE, THAT DOESN'T EXISTS ON THE REGISTER TABLE
                $query = "SELECT a.id
                    FROM ${tabla} a
                    LEFT JOIN tbl_registros b ON a.id=b.id_registro
                        AND b.id_aplicacion=${id_aplicacion}
                        AND b.first=1
                    WHERE b.id IS NULL
                        AND a.id>=$i
                        AND a.id<$i+100000
                    LIMIT 1000";
                $ids = execute_query_array($query);
                if (!count($ids)) {
                    break;
                }
                make_control($id_aplicacion, $ids);
                $total += count($ids);
                if (count($ids) < 1000) {
                    break;
                }
            }
        }
        $range = execute_query("SELECT MAX(id_registro) maxim, MIN(id_registro) minim
            FROM tbl_registros
            WHERE id_aplicacion=${id_aplicacion}
                AND first=1");
        for ($i = $range["minim"]; $i < $range["maxim"]; $i += 100000) {
            for (;;) {
                if (time_get_usage() > getDefault("server/percentstop")) {
                    break;
                }
                // SEARCH IDS OF THE REGISTER TABLE, THAT DOESN'T EXISTS ON THE MAIN APPLICATION TABLE
                $query = "SELECT a.id_registro
                    FROM tbl_registros a
                    LEFT JOIN ${tabla} b ON b.id=a.id_registro
                    WHERE a.id_aplicacion=${id_aplicacion}
                        AND a.first=1
                        AND b.id IS NULL
                        AND a.id_registro>=$i
                        AND a.id_registro<$i+100000
                    LIMIT 1000";
                $ids = execute_query_array($query);
                if (!count($ids)) {
                    break;
                }
                make_control($id_aplicacion, $ids);
                $total += count($ids);
                if (count($ids) < 1000) {
                    break;
                }
            }
        }
    }
    db_free($result);
    // CHECK INTEGRITY
    for (;;) {
        if (time_get_usage() > getDefault("server/percentstop")) {
            break;
        }
        // SEARCH FOR DUPLICATED ROWS IN REGISTER TABLE
        $query = "SELECT GROUP_CONCAT(id) ids, id_aplicacion, id_registro, COUNT(*) total
            FROM tbl_registros
            WHERE first=1
            GROUP BY id_aplicacion,id_registro
            HAVING total>1
            LIMIT 1000";
        $ids = execute_query_array($query);
        if (!count($ids)) {
            break;
        }
        foreach ($ids as $key => $val) {
            $val = explode(",", $val["ids"]);
            array_shift($val);
            $ids[$key] = implode(",", $val);
        }
        $temp = implode(",", $ids);
        $query = "DELETE FROM tbl_registros WHERE id IN (${temp})";
        db_query($query);
        $total += count($ids);
        if (count($ids) < 1000) {
            break;
        }
    }
    // CHECK INTEGRITY
    for (;;) {
        if (time_get_usage() > getDefault("server/percentstop")) {
            break;
        }
        // SEARCH IDS OF THE REGISTER TABLE THAT DOESN'T EXISTS IN THE APPLICATION TABLE
        $query = "SELECT id
            FROM tbl_registros
            WHERE id_aplicacion NOT IN (
                SELECT id
                FROM tbl_aplicaciones
            )
            LIMIT 1000";
        $ids = execute_query_array($query);
        if (!count($ids)) {
            break;
        }
        $temp = implode(",", $ids);
        $query = "DELETE FROM tbl_registros WHERE id IN (${temp})";
        db_query($query);
        $total += count($ids);
        if (count($ids) < 1000) {
            break;
        }
    }
    // CHECK FOR FILES FIRST ITERATION
    $range = execute_query("SELECT MAX(id) maxim, MIN(id) minim FROM tbl_ficheros");
    for ($i = $range["minim"]; $i < $range["maxim"]; $i += 100000) {
        for (;;) {
            if (time_get_usage() > getDefault("server/percentstop")) {
                break;
            }
            // SEARCH FILES THAT DON'T CONTAIN ANY DIRECTORY SEPARATOR
            $query = "SELECT id,id_aplicacion,fichero_file
                FROM tbl_ficheros
                WHERE fichero_file!=''
                    AND fichero_file NOT LIKE '%/%'
                    AND id>=$i
                    AND id<$i+100000
                LIMIT 1000";
            $rows = execute_query_array($query);
            if (!count($rows)) {
                break;
            }
            foreach ($rows as $row) {
                $row["fichero_file2"] = id2page($row["id_aplicacion"], "unknown") . "/" . $row["fichero_file"];
                $row["fichero_file3"] = get_directory("dirs/filesdir") . $row["fichero_file"];
                $row["fichero_file4"] = get_directory("dirs/filesdir") . $row["fichero_file2"];
                // CREATE DIRECTORY IF NOT EXISTS
                $dir = dirname($row["fichero_file4"]);
                if (!file_exists($dir)) {
                    mkdir($dir);
                    chmod_protected($dir, 0777);
                }
                // MOVE FILE
                if (file_exists($row["fichero_file3"])) {
                    rename($row["fichero_file3"], $row["fichero_file4"]);
                    chmod_protected($row["fichero_file4"], 0666);
                }
                // UPDATE DATABASE
                $query = make_update_query("tbl_ficheros", array(
                    "fichero_file" => $row["fichero_file2"]
                ), make_where_query(array(
                    "id" => $row["id"]
                )));
                db_query($query);
            }
            if (count($rows) < 1000) {
                break;
            }
        }
    }
    // CHECK FOR FILES SECOND ITERATION
    $checks = array(
        array("tbl_usuarios_c","email_signature_file","correo","1=1"),
        array("tbl_cuentas","logo_file","cuentas","1=1"),
        array("tbl_productos","foto_file","productos","1=1"),
        array("tbl_configuracion","valor","maincfg","clave='logo_file'"),
    );
    foreach ($checks as $check) {
        for (;;) {
            if (time_get_usage() > getDefault("server/percentstop")) {
                break;
            }
            // SEARCH FILES THAT DON'T CONTAIN ANY DIRECTORY SEPARATOR
            $query = "SELECT id,${check[1]}
                FROM ${check[0]}
                WHERE ${check[1]}!=''
                    AND ${check[1]} NOT LIKE '%/%'
                    AND ${check[3]}
                LIMIT 1000";
            $rows = execute_query_array($query);
            if (!count($rows)) {
                break;
            }
            foreach ($rows as $row) {
                $row["fichero_file2"] = $check[2] . "/" . $row[$check[1]];
                $row["fichero_file3"] = get_directory("dirs/filesdir") . $row[$check[1]];
                $row["fichero_file4"] = get_directory("dirs/filesdir") . $row["fichero_file2"];
                // CREATE DIRECTORY IF NOT EXISTS
                $dir = dirname($row["fichero_file4"]);
                if (!file_exists($dir)) {
                    mkdir($dir);
                    chmod_protected($dir, 0777);
                }
                // MOVE FILE
                if (file_exists($row["fichero_file3"])) {
                    rename($row["fichero_file3"], $row["fichero_file4"]);
                    chmod_protected($row["fichero_file4"], 0666);
                }
                // UPDATE DATABASE
                $query = make_update_query($check[0], array(
                    $check[1] => $row["fichero_file2"]
                ), make_where_query(array(
                    "id" => $row["id"]
                )));
                db_query($query);
            }
            if (count($rows) < 1000) {
                break;
            }
        }
    }
    // SEND RESPONSE
    if ($total) {
        javascript_alert($total . LANG("msgregistersindexed" . min($total, 2)));
    }
    // RELEASE SEMAPHORE
    semaphore_release(getParam("action"));
    javascript_headers();
    die();
}
