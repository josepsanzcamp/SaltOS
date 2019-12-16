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

"use strict";

/* ERROR MANAGEMENT */
window.onerror=function(msg,file,line) {
	var data={"jserror":msg,"details":"Error on file "+file+" at line "+line};
	data="array="+encodeURIComponent(btoa(JSON.stringify(data)));
	$.ajax({ url:"index.php?action=adderror",data:data,type:"post" });
};

/* LOG MANAGEMENT */
function addlog(msg) {
	var data="msg="+encodeURIComponent(btoa(msg));
	$.ajax({ url:"index.php?action=addlog",data:data,type:"post" });
};

/* COOKIE MANAGEMENT */
var cookies_data={};
var cookies_interval=null;
var cookies_counter=0;

function __sync_cookies_helper() {
	for(var hash in cookies_data) {
		if(cookies_data[hash].sync) {
			if(cookies_data[hash].val!=cookies_data[hash].orig) {
				var data="action=cookies&name="+encodeURIComponent(cookies_data[hash].key)+"&value="+encodeURIComponent(cookies_data[hash].val);
				var value=$.ajax({
					url:"index.php",
					data:data,
					type:"post",
					async:false,
				}).responseText;
				if(value!="") {
					cookies_data[hash].orig=cookies_data[hash].val;
					cookies_data[hash].sync=0;
				}
			} else {
				cookies_data[hash].sync=0;
			}
		}
	}
}

function sync_cookies(cmd) {
	if(typeof cmd=="undefined") var cmd="";
	if(cmd=="stop") {
		if(cookies_interval!=null) {
			clearInterval(cookies_interval);
			cookies_interval=null;
		}
		__sync_cookies_helper();
		for(var hash in cookies_data) delete cookies_data[hash];
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
					cookies_data[hash]={
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
		cookies_counter=0;
		cookies_interval=setInterval(function() {
			cookies_counter=cookies_counter+100;
			if(cookies_counter>=1000) {
				__sync_cookies_helper();
				cookies_counter=0;
			}
		},100);
	}
}

function getCookie(name) {
	var hash=md5(name);
	if(typeof cookies_data[hash]=="undefined") {
		var value=$.cookie(name);
	} else {
		var value=cookies_data[hash].val;
	}
	return value;
}

function getIntCookie(name) {
	return intval(getCookie(name));
}

function getBoolCookie(name) {
	return getIntCookie(name)?true:false;
}

function setCookie(name,value) {
	var hash=md5(name);
	if(typeof cookies_data[hash]=="undefined") {
		if(cookies_interval!=null) {
			cookies_data[hash]={
				"key":name,
				"val":value,
				"orig":value+"!",
				"sync":1
			};
			cookies_counter=0;
		} else {
			$.cookie(name,value,{expires:365,path:"/"});
		}
	} else {
		if(cookies_data[hash].val!=value) {
			cookies_data[hash].val=value;
			cookies_data[hash].sync=1;
		}
		cookies_counter=0;
	}
}

function setIntCookie(name,value) {
	setCookie(name,intval(value));
}

function setBoolCookie(name,value) {
	setIntCookie(name,value?1:0);
}

/* COLOR MANAGEMENT */
var cache_colors={};

function get_colors(clase,param) {
	if(typeof(clase)=="undefined" && typeof(param)=="undefined") {
		for(var hash in cache_colors) delete cache_colors[hash];
		return;
	}
	hash=md5(JSON.stringify([clase,param]));
	if(typeof(cache_colors[hash])=="undefined") {
		// GET THE COLORS USING THIS TRICK
		if($("#ui-color-trick").length==0) {
			$("body").append("<div id='ui-color-trick'></div>");
		}
		$("#ui-color-trick").addClass(clase);
		cache_colors[hash]=$("#ui-color-trick").css(param);
		$("#ui-color-trick").removeClass(clase);
	}
	return cache_colors[hash];
}

function rgb2hex(color) {
	if(strncasecmp(color,"rgba",4)==0) {
		var temp=color.split(/([\(,\)])/);
		if(temp.length==11) color=sprintf("%02x%02x%02x",temp[2],temp[4],temp[6]);
	} else if(strncasecmp(color,"rgb",3)==0) {
		var temp=color.split(/([\(,\)])/);
		if(temp.length==9) color=sprintf("%02x%02x%02x",temp[2],temp[4],temp[6]);
	}
	return color;
}

function modify_color(color,factor) {
	var temp=color.split(/([\(,\)])/);
	if(in_array(temp[0],["rgb","rgba"]) && in_array(temp.length,[9,11])) {
		temp[2]=max(0,min(255,intval(temp[2]*factor)));
		temp[4]=max(0,min(255,intval(temp[4]*factor)));
		temp[6]=max(0,min(255,intval(temp[6]*factor)));
		color=implode("",temp);
	}
	return color;
}

/* TEMPLATES MANAGEMENT */
//~ function template(name,js,css,htm) {
	//~ $("body").html("");
	//~ if(css) {
		//~ $("body").append("<link href='css/"+name+".css' rel='stylesheet'>");
	//~ }
	//~ if(htm) {
		//~ $("body").append($.ajax({url:"htm/"+name+".htm",async:false}).responseText);
	//~ }
	//~ if(js) {
		//~ $("body").append("<script src='js/"+name+".js'></script>");
	//~ }
//~ }

// PHP FUNCTIONS
function limpiar_key(arg) {
	if(is_array(arg)) {
		for(var key in arg) arg[key]=limpiar_key(arg[key])
		return arg;
	}
	var pos=strpos(arg,"#");
	if(pos!==false) arg=substr(arg,0,pos);
	return arg;
}

function querystring2array(querystring) {
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
}

function array2querystring(array) {
	var querystring=[];
	for(var key in array) querystring.push(key+"="+encodeURIComponent(array[key]));
	querystring=implode("&",querystring);
	return querystring;
}

// CLASS FUNCTIONS
function get_class_key_val(clase,param) {
	var clases=explode(" ",clase);
	var total=clases.length;
	var length=strlen(param);
	for(var i=0;i<total;i++) {
		if(substr(clases[i],0,length)==param) {
			return substr(clases[i],length);
		}
	}
	return "";
}

function get_class_id(clase) {
	return get_class_key_val(clase,"id_");
}

function get_class_fn(clase) {
	return get_class_key_val(clase,"fn_");
}

function get_class_hash(clase) {
	return get_class_key_val(clase,"hash_");
}

// BOOTSTRAP WIDGETS
function add_layout() {
	var layout=$(`
<nav class="navbar navbar-expand-lg navbar-primary bg-primary">
<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar">
<span class="navbar-toggler-icon"></span>
</button>
<div class="collapse navbar-collapse">
<div class="btn-group mr-auto" id="navbar-left"></div>
<div class="btn-group mx-auto" id="navbar-center"></div>
<div class="btn-group ml-auto" id="navbar-right"></div>
</div>
</nav>
<div class="container-fluid">
<div class="row">
<div class="col-lg-2 p-0" id="menu"></div>
<div class="col-lg-10 p-0" id="data"></div>
</div>
</div>
	`);
	$("body").append(layout);
}

function add_button_in_navbar(option) {
	// CHECK PARAMS
	var params=["class","tip","icon","label","onclick"];
	for(var key in params) if(!isset(option[params[key]])) option[params[key]]="";
	// CONTINUE
	var button=$(`
<button type="button" class="btn btn-primary ${option.class}" data-toggle="tooltip" title="${option.tip}"><span class="${option.icon}"></span> ${option.label}</button>
	`);
	$(button).on("click",function() {
		if(typeof option.onclick=="string") eval(option.onclick);
		if(typeof option.onclick=="function") option.onclick();
	});
	if(option.class2=="right") $("#navbar-right").append(button);
	else if(option.class2=="center") $("#navbar-center").append(button);
	else $("#navbar-left").append(button);
}

function add_group_in_menu(option) {
	// CHECK PARAMS
	var params=["name","icon","label","show","onclick"];
	for(var key in params) if(!isset(option[params[key]])) option[params[key]]="";
	// CONTINUE
	var group=$(`
<div class="list-group list-group-flush">
<button type="button" class="list-group-item list-group-item-action list-group-item-primary" data-toggle="collapse" data-target="#${option.name}"><span class="${option.icon}"></span> ${option.label}</button>
</div>
<div class="list-group list-group-flush collapse ${option.show}" id="${option.name}"></div>
	`);
	$("button",group).on("click",function() {
		if(typeof option.onclick=="string") eval(option.onclick);
		if(typeof option.onclick=="function") option.onclick();
	});
	$("#menu").append(group);
}

function add_link_in_menu(option) {
	// CHECK PARAMS
	var params=["class","tip","icon","label","onclick"];
	for(var key in params) if(!isset(option[params[key]])) option[params[key]]="";
	// CONTINUE
	var link=$(`
<button type="button" class="list-group-item list-group-item-action ${option.class} list-group-item-secondary" data-toggle="tooltip" title="${option.tip}"><span class="${option.icon}"></span> ${option.label}</button>
	`);
	// CHECK DEPTH
	var depth=intval(get_class_key_val(option.class,"depth_"));
	$("span",link).css("margin-left",(depth*16)+"px");
	// CONTINUE
	$(link).on("click",function() {
		if(typeof option.onclick=="string") eval(option.onclick);
		if(typeof option.onclick=="function") option.onclick();
	});
	$("#menu .list-group:last").append(link);
}

function add_table_in_data(option) {
	// CHECK PARAMS
	var params=["fields","rows"];
	for(var key in params) if(!isset(option[params[key]])) option[params[key]]="";
	// CONTINUE
	var table=$(`
<table class="table table-striped table-hover table-sm">
	<thead class="thead-dark"></thead>
	<tbody></tbody>
</table>
	`);
	$("thead",table).append("<tr></tr>");
	for(var key in option.fields) {
		var field=option.fields[key];
		$("thead tr",table).append("<th></th>");
		$("thead tr th:last",table).append(field.label);
	}
	for(var key in option.rows) {
		var row=option.rows[key];
		$("tbody",table).append("<tr></tr>");
		for(var key2 in option.fields) {
			var field=option.fields[key2];
			$("tbody tr:last",table).append("<td></td>");
			$("tbody tr:last td:last",table).append(get_filtered_field(row[field.name],field.size));
		}
	}
	$("#data").append(table);
}

/* FUNCIONES PARA AYUDAR CON EL MODELO DE DATOS DE SALTOS */
function __get_filtered_field_helper(field,size) {
	if(typeof size!="undefined") {
		var len=strlen(field);
		var size2=intval(size);
		if(len>size2) {
			var field2=htmlentities(substr(field,0,size2))+"...";
			field=`<span data-toggle="tooltip" title="${field}">${field2}</span>`;
		} else {
			field=htmlentities(field);
		}
	}
	return field;
}

function get_filtered_field(field,size) {
	if(substr(field,0,4)=="tel:") {
		var temp=explode(":",field,2);
		temp[2]=__get_filtered_field_helper(temp[1],size);
		field=$(`<a href="javascript:void(0)">${temp[2]}</a>`)
		$(field).on("click",function() { qrcode(temp[1]); });
	} else if(substr(field,0,7)=="mailto:") {
		var temp=explode(":",field,2);
		temp[2]=__get_filtered_field_helper(temp[1],size);
		field=$(`<a href="javascript:void(0)">${temp[2]}</a>`)
		$(field).on("click",function() { mailto(temp[1]); });
	} else if(substr(field,0,5)=="href:") {
		var temp=explode(":",field,2);
		temp[2]=__get_filtered_field_helper(temp[1],size);
		field=$(`<a href="javascript:void(0)">${temp[2]}</a>`)
		$(field).on("click",function() { openwin(temp[1]); });
	} else if(substr(field,0,5)=="link:") {
		var temp=explode(":",field,3);
		temp[2]=__get_filtered_field_helper(temp[2],size);
		field=$(`<a href="javascript:void(0)">${temp[2]}</a>`)
		$(field).on("click",function() { eval(temp[1]); });
	}
	return field;
}

/* FUNCIONES PARA TAREAS DEL USER INTERFACE */
function add_header(menu) {
	for(var key in menu) {
		if(limpiar_key(key)=="header") {
			for(var key2 in menu[key]) {
				if(limpiar_key(key2)=="option") {
					add_button_in_navbar(menu[key][key2]);
				}
			}
		}
	}
	// ARREGLAR FALLO RIGHT
	$("#navbar-right button").each(function() {
		$(this).parent().prepend(this);
	});
}

function remove_header_title() {
	$("#navbar-center *").remove();
}

function add_header_title(info) {
	add_button_in_navbar({
		"label":document.title,
		"onclick":"opencontent('?page=about')",
		"icon":info.icon,
		"tip":document.title,
		"class":"nowrap",
		"class2":"center",
	});
}

function add_menu(menu) {
	if(getIntCookie("saltos_ui_menu_closed")) {
		$("#menu").hide();
		$("#data").removeClass("col-lg-10");
		$("#data").addClass("col-lg-12");
	}
	for(var key in menu) {
		if(limpiar_key(key)=="group") {
			var visible=getIntCookie("saltos_ui_menu_"+menu[key].name)
			if(visible) {
				menu[key].icon="fa fa-arrow-alt-circle-down";
				menu[key].show="show";
			} else {
				menu[key].icon="fa fa-arrow-alt-circle-right";
				menu[key].show="";
			}
			menu[key].onclick=function() {
				setIntCookie("saltos_ui_menu_"+this.name,(getIntCookie("saltos_ui_menu_"+this.name)+1)%2);
			};
			add_group_in_menu(menu[key]);
			for(var key2 in menu[key]) {
				if(limpiar_key(key2)=="option") {
					add_link_in_menu(menu[key][key2]);
				}
			}
		}
	}
	$("#menu .collapse").on("show.bs.collapse",function() {
		$(this).prev().find("span").attr("class","fa fa-arrow-alt-circle-down");
	});
	$("#menu .collapse").on("hide.bs.collapse",function() {
		$(this).prev().find("span").attr("class","fa fa-arrow-alt-circle-right");
	});
}

//~ function add_alert(option) {
	//~ // CHECK PARAMS
	//~ var params=["type","data","node","prepend"];
	//~ for(var key in params) if(!isset(option[params[key]])) option[params[key]]="";
	//~ // CONTINUE
	//~ var alert=$(`<div class="alert alert-${option.type} m-0" role="alert">${option.data}</div>`);
	//~ if(option.prepend) $(option.node).prepend(alert);
	//~ if(!option.prepend) $(option.node).append(alert);
//~ }

/* OLD SALTOS COMPATIBILITY */
function toggle_menu() {
	if($("#menu").is(":visible")) {
		$("#menu").hide();
		$("#data").removeClass("col-lg-10");
		$("#data").addClass("col-lg-12");
		setIntCookie("saltos_ui_menu_closed",1);
	} else {
		$("#data").removeClass("col-lg-12");
		$("#data").addClass("col-lg-10");
		$("#menu").show();
		setIntCookie("saltos_ui_menu_closed",0);
	}
}

function calculator() {
	console.log("calculator");
}

function translator() {
	console.log("translator");
}

function opencontent(url,callback) {
	history_push_hash(url);
	// CHECK PARAMS
	if(!isset(url)) url="";
	if(!isset(callback)) callback=function() {};
	// CONTINUE
	var url2=parse_url(url);
	var array=querystring2array(url2.query);
	if(!isset(array["page"]) && !isset(array["action"]) && !isset(array["id"])) {
		var temp=$.ajax({url:"index.php?action=default",async:false}).responseJSON;
		array["page"]=temp.page;
		array["action"]=temp.action;
		array["id"]=temp.id;
	}
	if(isset(array["page"]) && !isset(array["action"]) && !isset(array["id"])) {
		var temp=$.ajax({url:"index.php?action=default&page="+array["page"],async:false}).responseJSON;
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
	var querystring=array2querystring(array);
	if(array["action"]=="list") {
		saltos.list=$.ajax({url:"index.php?"+querystring,async:false}).responseJSON;
		document.title=`${saltos.list.title} - ${saltos.info.title} - ${saltos.info.name} v${saltos.info.version} r${saltos.info.revision}`;
		remove_header_title();
		add_header_title(saltos.info);
		$("#data *").remove();
		add_table_in_data(saltos.list);
	}
	if(array["action"]=="form") {
		saltos.form=$.ajax({url:"index.php?"+querystring,async:false}).responseJSON;
		document.title=`${saltos.form.title} - ${saltos.info.title} - ${saltos.info.name} v${saltos.info.version} r${saltos.info.revision}`;
		remove_header_title();
		add_header_title(saltos.info);
		$("#data *").remove();
		console.log(saltos.form);
	}
}

function openwin(url) {
	window.open(url);
}

function openurl(url) {
	window.location.href=url;
}

function openapp(page,id) {
	opencontent(`index.php?page=${page}&action=form&id=${id}`);
}

function qrcode(id) {
	qrcode2(saltos.default.page,id);
}

function qrcode2(page,id) {
	console.log("qrcode2");
	console.log(page);
	console.log(id);
}

function mailto(mail) {
	opencontent(`index.php?page=correo&action=form&id=0_mailto_${mail}`);
}

/* FOR HISTORY MANAGEMENT */
function current_hash() {
	var url=window.location.hash;
	var pos=strpos(url,"#");
	if(pos!==false) url=substr(url,pos+1);
	return url;
}

function history_push_hash(hash) {
	var pos=strpos(hash,"?");
	if(pos!==false) hash=substr(hash,pos+1);
	if(hash!=current_hash()) {
		history.pushState(null,null,"#"+hash);
	}
}

function history_replace_hash(hash) {
	var pos=strpos(hash,"?");
	if(pos!==false) hash=substr(hash,pos+1);
	if(hash!=current_hash()) {
		history.replaceState(null,null,"#"+hash);
	}
}

function opencontent_hash() {
	var hash=current_hash();
	if(hash!="") hash="?"+hash;
	opencontent(hash);
}

function init_history() {
	$(window).on("hashchange",opencontent_hash);
	var hash=current_hash();
	if(hash=="") {
		var temp=$.ajax({url:"index.php?action=default",async:false}).responseJSON;
		history_replace_hash("page="+temp.page);
	}
	opencontent_hash();
}

/* MAIN CODE */
var saltos={};

(function($) {
	saltos.islogin=$.ajax({url:"index.php?action=islogin",async:false}).responseJSON;
	if(saltos.islogin) {
		// CARGAR DATOS
		sync_cookies("start");
		saltos.info=$.ajax({url:"index.php?action=info",async:false}).responseJSON;
		saltos.menu=$.ajax({url:"index.php?action=menu",async:false}).responseJSON;

		// MONTAR PANTALLA
		document.title=`${saltos.info.title} - ${saltos.info.name} v${saltos.info.version} r${saltos.info.revision}`;
		add_layout();
		add_header(saltos.menu);
		add_header_title(saltos.info);
		add_menu(saltos.menu);

		// CARGAR PRIMER CONTENIDO
		init_history();

		//~ add_alert({
			//~ "type":"info",
			//~ "data":"HOLA MUNDO",
			//~ "node":"#data",
		//~ });

		// TOOLTIPS
		$("body").tooltip({
			"selector":"[data-toggle='tooltip']",
			"container":"body",
			"trigger":"hover",
		});
		//~ feather.replace();
		//~ $("#data").append(page);
	} else {
		// TODO
	}
}(jQuery));
