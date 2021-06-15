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

// phpcs:disable PSR1.Classes.ClassDeclaration
// phpcs:disable Squiz.Classes.ValidClassName
// phpcs:disable PSR1.Methods.CamelCapsMethodName

class database_mssql
{
    private $link = null;

    public function __construct($args)
    {
        if (!function_exists("mssql_connect")) {
            show_php_error(array(
                "phperror" => "mssql_connect not found",
                "details" => "Try to install php-mssql package"
            ));
            return;
        }
        $this->link = mssql_connect($args["host"] . ":" . $args["port"], $args["user"], $args["pass"]);
        if ($this->link === false) {
            show_php_error(array("dberror" => mssql_get_last_message()));
        }
        if (!mssql_select_db($args["name"], $this->link)) {
            show_php_error(array("dberror" => mssql_get_last_message()));
        }
    }

    public function db_query($query, $fetch = "query")
    {
        $query = parse_query($query, "MSSQL");
        $result = array("total" => 0,"header" => array(),"rows" => array());
        if (!strlen(trim($query))) {
            return $result;
        }
        // DO QUERY
        $query = utf8_decode($query);
        $stmt = mssql_query($query, $this->link);
        if ($stmt === false) {
            show_php_error(array("dberror" => mssql_get_last_message(),"query" => $query));
        }
        unset($query); // TRICK TO RELEASE MEMORY
        // DUMP RESULT TO MATRIX
        if (!is_bool($stmt) && mssql_num_fields($stmt)) {
            if ($fetch == "auto") {
                $fetch = mssql_num_fields($stmt) > 1 ? "query" : "column";
            }
            if ($fetch == "query") {
                while ($row = mssql_fetch_assoc($stmt)) {
                    foreach ($row as $key => $val) {
                        $row[$key] = utf8_encode($val);
                    }
                    $result["rows"][] = $row;
                }
                $result["total"] = count($result["rows"]);
                if ($result["total"] > 0) {
                    $result["header"] = array_keys($result["rows"][0]);
                }
                mssql_free_result($stmt);
            }
            if ($fetch == "column") {
                while ($row = mssql_fetch_row($stmt)) {
                    $result["rows"][] = utf8_encode($row[0]);
                }
                $result["total"] = count($result["rows"]);
                $result["header"] = array("__a__");
                mssql_free_result($stmt);
            }
        }
        return $result;
    }

    public function db_disconnect()
    {
        mssql_close($this->link);
        $this->link = null;
    }
}
