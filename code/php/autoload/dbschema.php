<?php

/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2023 by Josep Sanz Campderrós
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

function db_schema()
{
    if (!eval_bool(getDefault("db/dbschema"))) {
        return;
    }
    capture_next_error();
    $hash1 = CONFIG("xml/dbschema.xml");
    get_clear_error();
    $hash2 = md5(serialize(array(
        xml2array(detect_apps_files("xml/dbschema.xml")),
        xml2array(detect_apps_files("xml/dbstatic.xml"))
    )));
    if ($hash1 != $hash2) {
        if (!semaphore_acquire(array("db_schema","db_static"), getDefault("semaphoretimeout", 100000))) {
            return;
        }
        $dbschema = __get_dbschema_with_indexing(
            eval_attr(xml_join(xml2array(detect_apps_files("xml/dbschema.xml")))),
            eval_attr(xml_join(xml2array(detect_apps_files("xml/dbstatic.xml"))))
        );
        if (is_array($dbschema) && isset($dbschema["tables"]) && is_array($dbschema["tables"])) {
            $tables1 = get_tables();
            $tables2 = get_tables_from_dbschema();
            if (isset($dbschema["excludes"]) && is_array($dbschema["excludes"])) {
                foreach ($dbschema["excludes"] as $exclude) {
                    foreach ($tables1 as $key => $val) {
                        if ($exclude["name"] == $val) {
                            unset($tables1[$key]);
                        }
                    }
                    foreach ($tables2 as $key => $val) {
                        if ($exclude["name"] == $val) {
                            unset($tables2[$key]);
                        }
                    }
                }
            }
            foreach ($tables1 as $table) {
                $isbackup = (substr($table, 0, 2) == "__" && substr($table, -2, 2) == "__");
                if (!$isbackup && !in_array($table, $tables2)) {
                    $backup = "__{$table}__";
                    db_query(sql_alter_table($table, $backup));
                }
            }
            foreach ($dbschema["tables"] as $tablespec) {
                $table = $tablespec["name"];
                $backup = "__{$table}__";
                if (in_array($table, $tables1)) {
                    $fields1 = get_fields($table);
                    $fields2 = get_fields_from_dbschema($table);
                    $hash3 = md5(serialize($fields1));
                    $hash4 = md5(serialize($fields2));
                    if ($hash3 != $hash4) {
                        db_query(sql_alter_table($table, $backup));
                        db_query(sql_create_table($tablespec));
                        db_query(sql_insert_from_select($table, $backup));
                        db_query(sql_drop_table($backup));
                    }
                } elseif (in_array($backup, $tables1)) {
                    $fields1 = get_fields($backup);
                    $fields2 = get_fields_from_dbschema($table);
                    $hash3 = md5(serialize($fields1));
                    $hash4 = md5(serialize($fields2));
                    if ($hash3 != $hash4) {
                        db_query(sql_create_table($tablespec));
                        db_query(sql_insert_from_select($table, $backup));
                        db_query(sql_drop_table($backup));
                    } else {
                        db_query(sql_alter_table($backup, $table));
                    }
                } else {
                    db_query(sql_create_table($tablespec));
                }
                if (isset($dbschema["indexes"]) && is_array($dbschema["indexes"])) {
                    $indexes1 = get_indexes($table);
                    $indexes2 = array();
                    foreach ($dbschema["indexes"] as $indexspec) {
                        if ($indexspec["table"] == $table) {
                            $indexes2[$indexspec["name"]] = array();
                            foreach ($indexspec["fields"] as $fieldspec) {
                                $indexes2[$indexspec["name"]][] = $fieldspec["name"];
                            }
                        }
                    }
                    foreach ($indexes1 as $index => $fields) {
                        if (!array_key_exists($index, $indexes2)) {
                            db_query(sql_drop_index($index, $table));
                        }
                    }
                    foreach ($dbschema["indexes"] as $indexspec) {
                        if ($indexspec["table"] == $table) {
                            $index = $indexspec["name"];
                            if (array_key_exists($index, $indexes1)) {
                                $fields1 = $indexes1[$index];
                                $fields2 = $indexes2[$index];
                                $hash3 = md5(serialize($fields1));
                                $hash4 = md5(serialize($fields2));
                                if ($hash3 != $hash4) {
                                    db_query(sql_drop_index($index, $table));
                                    db_query(sql_create_index($indexspec));
                                }
                            } else {
                                db_query(sql_create_index($indexspec));
                            }
                        }
                    }
                }
            }
        }
        setConfig("xml/dbschema.xml", $hash2);
        semaphore_release(array("db_schema","db_static"));
    }
}

function db_static()
{
    if (!eval_bool(getDefault("db/dbstatic"))) {
        return;
    }
    $hash1 = CONFIG("xml/dbstatic.xml");
    $hash2 = md5(serialize(xml2array(detect_apps_files("xml/dbstatic.xml"))));
    if ($hash1 != $hash2) {
        if (!semaphore_acquire(array("db_schema","db_static"), getDefault("semaphoretimeout", 100000))) {
            return;
        }
        $dbstatic = eval_attr(xml_join(xml2array(detect_apps_files("xml/dbstatic.xml"))));
        if (is_array($dbstatic)) {
            foreach ($dbstatic as $table => $rows) {
                $query = "DELETE FROM {$table}";
                db_query($query);
                foreach ($rows as $row) {
                    __db_static_helper($table, $row);
                }
            }
        }
        __db_static_integrity();
        setConfig("xml/dbstatic.xml", $hash2);
        semaphore_release(array("db_schema","db_static"));
    }
}

function __db_static_helper($table, $row)
{
    $fields = getDefault("db/dbfields");
    $found = "";
    if (is_array($fields)) {
        foreach ($fields as $field) {
            if (isset($row[$field]) && strpos($row[$field], ",") !== false) {
                $found = $field;
                break;
            }
        }
    }
    if ($found != "") {
        $a = explode(",", $row[$field]);
        foreach ($a as $b) {
            $row[$field] = $b;
            __db_static_helper($table, $row);
        }
    } else {
        $query = make_insert_query($table, $row);
        db_query($query);
    }
}

function __db_static_integrity()
{
    $query = "SELECT id FROM tbl_aplicaciones WHERE id NOT IN (
            SELECT id_aplicacion FROM tbl_aplicaciones_i
            UNION
            SELECT id_aplicacion FROM tbl_aplicaciones_p)";
    $ids = execute_query_array($query);
    if (count($ids)) {
        show_php_error(array(
            "phperror" => "Found the following apps without permissions: " . implode(", ", $ids)
        ));
    }
    $query = "SELECT id_aplicacion FROM tbl_aplicaciones_i
        WHERE id_aplicacion NOT IN (SELECT id FROM tbl_aplicaciones)
        UNION
        SELECT id_aplicacion FROM tbl_aplicaciones_p
        WHERE id_aplicacion NOT IN (SELECT id FROM tbl_aplicaciones)";
    $ids = execute_query_array($query);
    if (count($ids)) {
        show_php_error(array(
            "phperror" => "Found the following permissions without apps: " . implode(", ", $ids)
        ));
    }
    $query = "SELECT id_aplicacion FROM tbl_aplicaciones_i
        GROUP BY id_aplicacion,id_permiso HAVING COUNT(*)>1
        UNION
        SELECT id_aplicacion FROM tbl_aplicaciones_p
        GROUP BY id_aplicacion,id_permiso HAVING COUNT(*)>1";
    $ids = execute_query_array($query);
    if (count($ids)) {
        show_php_error(array(
            "phperror" => "Found the following apps with repeated permissions: " . implode(", ", $ids)
        ));
    }
}

function __get_dbschema_with_indexing($dbschema, $dbstatic)
{
    // SOME CHECKS
    if (!is_array($dbschema)) {
        return $dbschema;
    }
    if (!isset($dbschema["tables"])) {
        return $dbschema;
    }
    if (!is_array($dbschema["tables"])) {
        return $dbschema;
    }
    if (!is_array($dbstatic)) {
        return $dbschema;
    }
    if (!isset($dbstatic["tbl_aplicaciones"])) {
        return $dbschema;
    }
    if (!is_array($dbstatic["tbl_aplicaciones"])) {
        return $dbschema;
    }
    // CONTINUE
    foreach ($dbstatic["tbl_aplicaciones"] as $row) {
        if (isset($row["tabla"]) && $row["tabla"] != "") {
            $codigo = $row["codigo"];
            set_array($dbschema["tables"], "table", array(
                "name" => "idx_{$codigo}",
                "fields" => array(
                    "field" => array(
                        "name" => "id",
                        "type" => "/*MYSQL INT(11) *//*SQLITE INTEGER */",
                        "pkey" => "true",
                    ),
                    "field#2" => array(
                        "name" => "search",
                        "type" => "MEDIUMTEXT",
                    )
                )
            ));
            set_array($dbschema["indexes"], "index", array(
                "table" => "idx_{$codigo}",
                "fulltext" => "true",
                "fields" => array(
                    "field" => array(
                        "name" => "search",
                    )
                )
            ));
        }
    }
    if (!isset($dbschema["indexes"])) {
        return $dbschema;
    }
    if (!is_array($dbschema["indexes"])) {
        return $dbschema;
    }
    foreach ($dbschema["indexes"] as $key => $val) {
        $dbschema["indexes"][$key]["name"] =
            substr($val["table"] . "_" . implode("_", array_column($val["fields"], "name")), 0, 64);
    }
    foreach ($dbschema["tables"] as $tablespec) {
        foreach ($tablespec["fields"] as $field) {
            if (isset($field["fkey"]) && $field["fkey"] != "") {
                set_array($dbschema["indexes"], "index", array(
                    "name" => $tablespec["name"] . "_" . $field["name"],
                    "table" => $tablespec["name"],
                    "fields" => array(
                        "field" => array(
                            "name" => $field["name"]
                        )
                    )
                ));
            }
        }
    }
    return $dbschema;
}
