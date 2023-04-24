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

if (!check_user()) {
    action_denied();
}

require_once "php/report.php";
require_once "php/sendmail.php";
// DATOS SMPT
if (!CONFIG("id_cuenta_support")) {
    session_error(LANG("msgnotsmtpemail", "support"));
    javascript_history(-1);
    die();
}
// DATOS EMAIL
if (!CONFIG("email_support")) {
    session_error(LANG("msgnotemailsupport", "support"));
    javascript_history(-1);
    die();
}
$to = array();
foreach (explode(";", CONFIG("email_support")) as $addr) {
    $addr = trim($addr);
    list($addr,$addrname) = __sendmail_parser($addr);
    if ($addr != "") {
        if ($addrname != "") {
            $to[] = "to:" . $addrname . " <" . $addr . ">";
        }
        if ($addrname == "") {
            $to[] = "to:" . LANG("contact", "support") . " <" . $addr . ">";
        }
    }
}
$contact = LANG("contact", "support");
$nombre = getParam("default_0_nombre");
$subject = getParam("default_0_subject");
$comentarios = getParam("default_0_comentarios");
if (!$subject) {
    session_error(LANG("msgnotsubject", "support"));
} elseif (!$comentarios) {
    session_error(LANG("msgnotcomments", "support"));
} else {
    $body = __report_begin($contact);
    $body .= __report_text(LANG("nombre", "support"), $nombre);
    $body .= __report_text(LANG("subject", "support"), $subject);
    $body .= __report_textarea(LANG("comentarios", "support"), $comentarios, false);
    $body .= __report_end();
    // PARA DEBUGAR
    //~ echo $body;die();
    // ENVIAR EMAIL
    $send = sendmail(CONFIG("id_cuenta_support"), $to, $contact . ": " . $subject, $body);
    if ($send != "") {
        session_error($send);
    } else {
        session_alert(LANG("msgsendemail", "support"));
    }
}
javascript_history(-1);
die();
