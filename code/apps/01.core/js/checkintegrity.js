/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2024 by Josep Sanz Campderrós
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

if (typeof __checkintegrity__ == "undefined" && typeof parent.__checkintegrity__ == "undefined") {
    "use strict";
    var __checkintegrity__ = 1;

    var integrity_executing = 0;

    function check_integrity()
    {
        if (integrity_executing) {
            return;
        }
        integrity_executing = 1;
        // SOME CHECKS
        if ($(".ui-layout-west").text() == "") {
            integrity_executing = 0;
            return;
        }
        // NORMAL USAGE
        var data = "action=integrity";
        $.ajax({
            url:"index.php",
            data:data,
            type:"get",
            success:function (response) {
                $(".ui-layout-center").append(response);
                integrity_executing = 0;
            },
            error:function (XMLHttpRequest,textStatus,errorThrown) {
                integrity_executing = 0;
            }
        });
    }

    $(function () {
        if (config_integrity_interval() > 0) {
            var integrity_counter = config_integrity_interval();
            setInterval(function () {
                integrity_counter = integrity_executing ? 0 : integrity_counter + 100;
                if (integrity_counter >= config_integrity_interval()) {
                    check_integrity();
                    integrity_counter = 0;
                }
            },100);
        }
    });
}
