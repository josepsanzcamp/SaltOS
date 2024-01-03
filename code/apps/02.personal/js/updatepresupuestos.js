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

if (typeof __updatepresupuestos__ == "undefined" && typeof parent.__updatepresupuestos__ == "undefined") {
    "use strict";
    var __updatepresupuestos__ = 1;

    function update_presupuestos()
    {
        var cliente = $("select[name$=id_cliente]");
        var posiblecli = $("select[name$=id_posiblecli]");
        var presupuesto = $("select[name$=id_presupuesto]");
        if (presupuestos_defaults == "") {
            presupuestos_defaults = $(presupuesto).html();
        }
        var data = "action=ajax&query=presupuestos&id_cliente=" + $(cliente).val() + "&id_posiblecli=" + $(posiblecli).val();
        $.ajax({
            url:"index.php",
            data:data,
            type:"get",
            success:function (response) {
                var options = presupuestos_defaults;
                var original = $(presupuesto).attr("original");
                $(response["rows"]).each(function () {
                    var selected = (this["id"] == original) ? "selected='selected'" : "";
                    options += "<option value='" + this["id"] + "' " + selected + ">" + this["nombre"] + "</option>";
                });
                $(presupuesto).html(options);
            },
            error:function (XMLHttpRequest,textStatus,errorThrown) {
                errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
            }
        });
    }

}

"use strict";
var presupuestos_defaults = "";

$(function () {
    update_presupuestos();
});
