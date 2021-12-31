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

function make_indexing($id_aplicacion = null, $id_registro = null)
{
    // CHECK PARAMETERS
    if ($id_aplicacion === null) {
        $id_aplicacion = page2id(getParam("page"));
    }
    $tabla = id2table($id_aplicacion);
    if ($tabla == "") {
        return -1;
    }
    $subtablas = id2subtables($id_aplicacion);
    if ($id_registro === null) {
        $id_registro = execute_query("SELECT MAX(id) FROM ${tabla}");
    }
    if (is_string($id_registro) && strpos($id_registro, ",") !== false) {
        $id_registro = explode(",", $id_registro);
    }
    if (is_array($id_registro)) {
        $result = array();
        foreach ($id_registro as $id) {
            $result[] = make_indexing($id_aplicacion, $id);
        }
        return $result;
    }
    // BUSCAR SI EXISTE INDEXACION
    $page = id2page($id_aplicacion);
    $query = "SELECT id FROM idx_${page} WHERE id='${id_registro}'";
    $id_indexing = execute_query($query);
    // BUSCAR SI EXISTEN DATOS DE LA TABLA PRINCIPAL
    $query = "SELECT id FROM ${tabla} WHERE id='${id_registro}'";
    $id_data = execute_query($query);
    if (!$id_data) {
        if ($id_indexing) {
            $query = "DELETE FROM idx_${page} WHERE id='${id_indexing}'";
            db_query($query);
            return 3;
        } else {
            return -2;
        }
    }
    // CONTINUE
    $queries = array();
    // OBTENER DATOS DE LA TABLA PRINCIPAL
    $campos = __make_indexing_helper($tabla, $id_registro);
    foreach ($campos as $key => $val) {
        $campos[$key] = "IFNULL((${val}),'')";
    }
    $campos = "CONCAT(" . implode(",' ',", $campos) . ")";
    $query = "SELECT ${campos} FROM ${tabla} WHERE id='${id_registro}'";
    $queries[] = $query;
    // OBTENER DATOS DE LAS SUBTABLAS
    if ($subtablas != "") {
        foreach (explode(",", $subtablas) as $subtabla) {
            $tabla = strtok($subtabla, "(");
            $campo = strtok(")");
            $campos = __make_indexing_helper($tabla);
            foreach ($campos as $key => $val) {
                $campos[$key] = "IFNULL((${val}),'')";
            }
            $campos = "GROUP_CONCAT(CONCAT(" . implode(",' ',", $campos) . "))";
            $query = "SELECT ${campos} FROM ${tabla} WHERE ${campo}='${id_registro}'";
            $queries[] = $query;
        }
    }
    // OBTENER DATOS DE LAS TABLAS GENERICAS
    $tablas = array("tbl_ficheros","tbl_comentarios");
    foreach ($tablas as $tabla) {
        $campos = __make_indexing_helper($tabla);
        foreach ($campos as $key => $val) {
            $campos[$key] = "IFNULL((${val}),'')";
        }
        $campos = "GROUP_CONCAT(CONCAT(" . implode(",' ',", $campos) . "))";
        $query = "SELECT ${campos}
            FROM ${tabla}
            WHERE id_aplicacion='${id_aplicacion}'
                AND id_registro='${id_registro}'";
        $queries[] = $query;
    }
    // PREPARAR QUERY PRINCIPAL
    foreach ($queries as $key => $val) {
        $queries[$key] = "IFNULL((${val}),'')";
    }
    $search = "CONCAT(" . implode(",' ',", $queries) . ")";
    // AÑADIR A LA TABLA INDEXING
    if ($id_indexing) {
        $query = "UPDATE idx_${page} SET search=${search} WHERE id=${id_indexing}";
        db_query_protected($query);
        return 2;
    } else {
        $query = "REPLACE INTO idx_${page}(id,search) VALUES(${id_registro},${search})";
        db_query_protected($query);
        return 1;
    }
}

function __make_indexing_helper($tabla, $id = "")
{
    static $cache = array();
    $hash = md5(serialize(array($tabla,$id)));
    if (isset($cache[$hash])) {
        return $cache[$hash];
    }
    static $tables = null;
    static $types = null;
    static $fields = null;
    static $campos = null;
    if ($tables === null) {
        $dbschema = eval_attr(xml2array("xml/dbschema.xml"));
        $tables = array();
        $types = array();
        $fields = array();
        if (is_array($dbschema) && isset($dbschema["tables"]) && is_array($dbschema["tables"])) {
            foreach ($dbschema["tables"] as $tablespec) {
                $tables[$tablespec["name"]] = array();
                $types[$tablespec["name"]] = array();
                $fields[$tablespec["name"]] = array();
                foreach ($tablespec["fields"] as $fieldspec) {
                    if (!isset($fieldspec["fkey"])) {
                        $fieldspec["fkey"] = "";
                    }
                    if (!isset($fieldspec["fcheck"])) {
                        $fieldspec["fcheck"] = "true";
                    }
                    if ($fieldspec["fkey"] != "" && eval_bool($fieldspec["fcheck"])) {
                        $tables[$tablespec["name"]][$fieldspec["name"]] = $fieldspec["fkey"];
                        $types[$tablespec["name"]][$fieldspec["name"]] = get_field_type($fieldspec["type"]);
                    }
                    $fields[$tablespec["name"]][] = $fieldspec["name"];
                }
            }
        }
    }
    if ($campos === null) {
        $dbstatic = eval_attr(xml2array("xml/dbstatic.xml"));
        $campos = array();
        if (is_array($dbstatic) && isset($dbstatic["tbl_aplicaciones"]) && is_array($dbstatic["tbl_aplicaciones"])) {
            foreach ($dbstatic["tbl_aplicaciones"] as $row) {
                if (isset($row["tabla"]) && isset($row["campo"])) {
                    if (substr($row["campo"], 0, 1) == '"' && substr($row["campo"], -1, 1) == '"') {
                        $row["campo"] = eval_protected($row["campo"]);
                    }
                    $campos[$row["tabla"]] = $row["campo"];
                }
            }
        }
    }
    if (!isset($fields[$tabla])) {
        $fields[$tabla] = array();
        foreach (get_fields($tabla) as $field) {
            $fields[$tabla][] = $field["name"];
        }
    }
    $result = $fields[$tabla];
    $result[] = "LPAD(id," . intval(CONFIG("zero_padding_digits")) . ",0)";
    if (isset($campos[$tabla])) {
        $result[] = $campos[$tabla];
    }
    if (isset($tables[$tabla])) {
        foreach ($tables[$tabla] as $key => $val) {
            if (isset($campos[$val])) {
                $campo = $campos[$val];
            } elseif (isset($fields[$val])) {
                $campo = "CONCAT(" . implode(",' ',", $fields[$val]) . ")";
            } else {
                $campo = "";
            }
            $type = $types[$tabla][$key];
            if ($type == "int") {
                if ($id == "") {
                    $where = "${val}.id=${key}";
                } else {
                    $where = "${val}.id=(SELECT ${key} FROM ${tabla} WHERE id=${id})";
                }
            } elseif ($type == "string") {
                if ($id == "") {
                    $where = "FIND_IN_SET(${val}.id,${key})";
                } else {
                    $where = "FIND_IN_SET(${val}.id,(SELECT ${key} FROM ${tabla} WHERE id=${id}))";
                }
                $campo = "GROUP_CONCAT(${campo})";
            } else {
                $where = "";
            }
            if ($campo != "" && $where != "") {
                $result[] = "(SELECT ${campo} FROM ${val} WHERE ${where})";
            }
        }
    }
    $cache[$hash] = $result;
    return $result;
}
