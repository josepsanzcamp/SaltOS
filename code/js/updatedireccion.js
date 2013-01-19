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

if(typeof(__updatedireccion__)=="undefined" && typeof(parent.__updatedireccion__)=="undefined") {
	"use strict";
	var __updatedireccion__=1;

	function update_direcciones() {
		var lista=[
			{a:"pais",b:"paises",c:""},
			{a:"provincia",b:"provincias",c:"pais"},
			{a:"poblacion",b:"poblaciones",c:"provincia"},
			{a:"codpostal",b:"codpostal",c:"poblacion"}
		];
		$(lista).each(function() {
			var arg_a=this.a;
			var arg_b=this.b;
			var arg_c=this.c;
			$("input[name$=nombre_"+arg_a+"]").each(function() {
				var key=$(this).attr("name");
				var prefix="";
				$("input[name^=prefix_]").each(function() {
					var val=$(this).val();
					if(key.substr(0,val.length)==val) prefix=val;
				});
				$(this).autocomplete({
					delay:300,
					source:function(request,response) {
						var id=prefix?$("#"+prefix+"id_"+arg_c).val():"";
						var term=request.term;
						var input=this.element;
						var data="action=ajax&format=json&query="+arg_b+"&id="+id+"&term="+term;
						$.ajax({
							url:"xml.php",
							data:data,
							type:"get",
							dataType:"json",
							success:function(data) {
								// TO CANCEL OLD REQUESTS
								var term2=$(input).val();
								if(term==term2) response(data);
							},
							error:function(XMLHttpRequest,textStatus,errorThrown) {
								errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
							}
						});
					},
					search:function() {
						return this.value.length>0;
					},
					focus:function() {
						return false;
					},
					select:function(event,ui) {
						$("#"+prefix+"id_"+arg_a).val(ui.item.id);
						this.value=ui.item.label;
						return false;
					}
				});
			});
		});
	}

}

"use strict";
$(function() {
	update_direcciones();
	var checkbox="input[type=checkbox][name^=direccion][name$=seleccion]";
	$(checkbox).bind("change",function() {
		var value=$(this).prop("checked");
		$(checkbox).prop("checked",false);
		$(this).prop("checked",value);
	});
});
