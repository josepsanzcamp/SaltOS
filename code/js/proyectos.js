/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2015 by Josep Sanz Campderrós
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

if(typeof(__proyectos__)=="undefined" && typeof(parent.__proyectos__)=="undefined") {
	"use strict";
	var __proyectos__=1;

	function update_totales_proyecto() {
		if(!document.form) return;
		var horastotal=0;
		var preciototal=0;
		var horastotal2=0;
		var preciototal2=0;
		for(var i=0,len=document.form.elements.length;i<len;i++) {
			obj=document.form.elements[i];
			if(obj.type) {
				if(obj.name.substr(0,6)=="tareas" && obj.name.substr(obj.name.length-5,5)=="horas") {
					var prefix=obj.name.substr(0,obj.name.length-5);
					var horas_obj=eval("document.form."+prefix+"horas");
					var precio_obj=eval("document.form."+prefix+"precio");
					var descuento_obj=eval("document.form."+prefix+"descuento");
					var total_obj=eval("document.form."+prefix+"total2");
					var horas=floatval(horas_obj.value);
					var precio=floatval(precio_obj.value);
					var descuento=floatval(descuento_obj.value);
					var total=round(floatval(horas*precio*(1-(descuento/100))),2);
					var del_obj=eval("document.form."+prefix+"delete");
					if(del_obj) if(del_obj.checked) {
						horas=0;
						total=0;
					}
					if(total_obj) total_obj.value=total;
					horastotal+=horas;
					preciototal+=total;
					var horas2_obj=eval("document.form."+prefix+"horas2");
					var total2_obj=eval("document.form."+prefix+"total3");
					if(horas2_obj && total2_obj) {
						var horas2=floatval(horas2_obj.value);
						var total2=round(floatval(horas2*precio),2);
						horas2_obj.value=horas2;
						total2_obj.value=total2;
						horastotal2+=horas2;
						preciototal2+=total2;
					}
				}
			}
		}
		horastotal=round(horastotal,2);
		preciototal=round(preciototal,2);
		horastotal2=round(horastotal2,2);
		preciototal2=round(preciototal2,2);
		var preciomedio=horastotal?round(preciototal/horastotal,2):preciototal;
		var preciomedio2=horastotal2?round(preciototal2/horastotal2,2):preciototal2;
		var horastotal3=round((horastotal-horastotal2),2);
		var preciomedio3=round((preciomedio-preciomedio2),2);
		var preciototal3=round((preciototal-preciototal2),2);
		for(var i=0,len=document.form.elements.length;i<len;i++) {
			obj=document.form.elements[i];
			if(obj.type) {
				if(obj.name.substr(obj.name.length-10,10)=="horastotal") {
					obj.value=horastotal;
				} else if(obj.name.substr(obj.name.length-11,11)=="preciomedio") {
					obj.value=preciomedio;
				} else if(obj.name.substr(obj.name.length-11,11)=="preciototal") {
					obj.value=preciototal;
				} else if(obj.name.substr(obj.name.length-11,11)=="horastotal2") {
					obj.value=horastotal2;
				} else if(obj.name.substr(obj.name.length-12,12)=="preciomedio2") {
					obj.value=preciomedio2;
				} else if(obj.name.substr(obj.name.length-12,12)=="preciototal2") {
					obj.value=preciototal2;
				} else if(obj.name.substr(obj.name.length-11,11)=="horastotal3") {
					obj.value=horastotal3;
				} else if(obj.name.substr(obj.name.length-12,12)=="preciomedio3") {
					obj.value=preciomedio3;
				} else if(obj.name.substr(obj.name.length-12,12)=="preciototal3") {
					obj.value=preciototal3;
				}
			}
		}
		var unidadestotal=0;
		var preciototal4=0;
		for(var i=0,len=document.form.elements.length;i<len;i++) {
			obj=document.form.elements[i];
			if(obj.type) {
				if(obj.name.substr(0,8)=="products" && obj.name.substr(obj.name.length-8,8)=="unidades") {
					var prefix=obj.name.substr(0,obj.name.length-8);
					var unidades_obj=eval("document.form."+prefix+"unidades");
					var precio_obj=eval("document.form."+prefix+"precio");
					var descuento_obj=eval("document.form."+prefix+"descuento");
					var total_obj=eval("document.form."+prefix+"total2");
					var unidades=floatval(unidades_obj.value);
					var precio=floatval(precio_obj.value);
					var descuento=floatval(descuento_obj.value);
					var total=round(floatval(unidades*precio*(1-(descuento/100))),2);
					var del_obj=eval("document.form."+prefix+"delete");
					if(del_obj) if(del_obj.checked) {
						unidades=0;
						total=0;
					}
					if(total_obj) total_obj.value=total;
					unidadestotal+=unidades;
					preciototal4+=total;
				}
			}
		}
		unidadestotal=round(unidadestotal,2);
		preciototal4=round(preciototal4,2);
		for(var i=0,len=document.form.elements.length;i<len;i++) {
			obj=document.form.elements[i];
			if(obj.type) {
				if(obj.name.substr(obj.name.length-13,13)=="unidadestotal") {
					obj.value=unidadestotal;
				} else if(obj.name.substr(obj.name.length-12,12)=="preciototal4") {
					obj.value=preciototal4;
				} else if(obj.name.substr(obj.name.length-12,12)=="preciototal5") {
					obj.value=preciototal+preciototal4;
				}
			}
		}

	}

	function options2filter_proyectos() {
		return ""
		+"<br/>"
		+"<br/>"
		+"<table width='100%'>"
		+"<tr>"
		+"<td align='left' valign='top'>"
		+"<input type='checkbox' id='filter_contactos' value='1' "+(getParam("contactos")=="1"?"checked":"")+"/>"
		+"<label for='filter_contactos'>"+lang_contactos()+"</label><br/>"
		+"<input type='checkbox' id='filter_seguimientos' value='1' "+(getParam("seguimientos")=="1"?"checked":"")+"/>"
		+"<label for='filter_seguimientos'>"+lang_seguimientos()+"</label><br/>"
		+"<input type='checkbox' id='filter_incidencias' value='1' "+(getParam("incidencias")=="1"?"checked":"")+"/>"
		+"<label for='filter_incidencias'>"+lang_incidencias()+"</label><br/>"
		+"<input type='checkbox' id='filter_partes' value='1' "+(getParam("partes")=="1"?"checked":"")+"/>"
		+"<label for='filter_partes'>"+lang_partes()+"</label><br/>"
		+"<input type='checkbox' id='filter_facturas' value='1' "+(getParam("facturas")=="1"?"checked":"")+"/>"
		+"<label for='filter_facturas'>"+lang_facturas()+"</label><br/>"
		+"</td><td align='left' valign='top'>"
		+"<input type='checkbox' id='filter_gastos' value='1' "+(getParam("gastos")=="1"?"checked":"")+"/>"
		+"<label for='filter_gastos'>"+lang_gastos()+"</label><br/>"
		+"<input type='checkbox' id='filter_agenda' value='1' "+(getParam("agenda")=="1"?"checked":"")+"/>"
		+"<label for='filter_agenda'>"+lang_agenda()+"</label><br/>"
		+"<input type='checkbox' id='filter_presupuestos' value='1' "+(getParam("presupuestos")=="1"?"checked":"")+"/>"
		+"<label for='filter_presupuestos'>"+lang_presupuestos()+"</label><br/>"
		+"<input type='checkbox' id='filter_actas' value='1' "+(getParam("actas")=="1"?"checked":"")+"/>"
		+"<label for='filter_actas'>"+lang_actas()+"</label><br/>"
		+"</td>"
		+"</tr>"
		+"</table>"
		+"<br/>"
		+"<a href='javascript:void(0);' style='text-decoration:none' "
		+"onclick='$(\"input[type=checkbox][id^=filter_]\").prop(\"checked\",true);'>["+lang_selectall()+"]</a>"
		+" "
		+"<a href='javascript:void(0);' style='text-decoration:none' "
		+"onclick='$(\"input[type=checkbox][id^=filter_]\").prop(\"checked\",false);'>["+lang_selectnone()+"]</a>";
	}

	function update2filter_proyectos() {
		setParam("contactos",$("#filter_contactos").prop("checked")?"1":"0");
		setParam("seguimientos",$("#filter_seguimientos").prop("checked")?"1":"0");
		setParam("incidencias",$("#filter_incidencias").prop("checked")?"1":"0");
		setParam("partes",$("#filter_partes").prop("checked")?"1":"0");
		setParam("facturas",$("#filter_facturas").prop("checked")?"1":"0");
		setParam("gastos",$("#filter_gastos").prop("checked")?"1":"0");
		setParam("agenda",$("#filter_agenda").prop("checked")?"1":"0");
		setParam("presupuestos",$("#filter_presupuestos").prop("checked")?"1":"0");
		setParam("actas",$("#filter_actas").prop("checked")?"1":"0");
	}

	function pdf2filter_proyectos(id) {
		dialog(lang_pdf2filter_title(),lang_pdf2filter_message()+options2filter_proyectos(),[{
			text:lang_buttoncontinue(),
			click:function() {
				update2filter_proyectos();
				openurl("?page="+getParam("page")+"&action=pdf&id="+abs(id)
					+"&contactos="+getParam("contactos")
					+"&seguimientos="+getParam("seguimientos")
					+"&incidencias="+getParam("incidencias")
					+"&partes="+getParam("partes")
					+"&facturas="+getParam("facturas")
					+"&gastos="+getParam("gastos")
					+"&agenda="+getParam("agenda")
					+"&presupuestos="+getParam("presupuestos")
					+"&actas="+getParam("actas"));
				dialog("close");
			}
		}]);
	}

	function view2filter_proyectos(id) {
		dialog(lang_view2filter_title(),lang_view2filter_message()+options2filter_proyectos(),[{
			text:lang_buttoncontinue(),
			click:function() {
				update2filter_proyectos();
				viewpdf("page="+getParam("page")+"&id="+abs(id)
					+"&contactos="+getParam("contactos")
					+"&seguimientos="+getParam("seguimientos")
					+"&incidencias="+getParam("incidencias")
					+"&partes="+getParam("partes")
					+"&facturas="+getParam("facturas")
					+"&gastos="+getParam("gastos")
					+"&agenda="+getParam("agenda")
					+"&presupuestos="+getParam("presupuestos")
					+"&actas="+getParam("actas"));
				dialog("close");
			}
		}]);
	}

}

"use strict";
$(function() { update_totales_proyecto(); });
