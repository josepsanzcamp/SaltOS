/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2019 by Josep Sanz Campderrós
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

if(typeof(__grupos__)=="undefined" && typeof(parent.__grupos__)=="undefined") {
	"use strict";
	var __grupos__=1;

	function check_allow_deny(obj) {
		var padre=$(obj).parent().parent();
		var allow=$("input[type=checkbox][name$=allow]",padre);
		var deny=$("input[type=checkbox][name$=deny]",padre);
		if($(obj).attr("name")==$(allow).attr("name")) $(deny).prop("checked",false);
		if($(obj).attr("name")==$(deny).attr("name")) $(allow).prop("checked",false);
		var id_permiso=$("input[name$=id_permiso]",padre).val();
		if(id_permiso>0) {
			var id_aplicacion=$("input[type=hidden][name$=id_aplicacion]",padre).val();
			padre=$(padre).parent().parent();
			var total=0;
			var allow=0;
			var deny=0;
			var temp=$("input[type=hidden][name$=id_aplicacion][value="+id_aplicacion+"]",padre);
			$(temp).each(function() {
				var padre=$(this).parent();
				var id_permiso=$("input[name$=id_permiso]",padre).val();
				if(id_permiso>0) {
					total++;
					allow+=$("input[type=checkbox][name$=allow]",padre).prop("checked")?1:0;
					deny+=$("input[type=checkbox][name$=deny]",padre).prop("checked")?1:0;
				}
			});
			$(temp).each(function() {
				var padre=$(this).parent();
				var id_permiso=$("input[name$=id_permiso]",padre).val();
				if(id_permiso<0) {
					if(allow==0) {
						$("input[type=checkbox][name$=allow]",padre).prop("checked",false);
						$("input[type=checkbox][name$=allow]",padre).prop("disabled",false);
					} else if(allow==total) {
						$("input[type=checkbox][name$=allow]",padre).prop("checked",true);
						$("input[type=checkbox][name$=allow]",padre).prop("disabled",false);
					} else {
						$("input[type=checkbox][name$=allow]",padre).prop("checked",true);
						$("input[type=checkbox][name$=allow]",padre).prop("disabled",true);
					}
					if(deny==0) {
						$("input[type=checkbox][name$=deny]",padre).prop("checked",false);
						$("input[type=checkbox][name$=deny]",padre).prop("disabled",false);
					} else if(deny==total) {
						$("input[type=checkbox][name$=deny]",padre).prop("checked",true);
						$("input[type=checkbox][name$=deny]",padre).prop("disabled",false);
					} else {
						$("input[type=checkbox][name$=deny]",padre).prop("checked",true);
						$("input[type=checkbox][name$=deny]",padre).prop("disabled",true);
					}
					var hasallow=$("input[type=checkbox][name$=allow]",padre).prop("disabled");
					var hasdeny=$("input[type=checkbox][name$=deny]",padre).prop("disabled");
					if(hasallow) $("input[type=checkbox][name$=deny]",padre).prop("disabled",true);
					if(hasdeny) $("input[type=checkbox][name$=allow]",padre).prop("disabled",true);
				}
			});
		} else {
			var allow=$("input[type=checkbox][name$=allow]",padre).prop("checked");
			var deny=$("input[type=checkbox][name$=deny]",padre).prop("checked");
			var id_aplicacion=$("input[type=hidden][name$=id_aplicacion]",padre).val();
			padre=$(padre).parent().parent();
			var temp=$("input[type=hidden][name$=id_aplicacion][value="+id_aplicacion+"]",padre);
			$(temp).each(function() {
				var padre=$(this).parent();
				$("input[type=checkbox][name$=allow]",padre).prop("checked",allow);
				$("input[type=checkbox][name$=deny]",padre).prop("checked",deny);
			});
		}
	}

	function permiso_details(obj) {
		var padre=$(obj).parent().parent();
		var id_aplicacion=$("input[type=hidden][name$=id_aplicacion]",padre).val();
		var id_permiso=$("input[type=hidden][name$=id_permiso]",padre).val();
		padre=$(padre).parent().parent();
		var temp=$("input[type=hidden][name$=id_aplicacion][value="+id_aplicacion+"]",padre);
		$(temp).each(function() {
			var padre=$(this).parent();
			var id_permiso2=$("input[type=hidden][name$=id_permiso]",padre).val();
			if(id_permiso==-2) {
				if(id_permiso2==-2) {
					$(padre).addClass("none");
				} else {
					$(padre).removeClass("none");
				}
			} else if(id_permiso==-1) {
				if(id_permiso2==-2) {
					$(padre).removeClass("none");
				} else {
					$(padre).addClass("none");
				}
			}
		});
		padre=$(padre).parent();
		make_tables(padre);
	}

	function make_grupos() {
		if(getParam("action")!="form") return;
		var readonly=0
		if(intval(getParam("id"))<0) readonly=1;
		setTimeout(function() {
			$("input[type=hidden][name$=id_permiso][value=-2]").each(function(index) {
				var padre=$(this).parent();
				$(padre).removeClass("none");
				setTimeout(function() {
					var id_aplicacion=$("input[type=hidden][name$=id_aplicacion]",padre).val();
					padre=$(padre).parent().parent();
					var total=0;
					var allow=0;
					var deny=0;
					var temp=$("input[type=hidden][name$=id_aplicacion][value="+id_aplicacion+"]",padre);
					$(temp).each(function() {
						var padre=$(this).parent();
						var id_permiso=$("input[name$=id_permiso]",padre).val();
						if(id_permiso>0) {
							total++;
							allow+=$("input[type=checkbox][name$=allow]",padre).prop("checked")?1:0;
							deny+=$("input[type=checkbox][name$=deny]",padre).prop("checked")?1:0;
						}
					});
					$(temp).each(function() {
						var padre=$(this).parent();
						var id_permiso=$("input[name$=id_permiso]",padre).val();
						if(id_permiso<0) {
							if(allow==0) {
								$("input[type=checkbox][name$=allow]",padre).prop("checked",false);
								if(!readonly) $("input[type=checkbox][name$=allow]",padre).prop("disabled",false);
							} else if(allow==total) {
								$("input[type=checkbox][name$=allow]",padre).prop("checked",true);
								if(!readonly) $("input[type=checkbox][name$=allow]",padre).prop("disabled",false);
							} else {
								$("input[type=checkbox][name$=allow]",padre).prop("checked",true);
								if(!readonly) $("input[type=checkbox][name$=allow]",padre).prop("disabled",true);
							}
							if(deny==0) {
								$("input[type=checkbox][name$=deny]",padre).prop("checked",false);
								if(!readonly) $("input[type=checkbox][name$=deny]",padre).prop("disabled",false);
							} else if(deny==total) {
								$("input[type=checkbox][name$=deny]",padre).prop("checked",true);
								if(!readonly) $("input[type=checkbox][name$=deny]",padre).prop("disabled",false);
							} else {
								$("input[type=checkbox][name$=deny]",padre).prop("checked",true);
								if(!readonly) $("input[type=checkbox][name$=deny]",padre).prop("disabled",true);
							}
							var hasallow=$("input[type=checkbox][name$=allow]",padre).prop("disabled");
							var hasdeny=$("input[type=checkbox][name$=deny]",padre).prop("disabled");
							if(hasallow) $("input[type=checkbox][name$=deny]",padre).prop("disabled",true);
							if(hasdeny) $("input[type=checkbox][name$=allow]",padre).prop("disabled",true);
						}
					});
				},(index+1)*100);
			});
			var screen=$(".ui-layout-center");
			make_tables(screen);
		},100);
	}

}

"use strict";
$(function() { make_grupos(); });
