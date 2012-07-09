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

if(typeof(__usuarios__)=="undefined" && typeof(parent.__usuarios__)=="undefined") {
	"use strict";
	var __usuarios__=1;

	function update_usuarios(obj) {
		var empleado=$("select[name$=id_empleado]");
		var cliente=$("select[name$=id_cliente]");
		var proveedor=$("select[name$=id_proveedor]");
		if(obj!=empleado[0]) $(empleado).val("0");
		if(obj!=cliente[0]) $(cliente).val("0");
		if(obj!=proveedor[0]) $(proveedor).val("0");
	}

	function check_activado(obj) {
		var padre=$(obj).parent().parent();
		var id_registro=$("input[name$=id_registro]",padre).val();
		if(id_registro>0) {
			var id_aplicacion=$("input[type=hidden][name$=id_aplicacion]",padre).val();
			padre=$(padre).parent().parent();
			var total=0;
			var activado=0;
			var temp=$("input[type=hidden][name$=id_aplicacion][value="+id_aplicacion+"]",padre);
			$(temp).each(function() {
				var padre=$(this).parent();
				var id_registro=$("input[name$=id_registro]",padre).val();
				if(id_registro>0) {
					total++;
					activado+=$("input[type=checkbox][name$=activado]",padre).prop("checked")?1:0;
				}
			});
			$(temp).each(function() {
				var padre=$(this).parent();
				var id_registro=$("input[name$=id_registro]",padre).val();
				if(id_registro<0) {
					if(activado==0) {
						$("input[type=checkbox][name$=activado]",padre).prop("checked",false);
						$("input[type=checkbox][name$=activado]",padre).prop("disabled",false);
					} else if(activado==total) {
						$("input[type=checkbox][name$=activado]",padre).prop("checked",true);
						$("input[type=checkbox][name$=activado]",padre).prop("disabled",false);
					} else {
						$("input[type=checkbox][name$=activado]",padre).prop("checked",true);
						$("input[type=checkbox][name$=activado]",padre).prop("disabled",true);
					}
				}
			});
		} else {
			var activado=$("input[type=checkbox][name$=activado]",padre).prop("checked");
			var id_aplicacion=$("input[type=hidden][name$=id_aplicacion]",padre).val();
			padre=$(padre).parent().parent();
			var temp=$("input[type=hidden][name$=id_aplicacion][value="+id_aplicacion+"]",padre);
			$(temp).each(function() {
				var padre=$(this).parent();
				$("input[type=checkbox][name$=activado]",padre).prop("checked",activado);
			});
		}
	}

	function appreg_details(obj) {
		var padre=$(obj).parent().parent();
		var id_aplicacion=$("input[type=hidden][name$=id_aplicacion]",padre).val();
		var id_registro=$("input[type=hidden][name$=id_registro]",padre).val();
		padre=$(padre).parent().parent();
		var temp=$("input[type=hidden][name$=id_aplicacion][value="+id_aplicacion+"]",padre);
		$(temp).each(function() {
			var padre=$(this).parent();
			var id_registro2=$("input[type=hidden][name$=id_registro]",padre).val();
			if(id_registro==-2) {
				if(id_registro2==-2) {
					$(padre).hide();
				} else {
					$(padre).show();
				}
			} else if(id_registro==-1) {
				if(id_registro2==-2) {
					$(padre).show();
				} else {
					$(padre).hide();
				}
			}
		});
		padre=$(padre).parent();
		make_tables(padre);
	}

	function update_score() {
		var pass=$("input[name$=password_new]");
		if($(pass).val()) {
			data="action=score&pass="+rawurlencode($(pass).val())+"&format=xml"
			$.ajax({
				url:"xml.php",
				data:data,
				type:"post",
				success:function(response) {
					$(".score").attr("src",$("root>image",response).text());
					if(intval($("root>valid",response).text())) {
						$(pass).removeClass("ui-state-error");
					} else {
						$(pass).addClass("ui-state-error");
					}
				}
			});
		} else {
			$(".score").attr("src","");
			$(pass).removeClass("ui-state-error");
		}
	}

	function check_passwords() {
		var pass=$("input[name$=password_new]");
		var pass2=$("input[name$=password_new2]");
		if($(pass).val()==$(pass2).val()) {
			$(pass2).removeClass("ui-state-error");
		} else {
			$(pass2).addClass("ui-state-error");
		}
	}

}

"use strict";
$(document).ready(function() {
	$("input[type=hidden][name$=id_registro][value=-2]").each(function() {
		var padre=$(this).parent();
		$(padre).show();
		// TODO REVISED
		var id_aplicacion=$("input[type=hidden][name$=id_aplicacion]",padre).val();
		padre=$(padre).parent().parent();
		var total=0;
		var activado=0;
		var temp=$("input[type=hidden][name$=id_aplicacion][value="+id_aplicacion+"]",padre);
		$(temp).each(function() {
			var padre=$(this).parent();
			var id_registro=$("input[name$=id_registro]",padre).val();
			if(id_registro>0) {
				total++;
				activado+=$("input[type=checkbox][name$=activado]",padre).prop("checked")?1:0;
			}
		});
		$(temp).each(function() {
			var padre=$(this).parent();
			var id_registro=$("input[name$=id_registro]",padre).val();
			if(id_registro<0) {
				if(activado==0) {
					$("input[type=checkbox][name$=activado]",padre).prop("checked",false);
					$("input[type=checkbox][name$=activado]",padre).prop("disabled",false);
				} else if(activado==total) {
					$("input[type=checkbox][name$=activado]",padre).prop("checked",true);
					$("input[type=checkbox][name$=activado]",padre).prop("disabled",false);
				} else {
					$("input[type=checkbox][name$=activado]",padre).prop("checked",true);
					$("input[type=checkbox][name$=activado]",padre).prop("disabled",true);
				}
			}
		});
		// TODO REVISED
	});
});
