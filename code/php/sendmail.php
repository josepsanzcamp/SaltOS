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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

function sendmail($id_cuenta, $to, $subject, $body, $files = "")
{
    require_once "lib/phpmailer/vendor/autoload.php";
    require_once "php/getmail.php";
    // CHECK FOR SPECIAL ID_CUENTA CASE
    if (is_array($id_cuenta)) {
        if (count($id_cuenta) != 2) {
            return "id_cuenta error1";
        }
        list($id_cuenta0,$id_cuenta1) = array_values($id_cuenta);
        if (is_numeric($id_cuenta1) && is_string($id_cuenta0)) {
            list($id_cuenta0,$id_cuenta1) = array($id_cuenta1,$id_cuenta0);
        }
        if (!is_numeric($id_cuenta0) || !is_string($id_cuenta1)) {
            return "id_cuenta error2";
        }
    }
    // FIND ACCOUNT DATA
    if (isset($id_cuenta0)) {
        $id_cuenta = $id_cuenta0;
    }
    $query = "SELECT * FROM tbl_usuarios_c WHERE id='$id_cuenta'";
    $result = execute_query($query);
    if (!isset($result["id"])) {
        return "id not found";
    }
    if ($result["email_disabled"]) {
        return "email disabled";
    }
    $host = $result["smtp_host"];
    $port = $result["smtp_port"];
    $extra = $result["smtp_extra"];
    $user = $result["smtp_user"];
    $pass = $result["smtp_pass"];
    $from = $result["email_from"];
    $fromname = $result["email_name"];
    // CONTINUE
    $mail = new PHPMailer();
    if (!$mail->set("XMailer", get_name_version_revision())) {
        return $mail->ErrorInfo;
    }
    if (!$mail->AddCustomHeader("X-Originating-IP", getServer("REMOTE_ADDR"))) {
        if ($mail->ErrorInfo) {
            return $mail->ErrorInfo;
        }
    }
    if (!$mail->SetLanguage("es")) {
        return $mail->ErrorInfo;
    }
    if (!$mail->set("CharSet", "UTF-8")) {
        return $mail->ErrorInfo;
    }
    if (isset($id_cuenta1)) {
        $fromname = $id_cuenta1;
    }
    if (!$mail->SetFrom($from, $fromname)) {
        return $mail->ErrorInfo;
    }
    if (!$mail->set("WordWrap", 50)) {
        return $mail->ErrorInfo;
    }
    if (
        !$mail->set("SMTPOptions", array("ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
            "allow_self_signed" => true
        )))
    ) {
        return $mail->ErrorInfo;
    }
    $mail->IsHTML();
    if (!in_array($host, array("mail","sendmail","qmail",""))) {
        $mail->IsSMTP();
        if (!$mail->set("Host", $host)) {
            return $mail->ErrorInfo;
        }
        if ($port != "") {
            if (!$mail->set("Port", $port)) {
                return $mail->ErrorInfo;
            }
        }
        if ($extra != "") {
            if (!$mail->set("SMTPSecure", $extra)) {
                return $mail->ErrorInfo;
            }
        }
        if (!$mail->set("Username", $user)) {
            return $mail->ErrorInfo;
        }
        if (!$mail->set("Password", $pass)) {
            return $mail->ErrorInfo;
        }
        if (!$mail->set("SMTPAuth", ($user != "" || $pass != ""))) {
            return $mail->ErrorInfo;
        }
        if (!$mail->set("Hostname", $host)) {
            return $mail->ErrorInfo;
        }
    } else {
        if ($host == "mail") {
            $mail->IsMail();
        } elseif ($host == "sendmail") {
            $mail->IsSendmail();
        } elseif ($host == "qmail") {
            $mail->IsQmail();
        }
    }
    if (!$mail->set("Subject", $subject)) {
        return $mail->ErrorInfo;
    }
    if (!$mail->set("Body", $body)) {
        return $mail->ErrorInfo;
    }
    if (!$mail->set("AltBody", html2text($body))) {
        return $mail->ErrorInfo;
    }
    if (is_array($files)) {
        foreach ($files as $file) {
            if (isset($file["data"]) && !isset($file["cid"])) {
                $mail->AddStringAttachment($file["data"], $file["name"], "base64", $file["mime"]);
            }
            if (isset($file["file"]) && !isset($file["cid"])) {
                if (!$mail->AddAttachment($file["file"], $file["name"], "base64", $file["mime"])) {
                    return $mail->ErrorInfo;
                }
            }
            if (isset($file["data"]) && isset($file["cid"])) {
                $mail->AddStringEmbeddedImage($file["data"], $file["cid"], $file["name"], "base64", $file["mime"]);
            }
            if (isset($file["file"]) && isset($file["cid"])) {
                if (!$mail->AddEmbeddedImage($file["file"], $file["cid"], $file["name"], "base64", $file["mime"])) {
                    return $mail->ErrorInfo;
                }
            }
        }
    }
    $bcc = array();
    if (is_array($to)) {
        $valids = array("to:","cc:","bcc:","crt:","priority:","sensitivity:","replyto:");
        foreach ($to as $addr) {
            $type = $valids[0];
            foreach ($valids as $valid) {
                if (strncasecmp($addr, $valid, strlen($valid)) == 0) {
                    $type = $valid;
                    $addr = substr($addr, strlen($type));
                    break;
                }
            }
            // EXTRA FOR POPULATE $bcc
            if ($type == $valids[2]) {
                $bcc[] = $addr;
            }
            // CONTINUE
            list($addr,$addrname) = __sendmail_parser($addr);
            if ($type == $valids[0]) {
                if (!$mail->AddAddress($addr, $addrname)) {
                    if ($mail->ErrorInfo) {
                                        return $mail->ErrorInfo;
                    }
                }
            }
            if ($type == $valids[1]) {
                if (!$mail->AddCC($addr, $addrname)) {
                    if ($mail->ErrorInfo) {
                                        return $mail->ErrorInfo;
                    }
                }
            }
            if ($type == $valids[2]) {
                if (!$mail->AddBCC($addr, $addrname)) {
                    if ($mail->ErrorInfo) {
                                        return $mail->ErrorInfo;
                    }
                }
            }
            if ($type == $valids[3]) {
                if (!$mail->set("ConfirmReadingTo", $addr)) {
                    if ($mail->ErrorInfo) {
                                        return $mail->ErrorInfo;
                    }
                }
            }
            if ($type == $valids[4]) {
                if (!$mail->set("Priority", $addr)) {
                    if ($mail->ErrorInfo) {
                                        return $mail->ErrorInfo;
                    }
                }
            }
            if ($type == $valids[5]) {
                if (!$mail->AddCustomHeader("Sensitivity", $addr)) {
                    if ($mail->ErrorInfo) {
                                        return $mail->ErrorInfo;
                    }
                }
            }
            if ($type == $valids[6]) {
                if (!$mail->AddReplyTo($addr, $addrname)) {
                    if ($mail->ErrorInfo) {
                                        return $mail->ErrorInfo;
                    }
                }
            }
        }
    } else {
        list($to,$toname) = __sendmail_parser($to);
        if (!$mail->AddAddress($to, $toname)) {
            return $mail->ErrorInfo;
        }
    }
    capture_next_error();
    $current = $mail->PreSend();
    get_clear_error();
    if (!$current) {
        return $mail->ErrorInfo;
    }
    if (!semaphore_acquire(__FUNCTION__)) {
        show_php_error(array("phperror" => "Could not acquire the semaphore"));
    }
    $messageid = __sendmail_messageid($id_cuenta, $mail->From);
    $file1 = __sendmail_emlsaver($mail->GetSentMIMEMessage(), $messageid);
    $file2 = __sendmail_objsaver($mail, $messageid);
    $last_id = __getmail_insert($file1, $messageid, 0, 0, 0, 0, 0, 1, 1, "");
    semaphore_release(__FUNCTION__);
    if (count($bcc)) {
        __getmail_add_bcc($last_id, $bcc); // BCC DOESN'T APPEAR IN THE RFC822 SOMETIMES
    }
    if (CONFIG("email_async")) {
        __getmail_update("state_sent", 0, $last_id);
        __getmail_update("state_error", "", $last_id);
        return "";
    }
    capture_next_error();
    $current = $mail->PostSend();
    $error = get_clear_error();
    if (words_exists("PostSend non-object", $error)) {
        __getmail_update("state_sent", 1, $last_id);
        __getmail_update("state_error", LANG("interrorsendmail", "correo"), $last_id);
        unlink($file2);
        return LANG("interrorsendmail", "correo");
    }
    if (!$current) {
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
        return $error;
    }
    __getmail_update("state_sent", 1, $last_id);
    __getmail_update("state_error", "", $last_id);
    unlink($file2);
    return "";
}

function __sendmail_parser($oldaddr)
{
    $pos1 = strpos($oldaddr, "<");
    $pos2 = strpos($oldaddr, ">");
    if ($pos1 !== false && $pos2 !== false) {
        $name = trim(substr($oldaddr, 0, $pos1));
        $addr = trim(substr($oldaddr, $pos1 + 1, $pos2 - $pos1 - 1));
    } else {
        $name = "";
        $addr = trim($oldaddr);
    }
    return array($addr,$name);
}

function __sendmail_messageid($id_cuenta, $from)
{
    require_once "php/getmail.php";
    $prefix = get_directory("dirs/outboxdir") . $id_cuenta;
    if (!file_exists($prefix)) {
        mkdir($prefix);
        chmod($prefix, 0777);
    }
    $query = "SELECT MAX(id) FROM tbl_correo";
    $count = execute_query($query);
    if (!$count) {
        $count = 1;
    }
    $uidl2 = sprintf("%08X", crc32($from));
    for (;;) {
        $uidl1 = sprintf("%08X", $count);
        $file = $prefix . "/" . $uidl1 . $uidl2 . ".eml.gz";
        if (!file_exists($file)) {
            break;
        }
        $count++;
    }
    return $id_cuenta . "/" . $uidl1 . $uidl2;
}

function __sendmail_emlsaver($message, $messageid)
{
    require_once "php/getmail.php";
    $prefix = get_directory("dirs/outboxdir") . $messageid;
    $file = $prefix . ".eml.gz";
    $fp = gzopen($file, "w");
    gzwrite($fp, $message);
    gzclose($fp);
    chmod($file, 0666);
    return $file;
}

function __sendmail_objsaver($mail, $messageid)
{
    $prefix = get_directory("dirs/outboxdir") . $messageid;
    $file = $prefix . ".obj";
    file_put_contents($file, serialize($mail));
    chmod($file, 0666);
    return $file;
}
