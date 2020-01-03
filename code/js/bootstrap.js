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

// BOOTSTRAP WIDGETS
saltos.add_layout=function() {
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
};

saltos.add_button_in_navbar=function(option) {
	// CHECK PARAMS
	var params=["class","tip","icon","label","onclick"];
	for(var key in params) if(!isset(option[params[key]])) option[params[key]]="";
	// CONTINUE
	var button=$(`
		<button type="button" class="btn btn-primary ${option.class}" data-toggle="tooltip" title="${option.tip}">
			<span class="${option.icon}"></span> ${option.label}
		</button>
	`);
	$(button).on("click",function() {
		if(typeof option.onclick=="string") eval(option.onclick);
		if(typeof option.onclick=="function") option.onclick();
	});
	if(option.class2=="right") $("#navbar-right").append(button);
	else if(option.class2=="center") $("#navbar-center").append(button);
	else $("#navbar-left").append(button);
};

saltos.add_group_in_menu=function(option) {
	// CHECK PARAMS
	var params=["name","icon","label","show","onclick"];
	for(var key in params) if(!isset(option[params[key]])) option[params[key]]="";
	// CONTINUE
	var group=$(`
		<div class="list-group list-group-flush">
			<button type="button" class="list-group-item list-group-item-action list-group-item-primary" data-toggle="collapse" data-target="#${option.name}">
				<span class="${option.icon}"></span> ${option.label}
			</button>
		</div>
		<div class="list-group list-group-flush collapse ${option.show}" id="${option.name}"></div>
	`);
	$("button",group).on("click",function() {
		if(typeof option.onclick=="string") eval(option.onclick);
		if(typeof option.onclick=="function") option.onclick();
	});
	$("#menu").append(group);
};

saltos.add_link_in_group=function(option) {
	// CHECK PARAMS
	var params=["class","tip","icon","label","onclick"];
	for(var key in params) if(!isset(option[params[key]])) option[params[key]]="";
	// CONTINUE
	var link=$(`
		<button type="button" class="list-group-item list-group-item-action ${option.class} list-group-item-secondary" data-toggle="tooltip" title="${option.tip}">
			<span class="${option.icon}"></span> ${option.label}
		</button>
	`);
	// CHECK DEPTH
	var depth=intval(saltos.get_class_key_val(option.class,"depth_"));
	$("span",link).css("margin-left",(depth*16)+"px");
	// CONTINUE
	$(link).on("click",function() {
		if(typeof option.onclick=="string") eval(option.onclick);
		if(typeof option.onclick=="function") option.onclick();
	});
	$("#menu .list-group:last").append(link);
};

saltos.make_tabs=function(array) {
	var card=$(`
		<div class="card">
			<div class="card-header">
				<ul class="nav nav-tabs card-header-tabs"></ul>
			</div>
			<div class="card-body">
				<div class="tab-content"></div>
			</div>
		</div>
	`);
	for(var key in array) {
		var temp=(key==0)?"active":"";
		$(".nav-tabs",card).append(`
			<li class="nav-item">
				<a class="nav-link ${temp}" data-toggle="tab" href="#tab-pane-${key}">${array[key].title}</a>
			</li>
		`);
		$(".tab-content",card).append(`<div class="tab-pane ${temp}" id="tab-pane-${key}"></div>`);
		$(".tab-pane:last",card).append(array[key].obj);
	}
	return card;
};

/* FUNCIONES PARA EL PROCESADO DE LISTADOS */
saltos.make_table=function(option) {
	if(!count(option.rows)) {
		var alert=saltos.make_alert({
			"type":"warning",
			"data":option.nodata.label,
		});
		return alert;
	}
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
			field.value=saltos.get_filtered_field(row[field.name],field.size);
			$("tbody tr:last td:last",table).append(field.value);
		}
	}
	return table;
};

saltos.__get_filtered_field_helper=function(field,size) {
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
	}
	return field;
};

saltos.make_list=function(option) {
	var obj=$("<div></div>");
	if(isset(option.quick)) {
		var table=saltos.form_table();
		$(obj).append(table);
		$(table).append(saltos.form_by_row_3(option,"quick","row"));
		$(table).append(saltos.form_brtag_2());
	}
	$(obj).append(saltos.make_table(option));
	if(isset(option.pager)) {
		var table=saltos.form_table();
		$(obj).append(table);
		$(table).append(saltos.form_brtag_2());
		$(table).append(saltos.form_by_row_3(option,"pager","row"));
	}
	var array=[{
		title:option.title,
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
						array.push({ "title":title, "obj":obj });
						obj=$("<div></div>");
					}
					title=option[key].title;
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
																array.push({ "title":title, "obj":obj });
																obj=$("<div></div>");
															}
															title=node1[key6].title;
														} else {
															$(obj).append(saltos.form_brtag_1());
														}
														var table=saltos.form_table();
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
														array.push({ "title":title, "obj":obj });
														obj=$("<div></div>");
													}
													title=node1[key6].title;
												} else {
													$(obj).append(saltos.form_brtag_1());
												}
												var table=saltos.form_table();
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
		array.push({ "title":title, "obj":obj });
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
				var table=$("<table class='w-100' cellpadding='0' cellspacing='0' border='0'></table>");
				for(var key2 in temp) {
					$(table).append(temp[key2]);
				}
				var td=$("<td colspan='100'></td>");
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
	var table=$("<table class='w-100' cellpadding='0' cellspacing='0' border='0'></table>");
	for(var key in temp) {
		$(table).append(temp[key]);
	}
	var td=$("<td colspan='100'></td>");
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

saltos.form_table=function() {
	return $("<table class='w-100' cellpadding='0' cellspacing='0' border='0'></table>");
}

saltos.form_field=function(field) {
	// CHECK PARAMS
	var params=[
		"name","value",
		"onchange","onkey",
		"class","class2","class3",
		"colspan","colspan2",
		"rowspan","rowspan2",
		"width","width2",
		"required",
		"focus",
		"label","label2",
		"tip",
		"autocomplete","querycomplete","filtercomplete","oncomplete",
	];
	for(var key in params) if(!isset(field[params[key]])) field[params[key]]="";
	// CONTINUE
	var obj=[];
	if(field.type=="hidden") {
		var input=$(`<input type="hidden" autocomplete="off">`);
		if(field.name!="") $(input).attr("name",field.name).attr("id",field.name);
		if(field.value!="") $(input).attr("value",field.value);
		if(field.onchange!="") $(input).on("change",field.onchange);
		if(field.class!="") $(input).addClass(field.class);
		obj.push(input);
	} else if(field.type=="text") {
		if(field.label!="") {
			var td=$(`<td class="text-right text-nowrap"></td>`);
			if(field.class2!="") $(td).addClass(field.class2);
			if(field.colspan2!="") $(td).attr("colspan",field.colspan2);
			if(field.rowspan2!="") $(td).attr("rowspan",field.rowspan2);
			if(field.width2!="") $(td).attr("style","width:"+field.width2);
			if(field.required=="true") $(td).append("(*) ");
			if(field.label!="") $(td).append(field.label);
			obj.push(td);
		}
		var td=$(`<td class="text-left text-nowrap"></td>`);
		if(field.class!="") $(td).addClass(field.class);
		if(field.colspan!="") $(td).attr("colspan",field.colspan);
		if(field.rowspan!="") $(td).attr("rowspan",field.rowspan);
		if(field.width!="") $(td).attr("style","width:"+field.width);
		var input=$(`<input type="text" autocomplete="off">`);
		if(field.name!="") $(input).attr("name",field.name).attr("id",field.name);
		if(field.value!="") $(input).attr("value",field.value);
		if(field.width!="") $(input).attr("style","width:"+field.width);
		if(field.onchange!="") $(input).on("change",field.onchange);
		if(field.onkey!="") $(input).on("keydown",field.onkey);
		if(field.focus!="") $(input).attr("focused",field.focus);
		if(field.required!="") $(input).attr("isrequired",field.required);
		if(field.label2!="") $(input).attr("labeled",field.label+field.label2);
		if(field.tip!="") $(input).attr("title",field.tip);
		if(field.class3!="") $(input).addClass(field.class3);
		if(field.autocomplete!="") $(input).attr("isautocomplete",field.autocomplete);
		if(field.querycomplete!="") $(input).attr("querycomplete",field.querycomplete);
		if(field.filtercomplete!="") $(input).attr("filtercomplete",field.filtercomplete);
		if(field.oncomplete!="") $(input).attr("oncomplete",field.oncomplete);
		$(td).append(input);
		obj.push(td);
	} else if(field.type=="integer") {
		obj.push($(`<td>[INTEGER]</td>`));
	} else if(field.type=="float") {
		obj.push($(`<td>[FLOAT]</td>`));
	} else if(field.type=="color") {
		obj.push($(`<td>[COLOR]</td>`));
	} else if(field.type=="date") {
		obj.push($(`<td>[DATE]</td>`));
	} else if(field.type=="time") {
		obj.push($(`<td>[TIME]</td>`));
	} else if(field.type=="datetime") {
		obj.push($(`<td>[DATETIME]</td>`));
	} else if(field.type=="textarea") {
		obj.push($(`<td>[TEXTAREA]</td>`));
	} else if(field.type=="iframe") {
		obj.push($(`<td>[IFRAME]</td>`));
	} else if(field.type=="select") {
		obj.push($(`<td>[SELECT]</td>`));
	} else if(field.type=="multiselect") {
		obj.push($(`<td>[MULTISELECT]</td>`));
	} else if(field.type=="checkbox") {
		obj.push($(`<td>[CHECKBOX]</td>`));
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
	} else if(field.type=="button") {
		obj.push($(`
			<td>
				<button type="button" class="btn btn-primary text-nowrap" data-toggle="tooltip" title="${field.tip}">
					<span class="${field.icon}"></span> ${field.value}
				</button>
			</td>
		`));
	} else if(field.type=="password") {
		obj.push($(`<td>[PASSWORD]</td>`));
	} else if(field.type=="file") {
		obj.push($(`<td>[FILE]</td>`));
	} else if(field.type=="link") {
		obj.push($(`<td>[LINK]</td>`));
	} else if(field.type=="separator") {
		obj.push($(`
			<td class="separator ${field.class}" colspan="${field.colspan}" rowspan="${field.rowspan}" style="width:${field.width};height:${field.height}" id="${field.name}"></td>
		`));
	} else if(field.type=="label") {
		obj.push($(`<td>[LABEL]</td>`));
	} else if(field.type=="image") {
		obj.push($(`<td>[IMAGE]</td>`));
	} else if(field.type=="plot") {
		obj.push($(`<td>[PLOT]</td>`));
	} else if(field.type=="menu") {
		obj.push($(`<td>[MENU]</td>`));
	} else if(field.type=="grid") {
		obj.push($(`<td>[GRID]</td>`));
	} else if(field.type=="excel") {
		obj.push($(`<td>[EXCEL]</td>`));
	} else if(field.type=="copy") {
		obj.push($(`<td>[COPY]</td>`));
	}
	return obj;
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
	// ARREGLAR FALLO RIGHT
	$("#navbar-right button").each(function() {
		$(this).parent().prepend(this);
	});
};

saltos.remove_header_title=function() {
	$("#navbar-center *").remove();
};

saltos.add_header_title=function(info) {
	saltos.add_button_in_navbar({
		"label":document.title,
		"onclick":"opencontent('?page=about')",
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
				menu[key].icon="fa fa-arrow-alt-circle-down";
				menu[key].show="show";
			} else {
				menu[key].icon="fa fa-arrow-alt-circle-right";
				menu[key].show="";
			}
			menu[key].onclick=function() {
				saltos.setIntCookie("saltos_ui_menu_"+this.name,(saltos.getIntCookie("saltos_ui_menu_"+this.name)+1)%2);
			};
			saltos.add_group_in_menu(menu[key]);
			for(var key2 in menu[key]) {
				if(saltos.limpiar_key(key2)=="option") {
					saltos.add_link_in_group(menu[key][key2]);
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
};

saltos.make_alert=function(option) {
	// CHECK PARAMS
	var params=["type","data"];
	for(var key in params) if(!isset(option[params[key]])) option[params[key]]="";
	// CONTINUE
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
	opencontent(hash);
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

/* OLD SALTOS COMPATIBILITY */
function toggle_menu() {
	if($("#menu").is(":visible")) {
		$("#menu").hide();
		$("#data").removeClass("col-lg-10");
		$("#data").addClass("col-lg-12");
		saltos.setIntCookie("saltos_ui_menu_closed",1);
	} else {
		$("#data").removeClass("col-lg-12");
		$("#data").addClass("col-lg-10");
		$("#menu").show();
		saltos.setIntCookie("saltos_ui_menu_closed",0);
	}
};

function calculator() {
	console.log("calculator");
};

function translator() {
	console.log("translator");
};

function opencontent(url,callback) {
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
		$("#data > *").remove();
		$("#data").append(tabs);
	}
	if(array["action"]=="form") {
		saltos.form=$.ajax({url:"index.php?"+querystring,async:false}).responseJSON.form;
		document.title=`${saltos.form.title} - ${saltos.info.title} - ${saltos.info.name} v${saltos.info.version} r${saltos.info.revision}`;
		saltos.remove_header_title();
		saltos.add_header_title(saltos.info);
		var temp=saltos.make_form(saltos.form);
		var tabs=saltos.make_tabs(temp);
		$("#data > *").remove();
		$("#data").append(tabs);
	}
};

function openwin(url) {
	window.open(url);
};

function openurl(url) {
	window.location.href=url;
};

function openapp(page,id) {
	opencontent(`index.php?page=${page}&action=form&id=${id}`);
};

function qrcode(id) {
	qrcode2(saltos.default.page,id);
};

function qrcode2(page,id) {
	console.log("qrcode2");
	console.log(page);
	console.log(id);
};

function mailto(mail) {
	opencontent(`index.php?page=correo&action=form&id=0_mailto_${mail}`);
};

/* MAIN CODE */
(function($) {
	saltos.init_error();
	saltos.islogin=$.ajax({url:"index.php?action=islogin",async:false}).responseJSON.islogin;
	if(saltos.islogin) {
		// CARGAR DATOS
		saltos.sync_cookies("start");
		saltos.info=$.ajax({url:"index.php?action=info",async:false}).responseJSON.info;
		saltos.menu=$.ajax({url:"index.php?action=menu",async:false}).responseJSON.menu;

		// MONTAR PANTALLA
		document.title=`${saltos.info.title} - ${saltos.info.name} v${saltos.info.version} r${saltos.info.revision}`;
		saltos.add_layout();
		saltos.add_header(saltos.menu);
		saltos.add_header_title(saltos.info);
		saltos.add_menu(saltos.menu);

		// TOOLTIPS
		$("body").tooltip({
			"selector":"[data-toggle='tooltip']",
			"container":"body",
			"trigger":"hover",
		});

		// CARGAR PRIMER CONTENIDO
		saltos.init_history();
	} else {
		// TODO
	}
}(jQuery));
