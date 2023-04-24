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

if (typeof __favoritos__ == "undefined" && typeof parent.__favoritos__ == "undefined") {
    "use strict";
    var __favoritos__ = 1;

    function add_bookmark()
    {
        var data = 'action=favoritos&url=' + encodeURIComponent($("[name$=nuevofavorito]").val()) + "&refresh=1";
        $.ajax({
            url:'',
            data:data,
            type:"post",
            success:function (response) {
                $(".ui-layout-center").append(response);
            },
            error:function (XMLHttpRequest,textStatus,errorThrown) {
                errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
            }
        });
    }

    function update_tabs()
    {
        setTimeout(function () {
            var active = getIntCookie("saltos_favoritos_tab");
            $(".tabs").tabs("option","active",active);
            $(".tabs").on("tabsactivate",function (event,ui) {
                var active = $(".tabs").tabs("option","active");
                if (!in_array(active,[0,2])) {
                    return;
                }
                setIntCookie("saltos_favoritos_tab",active);
            });
        },100);
    }

    function init_previews()
    {
        var tabla = $(".tabla tr:not(:eq(0))").each(function () {
            var id1 = $("td:eq(1)",this).text();
            var id2 = $("td:eq(1) span",this).attr("title");
            var url1 = $("td:eq(2)",this).text();
            var url2 = $("td:eq(2) span",this).attr("title");
            var title1 = $("td:eq(3)",this).text();
            var title2 = $("td:eq(3) span",this).attr("title");
            var id = (typeof id2 == "undefined") ? id1 : id2;
            var url = (typeof url2 == "undefined") ? url1 : url2;
            var title = (typeof title2 == "undefined") ? title1 : title2;
            var img = $("<img>").attr("class","preview ui-state-default ui-corner-all").attr("src","?action=favoritos&id=" + id)
            var a = $("<a>").attr("href",url).attr("title",title).on("click",function () {
                openwin(this.href); return false; }).append(img);
            $(".previews").append(a);
        });
    }
}

"use strict";
$(function () {
    if (getParam("page") == "favoritos") {
        update_tabs(); init_previews();
    }
});
