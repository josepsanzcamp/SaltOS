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

if(typeof(__updatevencimientos__)=="undefined" && typeof(parent.__updatevencimientos__)=="undefined") {
	"use strict";
	var __updatevencimientos__=1;

	function update_vencimientos(caller) {
		// control vencimientos
		for(var i=0,len=document.form.elements.length;i<len;i++) {
			obj=document.form.elements[i];
			if(obj.type) {
				if(obj.name.substr(obj.name.length-5,5)=="total") {
					var total=obj.value;
					var importe2=total;
					var percent2=100;
				}
			}
		}
		for(var i=0,len=document.form.elements.length;i<len;i++) {
			obj=document.form.elements[i];
			if(obj.type) {
				if(obj.name.substr(obj.name.length-7,7)=="percent") {
					var prefix=obj.name.substr(0,obj.name.length-7);
					var importe_obj=eval("document.form."+prefix+"importe");
					var fecha_obj=eval("document.form."+prefix+"fecha");
					var percent=round(floatval(obj.value),2);
					var importe=round(floatval(importe_obj.value),2);
					var fecha=fecha_obj.value;
					if(caller==importe_obj) {
						percent=round(floatval(importe*100/total),2);
					}
					if(fecha=="") {
						percent=0;
					}
					obj.value=percent;
					importe=round(floatval(total/100*percent),2);
					importe_obj.value=importe;
				}
			}
		}
		for(var i=0,len=document.form.elements.length;i<len;i++) {
			obj=document.form.elements[i];
			if(obj.type) {
				if(obj.name.substr(obj.name.length-7,7)=="percent") {
					var prefix=obj.name.substr(0,obj.name.length-7);
					var importe_obj=eval("document.form."+prefix+"importe");
					var fecha_obj=eval("document.form."+prefix+"fecha");
					var percent=round(floatval(obj.value),2);
					var importe=round(floatval(importe_obj.value),2);
					var fecha=fecha_obj.value;
					var del_obj=eval("document.form."+prefix+"delete");
					if(del_obj) if(del_obj.checked) {
						percent=0;
						importe=0;
					}
					if(fecha=="" && (importe2!=0 || percent2!=0)) {
						percent=round(floatval(percent2),2);
						importe=round(floatval(importe2),2);
					}
					obj.value=percent;
					importe_obj.value=importe;
					percent2-=percent;
					importe2-=importe;
				}
			}
		}
	}

}
