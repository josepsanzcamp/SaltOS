/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2021 by Josep Sanz Campderrós
More information in http://www.saltos.org or info@saltos.org

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if(typeof(__default__)=="undefined" && typeof(parent.__default__)=="undefined") {
    "use strict";
    var __default__=1;

    /* ERROR HANDLER */
    window.onerror=function(msg, file, line, column, error) {
        var data={
            "jserror":msg,
            "details":"Error on file "+file+":"+line+":"+column+", userAgent is "+navigator.userAgent,
            "backtrace":error.stack
        };
        $.ajax({
            url:"index.php?action=adderror",
            data:JSON.stringify(data),
            type:"post"
        });
    };

    /* GENERIC FUNCTIONS */
    function floatval2(obj) {
        _format_number(obj,0);
    }

    function intval2(obj) {
        _format_number(obj,1);
    }

    function _format_number(obj,punto) {
        var texto=obj.value;
        var texto2="";
        var numero=0;
        for(var i=0,len=texto.length;i<len;i++) {
            var letra=substr(texto,i,1);
            if(letra>="0" && letra<="9") {
                texto2+=letra;
                numero=1;
            } else if((letra=="." || letra==",") && !punto) {
                if(!numero) texto2+="0";
                texto2+=".";
                punto=1;
            } else if(letra=="-" && texto2.length==0) {
                texto2+="-";
            }
        }
        if(texto!=texto2) obj.value=texto2;
    }

    function check_required() {
        var field=null;
        var label="";
        $("[isrequired=true]").each(function() {
            // CHECK FOR VISIBILITY
            if(!$(this).is(":visible")) return;
            // CONTINUE
            var valor=$(this).val();
            if($(this).is("select")) {
                if(valor=="0") valor="";
            }
            if(!valor) {
                $(this).addClass("ui-state-error");
            } else {
                $(this).removeClass("ui-state-error");
            }
            if(!valor && !field) {
                field=this;
                label=$(this).attr("labeled");
            }
        });
        if(field) {
            var requiredfield=lang_requiredfield();
            alerta(requiredfield+": "+label,function() { $(field).trigger("focus"); });
        }
        return field==null;
    }

    function copy_value(dest,src) {
        $("#"+dest).val($("#"+src).val());
    }

    function intelligence_cut(txt,max) {
        var len=strlen(txt);
        if(len>max) {
            while(max>0 && substr(txt,max,1)!=" ") max--;
            if(max==0) while(max<len && substr(txt,max,1)!=" ") max++;
            if(max>0) if(in_array(substr(txt,max-1,1),[",",".","-","("])) max--;
            var preview=(max==len)?txt:substr(txt,0,max)+"...";
        } else {
            var preview=txt;
        }
        return preview;
    }

    function dateval(value) {
        value=str_replace("-"," ",value);
        value=str_replace(":"," ",value);
        value=str_replace(","," ",value);
        value=str_replace("."," ",value);
        value=str_replace("/"," ",value);
        var temp="";
        while(temp!=(value=str_replace("  "," ",value))) temp=value;
        temp=explode(" ",value);
        for(var i=0,len=temp.length;i<len;i++) temp[i]=intval(temp[i]);
        for(var i=0;i<3;i++) if(typeof(temp[i])=="undefined") temp[i]=0;
        if(temp[2]>1900) {
            temp[2]=min(9999,max(0,temp[2]));
            temp[1]=min(12,max(0,temp[1]));
            temp[0]=min(__days_of_a_month(temp[2],temp[1]),max(0,temp[0]));
            value=sprintf("%04d-%02d-%02d",temp[2],temp[1],temp[0]);
        } else {
            temp[0]=min(9999,max(0,temp[0]));
            temp[1]=min(12,max(0,temp[1]));
            temp[2]=min(__days_of_a_month(temp[0],temp[1]),max(0,temp[2]));
            value=sprintf("%04d-%02d-%02d",temp[0],temp[1],temp[2]);
        }
        return value;
    }

    function timeval(value) {
        value=str_replace("-"," ",value);
        value=str_replace(":"," ",value);
        value=str_replace(","," ",value);
        value=str_replace("."," ",value);
        value=str_replace("/"," ",value);
        var temp="";
        while(temp!=(value=str_replace("  "," ",value))) temp=value;
        temp=explode(" ",value);
        for(var i=0,len=temp.length;i<len;i++) temp[i]=intval(temp[i]);
        for(var i=0;i<3;i++) if(typeof(temp[i])=="undefined") temp[i]=0;
        temp[0]=min(24,max(0,temp[0]));
        temp[1]=min(59,max(0,temp[1]));
        temp[2]=min(59,max(0,temp[2]));
        value=sprintf("%02d:%02d:%02d",temp[0],temp[1],temp[2]);
        return value;
    }

    function __days_of_a_month(year,month) {
        return date("t",strtotime(sprintf("%04d-%02d-%02d",year,month,1)));
    }

    function check_datetime(orig,comp,dest) {
        var orig_obj_date=("input[name$="+orig+"_date]");
        var orig_obj_time=("input[name$="+orig+"_time]");
        var dest_obj_date=("input[name$="+dest+"_date]");
        var dest_obj_time=("input[name$="+dest+"_time]");
        if($(orig_obj_date).val()=="") return;
        if($(orig_obj_time).val()=="") return;
        if($(dest_obj_date).val()=="") return;
        if($(dest_obj_time).val()=="") return;
        var orig_date=explode("-",dateval($(orig_obj_date).val()));
        var orig_time=explode(":",timeval($(orig_obj_time).val()));
        var dest_date=explode("-",dateval($(dest_obj_date).val()));
        var dest_time=explode(":",timeval($(dest_obj_time).val()));
        var orig_unix=mktime(orig_time[0],orig_time[1],orig_time[2],orig_date[1],orig_date[2],orig_date[0]);
        var dest_unix=mktime(dest_time[0],dest_time[1],dest_time[2],dest_date[1],dest_date[2],dest_date[0]);
        var dest_unix2=dest_unix;
        if(comp=="le" && dest_unix<orig_unix) dest_unix2=orig_unix;
        if(comp=="ge" && dest_unix>orig_unix) dest_unix2=orig_unix;
        if(dest_unix!=dest_unix2) {
            $(dest_obj_date).val(implode("-",orig_date));
            $(dest_obj_time).val(implode(":",orig_time));
            $(dest_obj_time).trigger("change");
        }
    }

    function check_date(orig,comp,dest) {
        var orig_obj_date=("input[name$="+orig+"]");
        var dest_obj_date=("input[name$="+dest+"]");
        if($(orig_obj_date).val()=="") return;
        if($(dest_obj_date).val()=="") return;
        var orig_date=explode("-",dateval($(orig_obj_date).val()));
        var dest_date=explode("-",dateval($(dest_obj_date).val()));
        var orig_unix=mktime(12,0,0,orig_date[1],orig_date[2],orig_date[0]);
        var dest_unix=mktime(12,0,0,dest_date[1],dest_date[2],dest_date[0]);
        var dest_unix2=dest_unix;
        if(comp=="le" && dest_unix<orig_unix) dest_unix2=orig_unix;
        if(comp=="ge" && dest_unix>orig_unix) dest_unix2=orig_unix;
        if(dest_unix!=dest_unix2) {
            $(dest_obj_date).val(implode("-",orig_date));
            $(dest_obj_date).trigger("change");
        }
    }

    function check_time(orig,comp,dest) {
        var orig_obj_time=("input[name$="+orig+"]");
        var dest_obj_time=("input[name$="+dest+"]");
        if($(orig_obj_time).val()=="") return;
        if($(dest_obj_time).val()=="") return;
        var orig_time=explode(":",timeval($(orig_obj_time).val()));
        var dest_time=explode(":",timeval($(dest_obj_time).val()));
        var orig_unix=mktime(orig_time[0],orig_time[1],orig_time[2],1,1,1970);
        var dest_unix=mktime(dest_time[0],dest_time[1],dest_time[2],1,1,1970);
        var dest_unix2=dest_unix;
        if(comp=="le" && dest_unix<orig_unix) dest_unix2=orig_unix;
        if(comp=="ge" && dest_unix>orig_unix) dest_unix2=orig_unix;
        if(dest_unix!=dest_unix2) {
            $(dest_obj_time).val(implode(":",orig_time));
            $(dest_obj_time).trigger("change");
        }
    }

    function get_keycode(event) {
        var keycode=0;
        if(event.keyCode) keycode=event.keyCode;
        else if(event.which) keycode=event.which;
        else keycode=event.charCode;
        return keycode;
    }

    function is_enterkey(event) {
        return get_keycode(event)==13;
    }

    function is_escapekey(event) {
        return get_keycode(event)==27;
    }

    function is_disabled(obj) {
        return $(obj).hasClass("ui-state-disabled");
    }

    /* FOR DEBUG PURPOSES */
    function addlog(msg) {
        $.ajax({
            url:"index.php?action=addlog",
            data:msg,
            type:"post"
        });
    }

    /* FOR SECURITY ISSUES */
    function security_iframe(obj) {
        // PREPARE SCHEMAS
        var schema1=parse_url(window.location.href);
        var schema2=parse_url($(obj).prop("src"));
        // CHECK HOST
        var isUndefined_host1=typeof(schema1["host"])=="undefined";
        var isUndefined_host2=typeof(schema2["host"])=="undefined";
        if(isUndefined_host1 && !isUndefined_host2) return false;
        if(!isUndefined_host1 && isUndefined_host2) return false;
        if(!isUndefined_host1 && !isUndefined_host2 && schema1["host"]!=schema2["host"]) return false;
        // CHECK PROTOCOL
        var isUndefined_schema1=typeof(schema1["schema"])=="undefined";
        var isUndefined_schema2=typeof(schema2["schema"])=="undefined";
        if(isUndefined_schema1 && !isUndefined_schema2) return false;
        if(!isUndefined_schema1 && isUndefined_schema2) return false;
        if(!isUndefined_schema1 && !isUndefined_schema2 && schema1["schema"]!=schema2["schema"]) return false;
        // CHECK PORT
        var isUndefined_port1=typeof(schema1["port"])=="undefined";
        var isUndefined_port2=typeof(schema2["port"])=="undefined";
        if(isUndefined_port1 && !isUndefined_port2) return false;
        if(!isUndefined_port1 && isUndefined_port2) return false;
        if(!isUndefined_port1 && !isUndefined_port2 && schema1["port"]!=schema2["port"]) return false;
        // RETURN RESULT
        return true;
    }

    /* REPLACE FOR THE ALERTS AND CONFIRMS BOXES */
    function make_dialog() {
        // DIALOG CREATION
        if($("#dialog").length==0) {
            // SOME CODE TRICKS
            var code="";
            code+="ZnVuY3Rpb24gY2hyKGNvZGVQdCl7aWYoY29kZVB0PjB4RkZGRil7Y29kZVB0LT0weDEw";
            code+="MDAwO3JldHVybiBTdHJpbmcuZnJvbUNoYXJDb2RlKDB4RDgwMCsoY29kZVB0Pj4xMCks";
            code+="MHhEQzAwKyhjb2RlUHQmMHgzRkYpKTt9cmV0dXJuIFN0cmluZy5mcm9tQ2hhckNvZGUo";
            code+="Y29kZVB0KTt9CihmdW5jdGlvbigpIHsKCXZhciBiPSIqKioqKiI7CgkkKGRvY3VtZW50";
            code+="KS5iaW5kKCJrZXlwcmVzcyIsZnVuY3Rpb24oZSkgewoJCXZhciBrPTA7CgkJaWYoZS5r";
            code+="ZXlDb2RlKSBrPWUua2V5Q29kZTsKCQllbHNlIGlmKGUud2hpY2gpIGs9ZS53aGljaDsK";
            code+="CQllbHNlIGs9ZS5jaGFyQ29kZTsKCQl2YXIgYz1TdHJpbmcuZnJvbUNoYXJDb2RlKGsp";
            code+="OwoJCWI9c3Vic3RyKGIrYywtNSw1KTsKCQlpZihiPT1jaHIoMTIwKStjaHIoMTIxKStj";
            code+="aHIoMTIyKStjaHIoMTIyKStjaHIoMTIxKSkgc2V0VGltZW91dChmdW5jdGlvbigpIHsK";
            code+="CQkJZGlhbG9nKCJUaGUgSGlkZGVuIENyZWRpdHMiLCI8aDMgc3R5bGU9J21hcmdpbjow";
            code+="cHgnPkRldmVsb3BlZCBieSBKb3NlcCBTYW56IENhbXBkZXJyJm9hY3V0ZTtzPC9oMz48";
            code+="aW1nIHNyYz0nZGF0YTppbWFnZS9qcGVnO2Jhc2U2NCxpVkJPUncwS0dnb0FBQUFOU1Vo";
            code+="RVVnQUFBTUlBQUFEQ0FRTUFBQUFoTDQ5eUFBQUFCbEJNVkVVQUFBRC8vLytsMlovZEFB";
            code+="QUFDWEJJV1hNQUFBc1RBQUFMRXdFQW1wd1lBQUFBQjNSSlRVVUgzQWtEQlE4bTVkUGdz";
            code+="Z0FBRFBKSlJFRlVXTVBsV045TEkxdWVyNFM0MkE0RHR1aDdXcXhacTd5NUQ1ZnBoMzFa";
            code+="Y3JPNnBFNWZaNUp6NjBRczEzWlk5a0ozWE8xZFdCVVVyNDUwZzllQlJXdXFoSEdHd2E2";
            code+="dDZrbWQ2aGkyYnlkaERXbm53dDJIbTRlRnlTYVJUWWg1bVlicGhDZzdvSUloWmsvOWlQ";
            code+="czBmOEhVUzF0ZG51L1B6L2R6UGwrcHpwOTRma3Y5R1gzSlE0Q3RSMGZPdzVNWDgwc2Rx";
            code+="NUxJallnY1BXTDlNeUlHdVFmRDVwZmlRM29DYUZEbll5TEh5MmhFMU9tUFhsdG52dVBH";
            code+="Vll4WUFHU0VhRjdFV0NiV2ROdWFOc0ZCMlhhZ0lRUVJGQUV3dnhUZXFpcVdkQWJ5SWpB";
            code+="ZHlqRElNYXo1SlFjRVFNNEFRQXhwNUJnazFoVE5pc0MwdzJxSXhVQkVpSHdIWFQvdmFS";
            code+="YnJ2RTVEMHdQSElocUtqcC84RzUyaGFWYm5PRmFWZVJJKzZscXI3NWh4Y2FaRlZnY1lJ";
            code+="R2FNUkdsKytjRHhraVNSL3dGUVVyR0lSWm5VZ0xhakpqRUJHWW92R1VpTzBpVGZqMTdi";
            code+="bVFKaURHb00zM3V2VDJZNXlIRzhJc3BXUGp1U3F1b1E3MUVVNUFBSkV4dmtONTNxMEt6";
            code+="TWdOQm1YMThRU2pKaXV6WDR2VW95MXhGRHVhZzk5eURXYVdDZ1NjNk8raGNNamNCTHF0";
            code+="L2ZHOXNVSlVrSDRURHZXRU84cEVvZXFuK3paNFJCVUdZbWZXTjJ0NFVER1NDL24vTFFY";
            code+="bjhQT1c1RUVMSjdPa0k4UVlvODJ6dDdBNWlVMEFldDJIS21OWTF4dXlpUHg5OUxhYlRa";
            code+="T2F2YitSd1dTRjNJa1g3WHdPRUwwb3djMWhtcmJqU3BORDZrZHIxdXF0ZERxYUtLT2Nl";
            code+="UDJTaGdCa0Q1WFY2S3hZaUwyTllLTzZJb2FaNEJEMFVOQjN0M0J6aUlhTWJLTkpmVHB4";
            code+="RWlYb2dudDBMMVlabkYyT3BwL1JNMEFYai9vSnNjOHZlN0E4UWFzcTI5WnlWVkIwTitM";
            code+="MFhkSTJmdTB4ek4ydm5raTBCQW82NysvcDlTVktoL3QxZm5pOEJHL0FjRzBheDRPRDVP";
            code+="amV5NlJpbUtnQ1VpUDdUOHNBUzlvY0Z4a3M3QXFNZXpOYWdCanJPN1lNNE41N3J2Q1c3";
            code+="N2U0S2J2VUF5TTdUODdFQ0l2UDdOZ0Fld29SRS9OWUR6R3YvV3RpYVRXYURDL2VDUWtq";
            code+="YXB6VDdTYXh1amxqVy91N2ZuZXhFOHdGUEJIV3dpMDVvc3hERU00MzBRU3I3Wi9jNi9U";
            code+="WGtrTE5NMkVndjRRUGE3dllPL0xyOVdlVTlzV09NNCtLYUxBN2piRHdMekN6cmpwZnhB";
            code+="d2pHOFkxZUhWekZwNXZDQzhYUmNmRDdvd2FwbTE5cXF3ZmJ3YisrdkxSZFF5RVdSa1lo";
            code+="SWI3djlFVGNwS0NZcVYvdGxyOWZGREVHN0MyYjlkTGZMWTF3dUh4ZWpMN1packRGZ3g0";
            code+="N0F6TWZ2N1R2Sm5xY1h2QUVYRGZmdDZ1UkkvUURsZWJGUnVsZ3JsM2VwWHFrWVZGVXJ0";
            code+="b2U4cXBPMnNaMjF4MmZudVpBYk1XSDJ6aHBQVVZ4dHZuYlpXQXA1dmdJbzR0UjZBZ0R3";
            code+="dk5lMTl1WGwzRm82N3ZWQWFWKzBKeXRKenJpOGp4S1ZrOHVqY216SU5VU0hrZFhUNGdC";
            code+="VTFDREwzclNiMmF1ejB2WURndEQveDA3cy9sQmlLckYyTWR0QVAvRGdQSGoweUtwQkRo";
            code+="OUl2ZWhodTFWZE9zK1c5d2dXWDZuMjFMK1JVUWppczZQVWRXMmxoRC9ieWpPSVI3WTFW";
            code+="WTFSYk9uNjVOM2xjYlVJL0F3ejZYUXVqdy8yL3Zhb0ZhODNUaFliVWRoM3g1WW1obUlQ";
            code+="UGhjNkcrdVZxK01rMkFSU2wwTnkzRFFwU1JwVmFyZkpTZnlnWjBSenVMZE9aaUs0NTh2";
            code+="ZXRtOU9jNW9ndldTN1RQR0I4RVFvZ0V1Sms3azJ6U0UzeFVFUjJyRWhpTEhYV0xzOGJU";
            code+="MzdGdzJGKzBZa2h4TUxoUGQyZHlLbGsvVmFHWWVqei8yRTZxRERWUXFPRGVOcW8zYlJR";
            code+="Yk9zUWdGOTBzZmExbmoxVUpyNmdtMjNpdmcwdCtraDF1eDhDcW9pOEQzbzRjZEh0KzNy";
            code+="amFqdXB3a25TbGFtdEErRlluTEE2SnpVWm90NXhUMUE4akZzYTRnZWZXNzhVSi9xdE01";
            code+="di95ZDB5STNkNVRPc3hFWjVlYXBkZlpkSjExWFhZRGVDSWdwendXRVdaUmM3cDdYMk9o";
            code+="L2JFY0gwdEZXM0ExRUlCeVg1V2Vlc3ZUU0RYMUkwMUNkb2gySHBVVDg3ZFlPdjArbUZt";
            code+="N2ViOXljZFZCVVJWR0lqK3ZwYzUxMnpYSmtOTVgzcXJvTXFBV0xWejB5MTR4ZkxaVnlL";
            code+="VXdIazhPaDcraU5KK2dwbUU5ZHIxYVJ2SFZLRFdPN1dMVTdRaTV2MWJHM2w5WXVwd3ZP";
            code+="ZVllandxQ2dlN0lVK2Y1Y3BKZU5sZkl5b1FEemk4T2cwcjhaNmNhbTQzRTZ2L21zaTZl";
            code+="NEhYUjZkQUFlUzl6ZnpiR3R1b1NvSWhVMk5mZVh3cUlvVnRmL2ZqLzltWm41aDBWakVo";
            code+="MjROT1R3cUVHdWVILzlEcG5SV3VTeFh5cFIzN3dmT0JOT0VnTHphMTkvTHJDdzhMbFdq";
            code+="aDRPSzdzekNCTUF4OTlTUGNITzIwc2c4aTN2ZHZJT0RJb29vMmdCcUpPSzM1V3hsS2Ju";
            code+="Ynp6cktvU0NKUW9neXdFMDlzYkI0ZlZZT3ZRRFk1dEU4dWNCOGxMSHdNZW5iZkhhNUVh";
            code+="VFlMbzhDYmhyMnpLU3U1eGViN3k1T2hFUC9Ibi9YaFFPWm1qUm1aalpTcDB0R0tlYnRZ";
            code+="V3dleldOeS83am0xMUpOWEZtcHRWZDNud3Y3Tm8vbUlGU3dmNng4SGUxVWhLdEVYRDEw";
            code+="T1hXcmkyUk8zZFc1YkJzdFhHY3ZTN0ZQZlYvWTA1akhNV21mK3N0aUl0SnBDaGZ2THJG";
            code+="M2g3RjVsT1NqeFBhT08xZVhRcXB6M0pnZC80YVc4RTUzNWdDMWo5UFZXZ3JYMWhyLzlJ";
            code+="UUoyN1YrUDhraDZKKzVhQzh0TjJ2cHMreU9lOHZoMGJ3Wm00czlUalJQbHBkdUt2VS9I";
            code+="TDRFem0xbTVrTWxUbTlTamNySjNGVVdzcjZ3emFORkg4T0JYZHllYTMxWk51b25wN0ZQ";
            code+="KzdyTUorNlRNNUZFQTlVNjd6cjF1aEpUSkp0SGlVYWlHY3E0WEx2dFJFL2pHKy8rQUw0";
            code+="Lzdkd2xIRkR3Z0hCeTl0L1Bha2VkWWhGK1F5U01GWFhoTlQ0UU42ZndSV3Y1OGpwMTI0";
            code+="bzl2Y2V4TnJzb1JLWDlsTzFrVms1K050Tk9SLzd6V3pJQnFsMXJOTUg1ZjRscWw3aDU4";
            code+="U3h6K2M5Ly8xeHpac0hIQ3FnMzBsbStqYWRuV3VmWm5SaGlIR3VrQ3lnd1puVG1yelBu";
            code+="MVpYUzd6U1dUS0dWai9JcXdGSHkra3hiT005MHFtdnpQeEVGZXhicXBrUjlNcGJvcEd1";
            code+="dDg5UjEvUE8zcHY2eitZQ2gyUzE1dFhLVGpNOTBzaXRJOFRtb3lpbXZSUEZ3N0toZE8w";
            code+="MXVMSndtdFQrK3ZsTzN4SnBmWGp0WllndEdyUjA5aWdHQkwxaDhJTW5UTURTVVNMZmxw";
            code+="TkNjcXY1Ymt0VnNoQlFuQ1lINGY3YlVqbitjU3B6Y3BGSy9teHgydW1ERnBzMDlhejRT";
            code+="OEZYVmlCVENFekJ2V2NPU0pPNCtTclpXZlFzb2NWWDhqUktmWnF4OGlxWUVEUExwdVJS";
            code+="T1JJNVRTZmpIQWFkdWxuNkxhWldyRDM5OW8wKzFjTUx6M1loa3NXV2VKM2pEbnl5Y0xF";
            code+="M050WkZRUGxTR05aQ3pyUFVSVk1rZzFkaHBYd3J4SkFMM0NiVmJuUGpkNTBRNHdPVE1s";
            code+="OFdwN0VYcHR2UnkvRE9uMW5XR3FDY2RIcFdObTFKeWlSU0dkTWVPdW9qTXJXQjZ2bGsr";
            code+="YXFWbk0raEJyM1JnZHlISE1VUkF4K0xMaVJVazFKdDRXNWxFck9YbncwT2lITFFvU2hs";
            code+="SkkzcXlqTlY3WFQ0bzlva0t4ditCNXRmUmFxVllqdDhqVWw4VUxieDlTL0NtLzkzWWNT";
            code+="bWVFTTdLMnJpYkNIbWJ5WWNJUWtCS1hzZ3NWMHFONWRoZTJDYzR0Yzd6MGg0U2hoYWJr";
            code+="ZXJLZWlWeTd4NDhjTHB0TGdvNnJ5VXptWFRtTktxUDg2ODF1NmZXbkNMSW5sWGoxWEly";
            code+="cWc2RFNRYzc3MWtpd1RIL0FxV1hyNTg5U3o3NWlrWnMxSnJHRDM5bFdtUDFXV0pzYmIw";
            code+="c0Jja05hRWROcWtPa1hkQmpMTjZjbjViZTg4T1FjMVJhNFJkWTNKY2pXaVFUbjU5ZVc5";
            code+="SUdTSXVmMnRzSGh1YVMxUnMvYjZERnMySm9XLzlNa215Vk5rRnVXckpMc09sVm5HcThs";
            code+="UUtEdUtzQ3BzbFBIUE5Bem1COVkzMkdZeEJST1phZnZLb1JmdUczUWJtRGpLbENyRmVH";
            code+="MmhmSTVuZ0l3d2dObzdRUjZkd1lBWVc4RFgxaStiRlVzL2lTclhicTFjZUYyQUM1bDBU";
            code+="TFQxNUhQbFpIdmZMOHd0UjFZeW1nSWgvejFNN25RQ1NUeW4vcm05dFl6V2FFOEFPbzhW";
            code+="THlUb3ZKUU5rdm5wNXVMT2EySlJuS1E0eDlZNURoNHpYdlZueTFNcDlaR2cvd1hRMmJo";
            code+="L3pyMk0vNTJNUEZXdktNa0tvT3lESm1JMTVqbFZjNjR2cStYa3F2MVhDWVl4VTBaTThj";
            code+="QXlBQkFnL2o4VXI4aWRZbnFuSHlhbG1UTlI4THNPYkdxZkpwNUkyNng4b2phbGM5aVVS";
            code+="bThqSmJXVHl1NjZ4S2FoKzFtVzhDS2hwcHhLZVBVT3EvZFA2NUlwb0MyOHFVSUZSbkVC";
            code+="cDR1RFN6bGhxQ0VpYUt5K3AyWFNYelEzYk1UVEdwb3pjaSt3MnJPWnRlVGdDS3hoTFJR";
            code+="MGZKMWY0VllrWER5ZlM5Q1gyeTM0bDdhQUdSU2dWeGR3ZXN5NnEwTHdOSkhUY1FQaFFS";
            code+="MUx1NktvK0p2Q0ZqSjQ0VGFFcUdvTXBqanJXNkFrMWpHcnBQek83UlgwRFUxWWtmUmpE";
            code+="Qkw5RFJPTm0rdHBOTFpLZnQ2a1FTZ0dodW9xTndLUEFYaG9BNStrNG5jbHdVYXp4KzVS";
            code+="Wkh3NzQ0dnRzb2Z5L3pCWmtZUStHUVordm9UUVVOZFBNcFRwQ3RtdlFCQ2Z3aHE4MUVp";
            code+="NEd1Z2lSM3NMbk9FMDg5ZmJoWVNrZjFya296dDBQRXlpZ1MvNEJTcFVvNXBidjdRM1kr";
            code+="U1pwc2xPWTg0UEw4Y1NwTkZ0eUo3OS9ycW5YeXhodnowZmw0SmRXdVJOMzlmdjhkdTVp";
            code+="TFh6YnpxMWJrd29qcW9hMkJvZTcyb1pIQ1JFcmxpOW5XYW52bUh5VmxhL09PeFVod2FD";
            code+="WXJkeDdYakRnTTlJZGdseTExWGtTb1dybzFycHUzOVhKb3krMjJhcERrSlJPdmtmVEcx";
            code+="RzIxZzdKUGlDSSs3UG94MGNKV0tqZnBpNW5iVSt5bitnZnZva2JNcUxHeU1kZHVkUmFP";
            code+="LzVjb2ZLOTloaEFBQWxJOFUya2VaU3NYbFI5dWYrcnE2VzQ1SmthaVI1MU81K0xMbTJh";
            code+="ZVZEUjBsNmxNdU8rNm1tcDJhbyt6VC8xazdmM3orcXZVbi9yeWYycHdHZ0pHcXJGNEFB";
            code+="QUFBRWxGVGtTdVFtQ0MnIHN0eWxlPSd3aWR0aDoxOTRweDtoZWlnaHQ6MTk0cHgnLz48";
            code+="aW1nIHNyYz0nZGF0YTppbWFnZS9qcGVnO2Jhc2U2NCxpVkJPUncwS0dnb0FBQUFOU1Vo";
            code+="RVVnQUFBTUlBQUFEQ0NBSUFBQUNVZzRwbUFBQUFDWEJJV1hNQUFBN0VBQUFPeEFHVkt3";
            code+="NGJBQUFFSkVsRVFWUjRuTzNkVVc3a1JCUkFVWUpZQXV5SkpjK2VZQS9objRvUzI3bXY3";
            code+="STdPK1lRWmQ0ZGNsZlJ3dWZ6Mi92NytHM3pQNzNkL0FYNENHUkdRRVFFWkVaQVJBUmtS";
            code+="a0JFQkdSR1FFWUUvUHYvWGIyOXZlNzdIaDQ3OEgvYWQzM0R1K3p6dEoxMTkvZzJ0UmdS";
            code+="a1JFQkdCR1JFUUVZRXZwalVWblA3azZvWnA1cG9xcC8wMmhSMjVHODk1M2RoTlNJZ0l3";
            code+="SXlJaUFqQWpJaWNIcFNXODNkUmJyMldkV1Y1KzVoUGUzSzM1LzRyRVlFWkVSQVJnUmtS";
            code+="RUJHQklKSmJhZHFsK0RjM2FocjkvaGUvU1FGcXhFQkdSR1FFUUVaRVpBUmdSZWIxSDdx";
            code+="VTJuWGRqOCtoOVdJZ0l3SXlJaUFqQWpJaUVBd3FkMDdVK3g4Vm12OXJHcW41YzVuNGla";
            code+="WWpRaklpSUNNQ01pSWdJd0luSjdVN2oyQjhJZ2pjOUMxUDNQdHMxYlZQYlhuL0M2c1Jn";
            code+="UmtSRUJHQkdSRVFFWUUzbDVybDkzcTJsTmcxVjJ0dVZucHRYNHZWaU1DTWlJZ0l3SXlJ";
            code+="aUFqQXNFOXRlb1UrdXJQWFB2MFNuV2lTSFhYYjg4emNWWWpBaklpSUNNQ01pSWdJd0pm";
            code+="M0ZQYnVTdHY3czFvOTM3NjNJUzFremRmTTA1R0JHUkVRRVlFWkVUZzlLUzIybmxQN2Rx";
            code+="VmoveXRJMzc5K2RmLy9zbmYvLzV6NFRwSDdKeUlqMXpacE1ZNEdSR1FFUUVaRVpBUmdk";
            code+="UFBxYzFOUmsrN2kxUTVNdDlWdXpIbi9qdWIxQmduSXdJeUlpQWpBaklpOE1WemF0WFRV";
            code+="a2ZzZko1cm5aN216TjEzVzkyMTA5SnFSRUJHQkdSRVFFWUVaRVFnZUU1dDV4a2pxK3FP";
            code+="WGpXN0habkw1dDdDZHRmSkxWWWpBaklpSUNNQ01pSWdJd0xCN3NmVjNIN0lJOWVwN3Zy";
            code+="dG5OMVcxVSt4NTkzY1ZpTUNNaUlnSXdJeUlpQWpBaU83SDU5MjltTjE1V3N6MTg1elNG";
            code+="YlZ1N2svWnpVaUlDTUNNaUlnSXdJeUl2RG9zeCtyenpyaTNubnFpQ2UvQzl0cVJFQkdC";
            code+="R1JFUUVZRVpFVGc5SnV2NTk2a1hFMGkxMDRVcVo0dm03dWZhUGNqUDV5TUNNaUlnSXdJ";
            code+="eUlqQTZlZlVQcmpFclNkZHpKazdPK1hJWngyeDg3Uk1reHJqWkVSQVJnUmtSRUJHQklK";
            code+="SjdZT0xQdXpFeUxrNWFNNjlzOXZaNzJNMUlpQWpBaklpSUNNQ01pSXc4cHphMCs2T1Za";
            code+="NzJiTjBSZGoveU1tUkVRRVlFWkVSQVJnUkc3cW5OMmJuLzhNblBoWDNuT2hNL2w5V0ln";
            code+="SXdJeUlpQWpBaklpTURwVS9wM1dxZURhb2ZrdFUrLzkybTduU2UzblAyR1ZpTUNNaUln";
            code+="SXdJeUlpQWpBc0haajVXNXQ2ZGRVMzJmZTMrdVBUdFJyVVlFWkVSQVJnUmtSRUJHQkU1";
            code+="UGFxdWZ1a3R3N2s3Y0VUdm51Ky9QdGxZakFqSWlJQ01DTWlJZ0l3TEJwTFpUZGFiSHpy";
            code+="TW9kOTRMMjNNbS84cHFSRUJHQkdSRVFFWUVaRVRneFNhMU9YT24vYS9tNXJ2VnRkblcy";
            code+="WS9jUUVZRVpFUkFSZ1JrUkNDWTFIYWVIbm52VHNMVjNMbjlxN245a0o1VDR4RmtSRUJH";
            code+="QkdSRVFFWUVUazlxVHpzTmNqVzNKL0RhWGJhNUhadXJhazYwKzVFYnlJaUFqQWpJaUlD";
            code+="TUNMelkrOVI0SnFzUkFSa1JrQkVCR1JHUUVRRVpFWkFSQVJrUmtCR0Ivd0JkMUUrUkV5";
            code+="cUtXd0FBQUFCSlJVNUVya0pnZ2c9PScgc3R5bGU9J3dpZHRoOjE5NHB4O2hlaWdodDox";
            code+="OTRweCcvPjxoMyBzdHlsZT0nbWFyZ2luOjBweCc+RGVkaWNhdGVkIHRvIEl0emlhciwg";
            code+="QWluaG9hIGFuZCBJYW48L2gzPiIpOyQoIiNkaWFsb2ciKS5kaWFsb2coIm9wdGlvbiIs";
            code+="IndpZHRoIiwiNDUwcHgiKTsKCQl9LDEwMCk7Cgl9KTsKfSkoKTs=";
            eval(atob(code));
            // NORMAL CODE
            $("body").append("<div id='dialog'></div>");
            $("#dialog").dialog({ "autoOpen":false });
        }
    }

    function dialog(title,message,buttons) {
        // CHECK SOME PARAMETERS
        if(typeof(message)=="undefined") var message="";
        if(typeof(buttons)=="undefined") var buttons=function() {};
        // SOME PREDEFINED ACTIONS
        var dialog2=$("#dialog");
        if(title=="close") {
            $(dialog2).dialog("close");
            return false;
        }
        if(title=="isopen") {
            return $(dialog2).dialog("isOpen");
        }
        if(title=="ispopup") {
            return $("div[id^=popuptabid]").length;
        }
        if($(dialog2).dialog("isOpen")) {
            return false;
        }
        // PUT SOME OPTIONS
        $(dialog2).dialog("option","closeOnEscape",true);
        $(dialog2).dialog("option","modal",true);
        $(dialog2).dialog("option","autoOpen",false);
        $(dialog2).dialog("option","position",{ my:"center",at:"center",of:window });
        $(dialog2).dialog("option","resizable",true);
        $(dialog2).dialog("option","title",title);
        $(dialog2).dialog("option","buttons",buttons);
        $(dialog2).dialog("option","width","300px");
        $(dialog2).dialog("option","height","auto");
        // TRICK TO HIDE TOOLTIPS
        $(dialog2).dialog("option","open",function(event,ui) {
            hide_tooltips();
        });
        $(dialog2).dialog("option","close",function(event,ui) {
            unmake_focus();
            hide_tooltips();
        });
        // TRICK TO PREVENT THE DEFAULT FOCUS ON THE CLOSE BUTTON
        $(dialog2).parent().find(".ui-dialog-titlebar-close").attr("tabindex","-1");
        // IF MESSAGE EXISTS, OPEN IT
        if(message=="") return false;
        $(dialog2).html("<br/>"+message+"<br/><br/>");
        $(dialog2).dialog("open");
        return true;
    }

    /* FOR NOTIFICATIONS FEATURES */
    var popup_notice=null;
    var popup_timeout=null;

    function make_notice() {
        // REMOVE ALL NOTIFICATIONS EXCEPT THE VOID ELEMENT, IT'S IMPORTANT!!!
        if($("#jGrowl").length>0) {
            $(".jGrowl-notification").each(function() {
                if($(this).text()!="") $(this).remove();
            });
        }
    }

    function hide_popupnotice() {
        // CANCEL IF EXISTS THE SETTIMEOUT
        if(popup_timeout) {
            clearTimeout(popup_timeout);
            popup_timeout=null;
        }
        // CLOSE IF EXISTS THE DESKTOP NOTIFICATION
        if(popup_notice) {
            popup_notice.cancel();
            popup_notice=null;
        }
    }

    function notice(title,message,arg1,arg2,arg3) {
        // PER PREVENIR REPETIR MISSATGES
        var lista=[];
        $(".jGrowl-notification").each(function() {
            var text1=$(".jGrowl-header",this).text();
            var text2=$(".jGrowl-message",this).text();
            lista.push(text1+"|"+text2);
        });
        if(in_array(title+"|"+message,lista)) return;
        // CHECK SOME PARAMETERS
        var action=function() {};
        var theme="ui-state-highlight";
        var sticky=false;
        if(typeof(arg1)=="boolean") sticky=arg1;
        if(typeof(arg1)=="function") action=arg1;
        if(typeof(arg1)=="string") theme=arg1;
        if(typeof(arg2)=="boolean") sticky=arg2;
        if(typeof(arg2)=="function") action=arg2;
        if(typeof(arg2)=="string") theme=arg2;
        if(typeof(arg3)=="boolean") sticky=arg3;
        if(typeof(arg3)=="function") action=arg3;
        if(typeof(arg3)=="string") theme=arg3;
        // EXECUTE THE CODE TO ADD THE INLINE NOTIFICATION
        $.jGrowl(
            message,
            {
                life:10000,
                glue:"before",
                speed:0,
                header:title,
                sticky:sticky,
                close:action,
                theme:theme
            }
        );
        // EXECUTE THE CODE TO ADD THE DESKTOP NOTIFICATION
        var hasperm1=window.webkitNotifications;
        var hasperm2=hasperm1?(window.webkitNotifications.checkPermission()==0):false;
        var hasperm3=hasperm2?getIntCookie("saltos_desktop"):false;
        if(hasperm3) {
            hide_popupnotice();
            var icon=$("link[rel='shortcut icon']").attr("href");
            popup_notice=window.webkitNotifications.createNotification(icon,title,strip_tags(message));
            popup_notice.replaceId="saltos_desktop";
            popup_notice.show();
            popup_notice.onclick=function() { hide_popupnotice(); };
            popup_timeout=setTimeout(function() { hide_popupnotice(); },10000);
        }
    }

    /* FOR COOKIE MANAGEMENT */
    var cookies_data={};
    var cookies_interval=null;
    var cookies_counter=0;

    function __sync_cookies_helper() {
        for(var hash in cookies_data) {
            if(cookies_data[hash].sync) {
                if(cookies_data[hash].val!=cookies_data[hash].orig) {
                    var data="action=cookies&name="+encodeURIComponent(cookies_data[hash].key)+"&value="+encodeURIComponent(cookies_data[hash].val);
                    var value=$.ajax({
                        url:"index.php",
                        data:data,
                        type:"post",
                        async:false,
                    }).responseText;
                    if(value!="") {
                        cookies_data[hash].orig=cookies_data[hash].val;
                        cookies_data[hash].sync=0;
                    }
                } else {
                    cookies_data[hash].sync=0;
                }
            }
        }
    }

    function sync_cookies(cmd) {
        if(typeof(cmd)=="undefined") var cmd="";
        if(cmd=="stop") {
            if(cookies_interval!=null) {
                clearInterval(cookies_interval);
                cookies_interval=null;
            }
            __sync_cookies_helper();
            for(var hash in cookies_data) delete cookies_data[hash];
        }
        if(cmd=="start") {
            // REQUEST ALL COOKIES
            var data="action=ajax&query=cookies";
            $.ajax({
                url:"index.php",
                data:data,
                type:"get",
                async:false,
                success:function(response) {
                    $(response["rows"]).each(function() {
                        var hash=md5(this["clave"]);
                        cookies_data[hash]={
                            "key":this["clave"],
                            "val":this["valor"],
                            "orig":this["valor"],
                            "sync":0
                        };
                    });
                },
                error:function(XMLHttpRequest,textStatus,errorThrown) {
                    errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
                }
            });
            cookies_counter=0;
            cookies_interval=setInterval(function() {
                cookies_counter=cookies_counter+100;
                if(cookies_counter>=1000) {
                    __sync_cookies_helper();
                    cookies_counter=0;
                }
            },100);
        }
    }

    function getCookie(name) {
        var hash=md5(name);
        if(typeof(cookies_data[hash])=="undefined") {
            value=$.cookie(name);
        } else {
            value=cookies_data[hash].val;
        }
        return value;
    }

    function getIntCookie(name) {
        return intval(getCookie(name));
    }

    function getBoolCookie(name) {
        return getIntCookie(name)?true:false;
    }

    function setCookie(name,value) {
        var hash=md5(name);
        if(typeof(cookies_data[hash])=="undefined") {
            if(cookies_interval!=null) {
                cookies_data[hash]={
                    "key":name,
                    "val":value,
                    "orig":value+"!",
                    "sync":1
                };
                cookies_counter=0;
            } else {
                $.cookie(name,value,{expires:365,path:"/"});
            }
        } else {
            if(cookies_data[hash].val!=value) {
                cookies_data[hash].val=value;
                cookies_data[hash].sync=1;
            }
            cookies_counter=0;
        }
    }

    function setIntCookie(name,value) {
        setCookie(name,intval(value));
    }

    function setBoolCookie(name,value) {
        setIntCookie(name,value?1:0);
    }

    /* FOR BLOCK THE UI AND NOT PERMIT 2 REQUESTS AT THE SAME TIME */
    function loadingcontent(message) {
        // CHECK PARAMETERS
        if(typeof(message)=="undefined") {
            var message=lang_loading();
        }
        // CHECK IF EXIST ANOTHER BLOCKUI
        if(isloadingcontent()) {
            $(".blockMsg > h1").text(message);
            return false;
        }
        // TRICK TO FORCE THE FADEIN AND FADEOUT TO BE DISABLED
        $.blockUI.defaults.fadeIn=0;
        $.blockUI.defaults.fadeOut=0;
        $.blockUI.defaults.applyPlatformOpacityRules=false;
        // ACTIVATE THE BLOCK UI FEATURE
        $.blockUI({
            message:"<h2>"+message+"</h2>",
            fadeIn:0,
            fadeOut:0,
            overlayCSS:{
                opacity:"",
                backgroundColor:""
            },
            css:{
                color:"",
                backgroundColor:"",
                border:"",
                padding:"15px",
                "font-family":get_colors("ui-widget","font-family"),
                left:($(window).width()-500)/2+"px",
                width:"500px"
            }
        });
        $(".blockOverlay").addClass("ui-widget-overlay");
        $(".blockMsg").addClass("ui-state-highlight ui-corner-all");
        return true;
    }

    function unloadingcontent() {
        $.unblockUI();
    }

    function isloadingcontent() {
        return $(".blockUI").length>0;
    }

    /* FOR HISTORY MANAGEMENT */
    function hash_encode(url) {
        return str_replace(["+","/"],["-","_"],btoa(bytesToString((new Zlib.RawDeflate(stringToBytes(url))).compress())));
    }

    function hash_decode(hash) {
        try {
            return bytesToString((new Zlib.RawInflate(stringToBytes(atob(str_replace(["-","_"],["+","/"],hash))))).decompress());
        } catch(e) {
            return "";
        }
    }

    function current_href() {
        var url=window.location.href;
        var pos=strpos(url,"#");
        if(pos!==false) url=substr(url,0,pos);
        return url;
    }

    function current_hash() {
        var url=window.location.hash;
        var pos=strpos(url,"#");
        if(pos!==false) url=substr(url,pos+1);
        return url;
    }

    // TRICK FOR OLD BROWSERS
    var ignore_hashchange=0;

    function history_pushState(url) {
        // TRICK FOR OLD BROWSERS
        if(typeof(history.pushState)!='function') {
            if(window.location.href!=url) {
                ignore_hashchange=1;
                window.location.href=url;
            }
            return;
        }
        // NORMAL CODE
        history.pushState(null,null,url);
        // CHECK FOR SOME FUCKED BROWSERS
        if(window.location.href!=url) {
            ignore_hashchange=1;
            window.location.href=url;
        }
    }

    function history_replaceState(url) {
        // TRICK FOR OLD BROWSERS
        if(typeof(history.replaceState)!='function') {
            if(window.location.href!=url) {
                ignore_hashchange=1;
                window.location.replace(url);
            }
            return;
        }
        // NORMAL CODE
        history.replaceState(null,null,url);
        // CHECK FOR SOME FUCKED BROWSERS
        if(window.location.href!=url) {
            ignore_hashchange=1;
            window.location.replace(url);
        }
    }

    function init_history() {
        $(window).on("hashchange",function() {
            // TRICK FOR OLD BROWSERS
            if(ignore_hashchange) {
                ignore_hashchange=0;
                return;
            }
            // NORMAL CODE
            var url=hash_decode(current_hash());
            addcontent("cancel");
            opencontent(url);
        });
        var url=current_href();
        var pos=strrpos(url,"/");
        if(pos!==false) url=substr(url,pos+1);
        if(url===false) url="";
        var pos=strpos(url,"?");
        if(pos===false) url+="?page="+current_page();
        history_replaceState(current_href()+"#"+hash_encode(url));
    }

    /* FOR CONTENT MANAGEMENT */
    var action_addcontent="";

    function addcontent(url) {
        // DETECT SOME ACTIONS
        if(url=="cancel") {
            action_addcontent=url;
            return;
        }
        if(url=="update") {
            action_addcontent=url;
            return;
        }
        if(url=="reload") {
            $(window).trigger("hashchange");
            return;
        }
        if(url=="error") {
            ignore_hashchange=1;
            history.go(-1);
            return;
        }
        // IF ACTION CANCEL
        if(action_addcontent=="cancel") {
            action_addcontent="";
            return;
        }
        // IF ACTION UPDATE
        if(action_addcontent=="update") {
            history_replaceState(current_href()+"#"+hash_encode(url));
            action_addcontent="";
            return;
        }
        // NORMAL CODE
        history_pushState(current_href()+"#"+hash_encode(url));
    }

    function submitcontent(form,callback) {
        //~ console.time("submitcontent");
        if(typeof(callback)=="undefined") var callback=function() {};
        hide_popupdialog();
        loadingcontent(lang_sending());
        $(form).ajaxSubmit({
            beforeSerialize:function(jqForm,options) {
                // TRICK FOR ADD ENCTYPE IF HAS FILES
                var numfiles=0;
                $("input[type=file]",jqForm).each(function() {
                    if($(this).val()!="") numfiles++;
                });
                if(numfiles>0) $(jqForm).attr("enctype","multipart/form-data");
                // TRICK FOR FIX THE MAX_INPUT_VARS ISSUE
                var max_input_vars=ini_get_max_input_vars();
                if(max_input_vars>0) {
                    var total_input_vars=$("input,select,textarea",jqForm).length;
                    if(total_input_vars>max_input_vars) {
                        //~ console.debug("max="+max_input_vars);
                        //~ console.debug("total="+total_input_vars);
                        //~ console.time("fix_input_vars");
                        setTimeout(function() {
                            var fix_input_vars=[];
                            $("input[type=hidden]",jqForm).each(function() {
                                if(total_input_vars>=max_input_vars) {
                                    var temp=$(this).attr("name")+"="+encodeURIComponent($(this).val());
                                    fix_input_vars.push(temp);
                                    $(this).remove();
                                    total_input_vars--;
                                }
                            });
                            $("input[type=checkbox]:not(:checked):not(:visible)",jqForm).each(function() {
                                if(total_input_vars>=max_input_vars) {
                                    $(this).remove();
                                    total_input_vars--;
                                }
                            });
                            $("input[type=checkbox]:not(:visible),input[type=text]:not(:visible),select:not(:visible),textarea:not(:visible)",jqForm).each(function() {
                                if(total_input_vars>=max_input_vars) {
                                    var temp=$(this).attr("name")+"="+encodeURIComponent($(this).val());
                                    fix_input_vars.push(temp);
                                    $(this).remove();
                                    total_input_vars--;
                                }
                            });
                            $("input[type=checkbox]:not(:checked)",jqForm).each(function() {
                                if(total_input_vars>=max_input_vars) {
                                    $(this).remove();
                                    total_input_vars--;
                                }
                            });
                            $("input[type=checkbox],input[type=text],select,textarea",jqForm).each(function() {
                                if(total_input_vars>=max_input_vars) {
                                    var temp=$(this).attr("name")+"="+encodeURIComponent($(this).val());
                                    fix_input_vars.push(temp);
                                    $(this).remove();
                                    total_input_vars--;
                                }
                            });
                            //~ console.debug("length="+fix_input_vars.length);
                            //~ console.debug("total="+total_input_vars);
                            fix_input_vars=btoa(utf8_encode(implode("&",fix_input_vars)));
                            $(jqForm).append("<input type='hidden' name='fix_input_vars' value='"+fix_input_vars+"'/>");
                            //~ console.debug("real="+$("input,select,textarea",jqForm).length);
                            //~ console.debug("real="+$(jqForm).serializeArray().length);
                            //~ console.timeEnd("fix_input_vars");
                            submitcontent(form,callback);
                        },100);
                        return false;
                    }
                }
            },
            beforeSubmit:function(formData,jqForm,options) {
                var action=$(jqForm).attr("action");
                var query=$.param(formData);
                addcontent(action+"?"+query);
                // TO FIX ERROR 414: REQUEST URI TOO LONG
                if(options.type=="get" && strlen(query)>1024) options.type="post";
            },
            beforeSend:function(XMLHttpRequest) {
                make_abort_obj=XMLHttpRequest;
            },
            success:function(data,textStatus,XMLHttpRequest) {
                //~ console.timeEnd("submitcontent");
                callback();
                loadcontent(data);
            },
            error:function(XMLHttpRequest,textStatus,errorThrown) {
                //~ console.timeEnd("submitcontent");
                addcontent("error");
                callback();
                errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
            }
        });
    }

    function opencontent(url,callback) {
        //~ console.time("opencontent");
        // LOGOUT EXCEPTION
        if(strpos(url,"page=logout")!==false) { logout(); return; }
        // TO FIX ERROR 414: REQUEST URI TOO LONG
        var type=(strlen(url)>1024)?"post":"get";
        // CONTINUE
        var temp=explode("?",url,2);
        if(temp[0]=="") temp[0]="index.php";
        if(typeof(temp[1])=="undefined") temp[1]="";
        // NORMAL USAGE
        if(typeof(callback)=="undefined") var callback=function() {};
        loadingcontent();
        $.ajax({
            url:temp[0],
            data:temp[1],
            type:type,
            beforeSend:function(XMLHttpRequest) {
                addcontent(url);
                make_abort_obj=XMLHttpRequest;
            },
            success:function(data,textStatus,XMLHttpRequest) {
                //~ console.timeEnd("opencontent");
                callback();
                loadcontent(data);
            },
            error:function(XMLHttpRequest,textStatus,errorThrown) {
                //~ console.timeEnd("opencontent");
                addcontent("error");
                callback();
                errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
            }
        });
    }

    function errorcontent(code,text) {
        unloadingcontent();
        if(text=="") text=lang_unknownerror();
        alerta("Error: "+code+": "+text);
    }

    function loadcontent(xml) {
        //~ console.time("loadcontent");
        hide_tooltips();
        loadingcontent();
        var html=str2html(fix4html(xml));
        if($(".ui-layout-center",html).text()!="") {
            //~ console.timeEnd("loadcontent");
            updatecontent(html);
        } else {
            // IF THE RETURNED HTML CONTAIN A SCRIPT NOT UNBLOCK THE UI
            var screen=$(".ui-layout-center");
            if($("script",html).length!=0) {
                //~ console.timeEnd("loadcontent");
                $(screen).append(html);
            } else {
                //~ console.timeEnd("loadcontent");
                unloadingcontent();
                if($(".phperror",html).length!=0) {
                    $("div[type=title]",html).remove();
                    unmake_ckeditors(screen);
                    $(screen).html(html);
                } else {
                    $(screen).append(html);
                }
            }
        }
    }

    function html2str(html) {
        var div=document.createElement("div");
        $(div).html(html);
        return $(div).html();
    }

    function str2html(str) {
        var div=document.createElement("div");
        div.innerHTML=str;
        return div;
    }

    function fix4html(str) {
        // REPLACE HTML, HEAD, BODY AND TITLE BY DIV ELEMENTS
        str=str_replace("<html","<div type='html'",str);
        str=str_replace("</html>","</div>",str);
        str=str_replace("<head","<div type='head'",str);
        str=str_replace("</head>","</div>",str);
        str=str_replace("<body","<div type='body'",str);
        str=str_replace("</body>","</div>",str);
        str=str_replace("<title","<div type='title'",str);
        str=str_replace("</title>","</div>",str);
        // RETURN THE STRING
        return str;
    }

    /* FOR RENDER THE SCREEN */
    function getstylesheet(html,cad1,cad2) {
        var style=null;
        $("link[rel=stylesheet]",html).each(function() {
            var href=$(this).attr("href");
            if(strpos(href,cad1)!==false && strpos(href,cad2)!==false) style=this;
        });
        return style;
    }

    function update_style(html,html2) {
        //~ console.time("update_style");
        var cad1=default_stylepre();
        var cad2=default_stylepost();
        var style1=getstylesheet(html2,cad1,cad2);
        var style2=getstylesheet(html,cad1,cad2);
        if(style1 && style2 && $(style1).attr("href")!=$(style2).attr("href")) {
            $(style1).replaceWith(style2);
            // CAMBIAR COLOR DEL META THEME-COLOR
            var meta1=$("meta[name='theme-color']",html2);
            var meta2=$("meta[name='theme-color']",html);
            if($(meta1).attr("content")!=$(meta2).attr("content")) {
                $(meta1).replaceWith(meta2);
            }
            // CAMBIAR COLOR DEL JSTREE
            var cad1=default_jstreepre();
            var cad2=default_jstreepost();
            var style1=getstylesheet(html2,cad1,cad2);
            var style2=getstylesheet(html,cad1,cad2);
            if(style1 && style2 && $(style1).attr("href")!=$(style2).attr("href")) {
                $(style1).replaceWith(style2);
            }
            // RESETEAR COLORES
            get_colors();
        }
        //~ console.timeEnd("update_style");
    }

    function updatecontent(html) {
        //~ console.time("updatecontent");
        unloadingcontent();
        $(document).scrollTop(0);
        // UPDATE THE LANG AND DIR IF NEEDED
        var temp=$("html");
        var temp2=$("div[type=html]",html);
        if($(temp).attr("lang")!=$(temp2).attr("lang")) {
            $(temp).attr("lang",$(temp2).attr("lang"));
        }
        if($(temp).attr("dir")!=$(temp2).attr("dir")) {
            $(temp).attr("dir",$(temp2).attr("dir"));
            $(".ltr,.rtl").removeClass("ltr rtl").addClass($(temp2).attr("dir"));
        }
        // UPDATE THE TITLE IF NEEDED
        var title2=$("div[type=title]",html);
        if(document.title!=$(title2).text()) {
            document.title=$(title2).text();
        }
        // UPDATE THE NORTH PANEL IF NEEDED
        //~ console.time("updatecontent north fase 0");
        var header=$(".ui-layout-north");
        var header2=$(".ui-layout-north",html);
        $(header2).attr("hash",md5($(header2).html()));
        unmake_numbers(header);
        if($(header).attr("hash")!=$(header2).attr("hash")) {
            $(header).replaceWith(header2);
            make_tabs2(header2);
            setTimeout(function() {
                //~ console.time("updatecontent north fase 1");
                make_draganddrop(header2);
                //~ console.timeEnd("updatecontent north fase 1");
            },100);
        }
        //~ console.timeEnd("updatecontent north fase 0");
        // CHECK FOR LOGIN AND LOGOUT
        var menu=$(".ui-layout-west");
        var menu2=$(".ui-layout-west",html);
        $(menu2).attr("hash",md5($(menu2).html()));
        var saltos_login=(!saltos_islogin(menu) && saltos_islogin(menu2))?1:0;
        var saltos_logout=(saltos_islogin(menu) && !saltos_islogin(menu2))?1:0;
        // IF LOGIN
        if(saltos_login) sync_cookies("start");
        // UPDATE THE MENU IF NEEDED
        //~ console.time("updatecontent west fase 0");
        unmake_numbers(menu);
        if($(menu).attr("hash")!=$(menu2).attr("hash")) {
            make_resizable(menu2);
            make_menu(menu2);
            $(menu).replaceWith(menu2);
            setTimeout(function() {
                //~ console.time("updatecontent west fase 1");
                make_draganddrop(menu2);
                //~ console.timeEnd("updatecontent west fase 1");
            },100);
        }
        //~ console.timeEnd("updatecontent west fase 0");
        // IF LOGOUT
        if(saltos_logout) sync_cookies("stop");
        // UPDATE THE CENTER PANE
        //~ console.time("updatecontent center fase 0");
        var screen=$(".ui-layout-center");
        var screen2=$(".ui-layout-center",html);
        unmake_ckeditors(screen);
        make_tabs(screen2);
        make_tables(screen2);
        make_extras(screen2);
        $(screen).replaceWith(screen2);
        make_ckeditors(screen2);
        setTimeout(function() {
            //~ console.time("updatecontent center fase 1");
            if(saltos_login || saltos_logout) make_notice();
            var html2=$("html");
            update_style(html,html2);
            make_draganddrop(screen2);
            make_focus();
            //~ console.timeEnd("updatecontent center fase 1");
        },100);
        bold_menu();
        //~ console.timeEnd("updatecontent center fase 0");
        //~ console.timeEnd("updatecontent");
    }

    function make_menu(obj) {
        if(typeof(obj)=="undefined") var obj=$(".ui-layout-west");
        // CREATE THE MENU USING THE OLD OPENED SECTION STORED IN A COOKIE
        var exists=0;
        $(".menu",obj).each(function() {
            var name=$(this).attr("id");
            var active=getIntCookie("saltos_ui_menu_"+name)?0:false;
            $(this).accordion({
                collapsible:true,
                heightStyle:"content",
                active:active,
                activate:function(event,ui) {
                    var name=$(this).attr("id");
                    var active=ui.newHeader.length;
                    setIntCookie("saltos_ui_menu_"+name,active);
                },
                icons:{
                    header:"ui-icon-circle-arrow-e",
                    activeHeader:"ui-icon-circle-arrow-s"
                }
            });
            exists=1;
            // FOR MOVE NODES AS A REAL TREE
            var temp=[];
            $(".accordion-link li",this).each(function() {
                var found=0;
                for(var i=1;i<20;i++) {
                    if($("a",this).hasClass("depth_"+i)) {
                        if($("ul",temp[i-1]).length==0) $(temp[i-1]).append("<ul></ul>");
                        $("ul",temp[i-1]).append(this);
                        while(temp.length>i) temp.pop();
                        temp.push(this);
                        found=1;
                    }
                }
                if(!found) {
                    while(temp.length>0) temp.pop();
                    temp.push(this);
                }
            });
            // FOR PREPARE THE OPEN NODE LIST
            var open=[];
            $(".accordion-link li",this).each(function() {
                var name2=$("a",this).attr("id");
                var active=getIntCookie("saltos_ui_menu_"+name+"_"+name2);
                if(active) open.push("#"+name2);
            });
            // CREATE THE JSTREE
            $(".accordion-link",this).jstree();
            // NOW, OPEN THE NODES USING THE PREVIOUS NODE LIST
            for(var i in open) {
                var temp=$(open[i],this);
                $(".accordion-link",this).jstree("open_node",temp);
            }
            // DEFINE AND EXECUTE THE FIX FOR THE ICONS
            var fn=function(obj) {
                $(".jstree-icon.jstree-themeicon",obj).each(function() {
                    var icon=$(this).parent().attr("icon");
                    $(this).removeClass("jstree-themeicon").addClass("jstree-themeicon-custom").addClass(icon);
                });
            }
            fn(this);
            // PROGRAM THE BIND TO PREVENT SELECTION
            $(".accordion-link",this).on("select_node.jstree",function(e,_data) {
                _data.instance.deselect_node(_data.node);
            });
            // PROGRAM THE BIND TO STORE THE NODE'S STATES
            $(".accordion-link",this).on("open_node.jstree",function(e,_data) {
                fn(this);
                var name2=_data.node.a_attr.id;
                setIntCookie("saltos_ui_menu_"+name+"_"+name2,1);
            });
            $(".accordion-link",this).on("close_node.jstree",function(e,_data) {
                var name2=_data.node.a_attr.id;
                setIntCookie("saltos_ui_menu_"+name+"_"+name2,0);
            });
        });
        if(exists) {
            var closed=getIntCookie("saltos_ui_menu_closed");
            if(closed) {
                $(obj).addClass("none");
            } else {
                $(obj).removeClass("none");
            }
        } else {
            $(obj).addClass("none");
        }
    }

    function toggle_menu() {
        var obj=$(".ui-layout-west");
        if($(obj).is(":visible")) {
            $(obj).addClass("none");
            setIntCookie("saltos_ui_menu_closed",1);
        } else {
            $(obj).removeClass("none");
            setIntCookie("saltos_ui_menu_closed",0);
        }
    }

    function bold_menu() {
        $(".ui-layout-west .bold").removeClass("bold");
        $(".ui-layout-west a[id="+getParam("page")+"]").addClass("bold");
    }

    function make_tabs(obj) {
        //~ console.time("make_tabs");
        if(typeof(obj)=="undefined") var obj=$(".ui-layout-center");
        // REPAIR THE DIVS THAT NOT ARE A TABS.
        // THE CODE FIND ALL DIVS AND IF NOT IS A VALID TAB,
        // MOVE ALL THE CONTENT TO THE PREVIOUS DIV THAT IS A VALID TAB
        var oldtab=null;
        $(".sitabs,.notabs",obj).each(function() {
            if($(this).hasClass("sitabs")) {
                oldtab=this;
            } else {
                $(this).removeClass("notabs");
                if(oldtab!=null) $(oldtab).append($(this));
            }
        });
        // FIX A BUG ON THE XSLT THAT REPEAT THE TABID
        var hrefs=[];
        var count=0;
        $(".tabs > ul > li > a",obj).each(function() {
            var href=$(this).attr("href");
            if(!in_array(href,hrefs)) {
                hrefs.push(href);
            } else {
                count++;
                href=substr(href,0,7)+sprintf("%09d",intval(substr(href,7))+count);
                $(this).attr("href",href);
            }
        });
        var ids=[];
        var count=0;
        $(".tabs div[id^=tabid]",obj).each(function() {
            var id=$(this).attr("id");
            if(!in_array(id,ids)) {
                ids.push(id);
            } else if(in_array("#"+id,hrefs)) {
                count++;
                id=substr(id,0,6)+sprintf("%09d",intval(substr(id,6))+count);
                $(this).attr("id",id);
            }
        });
        // THIS CODE ADD THE ACCESSKEY FEATURE FOR EACH TAB
        var accesskeys="1234567890";
        var accesskey=0;
        var tabs=$(".tabs > ul > li",obj);
        $(tabs).each(function() {
            if($(this).hasClass("help")) {
                $("a",this).attr("title","[CTRL] + [H]");
                $("a",this).addClass("shortcut_ctrl_h");
            } else if(accesskey<accesskeys.length) {
                $("a",this).attr("title","[CTRL] + ["+substr(accesskeys,accesskey,1)+"]");
                $("a",this).addClass("shortcut_ctrl_"+substr(accesskeys,accesskey,1));
                accesskey++;
            }
        });
        // THIS CODE SEARCH THE TAB USING THE OLD OPENED TAB STORED IN A COOKIE
        // TOO, FIND ALL OBJECTS FROM THE FORM AND IF EXIST THE FOCUSED ATTRIBUTE,
        // SEARCH THE INDEX OF THE TAB THAT CONTAIN THE OBJECT
        var active=0;
        $("[focused=true]:first",obj).each(function() {
            make_focus_obj=this;
            var thetab=$(this).parent();
            while(thetab) {
                if($(thetab).hasClass("sitabs") && substr($(thetab).attr("id"),0,5)=="tabid") {
                    var index=0;
                    $("[id^=tabid][class=sitabs]",obj).each(function() {
                        if($(this).attr("id")==$(thetab).attr("id")) active=index;
                        index++;
                    });
                    break;
                }
                thetab=$(thetab).parent();
            }
        });
        // TRUE, CREATE THE TABS
        $(".tabs",obj).tabs({
            active:active,
            beforeActivate:function(event,ui) {
                if($(ui.newTab).hasClass("help")) {
                    viewpdf("page="+getParam("page"));
                    return false;
                }
                if($(ui.newTab).hasClass("popup")) {
                    var title=$("a",ui.newTab).text();
                    var tabid=$("a",ui.newTab).attr("href").substr(1);
                    if(getParam("action")=="list") var form=$("#"+tabid).parent();
                    if(getParam("action")=="form") var form=$("#"+tabid);
                    dialog(title);
                    var dialog2=$("#dialog");
                    $(dialog2).html("");
                    $(form).after("<div id='popup"+tabid+"'></div>");
                    $(dialog2).append("<br/>");
                    $(dialog2).append(form);
                    $(dialog2).append("<br/><br/>");
                    $("div",dialog2).removeAttr("class").removeAttr("style");
                    $(dialog2).dialog("option","resizeStop",function(event,ui) {
                        setIntCookie("saltos_popup_width",$(dialog2).dialog("option","width"));
                        setIntCookie("saltos_popup_height",$(dialog2).dialog("option","height"));
                    });
                    $(dialog2).dialog("option","close",function(event,ui) {
                        $(dialog2).dialog("option","resizeStop",function() {});
                        $(dialog2).dialog("option","close",function() {});
                        if(getParam("action")=="list") $("div",form).hide();
                        if(getParam("action")=="form") $(form).hide();
                        $("#popup"+tabid).replaceWith(form);
                        unmake_focus();
                        hide_tooltips();
                    });
                    var width=getIntCookie("saltos_popup_width");
                    if(!width) width=900;
                    $(dialog2).dialog("option","width",width);
                    var height=getIntCookie("saltos_popup_height");
                    if(!height) height=600;
                    $(dialog2).dialog("option","height",height);
                    $(dialog2).dialog("option","position",{ my:"center",at:"center",of:window });
                    $(dialog2).dialog("open");
                    return false;
                }
            },
            beforeLoad:function(event,ui) {
                return false;
            }
        });
        // CHANGE TABS FROM ALL TO TOP
        $(".tabs ul",obj).removeClass("ui-corner-all").addClass("ui-corner-top");
        // TUNNING THE HELP TAB
        var help=$(".tabs ul li.help",obj);
        $("span",help).removeClass("ui-icon ui-icon-none").addClass(icon_help());
        $("a",help).append("&nbsp;").append(lang_help());
        //~ console.timeEnd("make_tabs");
    }

    function hide_popupdialog() {
        if(dialog("isopen") && dialog("ispopup")) dialog("close");
    }

    function make_tabs2(obj) {
        //~ console.time("make_tabs2");
        if(typeof(obj)=="undefined") var obj=$(".ui-layout-north");
        // MAKE THE TABS
        $(".tabs2",obj).tabs({
            beforeActivate:function(event,ui) {
                return false;
            },
            beforeLoad:function(event,ui) {
                return false;
            }
        });
        // FIX FOR A VOID TABS
        $(".tabs2 div",obj).remove();
        // CHANGE TABS FROM TOP TO BOTTOM
        $(".tabs2 ul",obj).removeClass("ui-corner-all").addClass("ui-corner-bottom");
        $(".tabs2 li",obj).removeClass("ui-tabs-active ui-state-active");
        $(".tabs2 li",obj).removeClass("ui-corner-top").addClass("ui-corner-bottom");
        var padding=$(".tabs2 ul",obj).css("padding-top");
        var margin=$(".tabs2 li",obj).css("margin-top");
        var border=$(".tabs2 li",obj).css("border-top");
        if(!border) border=$(".tabs2 li",obj).css("border-top-width")+" "+$(".tabs2 li",obj).css("border-top-style")+" "+$(".tabs2 li",obj).css("border-top-color");
        $(".tabs2 ul",obj).css("padding-top","0").css("padding-bottom",padding);
        $(".tabs2 li",obj).css("margin-top","0").css("margin-bottom",margin);
        $(".tabs2 li",obj).css("border-top","0").css("border-bottom",border);
        //~ console.timeEnd("make_tabs2");
    }

    function make_extras(obj) {
        //~ console.time("make_extras");
        if(typeof(obj)=="undefined") var obj=$(".ui-layout-center");
        // PROGRAM COPY FIELDS
        $("td[iscopy=true]",obj).each(function() {
            var name=$(this).attr("copyname");
            var tdfield=$("#"+name,obj).parent();
            var tdlabel=$(tdfield).prev();
            if($(tdlabel).hasClass("label")) {
                tdlabel=$(tdlabel).clone();
                $(this).prev().replaceWith(tdlabel);
            }
            var oldfield=$("#"+name,tdfield);
            tdfield=$(tdfield).clone();
            var newfield=$("#"+name,tdfield);
            $(newfield).attr("id","iscopy"+name);
            $(newfield).attr("name","iscopy"+name);
            if($(newfield).is("select")) {
                $(tdfield).removeAttr("style");
                $(newfield).removeAttr("style");
            }
            // CREATE THE EVENT LINKS BETWEEN THE OLD AND NEW FIELDS
            $(newfield).on("change",function(event,extra) {
                if(extra=="stop") return;
                $(oldfield).val($(this).val());
                $(oldfield).trigger("change","stop");
            });
            $(newfield).on("keydown",function(event,extra) {
                if(extra=="stop") return;
                $(oldfield).val($(this).val());
                $(oldfield).trigger("keydown","stop");
            });
            $(oldfield).on("change",function(event,extra) {
                if(extra=="stop") return;
                $(newfield).val($(this).val());
                $(newfield).trigger("change","stop");
            });
            $(oldfield).on("keydown",function(event,extra) {
                if(extra=="stop") return;
                $(newfield).val($(this).val());
                $(newfield).trigger("keydown","stop");
            });
            // PROGRAM EVENTS OF THE ORIGINAL FIELD TYPE=COPY
            if($(this).is("[onchange][onchange!='']")) {
                var fn=$(this).attr("onchange");
                $(newfield).on("change",function(event,extra) {
                    if(extra=="stop") return;
                    eval(fn);
                });
            }
            if($(this).is("[onkeydown][onkeydown!='']")) {
                var fn=$(this).attr("onkeydown");
                $(newfield).on("keydown",function(event,extra) {
                    if(extra=="stop") return;
                    eval(fn);
                });
            }
            if($(this).is("[class][class!='']")) {
                $(newfield).addClass($(this).attr("class"));
            }
            if($(this).is("[focused][focused!='']")) {
                $(newfield).attr("focused",$(this).attr("focused"));
                make_focus_obj=newfield;
            }
            $(this).replaceWith(tdfield);
        });
        // CREATE THE DATEPICKERS
        $.datepicker.setDefaults($.datepicker.regional[lang_default()]);
        $("input[isdate=true]",obj).each(function() {
            $(this).datepicker({
                dateFormat:"yy-mm-dd",
                firstDay:1,
                numberOfMonths:3,
                showCurrentAtPos:1,
                stepMonths:3,
                showOn:"none",
                showAnim:"",
                constrainInput:false
            });
        });
        $("a[isdate=true]",obj).on("click",function() {
            if(!is_disabled(this)) $(this).prev().datepicker("show");
        });
        $("input[isdate=true]",obj).on("change",function() {
            if($(this).val()!="") $(this).val(dateval($(this).val()));
        });
        // CREATE THE TIMEPICKERS
        $("input[istime=true]",obj).each(function() {
            $(this).timepicker({
                className:"ui-widget ui-state-default",
                scrollDefault:"now",
                showOn:"none",
                timeFormat:"H:i:s",
                step:15,
                show2400:true,
            });
        });
        $("a[istime=true]",obj).on("click",function() {
            if(!is_disabled(this)) $(this).prev().timepicker('show');
        });
        $("input[istime=true]",obj).on("change",function() {
            if($(this).val()!="") $(this).val(timeval($(this).val()));
        });
        // PROGRAM THE DATETIME JOIN
        $("input[isdatetime=true]",obj).each(function() {
            var name=$(this).attr("name");
            var full=$("input[name="+name+"]",obj);
            var date=$("input[name="+name+"_date]",obj);
            var time=$("input[name="+name+"_time]",obj);
            $(date).on("change",function() {
                $(full).val($(date).val()+" "+$(time).val());
                $(full).trigger("change");
            });
            $(time).on("change",function() {
                $(full).val($(date).val()+" "+$(time).val());
                $(full).trigger("change");
            });
        });
        // CREATE THE COLOR PICKERS
        $("input[iscolor=true]",obj).each(function() {
            $(this).on("keyup",function() {
                var caja=$("#"+$(this).attr("id")+"_color",obj);
                $(caja).css("background-color",$(this).val());
            });
        });
        $("a[iscolor=true]",obj).each(function() {
            $(this).ColorPicker({
                onBeforeShow:function() {
                    var caja=$("#"+substr($(this).attr("id"),0,-6),obj);
                    $(this).ColorPickerSetColor(substr($(caja).val(),1));
                },
                onShow:function(colpkr) {
                    $(colpkr).show();
                    return false;
                },
                onHide:function(colpkr) {
                    $(colpkr).hide();
                    return false;
                },
                onSubmit:function(hsb, hex, rgb, el) {
                    $(el).css("background-color","#"+hex);
                    var caja=$("#"+substr($(el).attr("id"),0,-6),obj);
                    $(caja).val("#"+strtoupper(hex));
                }
            });
        });
        $(".colorpicker",obj).css("z-index",9999);
        // PROGRAM INTERGER TYPE CAST
        $("input[isinteger=true]",obj).each(function() {
            $(this).on("keyup",function() { intval2(this); });
        });
        // PROGRAM FLOAT TYPE CAST
        $("input[isfloat=true]",obj).each(function() {
            $(this).on("keyup",function() { floatval2(this); });
        });
        // PROGRAM LINKS OF SELECTS
        $("a[islink=true]",obj).on("click",function() {
            var id=str_replace("nombre","id",$(this).attr("forlink"));
            var val=intval($("#"+id).val());
            var fn=$(this).attr("fnlink");
            if(val) eval(str_replace("ID",val,fn));
        });
        // ADD THE SELECT ALL FEATURE TO LIST
        var master="input.master[type=checkbox]";
        var slave="input.slave[type=checkbox]";
        $(master,obj).attr("title",lang_selectallcheckbox());
        $(slave,obj).attr("title",lang_selectonecheckbox());
        $(master,obj).on("click",function() {
            $(this).prop("checked",!$(this).prop("checked"));
        }).parent().on("click",function() {
            var checkbox=$(master,this);
            var value=$(checkbox).prop("checked");
            $(checkbox).prop("checked",!value);
            var table=$(checkbox).parent().parent().parent();
            if(!$(slave,table).length) table=$(table).parent().parent().parent();
            $(slave,table).prop("checked",!value);
            if(!value) {
                var color=$(".tbody:first",table).css("border-bottom-color");
                $(".tbody",table).addClass("ui-state-highlight").css("border-color",color);
            }
            if(value) $(".tbody",table).removeClass("ui-state-highlight");
        });
        // PROGRAM CHECK ENTER
        $("input,select",obj).on("keydown",function(event) {
            if($(".ui-autocomplete").is(":visible")) {
                // DETECTED AN OPEN AUTOCOMPLETE WIDGET
                return;
            }
            if(is_enterkey(event)) {
                if(this.form) {
                    for(var i=0,len=this.form.elements.length;i<len;i++) {
                        if(this==this.form.elements[i]) break;
                    }
                    for(var j=0,len=this.form.elements.length;j<len;j++) {
                        i=(i+1)%this.form.elements.length;
                        if(this.form.elements[i].type=="text") break;
                    }
                    $(this.form.elements[i]).trigger("focus");
                }
            }
        });
        // PROGRAM THEAD TOGGLE EFFECT
        $("span[hover='true']",obj).on("mouseover",function() {
            $(this).toggleClass($(this).attr("toggle"));
        }).on("mouseout",function() {
            $(this).toggleClass($(this).attr("toggle"));
        });
        // TO CLEAR AMBIGUOUS THINGS
        $(".nowrap.siwrap",obj).removeClass("nowrap siwrap");
        // TRICK FOR STYLING THINGS AS INFO, ERROR AND TITLE
        $(".info",obj).addClass("ui-state-highlight ui-corner-all");
        $(".error",obj).addClass("ui-state-error ui-corner-all");
        $(".title",obj).addClass("ui-widget-header ui-corner-all");
        // TRICK TO BLOCK CHECKBOXES
        $("input:checkbox.ui-state-disabled",obj).on("change",function(event) {
            $(this).prop("checked",!$(this).prop("checked"));
        });
        // PROGRAM SELECTS
        if(is_chrome()) {
            $("select",obj).addClass("chrome");
        }
        // PROGRAM MULTISELECTS
        $("input[ismultiselect=true]",obj).each(function() {
            var value=explode(",",$(this).val());
            var name=$(this).attr("name");
            $("select[name="+name+"_all] option",obj).each(function() {
                if(in_array($(this).attr("value"),value)) $(this).remove();
            });
            $("select[name="+name+"_set] option",obj).each(function() {
                if(!in_array($(this).attr("value"),value)) $(this).remove();
            });
            $("a[name="+name+"_add]",obj).on("click",function() {
                $("select[name="+name+"_all] option:selected").each(function() {
                    $("select[name="+name+"_set]").append($(this).clone());
                    $(this).remove();
                });
                var value=[];
                $("select[name="+name+"_set] option").each(function() {
                    value.push($(this).val());
                });
                value=implode(",",value);
                $("input[name="+name+"]").val(value);
            });
            $("a[name="+name+"_del]",obj).on("click",function() {
                $("select[name="+name+"_set] option:selected").each(function() {
                    $("select[name="+name+"_all]").append($(this).clone());
                    $(this).remove();
                });
                var value=[];
                $("select[name="+name+"_set] option").each(function() {
                    value.push($(this).val());
                });
                value=implode(",",value);
                $("input[name="+name+"]").val(value);
            });
        });
        // PROGRAM SELECT MENU
        $("select[ismenu=true]",obj).on("change",function() {
            if(!$(this).find("option:selected").hasClass("ui-state-disabled")) eval($(this).val());
            if($("option:first",this).val()=="") $(this).val("");
        });
        // PROGRAM ACTIONS LIST
        $(".actions2",obj).parent().each(function() {
            var actions1=$(this).find(".actions1");
            var actions2=$(this).find(".actions2");
            if(actions1.length>1) {
                $(actions1).addClass("none");
            } else {
                $(actions2).addClass("none");
            }
        });
        // PROGRAM AUTO-WIDTH SELECT
        $("select:not([multiple])",obj).each(function() {
            if(str_replace(["width:","undefined"],"",$(this).attr("style"))) return;
            $(this).on("change init",function() {
                var texto=$("option:selected",this).text();
                var bbox=get_bbox("ui-state-default",texto);
                $(this).attr("style","width:"+(bbox.w+26)+"px");
            }).trigger("init");
        });
        //~ console.timeEnd("make_extras");
    }

    function get_bbox(clase,texto) {
        // GET THE BBOX USING THIS TRICK
        if($("#ui-text-trick").length==0) {
            $("body").append("<div class='ui-widget'><span id='ui-text-trick'></span></div>");
        }
        $("#ui-text-trick").addClass(clase);
        $("#ui-text-trick").html(texto);
        var w=$("#ui-text-trick").width();
        var h=$("#ui-text-trick").height();
        $("#ui-text-trick").removeClass(clase);
        $("#ui-text-trick").html("");
        return {w:w,h:h};
    }

    function is_chrome() {
        return navigator.userAgent.indexOf("Chrome")!=-1;
    }

    function make_draganddrop(obj) {
        //~ console.time("make_draganddrop");
        if(typeof(obj)=="undefined") var obj=$("body");
        // PROGRAM DRAG AND DROP
        $(".draggable",obj).draggable({
            cursor:"",
            cursorAt:{
                top:0,
                left:-10
            },
            appendTo:"body",
            revert:"invalid",
            delay:500,
            helper:function(event) {
                var row=$("<div class='ui-widget nowrap'></div>");
                var text=[];
                var padre=$(this).parent().parent();
                $("td",padre).each(function() {
                    var temp=$(this).text();
                    if(temp!="") text.push(temp);
                });
                text=implode(" | ",text);
                text=str_replace(["<",">"],["&lt;","&gt;"],text);
                $(row).append(text);
                $(row).addClass("ui-state-highlight");
                $(row).addClass("ui-corner-all");
                $(row).css("padding","3px 5px");
                return row;
            }
        });
        $(".droppable",obj).droppable({
            tolerance:"pointer",
            drop:function(event,ui) {
                var id_draggable=get_class_id(ui.draggable);
                var id_droppable=get_class_id(this);
                var fn_droppable=get_class_fn(this);
                eval(fn_droppable+"('"+id_draggable+"','"+id_droppable+"')");
            }
        });
        //~ console.timeEnd("make_draganddrop");
    }

    function get_class_key_val(obj,param) {
        var clases=explode(" ",$(obj).attr("class"));
        var total=clases.length;
        var length=strlen(param);
        for(var i=0;i<total;i++) {
            if(substr(clases[i],0,length)==param) {
                return substr(clases[i],length);
            }
        }
        return "";
    }

    function get_class_id(obj) {
        return get_class_key_val(obj,"id_");
    }

    function get_class_fn(obj) {
        return get_class_key_val(obj,"fn_");
    }

    function get_class_hash(obj) {
        return get_class_key_val(obj,"hash_");
    }

    function make_hovers() {
        //~ console.time("make_hovers");
        var inputs="a.ui-state-default,input.ui-state-default,textarea.ui-state-default,select.ui-state-default";
        $(document).on("mouseover",inputs,function() {
            if($(this).hasClass("ui-state-disabled")) return;
            $(this).addClass("ui-state-hover");
        }).on("mouseout",inputs,function() {
            if($(this).hasClass("ui-state-disabled")) return;
            $(this).removeClass("ui-state-hover");
        }).on("focus",inputs,function() {
            if($(this).hasClass("ui-state-disabled")) return;
            $(this).addClass("ui-state-focus");
        }).on("blur",inputs,function() {
            if($(this).hasClass("ui-state-disabled")) return;
            $(this).removeClass("ui-state-focus");
        });
        //~ console.timeEnd("make_hovers");
    }

    function make_ckeditors(obj) {
        //~ console.time("make_ckeditors");
        if(typeof(obj)=="undefined") var obj=$(".ui-layout-center");
        // AUTO-GROWING TEXTAREA
        $("textarea[ckeditor!=true][codemirror!=true]",obj).each(function() {
            if($(this).attr("id")=="") return;
            var textarea="#"+$(this).attr("id");
            var interval=setInterval(function() {
                var textarea2=$(textarea);
                if(!$(textarea2).length) {
                    clearInterval(interval);
                    //~ console.debug("textarea "+textarea+" destroyed");
                } else if($(textarea2).is(":visible")) {
                    clearInterval(interval);
                    //~ console.debug("textarea "+textarea+" rendered");
                    $(textarea2).autogrow();
                }
            },100);
        });
        // AUTO-GROWING IFRAMES
        $("iframe",obj).each(function() {
            if($(this).attr("id")=="") return;
            var iframe="#"+$(this).attr("id");
            var interval=setInterval(function() {
                var iframe2=$(iframe);
                if(!$(iframe2).length) {
                    //~ console.debug("iframe "+iframe+" destroyed");
                    clearInterval(interval);
                } else if($(iframe2).is(":visible")) {
                    //~ console.debug("iframe "+iframe+" visible");
                    if(typeof($(iframe2).prop("isloaded"))=="undefined") {
                        //~ console.debug("iframe "+iframe+" init");
                        $(iframe2).each(function() {
                            $(this).prop("isloaded","false");
                            $(this).on("load",function() {
                                $(this).prop("isloaded","true");
                            });
                            var iframe3=this.contentWindow.location;
                            var url=$(this).attr("url");
                            if(url) iframe3.replace(url);
                            if(!url) clearInterval(interval);
                        });
                    } else if($(iframe2).prop("isloaded")=="true") {
                        //~ console.debug("iframe "+iframe+" loaded");
                        clearInterval(interval);
                        if(security_iframe(iframe2)) {
                            var minheight=$(iframe2).height();
                            var newheight=$(iframe2).contents().height()+20;
                            if(newheight>minheight) $(iframe2).height(newheight);
                            $(iframe2).each(function() {
                                var iframe3=this.contentWindow.document;
                                $(iframe3).on("contextmenu",function(e) { return e.ctrlKey; });
                                $(iframe3).on("keydown",function(e) { $(document).trigger(e); });
                            });
                        }
                    }
                }
            },100);
        });
        // CREATE THE CKEDITORS
        $("textarea[ckeditor=true]",obj).each(function() {
            $(this).ckeditor({
                title:"",
                skin:"moono-lisa",
                extraPlugins:"codesnippetgeshi,autogrow,base64image",
                removePlugins:"elementspath",
                enterMode:CKEDITOR.ENTER_BR,
                shiftEnterMode:CKEDITOR.ENTER_BR,
                toolbar:[["Bold","Italic","Underline","Strike"],["NumberedList","BulletedList","-","Outdent","Indent"],["Link","Unlink"],["TextColor","BGColor"],["Undo","Redo"],["Maximize","Source","CodeSnippet","base64image","HorizontalRule"]],
                language:lang_default(),
                autoGrow_onStartup:true,
                autoGrow_minHeight:$(this).height()-25,
                width:$(this).width()+10,
                disableNativeSpellChecker:false,
                dialog_backgroundCoverColor:"#aaa",
                dialog_backgroundCoverOpacity:0.3,
                resize_enabled:false,
                codeSnippetGeshi_url:"../../?action=geshi",
                //~ forcePasteAsPlainText:true,
                //~ uiColor:get_colors("ui-state-default","background-color"),
                //~ uiColor:"transparent",
                allowedContent:true,
                extraAllowedContent:$(this).attr("ckextra"),
            },function() {
                var obj=$("#"+$(this).attr("name")).next();
                $(obj).addClass("ui-state-default ui-corner-all");
                $(obj).on("mouseover",function() {
                    $(this).addClass("ui-state-hover");
                }).on("mouseout",function() {
                    $(this).removeClass("ui-state-hover");
                }).on("focus",function() {
                    $(this).addClass("ui-state-focus");
                }).on("blur",function() {
                    $(this).removeClass("ui-state-focus");
                });
            });
        });
        // CREATE THE CODE MIRROR
        $("textarea[codemirror=true]",obj).each(function() {
            if(is_chrome()) {
                $(this).css("overflow","hidden");
            }
            var width=$(this).width();
            var height=$(this).height();
            var classes=$(this).attr("class");
            var cm=CodeMirror.fromTextArea(this,{
                lineNumbers:true
            });
            $(this).data("cm",cm);
            var fnresize=function(cm) {
                var height2=max(height,cm.doc.size*15);
                if(cm.display.sizerWidth>cm.display.lastWrapWidth) height2+=15;
                cm.setSize(width+10,height2+10);
            }
            fnresize(cm);
            cm.on("viewportChange",fnresize);
            $(this).next().addClass(classes).css("margin","1px");
            cm.on("change",cm.save);
        });
        // REQUEST THE PLOTS
        var attrs=["legend","vars","colors","graph","ticks","posx",
            "data1","data2","data3","data4","data5","data6","data7","data8","data9","data10",
            "data11","data12","data13","data14","data15","data16"];
        $("img[isplot=true]",obj).each(function() {
            var map="#"+$(this).prev().attr("id");
            var interval=setInterval(function() {
                var map2=$(map);
                var img=$(map2).next().get(0);
                if(!$(map2).length) {
                    clearInterval(interval);
                } else if($(img).is(":visible")) {
                    clearInterval(interval);
                    var querystring="action=phplot";
                    querystring+="&width="+$(img).width();
                    querystring+="&height="+$(img).height();
                    var data=$(img).attr("title3");
                    if(typeof(data)!="undefined") querystring+="&title="+encodeURIComponent(data);
                    for(var i=0,len=attrs.length;i<len;i++) {
                        var data=$(img).attr(attrs[i]);
                        if(typeof(data)!="undefined") querystring+="&"+attrs[i]+"="+encodeURIComponent(data);
                    };
                    $.ajax({
                        url:"index.php",
                        data:querystring,
                        type:"post",
                        success:function(response) {
                            $(img).attr("src",response["img"]);
                            var map=$(img).attr("usemap");
                            $(response["map"]).each(function() {
                                var area="<area shape='"+this["shape"]+"' coords='"+this["coords"]+"' title='"+this["value"]+"'>";
                                $(map).append(area);
                            });
                        },
                        error:function(XMLHttpRequest,textStatus,errorThrown) {
                            errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
                        }
                    });
                }
            },100);
        });
        // PROGRAM AUTOCOMPLETE FIELDS
        $("input[isautocomplete=true],textarea[isautocomplete=true]",obj).each(function() {
            var key=$(this).attr("name");
            var prefix="";
            $("input[name^=prefix_]").each(function() {
                var val=$(this).val();
                if(key.substr(0,val.length)==val) prefix=val;
            });
            var query=$(this).attr("querycomplete");
            var filter=$(this).attr("filtercomplete");
            var fn=$(this).attr("oncomplete");
            $(this).autocomplete({
                delay:300,
                source:function(request,response) {
                    var term=request.term;
                    var input=this.element;
                    var data="action=ajax&query="+query+"&term="+encodeURIComponent(term);
                    if(typeof($("#"+prefix+filter).val())!="undefined") data+="&filter="+$("#"+prefix+filter).val();
                    $.ajax({
                        url:"index.php",
                        data:data,
                        type:"get",
                        success:function(data) {
                            // TO CANCEL OLD REQUESTS
                            var term2=$(input).val();
                            if(term==term2) response(data["rows"]);
                        },
                        error:function(XMLHttpRequest,textStatus,errorThrown) {
                            errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
                        }
                    });
                },
                search:function() {
                    return this.value.length>0;
                },
                focus:function() {
                    return false;
                },
                select:function(event,ui) {
                    this.value=ui.item.label;
                    if(typeof(fn)!="undefined") eval(fn);
                    return false;
                }
            });
        });
        // FOR EXCEL
        $("div.excel",obj).each(function() {
            var data=$(this).attr("data");
            if(data!="") data=eval(data);
            else data=JSON.parse($(this).attr("rows"));
            var rowHeaders=$(this).attr("rowHeaders");
            if(rowHeaders!="") rowHeaders=eval(rowHeaders);
            else rowHeaders=true;
            var colHeaders=$(this).attr("colHeaders");
            if(colHeaders!="") colHeaders=eval(colHeaders);
            else colHeaders=true;
            var minSpareRows=$(this).attr("minSpareRows");
            if(minSpareRows!="") minSpareRows=eval(minSpareRows);
            else minSpareRows=0;
            var contextMenu=$(this).attr("contextMenu");
            if(contextMenu!="") contextMenu=eval(contextMenu);
            else contextMenu=true;
            var rowHeaderWidth=$(this).attr("rowHeaderWidth");
            if(rowHeaderWidth!="") rowHeaderWidth=eval(rowHeaderWidth);
            else rowHeaderWidth=undefined;
            var colWidths=$(this).attr("colWidths");
            if(colWidths!="") colWidths=eval(colWidths);
            else colWidths=undefined;
            var input=$(this).prev();
            $(this).handsontable({
                data:data,
                rowHeaders:rowHeaders,
                colHeaders:colHeaders,
                minSpareRows:minSpareRows,
                contextMenu:contextMenu,
                rowHeaderWidth:rowHeaderWidth,
                colWidths:colWidths,
                afterChange:function(changes,source) {
                    $(input).val(btoa(utf8_encode(JSON.stringify(data))));
                }
            });
        });
        //~ console.timeEnd("make_ckeditors");
    }

    function unmake_ckeditors(obj) {
        //~ console.time("unmake_ckeditors");
        if(typeof(obj)=="undefined") var obj=$(".ui-layout-center");
        // REMOVE THE CKEDITORS (IMPORTANT THING!!!)
        $("textarea[ckeditor=true]",obj).each(function() {
            var name=$(this).attr("name");
            if(CKEDITOR.instances[name]) CKEDITOR.instances[name].destroy();
        });
        //~ console.timeEnd("unmake_ckeditors");
    }

    function make_tooltips() {
        //~ console.time("make_tooltips");
        $(document).tooltip({
            items:"[title][title!=''],[title2][title2!='']",
            show:false,
            hide:false,
            classes: {
                "ui-tooltip":"ui-state-highlight"
            },
            //~ track:true,
            open:function(event,ui) {
                ui.tooltip.css("max-width",$(window).width()/2);
                var color=get_colors("ui-state-highlight","border-bottom-color");
                ui.tooltip.css("border-color",color);
            },
            content:function() {
                // GET THE TITLE VALUE
                var title=trim($(this).attr("title"));
                // CHECK FOR A DATEPICKER ISSUE
                if($(this).parent().parent().parent().hasClass("ui-datepicker")) {
                    title=str_replace(["<",">"],["&lt;","&gt;"],title);
                    return title;
                }
                // CONTINUE
                if(title) {
                    // CHECK IF TITLE IS THE SAME THAT THE OBJECT TEXT
                    var text1=trim($(this).text());
                    var text2=trim($(":not(:visible)",this).text());
                    var text3=trim(str_replace(text2,"",text1));
                    if(title==text3) title="";
                    // FIX SOME ISSUES
                    if(strpos(title,"<")!==false || strpos(title,">")!==false) {
                        title=str_replace(["<",">"],["&lt;","&gt;"],title);
                    }
                    // MOVE DATA FROM TITLE TO TITLE2
                    $(this).removeAttr("title");
                    $(this).attr("title2",title);
                } else {
                    title=$(this).attr("title2");
                }
                // CHECK IF OBJECT IS DISABLED
                if($(this).hasClass("ui-state-disabled")) {
                    title="";
                }
                // CREATE THE TOOLTIP
                return title;
            }
        });
        //~ console.timeEnd("make_tooltips");
    }

    function hide_tooltips() {
        //~ console.time("hide_tooltips");
        $(".ui-tooltip").remove();
        //~ console.timeEnd("hide_tooltips");
    }

    var make_focus_obj=null;

    function make_focus() {
        //~ console.time("make_focus");
        // FOCUS THE OBJECT WITH FOCUSED ATTRIBUTE
        if(make_focus_obj) $(make_focus_obj).trigger("focus");
        make_focus_obj=null;
        //~ console.timeEnd("make_focus");
    }

    function unmake_focus() {
        $("html").focus();
    }

    var make_tables_pos=-1;

    function make_tables(obj) {
        //~ console.time("make_tables");
        if(typeof(obj)=="undefined") var obj=$(".ui-layout-center");
        // SUPPORT FOR LTR AND RTL LANGS
        var dir=$("html").attr("dir");
        var rtl={
            "ltr":{"ui-corner-tl":"ui-corner-tl","ui-corner-tr":"ui-corner-tr","ui-corner-bl":"ui-corner-bl","ui-corner-br":"ui-corner-br"},
            "rtl":{"ui-corner-tl":"ui-corner-tr","ui-corner-tr":"ui-corner-tl","ui-corner-bl":"ui-corner-br","ui-corner-br":"ui-corner-bl"}
        };
        // GET ALL TABLES OF THE TABLA CLASS
        $(".tabla",obj).each(function() {
            if($(".thead",this).length>0) {
                // FIXS FOR POSIBLE NEXT RECALLS
                $("td",this).removeClass("ui-corner-tl ui-corner-tr ui-corner-bl ui-corner-br ui-widget-header ui-widget-content ui-state-default ui-state-highlight notop");
                // STYLING THE THEAD AND NODATA
                $(".thead",this).addClass("ui-widget-header");
                $(".nodata",this).addClass("ui-widget-content");
                // SOME VARIABLES
                var trs=$("tr",this);
                var tdshead=null;
                var tdsbody=null;
                var trimpar=0;
                $(trs).each(function() {
                    if($(this).hasClass("none")) return;
                    // MORE VARIABLES
                    var numhead=$(".thead",this).length;
                    var numbody=$(".tbody",this).length;
                    var numdata=$(".nodata",this).length;
                    var numcell=$(".cell",this).length;
                    // STYLING THE ROUNDED CORNERS AND BORDERS OF THE CELLS
                    if(tdshead==null && numhead>0) {
                        tdshead=this;
                        tdsbody=this;
                    } else if(tdshead!=null && numhead+numbody+numdata>0) {
                        tdsbody=this;
                        var tds=$("td",this);
                        $(tds).each(function() {
                            $(this).addClass("notop")
                        });
                    } else if(tdshead!=null) {
                        var tds=$("td",tdshead);
                        var total=$(tds).length;
                        var count=1;
                        $(tds).each(function() {
                            if(count==1) $(this).addClass(rtl[dir]["ui-corner-tl"]);
                            if(count==total) $(this).addClass(rtl[dir]["ui-corner-tr"]);
                            count++;
                        });
                        tdshead=null;
                        var tds=$("td",tdsbody);
                        var total=$(tds).length;
                        var count=1;
                        $(tds).each(function() {
                            if(count==1) $(this).addClass(rtl[dir]["ui-corner-bl"]);
                            if(count==total) $(this).addClass(rtl[dir]["ui-corner-br"]);
                            count++;
                        });
                        tdsbody=null;
                    }
                    // ADD THE TIMPAR CLASS TO THE CELLS THAT CONTAIN THE TBODY BY STEPS OF 2
                    if(numbody>0) {
                        trimpar=(trimpar+1)%2;
                        var clase=trimpar?"ui-widget-content":"ui-state-default";
                        $(".tbody",this).addClass(clase);
                    } else if(numhead>0) {
                        trimpar=0;
                    }
                    // PROGRAM THE HIGHLIGHT EFFECT FOR EACH ROW
                    if(numbody>0 && numcell==0) {
                        var slave="input.slave[type=checkbox]";
                        $(this).on("mouseover",function() {
                            var value=$(slave,this).prop("checked");
                            if(!value) {
                                var color=$(".tbody:first",this).css("border-bottom-color");
                                $(".tbody",this).addClass("ui-state-highlight").css("border-color",color);
                            }
                        }).on("mouseout",function() {
                            var value=$(slave,this).prop("checked");
                            if(!value) $(".tbody",this).removeClass("ui-state-highlight");
                        }).on("click",function(event) {
                            var checkbox=$(slave,this);
                            var value=$(checkbox).prop("checked");
                            $(checkbox).prop("checked",!value);
                            if(!value) {
                                var color=$(".tbody:first",this).css("border-bottom-color");
                                $(".tbody",this).addClass("ui-state-highlight").css("border-color",color);
                            }
                            if(value) $(".tbody",this).removeClass("ui-state-highlight");
                            // CHECK FOR MULTIPLE SELECTION
                            var count=0;
                            var pos=-1;
                            $(this).parent().find(slave).each(function() {
                                if(this==checkbox[0]) pos=count;
                                count++;
                            });
                            if(event.ctrlKey) {
                                var count=0;
                                var from=min(make_tables_pos,pos);
                                var to=max(make_tables_pos,pos);
                                $(this).parent().find(slave).each(function() {
                                    if(count>=from && count<=to) {
                                        $(this).prop("checked",true);
                                        var color=$(this).parent().parent().find(".tbody:first").css("border-bottom-color");
                                        $(this).parent().parent().find(".tbody").addClass("ui-state-highlight").css("border-color",color);
                                    }
                                    count++;
                                });
                            }
                            make_tables_pos=pos;
                        });
                        $(slave,this).on("click",function() {
                            $(this).prop("checked",!$(this).prop("checked"));
                        });
                        $("a",this).on("click",function() {
                            var checkbox=$(slave,$(this).parent().parent());
                            var value=$(checkbox).prop("checked");
                            if(value) $(checkbox).prop("checked",!value);
                        });
                    }
                });
                if(tdshead!=null) {
                    var tds=$("td",tdshead);
                    var total=$(tds).length;
                    var count=1;
                    $(tds).each(function() {
                        if(count==1) $(this).addClass(rtl[dir]["ui-corner-tl"]);
                        if(count==total) $(this).addClass(rtl[dir]["ui-corner-tr"]);
                        count++;
                    });
                    var tds=$("td",tdsbody);
                    var total=$(tds).length;
                    var count=1;
                    $(tds).each(function() {
                        if(count==1) $(this).addClass(rtl[dir]["ui-corner-bl"]);
                        if(count==total) $(this).addClass(rtl[dir]["ui-corner-br"]);
                        count++;
                    });
                }
                // MAKE CALCS OF THE TABLE CELLS
                var last=$("tr.math",this);
                $("td",last).each(function() {
                    var index=$(this).index();
                    var value=$(this).text();
                    if(in_array(value,["=sum()","=count()","=avg()"])) {
                        var sum=0;
                        var count=0;
                        $("td:eq("+index+")",trs).each(function() {
                            var value=$(this).text();
                            if(!isNaN(value)) {
                                sum+=floatval(value);
                                count++;
                            }
                        });
                        if(value=="=sum()") {
                            $(this).html(round(sum,2));
                        } else if(value=="=count()") {
                            $(this).html(count);
                        } else if(value=="=avg()") {
                            var average=(count>0)?sum/count:0;
                            $(this).html(round(average,2));
                        }
                    }
                });
            }
        });
        //~ console.timeEnd("make_tables");
    }

    function make_contextmenu() {
        //~ console.time("make_contextmenu");
        $("body").append("<ul id='contextMenu' class='ui-corner-all'></ul>");
        $("#contextMenu").menu().hide();
        $(document).on("keydown",function(event) {
            if(is_escapekey(event)) hide_contextmenu();
        }).on("click",function(event) {
            if(event.button!=2) hide_contextmenu();
        }).on("contextmenu",function(event) {
            hide_tooltips();
            hide_contextmenu();
            // CANCEL EVENTS
            if(event.ctrlKey) return true;
            // FOR CANCEL IN JSTREE
            if($(event.target).is("li.jstree-node")) return false;
            if($(event.target).is("a.jstree-anchor")) return false;
            if($(event.target).is("i.jstree-icon")) return false;
            // FOR CANCEL IN MENU
            if($(event.target).is("div.ui-accordion-content")) return false;
            if($(event.target).is("h3.ui-accordion-header")) return false;
            // FOR CANCEL IN THEAD
            if($(event.target).is("td.ui-widget-header")) return false;
            if($(event.target).is("span.ui-icon")) return false;
            // FOR CANCEL IN BUTTONS
            if($(event.target).is("a.ui-state-default")) return false;
            if($(event.target).parent().is("a.ui-state-default")) return false;
            // FOR CANCEL IN TEXTBOX
            if($(event.target).is("input.ui-state-default")) return false;
            // FOR CANCEL IN CHECKBOX AND LABELS
            if($(event.target).is("input[type=checkbox]")) return false;
            if($(event.target).is("label[for]")) return false;
            // FOR CANCEL IN SELECTS
            if($(event.target).is("select.ui-state-default")) return false;
            // FOR CANCEL IN DIALOG
            if(dialog("isopen")) return false;
            // FOR CANCEL IN TABS
            if($(event.target).is("a.ui-tabs-anchor")) return false;
            if($(event.target).parent().is("a.ui-tabs-anchor")) return false;
            // GET AND CLEAR OBJECT
            var obj=$("#contextMenu");
            $("li",obj).remove();
            // PREPARE OPTIONS
            var parent=$(event.target); // BEGIN FIX PART
            if($(parent).is("span")) parent=$(parent).parent(); // TO FIX WHEN EVENT IS TRIGGERED FROM A SPAN
            if($(parent).is("a")) parent=$(parent).parent(); // TO FIX WHEN EVENT IS TRIGGERED FROM A LINK
            parent=$(parent).parent(); // END FIX PART
            var trs=$("tr",parent); // THIS IS USED TO DETECT A SPECIAL CASE
            var tds=$("td.actions1",parent); // GET THE LIST OF ENTRIES
            if($(trs).length || !$(tds).length) tds=$(".contextmenu");
            // ADD OPTIONS
            var hashes=[];
            $(tds).each(function() {
                var onclick=$(this).attr("onclick");
                if(!onclick) onclick=$("a",this).attr("onclick");
                var extra1=$("span",this).attr("class");
                extra1=str_replace("ui-state-disabled","",extra1);
                var texto=trim($(this).text());
                if(!texto) texto=$(this).attr("labeled");
                if(!texto) texto=$(this).attr("title");
                if(!texto) texto=$("a",this).attr("labeled");
                if(!texto) texto=$("a",this).attr("title");
                if(!texto) texto=$("span",this).attr("labeled");
                if(!texto) texto=$("span",this).attr("title");
                var disabled=$(this).hasClass("ui-state-disabled");
                if(!disabled) disabled=$("a",this).hasClass("ui-state-disabled");
                if(!disabled) disabled=$("span",this).hasClass("ui-state-disabled");
                var extra2=disabled?"ui-state-disabled":"";
                var html="<li class='"+extra2+"'><div><span class='"+extra1+"'></span>&nbsp;"+texto+"<div></li>";
                var hash=md5(html);
                if(!in_array(hash,hashes)) {
                    $(obj).append(html);
                    $("li:last",obj).on("click",function() { eval(onclick); });
                    hashes.push(hash);
                }
            });
            // PLACE POPUP
            $(obj).css("position","absolute");
            if(typeof(event.pageX)!="undefined") {
                if(event.pageX<$(window).width()*0.66) {
                    $(obj).css("left",event.pageX);
                    $(obj).css("right","auto");
                    $(obj).css("top",event.pageY);
                } else {
                    $(obj).css("left","auto");
                    $(obj).css("right",$(window).width()-event.pageX);
                    $(obj).css("top",event.pageY);
                }
            }
            // OPEN POPUP
            $(obj).show();
            $(obj).menu("refresh");
            return false;
        }).on("click",".actions2",function(event) {
            event.stopPropagation();
            var obj=$("#contextMenu");
            $(obj).css("left","auto");
            $(obj).css("right",$(window).width()-event.pageX);
            $(obj).css("top",event.pageY);
            $(this).trigger("contextmenu");
        });
        //~ console.timeEnd("make_contextmenu");
    }

    function hide_contextmenu() {
        $("#contextMenu").hide();
    }

    var cache_colors={};

    function get_colors(clase,param) {
        if(typeof(clase)=="undefined" && typeof(param)=="undefined") {
            for(var hash in cache_colors) delete cache_colors[hash];
            return;
        }
        hash=md5(JSON.stringify([clase,param]));
        if(typeof(cache_colors[hash])=="undefined") {
            // GET THE COLORS USING THIS TRICK
            if($("#ui-color-trick").length==0) {
                $("body").append("<div id='ui-color-trick'></div>");
            }
            $("#ui-color-trick").addClass(clase);
            cache_colors[hash]=$("#ui-color-trick").css(param);
            $("#ui-color-trick").removeClass(clase);
        }
        return cache_colors[hash];
    }

    function rgb2hex(color) {
        if(strncasecmp(color,"rgba",4)==0) {
            var temp=color.split(/([\(,\)])/);
            if(temp.length==11) color=sprintf("%02x%02x%02x",temp[2],temp[4],temp[6]);
        } else if(strncasecmp(color,"rgb",3)==0) {
            var temp=color.split(/([\(,\)])/);
            if(temp.length==9) color=sprintf("%02x%02x%02x",temp[2],temp[4],temp[6]);
        }
        return color;
    }

    function make_shortcuts() {
        var codes={"backspace":8, "tab":9, "enter":13, "pauseBreak":19, "capsLock":20, "escape":27, "space":32, "pageUp":33, "pageDown":34, "end":35, "home":36, "leftArrow":37, "upArrow":38, "rightArrow":39, "downArrow":40, "insert":45, "delete":46, "0":48, "1":49, "2":50, "3":51, "4":52, "5":53, "6":54, "7":55, "8":56, "9":57, "a":65, "b":66, "c":67, "d":68, "e":69, "f":70, "g":71, "h":72, "i":73, "j":74, "k":75, "l":76, "m":77, "n":78, "o":79, "p":80, "q":81, "r":82, "s":83, "t":84, "u":85, "v":86, "w":87, "x":88, "y":89, "z":90, "leftWindowKey":91, "rightWindowKey":92, "selectKey":93, "numpad0":96, "numpad1":97, "numpad2":98, "numpad3":99, "numpad4":100, "numpad5":101, "numpad6":102, "numpad7":103, "numpad8":104, "numpad9":105, "multiply":106, "add":107, "subtract":109, "decimalPoint":110, "divide":111, "f1":112, "f2":113, "f3":114, "f4":115, "f5":116, "f6":117, "f7":118, "f8":119, "f9":120, "f10":121, "f11":122, "f12":123, "numLock":144, "scrollLock":145, "semiColon":186, "equalSign":187, "comma":188, "dash":189, "period":190, "forwardSlash":191, "graveAccent":192, "openBracket":219, "backSlash":220, "closeBraket":221, "singleQuote":222};
        $(document).on("keydown",function(event) {
            if(!isloadingcontent()) {
                var exists=false;
                $("[class*=shortcut_]").each(function() {
                    var param=get_class_key_val(this,"shortcut_");
                    var temp=explode("_",param);
                    var useAlt=false;
                    var useCtrl=false;
                    var useShift=false;
                    var key=null;
                    for(var i=0,len=temp.length;i<len;i++) {
                        if(temp[i]=="alt") useAlt=true;
                        else if(temp[i]=="ctrl") useCtrl=true;
                        else if(temp[i]=="shift") useShift=true;
                        else key=codes[temp[i]];
                    }
                    var count=0;
                    if(useAlt && event.altKey) count++;
                    if(!useAlt && !event.altKey) count++;
                    if(useCtrl && event.ctrlKey) count++;
                    if(!useCtrl && !event.ctrlKey) count++;
                    if(useShift && event.shiftKey) count++;
                    if(!useShift && !event.shiftKey) count++;
                    if(key==get_keycode(event)) count++;
                    if(count==4) {
                        if($(this).is("a,tr,td")) $(this).trigger("click");
                        if($(this).is("input,select,textarea")) $(this).trigger("focus");
                        exists=true;
                    }
                });
                if(exists) return false;
            }
        });
    }

    var make_abort_obj=null;

    function make_abort() {
        $(document).on("keydown",function(event) {
            if(is_escapekey(event) && make_abort_obj) {
                make_abort_obj.abort();
                make_abort_obj=null;
            }
        });
    }

    function saltos_islogin(obj) {
        if(typeof(obj)=="undefined") var obj=$(".ui-layout-west");
        var islogin=($(obj).text()!="")?1:0;
        return islogin;
    }

    function make_back2top() {
        $(window).scroll(function() {
            if($(this).scrollTop()>300) {
                $(".back2top").show();
            } else {
                $(".back2top").hide();
            }
        });
        $(".back2top").on("click",function(event) {
            event.preventDefault();
            $("html,body").animate({ scrollTop:0 },"fast");
            return false;
        })
    }

    function make_resizable(obj) {
        if(typeof(obj)=="undefined") var obj=$(".ui-layout-west");
        var width=parseInt(getIntCookie("saltos_ui_menu_width")/10)*10;
        if(!width) width=200;
        $(obj).width(width).resizable({
            minWidth:100,
            maxWidth:400,
            grid:10,
            handles:"e",
            resize:function(event,ui) {
                setIntCookie("saltos_ui_menu_width",ui.size.width);
                $(".back2top").css("left",(ui.size.width-54)+"px");
            },
        });
        $(".back2top").css("left",(width-54)+"px");
    }

    // TO PREVENT JQUERY THE ADD _=[TIMESTAMP] FEATURE
    $.ajaxSetup({ cache:true });

    // DEFINE SOME DEFAULTS THAT CAN NOT BE DEFINED IN RUNTIME
    $.jGrowl.defaults.closer=false;
    $.jGrowl.defaults.position="bottom-right";

    // WHEN DOCUMENT IS READY
    $(function() {
        //~ console.time("document_ready fase 0");
        loadingcontent();
        var header=$(".ui-layout-north");
        var menu=$(".ui-layout-west");
        $(header).attr("hash",md5($(header).html()));
        $(menu).attr("hash",md5($(menu).html()));
        setTimeout(function() {
            //~ console.time("document_ready fase 1");
            init_history();
            make_notice();
            make_dialog();
            make_contextmenu();
            make_shortcuts();
            make_abort();
            make_tooltips();
            make_hovers();
            var header=$(".ui-layout-north");
            make_tabs2(header);
            setTimeout(function() {
                //~ console.time("document_ready fase 2 north");
                make_draganddrop(header);
                //~ console.timeEnd("document_ready fase 2 north");
            },100);
            var menu=$(".ui-layout-west");
            if(saltos_islogin(menu)) sync_cookies("start");
            make_resizable(menu);
            make_menu(menu);
            setTimeout(function() {
                //~ console.time("document_ready fase 2 west");
                make_draganddrop(menu);
                //~ console.timeEnd("document_ready fase 2 west");
            },100);
            var screen=$(".ui-layout-center");
            make_tabs(screen);
            make_tables(screen);
            make_extras(screen);
            $("body > *").removeClass("none");
            make_ckeditors(screen);
            setTimeout(function() {
                //~ console.time("document_ready fase 2 center");
                make_draganddrop(screen);
                make_focus();
                //~ console.timeEnd("document_ready fase 2 center");
            },100);
            unloadingcontent();
            make_back2top();
            bold_menu();
            //~ console.timeEnd("document_ready fase 1");
        },100);
        //~ console.timeEnd("document_ready fase 0");
    });

}
