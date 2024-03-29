<?php

/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2024 by Josep Sanz Campderrós
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

$id_aplicacion = page2id(getParam("page"));
$id_registro = (getParam("id") == "session") ? getParam("id") : abs(intval(getParam("id")));
$cid = getParam("cid");

require_once "php/libaction.php";
$result = __download($id_aplicacion, $id_registro, $cid);
output_handler(array(
    "file" => $result["file"],
    "type" => $result["type"],
    "cache" => true,
    "name" => $result["name"]
));
