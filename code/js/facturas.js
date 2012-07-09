/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2012 by Josep Sanz Campderrós
More information in http://www.saltos.net or info@saltos.net

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

if(typeof(__facturas__)=="undefined" && typeof(parent.__facturas__)=="undefined") {
	"use strict";
	var __facturas__=1;

	function update_totales_factura() {
		// sumas totales y parciales
		var iva=0;
		var irpf=0;
		var base=0;
		for(var i=0,len=document.form.elements.length;i<len;i++) {
			obj=document.form.elements[i];
			if(obj.type) {
				if(obj.name.substr(obj.name.length-6,6)=="precio") {
					var prefix=obj.name.substr(0,obj.name.length-6);
					var unid_obj=eval("document.form."+prefix+"unidades");
					var desc_obj=eval("document.form."+prefix+"descuento");
					var total_obj=eval("document.form."+prefix+"total2");
					var unidades=round(floatval(unid_obj.value),2);
					var descuento=round(floatval(desc_obj.value),2);
					var precio=round(floatval(obj.value),2);
					var total=round(floatval(unidades*precio*(100-descuento)/100),2);
					var del_obj=eval("document.form."+prefix+"delete");
					if(del_obj) if(del_obj.checked) total=0;
					total_obj.value=total;
					base=base+total;
				} else if(obj.name.substr(obj.name.length-3,3)=="iva") {
					iva=round(floatval(obj.value),2);
				} else if(obj.name.substr(obj.name.length-4,4)=="irpf") {
					irpf=round(floatval(obj.value),2);
				}
			}
		}
		var iva2=round(floatval(base*iva/100),2);
		var irpf2=round(floatval(base*irpf/100),2);
		var total=round(floatval(base+iva2-irpf2),2);
		for(var i=0,len=document.form.elements.length;i<len;i++) {
			obj=document.form.elements[i];
			if(obj.type) {
				if(obj.name.substr(obj.name.length-4,4)=="base") {
					obj.value=base;
				} else if(obj.name.substr(obj.name.length-4,4)=="iva2") {
					obj.value=iva2;
				} else if(obj.name.substr(obj.name.length-5,5)=="irpf2") {
					obj.value=irpf2;
				} else if(obj.name.substr(obj.name.length-5,5)=="total") {
					obj.value=total;
				}
			}
		}
		update_vencimientos(null);
	}

	function update_iva_irpf(overwrite) {
		if(!document.form) return;
		for(var i=0,len=document.form.elements.length;i<len;i++) {
			obj=document.form.elements[i];
			if(obj.type) {
				if(obj.name.substr(obj.name.length-9,9)=="id_cuenta") {
					var temp=obj.value;
				}
			}
		}
		if(typeof(temp)=="undefined") return;
		if(temp=="0") temp="0_1_0_1_0";
		temp=temp.split("_");
		var iva_bool=temp[1];
		var iva_value=temp[2];
		var irpf_bool=temp[3];
		var irpf_value=temp[4];
		for(var i=0,len=document.form.elements.length;i<len;i++) {
			obj=document.form.elements[i];
			if(obj.type) {
				if(obj.name.substr(obj.name.length-3,3)=="iva") {
					if(iva_bool==1) {
						if(temp[0]!=0 && overwrite) obj.value=iva_value;
						obj.disabled=false;
						$(obj).removeClass("ui-state-disabled");
					} else {
						obj.value=0;
						obj.disabled=true;
						$(obj).addClass("ui-state-disabled");
					}
				} else if(obj.name.substr(obj.name.length-4,4)=="irpf") {
					if(irpf_bool==1) {
						if(temp[0]!=0 && overwrite) obj.value=irpf_value;
						obj.disabled=false;
						$(obj).removeClass("ui-state-disabled");
					} else {
						obj.value=0;
						obj.disabled=true;
						$(obj).addClass("ui-state-disabled");
					}
				}
			}
		}
		update_totales_factura();
	}

	function update_datos() {
		var cliente=$("select[name$=id_cliente]");
		$("input[name$=nombre]").val("");
		$("input[name$=cif]").val("");
		$("input[name$=direccion]").val("");
		$("input[name$=id_pais]").val("");
		$("input[name$=id_provincia]").val("");
		$("input[name$=id_poblacion]").val("");
		$("input[name$=id_codpostal]").val("");
		$("input[name$=nombre_pais]").val("");
		$("input[name$=nombre_provincia]").val("");
		$("input[name$=nombre_poblacion]").val("");
		$("input[name$=nombre_codpostal]").val("");
		var data="action=ajax&query=cliente&id_cliente="+$(cliente).val();
		$.ajax({
			url:"xml.php",
			data:data,
			type:"get",
			success:function(response) {
				$("root>rows>row",response).each(function() {
					if($("id_tipo",this).text()=="1") $("input[name$=nombre]").val($("nombre2",this).text());
					if($("id_tipo",this).text()=="2") $("input[name$=nombre]").val($("nombre1",this).text()+" "+$("nombre2",this).text());
					$("input[name$=cif]").val($("cif",this).text());
					$("input[name$=direccion]").val($("direccion",this).text());
					$("input[name$=id_pais]").val($("id_pais",this).text());
					$("input[name$=id_provincia]").val($("id_provincia",this).text());
					$("input[name$=id_poblacion]").val($("id_poblacion",this).text());
					$("input[name$=id_codpostal]").val($("id_codpostal",this).text());
					$("input[name$=nombre_pais]").val($("nombre_pais",this).text());
					$("input[name$=nombre_provincia]").val($("nombre_provincia",this).text());
					$("input[name$=nombre_poblacion]").val($("nombre_poblacion",this).text());
					$("input[name$=nombre_codpostal]").val($("nombre_codpostal",this).text());
				});
			}
		});
	}

}

"use strict";
$(document).ready(function() { update_iva_irpf(false); });
