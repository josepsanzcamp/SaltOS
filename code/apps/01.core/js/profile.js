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

if (typeof __profile__ == "undefined" && typeof parent.__profile__ == "undefined") {
    "use strict";
    var __profile__ = 1;

    function update_score()
    {
        var pass = $("input[name$=password_new]");
        if ($(pass).val()) {
            var data = "action=score&pass=" + encodeURIComponent($(pass).val()) + "&format=json"
            $.ajax({
                url:"index.php",
                data:data,
                type:"post",
                success:function (response) {
                    $(".score").removeClass("none");
                    $(".score").attr("src",response["image"]);
                    if (intval(response["valid"])) {
                        $(pass).removeClass("ui-state-error");
                    } else {
                        $(pass).addClass("ui-state-error");
                    }
                },
                error:function (XMLHttpRequest,textStatus,errorThrown) {
                    errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
                }
            });
        } else {
            $(".score").addClass("none");
            $(pass).removeClass("ui-state-error");
        }
    }

    function check_passwords()
    {
        var pass = $("input[name$=password_new]");
        var pass2 = $("input[name$=password_new2]");
        if ($(pass).val() == $(pass2).val()) {
            $(pass2).removeClass("ui-state-error");
        } else {
            $(pass2).addClass("ui-state-error");
        }
    }

}

"use strict";
$(function () {
    var checkbox = "input[type=checkbox][name$=email_default]";
    $(checkbox).on("change",function () {
        var value = $(this).prop("checked");
        $(checkbox).prop("checked",false);
        $(this).prop("checked",value);
    });
});
