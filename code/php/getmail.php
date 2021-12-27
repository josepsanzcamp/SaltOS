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

// phpcs:disable Generic.Files.LineLength
// phpcs:disable PSR1.Files.SideEffects

// NEEDED INCLUDES
require_once "lib/phpclasses/mimeparser/mime_parser.php";
require_once "lib/phpclasses/mimeparser/rfc822_addresses.php";
require_once "lib/phpclasses/pop3class/pop3.php";

// SOME DEFINES
define("__HTML_PAGE_OPEN__", '<!DOCTYPE html><html><head><style type="text/css">' . getDefault("defines/htmlpage") . '</style></head><body>');
define("__HTML_PAGE_CLOSE__", '</body></html>');
define("__HTML_BOX_OPEN__", '<div style="' . getDefault("defines/htmlbox") . '">');
define("__HTML_BOX_CLOSE__", '</div>');
define("__HTML_TEXT_OPEN__", '<div style="' . getDefault("defines/htmltext") . '">');
define("__HTML_TEXT_CLOSE__", '</div>');
define("__PLAIN_TEXT_OPEN__", '<div style="' . getDefault("defines/plaintext") . '">');
define("__PLAIN_TEXT_CLOSE__", '</div>');
define("__HTML_SEPARATOR__", '<hr style="' . getDefault("defines/separator") . '"/>');
define("__HTML_NEWLINE__", '<br/>');
define("__BLOCKQUOTE_OPEN__", '<blockquote style="' . getDefault("defines/blockquote") . '">');
define("__BLOCKQUOTE_CLOSE__", '</blockquote>');
define("__SIGNATURE_OPEN__", '<div style="' . getDefault("defines/signature") . '">');
define("__SIGNATURE_CLOSE__", '</div>');

// REMOVE ALL BODY (ONLY FOR DEBUG PURPOSES)
function __getmail_removebody($array)
{
    if (isset($array["Body"])) {
        $array["Body"] = "##### BODY REMOVED FOR DEBUG PURPOSES #####";
    }
    $parts = __getmail_getnode("Parts", $array);
    if ($parts) {
        foreach ($parts as $index => $node) {
            $array["Parts"][$index] = __getmail_removebody($node);
        }
    }
    return $array;
}

// THE FOLLOW FUNCTIONS UNIFY THE CONCEPT OF PROCESS
function __getmail_processmessage($disp, $type)
{
    return ($type == "message" && $disp == "inline");
}

function __getmail_processplainhtml($disp, $type)
{
    return (in_array($type, array("plain","html")) && $disp == "inline");
}

function __getmail_processfile($disp, $type)
{
    return (
        $disp == "attachment" ||
        ($disp == "inline" && !in_array($type, array("plain","html","message","alternative","multipart")))
    );
}

// CHECK VIEW PERMISION FOR THE CURRENT USER AND THE REQUESTED EMAIL
function __getmail_checkperm($id)
{
    $query = "SELECT a.id
        FROM (
            SELECT a2.*,uc.email_privated email_privated
            FROM tbl_correo a2
            LEFT JOIN tbl_usuarios_c uc ON a2.id_cuenta=uc.id
        ) a
        LEFT JOIN tbl_registros e ON e.id_aplicacion='" . page2id("correo") . "'
            AND e.id_registro=a.id
            AND e.first=1
        LEFT JOIN tbl_usuarios d ON e.id_usuario=d.id
        WHERE a.id='" . abs($id) . "'
            AND (
                TRIM(IFNULL(email_privated,0))='0' OR
                (TRIM(IFNULL(email_privated,0))='1' AND e.id_usuario='" . current_user() . "')
            )
            AND " . check_sql("correo", "view");
    return execute_query($query);
}

// RETURN THE ORIGINAL RFC822 MESSAGE
function __getmail_getsource($id, $max = 0)
{
    $query = "SELECT * FROM tbl_correo WHERE id='$id'";
    $row = execute_query($query);
    if (!$row) {
        return "";
    }
    $email = "${row["id_cuenta"]}/${row["uidl"]}";
    $file = ($row["is_outbox"] ? get_directory("dirs/outboxdir") : get_directory("dirs/inboxdir")) . $email . ".eml.gz";
    if (!file_exists($file)) {
        return "";
    }
    $fp = gzopen($file, "r");
    $message = "";
    if (!$max) {
        $max = gzfilesize($file);
    }
    while (!feof($fp) && strlen($message) < $max) {
        $message .= gzread($fp, min(8192, $max - strlen($message)));
    }
    if (!feof($fp)) {
        $message .= "\n...";
    }
    gzclose($fp);
    return $message;
}

// RETURN THE DECODED MIME STRUCTURE OF MESSAGE
function __getmail_mime_decode_protected($input)
{
    $mime = new mime_parser_class();
    $decoded = "";
    $mime->Decode($input, $decoded);
    if (!count($decoded)) {
        $mime->decode_bodies = 0;
        $mime->Decode($input, $decoded);
    }
    return $decoded;
}

// RETURN THE DECODED MIME STRUCTURE BY ID
function __getmail_getmime($id)
{
    $query = "SELECT * FROM tbl_correo WHERE id='$id'";
    $row = execute_query($query);
    if (!$row) {
        return "";
    }
    $email = "${row["id_cuenta"]}/${row["uidl"]}";
    $cache = get_cache_file($row, ".eml");
    if (!file_exists($cache)) {
        $file = (
            $row["is_outbox"] ?
                get_directory("dirs/outboxdir") :
                get_directory("dirs/inboxdir")
        ) . $email . ".eml.gz";
        if (!file_exists($file)) {
            return "";
        }
        $decoded = __getmail_mime_decode_protected(array("File" => "compress.zlib://" . $file));
        file_put_contents($cache, serialize($decoded));
        chmod_protected($cache, 0666);
    } else {
        $decoded = unserialize(file_get_contents($cache));
    }
    return $decoded;
}

// RETURN A NODE USING A XPATH NOTATION
function __getmail_getnode($path, $array)
{
    if (!is_array($path)) {
        $path = explode("/", $path);
    }
    $elem = array_shift($path);
    if (!is_array($array) || !isset($array[$elem])) {
        return null;
    }
    if (count($path) == 0) {
        return $array[$elem];
    }
    return __getmail_getnode($path, $array[$elem]);
}

// RETURN INTERNAL CONTENT TYPE
function __getmail_gettype($array)
{
    $ctype = strtoupper(__getmail_fixstring(__getmail_getnode("Headers/content-type:", $array)));
    if (!$ctype) {
        $ctype = "TEXT/PLAIN";
    }
    if (strpos($ctype, "TEXT/HTML") !== false) {
        $type = "html";
    } elseif (strpos($ctype, "TEXT/PLAIN") !== false) {
        $type = "plain";
    } elseif (strpos($ctype, "MESSAGE/RFC822") !== false) {
        $type = "message";
    } elseif (strpos($ctype, "MULTIPART/ALTERNATIVE") !== false) {
        $type = "alternative";
    } elseif (strpos($ctype, "MULTIPART/") !== false) {
        $type = "multipart";
    } else {
        $type = "other";
    }
    return $type;
}

// RETURN INTERNAL DISPOSITION
function __getmail_getdisposition($array)
{
    $cdisp = strtoupper(__getmail_fixstring(__getmail_getnode("Headers/content-disposition:", $array)));
    if (!$cdisp) {
        $cdisp = "INLINE";
    }
    if (strpos($cdisp, "ATTACHMENT") !== false) {
        $disp = "attachment";
    } elseif (strpos($cdisp, "INLINE") !== false) {
        $disp = "inline";
    } else {
        $disp = "other";
    }
    return $disp;
}

// RETURN AN ARRAY OF ATTACHMENTS FILES
function __getmail_getfiles($array, $level = 0)
{
    $result = array();
    $disp = __getmail_getdisposition($array);
    $type = __getmail_gettype($array);
    if (__getmail_processfile($disp, $type)) {
        $temp = __getmail_getnode("Body", $array);
        if ($temp) {
            $cid = __getmail_fixstring(__getmail_getnode("Headers/content-id:", $array));
            if (substr($cid, 0, 1) == "<") {
                $cid = substr($cid, 1);
            }
            if (substr($cid, -1, 1) == ">") {
                $cid = substr($cid, 0, -1);
            }
            $cname = getutf8(__getmail_fixstring(__getmail_getnode("FileName", $array)));
            $location = __getmail_fixstring(__getmail_getnode("Headers/content-location:", $array));
            if ($cid == "" && $cname == "" && $location != "") {
                $cid = $location;
            }
            $ctype = __getmail_fixstring(__getmail_getnode("Headers/content-type:", $array));
            if (strpos($ctype, ";") !== false) {
                $ctype = strtok($ctype, ";");
            }
            if ($cid == "" && $cname == "" && __getmail_processfile($disp, $type)) {
                $cname = encode_bad_chars($ctype) . ".eml";
            }
            if ($cname != "") {
                $csize = __getmail_fixstring(__getmail_getnode("BodyLength", $array));
                $hsize = __getmail_gethumansize($csize);
                $chash = md5(serialize(array(md5($temp),$cid,$cname,$ctype,$csize))); // MD5 INSIDE AS MEMORY TRICK
                $result[] = array(
                    "disp" => $disp,
                    "type" => $type,
                    "ctype" => $ctype,
                    "cid" => $cid,
                    "cname" => $cname,
                    "csize" => $csize,
                    "hsize" => $hsize,
                    "chash" => $chash,
                    "body" => $temp
                );
            }
        }
    } elseif (__getmail_processplainhtml($disp, $type)) {
        // THIS DATA IS USED BY THE NEXT TRICK
        $temp = __getmail_getnode("Body", $array);
        if ($temp) {
            $temp = getutf8($temp);
            $result[] = array("disp" => $disp,"type" => $type,"body" => $temp);
        }
    } elseif (__getmail_processmessage($disp, $type)) {
        $temp = __getmail_getnode("Body", $array);
        if ($temp) {
            $decoded = __getmail_mime_decode_protected(array("Data" => $temp));
            $result = array_merge($result, __getmail_getfiles(__getmail_getnode("0", $decoded), $level + 1));
        }
    }
    $parts = __getmail_getnode("Parts", $array);
    if ($parts) {
        foreach ($parts as $index => $node) {
            $result = array_merge($result, __getmail_getfiles($node, $level + 1));
        }
    }
    if ($level == 0) {
        // TRICK TO REMOVE THE FILES THAT CONTAIN NAME AND CID
        foreach ($result as $index => $node) {
            $disp = $node["disp"];
            $type = $node["type"];
            if (__getmail_processplainhtml($disp, $type)) {
                $temp = $node["body"];
                foreach ($result as $index2 => $node2) {
                    $disp2 = $node2["disp"];
                    $type2 = $node2["type"];
                    if (__getmail_processfile($disp2, $type2)) {
                        $cid2 = $node2["cid"];
                        if ($cid2 != "") {
                            if (strpos($temp, "cid:${cid2}") !== false) {
                                unset($result[$index2]);
                            }
                        }
                    }
                }
                unset($result[$index]);
            }
        }
    }
    return $result;
}

// RETURN THE HUMAN SIZE (GBYTES, MBYTES, KBYTES OR BYTES)
function __getmail_gethumansize($size)
{
    if ($size >= 1073741824) {
        $size = round($size / 1073741824, 2) . " Gbytes";
    } elseif ($size >= 1048576) {
        $size = round($size / 1048576, 2) . " Mbytes";
    } elseif ($size >= 1024) {
        $size = round($size / 1024, 2) . " Kbytes";
    } else {
        $size = $size . " bytes";
    }
    return $size;
}

// RETURN ALL INFORMATION OF THE DECODED MESSAGE
function __getmail_getinfo($array)
{
    if (eval_bool(getDefault("debug/getmaildebug"))) {
        echo "<pre>" . sprintr(__getmail_removebody($array)) . "</pre>";
    }
    $result = array("emails" => array(),"datetime" => "","subject" => "","spam" => "","files" => array(),"crt" => 0,
        "priority" => 0,"sensitivity" => 0,"from" => "","to" => "","cc" => "","bcc" => "");
    // CREATE THE FROM, TO, CC AND BCC STRING
    $lista = array(1 => "from",2 => "to",3 => "cc",4 => "bcc",
        5 => "return-path",6 => "reply-to",7 => "disposition-notification-to");
    foreach ($lista as $key => $val) {
        $addresses = __getmail_getnode("ExtractedAddresses/${val}:", $array);
        if ($addresses) {
            $temp = array();
            foreach ($addresses as $a) {
                $name = getutf8(__getmail_fixstring(__getmail_getnode("name", $a)));
                $addr = getutf8(__getmail_fixstring(__getmail_getnode("address", $a)));
                $result["emails"][] = array("id_tipo" => $key,"tipo" => $val,"nombre" => $name,"valor" => $addr);
                $temp[] = ($name != "") ? $name . " <" . $addr . ">" : $addr;
            }
            $temp = implode("; ", $temp);
            if (array_key_exists($val, $result)) {
                $result[$val] = $temp;
            }
        }
    }
    // CREATE THE DATETIME STRING
    $datetime = __getmail_fixstring(__getmail_getnode("Headers/date:", $array));
    if (!$datetime) {
        $datetime = __getmail_fixstring(__getmail_getnode("Headers/delivery-date:", $array));
    }
    if ($datetime && strpos($datetime, "(") !== false) {
        $datetime = strtok($datetime, "(");
    }
    if ($datetime) {
        $result["datetime"] = date("Y-m-d H:i:s", strtotime($datetime));
    }
    if (!$datetime) {
        $result["datetime"] = current_datetime();
    }
    // CREATE THE SUBJECT STRING
    $subject = __getmail_fixstring(__getmail_getnode("DecodedHeaders/subject:/0/0/Value", $array));
    if (!$subject) {
        $subject = __getmail_fixstring(__getmail_getnode("Headers/subject:", $array));
    }
    $result["subject"] = prepare_words(str_replace("\t", " ", getutf8($subject)));
    // CHECK X-SPAM-STATUS HEADER
    $spam = strtoupper(trim(__getmail_fixstring(__getmail_getnode("Headers/x-spam-status:", $array))));
    $result["spam"] = (substr($spam, 0, 3) == "YES" || substr($spam, -3, 3) == "YES") ? "1" : "0";
    // GET THE NUMBER OF ATTACHMENTS
    $result["files"] = __getmail_getfiles($array);
    // GET THE CRT IF EXISTS
    foreach ($result["emails"] as $email) {
        if ($email["id_tipo"] == 7) {
            $result["crt"] = 1;
        }
    }
    // GET THE PRIORITY IF EXISTS
    $priority = strtolower(__getmail_fixstring(__getmail_getnode("Headers/x-priority:", $array)));
    $priorities = array("low" => 5,"high" => 1);
    if (isset($priorities[$priority])) {
        $priority = $priorities[$priority];
    }
    $priority = intval($priority);
    $priorities = array(5 => -1,4 => -1,3 => 0,2 => 1,1 => 1);
    if (isset($priorities[$priority])) {
        $result["priority"] = $priorities[$priority];
    }
    // GET THE SENSITIVITY IF EXISTS
    $sensitivity = strtolower(__getmail_fixstring(__getmail_getnode("Headers/sensitivity:", $array)));
    $sensitivities = array("personal" => 1,"private" => 2,"company-confidential" => 3,"company confidential" => 3);
    if (isset($sensitivities[$sensitivity])) {
        $result["sensitivity"] = $sensitivities[$sensitivity];
    }
    // RETURN THE RESULT
    if (eval_bool(getDefault("debug/getmaildebug"))) {
        $result["body"] = __getmail_gettextbody($array);
    }
    if (eval_bool(getDefault("debug/getmaildebug"))) {
        echo "<pre>" . sprintr($result) . "</pre>";
    }
    if (eval_bool(getDefault("debug/getmaildebug"))) {
        die();
    }
    return $result;
}

function __getmail_fixstring($arg)
{
    while (is_array($arg)) {
        $arg = array_shift($arg);
    }
    if ($arg === null) {
        $arg = "";
    }
    return $arg;
}

// RETURN ALL TEXT BODY CONCATENATED
function __getmail_gettextbody($array, $level = 0)
{
    $result = array();
    $disp = __getmail_getdisposition($array);
    $type = __getmail_gettype($array);
    if (__getmail_processplainhtml($disp, $type)) {
        $temp = __getmail_getnode("Body", $array);
        if ($temp) {
            $temp = getutf8($temp);
            if ($type == "html") {
                $temp = html2text($temp);
            }
            $result[] = array("type" => $type,"body" => $temp);
        }
    } elseif (__getmail_processmessage($disp, $type)) {
        $temp = __getmail_getnode("Body", $array);
        if ($temp) {
            $decoded = __getmail_mime_decode_protected(array("Data" => $temp));
            $result[] = array("type" => $type,"body" => __getmail_gettextbody(__getmail_getnode("0", $decoded)));
        }
    }
    $parts = __getmail_getnode("Parts", $array);
    if ($parts) {
        $recursive = array();
        foreach ($parts as $index => $node) {
            $recursive = array_merge($recursive, __getmail_gettextbody($node, $level + 1));
        }
        if ($type == "alternative") {
            $count_plain = 0;
            $count_html = 0;
            foreach ($recursive as $index => $node) {
                if ($node["type"] == "plain") {
                    $count_plain++;
                } elseif ($node["type"] == "html") {
                    $count_html++;
                }
            }
            if ($count_plain == 1 && $count_html == 1) {
                foreach ($recursive as $index => $node) {
                    if ($node["type"] == "plain") {
                        break;
                    }
                }
                unset($recursive[$index]);
            }
        }
        $result = array_merge($result, $recursive);
    }
    if ($level == 0) {
        foreach ($result as $index => $node) {
            $result[$index] = $node["body"];
        }
        $result = implode("\n", $result);
    }
    return $result;
}

// RETURN ALL BODY AND ATTACHMENTS INFORMATION
function __getmail_getfullbody($array)
{
    $result = array();
    $disp = __getmail_getdisposition($array);
    $type = __getmail_gettype($array);
    if (__getmail_processplainhtml($disp, $type)) {
        $temp = __getmail_getnode("Body", $array);
        if ($temp) {
            $temp = getutf8($temp);
            $result[] = array("disp" => $disp,"type" => $type,"body" => $temp);
        }
    } elseif (__getmail_processmessage($disp, $type)) {
        $temp = __getmail_getnode("Body", $array);
        if ($temp) {
            $decoded = __getmail_mime_decode_protected(array("Data" => $temp));
            $result = array_merge($result, __getmail_getfullbody(__getmail_getnode("0", $decoded)));
        }
    } else {
        $temp = __getmail_getnode("Body", $array);
        if ($temp) {
            $cid = __getmail_fixstring(__getmail_getnode("Headers/content-id:", $array));
            if (substr($cid, 0, 1) == "<") {
                $cid = substr($cid, 1);
            }
            if (substr($cid, -1, 1) == ">") {
                $cid = substr($cid, 0, -1);
            }
            $cname = getutf8(__getmail_fixstring(__getmail_getnode("FileName", $array)));
            $location = __getmail_fixstring(__getmail_getnode("Headers/content-location:", $array));
            if ($cid == "" && $cname == "" && $location != "") {
                $cid = $location;
            }
            $ctype = __getmail_fixstring(__getmail_getnode("Headers/content-type:", $array));
            if (strpos($ctype, ";") !== false) {
                $ctype = strtok($ctype, ";");
            }
            if ($cid == "" && $cname == "" && __getmail_processfile($disp, $type)) {
                $cname = encode_bad_chars($ctype) . ".eml";
            }
            if ($cid != "" || $cname != "") {
                $csize = __getmail_fixstring(__getmail_getnode("BodyLength", $array));
                $hsize = __getmail_gethumansize($csize);
                $chash = md5(serialize(array(md5($temp),$cid,$cname,$ctype,$csize))); // MD5 INSIDE AS MEMORY TRICK
                $result[] = array(
                    "disp" => $disp,
                    "type" => $type,
                    "ctype" => $ctype,
                    "cid" => $cid,
                    "cname" => $cname,
                    "csize" => $csize,
                    "hsize" => $hsize,
                    "chash" => $chash,
                    "body" => $temp
                );
            }
        }
    }
    $parts = __getmail_getnode("Parts", $array);
    if ($parts) {
        $recursive = array();
        foreach ($parts as $index => $node) {
            $recursive = array_merge($recursive, __getmail_getfullbody($node));
        }
        if ($type == "alternative") {
            $count_plain = 0;
            $count_html = 0;
            foreach ($recursive as $index => $node) {
                if ($node["type"] == "plain") {
                    $count_plain++;
                } elseif ($node["type"] == "html") {
                    $count_html++;
                }
            }
            if ($count_plain == 1 && $count_html == 1) {
                foreach ($recursive as $index => $node) {
                    if ($node["type"] == "plain") {
                        break;
                    }
                }
                unset($recursive[$index]);
            }
        }
        $result = array_merge($result, $recursive);
    }
    return $result;
}

// RETURN THE ATTACHMENT REQUESTED
function __getmail_getcid($array, $hash)
{
    $disp = __getmail_getdisposition($array);
    $type = __getmail_gettype($array);
    if (__getmail_processmessage($disp, $type)) {
        $temp = __getmail_getnode("Body", $array);
        if ($temp) {
            $decoded = __getmail_mime_decode_protected(array("Data" => $temp));
            $result = __getmail_getcid(__getmail_getnode("0", $decoded), $hash);
            if ($result) {
                return $result;
            }
        }
    } else {
        $temp = __getmail_getnode("Body", $array);
        if ($temp) {
            $cid = __getmail_fixstring(__getmail_getnode("Headers/content-id:", $array));
            if (substr($cid, 0, 1) == "<") {
                $cid = substr($cid, 1);
            }
            if (substr($cid, -1, 1) == ">") {
                $cid = substr($cid, 0, -1);
            }
            $cname = getutf8(__getmail_fixstring(__getmail_getnode("FileName", $array)));
            $location = __getmail_fixstring(__getmail_getnode("Headers/content-location:", $array));
            if ($cid == "" && $cname == "" && $location != "") {
                $cid = $location;
            }
            $ctype = __getmail_fixstring(__getmail_getnode("Headers/content-type:", $array));
            if (strpos($ctype, ";") !== false) {
                $ctype = strtok($ctype, ";");
            }
            if ($cid == "" && $cname == "" && __getmail_processfile($disp, $type)) {
                $cname = encode_bad_chars($ctype) . ".eml";
            }
            $csize = __getmail_fixstring(__getmail_getnode("BodyLength", $array));
            $chash = md5(serialize(array(md5($temp),$cid,$cname,$ctype,$csize))); // MD5 INSIDE AS MEMORY TRICK
            if ($chash == $hash) {
                $hsize = __getmail_gethumansize($csize);
                return array(
                    "disp" => $disp,
                    "type" => $type,
                    "ctype" => $ctype,
                    "cid" => $cid,
                    "cname" => $cname,
                    "csize" => $csize,
                    "hsize" => $hsize,
                    "chash" => $chash,
                    "body" => $temp
                );
            }
            // FOR COMPATIBILITY WITH OLD SALTOS VERSIONS
            if (
                in_array($hash, array(
                md5(md5($temp) . md5($cid) . md5($cname) . md5($ctype) . md5($csize)),
                md5(serialize(array($temp,$cid,$cname,$ctype,$csize))),
                md5(serialize(array(md5($temp),$cid,$cname,$ctype,$csize))),
                md5(json_encode(array(md5($temp),$cid,$cname,$ctype,$csize))),
                ))
            ) {
                $hsize = __getmail_gethumansize($csize);
                return array(
                    "disp" => $disp,
                    "type" => $type,
                    "ctype" => $ctype,
                    "cid" => $cid,
                    "cname" => $cname,
                    "csize" => $csize,
                    "hsize" => $hsize,
                    "chash" => $chash,
                    "body" => $temp
                );
            }
            // END OF COMPATIBILITY CODE
        }
    }
    $parts = __getmail_getnode("Parts", $array);
    if ($parts) {
        foreach ($parts as $index => $node) {
            $result = __getmail_getcid($node, $hash);
            if ($result) {
                return $result;
            }
        }
    }
    return null;
}

function __getmail_insert(
    $file,
    $messageid,
    $state_new,
    $state_reply,
    $state_forward,
    $state_wait,
    $id_correo,
    $is_outbox,
    $state_sent,
    $state_error
) {
    list($id_cuenta,$uidl) = explode("/", $messageid);
    $size = gzfilesize($file);
    $id_usuario = current_user();
    $id_aplicacion = page2id("correo");
    $datetime = current_datetime();
    // DECODE THE MESSAGE
    $decoded = __getmail_mime_decode_protected(array("File" => "compress.zlib://" . $file));
    $info = __getmail_getinfo(__getmail_getnode("0", $decoded));
    $body = __getmail_gettextbody(__getmail_getnode("0", $decoded));
    unset($decoded); // TRICK TO RELEASE MEMORY
    // INSERT THE NEW EMAIL
    if (!semaphore_acquire(__FUNCTION__)) {
        show_php_error(array("phperror" => "Could not acquire the semaphore"));
    }
    $query = make_insert_query("tbl_correo", array(
        "id_cuenta" => $id_cuenta,
        "uidl" => $uidl,
        "size" => $size,
        "datetime" => $info["datetime"],
        "subject" => $info["subject"],
        "body" => $body,
        "state_new" => $state_new,
        "state_reply" => $state_reply,
        "state_forward" => $state_forward,
        "state_wait" => $state_wait,
        "state_spam" => $info["spam"],
        "id_correo" => $id_correo,
        "is_outbox" => $is_outbox,
        "state_sent" => $state_sent,
        "state_error" => $state_error,
        "state_crt" => $info["crt"],
        "priority" => $info["priority"],
        "sensitivity" => $info["sensitivity"],
        "de" => $info["from"],
        "para" => $info["to"],
        "cc" => $info["cc"],
        "bcc" => $info["bcc"],
        "files" => count($info["files"])
    ));
    unset($body); // TRICK TO RELEASE MEMORY
    db_query($query);
    // GET LAST_ID
    $query = "SELECT id
        FROM tbl_correo
        WHERE id_cuenta='${id_cuenta}'
            AND is_outbox='${is_outbox}'
        ORDER BY id DESC
        LIMIT 1";
    $last_id = execute_query($query);
    semaphore_release(__FUNCTION__);
    // INSERT ALL ADDRESS
    foreach ($info["emails"] as $email) {
        $query = make_insert_query("tbl_correo_a", array(
            "id_correo" => $last_id,
            "id_tipo" => $email["id_tipo"],
            "nombre" => $email["nombre"],
            "valor" => $email["valor"]
        ));
        db_query($query);
    }
    // INSERT ALL ATTACHMENTS
    foreach ($info["files"] as $file) {
        $query = make_insert_query("tbl_ficheros", array(
            "id_aplicacion" => $id_aplicacion,
            "id_registro" => $last_id,
            "id_usuario" => $id_usuario,
            "datetime" => $datetime,
            "fichero" => $file["cname"],
            "fichero_size" => $file["csize"],
            "fichero_type" => $file["ctype"],
            "fichero_hash" => $file["chash"]
        ));
        db_query($query);
    }
    // INSERT THE CONTROL REGISTER
    make_control($id_aplicacion, $last_id);
    make_indexing($id_aplicacion, $last_id);
    return $last_id;
}

function __getmail_update($campo, $valor, $id)
{
    $query = make_update_query("tbl_correo", array(
        $campo => $valor
    ), make_where_query(array(
        "id" => $id
    )));
    db_query($query);
}

// FOR RAWURLDECODE AUTO DETECTION
function __getmail_rawurldecode($temp)
{
    if (strpos($temp, "%20") !== false) {
        $temp = rawurldecode($temp);
    }
    return $temp;
}

function __getmail_add_bcc($id, $bcc)
{
    foreach ($bcc as $addr) {
        list($valor,$nombre) = __sendmail_parser($addr);
        $query = make_insert_query("tbl_correo_a", array(
            "id_correo" => $id,
            "id_tipo" => 4, // DEFINED IN __getmail_getinfo FUNCTION
            "nombre" => $nombre,
            "valor" => $valor
        ));
        db_query($query);
    }
    $bcc = implode("; ", $bcc);
    $query = make_update_query("tbl_correo", array(
        "bcc" => $bcc
    ), make_where_query(array(
        "id" => $id
    )));
    db_query($query);
}
