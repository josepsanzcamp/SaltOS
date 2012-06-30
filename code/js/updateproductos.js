/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2011 by Josep Sanz Campderrós
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

if(typeof(__updateproductos__)=="undefined" && typeof(parent.__updateproductos__)=="undefined") {
	"use strict";
	var __updateproductos__=1;

	function update_producto(obj) {
		var prefix=obj.name.substr(0,obj.name.length-11);
		var precio=eval("document.form."+prefix+"precio");
		precio.value="0.00";
		var data="action=ajax&query=producto&id_producto="+$(obj).val();
		$.ajax({
			url:"xml.php",
			data:data,
			type:"get",
			success:function(response) {
				$("root>rows>row",response).each(function() {
					precio.value=$("precio",this).text();
				});
				update_totales();
			}
		});
	}

}
