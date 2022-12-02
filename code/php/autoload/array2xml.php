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

function __array2xml_check_node_name($name)
{
    try {
        new DOMElement(":{$name}");
        return 1;
    } catch (DOMException $e) {
        return 0;
    }
}

function __array2xml_check_node_attr($name)
{
    try {
        new DOMAttr($name);
        return 1;
    } catch (DOMException $e) {
        return 0;
    }
}

function __array2xml_write_nodes(&$array, $level = null)
{
    if ($level === null) {
        $prefix = "";
        $postfix = "";
    } else {
        $prefix = str_repeat("\t", $level);
        $postfix = "\n";
        $level++;
    }
    $buffer = "";
    foreach ($array as $key => $val) {
        $key = limpiar_key($key);
        if (!__array2xml_check_node_name($key)) {
            show_php_error(array("phperror" => "Invalid XML tag name '{$key}'"));
        }
        $attr = "";
        if (is_array($val) && isset($val["value"]) && isset($val["#attr"])) {
            $attr = array();
            foreach ($val["#attr"] as $key2 => $val2) {
                $key2 = limpiar_key($key2);
                if (!__array2xml_check_node_attr($key2)) {
                    show_php_error(array("phperror" => "Invalid XML attr name '{$key2}'"));
                }
                $val2 = str_replace("&", "&amp;", $val2);
                $attr[] = "{$key2}=\"{$val2}\"";
            }
            $attr = " " . implode(" ", $attr);
            $val = $val["value"];
        }
        if (is_array($val)) {
            $buffer .= "{$prefix}<{$key}{$attr}>{$postfix}";
            $buffer .= __array2xml_write_nodes($val, $level);
            $buffer .= "{$prefix}</{$key}>{$postfix}";
        } else {
            $val = remove_bad_chars(null2string($val));
            if (strpos($val, "<") !== false || strpos($val, "&") !== false) {
                $count = 1;
                while ($count) {
                    $val = str_replace(array("<![CDATA[","]]>"), "", $val, $count);
                }
                $val = "<![CDATA[{$val}]]>";
            }
            if ($val != "") {
                $buffer .= "{$prefix}<{$key}{$attr}>{$val}</{$key}>{$postfix}";
            } else {
                $buffer .= "{$prefix}<{$key}{$attr}/>{$postfix}";
            }
        }
    }
    return $buffer;
}

function array2xml($array, $usecache = true, $usexmlminify = true)
{
    $array = array("root" => $array);
    if ($usecache) {
        $cache = get_cache_file(array($array,$usexmlminify), ".xml");
        if (file_exists($cache)) {
            return file_get_contents($cache);
        }
    }
    $buffer = __array2xml_write_nodes($array, $usexmlminify ? null : 0);
    if ($usecache) {
        file_put_contents($cache, $buffer);
        chmod($cache, 0666);
    }
    return $buffer;
}
