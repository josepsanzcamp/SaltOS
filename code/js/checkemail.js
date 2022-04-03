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

if (typeof __checkemail__ == "undefined" && typeof parent.__checkemail__ == "undefined") {
    "use strict";
    var __checkemail__ = 1;

    var inbox_executing = 0;

    function check_inbox(sync)
    {
        if (typeof sync == "undefined") {
            var sync = 0;
        }
        // PREVENT OVERLOAD
        if (inbox_executing && sync) {
            alerta(lang_inbackground());
        }
        if (inbox_executing) {
            return;
        }
        inbox_executing = 1;
        // SOME CHECKS
        if ($(".ui-layout-west").text() == "") {
            inbox_executing = 0;
            return;
        }
        // CHECK IF IT IS SYNC
        if (sync) {
            setParam("action","getmail");
            submit1(function () {
                inbox_executing = 0; });
            return
        }
        // NORMAL USAGE
        var data = "action=getmail&ajax=1";
        $.ajax({
            url:"index.php",
            data:data,
            type:"get",
            success:function (response) {
                $(".ui-layout-center").append(response);
                inbox_executing = 0;
            },
            error:function (XMLHttpRequest,textStatus,errorThrown) {
                inbox_executing = 0;
            }
        });
    }

    var outbox_executing = 0;

    function check_outbox(sync)
    {
        if (typeof sync == "undefined") {
            var sync = 0;
        }
        // PREVENT OVERLOAD
        if (outbox_executing && sync) {
            alerta(lang_inbackground());
        }
        if (outbox_executing) {
            return;
        }
        outbox_executing = 1;
        // SOME CHECKS
        if ($(".ui-layout-west").text() == "") {
            outbox_executing = 0;
            return;
        }
        // CHECK IF IT IS SYNC
        if (sync) {
            setParam("action","sendmail");
            submit1(function () {
                outbox_executing = 0; });
            return
        }
        // NORMAL USAGE
        var data = "action=sendmail&ajax=1";
        $.ajax({
            url:"index.php",
            data:data,
            type:"get",
            success:function (response) {
                $(".ui-layout-center").append(response);
                outbox_executing = 0;
            },
            error:function (XMLHttpRequest,textStatus,errorThrown) {
                outbox_executing = 0;
            }
        });
    }

    function is_correo_list()
    {
        if (typeof getParam != 'function') {
            return 0;
        }
        return (getParam("page") == "correo" && getParam("action") == "list") ? 1 : 0;
    }

    function update_correo_list()
    {
        var islist = (getParam("page") == "correo" && getParam("action") == "list") ? 1 : 0;
        var nocheck = $("input.slave[type=checkbox]:checked").length ? 0 : 1;
        var istop = $(document).scrollTop() > 1 ? 0 : 1;
        var intab = $(".tabs").tabs("option","active") ? 0 : 1;
        var noover = $("td.ui-state-highlight").length ? 0 : 1;
        return islist && nocheck && istop && intab && noover;
    }

    $(function () {
        if (config_inbox_interval() > 0) {
            var inbox_counter = config_inbox_interval();
            setInterval(function () {
                inbox_counter = inbox_executing ? 0 : inbox_counter + 100;
                if (inbox_counter >= config_inbox_interval()) {
                    if (is_correo_list()) {
                        var disabled = $("#recibir").hasClass("ui-state-disabled");
                        $("#recibir").addClass("ui-state-disabled");
                    }
                    check_inbox();
                    if (is_correo_list()) {
                        var interval = setInterval(function () {
                            if (!inbox_executing) {
                                clearInterval(interval);
                                if (disabled) {
                                    $("#recibir").addClass("ui-state-disabled");
                                }
                                if (!disabled) {
                                    $("#recibir").removeClass("ui-state-disabled");
                                }
                            }
                        },100);
                    }
                    inbox_counter = 0;
                }
            },100);
        }
        if (config_outbox_interval() > 0) {
            var outbox_counter = config_outbox_interval();
            setInterval(function () {
                outbox_counter = outbox_executing ? 0 : outbox_counter + 100;
                if (outbox_counter >= config_outbox_interval()) {
                    if (is_correo_list()) {
                        var disabled = $("#enviar").hasClass("ui-state-disabled");
                        $("#enviar").addClass("ui-state-disabled");
                    }
                    check_outbox();
                    if (is_correo_list()) {
                        var interval = setInterval(function () {
                            if (!outbox_executing) {
                                clearInterval(interval);
                                if (disabled) {
                                    $("#enviar").addClass("ui-state-disabled");
                                }
                                if (!disabled) {
                                    $("#enviar").removeClass("ui-state-disabled");
                                }
                            }
                        },100);
                    }
                    outbox_counter = 0;
                }
            },100);
        }
    });
}
