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

if (typeof __updatetareas__ == "undefined" && typeof parent.__updatetareas__ == "undefined") {
    "use strict";
    var __updatetareas__ = 1;

    function update_tareas()
    {
        if (typeof proyectos_init == "undefined") {
            setTimeout(function () {
                update_tareas(); },100);
            return;
        }
        if (!proyectos_init) {
            setTimeout(function () {
                update_tareas(); },100);
            return;
        }
        var proyecto = $("select[name$=id_proyecto]");
        var tarea = $("select[name$=id_tarea]");
        if (tareas_defaults == "") {
            tareas_defaults = $(tarea).html();
        }
        var data = "action=ajax&query=tareas&id_proyecto=" + $(proyecto).val();
        $.ajax({
            url:"index.php",
            data:data,
            type:"get",
            success:function (response) {
                var options = tareas_defaults;
                var original = $(tarea).attr("original");
                $(response["rows"]).each(function () {
                    var selected = (this["id"] == original) ? "selected='selected'" : "";
                    options += "<option value='" + this["id"] + "' " + selected + ">" + this["tarea"] + "</option>";
                });
                $(tarea).html(options);
            },
            error:function (XMLHttpRequest,textStatus,errorThrown) {
                errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
            }
        });
    }

}

"use strict";
var tareas_defaults = "";

$(function () {
    update_tareas();
});
