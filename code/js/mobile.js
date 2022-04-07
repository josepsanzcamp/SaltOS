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

if (typeof __mobile__ == "undefined" && typeof parent.__mobile__ == "undefined") {
    "use strict";
    var __mobile__ = 1;

    /* ERROR HANDLER */
    window.onerror = function (msg, file, line, column, error) {
        var data = {
            "jserror":msg,
            "details":"Error on file " + file + ":" + line + ":" + column + ", userAgent is " + navigator.userAgent,
            "backtrace":error.stack
        };
        $.ajax({
            url:"index.php?action=adderror",
            data:JSON.stringify(data),
            type:"post"
        });
    };

    /* GENERIC FUNCTIONS */
    function floatval2(obj)
    {
        _format_number(obj,0);
    }

    function intval2(obj)
    {
        _format_number(obj,1);
    }

    function _format_number(obj,punto)
    {
        var texto = obj.value;
        var texto2 = "";
        var numero = 0;
        for (var i = 0,len = texto.length; i < len; i++) {
            var letra = substr(texto,i,1);
            if (letra >= "0" && letra <= "9") {
                texto2 += letra;
                numero = 1;
            } else if ((letra == "." || letra == ",") && !punto) {
                if (!numero) {
                    texto2 += "0";
                }
                texto2 += ".";
                punto = 1;
            } else if (letra == "-" && texto2.length == 0) {
                texto2 += "-";
            }
        }
        if (texto != texto2) {
            obj.value = texto2;
        }
    }

    function check_required()
    {
        var field = null;
        var label = "";
        $("[isrequired=true]").each(function () {
            // CHECK FOR VISIBILITY
            if (!$(this).is(":visible")) {
                return;
            }
            // CONTINUE
            var valor = $(this).val();
            if ($(this).is("select")) {
                if (valor == "0") {
                    valor = "";
                }
            }
            if (!valor) {
                $(this).addClass("ui-state-error");
            } else {
                $(this).removeClass("ui-state-error");
            }
            if (!valor && !field) {
                field = this;
                label = $(this).attr("labeled");
            }
        });
        if (field) {
            var requiredfield = lang_requiredfield();
            alerta(requiredfield + ": " + label,function () {
                $(field).trigger("focus"); });
        }
        return field == null;
    }

    function copy_value(dest,src)
    {
        $("#" + dest).val($("#" + src).val());
    }

    function intelligence_cut(txt,max)
    {
        var len = strlen(txt);
        if (len > max) {
            while (max > 0 && substr(txt,max,1) != " ") {
                max--;
            }
            if (max == 0) {
                while (max < len && substr(txt,max,1) != " ") {
                    max++;
                }
            }
            if (max > 0) {
                if (in_array(substr(txt,max - 1,1),[",",".","-","("])) {
                    max--;
                }
            }
            var preview = (max == len) ? txt : substr(txt,0,max) + "...";
        } else {
            var preview = txt;
        }
        return preview;
    }

    function dateval(value)
    {
        value = str_replace("-"," ",value);
        value = str_replace(":"," ",value);
        value = str_replace(","," ",value);
        value = str_replace("."," ",value);
        value = str_replace("/"," ",value);
        var temp = "";
        while (temp != (value = str_replace("  "," ",value))) {
            temp = value;
        }
        temp = explode(" ",value);
        for (var i = 0,len = temp.length; i < len; i++) {
            temp[i] = intval(temp[i]);
        }
        for (var i = 0; i < 3; i++) {
            if (typeof temp[i] == "undefined") {
                temp[i] = 0;
            }
        }
        if (temp[2] > 1900) {
            temp[2] = min(9999,max(0,temp[2]));
            temp[1] = min(12,max(0,temp[1]));
            temp[0] = min(__days_of_a_month(temp[2],temp[1]),max(0,temp[0]));
            value = sprintf("%04d-%02d-%02d",temp[2],temp[1],temp[0]);
        } else {
            temp[0] = min(9999,max(0,temp[0]));
            temp[1] = min(12,max(0,temp[1]));
            temp[2] = min(__days_of_a_month(temp[0],temp[1]),max(0,temp[2]));
            value = sprintf("%04d-%02d-%02d",temp[0],temp[1],temp[2]);
        }
        return value;
    }

    function timeval(value)
    {
        value = str_replace("-"," ",value);
        value = str_replace(":"," ",value);
        value = str_replace(","," ",value);
        value = str_replace("."," ",value);
        value = str_replace("/"," ",value);
        var temp = "";
        while (temp != (value = str_replace("  "," ",value))) {
            temp = value;
        }
        temp = explode(" ",value);
        for (var i = 0,len = temp.length; i < len; i++) {
            temp[i] = intval(temp[i]);
        }
        for (var i = 0; i < 3; i++) {
            if (typeof temp[i] == "undefined") {
                temp[i] = 0;
            }
        }
        temp[0] = min(24,max(0,temp[0]));
        temp[1] = min(59,max(0,temp[1]));
        temp[2] = min(59,max(0,temp[2]));
        value = sprintf("%02d:%02d:%02d",temp[0],temp[1],temp[2]);
        return value;
    }

    function __days_of_a_month(year,month)
    {
        return date("t",strtotime(sprintf("%04d-%02d-%02d",year,month,1)));
    }

    function check_datetime(orig,comp,dest)
    {
        var orig_obj_date = ("input[name$=" + orig + "_date]");
        var orig_obj_time = ("input[name$=" + orig + "_time]");
        var dest_obj_date = ("input[name$=" + dest + "_date]");
        var dest_obj_time = ("input[name$=" + dest + "_time]");
        if ($(orig_obj_date).val() == "") {
            return;
        }
        if ($(orig_obj_time).val() == "") {
            return;
        }
        if ($(dest_obj_date).val() == "") {
            return;
        }
        if ($(dest_obj_time).val() == "") {
            return;
        }
        var orig_date = explode("-",dateval($(orig_obj_date).val()));
        var orig_time = explode(":",timeval($(orig_obj_time).val()));
        var dest_date = explode("-",dateval($(dest_obj_date).val()));
        var dest_time = explode(":",timeval($(dest_obj_time).val()));
        var orig_unix = mktime(orig_time[0],orig_time[1],orig_time[2],orig_date[1],orig_date[2],orig_date[0]);
        var dest_unix = mktime(dest_time[0],dest_time[1],dest_time[2],dest_date[1],dest_date[2],dest_date[0]);
        var dest_unix2 = dest_unix;
        if (comp == "le" && dest_unix < orig_unix) {
            dest_unix2 = orig_unix;
        }
        if (comp == "ge" && dest_unix > orig_unix) {
            dest_unix2 = orig_unix;
        }
        if (dest_unix != dest_unix2) {
            $(dest_obj_date).val(implode("-",orig_date));
            $(dest_obj_time).val(implode(":",orig_time));
            $(dest_obj_time).trigger("change");
        }
    }

    function check_date(orig,comp,dest)
    {
        var orig_obj_date = ("input[name$=" + orig + "]");
        var dest_obj_date = ("input[name$=" + dest + "]");
        if ($(orig_obj_date).val() == "") {
            return;
        }
        if ($(dest_obj_date).val() == "") {
            return;
        }
        var orig_date = explode("-",dateval($(orig_obj_date).val()));
        var dest_date = explode("-",dateval($(dest_obj_date).val()));
        var orig_unix = mktime(12,0,0,orig_date[1],orig_date[2],orig_date[0]);
        var dest_unix = mktime(12,0,0,dest_date[1],dest_date[2],dest_date[0]);
        var dest_unix2 = dest_unix;
        if (comp == "le" && dest_unix < orig_unix) {
            dest_unix2 = orig_unix;
        }
        if (comp == "ge" && dest_unix > orig_unix) {
            dest_unix2 = orig_unix;
        }
        if (dest_unix != dest_unix2) {
            $(dest_obj_date).val(implode("-",orig_date));
            $(dest_obj_date).trigger("change");
        }
    }

    function check_time(orig,comp,dest)
    {
        var orig_obj_time = ("input[name$=" + orig + "]");
        var dest_obj_time = ("input[name$=" + dest + "]");
        if ($(orig_obj_time).val() == "") {
            return;
        }
        if ($(dest_obj_time).val() == "") {
            return;
        }
        var orig_time = explode(":",timeval($(orig_obj_time).val()));
        var dest_time = explode(":",timeval($(dest_obj_time).val()));
        var orig_unix = mktime(orig_time[0],orig_time[1],orig_time[2],1,1,1970);
        var dest_unix = mktime(dest_time[0],dest_time[1],dest_time[2],1,1,1970);
        var dest_unix2 = dest_unix;
        if (comp == "le" && dest_unix < orig_unix) {
            dest_unix2 = orig_unix;
        }
        if (comp == "ge" && dest_unix > orig_unix) {
            dest_unix2 = orig_unix;
        }
        if (dest_unix != dest_unix2) {
            $(dest_obj_time).val(implode(":",orig_time));
            $(dest_obj_time).trigger("change");
        }
    }

    function get_keycode(event)
    {
        var keycode = 0;
        if (event.keyCode) {
            keycode = event.keyCode;
        } else if (event.which) {
            keycode = event.which;
        } else {
            keycode = event.charCode;
        }
        return keycode;
    }

    function is_enterkey(event)
    {
        return get_keycode(event) == 13;
    }

    function is_escapekey(event)
    {
        return get_keycode(event) == 27;
    }

    function is_disabled(obj)
    {
        return $(obj).hasClass("ui-state-disabled");
    }

    /* FOR DEBUG PURPOSES */
    function addlog(msg)
    {
        $.ajax({
            url:"index.php?action=addlog",
            data:msg,
            type:"post"
        });
    }

    /* FOR SECURITY ISSUES */
    function security_iframe(obj)
    {
        // PREPARE SCHEMAS
        var schema1 = parse_url(window.location.href);
        var schema2 = parse_url($(obj).prop("src"));
        // CHECK HOST
        var isUndefined_host1 = typeof schema1["host"] == "undefined";
        var isUndefined_host2 = typeof schema2["host"] == "undefined";
        if (isUndefined_host1 && !isUndefined_host2) {
            return false;
        }
        if (!isUndefined_host1 && isUndefined_host2) {
            return false;
        }
        if (!isUndefined_host1 && !isUndefined_host2 && schema1["host"] != schema2["host"]) {
            return false;
        }
        // CHECK PROTOCOL
        var isUndefined_schema1 = typeof schema1["schema"] == "undefined";
        var isUndefined_schema2 = typeof schema2["schema"] == "undefined";
        if (isUndefined_schema1 && !isUndefined_schema2) {
            return false;
        }
        if (!isUndefined_schema1 && isUndefined_schema2) {
            return false;
        }
        if (!isUndefined_schema1 && !isUndefined_schema2 && schema1["schema"] != schema2["schema"]) {
            return false;
        }
        // CHECK PORT
        var isUndefined_port1 = typeof schema1["port"] == "undefined";
        var isUndefined_port2 = typeof schema2["port"] == "undefined";
        if (isUndefined_port1 && !isUndefined_port2) {
            return false;
        }
        if (!isUndefined_port1 && isUndefined_port2) {
            return false;
        }
        if (!isUndefined_port1 && !isUndefined_port2 && schema1["port"] != schema2["port"]) {
            return false;
        }
        // RETURN RESULT
        return true;
    }

    /* REPLACE FOR THE ALERTS AND CONFIRMS BOXES */
    function dialog(title,message,buttons)
    {
        // CHECK SOME PARAMETERS
        if (typeof message == "undefined") {
            var message = "";
        }
        if (typeof buttons == "undefined") {
            var buttons = function () {};
        }
        // SOME PREDEFINED ACTIONS
        if (title == "close") {
            if ($(".ui-dialog:visible").length > 0) {
                $("#dialog").hide();
                $("#dialog").page("destroy");
                if (typeof message == "function") {
                    message();
                }
            }
            return false;
        }
        if (title == "isopen") {
            return $(".ui-dialog:visible").length > 0;
        }
        if ($(".ui-dialog:visible").length > 0) {
            return false;
        }
        // IF MESSAGE EXISTS, OPEN IT
        if (message == "") {
            return false;
        }
        if ($("#dialog").length == 0) {
            $("body").append("<div data-role='dialog' id='dialog'></div>");
            $("#dialog").append("<div data-role='header'><h1></h1></div>");
            $("#dialog").append("<div data-role='content'><h4></h4><p></p></div>");
        }
        $("#dialog h1").html(title);
        $("#dialog h4").html(message);
        $("#dialog p").html("");
        jQuery.each(buttons,function (btn,fn) {
            $("#dialog p").append("<a href='javascript:void(0)' data-role='button' data-mini='true' data-inline='true'>" + btn + "</a>");
            $("#dialog p a:last").on("click",function () {
                dialog("close",fn); });
        });
        if ($("#dialog p a").length == 0) {
            $("#dialog p").append("<a href='javascript:void(0)' data-role='button' data-mini='true' data-inline='true'>" + lang_buttoncontinue() + "</a>");
            $("#dialog p a:last").on("click",function () {
                dialog("close"); });
        }
        $("#dialog").page();
        //~ $("#dialog").css("margin-top",$(document).scrollTop());
        $("#dialog .ui-content").addClass("ui-state-highlight");
        $("#dialog").show();
        $("#dialog p a:first").trigger("focus");
        var interval = setInterval(function () {
            if (dialog("isopen")) {
                var height1 = $(window).height();
                var height2 = $("#dialog .ui-dialog-contain").height();
                var offset = $(document).scrollTop();
                $("#dialog").css("margin-top",height1 / 2 - height2 / 2 + offset);
            } else {
                clearInterval(interval);
            }
        },100);
        return true;
    }

    /* FOR NOTIFICATIONS FEATURES */
    function make_notice()
    {
        // REMOVE ALL NOTIFICATIONS EXCEPT THE VOID ELEMENT, IT'S IMPORTANT!!!
        if ($("#jGrowl").length > 0) {
            $(".jGrowl-notification").each(function () {
                if ($(this).text() != "") {
                    $(this).remove();
                }
            });
        }
    }

    function notice(title,message,arg1,arg2,arg3)
    {
        // CHECK SOME PARAMETERS
        var sticky = false;
        var action = function () {};
        var theme = "ui-state-highlight";
        if (typeof arg1 == "boolean") {
            sticky = arg1;
        }
        if (typeof arg1 == "function") {
            action = arg1;
        }
        if (typeof arg1 == "string") {
            theme = arg1;
        }
        if (typeof arg2 == "boolean") {
            sticky = arg2;
        }
        if (typeof arg2 == "function") {
            action = arg2;
        }
        if (typeof arg2 == "string") {
            theme = arg2;
        }
        if (typeof arg3 == "boolean") {
            sticky = arg3;
        }
        if (typeof arg3 == "function") {
            action = arg3;
        }
        if (typeof arg3 == "string") {
            theme = arg3;
        }
        // EXECUTE THE CODE TO ADD THE INLINE NOTIFICATION
        $(".ui-footer").prepend("<div class='ui-bar ui-corner-all jGrowl-notification " + theme + "'>" + title + ": " + message + "</div>");
        var div = $(".ui-footer div:first");
        var timeout = null;
        if (!sticky) {
            timeout = setTimeout(function () {
                $(div).remove();
                action();
            },10000);
        }
        $(div).on("click",function () {
            if (timeout) {
                clearTimeout(timeout);
            }
            $(div).remove();
            action();
        });
    }

    /* FOR COOKIE MANAGEMENT */
    var cookies_data = {};
    var cookies_interval = null;
    var cookies_counter = 0;

    function __sync_cookies_helper()
    {
        for (var hash in cookies_data) {
            if (cookies_data[hash].sync) {
                if (cookies_data[hash].val != cookies_data[hash].orig) {
                    var data = "action=cookies&name=" + encodeURIComponent(cookies_data[hash].key) + "&value=" + encodeURIComponent(cookies_data[hash].val);
                    var value = $.ajax({
                        url:"index.php",
                        data:data,
                        type:"post",
                        async:false,
                    }).responseText;
                    if (value != "") {
                        cookies_data[hash].orig = cookies_data[hash].val;
                        cookies_data[hash].sync = 0;
                    }
                } else {
                    cookies_data[hash].sync = 0;
                }
            }
        }
    }

    function sync_cookies(cmd)
    {
        if (typeof cmd == "undefined") {
            var cmd = "";
        }
        if (cmd == "stop") {
            if (cookies_interval != null) {
                clearInterval(cookies_interval);
                cookies_interval = null;
            }
            __sync_cookies_helper();
            for (var hash in cookies_data) {
                delete cookies_data[hash];
            }
        }
        if (cmd == "start") {
            // REQUEST ALL COOKIES
            var data = "action=ajax&query=cookies";
            $.ajax({
                url:"index.php",
                data:data,
                type:"get",
                async:false,
                success:function (response) {
                    $(response["rows"]).each(function () {
                        var hash = md5(this["clave"]);
                        cookies_data[hash] = {
                            "key":this["clave"],
                            "val":this["valor"],
                            "orig":this["valor"],
                            "sync":0
                        };
                    });
                },
                error:function (XMLHttpRequest,textStatus,errorThrown) {
                    errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
                }
            });
            cookies_counter = 0;
            cookies_interval = setInterval(function () {
                cookies_counter = cookies_counter + 100;
                if (cookies_counter >= 1000) {
                    __sync_cookies_helper();
                    cookies_counter = 0;
                }
            },100);
        }
    }

    function getCookie(name)
    {
        var hash = md5(name);
        if (typeof cookies_data[hash] == "undefined") {
            value = $.cookie(name);
        } else {
            value = cookies_data[hash].val;
        }
        return value;
    }

    function getIntCookie(name)
    {
        return intval(getCookie(name));
    }

    function getBoolCookie(name)
    {
        return getIntCookie(name) ? true : false;
    }

    function setCookie(name,value)
    {
        var hash = md5(name);
        if (typeof cookies_data[hash] == "undefined") {
            if (cookies_interval != null) {
                cookies_data[hash] = {
                    "key":name,
                    "val":value,
                    "orig":value + "!",
                    "sync":1
                };
                cookies_counter = 0;
            } else {
                $.cookie(name,value,{expires:365,path:"/"});
            }
        } else {
            if (cookies_data[hash].val != value) {
                cookies_data[hash].val = value;
                cookies_data[hash].sync = 1;
            }
            cookies_counter = 0;
        }
    }

    function setIntCookie(name,value)
    {
        setCookie(name,intval(value));
    }

    function setBoolCookie(name,value)
    {
        setIntCookie(name,value ? 1 : 0);
    }

    /* FOR BLOCK THE UI AND NOT PERMIT 2 REQUESTS AT THE SAME TIME */
    function loadingcontent(message)
    {
        // CHECK PARAMETERS
        if (typeof message == "undefined") {
            var message = lang_loading();
        }
        // CHECK IF EXIST ANOTHER BLOCKUI
        if (isloadingcontent()) {
            $(".ui-loader > h1").text(message);
            return false;
        }
        // ACTIVATE THE BLOCK UI FEATURE
        $.mobile.loading("show",{
            text:message,
            textVisible:true,
            theme:"a",
            html:""
        });
        $(".ui-loader:visible").addClass("ui-state-highlight");
        return true;
    }

    function unloadingcontent()
    {
        $.mobile.loading("hide");
    }

    function isloadingcontent()
    {
        return $(".ui-loader:visible").length > 0;
    }

    /* FOR HISTORY MANAGEMENT */
    function current_href()
    {
        var url = window.location.href;
        var pos = strpos(url,"#");
        if (pos !== false) {
            url = substr(url,0,pos);
        }
        return url;
    }

    function current_hash()
    {
        var url = window.location.hash;
        var pos = strpos(url,"#");
        if (pos !== false) {
            url = substr(url,pos + 1);
        }
        return url;
    }

    function history_pushState(url)
    {
        history.pushState(null,null,url);
    }

    function history_replaceState(url)
    {
        history.replaceState(null,null,url);
    }

    function init_history()
    {
        $(window).on("hashchange",function () {
            var url = current_hash();
            addcontent("cancel");
            opencontent(url);
        });
        var url = current_href();
        var pos = strrpos(url,"/");
        if (pos !== false) {
            url = substr(url,pos + 1);
        }
        if (url === false) {
            url = "";
        }
        var pos = strpos(url,"?");
        if (pos === false) {
            url += "?page=" + current_page();
        }
        history_replaceState(current_href() + "#" + url);
    }

    /* FOR CONTENT MANAGEMENT */
    var action_addcontent = "";

    function addcontent(url)
    {
        // DETECT SOME ACTIONS
        if (url == "cancel") {
            action_addcontent = url;
            return;
        }
        if (url == "update") {
            action_addcontent = url;
            return;
        }
        if (url == "reload") {
            $(window).trigger("hashchange");
            return;
        }
        if (url == "error") {
            history.go(-1);
            return;
        }
        // IF ACTION CANCEL
        if (action_addcontent == "cancel") {
            action_addcontent = "";
            return;
        }
        // IF ACTION UPDATE
        if (action_addcontent == "update") {
            history_replaceState(current_href() + "#" + url);
            action_addcontent = "";
            return;
        }
        // NORMAL CODE
        history_pushState(current_href() + "#" + url);
    }

    function submitcontent(form,callback)
    {
        if (typeof callback == "undefined") {
            var callback = function () {};
        }
        loadingcontent(lang_sending());
        $(form).ajaxSubmit({
            beforeSerialize:function (jqForm,options) {
                // TRICK FOR ADD ENCTYPE IF HAS FILES
                var numfiles = 0;
                $("input[type=file]",jqForm).each(function () {
                    if ($(this).val() != "") {
                        numfiles++;
                    }
                });
                if (numfiles > 0) {
                    $(jqForm).attr("enctype","multipart/form-data");
                }
                // TRICK FOR FIX THE MAX_INPUT_VARS ISSUE
                var max_input_vars = ini_get_max_input_vars();
                if (max_input_vars > 0) {
                    var total_input_vars = $("input,select,textarea",jqForm).length;
                    if (total_input_vars > max_input_vars) {
                        setTimeout(function () {
                            var fix_input_vars = [];
                            $("input[type=checkbox]:not(:checked):not(:visible)",jqForm).each(function () {
                                if (total_input_vars >= max_input_vars) {
                                    $(this).remove();
                                    total_input_vars--;
                                }
                            });
                            $("input[type=checkbox]:checked:not(:visible)",jqForm).each(function () {
                                if (total_input_vars >= max_input_vars) {
                                    var temp = $(this).attr("name") + "=" + encodeURIComponent($(this).val());
                                    fix_input_vars.push(temp);
                                    $(this).remove();
                                    total_input_vars--;
                                }
                            });
                            $("input[type=hidden]",jqForm).each(function () {
                                if (total_input_vars >= max_input_vars) {
                                    var temp = $(this).attr("name") + "=" + encodeURIComponent($(this).val());
                                    fix_input_vars.push(temp);
                                    $(this).remove();
                                    total_input_vars--;
                                }
                            });
                            fix_input_vars = btoa(utf8_encode(implode("&",fix_input_vars)));
                            $(jqForm).append("<input type='hidden' name='fix_input_vars' value='" + fix_input_vars + "'/>");
                            submitcontent(form,callback);
                        },100);
                        return false;
                    }
                }
            },
            beforeSubmit:function (formData,jqForm,options) {
                var action = $(jqForm).attr("action");
                var query = $.param(formData);
                addcontent(action + "?" + query);
            },
            beforeSend:function (XMLHttpRequest) {
                make_abort_obj = XMLHttpRequest;
            },
            success:function (data,textStatus,XMLHttpRequest) {
                callback();
                loadcontent(data);
            },
            error:function (XMLHttpRequest,textStatus,errorThrown) {
                addcontent("error");
                callback();
                errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
            }
        });
    }

    function opencontent(url,callback)
    {
        // LOGOUT EXCEPTION
        if (strpos(url,"page=logout") !== false) {
            logout();
            return;
        }
        // TO FIX ERROR 414: REQUEST URI TOO LONG
        var type = (strlen(url) > 1024) ? "post" : "get";
        // CONTINUE
        var temp = explode("?",url,2);
        if (temp[0] == "") {
            temp[0] = "index.php";
        }
        if (typeof temp[1] == "undefined") {
            temp[1] = "";
        }
        // NORMAL USAGE
        if (typeof callback == "undefined") {
            var callback = function () {};
        }
        loadingcontent();
        $.ajax({
            url:temp[0],
            data:temp[1],
            type:type,
            beforeSend:function (XMLHttpRequest) {
                addcontent(url);
                make_abort_obj = XMLHttpRequest;
            },
            success:function (data,textStatus,XMLHttpRequest) {
                callback();
                loadcontent(data);
            },
            error:function (XMLHttpRequest,textStatus,errorThrown) {
                addcontent("error");
                callback();
                errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
            }
        });
    }

    function errorcontent(code,text)
    {
        unloadingcontent();
        if (text == "") {
            text = lang_unknownerror();
        }
        alerta("Error: " + code + ": " + text);
    }

    function loadcontent(xml)
    {
        loadingcontent();
        var html = str2html(fix4html(xml));
        if ($("#page",html).text() != "") {
            updatecontent(html);
        } else {
            // IF THE RETURNED HTML CONTAIN A SCRIPT NOT UNBLOCK THE UI
            var screen = $("#page");
            if ($("script",html).length != 0) {
                $(screen).append(html);
            } else {
                unloadingcontent();
                $(screen).html(html);
            }
        }
    }

    function html2str(html)
    {
        var div = document.createElement("div");
        $(div).html(html);
        return $(div).html();
    }

    function str2html(str)
    {
        var div = document.createElement("div");
        div.innerHTML = str;
        return div;
    }

    function fix4html(str)
    {
        // REPLACE HTML, HEAD, BODY AND TITLE BY DIV ELEMENTS
        str = str_replace("<html","<div type='html'",str);
        str = str_replace("</html>","</div>",str);
        str = str_replace("<head","<div type='head'",str);
        str = str_replace("</head>","</div>",str);
        str = str_replace("<body","<div type='body'",str);
        str = str_replace("</body>","</div>",str);
        str = str_replace("<title","<div type='title'",str);
        str = str_replace("</title>","</div>",str);
        // RETURN THE STRING
        return str;
    }

    /* FOR RENDER THE SCREEN */
    function getstylesheet(html,cad1,cad2)
    {
        var style = null;
        $("link[rel=stylesheet]",html).each(function () {
            var href = $(this).attr("href");
            if (strpos(href,cad1) !== false && strpos(href,cad2) !== false) {
                style = this;
            }
        });
        return style;
    }

    function update_style(html,html2)
    {
        var cad1 = default_stylepre();
        var cad2 = default_stylepost();
        var style1 = getstylesheet(html2,cad1,cad2);
        var style2 = getstylesheet(html,cad1,cad2);
        if (style1 && style2 && $(style1).attr("href") != $(style2).attr("href")) {
            $(style1).replaceWith(style2);
            // CAMBIAR COLOR DEL META THEME-COLOR
            var meta1 = $("meta[name='theme-color']",html2);
            var meta2 = $("meta[name='theme-color']",html);
            if ($(meta1).attr("content") != $(meta2).attr("content")) {
                $(meta1).replaceWith(meta2);
            }
            // RESETEAR COLORES
            get_colors();
        }
    }

    function updatecontent(html)
    {
        var page = $("#page");
        $(page).hide();
        $(page).page("destroy");
        // UPDATE THE LANG AND DIR IF NEEDED
        var temp = $("html");
        var temp2 = $("div[type=html]",html);
        if ($(temp).attr("lang") != $(temp2).attr("lang")) {
            $(temp).attr("lang",$(temp2).attr("lang"));
        }
        if ($(temp).attr("dir") != $(temp2).attr("dir")) {
            $(temp).attr("dir",$(temp2).attr("dir"));
            $(".ltr,.rtl").removeClass("ltr rtl").addClass($(temp2).attr("dir"));
        }
        // UPDATE THE TITLE IF NEEDED
        var title2 = $("div[type=title]",html);
        if (document.title != $(title2).text()) {
            document.title = $(title2).text();
        }
        // UPDATE THE NORTH PANEL IF NEEDED
        var header = $(".ui-layout-north");
        var header2 = $(".ui-layout-north",html);
        if ($(header).text() != $(header2).text()) {
            make_extras(header2);
            $(header).replaceWith(header2);
        }
        // CHECK FOR LOGIN AND LOGOUT
        var menu = $(".ui-layout-west");
        var menu2 = $(".ui-layout-west",html);
        var saltos_login = (!saltos_islogin(menu) && saltos_islogin(menu2)) ? 1 : 0;
        var saltos_logout = (saltos_islogin(menu) && !saltos_islogin(menu2)) ? 1 : 0;
        // IF LOGIN
        if (saltos_login) {
            sync_cookies("start");
        }
        // UPDATE THE MENU
        if ($(".menu",menu).text() != $(".menu",menu2).text()) {
            make_extras(menu2);
            $(menu).replaceWith(menu2);
        }
        // IF LOGOUT
        if (saltos_logout) {
            sync_cookies("stop");
        }
        // UPDATE THE CENTER PANE
        var screen = $(".ui-layout-center");
        var screen2 = $(".ui-layout-center",html);
        make_extras(screen2);
        $(screen).replaceWith(screen2);
        make_ckeditors(screen2);
        setTimeout(function () {
            var html2 = $("html");
            update_style(html,html2);
        },100);
        $(page).page();
        $(".ui-footer a").removeAttr("class").removeAttr("role");
        $(page).show();
        unloadingcontent();
        $(document).scrollTop(1);
    }

    function make_extras(obj)
    {
        if (typeof obj == "undefined") {
            var obj = $("body");
        }
        // PROGRAM INTERGER TYPE CAST
        $("input[isinteger=true]",obj).each(function () {
            $(this).on("keyup",function () {
                intval2(this); });
        });
        // PROGRAM FLOAT TYPE CAST
        $("input[isfloat=true]",obj).each(function () {
            $(this).on("keyup",function () {
                floatval2(this); });
        });
        // ADD THE SELECT ALL FEATURE TO LIST
        var master = "input.master[type=checkbox]";
        var slave = "input.slave[type=checkbox]";
        $(master,obj).prev().html(lang_selectallcheckbox());
        $(slave,obj).prev().html(lang_selectonecheckbox());
        $(master,obj).on("click",function () {
            var value = $(this).prop("checked");
            var ul = $(this).parent().parent().parent().parent();
            $(slave,ul).prop("checked",value).checkboxradio("refresh");
        });
        // ADD THE ACTIONS DEFAULT OPTION LABEL
        $(".actions",obj).find("option:eq(0)",obj).html(lang_actions());
        // PROGRAM CHECK ENTER
        $("input,select",obj).on("keydown",function (event) {
            if (is_enterkey(event)) {
                if (this.form) {
                    for (var i = 0,len = this.form.elements.length; i < len; i++) {
                        if (this == this.form.elements[i]) {
                            break;
                        }
                    }
                    for (var j = 0,len = this.form.elements.length; j < len; j++) {
                        i = (i + 1) % this.form.elements.length;
                        if (this.form.elements[i].type != "hidden") {
                            break;
                        }
                    }
                    $(this.form.elements[i]).trigger("focus");
                    if (this.form.elements[i].type) {
                        if (substr(this.form.elements[i].type,0,6) != "select") {
                            this.form.elements[i].select();
                        }
                    }
                }
            }
        });
        // TO CLEAR AMBIGUOUS THINGS
        $(".nowrap.siwrap",obj).removeClass("nowrap siwrap");
        // TRICK FOR STYLING THE INFO NOTIFY
        $(".info",obj).addClass("ui-bar ui-state-highlight ui-corner-all");
        // TRICK FOR STYLING THE TITLES
        $(".title",obj).addClass("ui-bar ui-state-highlight ui-corner-all");
        // TRICK FOR DISABLE BUTTONS
        $(".disabled",obj).removeClass("disabled").addClass("ui-state-disabled");
        // PROGRAM MENU SELECTS
        $("select[ismenu=true]",obj).on("change",function () {
            if (!$(this).find("option:eq(" + $(this).prop("selectedIndex") + ")").hasClass("ui-state-disabled")) {
                eval($(this).val());
            }
            if ($("option:first",this).val() == "") {
                $(this).prop("selectedIndex",0);
            }
        });
        // STYLING IFRAME BORDER
        $(".preiframe",obj).addClass("ui-input-text ui-body-inherit ui-corner-all ui-mini ui-shadow-inset");
        // MERGE TODOFIXHEAD AND TODOFIXBODY INTO TODOFIXFULL
        $(".todofixfull",obj).each(function () {
            var full = $(this);
            var head = $(this).next();
            var body = $(this).next().next();
            for (;;) {
                var head0 = $(head).find("div:first")
                var body0 = $(body).find("div:first")
                if ($(head0).length) {
                    $(full).append(head0);
                }
                if ($(body0).length) {
                    $(full).append(body0);
                }
                if (!$(head0).length && !$(body0).length) {
                    break;
                }
            }
        });
    }

    function get_class_key_val(obj,param)
    {
        var clases = explode(" ",$(obj).attr("class"));
        var total = clases.length;
        var length = strlen(param);
        for (var i = 0; i < total; i++) {
            if (substr(clases[i],0,length) == param) {
                return substr(clases[i],length);
            }
        }
        return "";
    }

    function get_class_id(obj)
    {
        return get_class_key_val(obj,"id_");
    }

    function get_class_hash(obj)
    {
        return get_class_key_val(obj,"hash_");
    }

    function make_hovers(obj)
    {
        // UNUSED
    }

    function make_ckeditors(obj)
    {
        if (typeof obj == "undefined") {
            var obj = $("body");
        }
        // AUTO-GROWING TEXTAREA
        $("textarea[ckeditor!=true][codemirror!=true]",obj).autogrow();
        // AUTO-GROWING IFRAMES
        $("iframe",obj).each(function () {
            var iframe = "#" + $(this).attr("id");
            var interval = setInterval(function () {
                var iframe2 = $(iframe,obj);
                if (!$(iframe2).length) {
                    clearInterval(interval);
                } else if ($(iframe2).is(":visible")) {
                    if (typeof $(iframe2).prop("isloaded") == "undefined") {
                        $(iframe2).each(function () {
                            $(this).prop("isloaded","false");
                            $(this).on("load",function () {
                                $(this).prop("isloaded","true");
                            });
                            var iframe3 = this.contentWindow.location;
                            var url = $(this).attr("url");
                            if (url) {
                                iframe3.replace(url);
                            }
                            if (!url) {
                                clearInterval(interval);
                            }
                        });
                    } else if ($(iframe2).prop("isloaded") == "true") {
                        clearInterval(interval);
                        if (security_iframe(iframe2)) {
                            var minheight = $(iframe2).height();
                            var newheight = $(iframe2).contents().height();
                            if (newheight > minheight) {
                                $(iframe2).height(newheight);
                            }
                            $(iframe2).each(function () {
                                var iframe3 = this.contentWindow.document;
                                $(iframe3).on("contextmenu",function (e) {
                                    return false; });
                                $(iframe3).on("keydown",function (e) {
                                    $(document).trigger(e); });
                            });
                        }
                    }
                }
            },100);
        });
        // CREATE THE CKEDITORS
        $("textarea[ckeditor=true]",obj).autogrow();
        // CREATE THE CODE MIRROR
        $("textarea[codemirror=true]",obj).autogrow();
    }

    var make_focus_obj = null;

    function make_focus(obj)
    {
        // UNUSED
    }

    function make_tables(obj)
    {
        // UNUSED
    }

    var cache_colors = {};

    function get_colors(clase,param)
    {
        if (typeof clase == "undefined" && typeof param == "undefined") {
            for (var hash in cache_colors) {
                delete cache_colors[hash];
            }
            return;
        }
        hash = md5(JSON.stringify([clase,param]));
        if (typeof cache_colors[hash] == "undefined") {
            // GET THE COLORS USING THIS TRICK
            if ($("#ui-color-trick").length == 0) {
                $("body").append("<div id='ui-color-trick'></div>");
            }
            $("#ui-color-trick").addClass(clase);
            cache_colors[hash] = $("#ui-color-trick").css(param);
            $("#ui-color-trick").removeClass(clase);
        }
        return cache_colors[hash];
    }

    function rgb2hex(color)
    {
        if (strncasecmp(color,"rgba",4) == 0) {
            var temp = color.split(/([\(,\)])/);
            if (temp.length == 11) {
                color = sprintf("%02x%02x%02x",temp[2],temp[4],temp[6]);
            }
        } else if (strncasecmp(color,"rgb",3) == 0) {
            var temp = color.split(/([\(,\)])/);
            if (temp.length == 9) {
                color = sprintf("%02x%02x%02x",temp[2],temp[4],temp[6]);
            }
        }
        return color;
    }

    var make_abort_obj = null;

    function make_abort()
    {
        // UNUSED
    }

    function saltos_islogin(obj)
    {
        if (typeof obj == "undefined") {
            var obj = $(".ui-layout-west");
        }
        var islogin = ($(obj).text() != "") ? 1 : 0;
        return islogin;
    }

    // TO PREVENT JQUERY THE ADD _=[TIMESTAMP] FEATURE
    jQuery.ajaxSetup({ cache:true });

    // TO CONTROL AJAX IN JQUERY MOBILE
    $.mobile.autoInitializePage = false;
    $.mobile.ajaxEnabled = false;
    $.mobile.linkBindingEnabled = false;
    $.mobile.hashListeningEnabled = false;
    $.mobile.pushStateEnabled = false;
    //~ $.mobile.page.prototype.options.keepNative="input[type=checkbox]";

    // TO DO COMPATIBLE WITH DESKTOP PLUGINS
    (function ($) {
        // JQUERY UI TABS
        $.fn.tabs = function () {
            // NOTHING TO DO
        };
        // JQUERY UI DATEPICKER
        $.datepicker = function () {
            // NOTHING TO DO
        };
        $.extend($.datepicker,{
            regional:function () {
                // NOTHING TO DO
            },
            setDefaults:function () {
                // NOTHING TO DO
            }
        });
        // JQUERY UI AUTOCOMPLETE
        $.fn.autocomplete = function () {
            // NOTHING TO DO
        };
    })(jQuery);

    // WHEN DOCUMENT IS READY
    $(function () {
        var menu = $(".ui-layout-west");
        if (saltos_islogin(menu)) {
            sync_cookies("start");
        }
        init_history();
        var header = $(".ui-layout-north");
        make_extras(header);
        var menu = $(".ui-layout-west");
        make_extras(menu);
        var screen = $(".ui-layout-center");
        make_extras(screen);
        make_ckeditors(screen);
        $.mobile.initializePage();
    });
}
