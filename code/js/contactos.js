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
					$(response["rows"]).each(function() {
						$("input[name$=direccion]").val(this["direccion"]);
						$("input[name$=id_pais]").val(this["id_pais"]);
						$("input[name$=id_provincia]").val(this["id_provincia"]);
						$("input[name$=id_poblacion]").val(this["id_poblacion"]);
						$("input[name$=id_codpostal]").val(this["id_codpostal"]);
						$("input[name$=nombre_pais]").val(this["nombre_pais"]);
						$("input[name$=nombre_provincia]").val(this["nombre_provincia"]);
						$("input[name$=nombre_poblacion]").val(this["nombre_poblacion"]);
						$("input[name$=nombre_codpostal]").val(this["nombre_codpostal"]);
					});
				},
				error:function(XMLHttpRequest,textStatus,errorThrown) {
					errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
				}
			});
		}
	}

}
