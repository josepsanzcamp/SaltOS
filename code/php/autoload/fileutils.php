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

function get_directory($key, $default = "")
{
    $default = $default ? $default : getcwd_protected() . "/cache";
    $dir = getDefault($key, $default);
    $bar = (substr($dir, -1, 1) != "/") ? "/" : "";
    return $dir . $bar;
}

function get_temp_file($ext = "")
{
    if ($ext == "") {
        $ext = ".dat";
    }
    if (substr($ext, 0, 1) != ".") {
        $ext = "." . $ext;
    }
    $dir = get_directory("dirs/cachedir");
    while (1) {
        $uniqid = get_unique_id_md5();
        $file = $dir . $uniqid . $ext;
        if (!file_exists($file)) {
            break;
        }
    }
    return $file;
}

function cache_exists($cache, $file)
{
    if (!file_exists($cache) || !is_file($cache)) {
        return 0;
    }
    $mtime1 = filemtime($cache);
    if (!is_array($file)) {
        $file = array($file);
    }
    foreach ($file as $f) {
        if (!file_exists($f) || !is_file($f)) {
            return 0;
        }
        $mtime2 = filemtime($f);
        if ($mtime2 >= $mtime1) {
            return 0;
        }
    }
    return 1;
}

function get_cache_file($data, $ext = "")
{
    if (is_array($data)) {
        $data = serialize($data);
    }
    if ($ext == "") {
        $ext = strtolower(extension($data));
    }
    if ($ext == "") {
        $ext = ".dat";
    }
    if (substr($ext, 0, 1) != ".") {
        $ext = "." . $ext;
    }
    $dir = get_directory("dirs/cachedir");
    $file = $dir . md5($data) . $ext;
    return $file;
}

function semi_realpath($file)
{
    $file = explode("/", $file);
    $count = count($file);
    for ($i = 1; $i < $count; $i++) {
        if ($file[$i] == "..") {
            for ($j = $i - 1; $j >= 0; $j--) {
                if (isset($file[$j]) && $file[$j] != "..") {
                    unset($file[$i]);
                    unset($file[$j]);
                    break;
                }
            }
        }
    }
    $file = implode("/", $file);
    return $file;
}

function truncate_protected($file)
{
    $fp = fopen($file, "w");
    if ($fp) {
        fclose($fp);
    }
}

function url_get_contents($url)
{
    // CHECK SCHEME
    $scheme = parse_url($url, PHP_URL_SCHEME);
    if (!$scheme) {
        $url = "http://" . $url;
    }
    // DO THE REQUEST
    list($body,$headers,$cookies) = __url_get_contents($url);
    // RETURN RESPONSE
    return $body;
}

function __url_get_contents($url, $args = array())
{
    require_once "lib/phpclasses/httpclient/http.php";
    $http = new http_class();
    $http->user_agent = get_name_version_revision();
    $http->follow_redirect = 1;
    if (isset($args["cookies"])) {
        $http->RestoreCookies($args["cookies"]);
    }
    $arguments = array();
    $error = $http->GetRequestArguments($url, $arguments);
    if ($error != "") {
        return array("",array(),array());
    }
    $error = $http->Open($arguments);
    if ($error != "") {
        return array("",array(),array());
    }
    if (isset($args["method"])) {
        $arguments["RequestMethod"] = strtoupper($args["method"]);
    }
    if (isset($args["values"])) {
        $arguments["PostValues"] = $args["values"];
    }
    if (isset($args["referer"])) {
        $arguments["Referer"] = $args["referer"];
    }
    if (isset($args["headers"])) {
        foreach ($args["headers"] as $key => $val) {
            $arguments["Headers"][$key] = $val;
        }
    }
    if (isset($args["body"])) {
        $arguments["Body"] = $args["body"];
    }
    $error = $http->SendRequest($arguments);
    if ($error != "") {
        return array("",array(),array());
    }
    $headers = array();
    $error = $http->ReadReplyHeaders($headers);
    if ($error != "") {
        return array("",array(),array());
    }
    $body = "";
    $error = $http->ReadWholeReplyBody($body);
    if ($error != "") {
        return array("",array(),array());
    }
    $http->Close();
    $cookies = array();
    $http->SaveCookies($cookies);
    return array($body,$headers,$cookies);
}

function extension($file)
{
    return pathinfo($file, PATHINFO_EXTENSION);
}

function extension2($mime)
{
    return saltos_content_type1($mime);
}

function getcwd_protected()
{
    $dir = getcwd();
    if ($dir == "/") {
        $dir = dirname(getServer("SCRIPT_FILENAME"));
    }
    return $dir;
}

// COPIED FROM http://php.net/manual/es/function.gzread.php#110078
function gzfilesize($filename)
{
    $gzfs = false;
    if (($zp = fopen($filename, 'r')) !== false) {
        if (@fread($zp, 2) == "\x1F\x8B") { // this is a gzip'd file
            fseek($zp, -4, SEEK_END);
            if (strlen($datum = @fread($zp, 4)) == 4) {
                extract(unpack('Vgzfs', $datum));
            }
        } else { // not a gzip'd file, revert to regular filesize function
            $gzfs = filesize($filename);
        }
        fclose($zp);
    }
    return($gzfs);
}

function glob_protected($pattern)
{
    $array = glob($pattern);
    return is_array($array) ? $array : array();
}

function find_files($dir, $ext = "")
{
    $files = glob("${dir}/*");
    $result = array();
    foreach ($files as $file) {
        if (is_dir($file)) {
            $result = array_merge($result, find_files($file));
        } elseif (is_file($file)) {
            if (!$ext || extension($file) == $ext) {
                $result[] = $file;
            }
        } else {
            show_php_error(array("phperror" => "Unknown type of archive for '${file}'"));
        }
    }
    return $result;
}

function fix_file($file)
{
    if (strpos($file, " ") !== false) {
        $file2 = get_cache_file($file);
        if (!file_exists($file2)) {
            symlink(realpath($file), $file2);
        }
        $file = $file2;
    }
    return $file;
}

function fsockopen_protected($hostname, $port, &$errno = 0, &$errstr = "", $timeout = null)
{
    if ($timeout == null) {
        $timeout = ini_get("default_socket_timeout");
    }
    return stream_socket_client(
        $hostname . ":" . $port,
        $errno,
        $errstr,
        $timeout,
        STREAM_CLIENT_CONNECT,
        stream_context_create(
            array(
                "ssl" => array(
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                    "allow_self_signed" => true
                )
            )
        )
    );
}

function encode_bad_chars_file($file)
{
    $file = strrev($file);
    $file = explode(".", $file, 2);
    // EXISTS MULTIPLE STRREV TO PREVENT UTF8 DATA LOST
    foreach ($file as $key => $val) {
        $file[$key] = strrev(encode_bad_chars(strrev($val)));
    }
    $file = implode(".", $file);
    $file = strrev($file);
    return $file;
}

function realpath_protected($path)
{
    // REALPATH NO RETORNA RES SI EL PATH NO EXISTEIX
    // ES FA SERVIR QUAN ES VOL EL REALPATH D'UN FITXER QUE ENCARA NO EXISTEIX
    // PER EXEMPLE, PER LA SORTIDA D'UNA COMANDA
    return realpath(dirname($path)) . "/" . basename($path);
}
