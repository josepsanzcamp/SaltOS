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

if (getParam("action") == "sendmail") {
    if (getParam("default_0_id") == "0") {
        require_once "php/getmail.php";
        require_once "php/sendmail.php";
        // GET ALL DATA
        $prefix = "default_0_";
        $id_extra = explode("_", getParam($prefix . "id_extra"), 3);
        $id_cuenta = intval(getParam($prefix . "id_cuenta"));
        $para = getParam($prefix . "para");
        $cc = getParam($prefix . "cc");
        $bcc = getParam($prefix . "bcc");
        $subject = getParam($prefix . "subject");
        $body = getParam($prefix . "body");
        $state_crt = intval(getParam($prefix . "state_crt"));
        $priority = intval(getParam($prefix . "priority"));
        $sensitivity = intval(getParam($prefix . "sensitivity"));
        // SEARCH FROM
        $query = "SELECT CONCAT(email_name,' <',email_from,'>') email
            FROM tbl_usuarios_c
            WHERE id_usuario='" . current_user() . "'
                AND id='${id_cuenta}'";
        $de = execute_query($query);
        if (!$de) {
            javascript_error(LANG("msgfromkosendmail", "correo"));
            javascript_unloading();
            die();
        }
        // REMOVE THE SIGNATURE TAG IF EXISTS
        $body = str_replace(array("<signature>","</signature>"), "", $body);
        // CHECK FOR MOBILE DEVICES
        if (ismobile()) {
            $source = $body;
            $source = htmlentities($source, ENT_COMPAT, "UTF-8");
            $source = str_replace(array(" ","\t","\n"), array("&nbsp;",str_repeat("&nbsp;", 8),"<br/>"), $source);
            $body = __HTML_PAGE_OPEN__;
            $body .= __PLAIN_TEXT_OPEN__;
            $body .= $source;
            $body .= __PLAIN_TEXT_CLOSE__;
            $body .= __HTML_PAGE_CLOSE__;
        }
        // REPLACE SIGNATURE IF NEEDED AND ADD THE INLINE IMAGE
        $inlines = array();
        require_once "php/libaction.php";
        $file = __signature_getauto(__signature_getfile($id_cuenta));
        if ($file && isset($file["src"])) {
            $cid = md5($file["data"]);
            $prehash = md5($body);
            $file["src"] = str_replace("&", "&amp;", $file["src"]); // CKEDITOR CORRECTION
            $body = str_replace($file["src"], "cid:${cid}", $body);
            $posthash = md5($body);
            if ($prehash != $posthash) {
                $inlines[] = array(
                    "body" => $file["data"],
                    "cid" => $cid,
                    "cname" => $file["name"],
                    "ctype" => $file["type"]
                );
            }
        }
        // PREPARE THE INLINES IMAGES AND EMBEDDED ATTACHMENTS
        $attachs = array();
        if (isset($id_extra[1]) && in_array($id_extra[1], array("reply","replyall","forward"))) {
            $decoded = __getmail_getmime($id_extra[2]);
            $result2 = __getmail_getfullbody(__getmail_getnode("0", $decoded));
            $useimginline = eval_bool(getDefault("cache/useimginline"));
            foreach ($result2 as $index2 => $node2) {
                $disp2 = $node2["disp"];
                $type2 = $node2["type"];
                if (!__getmail_processplainhtml($disp2, $type2) && !__getmail_processmessage($disp2, $type2)) {
                    $cid2 = $node2["cid"];
                    if ($cid2 != "") {
                        $chash2 = $node2["chash"];
                        $prehash = md5($body);
                        if ($useimginline) {
                            $data = base64_encode($node2["body"]);
                            $data = "data:image/png;base64,${data}";
                            $body = str_replace($data, "cid:${cid2}", $body);
                        } else {
                            $url = "?action=getmail&id=${id_extra[2]}&cid=${chash2}";
                            $url = str_replace("&", "&amp;", $url); // CKEDITOR CORRECTION
                            $body = str_replace($url, "cid:${cid2}", $body);
                        }
                        $posthash = md5($body);
                        if ($prehash != $posthash) {
                            $inlines[] = __getmail_getcid(__getmail_getnode("0", $decoded), $chash2);
                        }
                    }
                }
                if ($id_extra[1] == "forward" && __getmail_processfile($disp2, $type2)) {
                    $cname2 = $node2["cname"];
                    if ($cname2 != "") {
                        $chash2 = $node2["chash"];
                        $delete = "files_old_${chash2}_fichero_del";
                        if (!getParam($delete)) {
                            $attachs[] = __getmail_getcid(__getmail_getnode("0", $decoded), $chash2);
                        }
                    }
                }
            }
        }
        // PREPARE THE SESSION ATTACHMENT (IF EXISTS)
        if (isset($id_extra[1]) && $id_extra[1] == "session") {
            sess_init();
            $session = $_SESSION["correo"];
            sess_close();
            if (!isset($session["files"])) {
                $session["files"] = array();
            }
            foreach ($session["files"] as $key => $file) {
                $delete = "files_old_${key}_fichero_del";
                if (!getParam($delete)) {
                    $attachs[] = array(
                        "body" => file_get_contents($file["file"]),
                        "cname" => $file["name"],
                        "ctype" => $file["mime"]
                    );
                }
            }
        }
        // PREPARE THE RECIPIENTS
        $recipients = array();
        $para = explode(";", $para);
        foreach ($para as $addr) {
            $addr = trim($addr);
            if ($addr != "") {
                $recipients[] = "to:" . $addr;
            }
        }
        $cc = explode(";", $cc);
        foreach ($cc as $addr) {
            $addr = trim($addr);
            if ($addr != "") {
                $recipients[] = "cc:" . $addr;
            }
        }
        $bcc = explode(";", $bcc);
        foreach ($bcc as $addr) {
            $addr = trim($addr);
            if ($addr != "") {
                $recipients[] = "bcc:" . $addr;
            }
        }
        // ADD EXTRAS IN THE RECIPIENTS
        if ($state_crt) {
            $recipients[] = "crt:" . $de;
        }
        $priorities = array(-1 => "5 (Low)",1 => "1 (High)");
        if (isset($priorities[$priority])) {
            $recipients[] = "priority:" . $priorities[$priority];
        }
        $sensitivities = array(1 => "Personal",2 => "Private",3 => "Company-Confidential");
        if (isset($sensitivities[$sensitivity])) {
            $recipients[] = "sensitivity:" . $sensitivities[$sensitivity];
        }
        // ADD UPLOADED ATTACHMENTS
        $files = array();
        foreach ($_FILES as $file) {
            if (isset($file["tmp_name"]) && $file["tmp_name"] != "" && file_exists($file["tmp_name"])) {
                if (!isset($file["name"])) {
                    $file["name"] = basename($file["tmp_name"]);
                }
                if (!isset($file["type"])) {
                    $file["type"] = saltos_content_type($file["tmp_name"]);
                }
                $files[] = array("file" => $file["tmp_name"],"name" => $file["name"],"mime" => $file["type"]);
            } else {
                if (isset($file["name"]) && $file["name"] != "") {
                    javascript_error(LANG("fileuploaderror") . $file["name"]);
                }
                if (isset($file["error"]) && $file["error"] != "") {
                    javascript_error(
                        LANG("fileuploaderror") . upload_error2string($file["error"]) . " (code " . $file["error"] . ")"
                    );
                }
                javascript_unloading();
                die();
            }
        }
        // ADD INLINES IMAGES
        foreach ($inlines as $inline) {
            $files[] = array(
                "data" => $inline["body"],
                "cid" => $inline["cid"],
                "name" => $inline["cname"],
                "mime" => $inline["ctype"]
            );
        }
        // ADD EMBEDDED ATTACHMENTS
        foreach ($attachs as $attach) {
            $files[] = array(
                "data" => $attach["body"],
                "name" => $attach["cname"],
                "mime" => $attach["ctype"]
            );
        }
        // DO THE SEND ACTION
        $send = sendmail($id_cuenta, $recipients, $subject, $body, $files);
        if ($send == "") {
            $query = "SELECT MAX(id) FROM tbl_correo WHERE id_cuenta='${id_cuenta}' AND is_outbox='1'";
            $last_id = execute_query($query);
            // SOME UPDATES
            if (isset($id_extra[1]) && in_array($id_extra[1], array("reply","replyall","forward"))) {
                __getmail_update("id_correo", $id_extra[2], $last_id);
                if ($id_extra[1] == "reply") {
                    $campo = "state_reply";
                }
                if ($id_extra[1] == "replyall") {
                    $campo = "state_reply";
                }
                if ($id_extra[1] == "forward") {
                    $campo = "state_forward";
                }
                __getmail_update($campo, 1, $id_extra[2]);
            }
            // FINISH THE ACTION
            session_alert(LANG("msgsendoksendmail", "correo"));
            $go = eval_bool(intval(getParam("returnhere")) ? "true" : "false") ? 0 : -1;
            javascript_history($go);
        } else {
            // CANCEL THE ACTION
            javascript_error($send);
            javascript_unloading();
        }
        die();
    }
    if (eval_bool(getDefault("debug/cancelsendmail"))) {
        die();
    }
    // CHECK THE SEMAPHORE
    $semaphore = array(getParam("action"),current_user());
    if (!semaphore_acquire($semaphore, getDefault("semaphoretimeout", 100000))) {
        if (!getParam("ajax")) {
            session_error(LANG("msgerrorsemaphore") . getParam("action"));
            javascript_history(-1);
        } else {
            javascript_error(LANG("msgerrorsemaphore") . getParam("action"));
        }
        die();
    }
    // BEGIN THE SPOOL OPERATION
    $query = "SELECT a.id,a.id_cuenta,a.uidl
        FROM tbl_correo a
        LEFT JOIN tbl_usuarios_c c ON c.id=a.id_cuenta
        WHERE c.id_usuario='" . current_user() . "'
            AND a.is_outbox='1'
            AND a.state_sent='0'";
    $result = execute_query_array($query);
    if (!count($result)) {
        if (!getParam("ajax")) {
            session_alert(LANG("msgnotsendfound", "correo"));
            javascript_history(-1);
        }
        semaphore_release($semaphore);
        javascript_headers();
        die();
    }
    set_include_path("lib/phpmailer" . PATH_SEPARATOR . get_include_path());
    require_once "src/PHPMailer.php";
    require_once "src/SMTP.php";
    require_once "php/getmail.php";
    $sended = 0;
    $haserror = 0;
    foreach ($result as $row) {
        if (time_get_usage() > getDefault("server/percentstop")) {
            break;
        }
        $last_id = $row["id"];
        $messageid = $row["id_cuenta"] . "/" . $row["uidl"];
        $file = get_directory("dirs/outboxdir") . $messageid . ".obj";
        if (file_exists($file)) {
            $mail = unserialize(file_get_contents($file));
            capture_next_error();
            $current = $mail->PostSend();
            $error = get_clear_error();
            if (words_exists("PostSend non-object", $error)) {
                __getmail_update("state_sent", 1, $last_id);
                __getmail_update("state_error", LANG("interrorsendmail", "correo"), $last_id);
                unlink($file);
                $haserror = 1;
            } else {
                if ($current !== true) {
                    $host = $mail->Host;
                    $port = $mail->Port;
                    $extra = $mail->SMTPSecure;
                    $user = $mail->Username;
                    $pass = $mail->Password;
                    // FIND ACCOUNT DATA
                    $query = "SELECT * FROM tbl_usuarios_c WHERE id='" . $row["id_cuenta"] . "'";
                    $result2 = execute_query($query);
                    $current_host = $result2["smtp_host"];
                    $current_port = $result2["smtp_port"] ? $result2["smtp_port"] : 25;
                    $current_extra = $result2["smtp_extra"];
                    $current_user = $result2["smtp_user"];
                    $current_pass = $result2["smtp_pass"];
                    // CONTINUE
                    $idem = 1;
                    if ($current_host != $host) {
                        $idem = 0;
                    }
                    if ($current_port != $port) {
                        $idem = 0;
                    }
                    if ($current_extra != $extra) {
                        $idem = 0;
                    }
                    if ($current_user != $user) {
                        $idem = 0;
                    }
                    if ($current_pass != $pass) {
                        $idem = 0;
                    }
                    if (!$idem) {
                        if (!in_array($current_host, array("mail","sendmail","qmail",""))) {
                            $mail->IsSMTP();
                            $mail->set("Host", $current_host);
                            $mail->set("Port", $current_port);
                            $mail->set("SMTPSecure", $current_extra);
                            $mail->set("Username", $current_user);
                            $mail->set("Password", $current_pass);
                            $mail->set("SMTPAuth", ($current_user != "" || $current_pass != ""));
                            $mail->set("Hostname", $current_host);
                        } else {
                            if ($current_host == "mail") {
                                $mail->IsMail();
                            } elseif ($current_host == "sendmail") {
                                $mail->IsSendmail();
                            } elseif ($current_host == "qmail") {
                                $mail->IsQmail();
                            }
                        }
                        capture_next_error();
                        $current = $mail->PostSend();
                        $error = get_clear_error();
                    }
                }
                if ($current !== true) {
                    if (words_exists("connection refused", $error)) {
                        $error = LANG("msgconnrefusedpop3email", "correo");
                    } elseif (words_exists("unable to connect", $error)) {
                        $error = LANG("msgconnerrorpop3email", "correo");
                    } else {
                        $orig = array("\n","\r","'","\"");
                        $dest = array(" ","","","");
                        $error = str_replace($orig, $dest, $mail->ErrorInfo);
                    }
                    __getmail_update("state_sent", 0, $last_id);
                    __getmail_update("state_error", $error, $last_id);
                    if (!getParam("ajax")) {
                        session_error(LANG("msgerrorsendmail", "correo") . $error);
                    } else {
                        javascript_error(LANG("msgerrorsendmail", "correo") . $error);
                    }
                    $haserror = 1;
                } else {
                    __getmail_update("state_sent", 1, $last_id);
                    __getmail_update("state_error", "", $last_id);
                    unlink($file);
                    $sended++;
                }
            }
        } else {
            __getmail_update("state_sent", 1, $last_id);
            __getmail_update("state_error", LANG("interrorsendmail", "correo"), $last_id);
            $haserror = 1;
        }
    }
    if (!getParam("ajax")) {
        if ($sended > 0) {
            session_alert($sended . LANG("msgtotalsendmail" . min($sended, 2), "correo"));
        }
        javascript_history(-1);
    } else {
        if ($sended > 0) {
            javascript_alert($sended . LANG("msgtotalsendmail" . min($sended, 2), "correo"));
            if (!$haserror) {
                javascript_settimeout("$('#enviar').addClass('ui-state-disabled');", 1000, "is_correo_list()");
            }
        }
        if ($sended > 0 || $haserror) {
            $condition = "update_correo_list()";
            javascript_history(0, $condition);
        }
    }
    // RELEASE THE SEMAPHORE
    semaphore_release($semaphore);
    javascript_headers();
    die();
}

if (getParam("page") == "correo") {
    $id_cuenta = intval(getParam("id_cuenta"));
    if (!$id_cuenta) {
        $query = "SELECT id
            FROM tbl_usuarios_c
            WHERE id_usuario='" . current_user() . "'
                AND email_disabled='0'
                AND smtp_host!=''
                AND email_default='1'
            LIMIT 1";
        $id_cuenta = execute_query($query);
    }
    if (!$id_cuenta) {
        $query = "SELECT id FROM (
            SELECT id,(
                SELECT COUNT(*)
                FROM tbl_correo_a
                WHERE id_correo IN (
                    SELECT id
                    FROM tbl_correo
                    WHERE id_cuenta=a.id
                        AND is_outbox=1
                )
                AND id_tipo IN (1,2,3,4)
            ) contador,
            email_default
            FROM tbl_usuarios_c a
            WHERE id_usuario='" . current_user() . "'
                AND email_disabled='0'
                AND smtp_host!=''
            ORDER BY email_default DESC,contador DESC
            LIMIT 1) z";
        $id_cuenta = execute_query($query);
    }
    $id_extra = explode("_", getParam("id"), 3);
    $to_extra = "";
    $cc_extra = "";
    $bcc_extra = "";
    $state_crt = "";
    $subject_extra = "";
    $body_extra = "";
    if (isset($id_extra[1]) && in_array($id_extra[1], array("reply","replyall","forward"))) {
        $query = "SELECT id_cuenta FROM tbl_correo WHERE id='${id_extra[2]}'";
        $result2 = execute_query($query);
        if ($result2 && $id_cuenta != $result2) {
            $id_cuenta = $result2;
        }
    }
    if (1) { // GET THE DEFAULT ADDMETOCC
        $query = "SELECT * FROM tbl_usuarios_c WHERE id_usuario='" . current_user() . "' AND id='$id_cuenta'";
        $result2 = execute_query($query);
        if ($result2 && $result2["email_addmetocc"]) {
            $cc_extra = $result2["email_name"] . " <" . $result2["email_from"] . ">; ";
        }
    }
    if (1) { // GET THE DEFAULT CRT
        $query = "SELECT * FROM tbl_usuarios_c WHERE id_usuario='" . current_user() . "' AND id='$id_cuenta'";
        $result2 = execute_query($query);
        if ($result2) {
            $state_crt = $result2["email_crt"];
        }
    }
    if (1) { // GET THE DEFAULT SIGNATURE
        require_once "php/libaction.php";
        $file = __signature_getauto(__signature_getfile($id_cuenta));
        $body_extra = "<br/><br/><signature>" . ($file ? $file["auto"] : "") . "</signature>";
    }
    if (isset($id_extra[1]) && in_array($id_extra[1], array("reply","replyall"))) {
        $query = "SELECT * FROM tbl_correo_a WHERE id_correo='${id_extra[2]}'";
        $result2 = execute_query_array($query);
        foreach ($result2 as $addr) {
            if ($addr["id_tipo"] == 6) {
                $finded_replyto = $addr;
            }
            if ($addr["id_tipo"] == 1) {
                $finded_from = $addr;
            }
        }
        if (isset($finded_replyto) || isset($finded_from)) {
            if (isset($finded_replyto)) {
                $finded = $finded_replyto;
            } elseif (isset($finded_from)) {
                $finded = $finded_from;
            }
            if ($finded["nombre"] != "") {
                $to_extra = $finded["nombre"] . " <" . $finded["valor"] . ">; ";
            } else {
                $to_extra = $finded["valor"] . "; ";
            }
        }
    }
    if (isset($id_extra[1]) && $id_extra[1] == "replyall") {
        if (isset($finded_replyto) && isset($finded_from)) {
            $finded_tocc = array();
            $finded_tocc[] = $finded_from;
        }
        foreach ($result2 as $addr) {
            if ($addr["id_tipo"] == 2 || $addr["id_tipo"] == 3) {
                if (!isset($finded_tocc)) {
                    $finded_tocc = array();
                }
                $finded_tocc[] = $addr;
            }
        }
        if (isset($finded_tocc)) {
            if (isset($finded)) {
                foreach ($finded_tocc as $key2 => $addr) {
                    if ($addr["valor"] == $finded["valor"]) {
                        unset($finded_tocc[$key2]);
                    }
                }
            }
            $query = "SELECT * FROM tbl_usuarios_c WHERE id_usuario='" . current_user() . "' AND id='$id_cuenta'";
            $result2 = execute_query_array($query);
            foreach ($result2 as $addr) {
                foreach ($finded_tocc as $key2 => $addr2) {
                    if ($addr2["valor"] == $addr["email_from"]) {
                        unset($finded_tocc[$key2]);
                    }
                }
            }
            foreach ($finded_tocc as $addr) {
                if ($addr["nombre"] != "") {
                    $cc_extra .= $addr["nombre"] . " <" . $addr["valor"] . ">; ";
                } else {
                    $cc_extra .= $addr["valor"] . "; ";
                }
            }
        }
    }
    if (isset($id_extra[1]) && $id_extra[1] == "forward") {
        $query = "SELECT * FROM tbl_correo_a WHERE id_correo='${id_extra[2]}'";
        $result2 = execute_query_array($query);
        foreach ($result2 as $addr) {
            if ($addr["id_tipo"] == 1) {
                $finded_from = $addr;
            }
        }
    }
    if (isset($id_extra[1]) && in_array($id_extra[1], array("reply","replyall","forward"))) {
        require_once "php/getmail.php";
        $query = "SELECT * FROM tbl_correo WHERE id='${id_extra[2]}'";
        $row2 = execute_query($query);
        if ($row2 && isset($row2["subject"])) {
            $subject_extra = $row2["subject"];
            $prefix = LANG($id_extra[1] . "subject");
            if (strncasecmp($subject_extra, $prefix, strlen($prefix)) != 0) {
                $subject_extra = $prefix . $subject_extra;
            }
        }
        if (isset($row2["datetime"]) && isset($finded_from)) {
            $oldhead = "";
            $oldhead .= __HTML_TEXT_OPEN__;
            $oldhead .= LANG("embeddedmessage");
            $oldhead .= __HTML_TEXT_CLOSE__;
            $oldhead = str_replace("#datetime#", $row2["datetime"], $oldhead);
            $oldhead = str_replace(
                "#fromname#",
                $finded_from["nombre"] ? $finded_from["nombre"] : $finded_from["valor"],
                $oldhead
            );
            $oldbody = "";
            $decoded = __getmail_getmime($id_extra[2]);
            if ($id_extra[1] == "forward") {
                $result2 = __getmail_getinfo(__getmail_getnode("0", $decoded));
                $lista = array("from","to","cc","bcc");
                foreach ($lista as $temp) {
                    unset($result2[$temp]);
                }
                foreach ($result2["emails"] as $email) {
                    if ($email["nombre"] != "") {
                        $email["valor"] = "${email["nombre"]} <${email["valor"]}>";
                    }
                    if (!isset($result2[$email["tipo"]])) {
                        $result2[$email["tipo"]] = array();
                    }
                    $result2[$email["tipo"]][] = $email["valor"];
                }
                if (isset($result2["from"])) {
                    $result2["from"] = implode("; ", $result2["from"]);
                }
                if (isset($result2["to"])) {
                    $result2["to"] = implode("; ", $result2["to"]);
                    $query = "SELECT email_from
                        FROM tbl_usuarios_c
                        WHERE id=(
                            SELECT id_cuenta
                            FROM tbl_correo
                            WHERE id='${id_extra[2]}')";
                    $result2["to"] = str_replace("<>", "<" . execute_query($query) . ">", $result2["to"]);
                }
                if (!isset($result2["to"])) {
                    $query = "SELECT CASE
                        WHEN (
                            SELECT email_name
                            FROM tbl_usuarios_c
                            WHERE id=(
                                SELECT id_cuenta
                                FROM tbl_correo
                                WHERE id='${id_extra[2]}'
                            )
                        )=''
                        THEN (
                            SELECT email_from
                            FROM tbl_usuarios_c
                            WHERE id=(
                                SELECT id_cuenta
                                FROM tbl_correo
                                WHERE id='${id_extra[2]}'
                            )
                        )
                        ELSE (
                            SELECT CONCAT(email_name,' <',email_from,'>')
                            FROM tbl_usuarios_c
                            WHERE id=(
                                SELECT id_cuenta
                                FROM tbl_correo
                                WHERE id='${id_extra[2]}'
                            )
                        ) END";
                    $result2["to"] = execute_query($query);
                }
                if (isset($result2["cc"])) {
                    $result2["cc"] = implode("; ", $result2["cc"]);
                }
                if (isset($result2["bcc"])) {
                    $result2["bcc"] = implode("; ", $result2["bcc"]);
                }
                $lista = array(
                    "from" => LANG("from", "correo"),
                    "to" => LANG("to", "correo"),
                    "cc" => LANG("cc", "correo"),
                    "bcc" => LANG("bcc", "correo"),
                    "datetime" => LANG("datetime", "correo"),
                    "subject" => LANG("subject", "correo")
                );
                if (!isset($result2["from"])) {
                    unset($lista["from"]);
                }
                if (!isset($result2["to"])) {
                    unset($lista["to"]);
                }
                if (!isset($result2["cc"])) {
                    unset($lista["cc"]);
                }
                if (!isset($result2["bcc"])) {
                    unset($lista["bcc"]);
                }
                if (!$result2["subject"]) {
                    $result2["subject"] = LANG("sinsubject", "correo");
                }
                $oldbody .= __HTML_BOX_OPEN__;
                foreach ($lista as $key2 => $val2) {
                    $result2[$key2] = str_replace(array("<",">"), array("&lt;","&gt;"), $result2[$key2]);
                    $oldbody .= __HTML_TEXT_OPEN__;
                    $oldbody .= $lista[$key2] . ": ";
                    $oldbody .= "<b>" . $result2[$key2] . "</b>";
                    $oldbody .= __HTML_TEXT_CLOSE__;
                }
                $first = 1;
                foreach ($result2["files"] as $file) {
                    $cname = $file["cname"];
                    $hsize = $file["hsize"];
                    if ($first) {
                        $oldbody .= __HTML_TEXT_OPEN__;
                        $oldbody .= LANG("attachments", "correo") . ": ";
                    } else {
                        $oldbody .= " | ";
                    }
                    $oldbody .= "<b>${cname}</b> (${hsize})";
                    $first = 0;
                }
                if (!$first) {
                    $oldbody .= __HTML_TEXT_CLOSE__;
                }
                $oldbody .= __HTML_BOX_CLOSE__;
                $oldbody .= __HTML_SEPARATOR__;
            }
            $result2 = __getmail_getfullbody(__getmail_getnode("0", $decoded));
            $first = 1;
            $useimginline = eval_bool(getDefault("cache/useimginline"));
            foreach ($result2 as $index => $node) {
                $disp = $node["disp"];
                $type = $node["type"];
                if (__getmail_processplainhtml($disp, $type)) {
                    $temp = $node["body"];
                    if ($type == "plain") {
                        $temp = wordwrap($temp, 120);
                        $temp = htmlentities($temp, ENT_COMPAT, "UTF-8");
                        $temp = str_replace(
                            array(" ","\t","\n"),
                            array("&nbsp;",str_repeat("&nbsp;", 8),"<br/>"),
                            $temp
                        );
                    }
                    if ($type == "html") {
                        $temp = remove_script_tag($temp);
                        $temp = remove_style_tag($temp);
                    }
                    foreach ($result2 as $index2 => $node2) {
                        $disp2 = $node2["disp"];
                        $type2 = $node2["type"];
                        if (
                            !__getmail_processplainhtml($disp2, $type2) &&
                            !__getmail_processmessage($disp2, $type2)
                        ) {
                            $cid2 = $node2["cid"];
                            if ($cid2 != "") {
                                $chash2 = $node2["chash"];
                                if ($useimginline) {
                                    $data = base64_encode($node2["body"]);
                                    $data = "data:image/png;base64,${data}";
                                    $temp = str_replace("cid:${cid2}", $data, $temp);
                                } else {
                                    $url = "?action=getmail&id=${id_extra[2]}&cid=${chash2}";
                                    $temp = str_replace("cid:${cid2}", $url, $temp);
                                }
                            }
                        }
                    }
                    if (!$first) {
                        $oldbody .= __HTML_SEPARATOR__;
                    }
                    if ($type == "plain") {
                        $oldbody .= __PLAIN_TEXT_OPEN__ . $temp . __PLAIN_TEXT_CLOSE__;
                    }
                    if ($type == "html") {
                        $oldbody .= __HTML_TEXT_OPEN__ . $temp . __HTML_TEXT_CLOSE__;
                    }
                    $first = 0;
                }
            }
            $body_extra = $body_extra . __HTML_NEWLINE__ . __HTML_NEWLINE__ . $oldhead . __HTML_NEWLINE__ . __HTML_NEWLINE__ . __BLOCKQUOTE_OPEN__ . $oldbody . __BLOCKQUOTE_CLOSE__;
            unset($oldhead); // TRICK TO RELEASE MEMORY
            unset($oldbody); // TRICK TO RELEASE MEMORY
            unset($decoded); // TRICK TO RELEASE MEMORY
        }
    }
    if (isset($id_extra[1]) && $id_extra[1] == "session") {
        sess_init();
        $session = $_SESSION["correo"];
        sess_close();
        $subject_extra = isset($session["subject"]) ? $session["subject"] : "";
        $body_extra = (isset($session["body"]) ? $session["body"] : "") . $body_extra;
    }
    if (isset($id_extra[1]) && $id_extra[1] == "mailto") {
        require_once "php/getmail.php";
        $to_extra = __getmail_rawurldecode($id_extra[2]) . "; ";
        foreach ($_GET as $key2 => $val2) {
            $key2 = strtolower($key2);
            if ($key2 == "to") {
                $to_extra = __getmail_rawurldecode($val2) . "; ";
            }
            if ($key2 == "subject") {
                $subject_extra = __getmail_rawurldecode($val2);
            }
            if ($key2 == "body") {
                $body_extra = __getmail_rawurldecode($val2) . $body_extra;
            }
        }
    }
    if (isset($id_extra[1]) && $id_extra[1] == "feed") {
        require_once "php/getmail.php";
        $query = "SELECT *,
            (SELECT title FROM tbl_usuarios_f WHERE id=id_feed) feed,
            (SELECT link FROM tbl_usuarios_f WHERE id=id_feed) link2
            FROM tbl_feeds
            WHERE id='${id_extra[2]}'";
        $row2 = execute_query($query);
        if ($row2) {
            $subject_extra = LANG("forwardsubject") . $row2["title"];
            $oldhead = "";
            $oldhead .= __HTML_TEXT_OPEN__;
            $oldhead .= LANG("embeddedmessage");
            $oldhead .= __HTML_TEXT_CLOSE__;
            $oldhead = str_replace("#datetime#", $row2["pubdate"], $oldhead);
            $oldhead = str_replace("#fromname#", $row2["feed"], $oldhead);
            $oldbody = "";
            $lista = array(
                "title" => array("lang" => LANG("title", "feeds"),"link" => ""),
                "pubdate" => array("lang" => LANG("pubdate", "feeds"),"link" => ""),
                "feed" => array("lang" => LANG("feed", "feeds"),"link" => "link2"),
                "link" => array("lang" => LANG("link", "feeds"),"link" => "link"),
            );
            $oldbody .= __HTML_BOX_OPEN__;
            foreach ($lista as $key2 => $val2) {
                $oldbody .= __HTML_TEXT_OPEN__;
                $oldbody .= $val2["lang"] . ": ";
                if ($val2["link"] != "") {
                    $oldbody .= "<a href='" . $row2[$val2["link"]] . "'>";
                }
                $oldbody .= "<b>" . $row2[$key2] . "</b>";
                if ($val2["link"] != "") {
                    $oldbody .= "</a>";
                }
                $oldbody .= __HTML_TEXT_CLOSE__;
            }
            $oldbody .= __HTML_BOX_CLOSE__;
            $oldbody .= __HTML_SEPARATOR__;
            $oldbody .= __HTML_TEXT_OPEN__;
            $oldbody .= $row2["description"];
            $oldbody .= __HTML_TEXT_CLOSE__;
            $body_extra = $body_extra . __HTML_NEWLINE__ . __HTML_NEWLINE__ . $oldhead . __HTML_NEWLINE__ . __HTML_NEWLINE__ . __BLOCKQUOTE_OPEN__ . $oldbody . __BLOCKQUOTE_CLOSE__;
            unset($oldhead); // TRICK TO RELEASE MEMORY
            unset($oldbody); // TRICK TO RELEASE MEMORY
        }
    }
    // CHECK FOR MOBILE DEVICES
    if (ismobile() && $body_extra != "") {
        require_once "php/getmail.php";
        $body_extra = "\n\n\n" . html2text($body_extra);
    }
    // CONTINUE
    set_array($rows[$key], "row", array("id" => 0,"id_extra" => implode("_", $id_extra),
        "id_cuenta" => $id_cuenta,"para" => $to_extra,"cc" => $cc_extra,"bcc" => $bcc_extra,
        "subject" => $subject_extra,"body" => $body_extra,
        "state_crt" => $state_crt,"priority" => 0,"sensitivity" => 0));
}
