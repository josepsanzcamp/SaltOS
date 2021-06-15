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

function use_table_cookies($name, $value = "", $default = "")
{
    $uid = current_user();
    if ($uid) {
        if ($value != "") {
            if ($value == "null") {
                $value = "";
            }
            $query = "SELECT COUNT(*) FROM tbl_cookies WHERE id_usuario='${uid}' AND clave='${name}'";
            $count = execute_query($query);
            if ($count > 1) {
                $query = "DELETE FROM tbl_cookies WHERE id_usuario='${uid}' AND clave='${name}'";
                db_query($query);
                $count = 0;
            }
            if ($count == 0) {
                $query = make_insert_query("tbl_cookies", array(
                    "id_usuario" => $uid,
                    "clave" => $name,
                    "valor" => $value
                ));
                db_query($query);
            } else {
                $query = make_update_query("tbl_cookies", array(
                    "valor" => $value
                ), "id_usuario='${uid}' AND clave='${name}'");
                db_query($query);
            }
        } else {
            $query = "SELECT valor FROM tbl_cookies WHERE id_usuario='$uid' AND clave='$name'";
            $value = execute_query($query);
            if ($value == "") {
                $value = $default;
            }
        }
    } else {
        if ($value == "") {
            $value = $default;
        }
    }
    return $value;
}

function useCookie($name, $value = "", $default = "")
{
    if ($value != "") {
        setCookie2($name, $value == "null" ? "" : $value);
    } elseif (isset($_COOKIE[$name]) && $_COOKIE[$name] != "") {
        $value = $_COOKIE[$name];
    } else {
        $value = $default;
    }
    return $value;
}

function getCookie2($index, $default = "")
{
    if (isset($_COOKIE[$index])) {
        return $_COOKIE[$index];
    }
    return $default;
}

function setCookie2($index, $value = "")
{
    $expire = time() + getDefault("security/cookietimeout");
    $path = dirname(getDefault("server/pathname", getServer("SCRIPT_NAME")));
    $secure = eval_bool(getDefault("server/forcessl"));
    setcookie($index, $value, $expire, $path, "", $secure, false);
    $index = "__" . $index . "__";
    if ($value != "") {
        $value = $expire;
    }
    setcookie($index, $value, $expire, $path, "", $secure, false);
}
