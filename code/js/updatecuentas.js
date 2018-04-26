/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2018 by Josep Sanz Campderrós
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

if(typeof(__updatecuentas__)=="undefined" && typeof(parent.__updatecuentas__)=="undefined") {
	"use strict";
	var __updatecuentas__=1;

	function update_cuentas() {
		var usuario=$("select[name$=id_usuario]");
		var cuenta=$("select[name$=id_cuenta]");
		if(cuentas_defaults=="") cuentas_defaults=$(cuenta).html();
		var data="action=ajax&query=cuentas&id_usuario="+$(usuario).val();
		$.ajax({
			url:"index.php",
			data:data,
			type:"get",
			success:function(response) {
				var options=cuentas_defaults;
				var original=$(cuenta).attr("original");
				$(response["rows"]).each(function() {
					var selected=(this["id"]==original)?"selected='selected'":"";
					options+="<option value='"+this["id"]+"' "+selected+">"+this["nombre"]+"</option>";
				});
				$(cuenta).html(options).trigger("refresh");
			},
			error:function(XMLHttpRequest,textStatus,errorThrown) {
				errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
			}
		});
	}

}

"use strict";
var cuentas_defaults="";

$(function() { update_cuentas(); });
