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

if(typeof(__gastos__)=="undefined" && typeof(parent.__gastos__)=="undefined") {
    "use strict";
    var __gastos__=1;

    function update_totales_gasto() {
        for(var i=0,len=document.form.elements.length;i<len;i++) {
            obj=document.form.elements[i];
            if(obj.type) {
                if(obj.name.substr(obj.name.length-4,4)=="base") {
                    var prefix=obj.name.substr(0,obj.name.length-4);
                    var iva_obj=eval("document.form."+prefix+"iva");
                    var irpf_obj=eval("document.form."+prefix+"irpf");
                    var total_obj=eval("document.form."+prefix+"total");
                    var iva=round(floatval(iva_obj.value),2);
                    var irpf=round(floatval(irpf_obj.value),2);
                    var total=round(floatval(total_obj.value),2);
                    var base=round((100*total)/(100+iva-irpf),2);
                    iva_obj.value=iva;
                    irpf_obj.value=irpf;
                    total_obj.value=total;
                    obj.value=base;
                }
            }
        }
        update_vencimientos(null);
    }

    function search_proveedor() {
        var buscador=$("input[type=text][name$=buscador]");
        var proveedor=$("select[name$=id_proveedor]");
        if(proveedores_defaults=="") proveedores_defaults=$(proveedor).html();
        var data="action=ajax&query=proveedores&filtro="+encodeURIComponent(buscador.val()?buscador.val():"");
        $.ajax({
            url:"index.php",
            data:data,
            type:"get",
            success:function(response) {
                var options=proveedores_defaults;
                var original=$(proveedor).attr("original");
                var contador=response["rows"].length;
                $(response["rows"]).each(function() {
                    var selected=(this["id"]==original || contador==1)?"selected='selected'":"";
                    options+="<option value='"+this["id"]+"' "+selected+">"+this["nombre"]+"</option>";
                });
                $(proveedor).html(options);
            },
            error:function(XMLHttpRequest,textStatus,errorThrown) {
                errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
            }
        });
    }

}

"use strict";
var proveedores_defaults="";

$(function() { search_proveedor(); });
