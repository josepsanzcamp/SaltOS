/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2021 by Josep Sanz Campderrós
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

if(typeof(__filters__)=="undefined" && typeof(parent.__filters__)=="undefined") {
    "use strict";
    var __filters__=1;

    function load_filter() {
        var filtro=$("#id_filter");
        if($(filtro).val()=="") {
            alerta(lang_loadfilterko(),function() { $(filtro).trigger("focus"); });
        } else {
            loadingcontent();
            var id_filter=$(filtro).val();
            var data="action=ajax&query=loadfilter&page="+getParam("page")+"&id="+id_filter;
            $.ajax({
                url:"index.php",
                data:data,
                type:"get",
                success:function(response) {
                    // UNCKECK ALL CHECKBOXES
                    $("form[id=list] *[type=checkbox]").each(function() {
                        setCheck(this.name,false);
                    });
                    // PROCESS RESPONSE
                    $(response["rows"]).each(function() {
                        var querystring=this["querystring"];
                        querystring=utf8_decode(atob(querystring));
                        querystring=explode("&",querystring);
                        var count=0;
                        var interval=setInterval(function() {
                            var temp=explode("=",querystring[count],2);
                            temp[1]=decodeURIComponent(temp[1]); // VALUE
                            var type=$("form[id=list] *[name="+temp[0]+"]").prop("type"); // TYPE
                            if(type=="checkbox") setCheck(temp[0],temp[1]?true:false); // CHECKBOX FIELD
                            if(type!="checkbox") setParam(temp[0],temp[1]); // OTHER FIELD
                            if(substr(type,0,6)=="select") $("form[id=list] *[name="+temp[0]+"]").attr("original",temp[1]);
                            $("form[id=list] *[name="+temp[0]+"]").trigger("change");
                            count++;
                            if(count>=querystring.length) {
                                clearInterval(interval);
                                $(filtro).val("");
                                notice(lang_alert(),lang_loadfilterok());
                                unloadingcontent();
                                buscar();
                            }
                        },100);
                    });
                },
                error:function(XMLHttpRequest,textStatus,errorThrown) {
                    errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
                }
            });
        }
    }

    function update_filter() {
        var filtro=$("#id_filter");
        if($(filtro).val()=="") {
            alerta(lang_updatefilterko(),function() { $(filtro).trigger("focus"); });
        } else {
            loadingcontent(lang_sending());
            var id_filter=$(filtro).val();
            var querystring=encodeURIComponent(btoa(utf8_encode(querystring_filter())));
            var data="action=ajax&query=updatefilter&page="+getParam("page")+"&id="+id_filter+"&querystring="+querystring;
            $.ajax({
                url:"index.php",
                data:data,
                type:"post",
                success:function(response) {
                    $(filtro).val("");
                    notice(lang_alert(),lang_updatefilterok());
                    unloadingcontent();
                    // TRICK TO FORCE A MENU RELOAD
                    $(".ui-layout-west .menu:last").append("-");
                    buscar();
                },
                error:function(XMLHttpRequest,textStatus,errorThrown) {
                    errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
                }
            });
        }
    }

    function delete_filter() {
        var filtro=$("#id_filter");
        if($(filtro).val()=="") {
            alerta(lang_deletefilterko(),function() { $(filtro).trigger("focus"); });
        } else {
            loadingcontent(lang_sending());
            var id_filter=$(filtro).val();
            var data="action=ajax&query=deletefilter&page="+getParam("page")+"&id="+id_filter;
            $.ajax({
                url:"index.php",
                data:data,
                type:"get",
                success:function(response) {
                    $(filtro).val("");
                    notice(lang_alert(),lang_deletefilterok());
                    unloadingcontent();
                    buscar();
                },
                error:function(XMLHttpRequest,textStatus,errorThrown) {
                    errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
                }
            });
        }
    }

    function create_filter() {
        var filtro=$("#newfilter");
        if($(filtro).val()=="") {
            alerta(lang_createfilterko(),function() { $(filtro).trigger("focus"); });
        } else {
            loadingcontent(lang_sending());
            var nombre=encodeURIComponent($(filtro).val());
            var querystring=encodeURIComponent(btoa(utf8_encode(querystring_filter())));
            var data="action=ajax&query=createfilter&page="+getParam("page")+"&nombre="+nombre+"&querystring="+querystring;
            $.ajax({
                url:"index.php",
                data:data,
                type:"post",
                success:function(response) {
                    $(filtro).val("");
                    notice(lang_alert(),lang_createfilterok());
                    unloadingcontent();
                    buscar();
                },
                error:function(XMLHttpRequest,textStatus,errorThrown) {
                    errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
                }
            });
        }
    }

    function rename_filter() {
        var filtro1=$("#id_filter");
        var filtro2=$("#newfilter");
        if($(filtro1).val()=="") {
            alerta(lang_renamefilterko1(),function() { $(filtro1).trigger("focus"); });
        } else if($(filtro2).val()=="") {
            alerta(lang_renamefilterko2(),function() { $(filtro2).trigger("focus"); });
        } else {
            loadingcontent(lang_sending());
            var id_filter=$(filtro1).val();
            var nombre=encodeURIComponent($(filtro2).val());
            var data="action=ajax&query=renamefilter&page="+getParam("page")+"&id="+id_filter+"&nombre="+nombre;
            $.ajax({
                url:"index.php",
                data:data,
                type:"post",
                success:function(response) {
                    $(filtro1).val("");
                    $(filtro2).val("");
                    notice(lang_alert(),lang_renamefilterok());
                    unloadingcontent();
                    buscar();
                },
                error:function(XMLHttpRequest,textStatus,errorThrown) {
                    errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
                }
            });
        }
    }

    function querystring_filter() {
        var querystring=[];
        var array=$("form[id=list] *:not([class~=nofilter])").serializeArray();
        $(array).each(function(i,field) {
            var temp=field.name+"="+encodeURIComponent(field.value);
            querystring.push(temp);
        });
        querystring=implode("&",querystring);
        return querystring;
    }
}
