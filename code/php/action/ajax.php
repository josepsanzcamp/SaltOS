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

$config = xml2array("xml/ajax.xml");
if (eval_bool(getDefault("debug/actiondebug"))) {
    debug_dump(false);
}
$query = getParam("query", "query");
if (!isset($config[$query])) {
    die();
}
$config = eval_attr(array($query => $config[$query]));
if (eval_bool(getDefault("debug/actiondebug"))) {
    debug_dump();
}
$query = $config[$query];
$result = db_query($query);
$count = 0;
$_RESULT = array("rows" => array());
while ($row = db_fetch_row($result)) {
    foreach ($row as $key => $val) {
        if (substr($key, -7, 7) == "_base64" && file_exists($val) && is_file($val)) {
            $row[$key] = base64_encode(file_get_contents($val));
        }
    }
    $row["__ROW_NUMBER__"] = ++$count;
    set_array($_RESULT["rows"], "row", $row);
}
db_free($result);
$format = strtolower(getParam("format", "json"));
if ($format == "json") {
    $_RESULT["rows"] = array_values($_RESULT["rows"]);
    $buffer = json_encode($_RESULT);
    $format = "application/json";
} elseif ($format == "xml") {
    $buffer = __XML_HEADER__;
    $buffer .= array2xml($_RESULT);
    $format = "text/xml";
} elseif ($format == "plain") {
    $buffer = array();
    foreach ($_RESULT["rows"] as $row) {
        $buffer[] = implode("|", $row);
    }
    $buffer = implode("\n", $buffer);
    $format = "text/plain";
} else {
    die();
}
output_handler(array(
    "data" => $buffer,
    "type" => $format,
    "cache" => false
));
