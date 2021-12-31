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

function _javascript_location($url, $cond = "")
{
    javascript_template("window.location.href='${url}';", $cond);
}

function _javascript_history($go, $cond = "")
{
    if ($go) {
        javascript_template("history.go(${go})", $cond);
    } else {
        javascript_template("addcontent('reload')", $cond);
    }
}

function _javascript_opencontent($url, $cond = "")
{
    javascript_template("saltos.opencontent('${url}');", $cond);
}

function _javascript_addcontent($url, $cond = "")
{
    javascript_template("saltos.addcontent('${url}');", $cond);
}
