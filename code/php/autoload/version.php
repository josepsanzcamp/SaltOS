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

define("__SVNVERSION__", "cd __DIR__; svnversion");
define("__GITVERSION__", "cd __DIR__; git rev-list HEAD --count");

function svnversion($dir = ".")
{
    if ($dir == "." && file_exists("../code")) {
        $dir = "../code";
    }
    // USING REGULAR FILE
    if (file_exists("${dir}/svnversion")) {
        return intval(file_get_contents("${dir}/svnversion"));
    }
    // USING SVNVERSION
    if (check_commands(getDefault("commands/svnversion", "svnversion"), getDefault("default/commandexpires", 60))) {
        return intval(ob_passthru(str_replace(
            array("__DIR__"),
            array($dir),
            getDefault("commands/__svnversion__", __SVNVERSION__)
        ), getDefault("default/commandexpires", 60)));
    }
    // NOTHING TO DO
    return 0;
}

function gitversion($dir = ".")
{
    if ($dir == "." && file_exists("../code")) {
        $dir = "../code";
    }
    // USING REGULAR FILE
    if (file_exists("${dir}/gitversion")) {
        return intval(file_get_contents("${dir}/gitversion"));
    }
    // USING GIT
    if (check_commands(getDefault("commands/gitversion", "git"), getDefault("default/commandexpires", 60))) {
        return intval(ob_passthru(str_replace(
            array("__DIR__"),
            array($dir),
            getDefault("commands/__gitversion__", __GITVERSION__)
        ), getDefault("default/commandexpires", 60)));
    }
    // NOTHING TO DO
    return 0;
}
