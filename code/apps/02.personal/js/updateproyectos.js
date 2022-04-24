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

if (typeof __updateproyectos__ == "undefined" && typeof parent.__updateproyectos__ == "undefined") {
    "use strict";
    var __updateproyectos__ = 1;

    function update_proyectos()
    {
        var cliente = $("*[name$=id_cliente]");
        var posiblecli = $("select[name$=id_posiblecli]");
        var proyecto = $("select[name$=id_proyecto]");
        if (proyectos_defaults == "") {
            proyectos_defaults = $(proyecto).html();
        }
        if (typeof $(posiblecli).val() == "undefined") {
            var data = "action=ajax&query=proyectos&id_cliente=" + $(cliente).val();
        } else {
            var data = "action=ajax&query=proyectos&id_cliente=" + $(cliente).val() + "&id_posiblecli=" + $(posiblecli).val();
        }
        $.ajax({
            url:"index.php",
            data:data,
            type:"get",
            success:function (response) {
                var options = proyectos_defaults;
                var original = $(proyecto).attr("original");
                $(response["rows"]).each(function () {
                    var selected = (this["id"] == original) ? "selected='selected'" : "";
                    options += "<option value='" + this["id"] + "' " + selected + ">" + this["nombre"] + "</option>";
                });
                $(proyecto).html(options);
                proyectos_init = 1;
            },
            error:function (XMLHttpRequest,textStatus,errorThrown) {
                errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
            }
        });
    }

}

"use strict";
var proyectos_defaults = "";
var proyectos_init = 0;

$(function () {
    update_proyectos();
});
