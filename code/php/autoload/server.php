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

function force_ssl()
{
    // SOME CHECKS
    if (!eval_bool(getDefault("server/forcessl"))) {
        return;
    }
    $serverport = getServer("SERVER_PORT");
    $porthttps = getDefault("server/porthttps", 443);
    if ($serverport == $porthttps) {
        return;
    }
    // MAIN VARIABLES
    $protocol = "https://";
    $servername = getDefault("server/hostname", getServer("SERVER_NAME"));
    $addedport = "";
    $scriptname = getDefault("server/pathname", getServer("SCRIPT_NAME"));
    $querystring = getServer("QUERY_STRING");
    // SOME CHECKS
    if (substr($scriptname, 0, 1) != "/") {
        $scriptname = "/" . $scriptname;
    }
    if (basename($scriptname) == getDefault("server/dirindex", "index.php")) {
        $scriptname = dirname($scriptname);
        if (substr($scriptname, -1, 1) != "/") {
            $scriptname .= "/";
        }
    }
    // SOME CHECKS
    if ($querystring) {
        $querystring = "?" . str_replace("+", "%20", $querystring);
    }
    if ($porthttps != 443) {
        $addedport = ":{$porthttps}";
    }
    // CONTINUE
    $url = $protocol . $servername . $addedport . $scriptname . $querystring;
    javascript_location($url);
    die();
}

function getServer($index, $default = "")
{
    return isset($_SERVER[$index]) ? $_SERVER[$index] : $default;
}

function get_base()
{
    // MAIN VARIABLES
    $protocol = "http://";
    $servername = getDefault("server/hostname", getServer("SERVER_NAME"));
    $addedport = "";
    $scriptname = getDefault("server/pathname", getServer("SCRIPT_NAME"));
    // SOME CHECKS
    if (substr($scriptname, 0, 1) != "/") {
        $scriptname = "/" . $scriptname;
    }
    if (basename($scriptname) == getDefault("server/dirindex", "index.php")) {
        $scriptname = dirname($scriptname);
        if (substr($scriptname, -1, 1) != "/") {
            $scriptname .= "/";
        }
    }
    // SOME CHECKS
    $serverport = getServer("SERVER_PORT");
    $porthttp = getDefault("server/porthttp", 80);
    $porthttps = getDefault("server/porthttps", 443);
    if ($serverport == $porthttp) {
        $protocol = "http://";
        if ($porthttp != 80) {
            $addedport = ":$serverport";
        }
    }
    if ($serverport == $porthttps) {
        $protocol = "https://";
        if ($porthttps != 443) {
            $addedport = ":$serverport";
        }
    }
    // CONTINUE
    $url = $protocol . $servername . $addedport . $scriptname;
    return $url;
}
