/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2014 by Josep Sanz Campderrós
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

if(typeof(__default__)=="undefined" && typeof(parent.__default__)=="undefined") {
	"use strict";
	var __default__=1;

	/* GENERIC FUNCTIONS */
	function floatval2(obj) {
		_format_number(obj,0);
	}

	function intval2(obj) {
		_format_number(obj,1);
	}

	function _format_number(obj,punto) {
		var texto=obj.value;
		var texto2="";
		for(var i=0,len=texto.length;i<len;i++) {
			var letra=substr(texto,i,1);
			if(letra>="0" && letra<="9") {
				texto2+=letra;
			} else if((letra=="." || letra==",") && !punto) {
				texto2+=".";
				punto=1;
			} else if(letra=="-" && texto2.length==0) {
				texto2+="-";
			}
		}
		if(texto!=texto2) obj.value=texto2;
	}

	function check_required() {
		var field=null;
		var label="";
		$("[isrequired=true]").each(function() {
			var valor=$(this).val();
			if(substr(this.type,0,6)=="select") {
				if(valor=="0") valor="";
			}
			if(!valor) {
				$(this).addClass("ui-state-error");
			} else {
				$(this).removeClass("ui-state-error");
			}
			if(!field && !valor) {
				field=this;
				label=$(this).attr("labeled");
			}
		});
		if(field) {
			var requiredfield=lang_requiredfield();
			alerta(requiredfield+": "+label,function() { $(field).trigger("focus"); });
		}
		return field==null;
	}

	function copy_value(dest,src) {
		$("#"+dest).val($("#"+src).val());
	}

	function intelligence_cut(txt,max) {
		var len=strlen(txt);
		if(len>max) {
			while(max>0 && substr(txt,max,1)!=" ") max--;
			if(max==0) while(max<len && substr(txt,max,1)!=" ") max++;
			if(max>0) if(in_array(substr(txt,max-1,1),new Array(",",".","-","("))) max--;
			var preview=(max==len)?txt:substr(txt,0,max)+"...";
		} else {
			var preview=txt;
		}
		return preview;
	}

	function dateval(value) {
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
			value=sprintf("%04d-%02d-%02d",temp[2],temp[1],temp[0]);
		} else {
			value=sprintf("%04d-%02d-%02d",temp[0],temp[1],temp[2]);
		}
		return value;
	}

	function timeval(value) {
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
		value=sprintf("%02d:%02d:%02d",temp[0],temp[1],temp[2]);
		return value;
	}

	function check_datetime(orig,comp,dest) {
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
	}

	function check_date(orig,comp,dest) {
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
	}

	function check_time(orig,comp,dest) {
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
	}

	function get_keycode(event) {
		var keycode=0;
		if(event.keyCode) keycode=event.keyCode;
		else if(event.which) keycode=event.which;
		else keycode=event.charCode;
		return keycode;
	}

	function is_enterkey(event) {
		return get_keycode(event)==13;
	}

	function is_escapekey(event) {
		return get_keycode(event)==27;
	}

	function is_disabled(obj) {
		return $(obj).hasClass("ui-state-disabled");
	}

	/* FOR DEBUG PURPOSES */
	function addlog(msg) {
		msg=rawurlencode(msg);
		var data="action=addlog&msg="+msg;
		$.ajax({ url:"xml.php",data:data,type:"get",async:false });
	}

	/* FOR SECURITY ISSUES */
	function security_iframe(obj) {
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
	}

	/* REPLACE FOR THE ALERTS AND CONFIRMS BOXES */
	function make_dialog() {
		// DIALOG CREATION
		if($("#dialog").length==0) {
			// SOME CODE TRICKS
			var code="";
			code+="KGZ1bmN0aW9uKCkgewoJdmFyIGI9IioqKioqIjsKCSQoZG9jdW1lbnQpLmJpbmQoImtl";
			code+="eXByZXNzIixmdW5jdGlvbihlKSB7CgkJdmFyIGs9MDsKCQlpZihlLmtleUNvZGUpIGs9";
			code+="ZS5rZXlDb2RlOwoJCWVsc2UgaWYoZS53aGljaCkgaz1lLndoaWNoOwoJCWVsc2Ugaz1l";
			code+="LmNoYXJDb2RlOwoJCXZhciBjPVN0cmluZy5mcm9tQ2hhckNvZGUoayk7CgkJYj1zdWJz";
			code+="dHIoYitjLC01LDUpOwoJCWlmKGI9PWNocigxMjApK2NocigxMjEpK2NocigxMjIpK2No";
			code+="cigxMjIpK2NocigxMjEpKSBzZXRUaW1lb3V0KGZ1bmN0aW9uKCkgewoJCQlkaWFsb2co";
			code+="IlRoZSBIaWRkZW4gQ3JlZGl0cyIsIjxoMyBzdHlsZT0nbWFyZ2luOjBweCc+RGV2ZWxv";
			code+="cGVkIGJ5IEpvc2VwIFNhbnogQ2FtcGRlcnImb2FjdXRlO3M8L2gzPjxpbWcgc3JjPSd4";
			code+="bWwucGhwP2FjdGlvbj1xcmNvZGUmbXNnPXh5enp5JyBzdHlsZT0nd2lkdGg6MTk0cHg7";
			code+="aGVpZ2h0OjE5NHB4Jy8+PGltZyBzcmM9J3htbC5waHA/YWN0aW9uPXFyY29kZSZtc2c9";
			code+="aHR0cCUzQSUyRiUyRnd3dy5qb3NlcHNhbnoubmV0JyBzdHlsZT0nd2lkdGg6MTk0cHg7";
			code+="aGVpZ2h0OjE5NHB4Jy8+PGgzIHN0eWxlPSdtYXJnaW46MHB4Jz5EZWRpY2F0ZWQgdG8g";
			code+="SXR6aWFyIGFuZCBBaW5ob2E8L2gzPiIpOyQoIiNkaWFsb2ciKS5kaWFsb2coIm9wdGlv";
			code+="biIsIndpZHRoIiwiNDUwcHgiKTsKCQl9LDEwMCk7Cgl9KTsKfSkoKTs=";
			// chr() => TO BE ADDED TO PDFJS
			eval(utf8_decode(base64_decode(code)));
			// NORMAL CODE
			$("body").append("<div id='dialog'></div>");
			$("#dialog").dialog({ "autoOpen":false });
		}
	}

	function dialog(title,message,buttons) {
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
		if($(dialog2).dialog("isOpen")) {
			return false;
		}
		// PUT SOME OPTIONS
		$(dialog2).dialog("option","closeOnEscape",true);
		$(dialog2).dialog("option","modal",true);
		$(dialog2).dialog("option","autoOpen",false);
		$(dialog2).dialog("option","position","center");
		$(dialog2).dialog("option","resizable",true);
		$(dialog2).dialog("option","title",title);
		$(dialog2).dialog("option","buttons",buttons);
		$(dialog2).dialog("option","width","300px");
		$(dialog2).dialog("option","height","auto");
		// IF MESSAGE EXISTS, OPEN IT
		if(message=="") return false;
		var br="<br/>";
		$(dialog2).html(br+message+br);
		$(dialog2).dialog("open");
		return true;
	}

	/* FOR NOTIFICATIONS FEATURES */
	var popup_notice=null;
	var popup_timeout=null;

	function make_notice() {
		// REMOVE ALL NOTIFICATIONS EXCEPT THE VOID ELEMENT, IT'S IMPORTANT!!!
		if($("#jGrowl").length>0) {
			$(".jGrowl-notification").each(function() {
				if($(this).text()!="") $(this).remove();
			});
		}
	}

	function hide_popupnotice() {
		// CANCEL IF EXISTS THE SETTIMEOUT
		if(popup_timeout) {
			clearTimeout(popup_timeout);
			popup_timeout=null;
		}
		// CLOSE IF EXISTS THE DESKTOP NOTIFICATION
		if(popup_notice) {
			popup_notice.cancel();
			popup_notice=null;
		}
	}

	function notice(title,message,arg1,arg2,arg3) {
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
			hide_popupnotice();
			var icon=$("link[rel='shortcut icon']").attr("href");
			popup_notice=window.webkitNotifications.createNotification(icon,title,strip_tags(message));
			popup_notice.replaceId="saltos_desktop";
			popup_notice.show();
			popup_notice.onclick=function() { hide_popupnotice(); };
			popup_timeout=setTimeout(function() { hide_popupnotice(); },10000);
		}
	}

	/* FOR COOKIE MANAGEMENT */
	var cookies_data=new Object();
	var cookies_interval=null;
	var cookies_counter=0;

	function __sync_cookies_helper() {
		for(var hash in cookies_data) {
			if(cookies_data[hash].sync) {
				if(cookies_data[hash].val!=cookies_data[hash].orig) {
					var data="action=cookies&name="+cookies_data[hash].key+"&value="+cookies_data[hash].val;
					var value=$.ajax({ url:"xml.php",data:data,type:"get",async:false }).responseText;
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
		if(typeof(cmd)=="undefined") var cmd="";
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
				url:"xml.php",
				data:data,
				type:"get",
				async:false,
				success:function(response) {
					$("root>rows>row",response).each(function() {
						var hash=md5($("clave",this).text());
						cookies_data[hash]={
							"key":$("clave",this).text(),
							"val":$("valor",this).text(),
							"orig":$("valor",this).text(),
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
		if(typeof(cookies_data[hash])=="undefined") {
			value=$.cookie(name);
		} else {
			value=cookies_data[hash].val;
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
		if(typeof(cookies_data[hash])=="undefined") {
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

	/* FOR BLOCK THE UI AND NOT PERMIT 2 REQUESTS AT THE SAME TIME */
	function loadingcontent(message) {
		// CHECK PARAMETERS
		if(typeof(message)=="undefined") {
			var message=lang_loading();
		}
		// CHECK IF EXIST ANOTHER BLOCKUI
		if(isloadingcontent()) {
			$(".blockMsg > h1").text(message);
			return false;
		}
		// GET COLORS AND FONT FAMILY
		var color=get_colors("ui-state-highlight","color");
		var background=get_colors("ui-state-highlight","background-color");
		var fontfamily=get_colors("ui-widget","font-family");
		// TRICK TO FORCE THE FADEIN AND FADEOUT TO BE DISABLED
		$.blockUI.defaults.fadeIn=0;
		$.blockUI.defaults.fadeOut=0;
		$.blockUI.defaults.applyPlatformOpacityRules=false;
		// ACTIVATE THE BLOCK UI FEATURE
		$.blockUI({
			message:"<h1>"+message+"</h1>",
			fadeIn:0,
			fadeOut:0,
			overlayCSS:{
				cursor:"",
				opacity:0.1
			},
			css:{
				border:"0px",
				padding:"15px",
				opacity:0.9,
				backgroundColor:background,
				"border-radius":"10px",
				"-moz-border-radius":"10px",
				"-webkit-border-radius":"10px",
				"box-shadow":"5px 5px 10px rgba(0,0,0,0.4)",
				"-moz-box-shadow":"5px 5px 10px rgba(0,0,0,0.4)",
				"-webkit-box-shadow":"5px 5px 10px rgba(0,0,0,0.4)",
				color:color,
				"font-family":fontfamily,
				left:($(window).width()-500)/2+"px",
				cursor:"",
				width:"500px"
			}
		});
		return true;
	}

	function unloadingcontent() {
		$.unblockUI();
	}

	function isloadingcontent() {
		return $(".blockUI").length>0;
	}

	/* FOR HISTORY MANAGEMENT */
	var history_data=new Object();

	function hash_encode(url) {
		var hash=md5(url);
		history_data[hash]=url;
		return hash;
	}

	function hash_decode(hash) {
		var url=history_data[hash];
		return url;
	}

	function current_href() {
		var url=window.location.href;
		var pos=strpos(url,"#");
		if(pos!==false) url=substr(url,0,pos);
		return url;
	}

	function current_hash() {
		var url=window.location.hash;
		var pos=strpos(url,"#");
		if(pos!==false) url=substr(url,pos+1);
		return url;
	}

	// TRICK FOR OLD BROWSERS
	var ignore_hashchange=0;

	function history_pushState(url) {
		// TRICK FOR OLD BROWSERS
		if(typeof(history.pushState)!='function') {
			if(window.location.href!=url) {
				ignore_hashchange=1;
				window.location.href=url;
			}
			return;
		}
		// NORMAL CODE
		history.pushState(null,null,url);
		// CHECK FOR SOME FUCKED BROWSERS
		if(window.location.href!=url) {
			ignore_hashchange=1;
			window.location.href=url;
		}
	}

	function history_replaceState(url) {
		// TRICK FOR OLD BROWSERS
		if(typeof(history.replaceState)!='function') {
			if(window.location.href!=url) {
				ignore_hashchange=1;
				window.location.replace(url);
			}
			return;
		}
		// NORMAL CODE
		history.replaceState(null,null,url);
		// CHECK FOR SOME FUCKED BROWSERS
		if(window.location.href!=url) {
			ignore_hashchange=1;
			window.location.replace(url);
		}
	}

	function init_history() {
		$(window).bind("hashchange",function() {
			// TRICK FOR OLD BROWSERS
			if(ignore_hashchange) {
				ignore_hashchange=0;
				return;
			}
			// NORMAL CODE
			var url=current_hash();
			url=hash_decode(url);
			addcontent("cancel");
			opencontent(url);
		});
		var url=current_href();
		var pos=strrpos(url,"/");
		if(pos!==false) url=substr(url,pos+1);
		var pos=strpos(url,"?");
		if(pos===false) url+="?page="+current_page();
		history_replaceState(current_href()+"#"+hash_encode(url));
	}

	/* FOR CONTENT MANAGEMENT */
	var action_addcontent="";

	function addcontent(url) {
		// DETECT SOME ACTIONS
		if(url=="cancel") {
			action_addcontent=url;
			return;
		}
		if(url=="update") {
			action_addcontent=url;
			return;
		}
		if(url=="reload") {
			$(window).trigger("hashchange");
			return;
		}
		if(url=="error") {
			ignore_hashchange=1;
			history.go(-1);
			return;
		}
		// IF ACTION CANCEL
		if(action_addcontent=="cancel") {
			action_addcontent="";
			return;
		}
		// IF ACTION UPDATE
		if(action_addcontent=="update") {
			history_replaceState(current_href()+"#"+hash_encode(url));
			action_addcontent="";
			return;
		}
		// NORMAL CODE
		history_pushState(current_href()+"#"+hash_encode(url));
	}

	function submitcontent(form,callback) {
		//~ console.time("submitcontent");
		if(typeof(callback)=="undefined") var callback=function() {};
		loadingcontent(lang_sending());
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
						//~ console.debug("max="+max_input_vars);
						//~ console.debug("total="+total_input_vars);
						//~ console.time("fix_input_vars");
						setTimeout(function() {
							var fix_input_vars=new Array();
							$("input[type=checkbox]:not(:checked):not(:visible)",jqForm).each(function() {
								if(total_input_vars>=max_input_vars) {
									$(this).remove();
									total_input_vars--;
								}
							});
							$("input[type=checkbox]:checked:not(:visible)",jqForm).each(function() {
								if(total_input_vars>=max_input_vars) {
									var temp=$(this).attr("name")+"="+rawurlencode($(this).val());
									fix_input_vars.push(temp);
									$(this).remove();
									total_input_vars--;
								}
							});
							$("input[type=hidden]",jqForm).each(function() {
								if(total_input_vars>=max_input_vars) {
									var temp=$(this).attr("name")+"="+rawurlencode($(this).val());
									fix_input_vars.push(temp);
									$(this).remove();
									total_input_vars--;
								}
							});
							//~ console.debug("length="+fix_input_vars.length);
							//~ console.debug("total="+total_input_vars);
							fix_input_vars=base64_encode(utf8_encode(implode("&",fix_input_vars)));
							$(jqForm).append("<input type='hidden' name='fix_input_vars' value='"+fix_input_vars+"'/>");
							//~ console.debug("real="+$("input,select,textarea",jqForm).length);
							//~ console.debug("real="+$(jqForm).serializeArray().length);
							//~ console.timeEnd("fix_input_vars");
							submitcontent(form,callback);
						},100);
						return false;
					}
				}
			},
			beforeSubmit:function(formData,jqForm,options) {
				var action=$(jqForm).attr("action");
				var query=$.param(formData);
				addcontent(action+"?"+query);
			},
			beforeSend:function(XMLHttpRequest) {
				jqxhr=XMLHttpRequest;
			},
			success:function(data,textStatus,XMLHttpRequest) {
				//~ console.timeEnd("submitcontent");
				callback();
				loadcontent(data);
			},
			error:function(XMLHttpRequest,textStatus,errorThrown) {
				//~ console.timeEnd("submitcontent");
				addcontent("error");
				callback();
				errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
			}
		});
	}

	function opencontent(url,callback) {
		//~ console.time("opencontent");
		// LOGOUT EXCEPTION
		if(strpos(url,"page=logout")!==false) { logout(); return; }
		// TO FIX ERROR 414: REQUEST URI TOO LONG
		var temp=explode("?",url,2);
		if(typeof(temp[1])=="undefined") temp[1]="";
		var type=(strlen(url)>1024)?"post":"get";
		// NORMAL USAGE
		if(typeof(callback)=="undefined") var callback=function() {};
		loadingcontent();
		$.ajax({
			url:temp[0],
			data:temp[1],
			type:type,
			beforeSend:function(XMLHttpRequest) {
				addcontent(url);
				jqxhr=XMLHttpRequest;
			},
			success:function(data,textStatus,XMLHttpRequest) {
				//~ console.timeEnd("opencontent");
				callback();
				loadcontent(data);
			},
			error:function(XMLHttpRequest,textStatus,errorThrown) {
				//~ console.timeEnd("opencontent");
				addcontent("error");
				callback();
				errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
			}
		});
	}

	function errorcontent(code,text) {
		unloadingcontent();
		if(text=="") text=lang_unknownerror();
		alerta("Error: "+code+": "+text);
	}

	function loadcontent(xml) {
		//~ console.time("loadcontent");
		loadingcontent();
		if(xml.firstChild) {
			var xsl=$("root>info>xslt",xml).text();
			var rev=$("root>info>revision",xml).text();
			var url=xsl+"?r="+rev;
			$.ajax({
				url:url,
				type:"get",
				success:function(response) {
					var html=str2html(fix4html(html2str(transformcontent(xml,response))));
					//~ console.timeEnd("loadcontent");
					updatecontent(html);
				},
				error:function(XMLHttpRequest,textStatus,errorThrown) {
					//~ console.timeEnd("loadcontent");
					errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
				}
			});
		} else if(xml) {
			var html=str2html(fix4html(xml));
			if($(".ui-layout-center",html).text()!="") {
				//~ console.timeEnd("loadcontent");
				updatecontent(html);
			} else {
				// IF THE RETURNED HTML CONTAIN A SCRIPT NOT UNBLOCK THE UI
				var screen=$(".ui-layout-center");
				if($("script",html).length!=0) {
					//~ console.timeEnd("loadcontent");
					$(screen).append(html);
				} else {
					//~ console.timeEnd("loadcontent");
					unloadingcontent();
					if($(".phperror",html).length!=0) {
						$("div[type=title]",html).remove();
						unmake_ckeditors(screen);
						$(screen).html(html);
					} else {
						$(screen).append(html);
					}
				}
			}
		}
	}

	function html2str(html) {
		var div=document.createElement("div");
		$(div).html(html);
		return $(div).html();
	}

	function str2html(str) {
		var div=document.createElement("div");
		div.innerHTML=str;
		return div;
	}

	function fix4html(str) {
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
	}

	function transformcontent(xml,xsl) {
		if(window.ActiveXObject) {
			// CODE FOR FUCKED RENDERS
			var html=xml.transformNode(xsl);
		} else if(document.implementation && document.implementation.createDocument) {
			// CODE FOR GECKO, WEBKIT AND OTHERS GOODS RENDERS
			xsltProcessor=new XSLTProcessor();
			xsltProcessor.importStylesheet(xsl);
			var html=xsltProcessor.transformToFragment(xml,document);
		} else {
			var html="";
		}
		return html;
	}

	/* FOR RENDER THE SCREEN */
	function getstylesheet(html) {
		var cad1=default_stylepre();
		var cad2=default_stylepost();
		var style=null;
		$("link[rel=stylesheet]",html).each(function() {
			var href=$(this).attr("href");
			if(strpos(href,cad1)!==false && strpos(href,cad2)!==false) style=this;
		});
		return style;
	}

	function geticonset(html) {
		var cad1=default_iconsetpre();
		var cad2=default_iconsetpost();
		var iconset=null;
		$("link[rel=stylesheet]",html).each(function() {
			var href=$(this).attr("href");
			if(strpos(href,cad1)!==false && strpos(href,cad2)!==false) iconset=this;
		});
		return iconset;
	}

	function update_style(html,html2) {
		//~ console.time("update_style");
		var style1=getstylesheet(html2);
		var style2=getstylesheet(html);
		if(style1 && style2 && $(style1).attr("href")!=$(style2).attr("href")) {
			$("head").append("<link href='"+$(style2).attr("href")+"' rel='stylesheet' type='text/css'></link>");
			get_colors();
		}
		//~ console.timeEnd("update_style");
	}

	function update_iconset(html,html2) {
		//~ console.time("update_iconset");
		var iconset1=geticonset(html2);
		var iconset2=geticonset(html);
		if(iconset1 && iconset2 && $(iconset1).attr("href")!=$(iconset2).attr("href")) {
			$("head").append("<link href='"+$(iconset2).attr("href")+"' rel='stylesheet' type='text/css'></link>");
		}
		//~ console.timeEnd("update_iconset");
	}

	function updatecontent(html) {
		//~ console.time("updatecontent");
		unloadingcontent();
		$(document).scrollTop(0);
		// UPDATE THE LANG AND DIR IF NEEDED
		var temp=$("html");
		var temp2=$("div[type=html]",html);
		if($(temp).attr("lang")!=$(temp2).attr("lang")) {
			$(temp).attr("lang",$(temp2).attr("lang"));
		}
		if($(temp).attr("dir")!=$(temp2).attr("dir")) {
			$(temp).attr("dir",$(temp2).attr("dir"));
			$(".ltr,.rtl").removeClass("ltr rtl").addClass($(temp2).attr("dir"));
		}
		// UPDATE THE TITLE IF NEEDED
		var title2=$("div[type=title]",html);
		if(document.title!=$(title2).text()) {
			document.title=$(title2).text();
		}
		// UPDATE THE NORTH PANEL IF NEEDED
		//~ console.time("updatecontent north fase 0");
		var header=$(".ui-layout-north");
		var header2=$(".ui-layout-north",html);
		if($(header).text()!=$(header2).text()) {
			$(header).replaceWith(header2);
			setTimeout(function() {
				//~ console.time("updatecontent north fase 1");
				make_hovers(header2);
				make_draganddrop(header2);
				//~ console.timeEnd("updatecontent north fase 1");
			},100);
		}
		//~ console.timeEnd("updatecontent north fase 0");
		// CHECK FOR LOGIN AND LOGOUT
		var menu=$(".ui-layout-west");
		var menu2=$(".ui-layout-west",html);
		var saltos_login=(!saltos_islogin(menu) && saltos_islogin(menu2))?1:0;
		var saltos_logout=(saltos_islogin(menu) && !saltos_islogin(menu2))?1:0;
		// IF LOGIN
		if(saltos_login) sync_cookies("start");
		// UPDATE THE MENU IF NEEDED
		//~ console.time("updatecontent west fase 0");
		if($(".menu",menu).text()!=$(".menu",menu2).text()) {
			make_menu(menu2);
			$(menu).replaceWith(menu2);
			setTimeout(function() {
				//~ console.time("updatecontent west fase 1");
				make_hovers(menu2);
				make_draganddrop(menu2);
				//~ console.timeEnd("updatecontent west fase 1");
			},100);
		}
		//~ console.timeEnd("updatecontent west fase 0");
		// IF LOGOUT
		if(saltos_logout) sync_cookies("stop");
		// UPDATE THE CENTER PANE
		//~ console.time("updatecontent center fase 0");
		var screen=$(".ui-layout-center");
		var screen2=$(".ui-layout-center",html);
		unmake_ckeditors(screen);
		make_tabs(screen2);
		make_tables(screen2);
		make_extras(screen2);
		make_selects(screen2)
		$(screen).replaceWith(screen2);
		make_ckeditors(screen2);
		setTimeout(function() {
			//~ console.time("updatecontent center fase 1");
			if(saltos_login || saltos_logout) make_notice();
			var html2=$("html");
			update_style(html,html2);
			update_iconset(html,html2);
			make_hovers(screen2);
			make_draganddrop(screen2);
			make_focus();
			//~ console.timeEnd("updatecontent center fase 1");
		},100);
		//~ console.timeEnd("updatecontent center fase 0");
		//~ console.timeEnd("updatecontent");
	}

	function make_menu(obj) {
		if(typeof(obj)=="undefined") var obj=$("body");
		// CREATE THE MENU USING THE OLD OPENED SECTION STORED IN A COOKIE
		var exists=0;
		$(".menu,.tools",obj).each(function() {
			var name=$(this).attr("id");
			var active=getIntCookie("saltos_ui_menu_"+name)?0:false;
			$(this).accordion({
				collapsible:true,
				heightStyle:"content",
				active:active,
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
			exists=1;
		});
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
	}

	function toggle_menu() {
		var obj=$(".ui-layout-west");
		if($(obj).is(":visible")) {
			$(obj).addClass("none");
			setIntCookie("saltos_ui_menu_closed",1);
		} else {
			$(obj).removeClass("none");
			setIntCookie("saltos_ui_menu_closed",0);
		}
	}

	function make_tabs(obj) {
		//~ console.time("make_tabs");
		if(typeof(obj)=="undefined") var obj=$("body");
		// REPAIR THE DIVS THAT NOT ARE A TABS.
		// THE CODE FIND ALL DIVS AND IF NOT IS A VALID TAB,
		// MOVE ALL THE CONTENT TO THE PREVIOUS DIV THAT IS A VALID TAB
		var oldtab=null;
		$(".sitabs,.notabs",obj).each(function() {
			if($(this).hasClass("sitabs")) {
				oldtab=this;
			} else {
				$(this).removeClass("notabs");
				if(oldtab!=null) $(oldtab).append($(this));
			}
		});
		// FIX A BUG ON THE XSLT THAT REPEAT THE TABID
		var lista=new Array();
		var count=0;
		$(".tabs > ul > li > a",obj).each(function() {
			var href=$(this).attr("href");
			if(!in_array(href,lista)) {
				lista.push(href);
			} else {
				count++;
				href=substr(href,0,6)+sprintf("%06d",intval(substr(href,6))+count);
				$(this).attr("href",href);
			}
		});
		var lista=new Array();
		var count=0;
		$(".tabs div[id^=tabid]",obj).each(function() {
			var id=$(this).attr("id");
			if(!in_array(id,lista)) {
				lista.push(id);
			} else {
				count++;
				id=substr(id,0,5)+sprintf("%06d",intval(substr(id,5))+count);
				$(this).attr("id",id);
			}
		});
		// THIS CODE ADD THE ACCESSKEY FEATURE FOR EACH TAB
		var accesskeys="1234567890";
		var accesskey=0;
		$(".tabs > ul > li > a",obj).each(function() {
			if(accesskey<accesskeys.length) {
				$(this).attr("title","[CTRL] + ["+substr(accesskeys,accesskey,1)+"]");
				$(this).addClass("shortcut_ctrl_"+substr(accesskeys,accesskey,1));
				accesskey++;
			}
		});
		// THIS CODE SEARCH THE TAB USING THE OLD OPENED TAB STORED IN A COOKIE
		// TOO, FIND ALL OBJECTS FROM THE FORM AND IF EXIST THE FOCUSED ATTRIBUTE,
		// SEARCH THE INDEX OF THE TAB THAT CONTAIN THE OBJECT
		var active=0;
		$("[focused=true]:first",obj).each(function() {
			focused=this;
			var thetab=$(this).parent();
			while(thetab) {
				if($(thetab).hasClass("sitabs") && substr($(thetab).attr("id"),0,5)=="tabid") {
					var index=0;
					$("[id^=tabid][class=sitabs]",obj).each(function() {
						if($(this).attr("id")==$(thetab).attr("id")) active=index;
						index++;
					});
					break;
				}
				thetab=$(thetab).parent();
			}
		});
		// TRUE, CREATE THE TABS
		$(".tabs",obj).tabs({
			active:active,
			activate:function(event,ui) {
				$("svg[isplot=true]",ui.newPanel).each(function() {
					var chart=$(this).data("chart");
					chart.update();
				});
			}
		});
		//~ console.timeEnd("make_tabs");
	}

	function make_extras(obj) {
		//~ console.time("make_extras");
		if(typeof(obj)=="undefined") var obj=$("body");
		// CREATE THE DATEPICKERS
		$("input[isdate=true]",obj).each(function() {
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
		$("a[isdate=true]",obj).bind("click",function() {
			if(!is_disabled(this)) $(this).prev().datepicker("show");
		});
		// CREATE THE TIMEPICKERS
		$("input[istime=true]",obj).each(function() {
			$(this).ptTimeSelect({
				onFocusDisplay:false,
				hoursLabel:lang_horas(),
				minutesLabel:lang_minutos(),
				setButtonLabel:lang_buttonaccept()
			});
		});
		$("a[istime=true]",obj).bind("click",function() {
			if(!is_disabled(this)) jQuery.ptTimeSelect.openCntr($(this).prev());
		});
		// PROGRAM THE DATETIME JOIN
		$("input[isdatetime=true]",obj).each(function() {
			var name=$(this).attr("name");
			var full=$("input[name="+name+"]",obj);
			var date=$("input[name="+name+"_date]",obj);
			var time=$("input[name="+name+"_time]",obj);
			$(date).bind("change",function() {
				$(full).val($(date).val()+" "+$(time).val());
				$(full).trigger("change");
			});
			$(time).bind("change",function() {
				$(full).val($(date).val()+" "+$(time).val());
				$(full).trigger("change");
			});
		});
		// CREATE THE COLOR PICKERS
		$("input[iscolor=true]",obj).each(function() {
			$(this).bind("keyup",function() {
				var caja=$("#"+$(this).attr("id")+"_color",obj);
				$(caja).css("background-color",$(this).val());
			});
		});
		$("a[iscolor=true]",obj).each(function() {
			$(this).ColorPicker({
				onBeforeShow:function() {
					var caja=$("#"+substr($(this).attr("id"),0,-6),obj);
					$(this).ColorPickerSetColor(substr($(caja).val(),1));
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
					var caja=$("#"+substr($(el).attr("id"),0,-6),obj);
					$(caja).val("#"+strtoupper(hex));
				}
			});
		});
		$(".colorpicker",obj).css("z-index",9999);
		// PROGRAM INTERGER TYPE CAST
		$("input[isinteger=true]",obj).each(function() {
			$(this).bind("keyup",function() { intval2(this); });
		});
		// PROGRAM FLOAT TYPE CAST
		$("input[isfloat=true]",obj).each(function() {
			$(this).bind("keyup",function() { floatval2(this); });
		});
		// PROGRAM LINKS OF SELECTS
		$("a[islink=true]",obj).bind("click",function() {
			var id=str_replace("nombre","id",$(this).attr("forlink"));
			var val=intval($("#"+id).val());
			var fn=$(this).attr("fnlink");
			if(val) eval(str_replace("ID",val,fn));
		});
		// ADD THE SELECT ALL FEATURE TO LIST
		var master="input.master[type=checkbox]";
		var slave="input.slave[type=checkbox]";
		$(master,obj).attr("title",lang_selectallcheckbox());
		$(slave,obj).attr("title",lang_selectonecheckbox());
		$(master,obj).bind("click",function() {
			$(this).prop("checked",!$(this).prop("checked"));
		}).parent().bind("click",function() {
			var checkbox=$(master,this);
			var value=$(checkbox).prop("checked");
			$(checkbox).prop("checked",!value);
			var table=$(checkbox).parent().parent().parent();
			if(!$(slave,table).length) table=$(table).parent().parent().parent();
			$(slave,table).prop("checked",!value);
			if(!value) $(".tbody",table).addClass("ui-state-highlight");
			if(value) $(".tbody",table).removeClass("ui-state-highlight");
		});
		// PROGRAM CHECK ENTER
		$("input,select",obj).bind("keypress",function(event) {
			if(is_enterkey(event)) {
				if(this.form) {
					for(var i=0,len=this.form.elements.length;i<len;i++) {
						if(this==this.form.elements[i]) break;
					}
					for(var j=0,len=this.form.elements.length;j<len;j++) {
						i=(i+1)%this.form.elements.length;
						if(this.form.elements[i].type!="hidden") break;
					}
					$(this.form.elements[i]).trigger("focus");
					if(this.form.elements[i].type) {
						if(substr(this.form.elements[i].type,0,6)!="select") {
							this.form.elements[i].select();
						}
					}
				}
			}
		});
		// PROGRAM THEAD TOGGLE EFFECT
		$("span[hover='true']",obj).bind("mouseover",function() {
			$(this).toggleClass($(this).attr("toggle"));
		}).bind("mouseout",function() {
			$(this).toggleClass($(this).attr("toggle"));
		});
		// ADD STYLES TO COLUMNIZER BOXES
		$(".tabs",obj).bind("tabsactivate",function(event,ui) {
			if($(".columnizer",this).is(":visible")) {
				if($(".columnizer .column",this).length==0) {
					$(".columnizer",this).columnize();
				} else {
					$(".columnizer",this).trigger("resize");
				}
			}
		});
		// TO CLEAR AMBIGUOUS THINGS
		$(".nowrap.siwrap",obj).removeClass("nowrap siwrap");
		// TRICK FOR STYLING THE INFO NOTIFY
		$(".info",obj).addClass("ui-state-highlight ui-corner-all");
		// TRICK FOR STYLING THE TITLES
		$(".title",obj).addClass("ui-widget-header ui-corner-all");
		// TRICK FOR DISABLE BUTTONS
		$(".disabled",obj).removeClass("disabled").addClass("ui-state-disabled");
		// PROGRAM MENU SELECTS
		$("select[ismenu=true]",obj).bind("change",function() {
			eval($(this).val());
			if($("option:first",this).val()=="") $(this).prop("selectedIndex",0);
		});
		//~ console.timeEnd("make_extras");
	}

	function make_selects(obj) {
		//~ console.time("make_selects");
		if(typeof(obj)=="undefined") var obj=$("body");
		// TUNNING THE SELECTS
		$("select[tunned!=true][multiple!=multiple]",obj).each(function() {
			$(this).attr("tunned","true");
			$(this).wrap("<div/>");
			var div=$(this).parent();
			$(div).css("display","inline-block");
			$(div).css("position","relative");
			var div2=$("<div class='ui-state-default'><span class='ui-icon ui-icon-circle-arrow-s'/></div>");
			$(div).append(div2);
			$(div2).css("border","none");
			$(div2).css("position","absolute");
			$(div2).css("top","4px");
			// SUPPORT FOR LTR AND RTL LANGS
			var dir=$("html").attr("dir");
			var rtl={"ltr":{"right":"right"},"rtl":{"right":"left"}};
			$(div2).css(rtl[dir]["right"],"6px");
		});
		//~ console.timeEnd("make_selects");
	}

	function make_draganddrop(obj) {
		//~ console.time("make_draganddrop");
		if(typeof(obj)=="undefined") var obj=$("body");
		// PROGRAM DRAG AND DROP
		$(".draggable",obj).draggable({
			cursor:"",
			cursorAt:{
				top:0,
				left:-10
			},
			appendTo:"body",
			revert:"invalid",
			delay:500,
			helper:function(event) {
				var row=$("<div class='nowrap'></div>");
				var text=new Array();
				var padre=$(this).parent().parent();
				$("td",padre).each(function() {
					var temp=$(this).text();
					if(temp!="") text.push(temp);
				});
				text=implode(" | ",text);
				text=str_replace(new Array("<",">"),new Array("&lt;","&gt;"),text);
				$(row).append(text);
				$(row).addClass("ui-state-highlight");
				$(row).addClass("ui-corner-all");
				$(row).css("padding","3px 5px");
				return row;
			}
		});
		$(".droppable",obj).droppable({
			tolerance:"pointer",
			drop:function(event,ui) {
				var id_draggable=get_class_id(ui.draggable);
				var id_droppable=get_class_id(this);
				var fn_droppable=get_class_fn(this);
				eval(fn_droppable+"('"+id_draggable+"','"+id_droppable+"')");
			}
		});
		//~ console.timeEnd("make_draganddrop");
	}

	function get_class_key_val(obj,param) {
		var clases=explode(" ",$(obj).attr("class"));
		var total=clases.length;
		var length=strlen(param);
		for(var i=0;i<total;i++) {
			if(substr(clases[i],0,length)==param) {
				return substr(clases[i],length);
			}
		}
		return "";
	}

	function get_class_id(obj) {
		return get_class_key_val(obj,"id_");
	}

	function get_class_fn(obj) {
		return get_class_key_val(obj,"fn_");
	}

	function get_class_hash(obj) {
		return get_class_key_val(obj,"hash_");
	}

	function make_hovers(obj) {
		//~ console.time("make_hovers");
		if(typeof(obj)=="undefined") var obj=$("body");
		// ADD HOVER, FOCUS AND BLUR EVENTS
		var inputs="a.ui-state-default,input.ui-state-default,textarea.ui-state-default,select.ui-state-default";
		$(inputs,obj).bind("mouseover",function() {
			$(this).addClass("ui-state-hover");
		}).bind("mouseout",function() {
			$(this).removeClass("ui-state-hover");
		}).bind("focus",function() {
			$(this).addClass("ui-state-focus");
		}).bind("blur",function() {
			$(this).removeClass("ui-state-focus");
		});
		//~ console.timeEnd("make_hovers");
	}

	function make_ckeditors(obj) {
		//~ console.time("make_ckeditors");
		if(typeof(obj)=="undefined") var obj=$("body");
		// AUTO-GROWING TEXTAREA
		$("textarea[ckeditor!=true][codemirror!=true]",obj).autogrow();
		// AUTO-GROWING IFRAMES
		$("iframe",obj).each(function() {
			var iframe="#"+$(this).attr("id");
			var interval=setInterval(function() {
				var iframe2=$(iframe,obj);
				if(!$(iframe2).length) {
					clearInterval(interval);
				} else if($(iframe2).is(":visible")) {
					if(typeof($(iframe2).attr("isloaded"))=="undefined") {
						$(iframe2).attr("isloaded","false");
						$(iframe2).load(function() {
							$(this).attr("isloaded","true");
						}).each(function() {
							var iframe3=this.contentWindow.location;
							var url=$(this).attr("url");
							if(url) iframe3.replace(url);
							if(!url) clearInterval(interval);
						});
					} else if($(iframe2).attr("isloaded")=="true") {
						clearInterval(interval);
						if(security_iframe(iframe2)) {
							var minheight=$(iframe2).height();
							var newheight=$(iframe2).contents().height();
							if(newheight>minheight) $(iframe2).height(newheight);
							$(iframe2).each(function() {
								var iframe3=this.contentWindow.document;
								$(iframe3).bind("contextmenu",function(e) { return false; });
								$(iframe3).bind("keydown",function(e) { $(document).trigger(e); });
							});
						}
					}
				}
			},100);
		});
		// CREATE THE CKEDITORS
		$("textarea[ckeditor=true]",obj).ckeditor({
			skin:"kama",
			extraPlugins:"",
			removePlugins:"",
			enterMode:CKEDITOR.ENTER_BR,
			shiftEnterMode:CKEDITOR.ENTER_BR,
			forcePasteAsPlainText:true,
			toolbar:[["Bold","Italic","Underline","Strike"],["NumberedList","BulletedList","-","Outdent","Indent"],["Link","Unlink"],["TextColor","BGColor"],["Undo","Redo"],["Maximize","Source","Syntaxhighlight"],["About"]],
			language:lang_default(),
			uiColor:get_colors("ui-state-default","background-color"),
			autoGrow_onStartup:true,
			disableNativeSpellChecker:false
		},function() {
			$("#"+$(this).attr("name")).next().addClass("ui-state-default ui-corner-all");
		});
		// CREATE THE CODE MIRROR
		$("textarea[codemirror=true]",obj).each(function() {
			var width=$(this).width();
			var height=$(this).height();
			var classes=$(this).attr("class");
			var cm=CodeMirror.fromTextArea(this,{
				lineNumbers:true
			});
			$(this).data("cm",cm);
			cm.setSize(width,height);
			$(this).next().addClass(classes);
			cm.on("change",function() {
				cm.save();
			});
		});
		// CREATE THE PLOTS
		$("svg[isplot=true]",obj).each(function() {
			var obj=this;
			// GET DATA
			var width=intval($(this).width());
			var height=intval($(this).height());
			var title=$(this).attr("ititle");
			var legend=explode("|",$(this).attr("legend"));
			var vars=intval($(this).attr("vars"));
			var colors=explode("|",$(this).attr("colors"));
			var graph=$(this).attr("graph");
			var ticks=explode("|",$(this).attr("ticks"));
			var posx=explode("|",$(this).attr("posx"));
			var datas=[];
			for(var i=1;i<=16;i++) datas[i]=explode("|",$(this).attr("data"+i));
			// REPAIR DATA
			if(legend[count(legend)-1]=="") array_pop(legend);
			if(colors[count(colors)-1]=="") array_pop(colors);
			if(ticks[count(ticks)-1]=="") array_pop(ticks);
			if(posx[count(posx)-1]=="") array_pop(posx);
			for(var i=1;i<=16;i++) if(datas[i][count(datas[i])-1]=="") array_pop(datas[i]);
			// PREPARE DATA
			var data=[];
			for(var i=1;i<=vars;i++) {
				var j=i-1;
				data[j]=[];
				data[j]["key"]=title;
				if(vars>1) data[j]["key"]=legend[j];
				data[j]["values"]=[];
				for(var k=0;k<count(ticks);k++) {
					data[j]["values"][k]=[];
					data[j]["values"][k]["x"]=ticks[k];
					data[j]["values"][k]["y"]=datas[i][k];
				}
			}
			// DO THE PLOT
			nv.addGraph(function() {
				var chart;
				// FOR EACH SUPPORTED GRAPH AND VARS
				if(graph=="bars" && vars==1) {
					chart=nv.models.discreteBarChart()
						.noData(lang_withoutinfo())
						.tooltipContent(function(key,x,y,e,graph) { return "<span class='ui-state-highlight ui-corner-all'>"+x+": "+y+"</span>";})
						.color(d3.scale.category20().range())
						.showValues(true);
				}
				if(graph=="pie" && vars==1) {
					chart=nv.models.pieChart()
						.noData(lang_withoutinfo())
						.tooltipContent(function(key,x,y,e,graph) { return "<span class='ui-state-highlight ui-corner-all'>"+key+": "+x+"</span>";})
						.color(d3.scale.category20().range());
					data=data[0]["values"];
				}
				if(graph=="bars" && vars>=2) {
					chart=nv.models.multiBarChart()
						.noData(lang_withoutinfo())
						.tooltipContent(function(key,x,y,e,graph) { return "<span class='ui-state-highlight ui-corner-all'>"+x+": "+y+"</span>";})
						.color(d3.scale.category20().range())
						.margin({bottom:75})
						.rotateLabels(45)
						.groupSpacing(0.1)
						.showControls(false);
				}
				// CONTINUE
				d3.select(obj)
					.datum(data)
					.call(chart);
				$(obj).data("chart",chart);
				//~ nv.utils.windowResize(chart.update);
				return chart;
			});
		});
		// PROGRAM AUTOCOMPLETE FIELDS
		$("input[isautocomplete=true]",obj).each(function() {
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
					var data="action=ajax&format=json&query="+query+"&term="+term;
					if(typeof($("#"+prefix+filter).val())!="undefined") data+="&filter="+$("#"+prefix+filter).val();
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
					this.value=ui.item.label;
					if(typeof(fn)!="undefined") eval(fn);
					return false;
				}
			});
		});
		//~ console.timeEnd("make_ckeditors");
	}

	function unmake_ckeditors(obj) {
		//~ console.time("unmake_ckeditors");
		if(typeof(obj)=="undefined") var obj=$("body");
		// REMOVE THE CKEDITORS (IMPORTANT THING!!!)
		$("textarea[ckeditor=true]",obj).each(function() {
			var name=$(this).attr("name");
			if(CKEDITOR.instances[name]) CKEDITOR.instances[name].destroy();
		});
		// REMOVE THE PLOTS (IMPORTANT THING!!!)
		$("svg[isplot=true]",obj).each(function() {
			d3.select(this).remove();
		});
		while(nv.graphs.length>0) nv.graphs.pop();
		//~ console.timeEnd("unmake_ckeditors");
	}

	function make_tooltips() {
		//~ console.time("make_tooltips");
		$(document).tooltip({
			items:"[title][title!=''],[title2][title2!='']",
			show:{ effect:"none" },
			hide:{ effect:"none" },
			tooltipClass:"ui-state-highlight",
			track:true,
			open:function(event,ui) {
				ui.tooltip.css("max-width",$(window).width()/2);
			},
			content:function() {
				// GET THE TITLE VALUE
				var title=trim($(this).attr("title"));
				if(title) {
					// CHECK IF TITLE IS THE SAME THAT THE OBJECT TEXT
					if(title==$(this).text()) {
						title="";
					}
					// FIX SOME ISSUES
					if(strpos(title,"<")!==false || strpos(title,">")!==false) {
						title=str_replace(new Array("<",">"),new Array("&lt;","&gt;"),title);
					}
					// MOVE DATA FROM TITLE TO TITLE2
					$(this).attr("title","");
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
		//~ console.timeEnd("make_tooltips");
	}

	var focused=null;

	function make_focus() {
		//~ console.time("make_focus");
		// FOCUS THE OBJECT WITH FOCUSED ATTRIBUTE
		if(focused) $(focused).trigger("focus");
		focused=null;
		//~ console.timeEnd("make_focus");
	}

	function make_tables(obj) {
		//~ console.time("make_tables");
		if(typeof(obj)=="undefined") var obj=$("body");
		// SUPPORT FOR LTR AND RTL LANGS
		var dir=$("html").attr("dir");
		var rtl={
			"ltr":{"ui-corner-tl":"ui-corner-tl","ui-corner-tr":"ui-corner-tr","ui-corner-bl":"ui-corner-bl","ui-corner-br":"ui-corner-br","noright":"noright"},
			"rtl":{"ui-corner-tl":"ui-corner-tr","ui-corner-tr":"ui-corner-tl","ui-corner-bl":"ui-corner-br","ui-corner-br":"ui-corner-bl","noright":"noleft"}
		};
		// GET ALL TABLES OF THE TABLA CLASS
		$(".tabla",obj).each(function() {
			if($(".thead",this).length>0) {
				// FIXS FOR POSIBLE NEXT RECALLS
				$("td",this).removeClass("ui-corner-tl ui-corner-tr ui-corner-bl ui-corner-br ui-widget-header ui-widget-content ui-state-default ui-state-highlight");
				$("[hasbind=true]",this).unbind();
				// STYLING THE THEAD AND NODATA
				$(".thead",this).addClass("ui-widget-header");
				$(".nodata",this).addClass("ui-widget-content");
				// SOME VARIABLES
				var trs=$("tr",this);
				var tdshead=null;
				var tdsbody=null;
				var trimpar=1;
				$(trs).each(function() {
					if($(this).hasClass("none")) return;
					// MORE VARIABLES
					var numhead=$(".thead",this).length;
					var numbody=$(".tbody",this).length;
					var numdata=$(".nodata",this).length;
					var numcell=$(".cell",this).length;
					// STYLING THE ROUNDED CORNERS AND BORDERS OF THE CELLS
					if(tdshead==null && numhead>0) {
						tdshead=this;
						tdsbody=this;
						var tds=$("td",this);
						var total=$(tds).length;
						var count=1;
						$(tds).each(function() {
							if(count<total) $(this).addClass(rtl[dir]["noright"]);
							if(count==1) $(this).removeClass(rtl[dir]["ui-corner-bl"]);
							if(count==total) $(this).removeClass(rtl[dir]["ui-corner-br"]);
							count++;
						});
					} else if(tdshead!=null && numhead+numbody+numdata>0) {
						tdsbody=this;
						var tds=$("td",this);
						var total=$(tds).length;
						var count=1;
						$(tds).each(function() {
							if(count<total) $(this).addClass("notop").addClass(rtl[dir]["noright"]);
							if(count==total) $(this).addClass("notop")
							if(count==1) $(this).removeClass(rtl[dir]["ui-corner-bl"]);
							if(count==total) $(this).removeClass(rtl[dir]["ui-corner-br"]);
							count++;
						});
					} else if(tdshead!=null) {
						var tds=$("td",tdshead);
						var total=$(tds).length;
						var count=1;
						$(tds).each(function() {
							if(count==1) $(this).addClass(rtl[dir]["ui-corner-tl"]);
							if(count==total) $(this).addClass(rtl[dir]["ui-corner-tr"]);
							count++;
						});
						tdshead=null;
						var tds=$("td",tdsbody);
						var total=$(tds).length;
						var count=1;
						$(tds).each(function() {
							if(count==1) $(this).addClass(rtl[dir]["ui-corner-bl"]);
							if(count==total) $(this).addClass(rtl[dir]["ui-corner-br"]);
							count++;
						});
						tdsbody=null;
					}
					// ADD THE TIMPAR CLASS TO THE CELLS THAT CONTAIN THE TBODY BY STEPS OF 2
					if(numbody>0) {
						var clase=trimpar?"ui-widget-content":"ui-state-default";
						$(".tbody",this).addClass(clase);
						trimpar=(trimpar+1)%2;
					} else if(numhead>0) {
						trimpar=1;
					}
					// PROGRAM THE HIGHLIGHT EFFECT FOR EACH ROW
					if(numbody>0 && numcell==0) {
						var slave="input.slave[type=checkbox]";
						$(this).bind("mouseover",function() {
							var value=$(slave,this).prop("checked");
							if(!value) $(".tbody",this).addClass("ui-state-highlight");
						}).bind("mouseout",function() {
							var value=$(slave,this).prop("checked");
							if(!value) $(".tbody",this).removeClass("ui-state-highlight");
						}).bind("click",function() {
							var checkbox=$(slave,this);
							var value=$(checkbox).prop("checked");
							$(checkbox).prop("checked",!value);
							if(!value) $(".tbody",this).addClass("ui-state-highlight");
							if(value) $(".tbody",this).removeClass("ui-state-highlight");
						});
						$(slave,this).bind("click",function() {
							$(this).prop("checked",!$(this).prop("checked"));
						});
						$("a",this).bind("click",function() {
							var checkbox=$(slave,$(this).parent().parent());
							var value=$(checkbox).prop("checked");
							if(value) $(checkbox).prop("checked",!value);
						});
					}
				});
				if(tdshead!=null) {
					var tds=$("td",tdshead);
					var total=$(tds).length;
					var count=1;
					$(tds).each(function() {
						if(count==1) $(this).addClass(rtl[dir]["ui-corner-tl"]);
						if(count==total) $(this).addClass(rtl[dir]["ui-corner-tr"]);
						count++;
					});
					var tds=$("td",tdsbody);
					var total=$(tds).length;
					var count=1;
					$(tds).each(function() {
						if(count==1) $(this).addClass(rtl[dir]["ui-corner-bl"]);
						if(count==total) $(this).addClass(rtl[dir]["ui-corner-br"]);
						count++;
					});
				}
				// MAKE CALCS OF THE TABLE CELLS
				var last=$("tr.math",this);
				$("td",last).each(function() {
					var index=$(this).index();
					var value=$(this).text();
					if(in_array(value,new Array("=sum()","=count()","=avg()"))) {
						var sum=0;
						var count=0;
						$("td:eq("+index+")",trs).each(function() {
							var value=$(this).text();
							if(!isNaN(value)) {
								sum+=floatval(value);
								count++;
							}
						});
						if(value=="=sum()") {
							$(this).html(round(sum,2));
						} else if(value=="=count()") {
							$(this).html(count);
						} else if(value=="=avg()") {
							var average=(count>0)?sum/count:0;
							$(this).html(round(average,2));
						}
					}
				});
			}
		});
		//~ console.timeEnd("make_tables");
	}

	function make_contextmenu() {
		//~ console.time("make_contextmenu");
		$("body").append("<ul id='contextMenu'></ul>");
		$(document).bind("keydown",function(event) {
			if(is_escapekey(event)) hide_contextmenu();
		}).bind("click",function(event) {
			if(event.button!=2) hide_contextmenu();
		}).bind("contextmenu",function(event) {
			var obj=$("#contextMenu");
			$("li",obj).remove();
			var parent=$(event.target).parent();
			var trs=$("tr",parent);
			var tds=$("td.actions",parent);
			if($(trs).length || !$(tds).length) tds=$(".contextmenu");
			var hashes=new Array();
			$(tds).each(function() {
				var onclick=$(this).attr("onclick");
				if(!onclick) onclick=$("a",this).attr("onclick");
				var extra1=$("span",this).attr("class");
				extra1=str_replace("ui-state-disabled","",extra1);
				var texto=$(this).text();
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
				var html="<li class='"+extra2+"'><a href='javascript:void(0)'><span class='"+extra1+"'></span>&nbsp;"+texto+"</a></li>";
				var hash=md5(html);
				if(!in_array(hash,hashes)) {
					$(obj).append(html);
					$("li:last a",obj).bind("click",function() { eval(onclick); });
					hashes.push(hash);
				}
			});
			$(obj).css("position","absolute");
			$(obj).css("left",event.pageX);
			$(obj).css("top",event.pageY);
			$(obj).show();
			if(!$(obj).hasClass("ui-menu")) {
				$(obj).menu();
			} else {
				$(obj).menu("refresh");
			}
			return false;
		});
		//~ console.timeEnd("make_contextmenu");
	}

	function hide_contextmenu() {
		$("#contextMenu").hide();
	}

	var cache_colors=new Object();

	function get_colors(clase,param) {
		if(typeof(clase)=="undefined" && typeof(param)=="undefined") {
			for(var hash in cache_colors) delete cache_colors[hash];
			return;
		}
		hash=md5(serialize(new Array(clase,param)));
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

	function make_shortcuts() {
		var codes={"backspace":8, "tab":9, "enter":13, "pauseBreak":19, "capsLock":20, "escape":27, "space":32, "pageUp":33, "pageDown":34, "end":35, "home":36, "leftArrow":37, "upArrow":38, "rightArrow":39, "downArrow":40, "insert":45, "delete":46, "0":48, "1":49, "2":50, "3":51, "4":52, "5":53, "6":54, "7":55, "8":56, "9":57, "a":65, "b":66, "c":67, "d":68, "e":69, "f":70, "g":71, "h":72, "i":73, "j":74, "k":75, "l":76, "m":77, "n":78, "o":79, "p":80, "q":81, "r":82, "s":83, "t":84, "u":85, "v":86, "w":87, "x":88, "y":89, "z":90, "leftWindowKey":91, "rightWindowKey":92, "selectKey":93, "numpad0":96, "numpad1":97, "numpad2":98, "numpad3":99, "numpad4":100, "numpad5":101, "numpad6":102, "numpad7":103, "numpad8":104, "numpad9":105, "multiply":106, "add":107, "subtract":109, "decimalPoint":110, "divide":111, "f1":112, "f2":113, "f3":114, "f4":115, "f5":116, "f6":117, "f7":118, "f8":119, "f9":120, "f10":121, "f11":122, "f12":123, "numLock":144, "scrollLock":145, "semiColon":186, "equalSign":187, "comma":188, "dash":189, "period":190, "forwardSlash":191, "graveAccent":192, "openBracket":219, "backSlash":220, "closeBraket":221, "singleQuote":222};
		$(document).bind("keydown",function(e) {
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
					if(useAlt && e.altKey) count++;
					if(!useAlt && !e.altKey) count++;
					if(useCtrl && e.ctrlKey) count++;
					if(!useCtrl && !e.ctrlKey) count++;
					if(useShift && e.shiftKey) count++;
					if(!useShift && !e.shiftKey) count++;
					if(key==e.keyCode) count++;
					if(count==4) {
						if($(this).is("a,tr,td")) $(this).trigger("click");
						if($(this).is("input,select,textarea")) $(this).focus();
						exists=true;
					}
				});
				if(exists) return false;
			}
		});
	}

	var jqxhr=null;

	function make_abort() {
		$(document).bind("keydown",function(event) {
			if(is_escapekey(event) && jqxhr) {
				jqxhr.abort();
				jqxhr=null;
			}
		});
	}

	function saltos_islogin(obj) {
		if(typeof(obj)=="undefined") var obj=$(".ui-layout-west");
		var islogin=($(obj).text()!="")?1:0;
		return islogin;
	}

	// TO PREVENT JQUERY THE ADD _=[TIMESTAMP] FEATURE
	jQuery.ajaxSetup({ cache:true });

	// DEFINE SOME DEFAULTS THAT CAN NOT BE DEFINED IN RUNTIME
	$.jGrowl.defaults.closer=false;
	$.jGrowl.defaults.position="bottom-right";

	// WHEN DOCUMENT IS READY
	$(function() {
		//~ console.time("document_ready fase 0");
		loadingcontent();
		setTimeout(function() {
			//~ console.time("document_ready fase 1");
			init_history();
			make_notice();
			make_dialog();
			make_contextmenu();
			make_shortcuts();
			make_abort();
			make_tooltips();
			var header=$(".ui-layout-north");
			setTimeout(function() {
				//~ console.time("document_ready fase 2 north");
				make_hovers(header);
				make_draganddrop(header);
				//~ console.timeEnd("document_ready fase 2 north");
			},100);
			var menu=$(".ui-layout-west");
			if(saltos_islogin(menu)) sync_cookies("start");
			make_menu(menu);
			setTimeout(function() {
				//~ console.time("document_ready fase 2 west");
				make_hovers(menu);
				make_draganddrop(menu);
				//~ console.timeEnd("document_ready fase 2 west");
			},100);
			var screen=$(".ui-layout-center");
			make_tabs(screen);
			make_tables(screen);
			make_extras(screen);
			make_selects(screen);
			make_ckeditors(screen);
			$("body > *").removeClass("none");
			setTimeout(function() {
				//~ console.time("document_ready fase 2 center");
				make_hovers(screen);
				make_draganddrop(screen);
				make_focus();
				//~ console.timeEnd("document_ready fase 2 center");
			},100);
			unloadingcontent();
			//~ console.timeEnd("document_ready fase 1");
		},100);
		//~ console.timeEnd("document_ready fase 0");
	});

}
