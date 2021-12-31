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

if(typeof(__additem__)=="undefined" && typeof(parent.__additem__)=="undefined") {
    "use strict";
    var __additem__=1;

    function additem(obj) {
        var padre=$(obj).parent();
        var padre2=$(padre).parent();
        var maxiter=100;
        while(maxiter>0 && !$("table.tabla",padre2).length) {
            padre=$(padre).parent();
            padre2=$(padre).parent();
            maxiter--;
        }
        var limit=$("tr",padre).has("input[type=hidden]").length;
        var num=$("tr:visible",padre).has("input[type=hidden]").length;
        if(num<limit) {
            var count=0;
            $("tr",padre).has("input[type=hidden]").each(function() {
                if(num==count) $(this).removeClass("none");
                count++;
            });
            make_tables(padre);
            if(num+1==limit) $(obj).addClass("ui-state-disabled");
        }
    }

    function init_additem() {
        $(".init_additem").each(function() {
            var padre=$(this).parent();
            var padre2=$(padre).parent();
            var maxiter=100;
            while(maxiter>0 && !$("table.tabla",padre2).length) {
                padre=$(padre).parent();
                padre2=$(padre).parent();
                maxiter--;
            }
            var temp=$("input[type=hidden][name$=id]",padre);
            var total=0;
            $(temp).each(function() {
                if(is_numeric(this.value)) {
                    $(this).parent().removeClass("none");
                    total++;
                }
            });
            if(!total) additem(this);
        });

    }
}

"use strict";
$(function() { init_additem(); });
