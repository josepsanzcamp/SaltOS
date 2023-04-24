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

if (typeof __partes__ == "undefined" && typeof parent.__partes__ == "undefined") {
    "use strict";
    var __partes__ = 1;

    function update_totales_parte()
    {
        for (var i = 0,len = document.form.elements.length; i < len; i++) {
            obj = document.form.elements[i];
            if (obj.type) {
                if (obj.name.substr(obj.name.length - 5,5) == "horas") {
                    var prefix = obj.name.substr(0,obj.name.length - 5);
                    var precio_obj = eval("document.form." + prefix + "precio");
                    var total_obj = eval("document.form." + prefix + "total");
                    var horas = round(floatval(obj.value.replace(",",".")),2);
                    var precio = round(floatval(precio_obj.value.replace(",",".")),2);
                    var total = round(floatval(total_obj.value.replace(",",".")),2);
                    obj.value = horas;
                    precio_obj = precio;
                    total_obj.value = total;
                    if (precio) {
                        var total = round(floatval(horas * precio),2);
                        total_obj.value = total;
                    }
                }
            }
        }
    }

}
