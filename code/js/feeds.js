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

if(typeof(__feeds__)=="undefined" && typeof(parent.__feeds__)=="undefined") {
    "use strict";
    var __feeds__=1;

    function update_state2(id,type,value) {
        if(!id) return;
        var data="action=ajax&query=state2&id="+id+"&type="+type+"&value="+value;
        $.ajax({
            url:"index.php",
            data:data,
            type:"get",
            success:function(response) {
                if(type=="new") {
                    if(value==1) notice(lang_alert(),lang_msgnumnoleidos());
                    if(value==0) notice(lang_alert(),lang_msgnumsileidos());
                }
                if(type=="wait") {
                    if(value==1) notice(lang_alert(),lang_msgnumsiwait());
                    if(value==0) notice(lang_alert(),lang_msgnumnowait());
                }
                if(type=="cool") {
                    if(value==1) notice(lang_alert(),lang_msgnumsicool());
                    if(value==0) notice(lang_alert(),lang_msgnumnocool());
                }
            },
            error:function(XMLHttpRequest,textStatus,errorThrown) {
                errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
            }
        });
    }

    function feed2bookmark(id) {
        if(!id) return;
        var data="action=ajax&query=feed2bookmark&id="+id;
        $.ajax({
            url:"index.php",
            data:data,
            type:"get",
            success:function(response) {
                var data='action=favoritos&url='+encodeURIComponent(response["rows"][0]["link"]);
                $.ajax({
                    url:'',
                    data:data,
                    type:"post",
                    success:function(response) {
                        $(".ui-layout-center").append(response);
                    },
                    error:function(XMLHttpRequest,textStatus,errorThrown) {
                        errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
                    }
                });
            },
            error:function(XMLHttpRequest,textStatus,errorThrown) {
                errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
            }
        });
    }

    function update_checkbox2() {
        if(getParam("action")!="form") return;
        var checkbox="input[name="+$("input[name$=state_new]").attr("name")+"]";
        if($(checkbox).prop("checked")) {
            var interval=setInterval(function() {
                if(!$(checkbox).length) {
                    clearInterval(interval);
                } else if($(checkbox).prop("writed")) {
                    clearInterval(interval);
                } else {
                    $(checkbox).prop("checked",!$(checkbox).prop("checked"));
                }
            },1000);
        }
    }

}

"use strict";
$(function() {
    update_checkbox2();
});
