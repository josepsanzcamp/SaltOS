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
    _action_login();
}

$page = getParam("page");
$id = intval(getParam("id"));

if (!$page || !check_user($page, "menu")) {
    _action_denied();
}

require_once "php/libaction.php";
$_LANG["default"] = "${page},menu,common";
$_CONFIG[$page] = xml2array("xml/${page}.xml");
$page = lastpage($page);

eval_files();
$config = getDefault("$page/$action");

$commit = 1;
if ($action == "insert") {
    $go = -2;
}
if ($action == "update") {
    $go = -2;
}
if ($action == "delete") {
    $go = -1;
}
if (eval_bool(intval(getParam("returnhere")) ? "true" : "false")) {
    $go = -1;
}
if (eval_bool(intval(getParam("returnback")) ? "true" : "false")) {
    $go = -2;
}
$semaphore = array($page,$action);
if (!semaphore_acquire($semaphore)) {
    show_php_error(array("phperror" => "Could not acquire the semaphore"));
}
foreach ($config as $query) {
    $inline = eval_attr($query);
    foreach ($inline as $query) {
        $query = trim($query);
        if ($query == "") {
            continue;
        }
        $is_select = strtoupper(substr($query, 0, 6)) == "SELECT";
        if ($is_select) {
            $result = db_query($query);
            $count = 0;
            while ($row = db_fetch_row($result)) {
                $row["__ROW_NUMBER__"] = ++$count;
                if (isset($row["action_delete"])) {
                    $delete = $row["action_delete"];
                    if (substr($delete, 0, 1) != "/") {
                        $delete = get_directory("dirs/filesdir") . $delete;
                    }
                    if (file_exists($delete) && is_file($delete)) {
                        unlink_protected($delete);
                    }
                }
                if (isset($row["action_error"])) {
                    $error = $row["action_error"];
                    session_error($error);
                }
                if (isset($row["action_alert"])) {
                    $alert = $row["action_alert"];
                    session_alert($alert);
                }
                if (isset($row["action_commit"])) {
                    $commit = $row["action_commit"];
                }
                if (isset($row["action_go"])) {
                    $go = $row["action_go"];
                }
                if (isset($row["action_include"])) {
                    $include = $row["action_include"];
                    $include = explode(",", $include);
                    foreach ($include as $file) {
                        if (!file_exists($file)) {
                            show_php_error(array("xmlerror" => "Include '$file' not found"));
                        }
                        require $file;
                    }
                }
                if (!$commit) {
                    break;
                }
            }
            db_free($result);
        } else {
            db_query($query);
        }
        if (!$commit) {
            break;
        }
    }
    if (!$commit) {
        break;
    }
}
semaphore_release($semaphore);
if (is_numeric($go)) {
    $go++;
    _javascript_history($go);
} else {
    _javascript_addcontent("update");
    _javascript_opencontent($go);
}
die();
