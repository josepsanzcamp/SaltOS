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

if(typeof(__clientes__)=="undefined" && typeof(parent.__clientes__)=="undefined") {
	"use strict";
	var __clientes__=1;

	function update_tipo() {
		var id_tipo=$("select[name$=id_tipo]").val();
		if(id_tipo) {
			var data="action=ajax&query=tipocliente&id_tipo="+id_tipo;
			$.ajax({
				url:"index.php",
				data:data,
				type:"get",
				success:function(response) {
					$(Array("nombre1","nombre2","cif")).each(function() {
						$("input[name$="+this+"]").parent().prev().html(response["rows"][0][this]);
					});
				},
				error:function(XMLHttpRequest,textStatus,errorThrown) {
					errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
				}
			});
		}
	}

	function options2filter_clientes() {
		return ""
		+"<br/>"
		+"<br/>"
		+"<table width='100%'>"
		+"<tr>"
		+"<td align='left' valign='top'>"
		+"<input type='checkbox' id='filter_contactos' value='1' "+(getParam("contactos")=="1"?"checked":"")+"/>"
		+"<label for='filter_contactos'>"+lang_contactos()+"</label><br/>"
		+"<input type='checkbox' id='filter_incidencias' value='1' "+(getParam("incidencias")=="1"?"checked":"")+"/>"
		+"<label for='filter_incidencias'>"+lang_incidencias()+"</label><br/>"
		+"<input type='checkbox' id='filter_proyectos' value='1' "+(getParam("proyectos")=="1"?"checked":"")+"/>"
		+"<label for='filter_proyectos'>"+lang_proyectos()+"</label><br/>"
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

	function update2filter_clientes() {
		setParam("contactos",$("#filter_contactos").prop("checked")?"1":"0");
		setParam("incidencias",$("#filter_incidencias").prop("checked")?"1":"0");
		setParam("proyectos",$("#filter_proyectos").prop("checked")?"1":"0");
		setParam("partes",$("#filter_partes").prop("checked")?"1":"0");
		setParam("facturas",$("#filter_facturas").prop("checked")?"1":"0");
		setParam("gastos",$("#filter_gastos").prop("checked")?"1":"0");
		setParam("agenda",$("#filter_agenda").prop("checked")?"1":"0");
		setParam("presupuestos",$("#filter_presupuestos").prop("checked")?"1":"0");
		setParam("actas",$("#filter_actas").prop("checked")?"1":"0");
	}

	function pdf2filter_clientes(id) {
		dialog(lang_pdf2filter_title(),lang_pdf2filter_message()+options2filter_clientes(),[{
			text:lang_buttoncontinue(),
			click:function() {
				update2filter_clientes();
				openurl("?page="+getParam("page")+"&action=pdf&id="+abs(id)
					+"&contactos="+getParam("contactos")
					+"&incidencias="+getParam("incidencias")
					+"&proyectos="+getParam("proyectos")
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

	function view2filter_clientes(id) {
		dialog(lang_view2filter_title(),lang_view2filter_message()+options2filter_clientes(),[{
			text:lang_buttoncontinue(),
			click:function() {
				update2filter_clientes();
				viewpdf("page="+getParam("page")+"&id="+abs(id)
					+"&contactos="+getParam("contactos")
					+"&incidencias="+getParam("incidencias")
					+"&proyectos="+getParam("proyectos")
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
