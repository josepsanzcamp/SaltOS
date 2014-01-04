/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2014 by Josep Sanz Campderrós
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

if(typeof(__posiblescli__)=="undefined" && typeof(parent.__posiblescli__)=="undefined") {
	"use strict";
	var __posiblescli__=1;

	function options2filter_posiblescli() {
		return ""
		+"<br/>"
		+"<br/>"
		+"<table width='100%'>"
		+"<tr>"
		+"<td align='left' valign='top'>"
		+"<input type='checkbox' id='filter_agenda' value='1' "+(getParam("agenda")=="1"?"checked":"")+"/>"
		+"<label for='filter_agenda'>"+lang_agenda()+"</label><br/>"
		+"<input type='checkbox' id='filter_presupuestos' value='1' "+(getParam("presupuestos")=="1"?"checked":"")+"/>"
		+"<label for='filter_presupuestos'>"+lang_presupuestos()+"</label><br/>"
		+"</td><td align='left' valign='top'>"
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

	function update2filter_posiblescli() {
		setParam("agenda",$("#filter_agenda").prop("checked")?"1":"0");
		setParam("presupuestos",$("#filter_presupuestos").prop("checked")?"1":"0");
		setParam("actas",$("#filter_actas").prop("checked")?"1":"0");
	}

	function pdf2filter_posiblescli(id) {
		dialog(lang_pdf2filter_title(),lang_pdf2filter_message()+options2filter_posiblescli(),[{
			text:lang_buttoncontinue(),
			click:function() {
				update2filter_posiblescli();
				openurl("xml.php?page="+getParam("page")+"&action=pdf&id="+abs(id)
					+"&agenda="+getParam("agenda")
					+"&presupuestos="+getParam("presupuestos")
					+"&actas="+getParam("actas"));
				dialog("close");
			}
		}]);
	}

	function view2filter_posiblescli(id) {
		dialog(lang_view2filter_title(),lang_view2filter_message()+options2filter_posiblescli(),[{
			text:lang_buttoncontinue(),
			click:function() {
				update2filter_posiblescli();
				viewpdf("page="+getParam("page")+"&id="+abs(id)
					+"&agenda="+getParam("agenda")
					+"&presupuestos="+getParam("presupuestos")
					+"&actas="+getParam("actas"));
				dialog("close");
			}
		}]);
	}

}
