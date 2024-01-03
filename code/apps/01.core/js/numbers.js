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

if (typeof __numbers__ == "undefined" && typeof parent.__numbers__ == "undefined") {
    "use strict";
    var __numbers__ = 1;

    var numbers_running = 0;

    function update_numbers(app, num)
    {
        if (app != "") {
            var obj = $(".number_" + app);
        } else {
            var obj = $(".number");
        }
        // QUITAR NUMEROS
        $("span.number",obj).remove();
        // PONER SI ES NECESARIO
        if (num > 0) {
            num = max(min(num, 99), 0);
            clase = "number" + Math.ceil(Math.log10(num + 1));
            var span = "<span class='number " + clase + "'>" + num + "</span>";
            $(obj).append(span);
            numbers_running = 1;
        } else {
            numbers_running = 0;
        }
    }

    $(function () {
        $(document).on("mouseover",function () {
            if (numbers_running) {
                update_numbers("",0);
            }
        }).on("keydown",function () {
            if (numbers_running) {
                update_numbers("",0);
            }
        });
    });
}
