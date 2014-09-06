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

if(typeof(__contactos__)=="undefined" && typeof(parent.__contactos__)=="undefined") {
	"use strict";
	var __contactos__=1;

	function update_contacto() {
		var id_cliente=intval($("input[name$=id_cliente]").val());
		var id_proveedor=intval($("select[name$=id_proveedor]").val());
		var id_empleado=intval($("select[name$=id_empleado]").val());
		var id_proyecto=intval($("select[name$=id_proyecto]").val());
		var data="";
		if(id_cliente) data="action=ajax&query=cliente&id_cliente="+id_cliente;
		if(id_proveedor) data=""; // TODO
		if(id_empleado) data=""; // TODO
		if(id_proyecto) data=""; // TODO
		if(data) {
			$.ajax({
				url:"index.php",
				data:data,
				type:"get",
				success:function(response) {
					$("root>rows>row",response).each(function() {
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
				},
				error:function(XMLHttpRequest,textStatus,errorThrown) {
					errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
				}
			});
		}
	}

}
