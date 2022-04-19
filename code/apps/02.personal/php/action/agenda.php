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

// phpcs:disable Generic.Files.LineLength

if (!check_user()) {
    action_denied();
}

// GET IDS AND HASHS
$id_hash = getParam("id_hash");
$id_hash = explode(",", $id_hash);
foreach ($id_hash as $key => $val) {
    $id_hash[$key] = explode("_", $val);
}
// COMMON THINGS
$orig = array("/01/","/02/","/03/","/04/","/05/","/06/","/07/","/08/","/09/","/10/","/11/","/12/");
$dest = array(
    " " . mb_substr(LANG("enero"), 0, 3) . " ",
    " " . mb_substr(LANG("febrero"), 0, 3) . " ",
    " " . mb_substr(LANG("marzo"), 0, 3) . " ",
    " " . mb_substr(LANG("abril"), 0, 3) . " ",
    " " . mb_substr(LANG("mayo"), 0, 3) . " ",
    " " . mb_substr(LANG("junio"), 0, 3) . " ",
    " " . mb_substr(LANG("julio"), 0, 3) . " ",
    " " . mb_substr(LANG("agosto"), 0, 3) . " ",
    " " . mb_substr(LANG("setiembre"), 0, 3) . " ",
    " " . mb_substr(LANG("octubre"), 0, 3) . " ",
    " " . mb_substr(LANG("noviembre"), 0, 3) . " ",
    " " . mb_substr(LANG("diciembre"), 0, 3) . " "
);
$dias = array("",
    mb_substr(LANG("lunes"), 0, 3),
    mb_substr(LANG("martes"), 0, 3),
    mb_substr(LANG("miercoles"), 0, 3),
    mb_substr(LANG("jueves"), 0, 3),
    mb_substr(LANG("viernes"), 0, 3),
    mb_substr(LANG("sabado"), 0, 3),
    mb_substr(LANG("domingo"), 0, 3)
);
$today = current_date();
$yesterday = current_date(-86400);
$tomorrow = current_date(86400);
$dest2 = array(
    " " . LANG("enero") . " ",
    " " . LANG("febrero") . " ",
    " " . LANG("marzo") . " ",
    " " . LANG("abril") . " ",
    " " . LANG("mayo") . " ",
    " " . LANG("junio") . " ",
    " " . LANG("julio") . " ",
    " " . LANG("agosto") . " ",
    " " . LANG("setiembre") . " ",
    " " . LANG("octubre") . " ",
    " " . LANG("noviembre") . " ",
    " " . LANG("diciembre") . " "
);
$dias2 = array("",
    LANG("lunes"),
    LANG("martes"),
    LANG("miercoles"),
    LANG("jueves"),
    LANG("viernes"),
    LANG("sabado"),
    LANG("domingo")
);
// BUSCAR NOTIFICACIONES
$query = "SELECT 'dstart' type,
    '" . LANG_ESCAPE("notifyprev", "agenda") . "' title,
    a.id id,a.nombre nombre,a.lugar lugar,a.descripcion descripcion,dstart,dstop
    FROM tbl_agenda a
    LEFT JOIN tbl_registros f ON f.id_aplicacion='" . page2id("agenda") . "'
        AND f.id_registro=a.id
        AND f.first=1
    LEFT JOIN tbl_estados c ON a.id_estado=c.id
    WHERE f.id_usuario='" . current_user() . "'
        AND activo='1'
        AND notify_delay!='0'
        AND notify_dstart='0'
        AND UNIX_TIMESTAMP('" . current_datetime() . "') > UNIX_TIMESTAMP(dstart)+notify_delay*3600*notify_sign
    UNION
    SELECT 'dstop' type,
    '" . LANG_ESCAPE("notifypost", "agenda") . "' title,
    a.id id,a.nombre nombre,a.lugar lugar,a.descripcion descripcion,dstart,dstop
    FROM tbl_agenda a
    LEFT JOIN tbl_registros f ON f.id_aplicacion='" . page2id("agenda") . "'
        AND f.id_registro=a.id
        AND f.first=1
    LEFT JOIN tbl_estados c ON a.id_estado=c.id
    WHERE f.id_usuario='" . current_user() . "'
        AND activo='1'
        AND notify_dstop='0'
        AND UNIX_TIMESTAMP('" . current_datetime() . "') > UNIX_TIMESTAMP(dstop)
    ORDER BY dstart ASC";
$result = db_query($query);
while ($row = db_fetch_row($result)) {
    $unix = strtotime($row["dstart"]);
    $current = date("Y-m-d", $unix);
    $id = $row["id"];
    $type = $row["type"];
    $title = $row["title"];
    $nombre = $row["nombre"];
    $lugar = $row["lugar"];
    $descripcion = $row["descripcion"];
    $fecha = $dias[date("N", $unix)] . " " . str_replace($orig, $dest, date("d/m/Y", $unix));
    if ($current == $today) {
        $fecha = LANG("today", "agenda") . ", " . $fecha;
    }
    if ($current == $yesterday) {
        $fecha = LANG("yesterday", "agenda") . ", " . $fecha;
    }
    if ($current == $tomorrow) {
        $fecha = LANG("tomorrow", "agenda") . ", " . $fecha;
    }
    $horaini = substr($row["dstart"], 11, 5);
    $horafin = substr($row["dstop"], 11, 5);
    $fecha .= ", " . $horaini;
    if ($horaini != $horafin) {
        $fecha .= "-" . $horafin;
    }
    if ($lugar) {
        $lugar = ", ${lugar}";
    }
    $hash = md5(serialize(array($id,$type,$title,$nombre,$lugar,$descripcion,$fecha)));
    $msg = intelligence_cut("<b>${nombre}</b> (${fecha}${lugar}) ${descripcion}", 90);
    $urlview = "opencontent(\"?page=agenda&action=form&id=-$id\")";
    $urledit = "opencontent(\"?page=agenda&action=form&id=$id\")";
    $urlcopy = "opencontent(\"?page=agenda&action=form&id=0_copy_$id\")";
    $msg .= " [<a href='javascript:void(0)' onclick='$urlview'>" . LANG("view") . "</a>]";
    $msg .= " [<a href='javascript:void(0)' onclick='$urledit'>" . LANG("edit") . "</a>]";
    $msg .= " [<a href='javascript:void(0)' onclick='$urlcopy'>" . LANG("copy") . "</a>]";
    $urlrecv = "?action=ajax&query=agenda&type=$type&id=$id";
    $cancel = 0;
    foreach ($id_hash as $key => $val) {
        if ($id == $val[0] && $hash == $val[1]) {
            $cancel = 1;
        }
        if ($id == $val[0] && $hash != $val[1]) {
            javascript_template("$('.id_$id').remove()");
        }
        if ($id == $val[0]) {
            unset($id_hash[$key]);
        }
    }
    if (!$cancel) {
        $title = str_replace("'", "", $title);
        $msg = str_replace(array("'","\n","\r"), " ", $msg);
        javascript_template("notice('$title','$msg',true,function() { $.ajax({ url:'$urlrecv' }); },'ui-state-highlight id_$id hash_$hash');");
    }
}
db_free($result);
// OCULTAR NO ENCONTRADOS
foreach ($id_hash as $key => $val) {
    javascript_template("$('.id_${val[0]}').remove()");
}
javascript_headers();
die();
