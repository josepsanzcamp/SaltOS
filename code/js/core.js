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

"use strict";

/* MAIN OBJECT */
var saltos = {};

/* ERROR MANAGEMENT */
saltos.init_error = function () {
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
};

/* LOG MANAGEMENT */
saltos.addlog = function (msg) {
    $.ajax({
        url:"index.php?action=addlog",
        data:msg,
        type:"post"
    });
};

/* NUMERIC FUNCTIONS */
saltos.floatval2 = function (obj) {
    saltos._format_number(obj,0);
};

saltos.intval2 = function (obj) {
    saltos._format_number(obj,1);
};

saltos._format_number = function (obj,punto) {
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
};

/* REQUIRED FUNCTIONS */
saltos.copy_value = function (orig,dest) {
    $("#" + dest).val($("#" + orig).val());
};

/* STRING FUNCTIONS */
saltos.intelligence_cut = function (txt,max) {
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
};

/* DATETIME FUNCTIONS */
saltos.dateval = function (value) {
    value = str_replace(["-",":",",",".","/"]," ",value);
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
        temp[0] = min(saltos.__days_of_a_month(temp[2],temp[1]),max(0,temp[0]));
        value = sprintf("%04d-%02d-%02d",temp[2],temp[1],temp[0]);
    } else {
        temp[0] = min(9999,max(0,temp[0]));
        temp[1] = min(12,max(0,temp[1]));
        temp[2] = min(saltos.__days_of_a_month(temp[0],temp[1]),max(0,temp[2]));
        value = sprintf("%04d-%02d-%02d",temp[0],temp[1],temp[2]);
    }
    return value;
};

saltos.timeval = function (value) {
    value = str_replace(["-",":",",",".","/"]," ",value);
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
};

saltos.__days_of_a_month = function (year,month) {
    return date("t",strtotime(sprintf("%04d-%02d-%02d",year,month,1)));
};

saltos.check_datetime = function (orig,comp,dest) {
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
};

saltos.check_date = function (orig,comp,dest) {
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
};

saltos.check_time = function (orig,comp,dest) {
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
};

/* KEYBOARD FUNCTIONS */
saltos.get_keycode = function (event) {
    var keycode = 0;
    if (event.keyCode) {
        keycode = event.keyCode;
    } else if (event.which) {
        keycode = event.which;
    } else {
        keycode = event.charCode;
    }
    return keycode;
};

saltos.is_enterkey = function (event) {
    return saltos.get_keycode(event) == 13;
};

saltos.is_escapekey = function (event) {
    return saltos.get_keycode(event) == 27;
};

saltos.is_disabled = function (obj) {
    return $(obj).hasClass("ui-state-disabled");
};

/* COOKIES MANAGEMENT */
saltos.cookies = {};
saltos.cookies.data = {};
saltos.cookies.interval = null;
saltos.cookies.counter = 0;

saltos.cookies.sync = function (cmd) {
    if (!isset(cmd)) {
        var cmd = "start";
    }
    if (cmd == "stop") {
        if (saltos.cookies.interval != null) {
            clearInterval(saltos.cookies.interval);
            saltos.cookies.interval = null;
        }
        saltos.cookies.__sync_helper();
        for (var hash in saltos.cookies.data) {
            delete saltos.cookies.data[hash];
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
                for (var key in response.rows) {
                    var val = response.rows[key];
                    saltos.cookies.data[md5(val.clave)] = {
                        "key":val.clave,
                        "val":val.valor,
                        "orig":val.valor,
                        "sync":0
                    };
                }
            },
            error:function (XMLHttpRequest,textStatus,errorThrown) {
                errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
            }
        });
        saltos.cookies.counter = 0;
        saltos.cookies.interval = setInterval(function () {
            saltos.cookies.counter = saltos.cookies.counter + 1;
            if (saltos.cookies.counter >= 3) {
                saltos.cookies.__sync_helper();
                saltos.cookies.counter = 0;
            }
        },1000);
    }
};

saltos.cookies.__sync_helper = function () {
    for (var hash in saltos.cookies.data) {
        if (saltos.cookies.data[hash].sync) {
            if (saltos.cookies.data[hash].val != saltos.cookies.data[hash].orig) {
                var data = "action=cookies&name=" + encodeURIComponent(saltos.cookies.data[hash].key) + "&value=" + encodeURIComponent(saltos.cookies.data[hash].val);
                var value = $.ajax({
                    url:"index.php",
                    data:data,
                    type:"post",
                    async:false,
                }).responseText;
                if (value != "") {
                    saltos.cookies.data[hash].orig = saltos.cookies.data[hash].val;
                    saltos.cookies.data[hash].sync = 0;
                }
            } else {
                saltos.cookies.data[hash].sync = 0;
            }
        }
    }
};

saltos.cookies.getCookie = function (name) {
    var hash = md5(name);
    if (typeof saltos.cookies.data[hash] == "undefined") {
        var value = $.cookie(name);
    } else {
        var value = saltos.cookies.data[hash].val;
    }
    return value;
};

saltos.cookies.getIntCookie = function (name) {
    return intval(saltos.cookies.getCookie(name));
};

saltos.cookies.getBoolCookie = function (name) {
    return saltos.cookies.getIntCookie(name) ? true : false;
};

saltos.cookies.setCookie = function (name,value) {
    var hash = md5(name);
    if (typeof saltos.cookies.data[hash] == "undefined") {
        if (saltos.cookies.interval != null) {
            saltos.cookies.data[hash] = {
                "key":name,
                "val":value,
                "orig":value + "!",
                "sync":1
            };
            saltos.cookies.counter = 0;
        } else {
            $.cookie(name,value,{expires:365,path:"/"});
        }
    } else {
        if (saltos.cookies.data[hash].val != value) {
            saltos.cookies.data[hash].val = value;
            saltos.cookies.data[hash].sync = 1;
        }
        saltos.cookies.counter = 0;
    }
};

saltos.cookies.setIntCookie = function (name,value) {
    saltos.cookies.setCookie(name,intval(value));
};

saltos.cookies.setBoolCookie = function (name,value) {
    saltos.cookies.setIntCookie(name,value ? 1 : 0);
};

/* COLOR MANAGEMENT */
saltos.colors = {};

saltos.get_colors = function (clase,param) {
    if (typeof clase == "undefined" && typeof param == "undefined") {
        for (var hash in saltos.colors) {
            delete saltos.colors[hash];
        }
        return;
    }
    hash = md5(JSON.stringify([clase,param]));
    if (typeof saltos.colors[hash] == "undefined") {
        // GET THE COLORS USING THIS TRICK
        if ($("#ui-color-trick").length == 0) {
            $("body").append("<div id='ui-color-trick'></div>");
        }
        $("#ui-color-trick").addClass(clase);
        saltos.colors[hash] = $("#ui-color-trick").css(param);
        $("#ui-color-trick").removeClass(clase);
    }
    return saltos.colors[hash];
};

saltos.rgb2hex = function (color) {
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
};

saltos.modify_color = function (color,factor) {
    var temp = color.split(/([\(,\)])/);
    if (in_array(temp[0],["rgb","rgba"]) && in_array(temp.length,[9,11])) {
        temp[2] = max(0,min(255,intval(temp[2] * factor)));
        temp[4] = max(0,min(255,intval(temp[4] * factor)));
        temp[6] = max(0,min(255,intval(temp[6] * factor)));
        color = implode("",temp);
    }
    return color;
};

/* PHP FUNCTIONS */
saltos.limpiar_key = function (arg) {
    if (is_array(arg)) {
        for (var key in arg) {
            arg[key] = saltos.limpiar_key(arg[key])
        }
        return arg;
    }
    var pos = strpos(arg,"#");
    if (pos !== false) {
        arg = substr(arg,0,pos);
    }
    return arg;
};

saltos.querystring2array = function (querystring) {
    if (querystring == "") {
        return {};
    }
    var items = explode("&",querystring);
    var result = {};
    for (var key in items) {
        var item = items[key];
        var par = explode("=",item,2);
        if (!isset(par[1])) {
            par[1] = "";
        }
        par[1] = decodeURIComponent(par[1]);
        result[par[0]] = par[1];
    }
    return result;
};

saltos.array2querystring = function (array) {
    var querystring = [];
    for (var key in array) {
        querystring.push(key + "=" + encodeURIComponent(array[key]));
    }
    querystring = implode("&",querystring);
    return querystring;
};

/* CLASS FUNCTIONS */
saltos.get_class_key_val = function (clase,param) {
    var clases = explode(" ",clase);
    var total = clases.length;
    var length = strlen(param);
    for (var i = 0; i < total; i++) {
        if (substr(clases[i],0,length) == param) {
            return substr(clases[i],length);
        }
    }
    return "";
};

saltos.get_class_id = function (clase) {
    return saltos.get_class_key_val(clase,"id_");
};

saltos.get_class_hash = function (clase) {
    return saltos.get_class_key_val(clase,"hash_");
};

/* FOR SECURITY ISSUES */
saltos.security_iframe = function (obj) {
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
};

/* HELPERS DEL SALTOS ORIGINAL */
saltos.make_shortcuts = function () {
    var codes = {"backspace":8, "tab":9, "enter":13, "pauseBreak":19, "capsLock":20, "escape":27, "space":32, "pageUp":33, "pageDown":34, "end":35, "home":36, "leftArrow":37, "upArrow":38, "rightArrow":39, "downArrow":40, "insert":45, "delete":46, "0":48, "1":49, "2":50, "3":51, "4":52, "5":53, "6":54, "7":55, "8":56, "9":57, "a":65, "b":66, "c":67, "d":68, "e":69, "f":70, "g":71, "h":72, "i":73, "j":74, "k":75, "l":76, "m":77, "n":78, "o":79, "p":80, "q":81, "r":82, "s":83, "t":84, "u":85, "v":86, "w":87, "x":88, "y":89, "z":90, "leftWindowKey":91, "rightWindowKey":92, "selectKey":93, "numpad0":96, "numpad1":97, "numpad2":98, "numpad3":99, "numpad4":100, "numpad5":101, "numpad6":102, "numpad7":103, "numpad8":104, "numpad9":105, "multiply":106, "add":107, "subtract":109, "decimalPoint":110, "divide":111, "f1":112, "f2":113, "f3":114, "f4":115, "f5":116, "f6":117, "f7":118, "f8":119, "f9":120, "f10":121, "f11":122, "f12":123, "numLock":144, "scrollLock":145, "semiColon":186, "equalSign":187, "comma":188, "dash":189, "period":190, "forwardSlash":191, "graveAccent":192, "openBracket":219, "backSlash":220, "closeBraket":221, "singleQuote":222};
    $(document).on("keydown",function (event) {
        if (!isloadingcontent()) {
            var exists = false;
            $("[class*=shortcut_]").each(function () {
                var param = get_class_key_val(this,"shortcut_");
                var temp = explode("_",param);
                var useAlt = false;
                var useCtrl = false;
                var useShift = false;
                var key = null;
                for (var i = 0,len = temp.length; i < len; i++) {
                    if (temp[i] == "alt") {
                        useAlt = true;
                    } else if (temp[i] == "ctrl") {
                        useCtrl = true;
                    } else if (temp[i] == "shift") {
                        useShift = true;
                    } else {
                        key = codes[temp[i]];
                    }
                }
                var count = 0;
                if (useAlt && event.altKey) {
                    count++;
                }
                if (!useAlt && !event.altKey) {
                    count++;
                }
                if (useCtrl && event.ctrlKey) {
                    count++;
                }
                if (!useCtrl && !event.ctrlKey) {
                    count++;
                }
                if (useShift && event.shiftKey) {
                    count++;
                }
                if (!useShift && !event.shiftKey) {
                    count++;
                }
                if (key == get_keycode(event)) {
                    count++;
                }
                if (count == 4) {
                    if ($(this).is("a,tr,td,input[type=checkbox]")) {
                        $(this).trigger("click");
                    }
                    if ($(this).is("input,select,textarea")) {
                        $(this).trigger("focus");
                    }
                    exists = true;
                }
            });
            if (exists) {
                return false;
            }
        }
    });
};

saltos.make_abort_obj = null;

saltos.make_abort = function () {
    $(document).on("keydown",function (event) {
        if (is_escapekey(event) && saltos.make_abort_obj) {
            saltos.make_abort_obj.abort();
            saltos.make_abort_obj = null;
        }
    });
};

saltos.make_focus_obj = null;

saltos.make_focus = function () {
    // FOCUS THE OBJECT WITH FOCUSED ATTRIBUTE
    setTimeout(function () {
        if (saltos.make_focus_obj) {
            $(saltos.make_focus_obj).trigger("focus");
        }
        saltos.make_focus_obj = null;
    },100);
};

saltos.unmake_focus = function () {
    $("html").focus();
};

saltos.islogin = function () {
    return saltos.json_sync_request("index.php?action=islogin","islogin");
};

saltos.html2str = function (html) {
    var div = $("<div></div>");
    $(div).html(html);
    return $(div).html();
};

saltos.str2html = function (str) {
    var div = $("<div></div>");
    $(div).html(str);
    return $(div).get(0);
};

saltos.fix4html = function (str) {
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
};

/* HELPERS DEL NUEVO SALTOS */
saltos.check_params = function (obj,params,valor) {
    if (!isset(valor)) {
        valor = "";
    }
    for (var key in params) {
        if (!isset(obj[params[key]])) {
            obj[params[key]] = valor;
        }
    }
};

saltos.uniqid = function () {
    return "id" + Math.floor(Math.random() * 1000000);
};

saltos.when_visible = function (obj,fn,args) {
    if (!$(obj).is("[id]")) {
        $(obj).attr("id","fix" + saltos.uniqid());
    }
    var id = "#" + $(obj).attr("id");
    var interval = setInterval(function () {
        var obj2 = $(id);
        if (!$(obj2).length) {
            clearInterval(interval);
        } else if ($(obj2).is(":visible")) {
            clearInterval(interval);
            fn(args);
        }
    },100);
};

// TO PREVENT JQUERY THE ADD _=[TIMESTAMP] FEATURE
$.ajaxSetup({ cache:true });
