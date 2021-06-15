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

// DEFINES
define("__XML_HEADER__", "<?xml version='1.0' encoding='UTF-8' ?>\n");
define("__HTML_DOCTYPE__", "<!DOCTYPE html>\n");

// FUNCTIONS
function getParam($index, $default = "")
{
    if (substr($index, -5, 5) == "_file") {
        $prefix = substr($index, 0, -5);
        $temp = __getParam_helper($prefix . "_temp");
        if (file_exists($temp)) {
            $file = get_directory("dirs/filesdir") . __getParam_helper($prefix . "_file");
            $dir = dirname($file);
            if (!file_exists($dir)) {
                mkdir($dir);
                chmod_protected($dir, 0777);
            }
            move_uploaded_file($temp, $file);
            chmod_protected($file, 0666);
        }
    }
    return __getParam_helper($index, $default);
}

function __getParam_helper($index, $default = "")
{
    if (isset($_POST[$index])) {
        return __getParam_sanitize($_POST[$index]);
    }
    if (isset($_GET[$index])) {
        return __getParam_sanitize($_GET[$index]);
    }
    return $default;
}

function remove_bad_chars($temp, $pad = "")
{
    static $bad_chars = null;
    if ($bad_chars === null) {
        $bad_chars = array(0,1,2,3,4,5,6,7,8,11,12,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31);
        foreach ($bad_chars as $key => $val) {
            $bad_chars[$key] = chr($val);
        }
    }
    $temp = str_replace($bad_chars, $pad, $temp);
    return $temp;
}

function __getParam_sanitize($value)
{
    return remove_bad_chars($value);
}

function getParamWithoutPrefix($index, $default = "")
{
    return getParam($index, $default);
}

function setParam($index, $value = "")
{
    if (isset($_POST[$index])) {
        $_POST[$index] = $value;
    } elseif (isset($_GET[$index])) {
        $_GET[$index] = $value;
    } else {
        $_POST[$index] = $value;
    }
}

function encode_bad_chars($cad, $pad = "_", $extra = "")
{
    static $orig = array(
        "á","à","ä","â","é","è","ë","ê","í","ì","ï","î","ó","ò","ö","ô","ú","ù","ü","û","ñ","ç",
        "Á","À","Ä","Â","É","È","Ë","Ê","Í","Ì","Ï","Î","Ó","Ò","Ö","Ô","Ú","Ù","Ü","Û","Ñ","Ç");
    static $dest = array(
        "a","a","a","a","e","e","e","e","i","i","i","i","o","o","o","o","u","u","u","u","n","c",
        "a","a","a","a","e","e","e","e","i","i","i","i","o","o","o","o","u","u","u","u","n","c",);
    $cad = str_replace($orig, $dest, $cad);
    $cad = strtolower($cad);
    $len = strlen($cad);
    for ($i = 0; $i < $len; $i++) {
        $letter = $cad[$i];
        $replace = 1;
        if ($letter >= "a" && $letter <= "z") {
            $replace = 0;
        }
        if ($letter >= "0" && $letter <= "9") {
            $replace = 0;
        }
        if (strpos($extra, $letter) !== false) {
            $replace = 0;
        }
        if ($replace) {
            $cad[$i] = $pad;
        }
    }
    $cad = prepare_words($cad, $pad);
    return $cad;
}

function prepare_words($cad, $pad = " ")
{
    $count = 1;
    while ($count) {
        $cad = str_replace($pad . $pad, $pad, $cad, $count);
    }
    $len = strlen($pad);
    if (substr($cad, 0, $len) == $pad) {
        $cad = substr($cad, $len);
    }
    if (substr($cad, -$len, $len) == $pad) {
        $cad = substr($cad, 0, -$len);
    }
    return $cad;
}

function querystring2array($querystring)
{
    $items = explode("&", $querystring);
    $result = array();
    foreach ($items as $item) {
        $par = explode("=", $item, 2);
        if (!isset($par[1])) {
            $par[1] = "";
        }
        $par[1] = rawurldecode($par[1]);
        $result[$par[0]] = $par[1];
    }
    return $result;
}

function sprintr($array, $oneline = false)
{
    ob_start();
    print_r($array);
    $buffer = ob_get_clean();
    $buffer = explode("\n", $buffer);
    foreach ($buffer as $key => $val) {
        if (in_array(trim($val), array("(",")",""))) {
            unset($buffer[$key]);
        }
    }
    $buffer = implode($oneline ? "" : "\n", $buffer) . "\n";
    return $buffer;
}

function sign($n)
{
    return $n == abs($n) ? 1 : -1;
}

function color2dec($color, $component)
{
    static $offset = array("R" => 1,"G" => 3,"B" => 5);
    if (!isset($offset[$component])) {
        show_php_error(array("phperror" => "Unknown component on color2dec function"));
    }
    return hexdec(substr($color, $offset[$component], 2));
}

function get_unique_id_md5()
{
    init_random();
    return md5(uniqid(rand(), true));
}

function intelligence_cut($txt, $max, $end = "...")
{
    $len = strlen($txt);
    if ($len > $max) {
        while ($max > 0 && $txt[$max] != " ") {
            $max--;
        }
        if ($max == 0) {
            while ($max < $len && $txt[$max] != " ") {
                $max++;
            }
        }
        if ($max > 0) {
            if (in_array($txt[$max - 1], array(",",".","-","("))) {
                $max--;
            }
        }
        $preview = ($max == $len) ? $txt : substr($txt, 0, $max) . $end;
    } else {
        $preview = $txt;
    }
    return $preview;
}

function xml2html($buffer, $xslfile, $usecache = true)
{
    // SOME CHECKS
    if (!class_exists("DomDocument")) {
        show_php_error(array(
            "phperror" => "Class DomDocument not found","details" => "Try to install php-xml package"
        ));
    }
    if (!class_exists("XsltProcessor")) {
        show_php_error(array(
            "phperror" => "Class XsltProcessor not found","details" => "Try to install php-xsl package"
        ));
    }
    // CACHE MANAGEMENT
    if ($usecache) {
        $cache = get_cache_file($buffer, ".htm");
        if (cache_exists($cache, $xslfile)) {
            return file_get_contents($cache);
        }
    }
    // BEGIN THE TRANSFORMATION
    $doc = new DomDocument();
    $xsl = new XsltProcessor();
    $xsldata = file_get_contents($xslfile);
    $doc->loadXML($xsldata, LIBXML_COMPACT);
    $xsl->importStylesheet($doc);
    $doc->loadXML($buffer, LIBXML_COMPACT);
    capture_next_error();
    $buffer = $xsl->transformToXML($doc);
    $error = get_clear_error();
    // TO PREVENT A BUG IN LIBXML 2.9.1
    if ($error != "" && !words_exists("id already defined", $error)) {
        show_php_error();
    }
    if ($usecache) {
        file_put_contents($cache, $buffer);
        chmod_protected($cache, 0666);
    }
    return $buffer;
}

function ismobile($forcemobile = null)
{
    static $ismobile = null;
    if ($forcemobile !== null) {
        $ismobile = $forcemobile;
    }
    if ($ismobile === null) {
        require "lib/mobiledetect/Mobile_Detect.php";
        if (!isset($_SERVER["HTTP_ACCEPT"])) {
            $_SERVER["HTTP_ACCEPT"] = "";
        }
        if (!isset($_SERVER["HTTP_USER_AGENT"])) {
            $_SERVER["HTTP_USER_AGENT"] = "";
        }
        $detect = new Mobile_Detect();
        $ismobile = $detect->isMobile();
    }
    return $ismobile;
}

function normalize_value($value)
{
    $number = intval(substr($value, 0, -1));
    $letter = strtoupper(substr($value, -1, 1));
    if ($letter == "K") {
        $value = $number * 1024;
    }
    if ($letter == "M") {
        $value = $number * 1024 * 1024;
    }
    if ($letter == "G") {
        $value = $number * 1024 * 1024 * 1024;
    }
    return $value;
}

function get_name_version_revision($copyright = false)
{
    $result = getDefault("info/name", "SaltOS");
    $result .= " v" . getDefault("info/version", "3.7");
    if (!is_array(getDefault("info/revision", "SVN"))) {
        $result .= " r" . getDefault("info/revision", "SVN");
    }
    if ($copyright) {
        $result .= ", " . getDefault("info/copyright", "Copyright (C) 2007-2021 by Josep Sanz Campderrós");
    }
    return $result;
}

function str_word_count_utf8($subject)
{
    static $pattern = "/\p{L}[\p{L}\p{Mn}\p{Pd}'\x{2019}]*/u";
    $matches = array();
    preg_match_all($pattern, $subject, $matches);
    return $matches[0];
}

function output_handler($array)
{
    $file = isset($array["file"]) ? $array["file"] : "";
    $data = isset($array["data"]) ? $array["data"] : "";
    $type = isset($array["type"]) ? $array["type"] : "";
    $cache = isset($array["cache"]) ? $array["cache"] : "";
    $name = isset($array["name"]) ? $array["name"] : "";
    $extra = isset($array["extra"]) ? $array["extra"] : array();
    $die = isset($array["die"]) ? $array["die"] : true;
    if ($file != "") {
        if (!file_exists($file) || !is_file($file)) {
            show_php_error(array("phperror" => "file ${file} not found"));
        }
        if ($data == "" && filesize($file) < memory_get_free(true) / 3) {
            $data = file_get_contents($file);
        }
        if ($type == "") {
            $type = saltos_content_type($file);
        }
    }
    if ($type === "") {
        show_php_error(array("phperror" => "output_handler requires the type parameter"));
    }
    if ($cache === "") {
        show_php_error(array("phperror" => "output_handler requires the cache parameter"));
    }
    header("X-Powered-By: " . get_name_version_revision());
    if ($cache) {
        $hash1 = getServer("HTTP_IF_NONE_MATCH");
        if ($file != "" && $data == "") {
            $hash2 = md5_file($file);
        } else {
            $hash2 = md5($data);
        }
        if ($hash1 == $hash2) {
            header("HTTP/1.1 304 Not Modified");
            die();
        }
    }
    if ($file != "" && $data == "") {
        header("Content-Encoding: none");
    } else {
        $encoding = getServer("HTTP_ACCEPT_ENCODING");
        if (stripos($encoding, "gzip") !== false && function_exists("gzencode")) {
            header("Content-Encoding: gzip");
            $data = gzencode($data);
        } elseif (stripos($encoding, "deflate") !== false && function_exists("gzdeflate")) {
            header("Content-Encoding: deflate");
            $data = gzdeflate($data);
        } else {
            header("Content-Encoding: none");
        }
        header("Vary: Accept-Encoding");
    }
    if ($file != "" && $data == "") {
        $size = filesize($file);
    } else {
        $size = strlen($data);
    }
    if ($cache) {
        header("Expires: " . gmdate("D, d M Y H:i:s", time() + getDefault("cache/cachegctimeout")) . " GMT");
        header("Cache-Control: max-age=" . getDefault("cache/cachegctimeout") . ", no-transform");
        header("Pragma: public");
        header("ETag: ${hash2}");
    } else {
        header("Expires: -1");
        header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0, no-transform");
        header("Pragma: no-cache");
    }
    // EXCEPTION WHEN TYPE IS EXCEL AND NAME DENOTES CSV
    if ($name != "") {
        if (strtolower(extension($name)) == "csv") {
            if ($type == "application/vnd.ms-excel") {
                $type = "text/csv";
            }
        }
    }
    // CONTINUE
    header("Content-Type: ${type}");
    header("Content-Length: ${size}");
    if ($name != "") {
        header("Content-disposition: attachment; filename=\"${name}\"");
    }
    foreach ($extra as $temp) {
        header($temp, false);
    }
    header("Connection: keep-alive, close");
    if ($file != "" && $data == "") {
        readfile_protected($file);
    } else {
        echo $data;
    }
    if ($die) {
        die();
    }
}

function inline_images($buffer)
{
    $pos = strpos($buffer, "url(");
    while ($pos !== false) {
        $pos2 = strpos($buffer, ")", $pos + 4);
        $img = substr($buffer, $pos + 4, $pos2 - $pos - 4);
        if (in_array(substr($img, 0, 1), array("'",'"'))) {
            $img = substr($img, 1);
        }
        if (in_array(substr($img, -1, 1), array("'",'"'))) {
            $img = substr($img, 0, -1);
        }
        if (file_exists($img)) {
            $type = saltos_content_type($img);
            if (substr($type, 0, 5) == "image") {
                $data = "data:$type;base64," . base64_encode(file_get_contents($img));
                $buffer = substr_replace($buffer, $data, $pos + 4, $pos2 - $pos - 4);
                $pos2 = $pos2 - strlen($img) + strlen($data);
            }
        }
        $pos = strpos($buffer, "url(", $pos2 + 1);
    }
    return $buffer;
}

function isphp($version)
{
    return version_compare(PHP_VERSION, $version, ">=");
}

function ishhvm()
{
    return defined("HHVM_VERSION");
}

function ismsie($version = null)
{
    $useragent = getServer("HTTP_USER_AGENT");
    if ($version === null) {
        return strpos($useragent, "MSIE") !== false;
    } elseif (is_string($version)) {
        return strpos($useragent, "MSIE ${version}") !== false;
    } elseif (is_array($version)) {
        foreach ($version as $v) {
            if (strpos($useragent, "MSIE ${v}") !== false) {
                return true;
            }
        }
        return false;
    }
}

// USING ROUNDCUBEMAIL FEATURES
function html2text($html)
{
    require_once "lib/roundcube/rcube_html2text.php";
    $h2t = new rcube_html2text($html);
    capture_next_error();
    $text = $h2t->get_text();
    get_clear_error();
    return $text;
}

// RETURN THE UTF-8 CONVERTED STRING IF IT'S NEEDED
function getutf8($str)
{
    if (!mb_check_encoding($str, "UTF-8")) {
        ob_start();
        $str = mb_convert_encoding($str, "UTF-8", mb_detect_order());
        ob_get_clean();
    }
    return $str;
}

// USING WORDPRESS FEATURES
function saltos_make_clickable($temp)
{
    require_once "lib/wordpress/wordpress.php";
    $temp = make_clickable($temp);
    return $temp;
}

// FOR SOME HREF REPLACEMENTS
function href_replace($temp)
{
    // REPLACE THE INTERNALS LINKS TO OPENCONTENT CALLS
    $orig = "href='" . get_base();
    $dest = str_replace("href=", "__href__=", $orig);
    $onclick = "onclick='parent.opencontent(this.href);return false' ";
    $orig = array($orig,str_replace("'", '"', $orig),str_replace("'", '', $orig));
    $dest = array($onclick . $dest,$onclick . str_replace("'", '"', $dest),$onclick . str_replace("'", '', $dest));
    $temp = str_replace($orig, $dest, $temp);
    // REPLACE THE MAILTO LINKS TO MAILTO CALLS
    $orig = "href='mailto:";
    $dest = str_replace("href=", "__href__=", $orig);
    $onclick = "onclick='parent.mailto(parent.substr(this.href,7));return false' ";
    $orig = array($orig,str_replace("'", '"', $orig),str_replace("'", '', $orig));
    $dest = array($onclick . $dest,$onclick . str_replace("'", '"', $dest),$onclick . str_replace("'", '', $dest));
    $temp = str_replace($orig, $dest, $temp);
    // REPLACE THE REST OF LINKS TO OPENWIN CALLS
    $orig = "href='";
    $dest = str_replace("href=", "__href__=", $orig);
    $onclick = "onclick='parent.openwin(this.href);return false' ";
    $orig = array($orig,str_replace("'", '"', $orig),str_replace("'", '', $orig));
    $dest = array($onclick . $dest,$onclick . str_replace("'", '"', $dest),$onclick . str_replace("'", '', $dest));
    $temp = str_replace($orig, $dest, $temp);
    // RESTORE THE __HREF__= TO HREF=
    $temp = str_replace("__href__=", "href=", $temp);
    return $temp;
}

// REMOVE ALL SCRIPT TAGS
function remove_script_tag($temp)
{
    $temp = preg_replace("@<script[^>]*?.*?</script>@siu", "", $temp);
    return $temp;
}

function remove_style_tag($temp)
{
    $temp = preg_replace("@<style[^>]*?.*?</style>@siu", "", $temp);
    return $temp;
}

function highlight_geshi($html, $lang = "")
{
    require_once "lib/geshi/geshi.php";
    if ($lang == "") {
        static $open1 = "<pre>\n<code";
        static $open2 = ">";
        static $close = "</code></pre>";
        $lenopen1 = strlen($open1);
        $lenopen2 = strlen($open2);
        $lenclose = strlen($close);
        $pos1 = strpos($html, $open1);
        while ($pos1 !== false) {
            $pos2 = strpos($html, $open2, $pos1 + $lenopen1);
            $lang = substr($html, $pos1 + $lenopen1, $pos2 - $pos1 - $lenopen1);
            $lang = trim($lang);
            $lang = str_replace(array('class="language-','"'), "", $lang);
            if ($lang == "") {
                $lang = "text";
            }
            $pos3 = strpos($html, $close, $pos2);
            $html2 = substr($html, $pos2 + $lenopen2, $pos3 - $pos2 - $lenopen2);
            $html2 = html_entity_decode($html2, ENT_COMPAT, "UTF-8");
            $geshi = new GeSHi($html2, $lang);
            $html3 = $geshi->parse_code();
            $html = substr_replace($html, $html3, $pos1, $pos3 + $lenclose - $pos1);
            $pos1 = strpos($html, $open1, $pos3);
        }
    } else {
        $geshi = new GeSHi($html, $lang);
        $html = $geshi->parse_code();
    }
    return $html;
}

function is_array_key_val($array)
{
    $count = 0;
    foreach ($array as $key => $val) {
        if (!is_numeric($key)) {
            return true;
        }
        if ($key != $count) {
            return true;
        }
        $count++;
    }
    return false;
}

function words_exists($words, $buffer)
{
    if (!is_array($words)) {
        $words = explode(" ", $words);
    }
    foreach ($words as $word) {
        if (stripos($buffer, $word) === false) {
            return false;
        }
    }
    return true;
}

// COPIED FROM https://stackoverflow.com/questions/1252693/using-str-replace-so-that-it-only-acts-on-the-first-match
function str_replace_first($from, $to, $content)
{
    $from = '/' . preg_quote($from, '/') . '/';
    return preg_replace($from, $to, $content, 1);
}

function str_split2($a, $b)
{
    $c = array();
    while (count($b)) {
        $d = array_shift($b);
        $c[] = substr($a, 0, $d);
        $a = substr($a, $d);
    }
    return $c;
}

function remove_utf8mb4_chars($cad)
{
    $len = mb_strlen($cad);
    for ($i = 0; $i < $len; $i++) {
        $char = mb_substr($cad, $i, 1);
        if (strlen($char) == 4) {
            $cad = mb_substr($cad, 0, $i) . mb_substr($cad, $i + 1);
            $len--;
            $i--;
        }
    }
    return $cad;
}
