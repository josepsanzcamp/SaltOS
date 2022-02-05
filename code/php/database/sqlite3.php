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
// phpcs:disable PSR1.Classes.ClassDeclaration
// phpcs:disable Squiz.Classes.ValidClassName
// phpcs:disable PSR1.Methods.CamelCapsMethodName

require_once "php/database/libsqlite.php";

class database_sqlite3
{
    private $link = null;

    public function __construct($args)
    {
        if (!class_exists("SQLite3")) {
            show_php_error(array(
                "phperror" => "Class SQLite3 not found",
                "details" => "Try to install php-sqlite package"
            ));
            return;
        }
        if (!file_exists($args["file"])) {
            show_php_error(array("phperror" => "File '" . $args["file"] . "' not found"));
            return;
        }
        if (!is_writable($args["file"])) {
            show_php_error(array("phperror" => "File '" . $args["file"] . "' not writable"));
            return;
        }
        try {
            $this->link = new SQLite3($args["file"]);
        } catch (Exception $e) {
            show_php_error(array("dberror" => $e->getMessage() . " (code " . $e->getCode() . ")"));
        }
        if ($this->link) {
            $this->link->busyTimeout(0);
            $this->db_query("PRAGMA cache_size=2000");
            $this->db_query("PRAGMA synchronous=OFF");
            $this->db_query("PRAGMA foreign_keys=OFF");
            if (!$this->__check("SELECT GROUP_CONCAT(1)")) {
                $this->link->createAggregate(
                    "GROUP_CONCAT",
                    "__libsqlite_group_concat_step",
                    "__libsqlite_group_concat_finalize"
                );
            }
            if (!$this->__check("SELECT REPLACE(1,2,3)")) {
                $this->link->createFunction("REPLACE", "__libsqlite_replace");
            }
            $this->link->createFunction("LPAD", "__libsqlite_lpad");
            $this->link->createFunction("CONCAT", "__libsqlite_concat");
            $this->link->createFunction("UNIX_TIMESTAMP", "__libsqlite_unix_timestamp");
            $this->link->createFunction("YEAR", "__libsqlite_year");
            $this->link->createFunction("MONTH", "__libsqlite_month");
            $this->link->createFunction("WEEK", "__libsqlite_week");
            $this->link->createFunction("TRUNCATE", "__libsqlite_truncate");
            $this->link->createFunction("DAY", "__libsqlite_day");
            $this->link->createFunction("DAYOFYEAR", "__libsqlite_dayofyear");
            $this->link->createFunction("DAYOFWEEK", "__libsqlite_dayofweek");
            $this->link->createFunction("HOUR", "__libsqlite_hour");
            $this->link->createFunction("MINUTE", "__libsqlite_minute");
            $this->link->createFunction("SECOND", "__libsqlite_second");
            $this->link->createFunction("MD5", "__libsqlite_md5");
            $this->link->createFunction("REPEAT", "__libsqlite_repeat");
            $this->link->createFunction("FIND_IN_SET", "__libsqlite_find_in_set");
            $this->link->createFunction("IF", "__libsqlite_if");
            $this->link->createFunction("POW", "__libsqlite_pow");
        }
    }

    private function __check($query)
    {
        capture_next_error();
        $this->db_query($query);
        $error = get_clear_error();
        return !$error ? true : false;
    }

    public function db_query($query, $fetch = "query")
    {
        $query = parse_query($query, "SQLITE");
        $result = array("total" => 0,"header" => array(),"rows" => array());
        if (!strlen(trim($query))) {
            return $result;
        }
        // TRICK TO DO THE STRIP SLASHES
        $pos = strpos($query, "\\");
        while ($pos !== false) {
            $extra = "";
            if ($query[$pos + 1] == "'") {
                $extra = "'";
            }
            if ($query[$pos + 1] == "%") {
                $extra = "\\";
            }
            $query = substr_replace($query, $extra, $pos, 1);
            $pos = strpos($query, "\\", $pos + 1);
        }
        // CONTINUE THE NORMAL OPERATION
        $timeout = getDefault("db/semaphoretimeout", 10000000);
        if (semaphore_acquire(__FUNCTION__, $timeout)) {
            // DO QUERY
            while (1) {
                try {
                    $stmt = $this->link->query($query);
                    break;
                } catch (Exception $e) {
                    if ($timeout <= 0) {
                        show_php_error(array("dberror" => $e->getMessage(),"query" => $query));
                        break;
                    } elseif (stripos($e->getMessage(), "database is locked") !== false) {
                        $timeout -= __semaphore_usleep(rand(0, 1000));
                    } elseif (stripos($e->getMessage(), "database schema has changed") !== false) {
                        $timeout -= __semaphore_usleep(rand(0, 1000));
                    } else {
                        show_php_error(array("dberror" => $e->getMessage(),"query" => $query));
                        break;
                    }
                }
            }
            unset($query); // TRICK TO RELEASE MEMORY
            semaphore_release(__FUNCTION__);
            // DUMP RESULT TO MATRIX
            if (isset($stmt) && $stmt && $stmt->numColumns()) {
                if ($fetch == "auto") {
                    $fetch = $stmt->numColumns() > 1 ? "query" : "column";
                }
                if ($fetch == "query") {
                    while ($row = $stmt->fetchArray(SQLITE3_ASSOC)) {
                        $result["rows"][] = $row;
                    }
                    $result["total"] = count($result["rows"]);
                    if ($result["total"] > 0) {
                        $result["header"] = array_keys($result["rows"][0]);
                    }
                }
                if ($fetch == "column") {
                    while ($row = $stmt->fetchArray(SQLITE3_NUM)) {
                        $result["rows"][] = $row[0];
                    }
                    $result["total"] = count($result["rows"]);
                    $result["header"] = array("column");
                }
                if ($fetch == "concat") {
                    if ($row = $stmt->fetchArray(SQLITE3_NUM)) {
                        $result["rows"][] = $row[0];
                    }
                    while ($row = $stmt->fetchArray(SQLITE3_NUM)) {
                        $result["rows"][0] .= "," . $row[0];
                    }
                    $result["total"] = count($result["rows"]);
                    $result["header"] = array("concat");
                }
            }
        } else {
            show_php_error(array("phperror" => "Could not acquire the semaphore","query" => $query));
        }
        return $result;
    }

    public function db_disconnect()
    {
        $this->link->close();
        $this->link = null;
    }
}
