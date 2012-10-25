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

if(typeof(__updatetareas__)=="undefined" && typeof(parent.__updatetareas__)=="undefined") {
	"use strict";
	var __updatetareas__=1;

	function update_tareas() {
		if(typeof(proyectos_init)=="undefined") {
			setTimeout(function() { update_tareas(); },100);
			return;
		}
		if(!proyectos_init) {
			setTimeout(function() { update_tareas(); },100);
			return;
		}
		var proyecto=$("select[name$=id_proyecto]");
		var tarea=$("select[name$=id_tarea]");
		if(tareas_defaults=="") tareas_defaults=$(tarea).html();
		var data="action=ajax&query=tareas&id_proyecto="+$(proyecto).val();
		$.ajax({
			url:"xml.php",
			data:data,
			type:"get",
			success:function(response) {
				var options=tareas_defaults;
				var original=$(tarea).attr("original");
				$("root>rows>row",response).each(function() {
					var id=$("id",this).text();
					var nombre=$("tarea",this).text();
					var selected=(id==original)?"selected='selected'":"";
					options+="<option value='"+id+"' "+selected+">"+nombre+"</option>";
				});
				$(tarea).html(options);
			}
		});
	}

}

"use strict";
var tareas_defaults="";

$(document).ready(function() { update_tareas(); });
