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

if ($page == "agenda") {
    $restarted = 0;
    // REINICIAR LOS NOTIFY_DSTART
    $query = "SELECT a.id
        FROM tbl_agenda a
        LEFT JOIN tbl_registros f ON f.id_aplicacion='" . page2id("agenda") . "'
            AND f.id_registro=a.id
            AND f.first=1
        LEFT JOIN tbl_estados c ON a.id_estado=c.id
        WHERE f.id_usuario='" . current_user() . "'
            AND activo='1'
            AND notify_delay!='0'
            AND notify_dstart='1'
            AND UNIX_TIMESTAMP('" . current_datetime() . "')+0.0 >
                UNIX_TIMESTAMP(dstart)+notify_delay*3600.0*notify_sign
            AND UNIX_TIMESTAMP('" . current_datetime() . "') < UNIX_TIMESTAMP(dstop)";
    $result = db_query($query);
    while ($row = db_fetch_row($result)) {
        $id = $row["id"];
        $query = make_update_query("tbl_agenda", array(
            "notify_dstart" => 0
        ), "id='{$id}'");
        db_query($query);
        $restarted++;
    }
    db_free($result);
    // REINICIAR LOS NOTIFY_DSTOP
    $query = "SELECT a.id
        FROM tbl_agenda a
        LEFT JOIN tbl_registros f ON f.id_aplicacion='" . page2id("agenda") . "'
            AND f.id_registro=a.id
            AND f.first=1
        LEFT JOIN tbl_estados c ON a.id_estado=c.id
        WHERE f.id_usuario='" . current_user() . "'
            AND activo='1'
            AND notify_dstop='1'
            AND UNIX_TIMESTAMP('" . current_datetime() . "') > UNIX_TIMESTAMP(dstop)";
    $result = db_query($query);
    while ($row = db_fetch_row($result)) {
        $id = $row["id"];
        $query = make_update_query("tbl_agenda", array(
            "notify_dstop" => 0
        ), "id='{$id}'");
        db_query($query);
        $restarted++;
    }
    db_free($result);
    // VOLVER ATRAS
    if ($restarted) {
        session_alert(LANG("notifyclearok", "agenda") . " " . $restarted);
        javascript_template("check_agenda();");
    } else {
        session_alert(LANG("notifyclearko", "agenda"));
    }
    javascript_history(-1);
    die();
}
