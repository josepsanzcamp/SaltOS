/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2020 by Josep Sanz Campderrós
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
		// CHECK FOR VISIBILITY
		if(substr(this.type,0,6)=="select") {
			if(!$(this).next().is(":visible")) return;
		} else {
			if(!$(this).is(":visible")) return;
		}
		// CONTINUE
		var valor=$(this).val();
		var campo=this;
		if(substr(this.type,0,6)=="select") {
			if(valor=="0") valor="";
			campo=$(this).next().get(0);
		}
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
		var requiredfield=lang_requiredfield();
		alerta(requiredfield+": "+label,function() { $(field).trigger("focus"); });
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
	value=str_replace("-"," ",value);
	value=str_replace(":"," ",value);
	value=str_replace(","," ",value);
	value=str_replace("."," ",value);
	value=str_replace("/"," ",value);
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
	value=str_replace("-"," ",value);
	value=str_replace(":"," ",value);
	value=str_replace(","," ",value);
	value=str_replace("."," ",value);
	value=str_replace("/"," ",value);
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

saltos.__sync_cookies_helper=function() {
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

saltos.sync_cookies=function(cmd) {
	if(typeof cmd=="undefined") var cmd="";
	if(cmd=="stop") {
		if(saltos.cookies.interval!=null) {
			clearInterval(saltos.cookies.interval);
			saltos.cookies.interval=null;
		}
		saltos.__sync_cookies_helper();
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
				$(response["rows"]).each(function() {
					var hash=md5(this["clave"]);
					saltos.cookies.data[hash]={
						"key":this["clave"],
						"val":this["valor"],
						"orig":this["valor"],
						"sync":0
					};
				});
			},
			error:function(XMLHttpRequest,textStatus,errorThrown) {
				errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
			}
		});
		saltos.cookies.counter=0;
		saltos.cookies.interval=setInterval(function() {
			saltos.cookies.counter=saltos.cookies.counter+100;
			if(saltos.cookies.counter>=1000) {
				saltos.__sync_cookies_helper();
				saltos.cookies.counter=0;
			}
		},100);
	}
};

saltos.getCookie=function(name) {
	var hash=md5(name);
	if(typeof saltos.cookies.data[hash]=="undefined") {
		var value=$.cookie(name);
	} else {
		var value=saltos.cookies.data[hash].val;
	}
	return value;
};

saltos.getIntCookie=function(name) {
	return intval(saltos.getCookie(name));
};

saltos.getBoolCookie=function(name) {
	return saltos.getIntCookie(name)?true:false;
};

saltos.setCookie=function(name,value) {
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

saltos.setIntCookie=function(name,value) {
	saltos.setCookie(name,intval(value));
};

saltos.setBoolCookie=function(name,value) {
	saltos.setIntCookie(name,value?1:0);
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

// PHP FUNCTIONS
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

// CLASS FUNCTIONS
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

// TODO FUNCTIONS
saltos.security_iframe=function(obj) {
	console.log("call to unimplemented function security_iframe");
}

saltos.make_dialog=function() {
	console.log("call to unimplemented function make_dialog");
}

saltos.dialog=function(title,message,buttons) {
	console.log("call to unimplemented function dialog");
}

saltos.make_notice=function() {
	console.log("call to unimplemented function make_notice");
}

saltos.hide_popupnotice=function() {
	console.log("call to unimplemented function hide_popupnotice");
}

saltos.notice=function(title,message,arg1,arg2,arg3) {
	console.log("call to unimplemented function notice");
}

saltos.loadingcontent=function(message) {
	console.log("call to unimplemented function loadingcontent");
}

saltos.unloadingcontent=function() {
	console.log("call to unimplemented function unloadingcontent");
}

saltos.isloadingcontent=function() {
	console.log("call to unimplemented function isloadingcontent");
}

saltos.hash_encode=function(url) {
	console.log("call to unimplemented function hash_encode");
}

saltos.hash_decode=function(hash) {
	console.log("call to unimplemented function hash_decode");
}

saltos.current_href=function() {
	console.log("call to unimplemented function current_href");
}

saltos.history_pushState=function(url) {
	console.log("call to unimplemented function history_pushState");
}

saltos.history_replaceState=function(url) {
	console.log("call to unimplemented function history_replaceState");
}

saltos.addcontent=function(url) {
	console.log("call to unimplemented function addcontent");
}

saltos.submitcontent=function(form,callback) {
	console.log("call to unimplemented function submitcontent");
}

saltos.errorcontent=function(code,text) {
	console.log("call to unimplemented function errorcontent");
}

saltos.loadcontent=function(xml) {
	console.log("call to unimplemented function loadcontent");
}

saltos.html2str=function(html) {
	console.log("call to unimplemented function html2str");
}

saltos.str2html=function(str) {
	console.log("call to unimplemented function str2html");
}

saltos.fix4html=function(str) {
	console.log("call to unimplemented function fix4html");
}

saltos.getstylesheet=function(html,cad1,cad2) {
	console.log("call to unimplemented function getstylesheet");
}

saltos.update_style=function(html,html2) {
	console.log("call to unimplemented function update_style");
}

saltos.updatecontent=function(html) {
	console.log("call to unimplemented function updatecontent");
}

saltos.make_menu=function(obj) {
	console.log("call to unimplemented function make_menu");
}

saltos.toggle_menu=function() {
	console.log("call to unimplemented function toggle_menu");
}

saltos.hide_popupdialog=function() {
	console.log("call to unimplemented function hide_popupdialog");
}

saltos.make_tabs2=function(obj) {
	console.log("call to unimplemented function make_tabs2");
}

saltos.make_extras=function(obj) {
	console.log("call to unimplemented function make_extras");
}

saltos.make_draganddrop=function(obj) {
	console.log("call to unimplemented function make_draganddrop");
}

saltos.make_hovers=function() {
	console.log("call to unimplemented function make_hovers");
}

saltos.make_ckeditors=function(obj) {
	console.log("call to unimplemented function make_ckeditors");
}

saltos.unmake_ckeditors=function(obj) {
	console.log("call to unimplemented function unmake_ckeditors");
}

saltos.make_focus=function() {
	console.log("call to unimplemented function make_focus");
}

saltos.unmake_focus=function() {
	console.log("call to unimplemented function unmake_focus");
}

saltos.make_tables=function(obj) {
	console.log("call to unimplemented function make_tables");
}

saltos.make_contextmenu=function() {
	console.log("call to unimplemented function make_contextmenu");
}

saltos.hide_contextmenu=function() {
	console.log("call to unimplemented function hide_contextmenu");
}

saltos.make_shortcuts=function() {
	console.log("call to unimplemented function make_shortcuts");
}

saltos.make_abort=function() {
	console.log("call to unimplemented function make_abort");
}

saltos.make_back2top=function() {
	console.log("call to unimplemented function make_back2top");
}

saltos.make_resizable=function(obj) {
	console.log("call to unimplemented function make_resizable");
}

// JQUERYUI WIDGETS
saltos.add_layout=function(info) {
	var layout=$(`
		<table class="width100 none" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td valign="top" colspan="2">
					<div class="ui-layout-north">
						<div class="tabs2">
							<ul class="headertabs"></ul>
						</div>
					</div>
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
	$("html").attr("lang",info.lang);
	$("html").attr("dir",info.dir);
	// RESIZABLE CODE
	setTimeout(function() {
		var width=parseInt(saltos.getIntCookie("saltos_ui_menu_width")/10)*10;
		if(!width) width=200;
		$(".ui-layout-west").width(width).resizable({
			minWidth:100,
			maxWidth:400,
			grid:10,
			handles:"e",
			resize:function(event,ui) {
				setIntCookie("saltos_ui_menu_width",ui.size.width);
				$(".back2top").css("left",(ui.size.width-54)+"px");
			},
		});
		$(".back2top").css("left",(width-54)+"px");
		// REMOVE NONE CLASS
		$(layout).removeClass("none");
	},100);
};

saltos.tabs2_padding="";
saltos.tabs2_margin="";
saltos.tabs2_border="";

saltos.add_button_in_navbar=function(option) {
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

saltos.add_group_in_menu=function(option) {
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
				setIntCookie("saltos_ui_menu_"+name,active);
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
			var active=saltos.getIntCookie("saltos_ui_menu_"+name+"_"+name2);
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
			setIntCookie("saltos_ui_menu_"+name+"_"+name2,1);
		});
		$(".accordion-link",group).on("close_node.jstree",function(e,_data) {
			var name2=_data.node.a_attr.id;
			setIntCookie("saltos_ui_menu_"+name+"_"+name2,0);
		});
		// REMOVE NONE CLASS
		$(group).removeClass("none");
	},100);
};

saltos.add_link_in_group=function(option) {
	saltos.check_params(option,["class","tip","icon","label","onclick","name"]);
	var link=$(`
		<li>
			<a href="javascript:void(0)" class="${option.class}" icon="${option.icon}" title="${option.tip}" id="${option.name}">${option.label}</a>
		</li>
	`);
	// CONTINUE
	$("a",link).on("click",function() {
		if(typeof option.onclick=="string") eval(option.onclick);
		if(typeof option.onclick=="function") option.onclick();
	});
	$(".ui-layout-west ul:last").append(link);
};

saltos.make_focus_obj=null;

saltos.make_tabs=function(array) {
	var card=$(`
		<div class="tabs none">
			<ul class="centertabs"></ul>
			<div class="centertabs2"></div>
		</div>
	`);
	for(var key in array) {
		$(".centertabs",card).append(`
			<li>
				<a href="#tab-${key}"><span class="${array[key].icon}"></span> ${array[key].title}</a>
			</li>
		`);
		$(".centertabs2",card).append(`<div id="tab-${key}"></div>`);
		$(`#tab-${key}`,card).append(array[key].obj);
	}
	setTimeout(function() {
		// THIS CODE ADD THE ACCESSKEY FEATURE FOR EACH TAB
		var accesskeys="1234567890";
		var accesskey=0;
		var tabs=$("ul > li",card);
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
				if(substr($(thetab).attr("id"),0,4)=="tab-") {
					var index=0;
					$("[id^=tab-]",card).each(function() {
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
			active:0,
			beforeActivate:function(event,ui) {
				if($(ui.newTab).hasClass("help")) {
					viewpdf("page="+getParam("page"));
					return false;
				}
				if($(ui.newTab).hasClass("popup")) {
					var title=$("a",ui.newTab).text();
					var tabid=$("a",ui.newTab).attr("href").substr(1);
					if(getParam("action")=="list") var form=$("#"+tabid).parent();
					if(getParam("action")=="form") var form=$("#"+tabid);
					dialog(title);
					var dialog2=$("#dialog");
					$(dialog2).html("");
					$(form).after("<div id='popup"+tabid+"'></div>");
					$(dialog2).append("<br/>");
					$(dialog2).append(form);
					$(dialog2).append("<br/><br/>");
					$("div",dialog2).removeAttr("class").removeAttr("style");
					$(dialog2).dialog("option","resizeStop",function(event,ui) {
						setIntCookie("saltos_popup_width",$(dialog2).dialog("option","width"));
						setIntCookie("saltos_popup_height",$(dialog2).dialog("option","height"));
					});
					$(dialog2).dialog("option","close",function(event,ui) {
						$(dialog2).dialog("option","resizeStop",function() {});
						$(dialog2).dialog("option","close",function() {});
						if(getParam("action")=="list") $("div",form).hide();
						if(getParam("action")=="form") $(form).hide();
						$("#popup"+tabid).replaceWith(form);
						unmake_focus();
						hide_tooltips();
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
		var help=$("ul li.help",card);
		$("span",help).removeClass("ui-icon ui-icon-none").addClass(icon_help());
		$("a",help).append("&nbsp;").append(lang_help());
		// REMOVE NONE CLASS
		$(card).removeClass("none");
	},100);
	return card;
};

/* FUNCIONES PARA EL PROCESADO DE PARAMETROS */
saltos.check_params=function(obj,params) {
	for(var key in params) if(!isset(obj[params[key]])) obj[params[key]]="";
};

/* FUNCIONES PARA EL PROCESADO DE LISTADOS */
saltos.make_table=function(option) {
	saltos.check_params(option,["width"]);
	var table=$(`
		<table class="tabla helperlists" cellpadding="0" cellspacing="0" border="0">
			<thead></thead>
			<tbody></tbody>
		</table>
	`);
	if(option.width!="") $(table).attr("style",`width:${option.width}`);
	$("thead",table).append("<tr></tr>");
	$("thead tr",table).append(`<td class="width1 thead ui-widget-header shortcut_ctrl_a"><input type="checkbox" class="master" name="master" id="master" value="1" autocomplete="off"/></td>`);
	for(var key in option.fields) {
		var field=option.fields[key];
		saltos.check_params(field,["width","tip","label","sort"]);
		var td=$(`<td class="thead ui-widget-header"></td>`);
		$("thead tr:last",table).append(td);
		if(field.width!="") $(td).attr("style",`width:${field.width}`);
		if(field.tip!="") $(td).append(`<span title="${field.tip}">${field.label}</span>`);
		if(field.tip=="") $(td).append(field.label);
	}
	$("thead tr",table).append(`<td class=" width1 thead ui-widget-header" colspan="100"><span class="ui-icon ui-icon-none"></span></td>`);
	var count=0;
	for(var key in option.rows) {
		var row=option.rows[key];
		$("tbody",table).append("<tr></tr>");
		$("tbody tr:last",table).append(`<td class="width1 tbody"><input type="checkbox" class="slave id_${row.id}" name="slave_${row.id}" id="slave_${row.id}" value="1" autocomplete="off"/></td>`);
		for(var key2 in option.fields) {
			var field=option.fields[key2];
			var td=$(`<td class="tbody"></td>`);
			$("tbody tr:last",table).append(td);
			saltos.check_params(field,["name","size"]);
			field.value=saltos.get_filtered_field(row[field.name],field.size);
			$(td).append(field.value);
		}
		$("tbody tr:last",table).append(`<td class="tbody"></td>`);
		if(count%2==0) $("tbody tr:last td",table).addClass("ui-widget-content");
		if(count%2==1) $("tbody tr:last td",table).addClass("ui-state-default");
		if(count>0) $("tbody tr:last td",table).addClass("notop");
		count++;
	}
	if(!count) {
		$("tbody",table).append("<tr></tr>");
		$("tbody tr:last",table).append(`<td colspan="100" class="tbody ui-widget-content notop nodata italic">${option.nodata.label}</td>`);
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
	return table;
};

saltos.__get_filtered_field_helper=function(field,size) {
	if(size!="") {
		var len=strlen(field);
		size=intval(size);
		if(len>size) {
			var field2=str_replace('"',"'",field);
			var field3=htmlentities(substr(field,0,size))+"...";
			field=`<span title="${field2}">${field3}</span>`;
		} else {
			field=htmlentities(field);
		}
	} else {
		field=htmlentities(field);
	}
	return field;
};

saltos.get_filtered_field=function(field,size) {
	if(substr(field,0,4)=="tel:") {
		var temp=explode(":",field,2);
		temp[2]=saltos.__get_filtered_field_helper(temp[1],size);
		field=$(`<a href="javascript:void(0)">${temp[2]}</a>`)
		$(field).on("click",function() { qrcode(temp[1]); });
	} else if(substr(field,0,7)=="mailto:") {
		var temp=explode(":",field,2);
		temp[2]=saltos.__get_filtered_field_helper(temp[1],size);
		field=$(`<a href="javascript:void(0)">${temp[2]}</a>`)
		$(field).on("click",function() { mailto(temp[1]); });
	} else if(substr(field,0,5)=="href:") {
		var temp=explode(":",field,2);
		temp[2]=saltos.__get_filtered_field_helper(temp[1],size);
		field=$(`<a href="javascript:void(0)">${temp[2]}</a>`)
		$(field).on("click",function() { openwin(temp[1]); });
	} else if(substr(field,0,5)=="link:") {
		var temp=explode(":",field,3);
		temp[2]=saltos.__get_filtered_field_helper(temp[2],size);
		field=$(`<a href="javascript:void(0)">${temp[2]}</a>`)
		$(field).on("click",function() { eval(temp[1]); });
	} else {
		field=saltos.__get_filtered_field_helper(field,size)
	}
	return field;
};

saltos.make_list=function(option) {
	var obj=$("<div></div>");
	if(isset(option.quick)) {
		var table=saltos.form_table(option.width);
		$(obj).append(table);
		$(table).append(saltos.form_by_row_3(option,"quick","row"));
		$(table).append(saltos.form_brtag_2());
	}
	$(obj).append(saltos.make_table(option));
	if(isset(option.pager)) {
		var table=saltos.form_table(option.width);
		$(obj).append(table);
		$(table).append(saltos.form_brtag_2());
		$(table).append(saltos.form_by_row_3(option,"pager","row"));
	}
	var array=[{
		title:option.title,
		icon:option.icon,
		obj:obj,
	}];
	for(var key in option) {
		if(saltos.limpiar_key(key)=="form") {
			array=array_merge(array,saltos.make_form(option[key]));
		}
	}
	return array;
}

/* FUNCIONES PARA EL PROCESADO DE FORMULARIOS */
saltos.make_form=function(option) {
	var array=[];
	var title="";
	var icon="";
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
				if(isset(option[key].title) && option[key].title!="") {
					if(title!="") {
						array.push({ "title":title, "icon":icon, "obj":obj });
						obj=$("<div></div>");
					}
					title=option[key].title;
					icon=option[key].icon;
				} else {
					$(obj).append(saltos.form_brtag_1());
				}
				var table=saltos.form_table();
				$(obj).append(table);
				if(isset(option[key].quick) && option[key].quick=="true") {
					$(table).append(saltos.form_by_row_3(option,"quick","row"));
					$(table).append(saltos.form_brtag_2());
				}
				$(table).append(saltos.form_by_row_2(option[key],"row"));
				if(isset(option[key].buttons) && option[key].buttons=="true") {
					$(table).append(saltos.form_brtag_2());
					$(table).append(saltos.form_by_row_3(option,"buttons","row"));
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
																array.push({ "title":title, "icon":icon, "obj":obj });
																obj=$("<div></div>");
															}
															title=node1[key6].title;
															icon=node1[key6].icon;
														} else {
															$(obj).append(saltos.form_brtag_1());
														}
														var table=saltos.form_table(node1[key6].width);
														$(obj).append(table);
														if(isset(node1[key6].quick) && node1[key6].quick=="true") {
															var temp=saltos.form_prepare_fields_3(option,"quick","row",prefix);
															$(table).append(saltos.form_by_row_3(temp,"quick","row"));
															$(table).append(saltos.form_brtag_2());
														}
														var temp=saltos.form_prepare_fields_2(node1[key6],"row",prefix,node3);
														$(table).append(saltos.form_by_row_2(temp,"row"));
														if(isset(node1[key6].buttons) && node1[key6].buttons=="true") {
															$(table).append(saltos.form_brtag_2());
															var temp=saltos.form_prepare_fields_3(option,"buttons","row",prefix);
															$(table).append(saltos.form_by_row_3(temp,"buttons","row"));
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
														array.push({ "title":title, "icon":icon, "obj":obj });
														obj=$("<div></div>");
													}
													title=node1[key6].title;
													icon=node1[key6].icon;
												} else {
													$(obj).append(saltos.form_brtag_1());
												}
												var table=saltos.form_table(node1[key6].width);
												$(obj).append(table);
												if(isset(node1[key6].quick) && node1[key6].quick=="true") {
													$(table).append(saltos.form_by_row_3(option,"quick","row"));
													$(table).append(saltos.form_brtag_2());
												}
												$(table).append(saltos.form_by_row_2(node1[key6],"head"));
												var temp=saltos.form_prepare_fields_4(node1,key6,"row",node2,name2,"row");
												$(table).append(saltos.form_by_row_2(temp,"row"));
												$(table).append(saltos.form_by_row_4(node1[key6],"tail"));
												if(isset(node1[key6].buttons) && node1[key6].buttons=="true") {
													$(table).append(saltos.form_brtag_2());
													$(table).append(saltos.form_by_row_3(option,"buttons","row"));
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
	if(title!="") {
		array.push({ "title":title, "icon":icon, "obj":obj });
	}
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
						fields2[key].value=values[key2];
					}
				}
			}
			if(isset(field.name) && isset(prefix)) {
				fields2[key].name=prefix+field.name;
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
		}
	}
	return obj;
}

saltos.form_prepare_fields_3=function(fields,filter,filter2,prefix,values) {
	if(!count(fields)) return null;
	var obj={};
	for(var key in fields) {
		if(saltos.limpiar_key(key)==filter) {
			obj[key]=saltos.form_prepare_fields_2(fields[key],filter2,prefix,values);
		}
	}
	return obj;
}

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
				}
			}
		}
	}
	return obj;
}

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
				for(var key2 in temp) {
					$(tr).append(temp[key2]);
				}
				obj.push(tr);
			}
		}
	}
	return obj;
}

saltos.form_by_row_3=function(fields,filter,filter2) {
	if(!count(fields)) return null;
	var obj=[];
	for(var key in fields) {
		if(saltos.limpiar_key(key)==filter) {
			var temp=saltos.form_by_row_2(fields[key],filter2);
			if(count(temp)) {
				var table=$(`<table class="w-100" cellpadding="0" cellspacing="0" border="0"></table>`);
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
}

saltos.form_by_row_4=function(fields,filter) {
	if(!count(fields)) return null;
	var temp=saltos.form_by_row_2(fields,filter);
	var table=$(`<table class="w-100" cellpadding="0" cellspacing="0" border="0"></table>`);
	for(var key in temp) {
		$(table).append(temp[key]);
	}
	var td=$(`<td colspan="100"></td>`);
	$(td).append(table);
	var tr=$("<tr></tr>");
	$(tr).append(td);
	return tr;
}

saltos.form_brtag_1=function() {
	return $("<br/>");
}

saltos.form_brtag_2=function() {
	return $("<tr><td><br/></td></tr>");
}

saltos.form_table=function(arg) {
	if(arg!="") arg=`width:${arg}`;
	return $(`<table class="mx-auto" style="${arg}" cellpadding="0" cellspacing="0" border="0"></table>`);
}

saltos.form_field=function(field) {
	saltos.check_params(field,[
		"name","value",
		"onchange","onkey","onclick",
		"class","class2","class3",
		"colspan","colspan2",
		"rowspan","rowspan2",
		"width","width2","height",
		"required","focus","disabled",
		"label","label2","tip","icon",
		"link","tip2",
		"autocomplete","querycomplete","filtercomplete","oncomplete",
	]);
	// CONTINUE
	var obj=[];
	switch(field.type) {
		case "hidden":
			var input=$(`<input type="hidden" autocomplete="off"/>`);
			if(field.name!="") $(input).attr("name",field.name).attr("id",field.name);
			if(field.value!="") $(input).attr("value",field.value);
			if(field.onchange!="") $(input).on("change",field.onchange);
			if(field.class!="") $(input).addClass(field.class);
			obj.push(input);
			break;
		case "text":
			if(field.label!="") {
				var td=$(`<td class="right nowrap label text-right text-nowrap"></td>`);
				if(field.class2!="") $(td).addClass(field.class2);
				if(field.colspan2!="") $(td).attr("colspan",field.colspan2);
				if(field.rowspan2!="") $(td).attr("rowspan",field.rowspan2);
				if(field.width2!="") $(td).attr("style","width:"+field.width2);
				if(field.required=="true") $(td).append("(*) ");
				if(field.label!="") $(td).append(field.label);
				obj.push(td);
			}
			var td=$(`<td class="left nowrap text-left text-nowrap"></td>`);
			if(field.class!="") $(td).addClass(field.class);
			if(field.colspan!="") $(td).attr("colspan",field.colspan);
			if(field.rowspan!="") $(td).attr("rowspan",field.rowspan);
			if(field.width!="") $(td).attr("style","width:"+field.width);
			var input=$(`<input type="text" autocomplete="off" class="form-control"/>`);
			if(field.name!="") $(input).attr("name",field.name).attr("id",field.name);
			if(field.value!="") $(input).attr("value",field.value);
			if(field.width!="") $(input).attr("style","width:"+field.width);
			if(field.onchange!="") $(input).on("change",function() { field.onchange(); });
			if(field.onkey!="") $(input).on("keydown",function() { field.onkey(); });
			if(field.focus!="") $(input).attr("focused",field.focus);
			if(field.required!="") $(input).attr("isrequired",field.required);
			if(field.label!="" || field.label2!="") $(input).attr("labeled",field.label+field.label2);
			if(field.tip!="") $(input).attr("title",field.tip);
			if(field.class3!="") $(input).addClass(field.class3);
			if(field.autocomplete!="") $(input).attr("isautocomplete",field.autocomplete);
			if(field.querycomplete!="") $(input).attr("querycomplete",field.querycomplete);
			if(field.filtercomplete!="") $(input).attr("filtercomplete",field.filtercomplete);
			if(field.oncomplete!="") $(input).attr("oncomplete",field.oncomplete);
			$(td).append(input);
			obj.push(td);
			// TODO: FALTA AÑADIR EL BOTON DE LINK
			break;
		case "integer":
			if(field.label!="") {
				var td=$(`<td class="right nowrap label text-right text-nowrap"></td>`);
				if(field.class2!="") $(td).addClass(field.class2);
				if(field.colspan2!="") $(td).attr("colspan",field.colspan2);
				if(field.rowspan2!="") $(td).attr("rowspan",field.rowspan2);
				if(field.width2!="") $(td).attr("style","width:"+field.width2);
				if(field.required=="true") $(td).append("(*) ");
				if(field.label!="") $(td).append(field.label);
				obj.push(td);
			}
			var td=$(`<td class="left nowrap text-left text-nowrap"></td>`);
			if(field.class!="") $(td).addClass(field.class);
			if(field.colspan!="") $(td).attr("colspan",field.colspan);
			if(field.rowspan!="") $(td).attr("rowspan",field.rowspan);
			if(field.width!="") $(td).attr("style","width:"+field.width);
			var input=$(`<input type="text" autocomplete="off" class="form-control"/>`);
			if(field.name!="") $(input).attr("name",field.name).attr("id",field.name);
			if(field.value!="") $(input).attr("value",field.value);
			if(field.width!="") $(input).attr("style","width:"+field.width);
			if(field.onchange!="") $(input).on("change",function() { field.onchange(); });
			if(field.onkey!="") $(input).on("keydown",function() { field.onkey(); });
			if(field.focus!="") $(input).attr("focused",field.focus);
			if(field.required!="") $(input).attr("isrequired",field.required);
			if(field.label!="" || field.label2!="") $(input).attr("labeled",field.label+field.label2);
			if(field.tip!="") $(input).attr("title",field.tip);
			if(field.class3!="") $(input).addClass(field.class3);
			$(td).append(input);
			obj.push(td);
			// TODO: FALTA PROGRAMAR PARTE ISINTEGER
			break;
		case "float":
			if(field.label!="") {
				var td=$(`<td class="right nowrap label text-right text-nowrap"></td>`);
				if(field.class2!="") $(td).addClass(field.class2);
				if(field.colspan2!="") $(td).attr("colspan",field.colspan2);
				if(field.rowspan2!="") $(td).attr("rowspan",field.rowspan2);
				if(field.width2!="") $(td).attr("style","width:"+field.width2);
				if(field.required=="true") $(td).append("(*) ");
				if(field.label!="") $(td).append(field.label);
				obj.push(td);
			}
			var td=$(`<td class="left nowrap text-left text-nowrap"></td>`);
			if(field.class!="") $(td).addClass(field.class);
			if(field.colspan!="") $(td).attr("colspan",field.colspan);
			if(field.rowspan!="") $(td).attr("rowspan",field.rowspan);
			if(field.width!="") $(td).attr("style","width:"+field.width);
			var input=$(`<input type="text" autocomplete="off" class="form-control"/>`);
			if(field.name!="") $(input).attr("name",field.name).attr("id",field.name);
			if(field.value!="") $(input).attr("value",field.value);
			if(field.width!="") $(input).attr("style","width:"+field.width);
			if(field.onchange!="") $(input).on("change",function() { field.onchange(); });
			if(field.onkey!="") $(input).on("keydown",function() { field.onkey(); });
			if(field.focus!="") $(input).attr("focused",field.focus);
			if(field.required!="") $(input).attr("isrequired",field.required);
			if(field.label!="" || field.label2!="") $(input).attr("labeled",field.label+field.label2);
			if(field.tip!="") $(input).attr("title",field.tip);
			if(field.class3!="") $(input).addClass(field.class3);
			$(td).append(input);
			obj.push(td);
			// TODO: FALTA PROGRAMAR PARTE ISFLOAT
			break;
		case "color":
			if(field.label!="") {
				var td=$(`<td class="right nowrap label text-right text-nowrap"></td>`);
				if(field.class2!="") $(td).addClass(field.class2);
				if(field.colspan2!="") $(td).attr("colspan",field.colspan2);
				if(field.rowspan2!="") $(td).attr("rowspan",field.rowspan2);
				if(field.width2!="") $(td).attr("style","width:"+field.width2);
				if(field.required=="true") $(td).append("(*) ");
				if(field.label!="") $(td).append(field.label);
				obj.push(td);
			}
			var td=$(`<td class="left nowrap text-left text-nowrap"></td>`);
			if(field.class!="") $(td).addClass(field.class);
			if(field.colspan!="") $(td).attr("colspan",field.colspan);
			if(field.rowspan!="") $(td).attr("rowspan",field.rowspan);
			if(field.width!="") $(td).attr("style","width:"+field.width);
			var input=$(`<input type="text" autocomplete="off" class="form-control"/>`);
			if(field.name!="") $(input).attr("name",field.name).attr("id",field.name);
			if(field.value!="") $(input).attr("value",field.value);
			if(field.width!="") $(input).attr("style","width:"+field.width);
			if(field.onchange!="") $(input).on("change",function() { field.onchange(); });
			if(field.onkey!="") $(input).on("keydown",function() { field.onkey(); });
			if(field.focus!="") $(input).attr("focused",field.focus);
			if(field.required!="") $(input).attr("isrequired",field.required);
			if(field.label!="" || field.label2!="") $(input).attr("labeled",field.label+field.label2);
			if(field.tip!="") $(input).attr("title",field.tip);
			if(field.class3!="") $(input).addClass(field.class3);
			$(td).append(input);
			obj.push(td);
			// TODO: FALTA PROGRAMAR PARTE ISCOLOR
			break;
		case "date":
			if(field.label!="") {
				var td=$(`<td class="right nowrap label text-right text-nowrap"></td>`);
				if(field.class2!="") $(td).addClass(field.class2);
				if(field.colspan2!="") $(td).attr("colspan",field.colspan2);
				if(field.rowspan2!="") $(td).attr("rowspan",field.rowspan2);
				if(field.width2!="") $(td).attr("style","width:"+field.width2);
				if(field.required=="true") $(td).append("(*) ");
				if(field.label!="") $(td).append(field.label);
				obj.push(td);
			}
			var td=$(`<td class="left nowrap text-left text-nowrap"></td>`);
			if(field.class!="") $(td).addClass(field.class);
			if(field.colspan!="") $(td).attr("colspan",field.colspan);
			if(field.rowspan!="") $(td).attr("rowspan",field.rowspan);
			if(field.width!="") $(td).attr("style","width:"+field.width);
			var input=$(`<input type="text" autocomplete="off" class="form-control"/>`);
			if(field.name!="") $(input).attr("name",field.name).attr("id",field.name);
			if(field.value!="") $(input).attr("value",field.value);
			if(field.width!="") $(input).attr("style","width:"+field.width);
			if(field.onchange!="") $(input).on("change",function() { field.onchange(); });
			if(field.onkey!="") $(input).on("keydown",function() { field.onkey(); });
			if(field.focus!="") $(input).attr("focused",field.focus);
			if(field.required!="") $(input).attr("isrequired",field.required);
			if(field.label!="" || field.label2!="") $(input).attr("labeled",field.label+field.label2);
			if(field.tip!="") $(input).attr("title",field.tip);
			if(field.class3!="") $(input).addClass(field.class3);
			$(td).append(input);
			obj.push(td);
			// TODO: FALTA PROGRAMAR PARTE ISDATE
			break;
		case "time":
			if(field.label!="") {
				var td=$(`<td class="right nowrap label text-right text-nowrap"></td>`);
				if(field.class2!="") $(td).addClass(field.class2);
				if(field.colspan2!="") $(td).attr("colspan",field.colspan2);
				if(field.rowspan2!="") $(td).attr("rowspan",field.rowspan2);
				if(field.width2!="") $(td).attr("style","width:"+field.width2);
				if(field.required=="true") $(td).append("(*) ");
				if(field.label!="") $(td).append(field.label);
				obj.push(td);
			}
			var td=$(`<td class="left nowrap text-left text-nowrap"></td>`);
			if(field.class!="") $(td).addClass(field.class);
			if(field.colspan!="") $(td).attr("colspan",field.colspan);
			if(field.rowspan!="") $(td).attr("rowspan",field.rowspan);
			if(field.width!="") $(td).attr("style","width:"+field.width);
			var input=$(`<input type="text" autocomplete="off" class="form-control"/>`);
			if(field.name!="") $(input).attr("name",field.name).attr("id",field.name);
			if(field.value!="") $(input).attr("value",field.value);
			if(field.width!="") $(input).attr("style","width:"+field.width);
			if(field.onchange!="") $(input).on("change",function() { field.onchange(); });
			if(field.onkey!="") $(input).on("keydown",function() { field.onkey(); });
			if(field.focus!="") $(input).attr("focused",field.focus);
			if(field.required!="") $(input).attr("isrequired",field.required);
			if(field.label!="" || field.label2!="") $(input).attr("labeled",field.label+field.label2);
			if(field.tip!="") $(input).attr("title",field.tip);
			if(field.class3!="") $(input).addClass(field.class3);
			$(td).append(input);
			obj.push(td);
			// TODO: FALTA PROGRAMAR PARTE ISTIME
			break;
		case "datetime":
			if(field.label!="") {
				var td=$(`<td class="right nowrap label text-right text-nowrap"></td>`);
				if(field.class2!="") $(td).addClass(field.class2);
				if(field.colspan2!="") $(td).attr("colspan",field.colspan2);
				if(field.rowspan2!="") $(td).attr("rowspan",field.rowspan2);
				if(field.width2!="") $(td).attr("style","width:"+field.width2);
				if(field.required=="true") $(td).append("(*) ");
				if(field.label!="") $(td).append(field.label);
				obj.push(td);
			}
			var td=$(`<td class="left nowrap text-left text-nowrap"></td>`);
			if(field.class!="") $(td).addClass(field.class);
			if(field.colspan!="") $(td).attr("colspan",field.colspan);
			if(field.rowspan!="") $(td).attr("rowspan",field.rowspan);
			if(field.width!="") $(td).attr("style","width:"+field.width);
			var input=$(`<input type="text" autocomplete="off" class="form-control"/>`);
			if(field.name!="") $(input).attr("name",field.name).attr("id",field.name);
			if(field.value!="") $(input).attr("value",field.value);
			if(field.width!="") $(input).attr("style","width:"+field.width);
			if(field.onchange!="") $(input).on("change",function() { field.onchange(); });
			if(field.onkey!="") $(input).on("keydown",function() { field.onkey(); });
			if(field.focus!="") $(input).attr("focused",field.focus);
			if(field.required!="") $(input).attr("isrequired",field.required);
			if(field.label!="" || field.label2!="") $(input).attr("labeled",field.label+field.label2);
			if(field.tip!="") $(input).attr("title",field.tip);
			if(field.class3!="") $(input).addClass(field.class3);
			$(td).append(input);
			obj.push(td);
			// TODO: FALTA PROGRAMAR PARTE ISDATETIME
			break;
		case "textarea":
			obj.push($(`<td>[TEXTAREA]</td>`));
			break;
		case "iframe":
			obj.push($(`<td>[IFRAME]</td>`));
			break;
		case "select":
			obj.push($(`<td>[SELECT]</td>`));
			break;
		case "multiselect":
			obj.push($(`<td>[MULTISELECT]</td>`));
			break;
		case "checkbox":
			obj.push($(`<td>[CHECKBOX]</td>`));
			break;
			//~ obj.push($(`<div class="custom-control custom-switch"></div>`);
			//~ var input=$(`<input type="${field.type}" class="custom-control-input" id="${field.name}" name="${field.name}" value="${field.value}"/>`);
			//~ if(isset(field.checked)) {
				//~ if(field.checked=="true") {
					//~ $(input).attr("checked","checked");
				//~ }
			//~ } else if(isset(field.value)) {
				//~ if(field.value=="1") {
					//~ $(input).attr("checked","checked");
				//~ }
			//~ }
			//~ $(obj).append(input);
			//~ if(field.label!="") {
				//~ var label=$(`<label class="custom-control-label" for="${field.name}">${field.label}</label>`);
				//~ $(obj).append(label);
			//~ }
		case "button":
			var td=$(`<td class="text-left text-nowrap"></td>`);
			if(field.colspan!="") $(td).attr("colspan",field.colspan);
			if(field.rowspan!="") $(td).attr("rowspan",field.rowspan);
			if(field.class!="") $(td).addClass(field.class);
			if(field.width!="") $(td).attr("style","width:"+field.width);
			var button=$(`<button type="button" class="button btn btn-primary text-nowrap"/>`);
			if(field.onclick!="") $(button).on("click",{event:field.onclick},saltos.__form_event);
			if(field.focus!="") $(button).attr("focused",field.focus);
			if(field.label!="") $(button).attr("labeled",field.label);
			if(field.width2!="") $(button).attr("style","width:"+field.width2);
			if(field.tip!="") $(button).attr("title",field.tip).attr("data-toggle","tooltip");
			if(field.class2!="") $(button).addClass(field.class2);
			if(field.name!="") $(button).attr("id",field.name);
			if(field.icon!="") $(button).append(`<span class="${field.icon}"></span>`);
			if(field.icon!="" && field.value!="") $(button).append("&nbsp;");
			if(field.value!="") $(button).append(field.value);
			$(td).append(button);
			obj.push(td);
			break;
		case "password":
			if(field.label!="") {
				var td=$(`<td class="right nowrap label text-right text-nowrap"></td>`);
				if(field.class2!="") $(td).addClass(field.class2);
				if(field.colspan2!="") $(td).attr("colspan",field.colspan2);
				if(field.rowspan2!="") $(td).attr("rowspan",field.rowspan2);
				if(field.width2!="") $(td).attr("style","width:"+field.width2);
				if(field.required=="true") $(td).append("(*) ");
				if(field.label!="") $(td).append(field.label);
				obj.push(td);
			}
			var td=$(`<td class="left nowrap text-left text-nowrap"></td>`);
			if(field.class!="") $(td).addClass(field.class);
			if(field.colspan!="") $(td).attr("colspan",field.colspan);
			if(field.rowspan!="") $(td).attr("rowspan",field.rowspan);
			if(field.width!="") $(td).attr("style","width:"+field.width);
			var input=$(`<input type="password" autocomplete="off" class="form-control"/>`);
			if(field.name!="") $(input).attr("name",field.name).attr("id",field.name);
			if(field.value!="") $(input).attr("value",field.value);
			if(field.width!="") $(input).attr("style","width:"+field.width);
			if(field.onchange!="") $(input).on("change",{event:field.onchange},saltos.__form_event);
			if(field.onkey!="") $(input).on("keydown",{event:field.onkey},saltos.__form_event);
			if(field.focus!="") $(input).attr("focused",field.focus);
			if(field.required!="") $(input).attr("isrequired",field.required);
			if(field.label!="" || field.label2!="") $(input).attr("labeled",field.label+field.label2);
			if(field.tip!="") $(input).attr("title",field.tip);
			if(field.class3!="") $(input).addClass(field.class3);
			$(td).append(input);
			obj.push(td);
			break;
		case "file":
			obj.push($(`<td>[FILE]</td>`));
			break;
		case "link":
			obj.push($(`<td>[LINK]</td>`));
			break;
		case "separator":
			var td=$(`<td class="separator"></td>`);
			if(field.class!="") $(td).addClass(field.class);
			if(field.colspan!="") $(td).attr("colspan",field.colspan);
			if(field.rowspan!="") $(td).attr("rowspan",field.rowspan);
			if(field.width!="" && field.height!="") $(td).attr("style","width:"+field.width+";height:"+field.height);
			else if(field.width!="") $(td).attr("style","width:"+field.width);
			else if(field.height!="") $(td).attr("style","height:"+field.height);
			if(field.name!="") $(td).attr("id",field.name);
			obj.push(td);
			break;
		case "label":
			obj.push($(`<td>[LABEL]</td>`));
			break;
		case "image":
			obj.push($(`<td>[IMAGE]</td>`));
			break;
		case "plot":
			obj.push($(`<td>[PLOT]</td>`));
			break;
		case "menu":
			obj.push($(`<td>[MENU]</td>`));
			break;
		case "grid":
			obj.push($(`<td>[GRID]</td>`));
			break;
		case "excel":
			obj.push($(`<td>[EXCEL]</td>`));
			break;
		case "copy":
			obj.push($(`<td>[COPY]</td>`));
			break;
	}
	return obj;
}

saltos.__form_event=function(obj) {
	if(typeof obj.data.event=="string") {
		eval(obj.data.event);
	}
	if(typeof obj.data.event=="function") {
		obj.data.event();
	}
}

/* FUNCIONES PARA TAREAS DEL USER INTERFACE */
saltos.add_header=function(menu) {
	for(var key in menu) {
		if(saltos.limpiar_key(key)=="header") {
			for(var key2 in menu[key]) {
				if(saltos.limpiar_key(key2)=="option") {
					saltos.add_button_in_navbar(menu[key][key2]);
				}
			}
		}
	}
};

saltos.remove_header_title=function() {
	$(".tabs2 li.center").remove();
};

saltos.add_header_title=function(info) {
	saltos.add_button_in_navbar({
		"label":document.title,
		"onclick":"saltos.opencontent('?page=about')",
		"icon":info.icon,
		"tip":document.title,
		"class":"nowrap",
		"class2":"center",
	});
};

saltos.add_menu=function(menu) {
	if(saltos.getIntCookie("saltos_ui_menu_closed")) {
		$("#menu").hide();
		$("#data").removeClass("col-lg-10");
		$("#data").addClass("col-lg-12");
	}
	for(var key in menu) {
		if(saltos.limpiar_key(key)=="group") {
			var visible=saltos.getIntCookie("saltos_ui_menu_"+menu[key].name)
			if(visible) {
				menu[key].active=0;
			} else {
				menu[key].active=1;
			}
			saltos.add_group_in_menu(menu[key]);
			for(var key2 in menu[key]) {
				if(saltos.limpiar_key(key2)=="option") {
					saltos.add_link_in_group(menu[key][key2]);
				}
			}
		}
	}



};

saltos.make_alert=function(option) {
	saltos.check_params(option,["type","data"]);
	var alert=$(`<div class="alert alert-${option.type} m-0" role="alert">${option.data}</div>`);
	return alert;
};

/* FOR HISTORY MANAGEMENT */
saltos.current_hash=function() {
	var url=window.location.hash;
	var pos=strpos(url,"#");
	if(pos!==false) url=substr(url,pos+1);
	return url;
};

saltos.history_push_hash=function(hash) {
	var pos=strpos(hash,"?");
	if(pos!==false) hash=substr(hash,pos+1);
	if(hash!=saltos.current_hash()) {
		history.pushState(null,null,"#"+hash);
	}
};

saltos.history_replace_hash=function(hash) {
	var pos=strpos(hash,"?");
	if(pos!==false) hash=substr(hash,pos+1);
	if(hash!=saltos.current_hash()) {
		history.replaceState(null,null,"#"+hash);
	}
};

saltos.opencontent_hash=function() {
	var hash=saltos.current_hash();
	if(hash!="") hash="?"+hash;
	saltos.opencontent(hash);
};

saltos.init_history=function() {
	window.onhashchange=saltos.opencontent_hash;
	var hash=saltos.current_hash();
	if(hash=="") {
		var temp=$.ajax({url:"index.php?action=default",async:false}).responseJSON.default;
		saltos.history_replace_hash("page="+temp.page);
	}
	saltos.opencontent_hash();
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
	$("body").append(`<script type="text/javascript">${arg}</script>`);
}

saltos.add_js_file=function(arg) {
	$("body").append(`<script type="text/javascript" src="${arg}"></script>`);
}

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
	$("body").append(`<style type="text/css">${arg}</style>`);
}

saltos.add_css_file=function(arg) {
	$("body").append(`<link href="${arg}" rel="stylesheet" type="text/css"></link>`);
}

/* LOAD AND SAVE FUNCTIONS */
saltos.opencontent=function(url,callback) {
	$(".tooltip").remove();
	$(window).scrollTop(0);
	saltos.history_push_hash(url);
	// CHECK PARAMS
	if(!isset(url)) url="";
	if(!isset(callback)) callback=function() {};
	// CONTINUE
	var url2=parse_url(url);
	var array=saltos.querystring2array(url2.query);
	if(!isset(array["page"]) && !isset(array["action"]) && !isset(array["id"])) {
		var temp=$.ajax({url:"index.php?action=default",async:false}).responseJSON.default;
		array["page"]=temp.page;
		array["action"]=temp.action;
		array["id"]=temp.id;
	}
	if(isset(array["page"]) && !isset(array["action"]) && !isset(array["id"])) {
		var temp=$.ajax({url:"index.php?action=default&page="+array["page"],async:false}).responseJSON.default;
		array["action"]=temp.action;
		array["id"]=temp.id;
	}
	if(isset(array["page"]) && isset(array["action"]) && array["action"]=="limpiar") {
		array["action"]="list";
		array["limpiar"]="1";
	}
	if(!isset(saltos.default)) saltos.default={};
	if(isset(array["page"])) saltos.default.page=array["page"];
	if(isset(array["action"])) saltos.default.action=array["action"];
	if(isset(array["id"])) saltos.default.id=array["id"];
	var querystring=saltos.array2querystring(array);
	if(array["action"]=="list") {
		saltos.list=$.ajax({url:"index.php?"+querystring,async:false}).responseJSON.list;
		document.title=`${saltos.list.title} - ${saltos.info.title} - ${saltos.info.name} v${saltos.info.version} r${saltos.info.revision}`;
		saltos.remove_header_title();
		saltos.add_header_title(saltos.info);
		var temp=saltos.make_list(saltos.list);
		var tabs=saltos.make_tabs(temp);
		$(".ui-layout-center > *").remove();
		$(".ui-layout-center").append(tabs);
		saltos.add_js(saltos.list);
		saltos.add_css(saltos.list);
	}
	if(array["action"]=="form") {
		saltos.form=$.ajax({url:"index.php?"+querystring,async:false}).responseJSON.form;
		document.title=`${saltos.form.title} - ${saltos.info.title} - ${saltos.info.name} v${saltos.info.version} r${saltos.info.revision}`;
		saltos.remove_header_title();
		saltos.add_header_title(saltos.info);
		var temp=saltos.make_form(saltos.form);
		var tabs=saltos.make_tabs(temp);
		$(".ui-layout-center > *").remove();
		$(".ui-layout-center").append(tabs);
		saltos.add_js(saltos.form);
		saltos.add_css(saltos.form);
	}
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

/* FOR COMPATIBILITY */
saltos.make_compat=function() {
	// GENERAL CASES
	var fns=["floatval2(obj)","intval2(obj)","_format_number(obj,punto)","check_required()","intelligence_cut(txt,max)","dateval(value)","timeval(value)","__days_of_a_month(year,month)","check_datetime(orig,comp,dest)","check_date(orig,comp,dest)","check_time(orig,comp,dest)","get_keycode(event)","is_enterkey(event)","is_escapekey(event)","is_disabled(obj)","addlog(msg)","security_iframe(obj)","make_dialog()","dialog(title,message,buttons)","make_notice()","hide_popupnotice()","notice(title,message,arg1,arg2,arg3)","__sync_cookies_helper()","sync_cookies(cmd)","getCookie(name)","getIntCookie(name)","getBoolCookie(name)","setCookie(name,value)","setIntCookie(name,value)","setBoolCookie(name,value)","loadingcontent(message)","unloadingcontent()","isloadingcontent()","hash_encode(url)","hash_decode(hash)","current_href()","current_hash()","history_pushState(url)","history_replaceState(url)","init_history()","addcontent(url)","submitcontent(form,callback)","opencontent(url,callback)","errorcontent(code,text)","loadcontent(xml)","html2str(html)","str2html(str)","fix4html(str)","getstylesheet(html,cad1,cad2)","update_style(html,html2)","updatecontent(html)","make_menu(obj)","toggle_menu()","make_tabs(obj)","hide_popupdialog()","make_tabs2(obj)","make_extras(obj)","make_draganddrop(obj)","make_hovers()","make_ckeditors(obj)","unmake_ckeditors(obj)","make_tooltips()","hide_tooltips()","make_focus()","unmake_focus()","make_tables(obj)","make_contextmenu()","hide_contextmenu()","get_colors(clase,param)","rgb2hex(color)","make_shortcuts()","make_abort()","make_back2top()","make_resizable()"];
	for(var i in fns) {
		var name=strtok(fns[i],"(");
		var args=strtok(")");
		if(!args) args="";
		if(!isset(saltos[name])) {
			console.log(`unimplemented function ${name}`);
			saltos.add_js_code(`
				saltos.${name}=function(${args}) {
					console.log("call to unimplemented function saltos.${name}");
				}
			`);
		}
		if(isset(window[name])) {
			console.log(`overwriting an implemented function ${name}`);
		}
		saltos.add_js_code(`
			function ${name}(${args}) {
				console.log("call to deprecated function ${name}");
				return saltos.${name}(${args});
			}
		`);
	}
	// SPECIAL CASES WHERE OBJ MUST TO BE TRANSLATED TO CLASS
	var fns=["get_class_key_val(obj,param)","get_class_id(obj)","get_class_fn(obj)","get_class_hash(obj)"];
	for(var i in fns) {
		var name=strtok(fns[i],"(");
		var args=strtok(")");
		var temp=str_replace("obj",'$(obj).attr("class")',args);
		saltos.add_js_code(`
			function ${name}(${args}) {
				console.log("call to deprecated function ${name}");
				return saltos.${name}(${temp});
			}
		`);
	}
	// ISLOGIN CASE THAT NOW IS A VARIABLE AND NOT A FUNCTION
	var name="saltos_islogin";
	var args="obj";
	saltos.add_js_code(`
		function ${name}(${args}) {
			console.log("call to deprecated function ${name}");
			return saltos.${name};
		}
	`);
	// COPY_VALUE CASE WHERE SWAP THE ARGS
	var name="copy_value";
	var args="dest,src";
	var temp="src,dest";
	saltos.add_js_code(`
		function ${name}(${args}) {
			console.log("call to deprecated function ${name}");
			return saltos.${name}(${temp});
		}
	`);
};

/* MAIN CODE */
(function($) {
	saltos.init_error();
	saltos.make_compat();
	saltos.islogin=$.ajax({url:"index.php?action=islogin",async:false}).responseJSON.islogin;
	if(saltos.islogin) {
		// CARGAR DATOS
		saltos.sync_cookies("start");
		saltos.info=$.ajax({url:"index.php?action=info",async:false}).responseJSON.info;
		saltos.menu=$.ajax({url:"index.php?action=menu",async:false}).responseJSON.menu;

		// MONTAR PANTALLA
		document.title=`${saltos.info.title} - ${saltos.info.name} v${saltos.info.version} r${saltos.info.revision}`;
		saltos.add_layout(saltos.info);
		saltos.add_header(saltos.menu);
		saltos.add_header_title(saltos.info);
		saltos.add_menu(saltos.menu);

		// TOOLTIPS
		saltos.make_tooltips();

		// CARGAR PRIMER CONTENIDO
		saltos.init_history();
	} else {
		// CARGAR DATOS
		//~ saltos.info=$.ajax({url:"index.php?action=info",async:false}).responseJSON.info;

		// MONTAR PANTALLA
		//~ document.title=`${saltos.info.title} - ${saltos.info.name} v${saltos.info.version} r${saltos.info.revision}`;
		//~ saltos.add_layout(saltos.info);
		//~ saltos.add_header(saltos.menu);
		//~ saltos.add_header_title(saltos.info);
		//~ saltos.add_menu(saltos.menu);

		// CARGAR PRIMER CONTENIDO
		//~ saltos.init_history();
	}
}(jQuery));
