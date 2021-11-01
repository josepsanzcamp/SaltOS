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

if (!check_user($page, "export")) {
    action_denied();
}

if ($page == "datacfg") {
    $name = encode_bad_chars("backup_saltos_" . current_date()) . ".gz";
    $dbschema = xml2array("xml/dbschema.xml");
    $file = get_temp_file(".gz");
    $fp = gzopen($file, "w");
    foreach ($dbschema["tables"] as $table) {
        $fields = array();
        foreach ($table["fields"] as $field) {
            $fields[] = $field["name"];
        }
        $fields = "LENGTH(" . implode(")+LENGTH(", $fields) . ")";
        $table = $table["name"];
        $query = "DELETE FROM ${table};\n";
        gzwrite($fp, $query);
        $query = "SELECT COUNT(*) count FROM ${table}";
        $count = execute_query($query);
        $offset = 0;
        while ($offset < $count) {
            $limit = 10000;
            $free = memory_get_free(true);
            // LIMIT CHECK
            for (;;) {
                $query = "SELECT ${fields} FROM ${table} ORDER BY id ASC LIMIT ${offset},${limit}";
                $length = array_sum(execute_query_array($query));
                if ($length >= $free / 3 && $limit == 1) {
                    show_php_error(array("phperror" => "Could not get the query data"));
                }
                if ($length >= $free / 3) {
                    $limit = intval($limit / 2);
                }
                if ($length < $free / 3) {
                    break;
                }
            }
            // CONTINUE
            $query = "SELECT * FROM ${table} ORDER BY id ASC LIMIT ${offset},${limit}";
            $result = db_query($query);
            while ($row = db_fetch_row($result)) {
                $query = make_insert_query($table, $row) . ";\n";
                gzwrite($fp, $query);
            }
            db_free($result);
            $offset = $offset + $limit;
        }
    }
    gzclose($fp);
    // CONTINUE
    output_handler(array(
        "file" => $file,
        "type" => "application/octet-stream",
        "cache" => false,
        "extras" => array("Content-Type: application/force-download","Content-Type: application/download"),
        "name" => $name,
        "die" => false
    ));
    unlink($file);
    die();
}
