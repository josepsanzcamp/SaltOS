/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2021 by Josep Sanz Campderrós
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

"use strict";

/* MAIN OBJECT */
var saltos={};

/* ERROR MANAGEMENT */
saltos.init_error=function() {
	window.onerror=function(msg,file,line) {
		var data={"jserror":msg,"details":"Error on file "+file+" at line "+line};
		data="array="+encodeURIComponent(btoa(JSON.stringify(data)));
		$.ajax({ url:"index.php?action=adderror",data:data,type:"post" });
	};
};

/* LOG MANAGEMENT */
saltos.addlog=function(msg) {
	var data="msg="+encodeURIComponent(btoa(msg));
	$.ajax({ url:"index.php?action=addlog",data:data,type:"post" });
};

/* NUMERIC FUNCTIONS */
saltos.floatval2=function(obj) {
	saltos._format_number(obj,0);
};

saltos.intval2=function(obj) {
	saltos._format_number(obj,1);
};

saltos._format_number=function(obj,punto) {
	var texto=obj.value;
	var texto2="";
	var numero=0;
	for(var i=0,len=texto.length;i<len;i++) {
		var letra=substr(texto,i,1);
		if(letra>="0" && letra<="9") {
			texto2+=letra;
			numero=1;
		} else if((letra=="." || letra==",") && !punto) {
			if(!numero) texto2+="0";
			texto2+=".";
			punto=1;
		} else if(letra=="-" && texto2.length==0) {
			texto2+="-";
		}
	}
	if(texto!=texto2) obj.value=texto2;
};

/* REQUIRED FUNCTIONS */
saltos.check_required=function() {
	var field=null;
	var label="";
	$("[isrequired=true]").each(function() {
		var valor=$(this).val();
		var campo=this;
		if($(this).is("select")) {
			if(valor=="0") valor="";
			campo=$(this).next().get(0);
		}
		// CHECK FOR VISIBILITY
		if(!$(campo).is(":visible")) return;
		// CONTINUE
		if(!valor) {
			$(campo).addClass("ui-state-error");
		} else {
			$(campo).removeClass("ui-state-error");
		}
		if(!valor && !field) {
			field=campo;
			label=$(this).attr("labeled");
		}
	});
	if(field) {
		alerta(lang_requiredfield()+": "+label,function() {
			$(field).trigger("focus");
		});
	}
	return field==null;
};

saltos.copy_value=function(orig,dest) {
	$("#"+dest).val($("#"+orig).val());
};

/* STRING FUNCTIONS */
saltos.intelligence_cut=function(txt,max) {
	var len=strlen(txt);
	if(len>max) {
		while(max>0 && substr(txt,max,1)!=" ") max--;
		if(max==0) while(max<len && substr(txt,max,1)!=" ") max++;
		if(max>0) if(in_array(substr(txt,max-1,1),[",",".","-","("])) max--;
		var preview=(max==len)?txt:substr(txt,0,max)+"...";
	} else {
		var preview=txt;
	}
	return preview;
};

/* DATETIME FUNCTIONS */
saltos.dateval=function(value) {
	value=str_replace(["-",":",",",".","/"]," ",value);
	var temp="";
	while(temp!=(value=str_replace("  "," ",value))) temp=value;
	temp=explode(" ",value);
	for(var i=0,len=temp.length;i<len;i++) temp[i]=intval(temp[i]);
	for(var i=0;i<3;i++) if(typeof(temp[i])=="undefined") temp[i]=0;
	if(temp[2]>1900) {
		temp[2]=min(9999,max(0,temp[2]));
		temp[1]=min(12,max(0,temp[1]));
		temp[0]=min(saltos.__days_of_a_month(temp[2],temp[1]),max(0,temp[0]));
		value=sprintf("%04d-%02d-%02d",temp[2],temp[1],temp[0]);
	} else {
		temp[0]=min(9999,max(0,temp[0]));
		temp[1]=min(12,max(0,temp[1]));
		temp[2]=min(saltos.__days_of_a_month(temp[0],temp[1]),max(0,temp[2]));
		value=sprintf("%04d-%02d-%02d",temp[0],temp[1],temp[2]);
	}
	return value;
};

saltos.timeval=function(value) {
	value=str_replace(["-",":",",",".","/"]," ",value);
	var temp="";
	while(temp!=(value=str_replace("  "," ",value))) temp=value;
	temp=explode(" ",value);
	for(var i=0,len=temp.length;i<len;i++) temp[i]=intval(temp[i]);
	for(var i=0;i<3;i++) if(typeof(temp[i])=="undefined") temp[i]=0;
	temp[0]=min(24,max(0,temp[0]));
	temp[1]=min(59,max(0,temp[1]));
	temp[2]=min(59,max(0,temp[2]));
	value=sprintf("%02d:%02d:%02d",temp[0],temp[1],temp[2]);
	return value;
};

saltos.__days_of_a_month=function(year,month) {
	return date("t",strtotime(sprintf("%04d-%02d-%02d",year,month,1)));
};

saltos.check_datetime=function(orig,comp,dest) {
	var orig_obj_date=("input[name$="+orig+"_date]");
	var orig_obj_time=("input[name$="+orig+"_time]");
	var dest_obj_date=("input[name$="+dest+"_date]");
	var dest_obj_time=("input[name$="+dest+"_time]");
	if($(orig_obj_date).val()=="") return;
	if($(orig_obj_time).val()=="") return;
	if($(dest_obj_date).val()=="") return;
	if($(dest_obj_time).val()=="") return;
	var orig_date=explode("-",dateval($(orig_obj_date).val()));
	var orig_time=explode(":",timeval($(orig_obj_time).val()));
	var dest_date=explode("-",dateval($(dest_obj_date).val()));
	var dest_time=explode(":",timeval($(dest_obj_time).val()));
	var orig_unix=mktime(orig_time[0],orig_time[1],orig_time[2],orig_date[1],orig_date[2],orig_date[0]);
	var dest_unix=mktime(dest_time[0],dest_time[1],dest_time[2],dest_date[1],dest_date[2],dest_date[0]);
	var dest_unix2=dest_unix;
	if(comp=="le" && dest_unix<orig_unix) dest_unix2=orig_unix;
	if(comp=="ge" && dest_unix>orig_unix) dest_unix2=orig_unix;
	if(dest_unix!=dest_unix2) {
		$(dest_obj_date).val(implode("-",orig_date));
		$(dest_obj_time).val(implode(":",orig_time));
		$(dest_obj_time).trigger("change");
	}
};

saltos.check_date=function(orig,comp,dest) {
	var orig_obj_date=("input[name$="+orig+"]");
	var dest_obj_date=("input[name$="+dest+"]");
	if($(orig_obj_date).val()=="") return;
	if($(dest_obj_date).val()=="") return;
	var orig_date=explode("-",dateval($(orig_obj_date).val()));
	var dest_date=explode("-",dateval($(dest_obj_date).val()));
	var orig_unix=mktime(12,0,0,orig_date[1],orig_date[2],orig_date[0]);
	var dest_unix=mktime(12,0,0,dest_date[1],dest_date[2],dest_date[0]);
	var dest_unix2=dest_unix;
	if(comp=="le" && dest_unix<orig_unix) dest_unix2=orig_unix;
	if(comp=="ge" && dest_unix>orig_unix) dest_unix2=orig_unix;
	if(dest_unix!=dest_unix2) {
		$(dest_obj_date).val(implode("-",orig_date));
		$(dest_obj_date).trigger("change");
	}
};

saltos.check_time=function(orig,comp,dest) {
	var orig_obj_time=("input[name$="+orig+"]");
	var dest_obj_time=("input[name$="+dest+"]");
	if($(orig_obj_time).val()=="") return;
	if($(dest_obj_time).val()=="") return;
	var orig_time=explode(":",timeval($(orig_obj_time).val()));
	var dest_time=explode(":",timeval($(dest_obj_time).val()));
	var orig_unix=mktime(orig_time[0],orig_time[1],orig_time[2],1,1,1970);
	var dest_unix=mktime(dest_time[0],dest_time[1],dest_time[2],1,1,1970);
	var dest_unix2=dest_unix;
	if(comp=="le" && dest_unix<orig_unix) dest_unix2=orig_unix;
	if(comp=="ge" && dest_unix>orig_unix) dest_unix2=orig_unix;
	if(dest_unix!=dest_unix2) {
		$(dest_obj_time).val(implode(":",orig_time));
		$(dest_obj_time).trigger("change");
	}
};

/* KEYBOARD FUNCTIONS */
saltos.get_keycode=function(event) {
	var keycode=0;
	if(event.keyCode) keycode=event.keyCode;
	else if(event.which) keycode=event.which;
	else keycode=event.charCode;
	return keycode;
};

saltos.is_enterkey=function(event) {
	return saltos.get_keycode(event)==13;
};

saltos.is_escapekey=function(event) {
	return saltos.get_keycode(event)==27;
};

saltos.is_disabled=function(obj) {
	return $(obj).hasClass("ui-state-disabled");
};

/* COOKIES MANAGEMENT */
saltos.cookies={};
saltos.cookies.data={};
saltos.cookies.interval=null;
saltos.cookies.counter=0;

saltos.cookies.sync=function(cmd) {
	if(!isset(cmd)) var cmd="start";
	if(cmd=="stop") {
		if(saltos.cookies.interval!=null) {
			clearInterval(saltos.cookies.interval);
			saltos.cookies.interval=null;
		}
		saltos.cookies.__sync_helper();
		for(var hash in saltos.cookies.data) delete saltos.cookies.data[hash];
	}
	if(cmd=="start") {
		// REQUEST ALL COOKIES
		var data="action=ajax&query=cookies";
		$.ajax({
			url:"index.php",
			data:data,
			type:"get",
			async:false,
			success:function(response) {
				for(var key in response.rows) {
					var val=response.rows[key];
					saltos.cookies.data[md5(val.clave)]={
						"key":val.clave,
						"val":val.valor,
						"orig":val.valor,
						"sync":0
					};
				}
			},
			error:function(XMLHttpRequest,textStatus,errorThrown) {
				errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
			}
		});
		saltos.cookies.counter=0;
		saltos.cookies.interval=setInterval(function() {
			saltos.cookies.counter=saltos.cookies.counter+1;
			if(saltos.cookies.counter>=3) {
				saltos.cookies.__sync_helper();
				saltos.cookies.counter=0;
			}
		},1000);
	}
};

saltos.cookies.__sync_helper=function() {
	for(var hash in saltos.cookies.data) {
		if(saltos.cookies.data[hash].sync) {
			if(saltos.cookies.data[hash].val!=saltos.cookies.data[hash].orig) {
				var data="action=cookies&name="+encodeURIComponent(saltos.cookies.data[hash].key)+"&value="+encodeURIComponent(saltos.cookies.data[hash].val);
				var value=$.ajax({
					url:"index.php",
					data:data,
					type:"post",
					async:false,
				}).responseText;
				if(value!="") {
					saltos.cookies.data[hash].orig=saltos.cookies.data[hash].val;
					saltos.cookies.data[hash].sync=0;
				}
			} else {
				saltos.cookies.data[hash].sync=0;
			}
		}
	}
};

saltos.cookies.getCookie=function(name) {
	var hash=md5(name);
	if(typeof saltos.cookies.data[hash]=="undefined") {
		var value=$.cookie(name);
	} else {
		var value=saltos.cookies.data[hash].val;
	}
	return value;
};

saltos.cookies.getIntCookie=function(name) {
	return intval(saltos.cookies.getCookie(name));
};

saltos.cookies.getBoolCookie=function(name) {
	return saltos.cookies.getIntCookie(name)?true:false;
};

saltos.cookies.setCookie=function(name,value) {
	var hash=md5(name);
	if(typeof saltos.cookies.data[hash]=="undefined") {
		if(saltos.cookies.interval!=null) {
			saltos.cookies.data[hash]={
				"key":name,
				"val":value,
				"orig":value+"!",
				"sync":1
			};
			saltos.cookies.counter=0;
		} else {
			$.cookie(name,value,{expires:365,path:"/"});
		}
	} else {
		if(saltos.cookies.data[hash].val!=value) {
			saltos.cookies.data[hash].val=value;
			saltos.cookies.data[hash].sync=1;
		}
		saltos.cookies.counter=0;
	}
};

saltos.cookies.setIntCookie=function(name,value) {
	saltos.cookies.setCookie(name,intval(value));
};

saltos.cookies.setBoolCookie=function(name,value) {
	saltos.cookies.setIntCookie(name,value?1:0);
};

/* COLOR MANAGEMENT */
saltos.colors={};

saltos.get_colors=function(clase,param) {
	if(typeof(clase)=="undefined" && typeof(param)=="undefined") {
		for(var hash in saltos.colors) delete saltos.colors[hash];
		return;
	}
	hash=md5(JSON.stringify([clase,param]));
	if(typeof(saltos.colors[hash])=="undefined") {
		// GET THE COLORS USING THIS TRICK
		if($("#ui-color-trick").length==0) {
			$("body").append("<div id='ui-color-trick'></div>");
		}
		$("#ui-color-trick").addClass(clase);
		saltos.colors[hash]=$("#ui-color-trick").css(param);
		$("#ui-color-trick").removeClass(clase);
	}
	return saltos.colors[hash];
};

saltos.rgb2hex=function(color) {
	if(strncasecmp(color,"rgba",4)==0) {
		var temp=color.split(/([\(,\)])/);
		if(temp.length==11) color=sprintf("%02x%02x%02x",temp[2],temp[4],temp[6]);
	} else if(strncasecmp(color,"rgb",3)==0) {
		var temp=color.split(/([\(,\)])/);
		if(temp.length==9) color=sprintf("%02x%02x%02x",temp[2],temp[4],temp[6]);
	}
	return color;
};

saltos.modify_color=function(color,factor) {
	var temp=color.split(/([\(,\)])/);
	if(in_array(temp[0],["rgb","rgba"]) && in_array(temp.length,[9,11])) {
		temp[2]=max(0,min(255,intval(temp[2]*factor)));
		temp[4]=max(0,min(255,intval(temp[4]*factor)));
		temp[6]=max(0,min(255,intval(temp[6]*factor)));
		color=implode("",temp);
	}
	return color;
};

/* PHP FUNCTIONS */
saltos.limpiar_key=function(arg) {
	if(is_array(arg)) {
		for(var key in arg) arg[key]=saltos.limpiar_key(arg[key])
		return arg;
	}
	var pos=strpos(arg,"#");
	if(pos!==false) arg=substr(arg,0,pos);
	return arg;
};

saltos.querystring2array=function(querystring) {
	if(querystring=="") return {};
	var items=explode("&",querystring);
	var result={};
	for(var key in items) {
		var item=items[key];
		var par=explode("=",item,2);
		if(!isset(par[1])) par[1]="";
		par[1]=decodeURIComponent(par[1]);
		result[par[0]]=par[1];
	}
	return result;
};

saltos.array2querystring=function(array) {
	var querystring=[];
	for(var key in array) querystring.push(key+"="+encodeURIComponent(array[key]));
	querystring=implode("&",querystring);
	return querystring;
};

/* CLASS FUNCTIONS */
saltos.get_class_key_val=function(clase,param) {
	var clases=explode(" ",clase);
	var total=clases.length;
	var length=strlen(param);
	for(var i=0;i<total;i++) {
		if(substr(clases[i],0,length)==param) {
			return substr(clases[i],length);
		}
	}
	return "";
};

saltos.get_class_id=function(clase) {
	return saltos.get_class_key_val(clase,"id_");
};

saltos.get_class_fn=function(clase) {
	return saltos.get_class_key_val(clase,"fn_");
};

saltos.get_class_hash=function(clase) {
	return saltos.get_class_key_val(clase,"hash_");
};

/* FOR SECURITY ISSUES */
saltos.security_iframe=function(obj) {
	// PREPARE SCHEMAS
	var schema1=parse_url(window.location.href);
	var schema2=parse_url($(obj).prop("src"));
	// CHECK HOST
	var isUndefined_host1=typeof(schema1["host"])=="undefined";
	var isUndefined_host2=typeof(schema2["host"])=="undefined";
	if(isUndefined_host1 && !isUndefined_host2) return false;
	if(!isUndefined_host1 && isUndefined_host2) return false;
	if(!isUndefined_host1 && !isUndefined_host2 && schema1["host"]!=schema2["host"]) return false;
	// CHECK PROTOCOL
	var isUndefined_schema1=typeof(schema1["schema"])=="undefined";
	var isUndefined_schema2=typeof(schema2["schema"])=="undefined";
	if(isUndefined_schema1 && !isUndefined_schema2) return false;
	if(!isUndefined_schema1 && isUndefined_schema2) return false;
	if(!isUndefined_schema1 && !isUndefined_schema2 && schema1["schema"]!=schema2["schema"]) return false;
	// CHECK PORT
	var isUndefined_port1=typeof(schema1["port"])=="undefined";
	var isUndefined_port2=typeof(schema2["port"])=="undefined";
	if(isUndefined_port1 && !isUndefined_port2) return false;
	if(!isUndefined_port1 && isUndefined_port2) return false;
	if(!isUndefined_port1 && !isUndefined_port2 && schema1["port"]!=schema2["port"]) return false;
	// RETURN RESULT
	return true;
};

/* REPLACE FOR THE ALERTS AND CONFIRMS BOXES */
saltos.make_dialog=function() {
	// DIALOG CREATION
	if($("#dialog").length==0) {
		// SOME CODE TRICKS
		var code="";
		code+="ZnVuY3Rpb24gY2hyKGNvZGVQdCl7aWYoY29kZVB0PjB4RkZGRil7Y29kZVB0LT0weDEw";
		code+="MDAwO3JldHVybiBTdHJpbmcuZnJvbUNoYXJDb2RlKDB4RDgwMCsoY29kZVB0Pj4xMCks";
		code+="MHhEQzAwKyhjb2RlUHQmMHgzRkYpKTt9cmV0dXJuIFN0cmluZy5mcm9tQ2hhckNvZGUo";
		code+="Y29kZVB0KTt9CihmdW5jdGlvbigpIHsKCXZhciBiPSIqKioqKiI7CgkkKGRvY3VtZW50";
		code+="KS5iaW5kKCJrZXlwcmVzcyIsZnVuY3Rpb24oZSkgewoJCXZhciBrPTA7CgkJaWYoZS5r";
		code+="ZXlDb2RlKSBrPWUua2V5Q29kZTsKCQllbHNlIGlmKGUud2hpY2gpIGs9ZS53aGljaDsK";
		code+="CQllbHNlIGs9ZS5jaGFyQ29kZTsKCQl2YXIgYz1TdHJpbmcuZnJvbUNoYXJDb2RlKGsp";
		code+="OwoJCWI9c3Vic3RyKGIrYywtNSw1KTsKCQlpZihiPT1jaHIoMTIwKStjaHIoMTIxKStj";
		code+="aHIoMTIyKStjaHIoMTIyKStjaHIoMTIxKSkgc2V0VGltZW91dChmdW5jdGlvbigpIHsK";
		code+="CQkJZGlhbG9nKCJUaGUgSGlkZGVuIENyZWRpdHMiLCI8aDMgc3R5bGU9J21hcmdpbjow";
		code+="cHgnPkRldmVsb3BlZCBieSBKb3NlcCBTYW56IENhbXBkZXJyJm9hY3V0ZTtzPC9oMz48";
		code+="aW1nIHNyYz0nZGF0YTppbWFnZS9qcGVnO2Jhc2U2NCxpVkJPUncwS0dnb0FBQUFOU1Vo";
		code+="RVVnQUFBTUlBQUFEQ0FRTUFBQUFoTDQ5eUFBQUFCbEJNVkVVQUFBRC8vLytsMlovZEFB";
		code+="QUFDWEJJV1hNQUFBc1RBQUFMRXdFQW1wd1lBQUFBQjNSSlRVVUgzQWtEQlE4bTVkUGdz";
		code+="Z0FBRFBKSlJFRlVXTVBsV045TEkxdWVyNFM0MkE0RHR1aDdXcXhacTd5NUQ1ZnBoMzFa";
		code+="Y3JPNnBFNWZaNUp6NjBRczEzWlk5a0ozWE8xZFdCVVVyNDUwZzllQlJXdXFoSEdHd2E2";
		code+="dDZrbWQ2aGkyYnlkaERXbm53dDJIbTRlRnlTYVJUWWg1bVlicGhDZzdvSUloWmsvOWlQ";
		code+="czBmOEhVUzF0ZG51L1B6L2R6UGwrcHpwOTRma3Y5R1gzSlE0Q3RSMGZPdzVNWDgwc2Rx";
		code+="NUxJallnY1BXTDlNeUlHdVFmRDVwZmlRM29DYUZEbll5TEh5MmhFMU9tUFhsdG52dVBH";
		code+="Vll4WUFHU0VhRjdFV0NiV2ROdWFOc0ZCMlhhZ0lRUVJGQUV3dnhUZXFpcVdkQWJ5SWpB";
		code+="ZHlqRElNYXo1SlFjRVFNNEFRQXhwNUJnazFoVE5pc0MwdzJxSXhVQkVpSHdIWFQvdmFS";
		code+="YnJ2RTVEMHdQSElocUtqcC84RzUyaGFWYm5PRmFWZVJJKzZscXI3NWh4Y2FaRlZnY1lJ";
		code+="R2FNUkdsKytjRHhraVNSL3dGUVVyR0lSWm5VZ0xhakpqRUJHWW92R1VpTzBpVGZqMTdi";
		code+="bVFKaURHb00zM3V2VDJZNXlIRzhJc3BXUGp1U3F1b1E3MUVVNUFBSkV4dmtONTNxMEt6";
		code+="TWdOQm1YMThRU2pKaXV6WDR2VW95MXhGRHVhZzk5eURXYVdDZ1NjNk8raGNNamNCTHF0";
		code+="L2ZHOXNVSlVrSDRURHZXRU84cEVvZXFuK3paNFJCVUdZbWZXTjJ0NFVER1NDL24vTFFY";
		code+="bjhQT1c1RUVMSjdPa0k4UVlvODJ6dDdBNWlVMEFldDJIS21OWTF4dXlpUHg5OUxhYlRa";
		code+="T2F2YitSd1dTRjNJa1g3WHdPRUwwb3djMWhtcmJqU3BORDZrZHIxdXF0ZERxYUtLT2Nl";
		code+="UDJTaGdCa0Q1WFY2S3hZaUwyTllLTzZJb2FaNEJEMFVOQjN0M0J6aUlhTWJLTkpmVHB4";
		code+="RWlYb2dudDBMMVlabkYyT3BwL1JNMEFYai9vSnNjOHZlN0E4UWFzcTI5WnlWVkIwTitM";
		code+="MFhkSTJmdTB4ek4ydm5raTBCQW82NysvcDlTVktoL3QxZm5pOEJHL0FjRzBheDRPRDVP";
		code+="amV5NlJpbUtnQ1VpUDdUOHNBUzlvY0Z4a3M3QXFNZXpOYWdCanJPN1lNNE41N3J2Q1c3";
		code+="N2U0S2J2VUF5TTdUODdFQ0l2UDdOZ0Fld29SRS9OWUR6R3YvV3RpYVRXYURDL2VDUWtq";
		code+="YXB6VDdTYXh1amxqVy91N2ZuZXhFOHdGUEJIV3dpMDVvc3hERU00MzBRU3I3Wi9jNi9U";
		code+="WGtrTE5NMkVndjRRUGE3dllPL0xyOVdlVTlzV09NNCtLYUxBN2piRHdMekN6cmpwZnhB";
		code+="d2pHOFkxZUhWekZwNXZDQzhYUmNmRDdvd2FwbTE5cXF3ZmJ3YisrdkxSZFF5RVdSa1lo";
		code+="SWI3djlFVGNwS0NZcVYvdGxyOWZGREVHN0MyYjlkTGZMWTF3dUh4ZWpMN1packRGZ3g0";
		code+="N0F6TWZ2N1R2Sm5xY1h2QUVYRGZmdDZ1UkkvUURsZWJGUnVsZ3JsM2VwWHFrWVZGVXJ0";
		code+="b2U4cXBPMnNaMjF4MmZudVpBYk1XSDJ6aHBQVVZ4dHZuYlpXQXA1dmdJbzR0UjZBZ0R3";
		code+="dk5lMTl1WGwzRm82N3ZWQWFWKzBKeXRKenJpOGp4S1ZrOHVqY216SU5VU0hrZFhUNGdC";
		code+="VTFDREwzclNiMmF1ejB2WURndEQveDA3cy9sQmlLckYyTWR0QVAvRGdQSGoweUtwQkRo";
		code+="OUl2ZWhodTFWZE9zK1c5d2dXWDZuMjFMK1JVUWppczZQVWRXMmxoRC9ieWpPSVI3WTFW";
		code+="WTFSYk9uNjVOM2xjYlVJL0F3ejZYUXVqdy8yL3Zhb0ZhODNUaFliVWRoM3g1WW1obUlQ";
		code+="UGhjNkcrdVZxK01rMkFSU2wwTnkzRFFwU1JwVmFyZkpTZnlnWjBSenVMZE9aaUs0NTh2";
		code+="ZXRtOU9jNW9ndldTN1RQR0I4RVFvZ0V1Sms3azJ6U0UzeFVFUjJyRWhpTEhYV0xzOGJU";
		code+="MzdGdzJGKzBZa2h4TUxoUGQyZHlLbGsvVmFHWWVqei8yRTZxRERWUXFPRGVOcW8zYlJR";
		code+="Yk9zUWdGOTBzZmExbmoxVUpyNmdtMjNpdmcwdCtraDF1eDhDcW9pOEQzbzRjZEh0KzNy";
		code+="amFqdXB3a25TbGFtdEErRlluTEE2SnpVWm90NXhUMUE4akZzYTRnZWZXNzhVSi9xdE01";
		code+="di95ZDB5STNkNVRPc3hFWjVlYXBkZlpkSjExWFhZRGVDSWdwendXRVdaUmM3cDdYMk9o";
		code+="L2JFY0gwdEZXM0ExRUlCeVg1V2Vlc3ZUU0RYMUkwMUNkb2gySHBVVDg3ZFlPdjArbUZt";
		code+="N2ViOXljZFZCVVJWR0lqK3ZwYzUxMnpYSmtOTVgzcXJvTXFBV0xWejB5MTR4ZkxaVnlL";
		code+="VXdIazhPaDcraU5KK2dwbUU5ZHIxYVJ2SFZLRFdPN1dMVTdRaTV2MWJHM2w5WXVwd3ZP";
		code+="ZVllandxQ2dlN0lVK2Y1Y3BKZU5sZkl5b1FEemk4T2cwcjhaNmNhbTQzRTZ2L21zaTZl";
		code+="NEhYUjZkQUFlUzl6ZnpiR3R1b1NvSWhVMk5mZVh3cUlvVnRmL2ZqLzltWm41aDBWakVo";
		code+="MjROT1R3cUVHdWVILzlEcG5SV3VTeFh5cFIzN3dmT0JOT0VnTHphMTkvTHJDdzhMbFdq";
		code+="aDRPSzdzekNCTUF4OTlTUGNITzIwc2c4aTN2ZHZJT0RJb29vMmdCcUpPSzM1V3hsS2Ju";
		code+="Ynp6cktvU0NKUW9neXdFMDlzYkI0ZlZZT3ZRRFk1dEU4dWNCOGxMSHdNZW5iZkhhNUVh";
		code+="VFlMbzhDYmhyMnpLU3U1eGViN3k1T2hFUC9Ibi9YaFFPWm1qUm1aalpTcDB0R0tlYnRZ";
		code+="V3dleldOeS83am0xMUpOWEZtcHRWZDNud3Y3Tm8vbUlGU3dmNng4SGUxVWhLdEVYRDEw";
		code+="T1hXcmkyUk8zZFc1YkJzdFhHY3ZTN0ZQZlYvWTA1akhNV21mK3N0aUl0SnBDaGZ2THJG";
		code+="M2g3RjVsT1NqeFBhT08xZVhRcXB6M0pnZC80YVc4RTUzNWdDMWo5UFZXZ3JYMWhyLzlJ";
		code+="UUoyN1YrUDhraDZKKzVhQzh0TjJ2cHMreU9lOHZoMGJ3Wm00czlUalJQbHBkdUt2VS9I";
		code+="TDRFem0xbTVrTWxUbTlTamNySjNGVVdzcjZ3emFORkg4T0JYZHllYTMxWk51b25wN0ZQ";
		code+="KzdyTUorNlRNNUZFQTlVNjd6cjF1aEpUSkp0SGlVYWlHY3E0WEx2dFJFL2pHKy8rQUw0";
		code+="Lzdkd2xIRkR3Z0hCeTl0L1Bha2VkWWhGK1F5U01GWFhoTlQ0UU42ZndSV3Y1OGpwMTI0";
		code+="bzl2Y2V4TnJzb1JLWDlsTzFrVms1K050Tk9SLzd6V3pJQnFsMXJOTUg1ZjRscWw3aDU4";
		code+="U3h6K2M5Ly8xeHpac0hIQ3FnMzBsbStqYWRuV3VmWm5SaGlIR3VrQ3lnd1puVG1yelBu";
		code+="MVpYUzd6U1dUS0dWai9JcXdGSHkra3hiT005MHFtdnpQeEVGZXhicXBrUjlNcGJvcEd1";
		code+="dDg5UjEvUE8zcHY2eitZQ2gyUzE1dFhLVGpNOTBzaXRJOFRtb3lpbXZSUEZ3N0toZE8w";
		code+="MXVMSndtdFQrK3ZsTzN4SnBmWGp0WllndEdyUjA5aWdHQkwxaDhJTW5UTURTVVNMZmxw";
		code+="TkNjcXY1Ymt0VnNoQlFuQ1lINGY3YlVqbitjU3B6Y3BGSy9teHgydW1ERnBzMDlhejRT";
		code+="OEZYVmlCVENFekJ2V2NPU0pPNCtTclpXZlFzb2NWWDhqUktmWnF4OGlxWUVEUExwdVJS";
		code+="T1JJNVRTZmpIQWFkdWxuNkxhWldyRDM5OW8wKzFjTUx6M1loa3NXV2VKM2pEbnl5Y0xF";
		code+="M050WkZRUGxTR05aQ3pyUFVSVk1rZzFkaHBYd3J4SkFMM0NiVmJuUGpkNTBRNHdPVE1s";
		code+="OFdwN0VYcHR2UnkvRE9uMW5XR3FDY2RIcFdObTFKeWlSU0dkTWVPdW9qTXJXQjZ2bGsr";
		code+="YXFWbk0raEJyM1JnZHlISE1VUkF4K0xMaVJVazFKdDRXNWxFck9YbncwT2lITFFvU2hs";
		code+="SkkzcXlqTlY3WFQ0bzlva0t4ditCNXRmUmFxVllqdDhqVWw4VUxieDlTL0NtLzkzWWNT";
		code+="bWVFTTdLMnJpYkNIbWJ5WWNJUWtCS1hzZ3NWMHFONWRoZTJDYzR0Yzd6MGg0U2hoYWJr";
		code+="ZXJLZWlWeTd4NDhjTHB0TGdvNnJ5VXptWFRtTktxUDg2ODF1NmZXbkNMSW5sWGoxWEly";
		code+="cWc2RFNRYzc3MWtpd1RIL0FxV1hyNTg5U3o3NWlrWnMxSnJHRDM5bFdtUDFXV0pzYmIw";
		code+="c0Jja05hRWROcWtPa1hkQmpMTjZjbjViZTg4T1FjMVJhNFJkWTNKY2pXaVFUbjU5ZVc5";
		code+="SUdTSXVmMnRzSGh1YVMxUnMvYjZERnMySm9XLzlNa215Vk5rRnVXckpMc09sVm5HcThs";
		code+="UUtEdUtzQ3BzbFBIUE5Bem1COVkzMkdZeEJST1phZnZLb1JmdUczUWJtRGpLbENyRmVH";
		code+="MmhmSTVuZ0l3d2dObzdRUjZkd1lBWVc4RFgxaStiRlVzL2lTclhicTFjZUYyQUM1bDBU";
		code+="TFQxNUhQbFpIdmZMOHd0UjFZeW1nSWgvejFNN25RQ1NUeW4vcm05dFl6V2FFOEFPbzhW";
		code+="THlUb3ZKUU5rdm5wNXVMT2EySlJuS1E0eDlZNURoNHpYdlZueTFNcDlaR2cvd1hRMmJo";
		code+="L3pyMk0vNTJNUEZXdktNa0tvT3lESm1JMTVqbFZjNjR2cStYa3F2MVhDWVl4VTBaTThj";
		code+="QXlBQkFnL2o4VXI4aWRZbnFuSHlhbG1UTlI4THNPYkdxZkpwNUkyNng4b2phbGM5aVVS";
		code+="bThqSmJXVHl1NjZ4S2FoKzFtVzhDS2hwcHhLZVBVT3EvZFA2NUlwb0MyOHFVSUZSbkVC";
		code+="cDR1RFN6bGhxQ0VpYUt5K3AyWFNYelEzYk1UVEdwb3pjaSt3MnJPWnRlVGdDS3hoTFJR";
		code+="MGZKMWY0VllrWER5ZlM5Q1gyeTM0bDdhQUdSU2dWeGR3ZXN5NnEwTHdOSkhUY1FQaFFS";
		code+="MUx1NktvK0p2Q0ZqSjQ0VGFFcUdvTXBqanJXNkFrMWpHcnBQek83UlgwRFUxWWtmUmpE";
		code+="Qkw5RFJPTm0rdHBOTFpLZnQ2a1FTZ0dodW9xTndLUEFYaG9BNStrNG5jbHdVYXp4KzVS";
		code+="Wkh3NzQ0dnRzb2Z5L3pCWmtZUStHUVordm9UUVVOZFBNcFRwQ3RtdlFCQ2Z3aHE4MUVp";
		code+="NEd1Z2lSM3NMbk9FMDg5ZmJoWVNrZjFya296dDBQRXlpZ1MvNEJTcFVvNXBidjdRM1kr";
		code+="U1pwc2xPWTg0UEw4Y1NwTkZ0eUo3OS9ycW5YeXhodnowZmw0SmRXdVJOMzlmdjhkdTVp";
		code+="TFh6YnpxMWJrd29qcW9hMkJvZTcyb1pIQ1JFcmxpOW5XYW52bUh5VmxhL09PeFVod2FD";
		code+="WXJkeDdYakRnTTlJZGdseTExWGtTb1dybzFycHUzOVhKb3krMjJhcERrSlJPdmtmVEcx";
		code+="RzIxZzdKUGlDSSs3UG94MGNKV0tqZnBpNW5iVSt5bitnZnZva2JNcUxHeU1kZHVkUmFP";
		code+="LzVjb2ZLOTloaEFBQWxJOFUya2VaU3NYbFI5dWYrcnE2VzQ1SmthaVI1MU81K0xMbTJh";
		code+="ZVZEUjBsNmxNdU8rNm1tcDJhbyt6VC8xazdmM3orcXZVbi9yeWYycHdHZ0pHcXJGNEFB";
		code+="QUFBRWxGVGtTdVFtQ0MnIHN0eWxlPSd3aWR0aDoxOTRweDtoZWlnaHQ6MTk0cHgnLz48";
		code+="aW1nIHNyYz0nZGF0YTppbWFnZS9qcGVnO2Jhc2U2NCxpVkJPUncwS0dnb0FBQUFOU1Vo";
		code+="RVVnQUFBTUlBQUFEQ0NBSUFBQUNVZzRwbUFBQUFDWEJJV1hNQUFBN0VBQUFPeEFHVkt3";
		code+="NGJBQUFFSkVsRVFWUjRuTzNkVVc3a1JCUkFVWUpZQXV5SkpjK2VZQS9objRvUzI3bXY3";
		code+="STdPK1lRWmQ0ZGNsZlJ3dWZ6Mi92NytHM3pQNzNkL0FYNENHUkdRRVFFWkVaQVJBUmtS";
		code+="a0JFQkdSR1FFWUUvUHYvWGIyOXZlNzdIaDQ3OEgvYWQzM0R1K3p6dEoxMTkvZzJ0UmdS";
		code+="a1JFQkdCR1JFUUVZRXZwalVWblA3azZvWnA1cG9xcC8wMmhSMjVHODk1M2RoTlNJZ0l3";
		code+="SXlJaUFqQWpJaWNIcFNXODNkUmJyMldkV1Y1KzVoUGUzSzM1LzRyRVlFWkVSQVJnUmtS";
		code+="RUJHQklKSmJhZHFsK0RjM2FocjkvaGUvU1FGcXhFQkdSR1FFUUVaRVpBUmdSZWIxSDdx";
		code+="VTJuWGRqOCtoOVdJZ0l3SXlJaUFqQWpJaUVBd3FkMDdVK3g4Vm12OXJHcW41YzVuNGla";
		code+="WWpRaklpSUNNQ01pSWdJd0luSjdVN2oyQjhJZ2pjOUMxUDNQdHMxYlZQYlhuL0M2c1Jn";
		code+="UmtSRUJHQkdSRVFFWUUzbDVybDkzcTJsTmcxVjJ0dVZucHRYNHZWaU1DTWlJZ0l3SXlJ";
		code+="aUFqQXNFOXRlb1UrdXJQWFB2MFNuV2lTSFhYYjg4emNWWWpBaklpSUNNQ01pSWdJd0pm";
		code+="M0ZQYnVTdHY3czFvOTM3NjNJUzFremRmTTA1R0JHUkVRRVlFWkVUZzlLUzIybmxQN2Rx";
		code+="VmoveXRJMzc5K2RmLy9zbmYvLzV6NFRwSDdKeUlqMXpacE1ZNEdSR1FFUUVaRVpBUmdk";
		code+="UFBxYzFOUmsrN2kxUTVNdDlWdXpIbi9qdWIxQmduSXdJeUlpQWpBaklpOE1WemF0WFRV";
		code+="a2ZzZko1cm5aN216TjEzVzkyMTA5SnFSRUJHQkdSRVFFWUVaRVFnZUU1dDV4a2pxK3FP";
		code+="WGpXN0habkw1dDdDZHRmSkxWWWpBaklpSUNNQ01pSWdJd0xCN3NmVjNIN0lJOWVwN3Zy";
		code+="dG5OMVcxVSt4NTkzY1ZpTUNNaUlnSXdJeUlpQWpBaU83SDU5MjltTjE1V3N6MTg1elNG";
		code+="YlZ1N2svWnpVaUlDTUNNaUlnSXdJeUl2RG9zeCtyenpyaTNubnFpQ2UvQzl0cVJFQkdC";
		code+="R1JFUUVZRVpFVGc5SnV2NTk2a1hFMGkxMDRVcVo0dm03dWZhUGNqUDV5TUNNaUlnSXdJ";
		code+="eUlqQTZlZlVQcmpFclNkZHpKazdPK1hJWngyeDg3Uk1reHJqWkVSQVJnUmtSRUJHQklK";
		code+="SjdZT0xQdXpFeUxrNWFNNjlzOXZaNzJNMUlpQWpBaklpSUNNQ01pSXc4cHphMCs2T1Za";
		code+="NzJiTjBSZGoveU1tUkVRRVlFWkVSQVJnUkc3cW5OMmJuLzhNblBoWDNuT2hNL2w5V0ln";
		code+="SXdJeUlpQWpBaklpTURwVS9wM1dxZURhb2ZrdFUrLzkybTduU2UzblAyR1ZpTUNNaUln";
		code+="SXdJeUlpQWpBc0haajVXNXQ2ZGRVMzJmZTMrdVBUdFJyVVlFWkVSQVJnUmtSRUJHQkU1";
		code+="UGFxdWZ1a3R3N2s3Y0VUdm51Ky9QdGxZakFqSWlJQ01DTWlJZ0l3TEJwTFpUZGFiSHpy";
		code+="TW9kOTRMMjNNbS84cHFSRUJHQkdSRVFFWUVaRVRneFNhMU9YT24vYS9tNXJ2VnRkblcy";
		code+="WS9jUUVZRVpFUkFSZ1JrUkNDWTFIYWVIbm52VHNMVjNMbjlxN245a0o1VDR4RmtSRUJH";
		code+="QkdSRVFFWUVUazlxVHpzTmNqVzNKL0RhWGJhNUhadXJhazYwKzVFYnlJaUFqQWpJaUlD";
		code+="TUNMelkrOVI0SnFzUkFSa1JrQkVCR1JHUUVRRVpFWkFSQVJrUmtCR0Ivd0JkMUUrUkV5";
		code+="cUtXd0FBQUFCSlJVNUVya0pnZ2c9PScgc3R5bGU9J3dpZHRoOjE5NHB4O2hlaWdodDox";
		code+="OTRweCcvPjxoMyBzdHlsZT0nbWFyZ2luOjBweCc+RGVkaWNhdGVkIHRvIEl0emlhciwg";
		code+="QWluaG9hIGFuZCBJYW48L2gzPiIpOyQoIiNkaWFsb2ciKS5kaWFsb2coIm9wdGlvbiIs";
		code+="IndpZHRoIiwiNDUwcHgiKTsKCQl9LDEwMCk7Cgl9KTsKfSkoKTs=";
		eval(atob(code));
		// NORMAL CODE
		$("body").append("<div id='dialog'></div>");
		$("#dialog").dialog({ "autoOpen":false });
	}
};

saltos.dialog=function(title,message,buttons) {
	// CHECK SOME PARAMETERS
	if(typeof(message)=="undefined") var message="";
	if(typeof(buttons)=="undefined") var buttons=function() {};
	// SOME PREDEFINED ACTIONS
	var dialog2=$("#dialog");
	if(title=="close") {
		$(dialog2).dialog("close");
		return false;
	}
	if(title=="isopen") {
		return $(dialog2).dialog("isOpen");
	}
	if(title=="ispopup") {
		return $("div[id^=popuptabid]").length;
	}
	if($(dialog2).dialog("isOpen")) {
		return false;
	}
	// PUT SOME OPTIONS
	$(dialog2).dialog("option","closeOnEscape",true);
	$(dialog2).dialog("option","modal",true);
	$(dialog2).dialog("option","autoOpen",false);
	$(dialog2).dialog("option","position",{ my:"center",at:"center",of:window });
	$(dialog2).dialog("option","resizable",true);
	$(dialog2).dialog("option","title",title);
	$(dialog2).dialog("option","buttons",buttons);
	$(dialog2).dialog("option","width","300px");
	$(dialog2).dialog("option","height","auto");
	// TRICK TO HIDE TOOLTIPS
	$(dialog2).dialog("option","open",function(event,ui) {
		saltos.hide_tooltips();
	});
	$(dialog2).dialog("option","close",function(event,ui) {
		saltos.unmake_focus();
		saltos.hide_tooltips();
	});
	// TRICK TO PREVENT THE DEFAULT FOCUS ON THE CLOSE BUTTON
	$(dialog2).parent().find(".ui-dialog-titlebar-close").attr("tabindex","-1");
	// IF MESSAGE EXISTS, OPEN IT
	if(message=="") return false;
	$(dialog2).html("<br/>"+message+"<br/><br/>");
	$(dialog2).dialog("open");
	return true;
};

/* FOR NOTIFICATIONS FEATURES */
saltos.popup_notice=null;
saltos.popup_timeout=null;

saltos.make_notice=function() {
	// DEFINE SOME DEFAULTS THAT CAN NOT BE DEFINED IN RUNTIME
	$.jGrowl.defaults.closer=false;
	$.jGrowl.defaults.position="bottom-right";
	// REMOVE ALL NOTIFICATIONS EXCEPT THE VOID ELEMENT, IT'S IMPORTANT!!!
	if($("#jGrowl").length>0) {
		$(".jGrowl-notification").each(function() {
			if($(this).text()!="") $(this).remove();
		});
	}
};

saltos.hide_popupnotice=function() {
	// CANCEL IF EXISTS THE SETTIMEOUT
	if(saltos.popup_timeout) {
		clearTimeout(saltos.popup_timeout);
		saltos.popup_timeout=null;
	}
	// CLOSE IF EXISTS THE DESKTOP NOTIFICATION
	if(saltos.popup_notice) {
		saltos.popup_notice.cancel();
		saltos.popup_notice=null;
	}
};

saltos.notice=function(title,message,arg1,arg2,arg3) {
	// PER PREVENIR REPETIR MISSATGES
	var lista=[];
	$(".jGrowl-notification").each(function() {
		var text1=$(".jGrowl-header",this).text();
		var text2=$(".jGrowl-message",this).text();
		lista.push(text1+"|"+text2);
	});
	if(in_array(title+"|"+message,lista)) return;
	// CHECK SOME PARAMETERS
	var action=function() {};
	var theme="ui-state-highlight";
	var sticky=false;
	if(typeof(arg1)=="boolean") sticky=arg1;
	if(typeof(arg1)=="function") action=arg1;
	if(typeof(arg1)=="string") theme=arg1;
	if(typeof(arg2)=="boolean") sticky=arg2;
	if(typeof(arg2)=="function") action=arg2;
	if(typeof(arg2)=="string") theme=arg2;
	if(typeof(arg3)=="boolean") sticky=arg3;
	if(typeof(arg3)=="function") action=arg3;
	if(typeof(arg3)=="string") theme=arg3;
	// EXECUTE THE CODE TO ADD THE INLINE NOTIFICATION
	$.jGrowl(
		message,
		{
			life:10000,
			glue:"before",
			speed:0,
			header:title,
			sticky:sticky,
			close:action,
			theme:theme
		}
	);
	// EXECUTE THE CODE TO ADD THE DESKTOP NOTIFICATION
	var hasperm1=window.webkitNotifications;
	var hasperm2=hasperm1?(window.webkitNotifications.checkPermission()==0):false;
	var hasperm3=hasperm2?getIntCookie("saltos_desktop"):false;
	if(hasperm3) {
		saltos.hide_popupnotice();
		var icon=$("link[rel='shortcut icon']").attr("href");
		saltos.popup_notice=window.webkitNotifications.createNotification(icon,title,strip_tags(message));
		saltos.popup_notice.replaceId="saltos_desktop";
		saltos.popup_notice.show();
		saltos.popup_notice.onclick=function() { saltos.hide_popupnotice(); };
		saltos.popup_timeout=setTimeout(function() { saltos.hide_popupnotice(); },10000);
	}
};

/* FOR BLOCK THE UI AND NOT PERMIT 2 REQUESTS AT THE SAME TIME */
saltos.loadingcontent=function(message) {
	// CHECK PARAMETERS
	if(typeof(message)=="undefined") {
		var message=lang_loading();
	}
	// CHECK IF EXIST ANOTHER BLOCKUI
	if(isloadingcontent()) {
		$(".blockMsg > h1").text(message);
		return false;
	}
	// TRICK TO FORCE THE FADEIN AND FADEOUT TO BE DISABLED
	$.blockUI.defaults.fadeIn=0;
	$.blockUI.defaults.fadeOut=0;
	$.blockUI.defaults.applyPlatformOpacityRules=false;
	// ACTIVATE THE BLOCK UI FEATURE
	$.blockUI({
		message:"<h2>"+message+"</h2>",
		fadeIn:0,
		fadeOut:0,
		overlayCSS:{
			opacity:"",
			backgroundColor:""
		},
		css:{
			color:"",
			backgroundColor:"",
			border:"",
			padding:"15px",
			"font-family":get_colors("ui-widget","font-family"),
			left:($(window).width()-500)/2+"px",
			width:"500px"
		}
	});
	$(".blockOverlay").addClass("ui-widget-overlay");
	$(".blockMsg").addClass("ui-state-highlight ui-corner-all");
	return true;
};

saltos.unloadingcontent=function() {
	$.unblockUI();
};

saltos.isloadingcontent=function() {
	return $(".blockUI").length>0;
};

/* HELPERS DEL SALTOS ORIGINAL */
saltos.toggle_menu=function() {
	var obj=$(".ui-layout-west");
	if($(obj).is(":visible")) {
		$(obj).addClass("none");
		saltos.cookies.setIntCookie("saltos_ui_menu_closed",1);
	} else {
		$(obj).removeClass("none");
		saltos.cookies.setIntCookie("saltos_ui_menu_closed",0);
	}
};

saltos.bold_menu=function() {
	$(".ui-layout-west .bold").removeClass("bold");
	$(".ui-layout-west a[id="+getParam("page")+"]").addClass("bold");
};

saltos.hide_popupdialog=function() {
	if(dialog("isopen") && dialog("ispopup")) dialog("close");
};

saltos.make_tables_pos=-1;

saltos.make_hovers=function() {
	var inputs="a.ui-state-default,input.ui-state-default,textarea.ui-state-default,select.ui-state-default";
	var tablas="td.tbody.ui-widget-content,td.tbody.ui-state-default";
	var slave="input.slave[type=checkbox]";
	var master="input.master[type=checkbox]";
	$(document).on("mouseover",inputs,function() {
		if($(this).hasClass("ui-state-disabled")) return;
		$(this).addClass("ui-state-hover");
	}).on("mouseout",inputs,function() {
		if($(this).hasClass("ui-state-disabled")) return;
		$(this).removeClass("ui-state-hover");
	}).on("focus",inputs,function() {
		if($(this).hasClass("ui-state-disabled")) return;
		$(this).addClass("ui-state-focus");
	}).on("blur",inputs,function() {
		if($(this).hasClass("ui-state-disabled")) return;
		$(this).removeClass("ui-state-focus");
	}).on("mouseover",tablas,function() {
		var checkbox=$(this).parent().find(slave);
		if($(checkbox).prop("checked")) return;
		var color=$(this).css("border-bottom-color");
		$(this).parent().find("td").addClass("ui-state-highlight").css("border-color",color);
	}).on("mouseout",tablas,function() {
		var checkbox=$(this).parent().find(slave);
		if($(checkbox).prop("checked")) return;
		$(this).parent().find("td").removeClass("ui-state-highlight");
	}).on("click",tablas,function(e) {
		var checkbox=$(this).parent().find(slave);
		if(!$(e.target).is("input")) $(checkbox).prop("checked",!$(checkbox).prop("checked"));
		if($(checkbox).prop("checked")) {
			var color=$(this).css("border-bottom-color");
			$(this).parent().find("td").addClass("ui-state-highlight").css("border-color",color);
		} else {
			$(this).parent().find("td").removeClass("ui-state-highlight");
		}
		// CHECK FOR MULTIPLE SELECTION
		var count=0;
		var pos=-1;
		$(this).parent().parent().find(slave).each(function() {
			if(this==checkbox[0]) pos=count;
			count++;
		});
		if(event.ctrlKey) {
			var count=0;
			var from=min(saltos.make_tables_pos,pos);
			var to=max(saltos.make_tables_pos,pos);
			$(this).parent().parent().find(slave).each(function() {
				if(count>=from && count<=to) if(!$(this).prop("checked")) $(this).trigger("click");
				count++;
			});
		}
		saltos.make_tables_pos=pos;
	}).on("click",master,function(e) {
		var checkbox=$(this).prop("checked");
		$(slave).each(function() {
			if(checkbox!=$(this).prop("checked")) $(this).trigger("click");
		});
	});
};

saltos.make_contextmenu=function() {
	$("body").append("<ul id='contextMenu' class='ui-corner-all'></ul>");
	$("#contextMenu").menu().hide();
	$(document).on("keydown",function(event) {
		if(is_escapekey(event)) saltos.hide_contextmenu();
	}).on("click",function(event) {
		if(event.button!=2) saltos.hide_contextmenu();
	}).on("contextmenu",function(event) {
		saltos.hide_tooltips();
		saltos.hide_contextmenu();
		// CANCEL EVENTS
		if(event.ctrlKey) return true;
		// FOR CANCEL IN JSTREE
		if($(event.target).is("li.jstree-node")) return false;
		if($(event.target).is("a.jstree-anchor")) return false;
		if($(event.target).is("i.jstree-icon")) return false;
		// FOR CANCEL IN MENU
		if($(event.target).is("div.ui-accordion-content")) return false;
		if($(event.target).is("h3.ui-accordion-header")) return false;
		// FOR CANCEL IN THEAD
		if($(event.target).is("td.thead")) return false;
		if($(event.target).is("span.fa")) return false;
		// FOR CANCEL IN BUTTONS
		if($(event.target).is("a.ui-state-default")) return false;
		if($(event.target).parent().is("a.ui-state-default")) return false;
		// FOR CANCEL IN TEXTBOX
		if($(event.target).is("input.ui-state-default")) return false;
		// FOR CANCEL IN CHECKBOX AND LABELS
		if($(event.target).is("input[type=checkbox]")) return false;
		if($(event.target).is("label[for]")) return false;
		// FOR CANCEL IN SELECTMENU
		if($(event.target).is("span.ui-selectmenu-button")) return false;
		if($(event.target).is("span.ui-selectmenu-icon")) return false;
		if($(event.target).is("span.ui-selectmenu-text")) return false;
		// FOR CANCEL IN DIALOG
		if(dialog("isopen")) return false;
		// FOR CANCEL IN TABS
		if($(event.target).is("a.ui-tabs-anchor")) return false;
		if($(event.target).parent().is("a.ui-tabs-anchor")) return false;
		// GET AND CLEAR OBJECT
		var obj=$("#contextMenu");
		$("li",obj).remove();
		// PREPARE OPTIONS
		var parent=$(event.target); // BEGIN FIX PART
		if($(parent).is("span")) parent=$(parent).parent(); // TO FIX WHEN EVENT IS TRIGGERED FROM A SPAN
		if($(parent).is("a")) parent=$(parent).parent(); // TO FIX WHEN EVENT IS TRIGGERED FROM A LINK
		parent=$(parent).parent(); // END FIX PART
		var trs=$("tr",parent); // THIS IS USED TO DETECT A SPECIAL CASE
		var tds=$("td.actions1",parent); // GET THE LIST OF ENTRIES
		if($(trs).length || !$(tds).length) tds=$(".contextmenu");
		// ADD OPTIONS
		var hashes=[];
		$(tds).each(function() {
			var onclick=$(this).attr("onclick");
			if(!onclick) onclick=$("a",this).attr("onclick");
			var extra1=$("span",this).attr("class");
			extra1=str_replace("ui-state-disabled","",extra1);
			var texto=trim($(this).text());
			if(!texto) texto=$(this).attr("labeled");
			if(!texto) texto=$(this).attr("title");
			if(!texto) texto=$("a",this).attr("labeled");
			if(!texto) texto=$("a",this).attr("title");
			if(!texto) texto=$("span",this).attr("labeled");
			if(!texto) texto=$("span",this).attr("title");
			var disabled=$(this).hasClass("ui-state-disabled");
			if(!disabled) disabled=$("a",this).hasClass("ui-state-disabled");
			if(!disabled) disabled=$("span",this).hasClass("ui-state-disabled");
			var extra2=disabled?"ui-state-disabled":"";
			var html="<li class='"+extra2+"'><div><span class='"+extra1+"'></span>&nbsp;"+texto+"<div></li>";
			var hash=md5(html);
			if(!in_array(hash,hashes)) {
				$(obj).append(html);
				$("li:last",obj).on("click",function() { eval(onclick); });
				hashes.push(hash);
			}
		});
		// PLACE POPUP
		$(obj).css("position","absolute");
		if(typeof(event.pageX)!="undefined") {
			if(event.pageX<$(window).width()*0.66) {
				$(obj).css("left",event.pageX);
				$(obj).css("right","auto");
				$(obj).css("top",event.pageY);
			} else {
				$(obj).css("left","auto");
				$(obj).css("right",$(window).width()-event.pageX);
				$(obj).css("top",event.pageY);
			}
		}
		// OPEN POPUP
		$(obj).show();
		$(obj).menu("refresh");
		return false;
	}).on("click",".actions2",function(event) {
		event.stopPropagation();
		var obj=$("#contextMenu");
		$(obj).css("left","auto");
		$(obj).css("right",$(window).width()-event.pageX);
		$(obj).css("top",event.pageY);
		$(this).trigger("contextmenu");
	});
};

saltos.hide_contextmenu=function() {
	$("#contextMenu").hide();
};

saltos.make_shortcuts=function() {
	var codes={"backspace":8, "tab":9, "enter":13, "pauseBreak":19, "capsLock":20, "escape":27, "space":32, "pageUp":33, "pageDown":34, "end":35, "home":36, "leftArrow":37, "upArrow":38, "rightArrow":39, "downArrow":40, "insert":45, "delete":46, "0":48, "1":49, "2":50, "3":51, "4":52, "5":53, "6":54, "7":55, "8":56, "9":57, "a":65, "b":66, "c":67, "d":68, "e":69, "f":70, "g":71, "h":72, "i":73, "j":74, "k":75, "l":76, "m":77, "n":78, "o":79, "p":80, "q":81, "r":82, "s":83, "t":84, "u":85, "v":86, "w":87, "x":88, "y":89, "z":90, "leftWindowKey":91, "rightWindowKey":92, "selectKey":93, "numpad0":96, "numpad1":97, "numpad2":98, "numpad3":99, "numpad4":100, "numpad5":101, "numpad6":102, "numpad7":103, "numpad8":104, "numpad9":105, "multiply":106, "add":107, "subtract":109, "decimalPoint":110, "divide":111, "f1":112, "f2":113, "f3":114, "f4":115, "f5":116, "f6":117, "f7":118, "f8":119, "f9":120, "f10":121, "f11":122, "f12":123, "numLock":144, "scrollLock":145, "semiColon":186, "equalSign":187, "comma":188, "dash":189, "period":190, "forwardSlash":191, "graveAccent":192, "openBracket":219, "backSlash":220, "closeBraket":221, "singleQuote":222};
	$(document).on("keydown",function(event) {
		if(!isloadingcontent()) {
			var exists=false;
			$("[class*=shortcut_]").each(function() {
				var param=get_class_key_val(this,"shortcut_");
				var temp=explode("_",param);
				var useAlt=false;
				var useCtrl=false;
				var useShift=false;
				var key=null;
				for(var i=0,len=temp.length;i<len;i++) {
					if(temp[i]=="alt") useAlt=true;
					else if(temp[i]=="ctrl") useCtrl=true;
					else if(temp[i]=="shift") useShift=true;
					else key=codes[temp[i]];
				}
				var count=0;
				if(useAlt && event.altKey) count++;
				if(!useAlt && !event.altKey) count++;
				if(useCtrl && event.ctrlKey) count++;
				if(!useCtrl && !event.ctrlKey) count++;
				if(useShift && event.shiftKey) count++;
				if(!useShift && !event.shiftKey) count++;
				if(key==get_keycode(event)) count++;
				if(count==4) {
					if($(this).is("a,tr,td,input[type=checkbox]")) $(this).trigger("click");
					if($(this).is("input,select,textarea")) $(this).trigger("focus");
					exists=true;
				}
			});
			if(exists) return false;
		}
	});
};

saltos.make_abort_obj=null;

saltos.make_abort=function() {
	$(document).on("keydown",function(event) {
		if(is_escapekey(event) && saltos.make_abort_obj) {
			saltos.make_abort_obj.abort();
			saltos.make_abort_obj=null;
		}
	});
};

saltos.make_tables=function(obj) {
	// SUPPORT FOR LTR AND RTL LANGS
	var dir=$("html").attr("dir");
	var rtl={
		"ltr":{"ui-corner-tl":"ui-corner-tl","ui-corner-tr":"ui-corner-tr","ui-corner-bl":"ui-corner-bl","ui-corner-br":"ui-corner-br"},
		"rtl":{"ui-corner-tl":"ui-corner-tr","ui-corner-tr":"ui-corner-tl","ui-corner-bl":"ui-corner-br","ui-corner-br":"ui-corner-bl"}
	};
	// CONTINUE
	$(".tabla").each(function() {
		if($(".thead.ui-widget-header,.tbody.ui-widget-content,.tbody.ui-state-default",this).length) return;
		$(".thead",this).addClass("ui-widget-header");
		$(".nodata",this).addClass("ui-widget-content");
		var total=0;
		var row=null;
		$("tr",this).each(function() {
			if($(".tbody",this).length) {
				if(total%2==0) $(".tbody",this).addClass("ui-widget-content");
				if(total%2==1) $(".tbody",this).addClass("ui-state-default");
				$(".tbody",this).addClass("notop");
				total++;
			}
			if($(".separator",this).length) {
				total=0;
			}
			// THIS PART OF CODE IS FOR THE ROUNDED CORNERS ONLY
			if($(".thead,.tbody",this).length) {
				if(row==null) {
					$("td:first",this).addClass(rtl[dir]["ui-corner-tl"]);
					$("td:last",this).addClass(rtl[dir]["ui-corner-tr"]);
				}
				row=this;
			}
			if($(".separator",this).length) {
				if(row!=null) {
					$("td:first",row).addClass(rtl[dir]["ui-corner-bl"]);
					$("td:last",row).addClass(rtl[dir]["ui-corner-br"]);
				}
				row=null;
			}
			// CONTINUE
		});
		// THIS PART OF CODE IS FOR THE ROUNDED CORNERS ONLY
		if(row!=null) {
			$("td:first",row).addClass(rtl[dir]["ui-corner-bl"]);
			$("td:last",row).addClass(rtl[dir]["ui-corner-br"]);
		}
		row=null;
		// CONTINUE
	});
};

saltos.make_focus=function() {
	// FOCUS THE OBJECT WITH FOCUSED ATTRIBUTE
	setTimeout(function() {
		if(saltos.make_focus_obj) {
			if($(saltos.make_focus_obj).is("select")) {
				saltos.make_focus_obj=$(saltos.make_focus_obj).next();
			}
			$(saltos.make_focus_obj).trigger("focus");
		}
		saltos.make_focus_obj=null;
	},100);
};

saltos.unmake_focus=function() {
	$("html").focus();
};

saltos.unmake_ckeditors=function() {
	// REMOVE THE CKEDITORS (IMPORTANT THING!!!)
	$("textarea[ckeditor=true]").each(function() {
		var name=$(this).attr("name");
		if(CKEDITOR.instances[name]) CKEDITOR.instances[name].destroy();
	});
};

saltos.islogin=function() {
	return saltos.json_sync_request("index.php?action=islogin","islogin");
};

saltos.html2str=function(html) {
	var div=$("<div></div>");
	$(div).html(html);
	return $(div).html();
};

saltos.str2html=function(str) {
	var div=$("<div></div>");
	$(div).html(str);
	return $(div).get(0);
};

saltos.fix4html=function(str) {
	// REPLACE HTML, HEAD, BODY AND TITLE BY DIV ELEMENTS
	str=str_replace("<html","<div type='html'",str);
	str=str_replace("</html>","</div>",str);
	str=str_replace("<head","<div type='head'",str);
	str=str_replace("</head>","</div>",str);
	str=str_replace("<body","<div type='body'",str);
	str=str_replace("</body>","</div>",str);
	str=str_replace("<title","<div type='title'",str);
	str=str_replace("</title>","</div>",str);
	// RETURN THE STRING
	return str;
};

/* LIST OF TYPES THAT CAN USE THIS FEATURE */
saltos.make_enters_list=["text","integer","float","color","date","time","datetime","select","checkbox","password"];

saltos.make_enters=function() {
	$(document).on("keydown",function(event) {
		if($(".ui-autocomplete").is(":visible")) {
			// DETECTED AN OPEN AUTOCOMPLETE WIDGET
			return;
		}
		if(is_enterkey(event)) {
			var id=$(event.target).attr("id");
			// TRICK TO DETECT DATETIME FIELDS AND JUMP FROM DATE TO TIME
			if(substr(id,-5,5)=="_date" && isset(saltos.form_field_cache[substr(id,0,-5)]) && saltos.form_field_cache[substr(id,0,-5)].type=="datetime") {
				$("#"+substr(id,0,-5)+"_time").trigger("focus");
				$("#"+substr(id,0,-5)+"_time").trigger("select");
				return;
			}
			// CONTINUE
			if(substr(id,-7,7)=="-button" && isset(saltos.form_field_cache[substr(id,0,-7)]) && saltos.form_field_cache[substr(id,0,-7)].type=="select") id=substr(id,0,-7);
			if(substr(id,-5,5)=="_time" && isset(saltos.form_field_cache[substr(id,0,-5)]) && saltos.form_field_cache[substr(id,0,-5)].type=="datetime") id=substr(id,0,-5);
			var div=$(event.target);
			for(;;) {
				if(!$(div).length) break;
				if(substr($(div).attr("id"),0,5)=="tabid") break;
				div=$(div).parent();
			}
			var found=0;
			var focus="";
			var first="";
			for(var key in saltos.form_field_cache) {
				var field=saltos.form_field_cache[key];
				var valid=in_array(field.type,saltos.make_enters_list);
				if($("#"+field.name).is("select")) {
					var visible=$("#"+field.name).next().is(":visible");
				} else if($("#"+field.name).is(":hidden")) {
					var visible=true;
				} else {
					var visible=$("#"+field.name).is(":visible");
				}
				var indiv=$(div).has("#"+field.name).length;
				var filter=$("#"+field.name).hasClass("nofilter");
				if(valid && visible && indiv && !filter) {
					if(found) {
						focus=field.name;
						found=0;
						break;
					}
					if(field.name==id) {
						found=1;
					}
					if(!first) {
						first=field.name;
					}
				}
			}
			if(found) {
				focus=first;
			}
			if(focus!="") {
				if(saltos.form_field_cache[focus].type=="select") {
					$("#"+focus+"-button").trigger("focus");
				} else if(saltos.form_field_cache[focus].type=="datetime") {
					$("#"+focus+"_date").trigger("focus");
					$("#"+focus+"_date").trigger("select");
				} else {
					$("#"+focus).trigger("focus");
					$("#"+focus).trigger("select");
				}
			}
		}
	});
};

// JQUERYUI WIDGETS
saltos.add_layout=function() {
	var layout=$(`
		<table class="width100 none" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td valign="top" colspan="2">
					<div class="ui-layout-north"></div>
				</td>
			</tr>
			<tr>
				<td valign="top">
					<div class="ui-layout-west"></div>
				</td>
				<td valign="top" class="width100">
					<div class="ui-layout-center"></div>
				</td>
			</tr>
		</table>
	`);
	$("body").append(layout);
	setTimeout(function() {
		// APPEND BACK2TOP
		$(".ui-layout-west").append(`
			<a href="#" class="back2top ui-widget-header ui-corner-all">
				<span class="fa fa-arrow-circle-up"></span>
			</a>
		`);
		// OLD MAKE_BACK2TOP
		$(window).scroll(function() {
			if($(this).scrollTop()>300) {
				$(".back2top").show();
			} else {
				$(".back2top").hide();
			}
		});
		$(".back2top").on("click",function(event) {
			event.preventDefault();
			$("html,body").animate({ scrollTop:0 },"fast");
			return false;
		})
		// RESIZABLE CODE
		var width=parseInt(saltos.cookies.getIntCookie("saltos_ui_menu_width")/10)*10;
		if(!width) width=200;
		$(".ui-layout-west").width(width).resizable({
			minWidth:100,
			maxWidth:400,
			grid:10,
			handles:"e",
			resize:function(event,ui) {
				saltos.cookies.setIntCookie("saltos_ui_menu_width",ui.size.width);
				$(".back2top").css("left",(ui.size.width-54)+"px");
			},
		});
		$(".back2top").css("left",(width-54)+"px");
		// REMOVE NONE CLASS
		$(layout).removeClass("none");
	},100);
};

saltos.add_header=function(menu) {
	$(".ui-layout-north").append(`
		<div class="tabs2">
			<ul class="headertabs"></ul>
		</div>
	`);
	for(var key in menu) {
		if(saltos.limpiar_key(key)=="header") {
			for(var key2 in menu[key]) {
				if(saltos.limpiar_key(key2)=="option") {
					saltos.add_header_button(menu[key][key2]);
				}
			}
		}
	}
};

saltos.tabs2_padding="";
saltos.tabs2_margin="";
saltos.tabs2_border="";

saltos.add_header_button=function(option) {
	saltos.check_params(option,["class","tip","icon","label","onclick","class2"]);
	var button=$(`
		<li class="${option.class2}"><a href="javascript:void(0)" title="${option.tip}" class="${option.class}">
			<span class="${option.icon}"></span>
			${option.label}
		</a></li>
	`);
	$(button).on("click",function() {
		if(typeof option.onclick=="string") eval(option.onclick);
		if(typeof option.onclick=="function") option.onclick();
	});
	$(".tabs2 ul").append(button);
	if($(".tabs2").hasClass("ui-tabs")) {
		$(".tabs2").tabs("refresh");
	} else {
		$(".tabs2").tabs({
			beforeActivate:function(event,ui) {
				return false;
			},
			beforeLoad:function(event,ui) {
				return false;
			}
		});
		// CHANGE TABS FROM TOP TO BOTTOM
		saltos.tabs2_padding=$(".tabs2 ul").css("padding-top");
		saltos.tabs2_margin=$(".tabs2 li").css("margin-top");
		saltos.tabs2_border=$(".tabs2 li").css("border-top");
		if(!saltos.tabs2_border) saltos.tabs2_border=$(".tabs2 li").css("border-top-width")+" "+$(".tabs2 li").css("border-top-style")+" "+$(".tabs2 li").css("border-top-color");
	}
	// FIX FOR A VOID TABS
	$(".tabs2 div").remove();
	// CHANGE TABS FROM TOP TO BOTTOM
	$(".tabs2 ul").removeClass("ui-corner-all").addClass("ui-corner-bottom");
	$(".tabs2 li").removeClass("ui-tabs-active ui-state-active");
	$(".tabs2 li").removeClass("ui-corner-top").addClass("ui-corner-bottom");
	$(".tabs2 ul").css("padding-top","0").css("padding-bottom",saltos.tabs2_padding);
	$(".tabs2 li").css("margin-top","0").css("margin-bottom",saltos.tabs2_margin);
	$(".tabs2 li").css("border-top","0").css("border-bottom",saltos.tabs2_border);
};

saltos.update_header_title=function(title) {
	$(".tabs2 li.center").remove();
	if(!isset(title)) title=lang_loading();
	document.title=`${title} - ${saltos.info.title} - ${saltos.info.name} v${saltos.info.version} r${saltos.info.revision}`;
	saltos.add_header_button({
		"label":document.title,
		"onclick":"saltos.opencontent('#page=about')",
		"icon":saltos.info.icon,
		"tip":document.title,
		"class":"nowrap",
		"class2":"center",
	});
};

saltos.add_menu=function(menu) {
	var exists=0;
	for(var key in menu) {
		if(saltos.limpiar_key(key)=="group") {
			exists=1;
			var visible=saltos.cookies.getIntCookie("saltos_ui_menu_"+menu[key].name)
			if(visible) {
				menu[key].active=0;
			} else {
				menu[key].active=1;
			}
			saltos.add_menu_group(menu[key]);
			for(var key2 in menu[key]) {
				if(saltos.limpiar_key(key2)=="option") {
					saltos.add_menu_link(menu[key][key2]);
				}
			}
		}
	}
	var obj=$(".ui-layout-west");
	if(exists) {
		var closed=getIntCookie("saltos_ui_menu_closed");
		if(closed) {
			$(obj).addClass("none");
		} else {
			$(obj).removeClass("none");
		}
	} else {
		$(obj).addClass("none");
	}
};

saltos.add_menu_group=function(option) {
	saltos.check_params(option,["name","label","show","class","tip"]);
	var group=$(`
		<div class="${option.class} none" id="${option.name}">
			<h3 title="${option.tip}">${option.label}</h3>
			<div class="accordion-link">
				<ul></ul>
			</div>
		</div>
	`);
	$(".ui-layout-west").append(group);
	setTimeout(function() {
		$(group).accordion({
			collapsible:true,
			heightStyle:"content",
			active:option.active,
			activate:function(event,ui) {
				var name=$(this).attr("id");
				var active=ui.newHeader.length;
				saltos.cookies.setIntCookie("saltos_ui_menu_"+name,active);
			},
			icons:{
				header:"ui-icon-circle-arrow-e",
				activeHeader:"ui-icon-circle-arrow-s"
			}
		});
		// FOR MOVE NODES AS A REAL TREE
		var temp=[];
		$(".accordion-link li",group).each(function() {
			var found=0;
			for(var i=1;i<20;i++) {
				if($("a",this).hasClass("depth_"+i)) {
					if($("ul",temp[i-1]).length==0) $(temp[i-1]).append("<ul></ul>");
					$("ul",temp[i-1]).append(this);
					while(temp.length>i) temp.pop();
					temp.push(this);
					found=1;
				}
			}
			if(!found) {
				while(temp.length>0) temp.pop();
				temp.push(this);
			}
		});
		// FOR PREPARE THE OPEN NODE LIST
		var open=[];
		var name=$(group).attr("id");
		$(".accordion-link li",group).each(function() {
			var name2=$("a",this).attr("id");
			var active=saltos.cookies.getIntCookie("saltos_ui_menu_"+name+"_"+name2);
			if(active) open.push("#"+name2);
		});
		// CREATE THE JSTREE
		$(".accordion-link",group).jstree();
		// NOW, OPEN THE NODES USING THE PREVIOUS NODE LIST
		for(var i in open) {
			var temp=$(open[i],group);
			$(".accordion-link",group).jstree("open_node",temp);
		}
		// DEFINE AND EXECUTE THE FIX FOR THE ICONS
		var fn=function(obj) {
			$(".jstree-icon.jstree-themeicon",obj).each(function() {
				var icon=$(this).parent().attr("icon");
				$(this).removeClass("jstree-themeicon").addClass("jstree-themeicon-custom").addClass(icon);
			});
		}
		fn(group);
		// PROGRAM THE BIND TO PREVENT SELECTION
		$(".accordion-link",group).on("select_node.jstree",function(e,_data) {
			_data.instance.deselect_node(_data.node);
		});
		// PROGRAM THE BIND TO STORE THE NODE'S STATES
		$(".accordion-link",group).on("open_node.jstree",function(e,_data) {
			fn(this);
			var name2=_data.node.a_attr.id;
			saltos.cookies.setIntCookie("saltos_ui_menu_"+name+"_"+name2,1);
		});
		$(".accordion-link",group).on("close_node.jstree",function(e,_data) {
			var name2=_data.node.a_attr.id;
			saltos.cookies.setIntCookie("saltos_ui_menu_"+name+"_"+name2,0);
		});
		// REMOVE NONE CLASS
		$(group).removeClass("none");
	},100);
};

saltos.add_menu_link=function(option) {
	saltos.check_params(option,["class","tip","icon","label","onclick","name"]);
	var link=$(`
		<li>
			<a href="javascript:void(0)" class="${option.class}" icon="${option.icon}" title="${option.tip}" id="${option.name}">${option.label}</a>
		</li>
	`);
	//~ $("a",link).on("click",function() {
		//~ if(typeof option.onclick=="string") eval(option.onclick);
		//~ if(typeof option.onclick=="function") option.onclick();
	//~ });
	// TRICK BECAUSE EVENT DOESN'T WORKS
	$("a",link).attr("onclick",option.onclick);
	// CONTINUE
	$(".ui-layout-west ul:last").append(link);
};

saltos.make_focus_obj=null;

saltos.make_tabs=function(array) {
	var card=$(`
		<div class="tabs none">
			<ul class="centertabs"></ul>
			<form onsubmit="return false"></form>
		</div>
	`);
	for(var key in array) {
		if(isset(array[key].title)) {
			var uniqid=saltos.uniqid();
			$(".centertabs",card).append(`
				<li>
					<a href="#tab${uniqid}"><span class="${array[key].icon}"></span> ${array[key].title}</a>
				</li>
			`);
			if(array[key].popup=="true") $(".centertabs li:last",card).addClass("popup");
			$("form",card).append(`<div id="tab${uniqid}"></div>`);
			$(`#tab${uniqid}`,card).append(array[key].obj);
		}
		if(isset(array[key].name)) {
			$("form",card).attr("id",array[key].name);
			$("form",card).attr("name",array[key].name);
			$("form",card).attr("action",array[key].action);
			$("form",card).attr("method",array[key].method);
		}
		if(isset(array[key].help)) {
			$(".centertabs",card).append(`
				<li class="help"><a href="javascript:void(0)"><span class=""></span></a></li>
			`);
		}
	}
	// THIS CODE ADD THE ACCESSKEY FEATURE FOR EACH TAB
	var accesskeys="1234567890";
	var accesskey=0;
	var tabs=$(".centertabs > li",card);
	$(tabs).each(function() {
		if($(this).hasClass("help")) {
			$("a",this).attr("title","[CTRL] + [H]");
			$("a",this).addClass("shortcut_ctrl_h");
		} else if(accesskey<accesskeys.length) {
			$("a",this).attr("title","[CTRL] + ["+substr(accesskeys,accesskey,1)+"]");
			$("a",this).addClass("shortcut_ctrl_"+substr(accesskeys,accesskey,1));
			accesskey++;
		}
	});
	// THIS CODE SEARCH THE TAB USING THE OLD OPENED TAB STORED IN A COOKIE
	// TOO, FIND ALL OBJECTS FROM THE FORM AND IF EXIST THE FOCUSED ATTRIBUTE,
	// SEARCH THE INDEX OF THE TAB THAT CONTAIN THE OBJECT
	var active=0;
	$("[focused=true]:first",card).each(function() {
		saltos.make_focus_obj=this;
		var thetab=$(this).parent();
		while(thetab) {
			if(substr($(thetab).attr("id"),0,5)=="tabid") {
				var index=0;
				$("[id^=tabid]",card).each(function() {
					if($(this).attr("id")==$(thetab).attr("id")) active=index;
					index++;
				});
				break;
			}
			thetab=$(thetab).parent();
		}
	});
	// TRUE, CREATE THE TABS
	$(card).tabs({
		active:active,
		beforeActivate:function(event,ui) {
			if($(ui.newTab).hasClass("help")) {
				viewpdf("page="+getParam("page"));
				return false;
			}
			if($(ui.newTab).hasClass("popup")) {
				var title=$("a",ui.newTab).text();
				var tabid=$("a",ui.newTab).attr("href").substr(1);
				var form=$("#"+tabid);
				dialog(title);
				var dialog2=$("#dialog");
				$(dialog2).parent().appendTo("form");
				$(dialog2).html("");
				$(form).after("<div id='popup"+tabid+"'></div>");
				$(dialog2).append("<br/>");
				$(dialog2).append(form);
				$(dialog2).append("<br/><br/>");
				$("div",dialog2).removeAttr("class").removeAttr("style");
				$(dialog2).dialog("option","resizeStop",function(event,ui) {
					saltos.cookies.setIntCookie("saltos_popup_width",$(dialog2).dialog("option","width"));
					saltos.cookies.setIntCookie("saltos_popup_height",$(dialog2).dialog("option","height"));
				});
				$(dialog2).dialog("option","close",function(event,ui) {
					$(dialog2).dialog("option","resizeStop",function() {});
					$(dialog2).dialog("option","close",function() {});
					$(form).hide();
					$("#popup"+tabid).replaceWith(form);
					$(dialog2).parent().appendTo("body");
					saltos.unmake_focus();
					saltos.hide_tooltips();
				});
				var width=getIntCookie("saltos_popup_width");
				if(!width) width=900;
				$(dialog2).dialog("option","width",width);
				var height=getIntCookie("saltos_popup_height");
				if(!height) height=600;
				$(dialog2).dialog("option","height",height);
				$(dialog2).dialog("option","position",{ my:"center",at:"center",of:window });
				$(dialog2).dialog("open");
				return false;
			}
		},
		beforeLoad:function(event,ui) {
			return false;
		}
	});
	// CHANGE TABS FROM ALL TO TOP
	$("ul",card).removeClass("ui-corner-all").addClass("ui-corner-top");
	// TUNNING THE HELP TAB
	var help=$("li.help",card);
	$("span",help).addClass(icon_help());
	$("a",help).append("&nbsp;").append(lang_help());
	// REMOVE NONE CLASS
	$(card).removeClass("none");
	return card;
};

/* HELPERS DEL NUEVO SALTOS */
saltos.check_params=function(obj,params,valor) {
	if(!isset(valor)) valor="";
	for(var key in params) if(!isset(obj[params[key]])) obj[params[key]]=valor;
};

saltos.uniqid=function() {
	return "id"+Math.floor(Math.random()*1000000);
};

saltos.when_visible=function(obj,fn,args) {
	if(!$(obj).is("[id]")) $(obj).attr("id","fix"+saltos.uniqid());
	var id="#"+$(obj).attr("id");
	var interval=setInterval(function() {
		var obj2=$(id);
		if(!$(obj2).length) {
			clearInterval(interval);
		} else if($(obj2).is(":visible")) {
			clearInterval(interval);
			fn(args);
		}
	},100);
};

saltos.form_fix_width=function(obj,ref) {
	saltos.when_visible(obj,function(args) {
		if(args.ref=="next") var obj2=$(args.obj).next();
		if(args.ref=="prev") var obj2=$(args.obj).prev();
		$(args.obj).width($(obj2).width());
	},{"obj":obj,"ref":ref});
};

/* FUNCIONES PARA EL PROCESADO DE LISTADOS */
saltos.make_table=function(option) {
	saltos.check_params(option,["width","expand","checkbox"]);
	saltos.check_params(option,["rows","fields","actions"],[]);
	var table=$(`
		<table class="tabla helperlists" cellpadding="0" cellspacing="0" border="0">
			<thead></thead>
			<tbody></tbody>
		</table>
	`);
	if(option.width!="") $(table).attr("style",`width:${option.width}`);
	// HEAD
	$("thead",table).append("<tr></tr>");
	if(option.checkbox) {
		$("thead tr",table).append(`
			<td class="width1 thead ui-widget-header">
				<input type="checkbox" class="master shortcut_ctrl_a" name="master" id="master" value="1" autocomplete="off"/></td>`);
		$("thead tr input:last",table).attr("title",lang_selectallcheckbox());
	}
	saltos.check_params(option.sort,["iconascin","iconascout","icondescin","icondescout","labelasc","labeldesc","onclick"]);
	for(var key in option.fields) {
		var field=option.fields[key];
		saltos.check_params(field,["width","tip","label","sort","selected","name","order","orderasc","orderdesc"]);
		var td=$(`<td class="thead ui-widget-header"></td>`);
		$("thead tr:last",table).append(td);
		if(field.width!="") $(td).attr("style",`width:${field.width}`);
		if(field.tip!="") $(td).append(`<span title="${field.tip}">${field.label}</span>`);
		if(field.tip=="") $(td).append(field.label);
		if(field.sort=="true") {
			var hrefasc=$(`<a href="javascript:void(0)" title="${option.sort.labelasc}"></a>`);
			if(field.selected=="asc") {
				var iconasc=$(`<span class="${option.sort.iconascin}"></span>`);
			} else {
				var iconasc=$(`<span class="${option.sort.iconascout}" toggle="${option.sort.iconascin} ${option.sort.iconascout}"></span>`);
				$(iconasc).on("mouseover",function() {
					$(this).toggleClass($(this).attr("toggle"));
				}).on("mouseout",function() {
					$(this).toggleClass($(this).attr("toggle"));
				});
			}
			hrefasc.append(iconasc);
			if(field.orderasc!="") var orderasc=field.orderasc+" asc";
			else if(field.order!="") var orderasc=field.order+" asc";
			else var orderasc=field.name+" asc";
			hrefasc.on("click",{
				onclick:str_replace("FIELD",orderasc,option.sort.onclick)
			},function(e) {
				if(typeof e.data.onclick=="string") eval(e.data.onclick);
				if(typeof e.data.onclick=="function") e.data.onclick();
			});
			$(td).append(hrefasc);
			var hrefdesc=$(`<a href="javascript:void(0)" title="${option.sort.labeldesc}"></a>`);
			if(field.selected=="desc") {
				var icondesc=$(`<span class="${option.sort.icondescin}"></span>`);
			} else {
				var icondesc=$(`<span class="${option.sort.icondescout}" toggle="${option.sort.icondescin} ${option.sort.icondescout}"></span>`);
				$(icondesc).on("mouseover",function() {
					$(this).toggleClass($(this).attr("toggle"));
				}).on("mouseout",function() {
					$(this).toggleClass($(this).attr("toggle"));
				});
			}
			hrefdesc.append(icondesc);
			if(field.orderdesc!="") var orderdesc=field.orderdesc+" desc";
			else if(field.order!="") var orderdesc=field.order+" desc";
			else var orderdesc=field.name+" desc";
			hrefdesc.on("click",{
				onclick:str_replace("FIELD",orderdesc,option.sort.onclick)
			},function(e) { eval(e.data.onclick); });
			$(td).append(hrefdesc);
		}
	}
	if(count(option.actions)) {
		$("thead tr",table).append(`
			<td class="width1 thead ui-widget-header" colspan="100"></td>
		`);
	}
	// BODY
	var total=0;
	for(var key in option.rows) {
		var row=option.rows[key];
		saltos.check_params(row,["action_style","action_id"]);
		$("tbody",table).append("<tr></tr>");
		if(option.checkbox) {
			$("tbody tr:last",table).append(`
				<td class="width1 tbody">
					<input type="checkbox" class="slave id_${row.action_id}" name="slave_${row.action_id}" id="slave_${row.action_id}" value="1" autocomplete="off"/></td>
			`);
			$("tbody tr:last input:last",table).attr("title",lang_selectonecheckbox());
		}
		for(var key2 in option.fields) {
			var field=option.fields[key2];
			saltos.check_params(field,["name","size","class"]);
			var td=$(`<td class="tbody ${field.class} ${row.action_style}"></td>`);
			$("tbody tr:last",table).append(td);
			field.value=saltos.get_filtered_field(row[field.name],field.size,row.action_id);
			$(td).append(field.value);
		}
		if(count(option.actions)) {
			var total2=0;
			for(var key2 in option.actions) {
				if(isset(row["action_"+key2])) {
					var action=option.actions[key2];
					saltos.check_params(action,["label","onclick","icon"]);
					if(row["action_"+key2]=="true") {
						var td=$(`
							<td class="width1 actions1 tbody">
								<a href="javascript:void(0)">
									<span class="${action.icon}" title="${action.label}" labeled="${action.label}"></span></a></td>
						`);
						$("tbody tr:last",table).append(td);
						//~ $("a",td).on("click",{
							//~ onclick:str_replace("ID",row.action_id,action.onclick)
						//~ },function(e) { eval(e.data.onclick); });
						// TRICK BECAUSE EVENT DOESN'T WORKS
						$("a",td).attr("onclick",str_replace("ID",row.action_id,action.onclick));
					} else {
						var td=$(`
							<td class="width1 actions1 tbody">
								<span class="${action.icon} ui-state-disabled" title="${action.label}" labeled="${action.label}" disabled="true"></span></td>
						`);
						$("tbody tr:last",table).append(td);
					}
					total2++;
				}
			}
			if(option.expand!="true" && total2>1) {
				$("tbody tr:last td.actions1",table).addClass("none");
				var td=$(`
					<td class="width1 actions2 tbody">
						<a href="javascript:void(0)">
							<span class="${option.actions2.icon}" title="${option.actions2.label}"></span></a></td>
				`);
				$("tbody tr:last",table).append(td);
			}
		}
		if(total%2==0) $("tbody tr:last td",table).addClass("ui-widget-content");
		if(total%2==1) $("tbody tr:last td",table).addClass("ui-state-default");
		$("tbody tr:last td",table).addClass("notop");
		total++;
	}
	if(!total) {
		$("tbody",table).append("<tr></tr>");
		$("tbody tr:last",table).append(`
			<td colspan="100" class="tbody ui-widget-content notop nodata italic">
				${option.nodata.label}</td>
		`);
	}
	// SUPPORT FOR LTR AND RTL LANGS
	var dir=$("html").attr("dir");
	var rtl={
		"ltr":{"ui-corner-tl":"ui-corner-tl","ui-corner-tr":"ui-corner-tr","ui-corner-bl":"ui-corner-bl","ui-corner-br":"ui-corner-br"},
		"rtl":{"ui-corner-tl":"ui-corner-tr","ui-corner-tr":"ui-corner-tl","ui-corner-bl":"ui-corner-br","ui-corner-br":"ui-corner-bl"}
	};
	$("tr:first td:first",table).addClass(rtl[dir]["ui-corner-tl"]);
	$("tr:first td:last",table).addClass(rtl[dir]["ui-corner-tr"]);
	$("tr:last td:first",table).addClass(rtl[dir]["ui-corner-bl"]);
	$("tr:last td:last",table).addClass(rtl[dir]["ui-corner-br"]);
	// CHECK FOR MATH ROWS
	var found=0;
	var math_row=[];
	for(var key in option.fields) {
		if(isset(option.fields[key].math)) {
			found=1;
			var name=option.fields[key].name;
			var suma=0;
			var total=0;
			var average=0;
			for(var key2 in option.rows) {
				var row=option.rows[key2];
				suma+=floatval(row[name]);
				total++;
				average=suma/total;
			}
			var label=option.fields[key].math.label;
			var func=option.fields[key].math.func;
			var value="";
			if(func=="sum()") value=round(suma,2);
			if(func=="count()") value=total;
			if(func=="avg()") value=round(average,2);
			math_row.push({
				"label":label,
				"value":value,
			});
		} else {
			math_row.push({});
		}
	}
	if(found) {
		$("tbody",table).append("<tr></tr>");
		$("tbody tr:last",table).append(`<td class="separator"></td>`);
		// HEADER
		$("tbody",table).append("<tr></tr>");
		if(option.checkbox) {
			$("tbody tr:last",table).append(`<td class="width1 thead ui-widget-header"></td>`);
		}
		for(var key in math_row) {
			$("tbody tr:last",table).append(`<td class="thead ui-widget-header"></td>`);
			if(isset(math_row[key].label)) $("tbody tr:last td:last",table).append(math_row[key].label);
		}
		if(count(option.actions)) {
			$("tbody tr:last",table).append(`<td class="width1 thead ui-widget-header" colspan="100"></td>`);
		}
		$("tr:last td:first",table).addClass(rtl[dir]["ui-corner-tl"]);
		$("tr:last td:last",table).addClass(rtl[dir]["ui-corner-tr"]);
		// BODY
		$("tbody",table).append("<tr></tr>");
		if(option.checkbox) {
			$("tbody tr:last",table).append(`<td class="width1 tbody ui-widget-content"></td>`);
		}
		for(var key in math_row) {
			$("tbody tr:last",table).append(`<td class="tbody ui-widget-content"></td>`);
			if(isset(math_row[key].value)) $("tbody tr:last td:last",table).append(math_row[key].value);
		}
		if(count(option.actions)) {
			$("tbody tr:last",table).append(`<td class="width1 tbody ui-widget-content" colspan="100"></td>`);
		}
		$("tbody tr:last td",table).addClass("notop");
		$("tr:last td:first",table).addClass(rtl[dir]["ui-corner-bl"]);
		$("tr:last td:last",table).addClass(rtl[dir]["ui-corner-br"]);
	}
	return table;
};

saltos.__get_filtered_field_helper=function(field,size) {
	var title="";
	if(size!="") {
		size=intval(size);
		if(strlen(field)>size) {
			title=field;
			field=substr(field,0,size)+"...";
		}
	}
	var span=$("<span></span>");
	if(title!="") $(span).attr("title",title);
	$(span).text(field);
	return span;
};

saltos.get_filtered_field=function(field,size,id) {
	if(field=="") {
		field="-";
	} else if(substr(field,0,4)=="tel:") {
		var temp=explode(":",field,2);
		field=$(`<a class="tellink draggable id_${id}" href="javascript:void(0)"></a>`)
		$(field).append(saltos.__get_filtered_field_helper(temp[1],size));
		$(field).on("click",function() { qrcode(temp[1]); });
	} else if(substr(field,0,7)=="mailto:") {
		var temp=explode(":",field,2);
		field=$(`<a class="maillink draggable id_${id}" href="javascript:void(0)"></a>`)
		$(field).append(saltos.__get_filtered_field_helper(temp[1],size));
		$(field).on("click",function() { mailto(temp[1]); });
	} else if(substr(field,0,5)=="href:") {
		var temp=explode(":",field,2);
		field=$(`<a class="weblink draggable id_${id}" href="javascript:void(0)"></a>`)
		$(field).append(saltos.__get_filtered_field_helper(temp[1],size));
		$(field).on("click",function() { openwin(temp[1]); });
	} else if(substr(field,0,5)=="link:") {
		var temp=explode(":",field,3);
		field=$(`<a class="applink draggable id_${id}" href="javascript:void(0)"></a>`)
		$(field).append(saltos.__get_filtered_field_helper(temp[2],size));
		$(field).on("click",function() { eval(temp[1]); });
	} else {
		field=saltos.__get_filtered_field_helper(field,size)
	}
	return field;
};

saltos.make_list=function(option) {
	var array=[];
	for(var key in option) {
		if(saltos.limpiar_key(key)=="form") {
			array=array_merge(array,saltos.make_form(option[key]));
		}
	}
	// TRICK TO POPULATE THE FIELDS CACHE BEFORE THE USAGE OF THE FIELD OF TYPE=COPY
	var obj=$("<div></div>");
	if(isset(option.quick)) {
		var table=saltos.form_table(option.width,"helperbuttons");
		$(obj).append(table);
		$(table).append(saltos.form_by_row_3(option,"quick","row"));
		$(table).append(saltos.form_brtag_2());
	}
	option.checkbox="true";
	$(obj).append(saltos.make_table(option));
	if(isset(option.pager)) {
		var table=saltos.form_table(option.width,"helperbuttons");
		$(obj).append(table);
		$(table).append(saltos.form_brtag_2());
		$(table).append(saltos.form_by_row_3(option,"pager","row"));
	}
	array.unshift({
		"title":option.title,
		"icon":option.icon,
		"popup":"",
		"obj":obj,
	});
	// CONTINUE
	if(isset(option.help)) array.push({ "help":option.help });
	return array;
};

/* FUNCIONES PARA EL PROCESADO DE FORMULARIOS */
saltos.make_form=function(option) {
	var array=[];
	if(option.action=="") option.action="index.php";
	array.push({ name:option.name, action:option.action, method:option.method });
	var title="";
	var icon="";
	var popup="";
	var obj=$("<div></div>");
	for(var key in option) {
		if(saltos.limpiar_key(key)=="hiddens") {
			$(obj).append(saltos.form_by_row_1(option[key]));
		}
	}
	if(isset(option.fields) && isset(option.fields.row)) {
		// CASO 1
		for(var key in option) {
			if(saltos.limpiar_key(key)=="fields") {
				saltos.check_params(option[key],["title","icon","width","class","quick","buttons","popup"]);
				saltos.check_params(option[key],["row"],[]);
				saltos.check_params(option,["quick","buttons"],[]);
				if(option[key].title!="") {
					if(title!="") {
						array.push({ "title":title, "icon":icon, "popup":popup, "obj":obj });
						obj=$("<div></div>");
					}
					title=option[key].title;
					icon=option[key].icon;
					popup=option[key].popup;
				} else {
					$(obj).append(saltos.form_brtag_1());
				}
				if(option[key].quick=="true") {
					var table=saltos.form_table(option[key].width,"helperbuttons");
					$(obj).append(table);
					$(table).append(saltos.form_by_row_3(option,"quick","row"));
					$(table).append(saltos.form_brtag_2());
					saltos.form_fix_width(table,"next");
				}
				var table=saltos.form_table(option[key].width,option[key].class);
				$(obj).append(table);
				$(table).append(saltos.form_by_row_2(option[key],"row"));
				if(option[key].buttons=="true") {
					var table=saltos.form_table(option[key].width,"helperbuttons");
					$(obj).append(table);
					$(table).append(saltos.form_brtag_2());
					$(table).append(saltos.form_by_row_3(option,"buttons","row"));
					saltos.form_fix_width(table,"prev");
				}
			}
		}
	} else {
		for(var key in option) {
			if(saltos.limpiar_key(key)=="fields") {
				for(var key2 in option[key]) {
					var name1=saltos.limpiar_key(key2);
					var node1=option[key][key2];
					for(var key3 in option) {
						if(saltos.limpiar_key(key3)=="rows") {
							for(var key4 in option[key3]) {
								var name2=saltos.limpiar_key(key4);
								var node2=option[key3][key4];
								if(name1==name2) {
									if(isset(node2.row)) {
										// CASO 2
										for(var key5 in node2) {
											if(saltos.limpiar_key(key5)=="row") {
												var node3=node2[key5];
												var prefix=name2+"_"+node3.id+"_";
												for(var key6 in node1) {
													if(saltos.limpiar_key(key6)=="fieldset") {
														if(isset(node1[key6].title) && node1[key6].title!="") {
															if(title!="") {
																array.push({ "title":title, "icon":icon, "popup":popup, "obj":obj });
																obj=$("<div></div>");
															}
															title=node1[key6].title;
															icon=node1[key6].icon;
															popup=node1[key6].popup;
														} else {
															$(obj).append(saltos.form_brtag_1());
														}
														if(isset(node1[key6].quick) && node1[key6].quick=="true") {
															var table=saltos.form_table(node1[key6].width,"helperbuttons");
															$(obj).append(table);
															var temp=saltos.form_prepare_fields_3(option,"quick","row"); // NO PREFIX
															$(table).append(saltos.form_by_row_3(temp,"quick","row"));
															$(table).append(saltos.form_brtag_2());
															saltos.form_fix_width(table,"next");
														}
														var table=saltos.form_table(node1[key6].width,node1[key6].class);
														$(obj).append(table);
														var temp=saltos.form_prepare_fields_2(node1[key6],"row",prefix,node3);
														$(table).append(saltos.form_by_row_2(temp,"row"));
														if(isset(node1[key6].buttons) && node1[key6].buttons=="true") {
															var table=saltos.form_table(node1[key6].width,"helperbuttons");
															$(obj).append(table);
															$(table).append(saltos.form_brtag_2());
															var temp=saltos.form_prepare_fields_3(option,"buttons","row"); // NO PREFIX
															$(table).append(saltos.form_by_row_3(temp,"buttons","row"));
															saltos.form_fix_width(table,"prev");
														}
													}
												}
											}
										}
									} else if(isset(node2[name2]) && isset(node2[name2].row)) {
										// CASO 3
										for(var key6 in node1) {
											if(saltos.limpiar_key(key6)=="fieldset") {
												if(isset(node1[key6].title) && node1[key6].title!="") {
													if(title!="") {
														array.push({ "title":title, "icon":icon, "popup":popup, "obj":obj });
														obj=$("<div></div>");
													}
													title=node1[key6].title;
													icon=node1[key6].icon;
													popup=node1[key6].popup;
												} else {
													$(obj).append(saltos.form_brtag_1());
												}
												if(isset(node1[key6].quick) && node1[key6].quick=="true") {
													var table=saltos.form_table(node1[key6].width,"helperbuttons");
													$(obj).append(table);
													$(table).append(saltos.form_by_row_3(option,"quick","row"));
													$(table).append(saltos.form_brtag_2());
													saltos.form_fix_width(table,"next");
												}
												var table=saltos.form_table(node1[key6].width,"tabla "+node1[key6].class);
												$(obj).append(table);
												$(table).append(saltos.form_by_row_2(node1[key6],"head"));
												var temp=saltos.form_prepare_fields_4(node1,key6,"row",node2,name2,"row");
												$(table).append(saltos.form_by_row_2(temp,"row"));
												$(table).append(saltos.form_by_row_4(node1[key6],"tail"));
												if(isset(node1[key6].buttons) && node1[key6].buttons=="true") {
													var table=saltos.form_table(node1[key6].width,"helperbuttons");
													$(obj).append(table);
													$(table).append(saltos.form_brtag_2());
													$(table).append(saltos.form_by_row_3(option,"buttons","row"));
													saltos.form_fix_width(table,"prev");
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}
	// ULTIMO CASO
	if(title!="") array.push({ "title":title, "icon":icon, "popup":popup, "obj":obj });
	if(isset(option.help)) array.push({ "help":option.help });
	return array;
};

saltos.form_prepare_fields_1=function(fields,prefix,values) {
	if(!count(fields)) return null;
	var fields2=JSON.parse(JSON.stringify(fields));
	for(var key in fields2) {
		if(saltos.limpiar_key(key)=="field") {
			var field=fields2[key];
			if(isset(field.name) && isset(values)) {
				for(var key2 in values) {
					if(key2==field.name) {
						if(isset(field.type) && field.type=="checkbox") {
							fields2[key].checked=(field.value==values[key2])?"true":"false";
						} else {
							fields2[key].value=values[key2];
						}
					}
				}
			}
			if(isset(field.name) && isset(prefix)) {
				fields2[key].name=prefix+field.name;
			}
			if(isset(field.type) && field.type=="grid" && isset(field.rows)) {
				fields2[key].rows=saltos.form_prepare_fields_2(field.rows,"row",prefix,values);
			}
		}
	}
	return fields2;
};

saltos.form_prepare_fields_2=function(fields,filter,prefix,values) {
	if(!count(fields)) return null;
	var obj={};
	for(var key in fields) {
		if(saltos.limpiar_key(key)==filter) {
			obj[key]=saltos.form_prepare_fields_1(fields[key],prefix,values);
			if(is_array(obj[key])) {
				obj[key]["field#prefix"]={
					"type":"hidden",
					"name":"prefix_"+prefix,
					"value":prefix,
				};
			}
		}
	}
	return obj;
};

saltos.form_prepare_fields_3=function(fields,filter,filter2,prefix,values) {
	if(!count(fields)) return null;
	var obj={};
	for(var key in fields) {
		if(saltos.limpiar_key(key)==filter) {
			obj[key]=saltos.form_prepare_fields_2(fields[key],filter2,prefix,values);
		}
	}
	return obj;
};

saltos.form_prepare_fields_4=function(node1,name1,filter1,node2,name2,filter2) {
	if(!count(node1)) return null;
	if(!count(node2)) return null;
	var obj={};
	for(var key1 in node1[name1]) {
		if(saltos.limpiar_key(key1)==filter1) {
			for(var key2 in node2[name2]) {
				if(saltos.limpiar_key(key2)==filter2) {
					var node3=node2[name2][key2];
					var prefix=name2+"_"+node3.id+"_";
					obj[key1+"#"+key2]=saltos.form_prepare_fields_1(node1[name1][key1],prefix,node3);
					if(is_array(obj[key1+"#"+key2])) {
						obj[key1+"#"+key2]["field#prefix"]={
							"type":"hidden",
							"name":"prefix_"+prefix,
							"value":prefix,
						};
					}
				}
			}
		}
	}
	return obj;
};

saltos.form_by_row_1=function(fields) {
	if(!count(fields)) return null;
	var obj=[];
	for(var key in fields) {
		if(saltos.limpiar_key(key)=="field") {
			var field=fields[key];
			var temp=saltos.form_field(field);
			while(count(temp)) {
				obj.push(temp.shift());
			}
		}
	}
	return obj;
};

saltos.form_by_row_2=function(fields,filter) {
	if(!count(fields)) return null;
	var obj=[];
	for(var key in fields) {
		if(saltos.limpiar_key(key)==filter) {
			var temp=saltos.form_by_row_1(fields[key]);
			if(count(temp)) {
				var tr=$("<tr></tr>");
				if(isset(fields[key].id)) $(tr).attr("id",fields[key].id);
				if(isset(fields[key].class)) $(tr).attr("class",fields[key].class);
				if(isset(fields[key].height)) $(tr).attr("style","height:"+fields[key].height);
				for(var key2 in temp) {
					$(tr).append(temp[key2]);
				}
				obj.push(tr);
			}
		}
	}
	return obj;
};

saltos.form_by_row_3=function(fields,filter,filter2) {
	if(!count(fields)) return null;
	var obj=[];
	for(var key in fields) {
		if(saltos.limpiar_key(key)==filter) {
			var temp=saltos.form_by_row_2(fields[key],filter2);
			if(count(temp)) {
				var table=$(`<table class="width100" cellpadding="0" cellspacing="0" border="0"></table>`);
				for(var key2 in temp) {
					$(table).append(temp[key2]);
				}
				var td=$(`<td colspan="100"></td>`);
				$(td).append(table);
				var tr=$("<tr></tr>");
				$(tr).append(td);
				obj.push(tr);
			}
		}
	}
	return obj;
};

saltos.form_by_row_4=function(fields,filter) {
	if(!count(fields)) return null;
	var temp=saltos.form_by_row_2(fields,filter);
	var table=$(`<table class="width100" cellpadding="0" cellspacing="0" border="0"></table>`);
	for(var key in temp) {
		$(table).append(temp[key]);
	}
	var td=$(`<td colspan="100"></td>`);
	$(td).append(table);
	var tr=$("<tr></tr>");
	$(tr).append(td);
	return tr;
};

saltos.form_brtag_1=function() {
	return $(`<table cellpadding="0" cellspacing="0" border="0"></table>`).append(saltos.form_brtag_2());
};

saltos.form_brtag_2=function() {
	return $(`<tr><td class="separator"></td></tr>`);
};

saltos.form_table=function(width,clase) {
	if(!isset(width)) width="";
	if(!isset(clase)) clase="";
	return $(`<table class="${clase}" style="width:${width}" cellpadding="0" cellspacing="0" border="0"></table>`);
};

/*
 * LIST OF SUPPORTED TYPES:
 * - hidden
 * - text
 * - integer
 * - float
 * - color
 * - date
 * - time
 * - datetime
 * - textarea
 * - ckeditor
 * - codemirror
 * - iframe
 * - select
 * - multiselect
 * - checkbox
 * - button
 * - password
 * - file
 * - link
 * - separator
 * - label
 * - image
 * - plot
 * - menu
 * - grid
 * - excel
 * - copy
 */

saltos.form_field=function(field) {
	saltos.check_params(field,["type"]);
	if(field.type=="textarea" && isset(field.ckeditor) && field.ckeditor=="true") field.type="ckeditor";
	if(field.type=="textarea" && isset(field.codemirror) && field.codemirror=="true") field.type="codemirror";
	if(field.type!="copy" && isset(field.name) && field.name!="") saltos.form_field_cache[field.name]=field;
	return eval(`saltos.form_field_${field.type}(field)`);
};

saltos.form_field_hidden=function(field) {
	saltos.check_params(field,["name","value","onchange","class"]);
	var obj=[];
	var input=$(`
		<input type="hidden" name="${field.name}" id="${field.name}" value="" class="${field.class}" autocomplete="off">
	`);
	$(input).val(field.value);
	//~ if(field.onchange!="") $(input).on("change",{event:field.onchange},saltos.__form_event);
	if(field.onchange!="") $(input).attr("onchange",field.onchange);
	obj.push(input);
	return obj;
};

saltos.form_field_text=function(field) {
	saltos.check_params(field,["label",
		"class2","colspan2","rowspan2","width2","required",
		"class","colspan","rowspan","width",
		"name","value","onchange","onkey","focus","label2","tip","class3",
		"autocomplete","querycomplete","filtercomplete","oncomplete",
		"readonly","link","icon","tip2"]);
	var obj=[];
	if(field.label!="") {
		var td=$(`
			<td class="right nowrap label ${field.class2}" colspan="${field.colspan2}" rowspan="${field.rowspan2}" style="width:${field.width2}">
				${field.label}</td>
		`);
		if(field.required=="true") $(td).prepend("(*) ");
		obj.push(td);
	}
	var td=$(`
		<td class="left nowrap ${field.class}" colspan="${field.colspan}" rowspan="${field.rowspan}" style="width:${field.width}">
			<input type="text" name="${field.name}" id="${field.name}" value="" style="width:${field.width}"
				focused="${field.focus}" isrequired="${field.required}" labeled="${field.label}${field.label2}"
				title="${field.tip}" class="ui-state-default ui-corner-all ${field.class3}" autocomplete="off"
				isautocomplete="${field.autocomplete}" querycomplete="${field.querycomplete}"
				filtercomplete="${field.filtercomplete}" oncomplete="${field.oncomplete}"/></td>
	`);
	$("input",td).val(field.value);
	//~ if(field.onchange!="") $("input",td).on("change",{event:field.onchange},saltos.__form_event);
	//~ if(field.onkey!="") $("input",td).on("keydown",{event:field.onkey},saltos.__form_event);
	if(field.onchange!="") $("input",td).attr("onchange",field.onchange);
	if(field.onkey!="") $("input",td).attr("onkeydown",field.onkey);
	if(field.readonly=="true") {
		$("input",td).attr("readonly","true").addClass("ui-state-disabled");
		if(field.link!="") {
			var link=$(`
				<a href="javascript:void(0)" class="ui-state-default ui-corner-all" islink="true" fnlink="${field.link}" forlink="${field.name}">
					<span class="${field.icon}" title="${field.tip2}"></span></a>
			`)
			$(td).append(link);
		}
	}
	obj.push(td);
	// PROGRAM LINKS OF SELECTS
	saltos.form_field_islink_helper(td);
	// PROGRAM AUTOCOMPLETE FIELDS
	saltos.form_field_autocomplete_helper(td);
	return obj;
};

saltos.form_field_islink_helper=function(td) {
	// PROGRAM LINKS OF SELECTS
	$("a[islink=true]",td).on("click",function() {
		var id=str_replace("nombre","id",$(this).attr("forlink"));
		var val=intval($("#"+id).val());
		var fn=$(this).attr("fnlink");
		if(val) eval(str_replace("ID",val,fn));
	});
};

saltos.form_field_autocomplete_helper=function(td) {
	// PROGRAM AUTOCOMPLETE FIELDS
	$("input[isautocomplete=true],textarea[isautocomplete=true]",td).each(function() {
		var key=$(this).attr("name");
		var prefix="";
		$("input[name^=prefix_]").each(function() {
			var val=$(this).val();
			if(key.substr(0,val.length)==val) prefix=val;
		});
		var query=$(this).attr("querycomplete");
		var filter=$(this).attr("filtercomplete");
		var fn=$(this).attr("oncomplete");
		$(this).autocomplete({
			delay:300,
			source:function(request,response) {
				var term=request.term;
				var input=this.element;
				var data="action=ajax&query="+query+"&term="+encodeURIComponent(term);
				if(typeof($("#"+prefix+filter).val())!="undefined") data+="&filter="+$("#"+prefix+filter).val();
				$.ajax({
					url:"index.php",
					data:data,
					type:"get",
					success:function(data) {
						// TO CANCEL OLD REQUESTS
						var term2=$(input).val();
						if(term==term2) response(data["rows"]);
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
				this.value=ui.item.label;
				if(typeof(fn)!="undefined") eval(fn);
				return false;
			}
		});
	});
};

saltos.form_field_integer=function(field) {
	saltos.check_params(field,["label",
		"class2","colspan2","rowspan2","width2","required",
		"class","colspan","rowspan","width",
		"name","value","onchange","onkey","focus","label2","tip","class3",
		"readonly"]);
	var obj=[];
	if(field.label!="") {
		var td=$(`
			<td class="right nowrap label ${field.class2}" colspan="${field.colspan2}" rowspan="${field.rowspan2}" style="width:${field.width2}">
				${field.label}</td>
		`);
		if(field.required=="true") $(td).prepend("(*) ");
		obj.push(td);
	}
	var td=$(`
		<td class="left ${field.class}" colspan="${field.colspan}" rowspan="${field.rowspan}" style="width:${field.width}">
			<input type="text" name="${field.name}" id="${field.name}" value="" style="width:${field.width}"
				focused="${field.focus}" isrequired="${field.required}" labeled="${field.label}${field.label2}"
				title="${field.tip}" class="ui-state-default ui-corner-all ${field.class3}" autocomplete="off"/></td>
	`);
	$("input",td).val(field.value);
	//~ if(field.onchange!="") $("input",td).on("change",{event:field.onchange},saltos.__form_event);
	//~ if(field.onkey!="") $("input",td).on("keydown",{event:field.onkey},saltos.__form_event);
	if(field.onchange!="") $("input",td).attr("onchange",field.onchange);
	if(field.onkey!="") $("input",td).attr("onkeydown",field.onkey);
	if(field.readonly=="true") {
		$("input",td).attr("readonly","true").addClass("ui-state-disabled");
	} else {
		$("input",td).attr("isinteger","true");
	}
	obj.push(td);
	// PROGRAM INTERGER TYPE CAST
	$("input[isinteger=true]",td).each(function() {
		$(this).on("keyup",function() { intval2(this); });
	});
	return obj;
};

saltos.form_field_float=function(field) {
	saltos.check_params(field,["label",
		"class2","colspan2","rowspan2","width2","required",
		"class","colspan","rowspan","width",
		"name","value","onchange","onkey","focus","label2","tip","class3",
		"readonly"]);
	var obj=[];
	if(field.label!="") {
		var td=$(`
			<td class="right nowrap label ${field.class2}" colspan="${field.colspan2}" rowspan="${field.rowspan2}" style="width:${field.width2}">
				${field.label}</td>
		`);
		if(field.required=="true") $(td).prepend("(*) ");
		obj.push(td);
	}
	var td=$(`
		<td class="left ${field.class}" colspan="${field.colspan}" rowspan="${field.rowspan}" style="width:${field.width}">
			<input type="text" name="${field.name}" id="${field.name}" value="" style="width:${field.width}"
				focused="${field.focus}" isrequired="${field.required}" labeled="${field.label}${field.label2}"
				title="${field.tip}" class="ui-state-default ui-corner-all ${field.class3}" autocomplete="off"/></td>
	`);
	$("input",td).val(field.value);
	//~ if(field.onchange!="") $("input",td).on("change",{event:field.onchange},saltos.__form_event);
	//~ if(field.onkey!="") $("input",td).on("keydown",{event:field.onkey},saltos.__form_event);
	if(field.onchange!="") $("input",td).attr("onchange",field.onchange);
	if(field.onkey!="") $("input",td).attr("onkeydown",field.onkey);
	if(field.readonly=="true") {
		$("input",td).attr("readonly","true").addClass("ui-state-disabled");
	} else {
		$("input",td).attr("isfloat","true");
	}
	obj.push(td);
	// PROGRAM FLOAT TYPE CAST
	$("input[isfloat=true]",td).each(function() {
		$(this).on("keyup",function() { floatval2(this); });
	});
	return obj;
};

saltos.form_field_color=function(field) {
	saltos.check_params(field,["label",
		"class2","colspan2","rowspan2","width2","required",
		"class","colspan","rowspan","width",
		"name","value","onchange","onkey","focus","label2","tip","class3",
		"readonly","icon","tip2"]);
	var obj=[];
	if(field.label!="") {
		var td=$(`
			<td class="right nowrap label ${field.class2}" colspan="${field.colspan2}" rowspan="${field.rowspan2}" style="width:${field.width2}">
				${field.label}</td>
		`);
		if(field.required=="true") $(td).prepend("(*) ");
		obj.push(td);
	}
	var td=$(`
		<td class="left nowrap ${field.class}" colspan="${field.colspan}" rowspan="${field.rowspan}" style="width:${field.width}">
			<input type="text" name="${field.name}" id="${field.name}" value="" style="width:${field.width}"
				focused="${field.focus}" isrequired="${field.required}" labeled="${field.label}${field.label2}"
				title="${field.tip}" class="ui-state-default ui-corner-all ${field.class3}" autocomplete="off"/></td>
	`);
	$("input",td).val(field.value);
	//~ if(field.onchange!="") $("input",td).on("change",{event:field.onchange},saltos.__form_event);
	//~ if(field.onkey!="") $("input",td).on("keydown",{event:field.onkey},saltos.__form_event);
	if(field.onchange!="") $("input",td).attr("onchange",field.onchange);
	if(field.onkey!="") $("input",td).attr("onkeydown",field.onkey);
	if(field.readonly=="true") {
		$("input",td).attr("readonly","true").addClass("ui-state-disabled");
	} else {
		$("input",td).attr("iscolor","true");
		var link=$(`
			<a href="javascript:void(0)" class="ui-state-default ui-corner-all" iscolor="true" style="background-color:${field.value}">
				<span class="" title="${field.tip2}"></span></a>
		`)
		if(field.icon!="") $("span",link).attr("class",field.icon);
		$(td).append(link);
	}
	obj.push(td);
	// CREATE THE COLOR PICKERS
	$("input[iscolor=true]",td).each(function() {
		$(this).on("keyup",function() {
			$(this).next().css("background-color",$(this).val());
		});
	});
	$("a[iscolor=true]",td).each(function() {
		$(this).ColorPicker({
			onBeforeShow:function() {
				$(this).ColorPickerSetColor(substr($(this).prev().val(),1));
			},
			onShow:function(colpkr) {
				$(colpkr).show();
				return false;
			},
			onHide:function(colpkr) {
				$(colpkr).hide();
				return false;
			},
			onSubmit:function(hsb, hex, rgb, el) {
				$(el).css("background-color","#"+hex);
				$(el).prev().val("#"+strtoupper(hex));
			}
		});
	});
	$(".colorpicker",td).css("z-index",9999);
	return obj;
};

saltos.form_field_date=function(field) {
	saltos.check_params(field,["label",
		"class2","colspan2","rowspan2","width2","required",
		"class","colspan","rowspan","width",
		"name","value","onchange","onkey","focus","label2","tip","class3",
		"readonly","icon","tip2"]);
	var obj=[];
	if(field.label!="") {
		var td=$(`
			<td class="right nowrap label ${field.class2}" colspan="${field.colspan2}" rowspan="${field.rowspan2}" style="width:${field.width2}">
				${field.label}</td>
		`);
		if(field.required=="true") $(td).prepend("(*) ");
		obj.push(td);
	}
	var td=$(`
		<td class="left nowrap ${field.class}" colspan="${field.colspan}" rowspan="${field.rowspan}" style="width:${field.width}">
			<input type="text" name="${field.name}" id="${field.name}" value="" style="width:${field.width}"
				focused="${field.focus}" isrequired="${field.required}" labeled="${field.label}${field.label2}"
				title="${field.tip}" class="ui-state-default ui-corner-all ${field.class3}" autocomplete="off"/></td>
	`);
	$("input",td).val(field.value);
	//~ if(field.onchange!="") $("input",td).on("change",{event:field.onchange},saltos.__form_event);
	//~ if(field.onkey!="") $("input",td).on("keydown",{event:field.onkey},saltos.__form_event);
	if(field.onchange!="") $("input",td).attr("onchange",field.onchange);
	if(field.onkey!="") $("input",td).attr("onkeydown",field.onkey);
	if(field.readonly=="true") {
		$("input",td).attr("readonly","true").addClass("ui-state-disabled");
	} else {
		$("input",td).attr("isdate","true");
		var link=$(`
			<a href="javascript:void(0)" class="ui-state-default ui-corner-all" isdate="true">
				<span class="" title="${field.tip2}"></span></a>
		`)
		if(field.icon!="") $("span",link).attr("class",field.icon);
		$(td).append(link);
	}
	obj.push(td);
	// CREATE THE DATEPICKERS
	saltos.form_field_date_helper(td);
	return obj;
};

saltos.form_field_date_helper=function(td) {
	// CREATE THE DATEPICKERS
	$("input[isdate=true]",td).each(function() {
		$(this).datepicker({
			dateFormat:"yy-mm-dd",
			firstDay:1,
			numberOfMonths:3,
			showCurrentAtPos:1,
			stepMonths:3,
			showOn:"none",
			showAnim:"",
			constrainInput:false
		});
	});
	$("a[isdate=true]",td).on("click",function() {
		if(!is_disabled(this)) $(this).prev().datepicker("show");
	});
	$("input[isdate=true]",td).on("change",function() {
		if($(this).val()!="") $(this).val(dateval($(this).val()));
	});
};

saltos.form_field_time=function(field) {
	saltos.check_params(field,["label",
		"class2","colspan2","rowspan2","width2","required",
		"class","colspan","rowspan","width",
		"name","value","onchange","onkey","focus","label2","tip","class3",
		"readonly","icon","tip2"]);
	var obj=[];
	if(field.label!="") {
		var td=$(`
			<td class="right nowrap label ${field.class2}" colspan="${field.colspan2}" rowspan="${field.rowspan2}" style="width:${field.width2}">
				${field.label}</td>
		`);
		if(field.required=="true") $(td).prepend("(*) ");
		obj.push(td);
	}
	var td=$(`
		<td class="left nowrap ${field.class}" colspan="${field.colspan}" rowspan="${field.rowspan}" style="width:${field.width}">
			<input type="text" name="${field.name}" id="${field.name}" value="" style="width:${field.width}"
				focused="${field.focus}" isrequired="${field.required}" labeled="${field.label}${field.label2}"
				title="${field.tip}" class="ui-state-default ui-corner-all ${field.class3}" autocomplete="off"/></td>
	`);
	$("input",td).val(field.value);
	//~ if(field.onchange!="") $("input",td).on("change",{event:field.onchange},saltos.__form_event);
	//~ if(field.onkey!="") $("input",td).on("keydown",{event:field.onkey},saltos.__form_event);
	if(field.onchange!="") $("input",td).attr("onchange",field.onchange);
	if(field.onkey!="") $("input",td).attr("onkeydown",field.onkey);
	if(field.readonly=="true") {
		$("input",td).attr("readonly","true").addClass("ui-state-disabled");
	} else {
		$("input",td).attr("istime","true");
		var link=$(`
			<a href="javascript:void(0)" class="ui-state-default ui-corner-all" istime="true">
				<span class="" title="${field.tip2}"></span></a>
		`)
		if(field.icon!="") $("span",link).attr("class",field.icon);
		$(td).append(link);
	}
	obj.push(td);
	// CREATE THE TIMEPICKERS
	saltos.form_field_time_helper(td);
	return obj;
};

saltos.form_field_time_helper=function(td) {
	// CREATE THE TIMEPICKERS
	$("input[istime=true]",td).each(function() {
		$(this).timepicker({
			className:"ui-widget ui-state-default",
			scrollDefault:"now",
			showOn:"none",
			timeFormat:"H:i:s",
			step:15,
			show2400:true,
		});
	});
	$("a[istime=true]",td).on("click",function() {
		if(!is_disabled(this)) $(this).prev().timepicker('show');
	});
	$("input[istime=true]",td).on("change",function() {
		if($(this).val()!="") $(this).val(timeval($(this).val()));
	});
};

saltos.form_field_datetime=function(field) {
	saltos.check_params(field,["label",
		"class2","colspan2","rowspan2","width2","required",
		"class","colspan","rowspan","width",
		"name","value","onchange","onkey","focus","label2","tip","class3",
		"readonly","icon","tip2","icon2"]);
	var obj=[];
	if(field.label!="") {
		var td=$(`
			<td class="right nowrap label ${field.class2}" colspan="${field.colspan2}" rowspan="${field.rowspan2}" style="width:${field.width2}">
				${field.label}</td>
		`);
		if(field.required=="true") $(td).prepend("(*) ");
		obj.push(td);
	}
	var td=$(`
		<td class="left nowrap ${field.class}" colspan="${field.colspan}" rowspan="${field.rowspan}" style="width:${field.width}">
			<input type="hidden" name="${field.name}" id="${field.name}" value="" autocomplete="off"/></td>
	`);
	$("input",td).val(field.value);
	//~ if(field.onchange!="") $("input",td).on("change",{event:field.onchange},saltos.__form_event);
	if(field.onchange!="") $("input",td).attr("onchange",field.onchange);
	if(field.readonly!="true") {
		$("input",td).attr("isdatetime","true");
	}
	var width=(intval(field.width)/2)+"px";
	var value=explode(" ",field.value);
	if(!isset(value[1])) value[1]="";
	var date=$(`
		<input type="text" name="${field.name}_date" id="${field.name}_date" value="${value[0]}"
			style="width:${width}" focused="${field.focus}" isrequired="${field.required}" labeled="${field.label}${field.label2}"
			title="${field.tip}" class="ui-state-default ui-corner-all ${field.class3}" autocomplete="off"/>
	`);
	//~ if(field.onkey!="") $(date).on("keydown",{event:field.onkey},saltos.__form_event);
	if(field.onkey!="") $(date).attr("onkeydown",field.onkey);
	$(td).append(date);
	if(field.readonly=="true") {
		$(date).attr("readonly","true").addClass("ui-state-disabled");
	} else {
		$(date).attr("isdate","true");
		var link=$(`
			<a href="javascript:void(0)" class="ui-state-default ui-corner-all" isdate="true">
				<span class="" title="${field.tip2}"></span></a>
		`)
		if(field.icon!="") $("span",link).attr("class",field.icon);
		$(td).append(link);
	}
	var time=$(`
		<input type="text" name="${field.name}_time" id="${field.name}_time" value="${value[1]}"
			style="width:${width}" isrequired="${field.required}" labeled="${field.label}${field.label2}"
			title="${field.tip}" class="ui-state-default ui-corner-all ${field.class3}" autocomplete="off"/>
	`);
	//~ if(field.onkey!="") $(time).on("keydown",{event:field.onkey},saltos.__form_event);
	if(field.onkey!="") $(time).attr("onkeydown",field.onkey);
	$(td).append(time);
	if(field.readonly=="true") {
		$(time).attr("readonly","true").addClass("ui-state-disabled");
	} else {
		$(time).attr("istime","true");
		var link=$(`
			<a href="javascript:void(0)" class="ui-state-default ui-corner-all" istime="true">
				<span class="" title="${field.tip2}"></span></a>
		`)
		if(field.icon2!="") $("span",link).attr("class",field.icon2);
		$(td).append(link);
	}
	obj.push(td);
	// CREATE THE DATEPICKERS
	saltos.form_field_date_helper(td);
	// CREATE THE TIMEPICKERS
	saltos.form_field_time_helper(td);
	// PROGRAM THE DATETIME JOIN
	$("input[isdatetime=true]",td).each(function() {
		var name=$(this).attr("name");
		var full=$("input[name="+name+"]",td);
		var date=$("input[name="+name+"_date]",td);
		var time=$("input[name="+name+"_time]",td);
		$(date).on("change",function() {
			$(full).val($(date).val()+" "+$(time).val());
			$(full).trigger("change");
		});
		$(time).on("change",function() {
			$(full).val($(date).val()+" "+$(time).val());
			$(full).trigger("change");
		});
	});
	return obj;
};

saltos.form_field_textarea=function(field) {
	saltos.check_params(field,["label",
		"class2","colspan2","rowspan2","width2","required",
		"class","colspan","rowspan","width","height",
		"name","value","onchange","onkey","focus","label2","tip","class3",
		"autocomplete","querycomplete","filtercomplete","oncomplete",
		"readonly"]);
	var obj=[];
	if(field.label!="") {
		var td=$(`
			<td class="right nowrap label ${field.class2}" colspan="${field.colspan2}" rowspan="${field.rowspan2}" style="width:${field.width2}">
				${field.label}</td>
		`);
		obj.push(td);
		if(field.required=="true") $(td).prepend("(*) ");
	}
	var td=$(`
		<td class="left ${field.class}" colspan="${field.colspan}" rowspan="${field.rowspan}" style="width:${field.width}">
			<textarea name="${field.name}" id="${field.name}" style="width:${field.width};height:${field.height}"
				focused="${field.focus}" isrequired="${field.required}" labeled="${field.label}${field.label2}"
				title="${field.tip}" class="ui-state-default ui-corner-all ${field.class3}" autocomplete="off"
				isautocomplete="${field.autocomplete}" querycomplete="${field.querycomplete}"
				filtercomplete="${field.filtercomplete}" oncomplete="${field.oncomplete}"></textarea></td>
	`);
	$("textarea",td).val(field.value);
	//~ if(field.onchange!="") $("textarea",td).on("change",{event:field.onchange},saltos.__form_event);
	//~ if(field.onkey!="") $("textarea",td).on("keydown",{event:field.onkey},saltos.__form_event);
	if(field.onchange!="") $("textarea",td).attr("onchange",field.onchange);
	if(field.onkey!="") $("textarea",td).attr("onkeydown",field.onkey);
	if(field.readonly=="true") {
		$("textarea",td).attr("readonly","true").addClass("ui-state-disabled");
	}
	obj.push(td);
	// AUTO-GROWING TEXTAREA
	$("textarea[ckeditor!=true][codemirror!=true]",td).each(function() {
		if($(this).attr("id")=="") return;
		var textarea="#"+$(this).attr("id");
		var interval=setInterval(function() {
			var textarea2=$(textarea);
			if(!$(textarea2).length) {
				clearInterval(interval);
			} else if($(textarea2).is(":visible")) {
				clearInterval(interval);
				$(textarea2).autogrow();
			}
		},100);
	});
	// PROGRAM AUTOCOMPLETE FIELDS
	saltos.form_field_autocomplete_helper(td);
	return obj;
};

saltos.form_field_ckeditor=function(field) {
	saltos.check_params(field,["label",
		"class2","colspan2","rowspan2","width2","required",
		"class","colspan","rowspan","width","height",
		"name","value","onchange","onkey","focus","label2","tip","class3",
		"ckeditor","ckextra","readonly"]);
	var obj=[];
	if(field.label!="") {
		var td=$(`
			<td class="right nowrap label ${field.class2}" colspan="${field.colspan2}" rowspan="${field.rowspan2}" style="width:${field.width2}">
				${field.label}</td>
		`);
		obj.push(td);
		if(field.required=="true") $(td).prepend("(*) ");
	}
	var td=$(`
		<td class="left ${field.class}" colspan="${field.colspan}" rowspan="${field.rowspan}" style="width:${field.width}">
			<textarea name="${field.name}" id="${field.name}" style="width:${field.width};height:${field.height}"
				focused="${field.focus}" isrequired="${field.required}" labeled="${field.label}${field.label2}"
				title="${field.tip}" class="ui-state-default ui-corner-all ${field.class3}" autocomplete="off"
				ckeditor="${field.ckeditor}" ckextra="${field.ckextra}"></textarea></td>
	`);
	$("textarea",td).val(field.value);
	//~ if(field.onchange!="") $("textarea",td).on("change",{event:field.onchange},saltos.__form_event);
	//~ if(field.onkey!="") $("textarea",td).on("keydown",{event:field.onkey},saltos.__form_event);
	if(field.onchange!="") $("textarea",td).attr("onchange",field.onchange);
	if(field.onkey!="") $("textarea",td).attr("onkeydown",field.onkey);
	if(field.readonly=="true") {
		$("textarea",td).attr("readonly","true").addClass("ui-state-disabled");
	}
	obj.push(td);
	setTimeout(function() {
		// CREATE THE CKEDITORS
		$("textarea[ckeditor=true]",td).each(function() {
			$(this).ckeditor({
				title:"",
				skin:"moono-lisa",
				extraPlugins:"codesnippetgeshi,autogrow,base64image",
				removePlugins:"elementspath",
				enterMode:CKEDITOR.ENTER_BR,
				shiftEnterMode:CKEDITOR.ENTER_BR,
				toolbar:[["Bold","Italic","Underline","Strike"],["NumberedList","BulletedList","-","Outdent","Indent"],["Link","Unlink"],["TextColor","BGColor"],["Undo","Redo"],["Maximize","Source","CodeSnippet","base64image","HorizontalRule"]],
				language:lang_default(),
				autoGrow_onStartup:true,
				autoGrow_minHeight:$(this).height()-25,
				//~ width:$(this).width()+10,
				disableNativeSpellChecker:false,
				dialog_backgroundCoverColor:"#aaa",
				dialog_backgroundCoverOpacity:0.3,
				resize_enabled:false,
				codeSnippetGeshi_url:"../../?action=geshi",
				//~ forcePasteAsPlainText:true,
				//~ uiColor:get_colors("ui-state-default","background-color"),
				//~ uiColor:"transparent",
				allowedContent:true,
				extraAllowedContent:$(this).attr("ckextra"),
			},function() {
				var obj=$("#"+$(this).attr("name")).next();
				$(obj).addClass("ui-state-default ui-corner-all");
				$(obj).on("mouseover",function() {
					$(this).addClass("ui-state-hover");
				}).on("mouseout",function() {
					$(this).removeClass("ui-state-hover");
				}).on("focus",function() {
					$(this).addClass("ui-state-focus");
				}).on("blur",function() {
					$(this).removeClass("ui-state-focus");
				});
			});
		});
	},100);
	return obj;
};

saltos.form_field_codemirror=function(field) {
	saltos.check_params(field,["label",
		"class2","colspan2","rowspan2","width2","required",
		"class","colspan","rowspan","width","height",
		"name","value","onchange","onkey","focus","label2","tip","class3",
		"codemirror","readonly"]);
	var obj=[];
	if(field.label!="") {
		var td=$(`
			<td class="right nowrap label ${field.class2}" colspan="${field.colspan2}" rowspan="${field.rowspan2}" style="width:${field.width2}">
				${field.label}</td>
		`);
		obj.push(td);
		if(field.required=="true") $(td).prepend("(*) ");
	}
	var td=$(`
		<td class="left ${field.class}" colspan="${field.colspan}" rowspan="${field.rowspan}" style="width:${field.width}">
			<textarea name="${field.name}" id="${field.name}" style="width:${field.width};height:${field.height}"
				focused="${field.focus}" isrequired="${field.required}" labeled="${field.label}${field.label2}"
				title="${field.tip}" class="ui-state-default ui-corner-all ${field.class3}" autocomplete="off"
				codemirror="${field.codemirror}"></textarea></td>
	`);
	$("textarea",td).val(field.value);
	//~ if(field.onchange!="") $("textarea",td).on("change",{event:field.onchange},saltos.__form_event);
	//~ if(field.onkey!="") $("textarea",td).on("keydown",{event:field.onkey},saltos.__form_event);
	if(field.onchange!="") $("textarea",td).attr("onchange",field.onchange);
	if(field.onkey!="") $("textarea",td).attr("onkeydown",field.onkey);
	if(field.readonly=="true") {
		$("textarea",td).attr("readonly","true").addClass("ui-state-disabled");
	}
	obj.push(td);
	setTimeout(function() {
		// CREATE THE CODE MIRROR
		$("textarea[codemirror=true]",td).each(function() {
			$(this).css("overflow","hidden"); // TRICK FOR THE FUCKED GOOGLE CHROME
			var width=$(this).width();
			var height=$(this).height();
			var classes=$(this).attr("class");
			var cm=CodeMirror.fromTextArea(this,{
				lineNumbers:true
			});
			$(this).data("cm",cm);
			var fnresize=function(cm) {
				var height2=max(height,cm.doc.size*14);
				if(cm.display.sizerWidth>cm.display.lastWrapWidth) height2+=14;
				cm.setSize(width+10,height2+10);
			}
			fnresize(cm);
			cm.on("viewportChange",fnresize);
			$(this).next().addClass(classes).css("margin","1px");
			cm.on("change",cm.save);
		});
	},100);
	return obj;
};

saltos.form_field_iframe=function(field) {
	saltos.check_params(field,["label",
		"class2","colspan2","rowspan2","width2",
		"class3","colspan","rowspan","width","height",
		"name","value","onchange","onkey","focus","tip","class"]);
	var obj=[];
	if(field.label!="") {
		var td=$(`
			<td class="right nowrap label ${field.class2}" colspan="${field.colspan2}" rowspan="${field.rowspan2}" style="width:${field.width2}">
				${field.label}</td>
		`);
		obj.push(td);
	}
	var td=$(`
		<td class="left ${field.class3}" colspan="${field.colspan}" rowspan="${field.rowspan}" style="width:${field.width}">
			<iframe src="" url="" name="${field.name}" id="${field.name}" style="width:${field.width};height:${field.height}"
				focused="${field.focus}" frameborder="0" title="${field.tip}" class="${field.class}"></iframe></td>
	`);
	$("iframe",td).attr("url",field.value);
	if(field.class=="") $("iframe",td).addClass("ui-state-default ui-corner-all iframe");
	//~ if(field.onchange!="") $("iframe",td).on("change",{event:field.onchange},saltos.__form_event);
	//~ if(field.onkey!="") $("iframe",td).on("keydown",{event:field.onkey},saltos.__form_event);
	if(field.onchange!="") $("iframe",td).attr("onchange",field.onchange);
	if(field.onkey!="") $("iframe",td).attr("onkeydown",field.onkey);
	obj.push(td);
	// AUTO-GROWING IFRAMES
	$("iframe",td).each(function() {
		if($(this).attr("id")=="") return;
		var iframe="#"+$(this).attr("id");
		var interval=setInterval(function() {
			var iframe2=$(iframe);
			if(!$(iframe2).length) {
				clearInterval(interval);
			} else if($(iframe2).is(":visible")) {
				if(typeof($(iframe2).prop("isloaded"))=="undefined") {
					$(iframe2).each(function() {
						$(this).prop("isloaded","false");
						$(this).on("load",function() {
							$(this).prop("isloaded","true");
						});
						var iframe3=this.contentWindow.location;
						var url=$(this).attr("url");
						if(url) iframe3.replace(url);
						if(!url) clearInterval(interval);
					});
				} else if($(iframe2).prop("isloaded")=="true") {
					clearInterval(interval);
					if(security_iframe(iframe2)) {
						var minheight=$(iframe2).height();
						var newheight=$(iframe2).contents().height()+20;
						if(newheight>minheight) $(iframe2).height(newheight);
						$(iframe2).each(function() {
							var iframe3=this.contentWindow.document;
							$(iframe3).on("contextmenu",function(e) { return e.ctrlKey; });
							$(iframe3).on("keydown",function(e) { $(document).trigger(e); });
						});
					}
				}
			}
		},100);
	});
	return obj;
};

saltos.form_field_select=function(field) {
	saltos.check_params(field,["label",
		"class2","colspan2","rowspan2","width2","required",
		"class","colspan","rowspan","width",
		"name","value","onchange","onkey","focus","label2","tip","class3",
		"dir",
		"readonly","link","icon","tip2"]);
	saltos.check_params(field,["rows"],[]);
	var obj=[];
	if(field.label!="") {
		var td=$(`
			<td class="right nowrap label ${field.class2}" colspan="${field.colspan2}" rowspan="${field.rowspan2}" style="width:${field.width2}">
				${field.label}</td>
		`);
		if(field.required=="true") $(td).prepend("(*) ");
		obj.push(td);
	}
	var td=$(`
		<td class="left nowrap ${field.class}" colspan="${field.colspan}" rowspan="${field.rowspan}" style="width:${field.width}">
			<select name="${field.name}" id="${field.name}" style="width:${field.width}"
				focused="${field.focus}" isrequired="${field.required}" labeled="${field.label}${field.label2}"
				title="${field.tip}" class="ui-state-default ui-corner-all ${field.class3}" autocomplete="off"
				original="" width2="${field.width}" dir="${field.dir}"></select></td>
	`);
	$("select",td).attr("original",field.value);
	//~ if(field.onchange!="") $("select",td).on("change",{event:field.onchange},saltos.__form_event);
	//~ if(field.onkey!="") $("select",td).on("keydown",{event:field.onkey},saltos.__form_event);
	if(field.onchange!="") $("select",td).attr("onchange",field.onchange);
	if(field.onkey!="") $("select",td).attr("onkeydown",field.onkey);
	if(field.readonly=="true") $("select",td).attr("disabled","true");
	for(var key in field.rows) {
		var row=field.rows[key];
		saltos.check_params(row,["label","value"]);
		var option=$(`
			<option value=""></option>
		`);
		$(option).val(row.value);
		$(option).text(row.label);
		if(field.value==row.value) $(option).attr("selected","true");
		$("select",td).append(option);
	}
	if(field.link!="" && field.readonly=="true") {
		var link=$(`
			<a href="javascript:void(0)" class="ui-state-default ui-corner-all" islink="true" fnlink="${field.link}" forlink="${field.name}">
				<span class="${field.icon}" title="${field.tip2}"></span></a>
		`)
		$(td).append(link);
	}
	if(field.readonly=="true") {
		var input=$(`
			<input type="hidden" name="${field.name}" id="${field.name}" value="" class="${field.class}" autocomplete="off">
		`);
		$(input).val(field.value);
		$(td).append(input);
		//~ if(field.onchange!="") $(input).on("change",{event:field.onchange},saltos.__form_event);
		if(field.onchange!="") $(input).attr("onchange",field.onchange);
	}
	obj.push(td);
	// PROGRAM SELECTS
	saltos.form_field_select_helper(td);
	// PROGRAM LINKS OF SELECTS
	saltos.form_field_islink_helper(td);
	return obj;
};

saltos.form_field_select_helper=function(td) {
	// PROGRAM SELECTS
	$("select:not([multiple])",td).each(function() {
		var width=$(this).css("width");
		// TO FIX SOME GOOGLE CHROME ISSUES
		var width2=$(this).width();
		if(substr(width,-2,2)=="px" && intval(width)>0 && intval(width2)>0) {
			if(intval(width)!=intval(width2)) {
				width=(intval(width)+12)+"px";
			} else {
				// TO FIX SOME GOOGLE CHROME ISSUES
				width=(intval(width)+5)+"px";
			}
		} else {
			var width2=$(this).attr("width2");
			if(typeof(width2)=="undefined") width2="";
			if(width2!="") {
				width=width2;
			} else {
				width="auto";
			}
		}
		var position={my:"left top",at:"left bottom",collision:"none"};
		if($(this).attr("dir")=="up") position={my:"left bottom",at:"left top",collision:"none"};
		if($(this).attr("dir")=="right") position={my:"right top",at:"right bottom",collision:"none"};
		if($(this).attr("dir")=="up right") position={my:"right bottom",at:"right top",collision:"none"};
		$(this).selectmenu({
			width:width,
			position:position,
			change:function() {
				$(this).trigger("change");
			},
			open:function() {
				saltos.hide_tooltips();
				if(dialog("isopen")) {
					var zindex=$(".ui-dialog").css("z-index");
					$(".ui-selectmenu-open").css("z-index",zindex);
				}
			},
			close:function() {
				saltos.hide_tooltips();
			}
		});
	}).on("refresh change",function() {
		if(typeof($(this).selectmenu("instance"))!="undefined") {
			$(this).selectmenu("refresh");
		}
	});
};

saltos.form_field_multiselect=function(field) {
	saltos.check_params(field,["label",
		"class2","colspan2","rowspan2","width2","required",
		"class","colspan","rowspan","width","height",
		"name","value","onchange","onkey","focus","label2","tip","class3",
		"readonly","tip2"]);
	saltos.check_params(field,["rows"],[]);
	var obj=[];
	if(field.label!="") {
		var td=$(`
			<td class="right nowrap label ${field.class2}" colspan="${field.colspan2}" rowspan="${field.rowspan2}" style="width:${field.width2}">
				${field.label}</td>
		`);
		if(field.required=="true") $(td).prepend("(*) ");
		obj.push(td);
	}
	var width=((intval(field.width)-20)/2)+"px";
	var height=(intval(field.height)+6)+"px";
	var td=$(`
		<td class="left nowrap ${field.class}" colspan="${field.colspan}" rowspan="${field.rowspan}" style="width:${field.width};height:${field.height}">
			<input type="hidden" name="${field.name}" id="${field.name}" value="" ismultiselect="true" autocomplete="off"/>
			<table align="left" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td>
						<select multiple="multiple" name="${field.name}_all" id="${field.name}_all" style="width:${width};height:${height}"
							focused="${field.focus}" isrequired="${field.required}" labeled="${field.label}${field.label2}" title="${field.tip}"
							class="ui-state-default ui-corner-all ${field.class3}" autocomplete="off">
						</select>
					</td>
					<td>
						<a href="javascript:void(0)" class="ui-state-default ui-corner-all" name="${field.name}_add" id="${field.name}_add">
							<span class="ui-icon ui-icon-circle-arrow-e" title="${field.tip2}"></span>
						</a>
						<br/>
						<a href="javascript:void(0)" class="ui-state-default ui-corner-all" name="${field.name}_del" id="${field.name}_del">
							<span class="ui-icon ui-icon-circle-arrow-w" title="${field.tip2}"></span>
						</a>
					</td>
					<td>
						<select multiple="multiple" name="${field.name}_set" id="${field.name}_set" style="width:${width};height:${height}"
							focused="${field.focus}" isrequired="${field.required}" labeled="${field.label}${field.label2}" title="${field.tip}"
							class="ui-state-default ui-corner-all ${field.class3}" autocomplete="off">
						</select>
					</td>
				</tr>
			</table>
		</td>
	`);
	$("input",td).val(field.value);
	//~ if(field.onchange!="") $("input,select",td).on("change",{event:field.onchange},saltos.__form_event);
	//~ if(field.onkey!="") $("select",td).on("keydown",{event:field.onkey},saltos.__form_event);
	if(field.onchange!="") $("input,select",td).attr("onchange",field.onchange);
	if(field.onkey!="") $("select",td).attr("onkeydown",field.onkey);
	if(field.readonly=="true") {
		$("select",td).attr("disabled","true").addClass("ui-state-disabled");
		$("a",td).addClass("ui-state-disabled");
	}
	for(var key in field.rows) {
		var row=field.rows[key];
		saltos.check_params(row,["label","value"]);
		var option=$(`
			<option value=""></option>
		`);
		$(option).val(row.value);
		$(option).text(row.label);
		if(field.value==row.value) $(option).attr("selected","true");
		$("select",td).append(option);
	}
	obj.push(td);
	// PROGRAM MULTISELECTS
	$("input[ismultiselect=true]",td).each(function() {
		var value=explode(",",$(this).val());
		var name=$(this).attr("name");
		$("select[name="+name+"_all] option",td).each(function() {
			if(in_array($(this).attr("value"),value)) $(this).remove();
		});
		$("select[name="+name+"_set] option",td).each(function() {
			if(!in_array($(this).attr("value"),value)) $(this).remove();
		});
		$("a[name="+name+"_add]",td).on("click",function() {
			$("select[name="+name+"_all] option:selected").each(function() {
				$("select[name="+name+"_set]").append($(this).clone());
				$(this).remove();
			});
			var value=[];
			$("select[name="+name+"_set] option").each(function() {
				value.push($(this).val());
			});
			value=implode(",",value);
			$("input[name="+name+"]").val(value);
		});
		$("a[name="+name+"_del]",td).on("click",function() {
			$("select[name="+name+"_set] option:selected").each(function() {
				$("select[name="+name+"_all]").append($(this).clone());
				$(this).remove();
			});
			var value=[];
			$("select[name="+name+"_set] option").each(function() {
				value.push($(this).val());
			});
			value=implode(",",value);
			$("input[name="+name+"]").val(value);
		});
	});
	return obj;
};

saltos.form_field_checkbox=function(field) {
	saltos.check_params(field,["label",
		"class2","colspan2","rowspan2","width",
		"name","value","onchange","onkey","focus","label2","tip","class3",
		"checked","readonly",
		"class","colspan","rowspan","width2",
		"icon"]);
	var obj=[];
	if(field.label!="") {
		var td=$(`
			<td class="left nowrap label ${field.class}" colspan="${field.colspan}"
				rowspan="${field.rowspan}" style="width:${field.width2}"></td>
		`);
		if(field.icon!="") {
			$(td).append(`
				<label for="${field.name}">
					<span class="${field.icon} ${field.class3}" title="${field.label}"></span>
				</label>
			`);
		} else if(field.readonly=="true") {
			$(td).append(field.label);
		} else {
			$(td).append(`
				<label for="${field.name}" title="${field.tip}">${field.label}</label>
			`);
		}
		obj.push(td);
		// TRICK TO HOVER CHECKBOXES
		$("label",td).on("mouseover",function() {
			if($(this).hasClass("ui-state-disabled")) return;
			$(this).parent().addClass("checkbox-focused").prev().addClass("checkbox-focused");
		}).on("mouseout",function() {
			if($(this).hasClass("ui-state-disabled")) return;
			$(this).parent().removeClass("checkbox-focused").prev().removeClass("checkbox-focused");
		}).on("focus",function() {
			if($(this).hasClass("ui-state-disabled")) return;
			$(this).parent().addClass("checkbox-focused").prev().addClass("checkbox-focused");
		}).on("blur",function() {
			if($(this).hasClass("ui-state-disabled")) return;
			$(this).parent().removeClass("checkbox-focused").prev().removeClass("checkbox-focused");
		});
	}
	var td=$(`
		<td class="right ${field.class2}" colspan="${field.colspan2}" rowspan="${field.rowspan2}" style="width:${field.width}">
			<input type="checkbox" name="${field.name}" id="${field.name}" value="" focused="${field.focus}"
				labeled="${field.label}${field.label2}" title="${field.tip}" class="${field.class3}" autocomplete="off"/></td>
	`);
	$("input",td).val(field.value);
	if(field.checked=="true") $("input",td).attr("checked","checked");
	if(field.readonly=="true") $("input",td).addClass("ui-state-disabled");
	//~ if(field.onchange!="") $("input",td).on("change",{event:field.onchange},saltos.__form_event);
	//~ if(field.onkey!="") $("input",td).on("keydown",{event:field.onkey},saltos.__form_event);
	if(field.onchange!="") $("input",td).attr("onchange",field.onchange);
	if(field.onkey!="") $("input",td).attr("onkeydown",field.onkey);
	obj.unshift(td);
	// TRICK TO HOVER CHECKBOXES
	$("input",td).on("mouseover",function() {
		if($(this).hasClass("ui-state-disabled")) return;
		$(this).parent().addClass("checkbox-focused").next().addClass("checkbox-focused");
	}).on("mouseout",function() {
		if($(this).hasClass("ui-state-disabled")) return;
		$(this).parent().removeClass("checkbox-focused").next().removeClass("checkbox-focused");
	}).on("focus",function() {
		if($(this).hasClass("ui-state-disabled")) return;
		$(this).parent().addClass("checkbox-focused").next().addClass("checkbox-focused");
	}).on("blur",function() {
		if($(this).hasClass("ui-state-disabled")) return;
		$(this).parent().removeClass("checkbox-focused").next().removeClass("checkbox-focused");
	});
	// TRICK TO BLOCK CHECKBOXES
	$("input:checkbox.ui-state-disabled",td).on("change",function(event) {
		$(this).prop("checked",!$(this).prop("checked"));
	});
	return obj;
};

saltos.form_field_button=function(field) {
	saltos.check_params(field,["colspan","rowspan","class","width",
		"onclick","focus","label","width2","tip","class2","name",
		"icon","value","disabled"]);
	var obj=[];
	var td=$(`
		<td colspan="${field.colspan}" rowspan="${field.rowspan}" class="${field.class}" style="width:${field.width}">
			<a href="javascript:void(0)" focused="${field.focus}" labeled="${field.label}" style="width:${field.width2}"
				title="${field.tip}" class="ui-state-default ui-corner-all ${field.class2}" id="${field.name}">
				<span class="${field.icon}"></span></a></td>
	`);
	$("a",td).append(" "+field.value);
	if(field.disabled=="true") {
		$("a",td).addClass("ui-state-disabled");
	} else {
		//~ $("a",td).on("click",{event:field.onclick},saltos.__form_event);
		$("a",td).attr("onclick",field.onclick);
	}
	obj.push(td);
	return obj;
};

saltos.form_field_password=function(field) {
	saltos.check_params(field,["label",
		"class2","colspan2","rowspan2","width2","required",
		"class","colspan","rowspan","width",
		"name","value","onchange","onkey","focus","label2","tip","class3",
		"readonly"]);
	var obj=[];
	if(field.label!="") {
		var td=$(`
			<td class="right nowrap label ${field.class2}" colspan="${field.colspan2}" rowspan="${field.rowspan2}" style="width:${field.width2}">
				${field.label}</td>
		`);
		if(field.required=="true") $(td).prepend("(*) ");
		obj.push(td);
	}
	var td=$(`
		<td class="left ${field.class}" colspan="${field.colspan}" rowspan="${field.rowspan}" style="width:${field.width}">
			<input type="password" name="${field.name}" id="${field.name}" value="" style="width:${field.width}"
				focused="${field.focus}" isrequired="${field.required}" labeled="${field.label}${field.label2}"
				title="${field.tip}" class="ui-state-default ui-corner-all ${field.class3}" autocomplete="off"/></td>
	`);
	$("input",td).val(field.value);
	//~ if(field.onchange!="") $("input",td).on("change",{event:field.onchange},saltos.__form_event);
	//~ if(field.onkey!="") $("input",td).on("keydown",{event:field.onkey},saltos.__form_event);
	if(field.onchange!="") $("input",td).attr("onchange",field.onchange);
	if(field.onkey!="") $("input",td).attr("onkeydown",field.onkey);
	if(field.readonly=="true") $("input",td).attr("readonly","true").addClass("ui-state-disabled");
	obj.push(td);
	return obj;
};

saltos.form_field_file=function(field) {
	saltos.check_params(field,["label",
		"class2","colspan2","rowspan2","width2","required",
		"class","colspan","rowspan","width","size",
		"name","value","focus","label2","tip","class3"]);
	var obj=[];
	if(field.label!="") {
		var td=$(`
			<td class="right nowrap label ${field.class2}" colspan="${field.colspan2}" rowspan="${field.rowspan2}" style="width:${field.width2}">
				${field.label}</td>
		`);
		if(field.required=="true") $(td).prepend("(*) ");
		obj.push(td);
	}
	var td=$(`
		<td class="left ${field.class}" colspan="${field.colspan}" rowspan="${field.rowspan}" style="width:${field.width}">
			<input type="file" name="${field.name}" id="${field.name}" value="" style="width:${field.width}"
				size="${field.size}" focused="${field.focus}" isrequired="${field.required}" labeled="${field.label}${field.label2}"
				title="${field.tip}" class="${field.class3}" autocomplete="off"/></td>
	`);
	$("input",td).val(field.value);
	obj.push(td);
	return obj;
};

saltos.form_field_link=function(field) {
	saltos.check_params(field,["label","label2",
		"class2","colspan2","rowspan2","width2",
		"class","colspan","rowspan","width",
		"focus","tip","class3","name",
		"icon","onclick","value"]);
	var obj=[];
	if(field.label2!="") {
		var td=$(`
			<td class="right nowrap label ${field.class2}" colspan="${field.colspan2}" rowspan="${field.rowspan2}" style="width:${field.width2}">
				${field.label2}</td>
		`);
		obj.push(td);
	}
	var tip=field.tip;
	if(tip=="") tip=field.label;
	var td=$(`
		<td class="left nowrap ${field.class}" colspan="${field.colspan}" rowspan="${field.rowspan}" style="width:${field.width}">
			<a href="javascript:void(0)" focused="${field.focus}" labeled="${field.label}" title="${tip}" class="${field.class3}" id="${field.name}"></a></td>
	`);
	//~ if(field.onclick!="") $("a",td).on("click",{event:field.onclick},saltos.__form_event);
	//~ else if(field.value!="") $("a",td).on("click",{event:field.value},saltos.__form_event);
	if(field.onclick!="") $("a",td).attr("onclick",field.onclick);
	else if(field.value!="") $("a",td).attr("onclick",field.value);
	if(field.icon!="") {
		$("a",td).append(`<span class="${field.icon} ${field.class2}" labeled="${field.label}" title="${tip}"></span>`);
	} else {
		$("a",td).append(field.label);
	}
	obj.push(td);
	return obj;
};

saltos.form_field_separator=function(field) {
	saltos.check_params(field,["class","colspan","rowspan","width","height","name"]);
	var obj=[];
	var td=$(`
		<td class="separator ${field.class}" colspan="${field.colspan}" rowspan="${field.rowspan}"
			style="width:${field.width};height:${field.height}" id="${field.name}"></td>
	`);
	obj.push(td);
	return obj;
};

saltos.form_field_label=function(field) {
	saltos.check_params(field,[
		"class","colspan","rowspan","width","height","name",
		"icon","class2","label","tip","value"]);
	var obj=[];
	var td=$(`
		<td class="left nowrap label ${field.class}" colspan="${field.colspan}" rowspan="${field.rowspan}"
			style="width:${field.width};height:${field.height}" id="${field.name}"></td>
	`);
	if(field.value!="") {
		$(td).append(saltos.get_filtered_field(field.value,"",""));
	} else if(field.icon!="") {
		$(td).append(`<span class="${field.icon} ${field.class2}" title="${field.label}"></span>`);
	} else if(field.label!="") {
		$(td).append(`<span class="${field.class2}" title="${field.tip}">${field.label}</span>`);
	}
	obj.push(td);
	$(".info",td).addClass("ui-state-highlight ui-corner-all");
	$(".error",td).addClass("ui-state-error ui-corner-all");
	$(".title",td).addClass("ui-widget-header ui-corner-all");
	if($(td).hasClass("info")) $(td).addClass("ui-state-highlight ui-corner-all");
	if($(td).hasClass("error")) $(td).addClass("ui-state-error ui-corner-all");
	if($(td).hasClass("title")) $(td).addClass("ui-widget-header ui-corner-all");
	return obj;
};

saltos.form_field_image=function(field) {
	saltos.check_params(field,["label",
		"class2","colspan2","rowspan2","width2",
		"class3","colspan","rowspan","width","height",
		"class","tip","name","phpthumb","image","value"]);
	var obj=[];
	if(field.label!="") {
		var td=$(`
			<td class="right nowrap label ${field.class2}" colspan="${field.colspan2}" rowspan="${field.rowspan2}" style="width:${field.width2}">
				${field.label}</td>
		`);
		obj.push(td);
	}
	var td=$(`
		<td class="left ${field.class3}" colspan="${field.colspan}" rowspan="${field.rowspan}" style="width:${field.width};height:${field.height}">
			<img class="${field.class}" src="" title="${field.tip}" id="${field.name}"/></td>
	`);
	if(field.class=="") $("img",td).addClass("ui-state-default ui-corner-all image");
	var image=field.image;
	if(image=="") image=field.value;
	if(field.phpthumb=="false") {
		$("img",td).attr("src",image);
	} else {
		$("img",td).attr("src",`?action=phpthumb&amp;src=${image}&amp;w=${field.width}&amp;h=${field.height}`);
	}
	obj.push(td);
	return obj;
};

saltos.form_field_plot=function(field) {
	saltos.check_params(field,["label",
		"class2","colspan2","rowspan2","width2",
		"class3","colspan","rowspan","width","height",
		"name","class"]);
	var obj=[];
	if(field.label!="") {
		var td=$(`
			<td class="right nowrap label ${field.class2}" colspan="${field.colspan2}" rowspan="${field.rowspan2}" style="width:${field.width2}">
				${field.label}</td>
		`);
		obj.push(td);
	}
	var uniqid=saltos.uniqid();
	var td=$(`
		<td class="left ${field.class3}" colspan="${field.colspan}" rowspan="${field.rowspan}" style="width:${field.width};height:${field.height}">
			<map name="map${uniqid}" id="map${uniqid}"></map>
			<img style="width:${field.width};height:${field.height}" class="${field.class}"
				src="?action=phplot&amp;width=${field.width}&amp;height=${field.height}&amp;format=png&amp;loading=1"
				isplot="true" id="${field.name}" usemap="#map${uniqid}"/></td>
	`);
	if(field.class=="") $("img",td).addClass("ui-state-default ui-corner-all image phplot");
	obj.push(td);
	// REQUEST THE PLOTS
	var attr1=["width","height","title","legend","vars","colors","graph"];
	var attr2={"x0":"posx","y0":"ticks","y1":"data1","y2":"data2","y3":"data3","y4":"data4","y5":"data5","y6":"data6","y7":"data7","y8":"data8","y9":"data9","y10":"data10","y11":"data11","y12":"data12","y13":"data13","y14":"data14","y15":"data15","y16":"data16"};
	$("img[isplot=true]",td).each(function() {
		var map="#"+$(this).prev().attr("id");
		var interval=setInterval(function() {
			var map2=$(map);
			var img=$(map2).next().get(0);
			if(!$(map2).length) {
				clearInterval(interval);
			} else if($(img).is(":visible")) {
				clearInterval(interval);
				var querystring="action=phplot";
				for(var key in attr1) {
					if(isset(field[attr1[key]])) querystring+="&"+attr1[key]+"="+encodeURIComponent(field[attr1[key]]);
				};
				for(var key in field.rows.row) {
					querystring+="&"+attr2[key]+"="+encodeURIComponent(implode("|",array_column(field.rows,key)));
				}
				$.ajax({
					url:"index.php",
					data:querystring,
					type:"post",
					success:function(response) {
						$(img).attr("src",response["img"]);
						var map=$(img).attr("usemap");
						$(response["map"]).each(function() {
							var area="<area shape='"+this["shape"]+"' coords='"+this["coords"]+"' title='"+this["value"]+"'>";
							$(map).append(area);
						});
					},
					error:function(XMLHttpRequest,textStatus,errorThrown) {
						errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
					}
				});
			}
		},100);
	});
	return obj;
};

saltos.form_field_menu=function(field) {
	saltos.check_params(field,["label",
		"class2","colspan2","rowspan2","width2",
		"colspan","rowspan","class","width",
		"name","focus","required","tip",
		"onchange","onkey","readonly"]);
	var obj=[];
	if(field.label!="") {
		var td=$(`
			<td class="right nowrap label ${field.class2}" colspan="${field.colspan2}" rowspan="${field.rowspan2}" style="width:${field.width2}">
				${field.label}</td>
		`);
		obj.push(td);
	}
	var td=$(`
		<td colspan="${field.colspan}" rowspan="${field.rowspan}" class="${field.class}" style="width:${field.width}">
			<select name="${field.name}" id="${field.name}" style="width:${field.width}" focused="${field.focus}"
				isrequired="${field.required}" labeled="${field.label}" title="${field.tip}"
				class="ui-state-default ui-corner-all ${field.class2}" ismenu="true" autocomplete="off"></select></td>
	`);
	//~ if(field.onchange!="") $("select",td).on("change",{event:field.onchange},saltos.__form_event);
	//~ if(field.onkey!="") $("select",td).on("keydown",{event:field.onkey},saltos.__form_event);
	if(field.onchange!="") $("select",td).attr("onchange",field.onchange);
	if(field.onkey!="") $("select",td).attr("onkeydown",field.onkey);
	if(field.readonly=="true") $("select",td).attr("disabled","true");
	for(var key in field) {
		if(saltos.limpiar_key(key)=="option") {
			var field2=field[key];
			saltos.check_params(field2,["onclick","class","label","disabled"]);
			var option=$(`
				<option value="" class="${field2.class}"></option>
			`);
			$(option).val(field2.onclick);
			$(option).text(field2.label);
			if(field2.disabled=="true") $(option).attr("disabled","disabled");
			$("select",td).append(option);
		}
		if(saltos.limpiar_key(key)=="group") {
			var field2=field[key];
			saltos.check_params(field2,["label","class"]);
			var option=$(`
				<optgroup label="" class="${field2.class}"></optgroup>
			`);
			$(option).attr("label",field2.label);
			$("select",td).append(option);
			for(var key2 in field[key]) {
				if(saltos.limpiar_key(key2)=="option") {
					var field3=field[key][key2];
					saltos.check_params(field3,["onclick","class","label","disabled"]);
					var option=$(`
						<option value="" class="${field3.class}"></option>
					`);
					$(option).val(field3.onclick);
					$(option).text(field3.label);
					if(field3.disabled=="true") $(option).attr("disabled","disabled");
					$("optgroup:last",td).append(option);
				}
			}
		}
	}
	// PROGRAM SELECT MENU
	$("select[ismenu=true]",td).on("change",function() {
		if(!$(this).find("option:eq("+$(this).prop("selectedIndex")+")").hasClass("ui-state-disabled")) eval($(this).val());
		if($("option:first",this).val()=="") $(this).prop("selectedIndex",0);
	});
	// PROGRAM SELECTS
	saltos.form_field_select_helper(td);
	obj.push(td);
	return obj;
};

saltos.form_field_grid=function(field) {
	saltos.check_params(field,["label",
		"class2","colspan2","rowspan2","width2",
		"class","colspan","rowspan","width","height"]);
	saltos.check_params(field,["rows"],[]);
	var obj=[];
	if(field.label!="") {
		var td=$(`
			<td class="right nowrap label ${field.class2}" colspan="${field.colspan2}" rowspan="${field.rowspan2}" style="width:${field.width2}">
				${field.label}</td>
		`);
		obj.push(td);
	}
	var td=$(`
		<td class="${field.class}" colspan="${field.colspan}" rowspan="${field.rowspan}" style="width:${field.width};height:${field.height}">
			<table cellpadding="0" cellspacing="0" border="0" width="100%"></table></td>
	`);
	$("table",td).append(saltos.form_by_row_2(field.rows,"row"));
	obj.push(td);
	return obj;
};

saltos.form_field_excel=function(field) {
	saltos.check_params(field,["label",
		"class2","colspan2","rowspan2","width2",
		"class","colspan","rowspan","width","height",
		"name","onchange",
		"rows","data","rowHeaders","colHeaders","minSpareRows","contextMenu","rowHeaderWidth","colWidths"]);
	saltos.check_params(field,["rows"],[]);
	var obj=[];
	if(field.label!="") {
		var td=$(`
			<td class="right nowrap label ${field.class2}" colspan="${field.colspan2}" rowspan="${field.rowspan2}" style="width:${field.width2}">
				${field.label}</td>
		`);
		obj.push(td);
	}
	var td=$(`
		<td class="${field.class}" colspan="${field.colspan}" rowspan="${field.rowspan}" style="width:${field.width};height:${field.height}">
			<input type="hidden" name="${field.name}" id="${field.name}" autocomplete="off"/>
			<div class="excel" style="height:100%"></div></td>
	`);
	//~ if(field.onchange!="") $("input",td).on("change",{event:field.onchange},saltos.__form_event);
	if(field.onchange!="") $("input",td).attr("onchange",field.onchange);
	setTimeout(function() {
		// FOR EXCEL
		$("div.excel",td).each(function() {
			if(field.data!="") field.data=eval(field.data);
			else field.data=JSON.parse(field.rows);
			if(field.rowHeaders!="") field.rowHeaders=eval(field.rowHeaders);
			else field.rowHeaders=true;
			if(field.colHeaders!="") field.colHeaders=eval(field.colHeaders);
			else field.colHeaders=true;
			if(field.minSpareRows!="") field.minSpareRows=eval(field.minSpareRows);
			else field.minSpareRows=0;
			if(field.contextMenu!="") field.contextMenu=eval(field.contextMenu);
			else field.contextMenu=true;
			if(field.rowHeaderWidth!="") field.rowHeaderWidth=eval(field.rowHeaderWidth);
			else field.rowHeaderWidth=undefined;
			if(field.colWidths!="") field.colWidths=eval(field.colWidths);
			else field.colWidths=undefined;
			var input=$(this).prev();
			$(this).handsontable({
				data:field.data,
				rowHeaders:field.rowHeaders,
				colHeaders:field.colHeaders,
				minSpareRows:field.minSpareRows,
				contextMenu:field.contextMenu,
				rowHeaderWidth:field.rowHeaderWidth,
				colWidths:field.colWidths,
				afterChange:function(changes,source) {
					$(input).val(btoa(utf8_encode(JSON.stringify(field.data))));
				}
			});
		});
	},100);
	obj.push(td);
	return obj;
};

// FORM FIELDS USED ONLY IN THE COPY TYPE
saltos.form_field_cache={};

saltos.form_field_copy=function(field) {
	saltos.check_params(field,["name"]);
	if(!isset(saltos.form_field_cache[field.name])) {
		return [];
	}
	saltos.check_params(field,["label",
		"class2","colspan2","rowspan2","width2",
		"name","onchange","onkey","class","focus"]);
	var obj=[];
	if(field.label!="") {
		var td=$(`
			<td class="right nowrap label ${field.class2}" colspan="${field.colspan2}" rowspan="${field.rowspan2}" style="width:${field.width2}">
				${field.label}</td>
		`);
		obj.push(td);
	}
	var field2=JSON.parse(JSON.stringify(saltos.form_field_cache[field.name]));
	if(field.label!="") field2.label="";
	field2.oldname=field2.name;
	field2.name="iscopy"+field2.name;
	if(field2.type=="select") field2.width="";
	obj=array_merge(obj,saltos.form_field(field2));
	setTimeout(function() {
		var oldfield="#"+field2.oldname;
		var newfield="#"+field2.name;
		$(newfield).addClass("nofilter");
		// CREATE THE EVENT LINKS BETWEEN THE OLD AND NEW FIELDS
		$(newfield).on("change",function(event,extra) {
			if(extra=="stop") return;
			$(oldfield).val($(this).val());
			$(oldfield).trigger("change","stop");
		});
		$(newfield).on("keydown",function(event,extra) {
			if(extra=="stop") return;
			$(oldfield).val($(this).val());
			$(oldfield).trigger("keydown","stop");
		});
		$(oldfield).on("change",function(event,extra) {
			if(extra=="stop") return;
			$(newfield).val($(this).val());
			$(newfield).trigger("change","stop");
		});
		$(oldfield).on("keydown",function(event,extra) {
			if(extra=="stop") return;
			$(newfield).val($(this).val());
			$(newfield).trigger("keydown","stop");
		});
		// PROGRAM EVENTS OF THE ORIGINAL FIELD TYPE=COPY
		if(field.onchange!="") {
			$(newfield).on("change",function(event,extra) {
				if(extra=="stop") return;
				eval(field.onchange);
			});
		}
		if(field.onkey!="") {
			$(newfield).on("keydown",function(event,extra) {
				if(extra=="stop") return;
				eval(field.onkey);
			});
		}
		if(field.class!="") {
			$(newfield).addClass(field.class);
		}
		if(field.focus=="true") {
			$(newfield).attr("focused",field.focus);
			saltos.make_focus_obj=newfield;
		}
	},100);
	return obj;
};

saltos.__form_event=function(obj) {
	if(typeof obj.data.event=="string") eval(obj.data.event);
	if(typeof obj.data.event=="function") obj.data.event();
};

/* FOR HISTORY MANAGEMENT */
saltos.history={};

saltos.history.current_hash=function() {
	var hash=window.location.hash;
	if(substr(hash,0,1)=="#") hash=substr(hash,1);
	return hash;
};

saltos.history.parse_hash=function(hash) {
	var pos=strpos(hash,"#");
	if(pos===false) pos=strpos(hash,"?");
	if(pos!==false) hash=substr(hash,pos+1);
	return hash;
};

saltos.history.push_hash=function(hash) {
	if(hash!=saltos.history.current_hash()) {
		history.pushState(null,null,".#"+hash);
	}
};

saltos.history.replace_hash=function(hash) {
	if(hash!=saltos.history.current_hash()) {
		history.replaceState(null,null,".#"+hash);
	}
};

saltos.history.init=function() {
	window.onhashchange=function() {
		saltos.opencontent(saltos.history.current_hash());
	};
};

/* FOR OLD JS AND CSS MANAGEMENT */
saltos.add_js=function(arg) {
	for(var key in arg) {
		if(saltos.limpiar_key(key)=="javascript") {
			for(var key2 in arg[key]) {
				switch(saltos.limpiar_key(key2)) {
					case "function":
						saltos.add_js_code("function "+arg[key][key2]);
						break;
					case "include":
						saltos.add_js_file(arg[key][key2]);
						break;
					case "inline":
						saltos.add_js_code(arg[key][key2]);
						break;
					case "cache":
						for(var key3 in arg[key][key2]) {
							if(saltos.limpiar_key(key3)=="include") {
								saltos.add_js_file(arg[key][key2][key3]);
							}
						}
						break;
				}
			}
		}
	}
};

saltos.add_js_code=function(arg) {
	$(".ui-layout-center").append(`<script type="text/javascript">${arg}</script>`);
};

saltos.add_js_file=function(arg) {
	$(".ui-layout-center").append(`<script type="text/javascript" src="${arg}"></script>`);
};

saltos.add_css=function(arg) {
	for(var key in arg) {
		if(saltos.limpiar_key(key)=="styles") {
			for(var key2 in arg[key]) {
				switch(saltos.limpiar_key(key2)) {
					case "include":
						saltos.add_css_file(arg[key][key2]);
						break;
					case "inline":
						saltos.add_css_code(arg[key][key2]);
						break;
					case "cache":
						for(var key3 in arg[key][key2]) {
							if(saltos.limpiar_key(key3)=="include") {
								saltos.add_css_file(arg[key][key2][key3]);
							}
						}
						break;
				}
			}
		}
	}
};

saltos.add_css_code=function(arg) {
	$(".ui-layout-center").append(`<style type="text/css">${arg}</style>`);
};

saltos.add_css_file=function(arg) {
	$(".ui-layout-center").append(`<link href="${arg}" rel="stylesheet" type="text/css"></link>`);
};

/* LOAD AND SAVE FUNCTIONS */
saltos.json_sync_request=function(url,key) {
	var temp=$.ajax({
		url:url,
		async:false,
		beforeSend:function(XMLHttpRequest) {
			saltos.make_abort_obj=XMLHttpRequest;
		},
		error:function(XMLHttpRequest,textStatus,errorThrown) {
			saltos.errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
		},
	}).responseJSON;
	if(!is_array(temp)) return {};
	if(!isset(temp[key])) return {};
	return temp[key];
};

saltos.opencontent=function(url,callback) {
	// CHECK PARAMS
	if(!isset(url)) url="";
	if(!isset(callback)) callback=function() {};
	// FIX FOR SOME URLS
	url=str_replace("+","%20",url);
	// CONTINUE
	var hash=saltos.history.parse_hash(url);
	var array=saltos.querystring2array(hash);
	if(isset(array.page) && array.page=="logout") {
		logout();
		return;
	}
	if(isset(array.page) && isset(array.action) && isset(array.id) && array.page=="login" && array.action=="form" && array.id=="0" && saltos.islogin()) {
		delete array.page;
		delete array.action;
		delete array.id;
	}
	loadingcontent(lang_loading());
	if(!isset(array.page) && !isset(array.action) && !isset(array.id)) {
		var temp=saltos.json_sync_request("index.php?action=default","default");
		if(!isset(temp.page)) {
			unloadingcontent();
			return;
		}
		array.page=temp.page;
		array.action=temp.action;
		array.id=temp.id;
		hash=saltos.array2querystring(array);
	}
	if(isset(array.page) && !isset(array.action) && !isset(array.id)) {
		var temp=saltos.json_sync_request("index.php?action=default&page="+array.page,"default");
		if(!isset(temp.action)) {
			unloadingcontent();
			return;
		}
		array.action=temp.action;
		array.id=temp.id;
		hash=saltos.array2querystring(array);
	}
	// TO FIX ERROR 414: REQUEST URI TOO LONG
	var type=(strlen(hash)>1024)?"post":"get";
	$.ajax({
		url:"index.php",
		data:hash,
		type:type,
		beforeSend:function(XMLHttpRequest) {
			saltos.addcontent(hash);
			saltos.make_abort_obj=XMLHttpRequest;
		},
		success:function(data,textStatus,XMLHttpRequest) {
			callback();
			saltos.updatecontent(data);
		},
		error:function(XMLHttpRequest,textStatus,errorThrown) {
			callback();
			saltos.errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
		}
	});
};

saltos.submitcontent=function(form,callback) {
	// CHECK PARAMS
	if(!isset(form)) form=null;
	if(!isset(callback)) callback=function() {};
	// CONTINUE
	saltos.hide_popupdialog();
	saltos.loadingcontent(lang_sending());
	$(form).ajaxSubmit({
		beforeSerialize:function(jqForm,options) {
			// TRICK FOR ADD ENCTYPE IF HAS FILES
			var numfiles=0;
			$("input[type=file]",jqForm).each(function() {
				if($(this).val()!="") numfiles++;
			});
			if(numfiles>0) $(jqForm).attr("enctype","multipart/form-data");
			// TRICK FOR FIX THE MAX_INPUT_VARS ISSUE
			var max_input_vars=ini_get_max_input_vars();
			if(max_input_vars>0) {
				var total_input_vars=$("input,select,textarea",jqForm).length;
				if(total_input_vars>max_input_vars) {
					setTimeout(function() {
						var fix_input_vars=[];
						$("input[type=hidden]",jqForm).each(function() {
							if(in_array($(this).attr("name"),["page","action","id"])) {
								// NOTHING TO DO
							} else if(total_input_vars>=max_input_vars) {
								var temp=$(this).attr("name")+"="+encodeURIComponent($(this).val());
								fix_input_vars.push(temp);
								$(this).remove();
								total_input_vars--;
							}
						});
						$("input[type=checkbox]:not(:checked):not(:visible)",jqForm).each(function() {
							if(total_input_vars>=max_input_vars) {
								$(this).remove();
								total_input_vars--;
							}
						});
						$("input[type=checkbox]:not(:visible),input[type=text]:not(:visible),select:not(:visible),textarea:not(:visible)",jqForm).each(function() {
							if(total_input_vars>=max_input_vars) {
								var temp=$(this).attr("name")+"="+encodeURIComponent($(this).val());
								fix_input_vars.push(temp);
								$(this).remove();
								total_input_vars--;
							}
						});
						$("input[type=checkbox]:not(:checked)",jqForm).each(function() {
							if(total_input_vars>=max_input_vars) {
								$(this).remove();
								total_input_vars--;
							}
						});
						$("input[type=checkbox],input[type=text],select,textarea",jqForm).each(function() {
							if(total_input_vars>=max_input_vars) {
								var temp=$(this).attr("name")+"="+encodeURIComponent($(this).val());
								fix_input_vars.push(temp);
								$(this).remove();
								total_input_vars--;
							}
						});
						fix_input_vars=btoa(utf8_encode(implode("&",fix_input_vars)));
						$(jqForm).append("<input type='hidden' name='fix_input_vars' value='"+fix_input_vars+"'/>");
						saltos.submitcontent(form,callback);
					},100);
					return false;
				}
			}
		},
		beforeSubmit:function(formData,jqForm,options) {
			var query=$.param(formData);
			saltos.addcontent(query);
			// TO FIX ERROR 414: REQUEST URI TOO LONG
			if(options.type=="get" && strlen(query)>1024) options.type="post";
		},
		beforeSend:function(XMLHttpRequest) {
			saltos.make_abort_obj=XMLHttpRequest;
		},
		success:function(data,textStatus,XMLHttpRequest) {
			callback();
			saltos.updatecontent(data);
		},
		error:function(XMLHttpRequest,textStatus,errorThrown) {
			callback();
			saltos.errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
		}
	});
};

saltos.updatecontent=function(data) {
	if(!is_array(data)) {
		var html=saltos.str2html(saltos.fix4html(data));
		if($(".phperror",html).length) {
			saltos.unloadingcontent();
			saltos.unmake_ckeditors();
			$("div[type=title]",html).remove();
			$(".ui-layout-center").html(html);
		} else if($("script",html).length!=0) {
			$(".ui-layout-center").append(html);
		} else {
			saltos.unloadingcontent();
			$(".ui-layout-center").append(html);
		}
		return;
	}
	if(isset(data.login)) {
		if(data.login) saltos.addcontent("restart");
		if(!data.login) saltos.addcontent("reload");
		return;
	}
	if(isset(data.logout)) {
		saltos.addcontent("restart");
		return;
	}
	if(isset(data.list)) {
		data.temp1=data.list;
	}
	if(isset(data.form)) {
		data.temp1=data.form;
	}
	if(!isset(data.temp1)) {
		unloadingcontent();
		return;
	}
	saltos.add_js(data.temp1);
	saltos.update_header_title(data.temp1.title);
	if(isset(data.list)) {
		saltos.form_field_cache={};
		data.temp2=saltos.make_list(data.list);
	}
	if(isset(data.form)) {
		saltos.form_field_cache={};
		data.temp2=saltos.make_form(data.form);
	}
	var tabs=saltos.make_tabs(data.temp2);
	saltos.unloadingcontent();
	saltos.unmake_ckeditors();
	saltos.hide_tooltips();
	$(window).scrollTop(0);
	$(".ui-layout-center > *").remove();
	$(".ui-layout-center").append(tabs);
	saltos.add_css(data.temp1);
	saltos.make_tables();
	saltos.make_focus();
	saltos.bold_menu();
};

saltos.errorcontent=function(code,text) {
	saltos.unloadingcontent();
	if(text=="") text=lang_unknownerror();
	alerta("Error: "+code+": "+text);
};

/* TEMPORARY VARIABLE TO STORE TEMPORARY ACTIONS */
saltos.addcontent_action="";

/* LIST OF ACTIONS BLOCKED TO USE THE HISTORY FEATURE */
saltos.addcontent_list=["login","logout","insert","update","delete"];

saltos.addcontent=function(url) {
	// DETECT SOME ACTIONS
	if(url=="cancel") {
		saltos.addcontent_action=url;
		return;
	}
	if(url=="update") {
		saltos.addcontent_action=url;
		return;
	}
	if(url=="reload") {
		$(window).trigger("hashchange");
		return;
	}
	if(url=="restart") {
		window.location.reload();
		return;
	}
	// BLOCK SOME OPERATIVE ACTIONS
	var hash=saltos.history.parse_hash(url);
	var array=saltos.querystring2array(hash);
	if(isset(array.action) && in_array(array.action,saltos.addcontent_list)) {
		return;
	}
	// IF ACTION CANCEL
	if(saltos.addcontent_action=="cancel") {
		saltos.addcontent_action="";
		return;
	}
	// IF ACTION UPDATE
	if(saltos.addcontent_action=="update") {
		saltos.history.replace_hash(hash)
		saltos.addcontent_action="";
		return;
	}
	// NORMAL CODE
	saltos.history.push_hash(hash);
};

/* FOR TOOLTIPS */
saltos.make_tooltips=function() {
	$(document).tooltip({
		items:"[title][title!=''],[title2][title2!='']",
		show:false,
		hide:false,
		tooltipClass:"ui-state-highlight",
		//~ track:true,
		open:function(event,ui) {
			ui.tooltip.css("max-width",$(window).width()/2);
			var color=get_colors("ui-state-highlight","border-bottom-color");
			ui.tooltip.css("border-color",color);
		},
		content:function() {
			// GET THE TITLE VALUE
			var title=trim($(this).attr("title"));
			if(title) {
				// CHECK IF TITLE IS THE SAME THAT THE OBJECT TEXT
				var text1=trim($(this).text());
				var text2=trim($(":not(:visible)",this).text());
				var text3=trim(str_replace(text2,"",text1));
				if(title==text3) title="";
				// FIX SOME ISSUES
				if(strpos(title,"<")!==false || strpos(title,">")!==false) {
					title=str_replace(["<",">"],["&lt;","&gt;"],title);
				}
				// MOVE DATA FROM TITLE TO TITLE2
				$(this).removeAttr("title");
				$(this).attr("title2",title);
			} else {
				title=$(this).attr("title2");
			}
			// CHECK IF OBJECT IS DISABLED
			if($(this).hasClass("ui-state-disabled")) {
				title="";
			}
			// CREATE THE TOOLTIP
			return title;
		}
	});
};

saltos.hide_tooltips=function() {
	$(".ui-tooltip").remove();
};

/* FOR ZOOM FEATURE */
saltos.zoom_index=5;
saltos.zoom_valors=[30,50,67,80,90,100,110,120,133,150,170,200,240,300];
saltos.zoom=function(arg) {
	switch(arg) {
		case "+1":
			saltos.zoom_index++;
			if(saltos.zoom_index>count(saltos.zoom_valors)-1) saltos.zoom_index=count(saltos.zoom_valors)-1;
			break;
		case "-1":
			saltos.zoom_index--;
			if(saltos.zoom_index<0) saltos.zoom_index=0;
			break;
		default:
			if(isset(saltos.zoom_valors[arg])) saltos.zoom_index=arg;
			if(array_search(arg,saltos.zoom_valors)) saltos.zoom_index=array_search(arg,saltos.zoom_valors);
			break;
	}
	$("html").css("font-size",saltos.zoom_valors[saltos.zoom_index]+"%");
};

/* FOR PUSH FEATURE */
saltos.push={};
saltos.push.executing=0;
saltos.push.timestamp=0;

saltos.push.fn=function() {
	if(!saltos.push.executing) {
		saltos.push.executing=1;
		$.ajax({
			url:"index.php",
			data:"action=push&timestamp="+saltos.push.timestamp,
			type:"get",
			success:function(response) {
				if(is_array(response) && isset(response.messages)) {
					for(var key in response.messages) {
						var message=response.messages[key];
						if(message.type=="alert") notice(lang_alert(),message.message,false,"ui-state-highlight");
						if(message.type=="error") notice(lang_error(),message.message,false,"ui-state-error");
						saltos.push.timestamp=max(saltos.push.timestamp,message.timestamp);
					}
				}
				saltos.push.executing=0;
			},
			error:function(XMLHttpRequest,textStatus,errorThrown) {
				saltos.push.executing=0;
			}
		});
	}
};

saltos.push.start=function() {
	saltos.push.timestamp=saltos.info.time;
	saltos.push.interval=setInterval(saltos.push.fn,1000);
};

saltos.push.stop=function() {
	clearInterval(saltos.push.interval);
};

/* UNIMPLEMENTED FUNCTIONS */
saltos.hash_encode=function(url) {
	console.log("call to unimplemented function hash_encode");
};

saltos.hash_decode=function(hash) {
	console.log("call to unimplemented function hash_decode");
};

saltos.current_href=function() {
	console.log("call to unimplemented function current_href");
};

saltos.history_pushState=function(url) {
	console.log("call to unimplemented function history_pushState");
};

saltos.history_replaceState=function(url) {
	console.log("call to unimplemented function history_replaceState");
};

saltos.loadcontent=function(xml) {
	console.log("call to unimplemented function loadcontent");
};

saltos.getstylesheet=function(html,cad1,cad2) {
	console.log("call to unimplemented function getstylesheet");
};

saltos.update_style=function(html,html2) {
	console.log("call to unimplemented function update_style");
};

saltos.make_menu=function(obj) {
	console.log("call to unimplemented function make_menu");
};

saltos.make_tabs2=function(obj) {
	console.log("call to unimplemented function make_tabs2");
};

saltos.make_extras=function(obj) {
	console.log("call to unimplemented function make_extras");
};

saltos.make_draganddrop=function(obj) {
	console.log("call to unimplemented function make_draganddrop");
};

saltos.make_ckeditors=function(obj) {
	console.log("call to unimplemented function make_ckeditors");
};

saltos.make_back2top=function() {
	console.log("call to unimplemented function make_back2top");
};

saltos.make_resizable=function(obj) {
	console.log("call to unimplemented function make_resizable");
};

/* FOR COMPATIBILITY */
function floatval2(obj) {
	//~ console.log("call to deprecated function floatval2");
	return saltos.floatval2(obj);
};

function intval2(obj) {
	//~ console.log("call to deprecated function intval2");
	return saltos.intval2(obj);
};

function _format_number(obj,punto) {
	//~ console.log("call to deprecated function _format_number");
	return saltos._format_number(obj,punto);
};

function check_required() {
	//~ console.log("call to deprecated function check_required");
	return saltos.check_required();
};

function intelligence_cut(txt,max) {
	//~ console.log("call to deprecated function intelligence_cut");
	return saltos.intelligence_cut(txt,max);
};

function dateval(value) {
	//~ console.log("call to deprecated function dateval");
	return saltos.dateval(value);
};

function timeval(value) {
	//~ console.log("call to deprecated function timeval");
	return saltos.timeval(value);
};

function __days_of_a_month(year,month) {
	//~ console.log("call to deprecated function __days_of_a_month");
	return saltos.__days_of_a_month(year,month);
};

function check_datetime(orig,comp,dest) {
	//~ console.log("call to deprecated function check_datetime");
	return saltos.check_datetime(orig,comp,dest);
};

function check_date(orig,comp,dest) {
	//~ console.log("call to deprecated function check_date");
	return saltos.check_date(orig,comp,dest);
};

function check_time(orig,comp,dest) {
	//~ console.log("call to deprecated function check_time");
	return saltos.check_time(orig,comp,dest);
};

function get_keycode(event) {
	//~ console.log("call to deprecated function get_keycode");
	return saltos.get_keycode(event);
};

function is_enterkey(event) {
	//~ console.log("call to deprecated function is_enterkey");
	return saltos.is_enterkey(event);
};

function is_escapekey(event) {
	//~ console.log("call to deprecated function is_escapekey");
	return saltos.is_escapekey(event);
};

function is_disabled(obj) {
	//~ console.log("call to deprecated function is_disabled");
	return saltos.is_disabled(obj);
};

function addlog(msg) {
	//~ console.log("call to deprecated function addlog");
	return saltos.addlog(msg);
};

function security_iframe(obj) {
	//~ console.log("call to deprecated function security_iframe");
	return saltos.security_iframe(obj);
};

function make_dialog() {
	//~ console.log("call to deprecated function make_dialog");
	return saltos.make_dialog();
};

function dialog(title,message,buttons) {
	//~ console.log("call to deprecated function dialog");
	return saltos.dialog(title,message,buttons);
};

function make_notice() {
	//~ console.log("call to deprecated function make_notice");
	return saltos.make_notice();
};

function hide_popupnotice() {
	//~ console.log("call to deprecated function hide_popupnotice");
	return saltos.hide_popupnotice();
};

function notice(title,message,arg1,arg2,arg3) {
	//~ console.log("call to deprecated function notice");
	return saltos.notice(title,message,arg1,arg2,arg3);
};

function __sync_cookies_helper() {
	//~ console.log("call to deprecated function __sync_cookies_helper");
	return saltos.cookies.__sync_helper();
};

function sync_cookies(cmd) {
	//~ console.log("call to deprecated function sync_cookies");
	return saltos.cookies.sync(cmd);
};

function getCookie(name) {
	//~ console.log("call to deprecated function getCookie");
	return saltos.cookies.getCookie(name);
};

function getIntCookie(name) {
	//~ console.log("call to deprecated function getIntCookie");
	return saltos.cookies.getIntCookie(name);
};

function getBoolCookie(name) {
	//~ console.log("call to deprecated function getBoolCookie");
	return saltos.cookies.getBoolCookie(name);
};

function setCookie(name,value) {
	//~ console.log("call to deprecated function setCookie");
	return saltos.cookies.setCookie(name,value);
};

function setIntCookie(name,value) {
	//~ console.log("call to deprecated function setIntCookie");
	return saltos.cookies.setIntCookie(name,value);
};

function setBoolCookie(name,value) {
	//~ console.log("call to deprecated function setBoolCookie");
	return saltos.cookies.setBoolCookie(name,value);
};

function loadingcontent(message) {
	//~ console.log("call to deprecated function loadingcontent");
	return saltos.loadingcontent(message);
};

function unloadingcontent() {
	//~ console.log("call to deprecated function unloadingcontent");
	return saltos.unloadingcontent();
};

function isloadingcontent() {
	//~ console.log("call to deprecated function isloadingcontent");
	return saltos.isloadingcontent();
};

function hash_encode(url) {
	//~ console.log("call to deprecated function hash_encode");
	return saltos.hash_encode(url);
};

function hash_decode(hash) {
	//~ console.log("call to deprecated function hash_decode");
	return saltos.hash_decode(hash);
};

function current_href() {
	//~ console.log("call to deprecated function current_href");
	return saltos.current_href();
};

function current_hash() {
	//~ console.log("call to deprecated function current_hash");
	return saltos.history.current_hash();
};

function history_pushState(url) {
	//~ console.log("call to deprecated function history_pushState");
	return saltos.history_pushState(url);
};

function history_replaceState(url) {
	//~ console.log("call to deprecated function history_replaceState");
	return saltos.history_replaceState(url);
};

function init_history() {
	//~ console.log("call to deprecated function init_history");
	return saltos.history.init();
};

function addcontent(url) {
	//~ console.log("call to deprecated function addcontent");
	return saltos.addcontent(url);
};

function submitcontent(form,callback) {
	//~ console.log("call to deprecated function submitcontent");
	return saltos.submitcontent(form,callback);
};

function opencontent(url,callback) {
	//~ console.log("call to deprecated function opencontent");
	return saltos.opencontent(url,callback);
};

function errorcontent(code,text) {
	//~ console.log("call to deprecated function errorcontent");
	return saltos.errorcontent(code,text);
};

function loadcontent(xml) {
	console.log("call to deprecated function loadcontent");
	//~ return saltos.loadcontent(xml);
};

function html2str(html) {
	//~ console.log("call to deprecated function html2str");
	return saltos.html2str(html);
};

function str2html(str) {
	//~ console.log("call to deprecated function str2html");
	return saltos.str2html(str);
};

function fix4html(str) {
	//~ console.log("call to deprecated function fix4html");
	return saltos.fix4html(str);
};

function getstylesheet(html,cad1,cad2) {
	//~ console.log("call to deprecated function getstylesheet");
	return saltos.getstylesheet(html,cad1,cad2);
};

function update_style(html,html2) {
	//~ console.log("call to deprecated function update_style");
	return saltos.update_style(html,html2);
};

function updatecontent(html) {
	console.log("call to deprecated function updatecontent");
	//~ return saltos.updatecontent(html);
};

function make_menu(obj) {
	//~ console.log("call to deprecated function make_menu");
	return saltos.make_menu(obj);
};

function toggle_menu() {
	//~ console.log("call to deprecated function toggle_menu");
	return saltos.toggle_menu();
};

function bold_menu() {
	//~ console.log("call to deprecated function bold_menu");
	return saltos.bold_menu();
};

function make_tabs(obj) {
	//~ console.log("call to deprecated function make_tabs");
	return saltos.make_tabs(obj);
};

function hide_popupdialog() {
	//~ console.log("call to deprecated function hide_popupdialog");
	return saltos.hide_popupdialog();
};

function make_tabs2(obj) {
	//~ console.log("call to deprecated function make_tabs2");
	return saltos.make_tabs2(obj);
};

function make_extras(obj) {
	//~ console.log("call to deprecated function make_extras");
	return saltos.make_extras(obj);
};

function make_draganddrop(obj) {
	//~ console.log("call to deprecated function make_draganddrop");
	return saltos.make_draganddrop(obj);
};

function make_hovers() {
	//~ console.log("call to deprecated function make_hovers");
	return saltos.make_hovers();
};

function make_ckeditors(obj) {
	//~ console.log("call to deprecated function make_ckeditors");
	return saltos.make_ckeditors(obj);
};

function unmake_ckeditors(obj) {
	//~ console.log("call to deprecated function unmake_ckeditors");
	return saltos.unmake_ckeditors(obj);
};

function make_tooltips() {
	//~ console.log("call to deprecated function make_tooltips");
	return saltos.make_tooltips();
};

function hide_tooltips() {
	//~ console.log("call to deprecated function hide_tooltips");
	return saltos.hide_tooltips();
};

function make_focus() {
	//~ console.log("call to deprecated function make_focus");
	return saltos.make_focus();
};

function unmake_focus() {
	//~ console.log("call to deprecated function unmake_focus");
	return saltos.unmake_focus();
};

function make_tables(obj) {
	//~ console.log("call to deprecated function make_tables");
	return saltos.make_tables(obj);
};

function make_contextmenu() {
	//~ console.log("call to deprecated function make_contextmenu");
	return saltos.make_contextmenu();
};

function hide_contextmenu() {
	//~ console.log("call to deprecated function hide_contextmenu");
	return saltos.hide_contextmenu();
};

function get_colors(clase,param) {
	//~ console.log("call to deprecated function get_colors");
	return saltos.get_colors(clase,param);
};

function rgb2hex(color) {
	//~ console.log("call to deprecated function rgb2hex");
	return saltos.rgb2hex(color);
};

function make_shortcuts() {
	//~ console.log("call to deprecated function make_shortcuts");
	return saltos.make_shortcuts();
};

function make_abort() {
	//~ console.log("call to deprecated function make_abort");
	return saltos.make_abort();
};

function make_back2top() {
	//~ console.log("call to deprecated function make_back2top");
	return saltos.make_back2top();
};

function make_resizable() {
	//~ console.log("call to deprecated function make_resizable");
	return saltos.make_resizable();
};

function get_class_key_val(obj,param) {
	//~ console.log("call to deprecated function get_class_key_val");
	return saltos.get_class_key_val($(obj).attr("class"),param);
};

function get_class_id(obj) {
	//~ console.log("call to deprecated function get_class_id");
	return saltos.get_class_id($(obj).attr("class"));
};

function get_class_fn(obj) {
	//~ console.log("call to deprecated function get_class_fn");
	return saltos.get_class_fn($(obj).attr("class"));
};

function get_class_hash(obj) {
	//~ console.log("call to deprecated function get_class_hash");
	return saltos.get_class_hash($(obj).attr("class"));
};

function saltos_islogin(obj) {
	//~ console.log("call to deprecated function saltos_islogin");
	return saltos.islogin(obj);
};

function copy_value(dest,src) {
	//~ console.log("call to deprecated function copy_value");
	return saltos.copy_value(src,dest);
};

function lang_loading() {
	// TODO FIXED IN A FUTURE
	return "Loading contents...";
};

// TO PREVENT JQUERY THE ADD _=[TIMESTAMP] FEATURE
$.ajaxSetup({ cache:true });

/* MAIN CODE */
(function($) {
	saltos.init_error();

	// CARGAR DATOS
	saltos.cookies.sync();
	saltos.info=saltos.json_sync_request("index.php?action=info","info");
	saltos.menu=saltos.json_sync_request("index.php?action=menu","menu");

	// MONTAR PANTALLA
	saltos.add_layout();
	saltos.add_header(saltos.menu);
	saltos.add_menu(saltos.menu);

	// MULTIPLES INITS
	saltos.history.init();
	saltos.make_notice();
	saltos.make_dialog();
	saltos.make_contextmenu();
	saltos.make_shortcuts();
	saltos.make_abort();
	saltos.make_tooltips();
	saltos.make_hovers();
	saltos.make_enters();
	saltos.push.start();

	// CARGAR PRIMER CONTENIDO
	saltos.opencontent(saltos.history.current_hash());

}(jQuery));

