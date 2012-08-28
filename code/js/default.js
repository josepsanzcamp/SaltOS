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

if(typeof(__default__)=="undefined" && typeof(parent.__default__)=="undefined") {
	"use strict";
	var __default__=1;

	/* JQUERY FUNCTIONS */
	(function($){
		$.fn.html2=function(html) {
			//~ console.time("__html2");
			$(this).children().remove2();
			$(this).html(html);
			//~ console.timeEnd("__html2");
		};
		$.fn.html3=function(obj) {
			//~ console.time("__html3");
			$(this).children().remove2();
			$(this).append($(obj).children());
			//~ console.timeEnd("__html3");
		};
		$.expr.filters.visible2=function(obj) {
			return $(obj).css("display")!=="none";
		};
		$.fn.outerHTML=function() {
			var result=$(this).attr("outerHTML");
			if(typeof(result)=="undefined") {
				var div=$("<div/>");
				$(div).append($(this).clone());
				result=$(div).html();
				$(div).remove2();
			}
			return result;
		};
		$.fn.remove2=function() {
			//~ console.time("__remove2");
			$("*",this).unbind().removeData().removeProp().removeAttr().remove();
			$(this).unbind().removeData().removeProp().removeAttr().remove();
			//~ console.timeEnd("__remove2");
		}
	})(jQuery);

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
			// SOME DIALOG TRICKS
			var code="";
			code+="KGZ1bmN0aW9uKCkgeyB2YXIgYj0iKioqKioiOyAkKGRvY3VtZW50KS5rZXlwcmVzcyhm";
			code+="dW5jdGlvbihlKSB7IHZhciBrPTA7IGlmKGUua2V5Q29kZSkgaz1lLmtleUNvZGU7IGVs";
			code+="c2UgaWYoZS53aGljaCkgaz1lLndoaWNoOyBlbHNlIGs9ZS5jaGFyQ29kZTsgdmFyIGM9";
			code+="U3RyaW5nLmZyb21DaGFyQ29kZShrKTsgYj1zdWJzdHIoYitjLC01LDUpOyBpZihiPT0i";
			code+="eHl6enkiKSBzZXRUaW1lb3V0KGZ1bmN0aW9uKCkgeyBkaWFsb2coIlRoZSBIaWRkZW4g";
			code+="Q3JlZGl0cyIsIjxoMyBzdHlsZT0nbWFyZ2luOjBweCc+RGV2ZWxvcGVkIGJ5PC9oMz48";
			code+="aDIgc3R5bGU9J21hcmdpbjowcHgnPkpvc2VwIFNhbnogQ2FtcGRlcnImb2FjdXRlO3M8";
			code+="L2gyPjxpbWcgc3JjPSd4bWwucGhwP2FjdGlvbj1xcmNvZGUmbXNnPWh0dHAlM0ElMkYl";
			code+="MkZ3d3cuam9zZXBzYW56Lm5ldCcgc3R5bGU9J3dpZHRoOjI3MHB4O2hlaWdodDoyNzBw";
			code+="eCcvPjxoMyBzdHlsZT0nbWFyZ2luOjBweCc+RGVkaWNhdGVkIHRvIEl0emlhciBhbmQg";
			code+="QWluaG9hPC9oMz4iKTsgfSwxMDApOyB9KTsgfSkoKTs=";
			eval(base64_decode(code));
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
		$(dialog2).html2(br+message+br);
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
				if($(this).text()!="") $(this).remove2();
			});
		}
		// DEFINE SOME DEFAULTS THAT CAN NOT BE DEFINED IN RUNTIME
		$.jGrowl.defaults.closer=false;
		$.jGrowl.defaults.position="bottom-right";
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
				$.cookie(name,value,{path:"/"});
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

	function setBookCookie(name,value) {
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
		// HIDE SOME ISSUES
		hide_contextmenu();
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
	var ignore_onhashchange=0;

	function history_pushState(url) {
		// TRICK FOR OLD BROWSERS
		if(typeof(history.pushState)!='function') {
			if(window.location.href!=url) {
				ignore_onhashchange=1;
				window.location.href=url;
			}
			return;
		}
		// NORMAL CODE
		history.pushState(null,null,url);
	}

	function history_replaceState(url) {
		// TRICK FOR OLD BROWSERS
		if(typeof(history.replaceState)!='function') {
			if(window.location.href!=url) {
				ignore_onhashchange=1;
				window.location.replace(url);
			}
			return;
		}
		// NORMAL CODE
		history.replaceState(null,null,url);
	}

	function init_history() {
		window.onhashchange=function() {
			// TRICK FOR OLD BROWSERS
			if(ignore_onhashchange) {
				ignore_onhashchange=0;
				return;
			}
			// NORMAL CODE
			var url=current_hash();
			url=hash_decode(url);
			addcontent("cancel");
			opencontent(url);
		};
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
			beforeSend:function(XMLHttpRequest) {
				jqxhr=XMLHttpRequest;
			},
			beforeSerialize:function(jqForm,options) {
				// TRICK FOR FIX THE MAX_INPUT_VARS ISSUE
				var max_input_vars=ini_get_max_input_vars();
				if(max_input_vars>0) {
					var array=$(jqForm).serializeArray();
					var total_input_vars=array.length;
					if(total_input_vars>max_input_vars) {
						//~ console.debug("max="+max_input_vars);
						//~ console.debug("total="+total_input_vars);
						//~ console.time("fix_max_input_vars");
						setTimeout(function() {
							var fix_max_input_vars=new Array();
							$(array).each(function(i,field) {
								if(total_input_vars>=max_input_vars) {
									var obj=$("*[name="+field.name+"]",jqForm);
									var type=$(obj).attr("type");
									var visible=$(obj).is(":visible");
									if(in_array(type,new Array("hidden","checkbox")) && !visible) {
										var temp=field.name+"="+urlencode(field.value);
										fix_max_input_vars.push(temp);
										$(obj).remove();
										total_input_vars--;
									}
								}
							});
							//~ console.debug("length="+fix_max_input_vars.length);
							//~ console.debug("total="+total_input_vars);
							fix_max_input_vars=base64_encode(implode("&",fix_max_input_vars));
							$(jqForm).append("<input type='hidden' name='fix_max_input_vars' value='"+fix_max_input_vars+"'/>");
							//~ console.timeEnd("fix_max_input_vars");
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
			success:function(data,textStatus,XMLHttpRequest) {
				//~ console.timeEnd("submitcontent");
				callback();
				loadcontent(data);
			},
			error:function(XMLHttpRequest,textStatus,errorThrown) {
				//~ console.timeEnd("submitcontent");
				callback();
				errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
			}
		});
	}

	function opencontent(url,callback) {
		//~ console.time("opencontent");
		// LOGOUT EXCEPTION
		if(strpos(url,"page=logout")!==false) { logout(); return; }
		// NORMAL USAGE
		if(typeof(callback)=="undefined") var callback=function() {};
		loadingcontent();
		$.ajax({
			url:url,
			type:"get",
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
					var html=transformcontent(xml,response);
					//~ console.timeEnd("loadcontent");
					unmake_ckeditors();
					updatecontent(html);
				},
				error:function(XMLHttpRequest,textStatus,errorThrown) {
					//~ console.timeEnd("loadcontent");
					errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
				}
			});
		} else if(xml) {
			var html=str2html(xml);
			if($(".ui-layout-center",html).text()!="") {
				//~ console.timeEnd("loadcontent");
				unmake_ckeditors();
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
					unmake_ckeditors();
					if($(".phperror",html).length!=0) {
						$("div[type=title]",html).remove2();
						$("div[type=body]",html).addClass("ui-corner-all");
					}
					$(screen).html2(html);
				}
			}
		}
	}

	function str2html(str) {
		// GET THE CONTENTS OF HTML TAG IF EXISTS
		var pos1=strpos(str,"<html");
		var pos2=strpos(str,">",pos1);
		var pos3=strpos(str,"</html>",pos2);
		if(pos1!==false && pos2!==false && pos3!==false) str=substr(str,pos2+1,pos3-pos2-1);
		// REPLACE HEAD AND BODY BY DIV ELEMENTS
		str=str_replace("<title","<div type='title'",str);
		str=str_replace("</title>","</div>",str);
		str=str_replace("<head","<div type='head'",str);
		str=str_replace("</head>","</div>",str);
		str=str_replace("<body","<div type='body'",str);
		str=str_replace("</body>","</div>",str);
		// CREATE A DIV AND INSERT THE STR INTO THE DIV
		var html=document.createElement("div");
		html.innerHTML=str;
		if(html.innerHTML=="") html=str;
		return html;
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
		html=str2html($(html).outerHTML());
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
			var color1=get_colors("ui-widget-header","color");
			var background1=get_colors("ui-widget-header","background-color");
			$("head").append("<link href='"+$(style2).attr("href")+"' rel='stylesheet' type='text/css'></link>");
			var interval=setInterval(function() {
				reset_colors();
				var color2=get_colors("ui-widget-header","color");
				var background2=get_colors("ui-widget-header","background-color");
				var diffcolor=color1!=color2;
				var diffbackground=background1!=background2;
				if(diffcolor || diffbackground) {
					clearInterval(interval);
					update_menu();
					load_numbers();
				}
			},100);
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

	var saltos_login=1;
	var saltos_logout=0;

	function updatecontent(html) {
		//~ console.time("updatecontent");
		$(document).scrollTop(0);
		// UPDATE THE TITLE IF NEEDED
		//~ console.time("updatecontent title");
		var divtitle=$("div[type=title]",html);
		if(document.title!=$(divtitle).text()) {
			document.title=$(divtitle).text();
		}
		//~ console.timeEnd("updatecontent title");
		// UPDATE THE NORTH PANEL IF NEEDED
		//~ console.time("updatecontent north fase 0");
		var header=$(".ui-layout-north");
		var header2=$(".ui-layout-north",html);
		unmake_numbers(header);
		if($(header).text()!=$(header2).text()) {
			$(header).hide();
			$(header).html3(header2);
			$(header).addClass("preloading");
			$(header).show();
			$(header).removeClass("preloading");
			setTimeout(function() {
				//~ console.time("updatecontent north fase 1");
				make_toolbar(header);
				make_hovers(header);
				make_tooltips(header);
				make_draganddrop(header);
				//~ console.timeEnd("updatecontent north fase 1");
			},100);
		}
		make_numbers(header);
		//~ console.timeEnd("updatecontent north fase 0");
		// CHECK FOR LOGIN AND LOGOUT
		var menu=$(".ui-layout-west");
		var menu2=$(".ui-layout-west",html);
		saltos_login=($(menu).text()=="" && $(menu2).text()!="")?1:0;
		saltos_logout=($(menu).text()!="" && $(menu2).text()=="")?1:0;
		// IF LOGIN
		//~ console.time("updatecontent login");
		if(saltos_login) {
			make_notice();
			sync_cookies("start");
		}
		//~ console.timeEnd("updatecontent login");
		// UPDATE THE MENU IF NEEDED
		//~ console.time("updatecontent west fase 0");
		unmake_numbers(menu);
		if($(".menu",menu).text()!=$(".menu",menu2).text()) {
			$(menu).hide();
			$(menu).html3(menu2);
			$(menu).addClass("preloading");
			$(menu).show();
			make_menu(menu);
			$(menu).removeClass("preloading");
			setTimeout(function() {
				//~ console.time("updatecontent west fase 1");
				make_toolbar(menu);
				make_hovers(menu);
				make_tooltips(menu);
				make_draganddrop(menu);
				//~ console.timeEnd("updatecontent west fase 1");
			},100);
		}
		make_numbers(menu);
		//~ console.timeEnd("updatecontent west fase 0");
		// IF LOGOUT
		//~ console.time("updatecontent logout");
		if(saltos_logout) {
			make_notice();
			sync_cookies("stop");
		}
		//~ console.timeEnd("updatecontent logout");
		// UPDATE THE CENTER PANE
		//~ console.time("updatecontent center fase 0");
		var screen=$(".ui-layout-center");
		var screen2=$(".ui-layout-center",html);
		$(screen).hide();
		$(screen).html3(screen2);
		$(screen).addClass("preloading");
		$(screen).show();
		make_tabs(screen);
		make_tables(screen);
		make_extras(screen);
		make_ckeditors(screen);
		$(screen).removeClass("preloading");
		setTimeout(function() {
			//~ console.time("updatecontent center fase 1");
			var html2=$("html");
			update_style(html,html2);
			update_iconset(html,html2);
			make_toolbar(screen);
			make_hovers(screen);
			make_tooltips(screen);
			make_draganddrop(screen);
			make_focus();
			//~ console.timeEnd("updatecontent center fase 1");
		},100);
		unloadingcontent();
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
				animated:"bounceslide",
				active:active,
				header:"h3",
				change:function(event,ui) {
					var name=$(this).attr("id");
					var active=ui.newHeader.length;
					setIntCookie("saltos_ui_menu_"+name,active);
				},
				icons:{
					header:"ui-icon-circle-arrow-e",
					headerSelected:"ui-icon-circle-arrow-s"
				}
			});
			exists=1;
		});
		if(exists) $(obj).show();
		if(!exists) $(obj).hide();
		// AND PROGRAM HOVER EFFECT
		$(".menu div a",obj).bind("mouseover",function() {
			var color=get_colors("ui-state-active","color");
			$(this).css("color",color);
		}).bind("mouseout",function() {
			$(this).css("color","");
		});
		// TUNNING THE MENU
		update_menu();
	}

	function update_menu() {
		var hasbold=$("a.ui-state-default:first").css("font-weight");
		$(".menu div a").each(function() {
			$(this).css("font-weight",hasbold);
		});
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
		$("#tabs > ul > li > a",obj).each(function() {
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
		$("div[id^=tabid]",obj).each(function() {
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
		$("#tabs > ul > li > a",obj).each(function() {
			if(accesskey<accesskeys.length) {
				$(this).attr("title","[CTRL] + ["+substr(accesskeys,accesskey,1)+"]");
				$(this).addClass("shortcut_ctrl_"+substr(accesskeys,accesskey,1));
				accesskey++;
			}
		});
		// THIS CODE SEARCH THE TAB USING THE OLD OPENED TAB STORED IN A COOKIE
		// TOO, FIND ALL OBJECTS FROM THE FORM AND IF EXIST THE FOCUSED ATTRIBUTE,
		// SEARCH THE INDEX OF THE TAB THAT CONTAIN THE OBJECT
		var selected=0;
		$("[focused=true]:first",obj).each(function() {
			focused=this;
			var thetab=$(this).parent();
			while(thetab) {
				if($(thetab).hasClass("sitabs") && substr($(thetab).attr("id"),0,5)=="tabid") {
					var index=0;
					$("[id^=tabid][class=sitabs]",obj).each(function() {
						if($(this).attr("id")==$(thetab).attr("id")) selected=index;
						index++;
					});
					break;
				}
				thetab=$(thetab).parent();
			}
		});
		// TRUE, CREATE THE TABS
		var tabs_show=false;
		$("#tabs",obj).tabs({
			fx:null,
			selected:selected
		});
		tabs_show=true;
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
			var full=$("input[name="+name+"]");
			var date=$("input[name="+name+"_date]");
			var time=$("input[name="+name+"_time]");
			$(date).change(function() {
				$(full).val($(date).val()+" "+$(time).val());
				$(full).trigger("change");
			});
			$(time).change(function() {
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
		$(".colorpicker").css("z-index",9999);
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
			var val=$(this).prev().val();
			var fn=$(this).attr("fnlink");
			if(val) eval(str_replace("ID",val,fn));
		});
		// ADD THE SELECT ALL FEATURE TO LIST
		var master="input.master[type=checkbox]";
		var slave="input.slave[type=checkbox]";
		$(master,obj).attr("title",function() { return lang_selectallcheckbox(); });
		$(slave,obj).attr("title",function() { return lang_selectonecheckbox(); });
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
		// REQUEST THE PLOTS
		var attrs=new Array("title","legend","vars","colors","graph","ticks","posx",
			"data1","data2","data3","data4","data5","data6","data7","data8","data9","data10",
			"data11","data12","data13","data14","data15","data16");
		$("img[isplot=true]",obj).each(function() {
			var querystring="action=phplot";
			querystring+="&width="+$(this).width();
			querystring+="&height="+$(this).height();
			for(var i=0,len=attrs.length;i<len;i++) {
				var data=$(this).attr(attrs[i]);
				if(typeof(data)!="undefined") querystring+="&"+attrs[i]+"="+rawurlencode(data);
			};
			var img=this;
			$.ajax({
				url:"xml.php",
				data:querystring,
				type:"post",
				success:function(response) {
					$(img).attr("src",$("root>img",response).text());
					var map=$(img).attr("usemap");
					$("root>map>area",response).each(function() {
						var shape=$("shape",this).text();
						var coords=$("coords",this).text();
						var value=$("value",this).text();
						var area="<area shape='"+shape+"' coords='"+coords+"' title='"+value+"'>";
						$(map).append(area);
					});
					make_tooltips(map);
				},
				error:function(XMLHttpRequest,textStatus,errorThrown) {
					errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
				}
			});
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
		$("#tabs",obj).bind("tabsshow",function(event, ui) {
			if($(".columnizer",this).is(":visible")) {
				if($(".columnizer .column",this).length==0) {
					// CONTINUE NORMAL CODE
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
		// AUTO-GROWING TEXTAREA
		$("textarea[ckeditor!=true]",obj).autogrow();
		// AUTO-GROWING IFRAMES
		$("iframe").each(function() {
			if(security_iframe(this)) {
				var iframe="#"+$(this).attr("id");
				if($(iframe).length==1) {
					var interval=setInterval(function() {
						if($(iframe).length==0) {
							clearInterval(interval);
						} else if($(iframe).attr("isloaded")=="false") {
							// NOTHING TO DO
						} else if($(iframe).is(":visible")) {
							clearInterval(interval);
							var minheight=$(iframe).height();
							var newheight=$(iframe).contents().height();
							if(newheight>minheight) $(iframe).height(newheight);
							$(iframe).each(function() {
								var iframe2=this.contentWindow.document;
								$(iframe2).bind("contextmenu",function(e) { return false; });
								$(iframe2).bind("keydown",function(e) { $(document).trigger(e); });
							});
						}
					},1000);
				}
			}
		});
		// PROGRAM MENU SELECTS
		$("select[ismenu=true]",obj).change(function() {
			eval($(this).val());
			$(this).prop("selectedIndex",0);
		});
		// TUNNING THE SELECTS
		select_tunning(obj);
		//~ console.timeEnd("make_extras");
	}

	function select_tunning(obj) {
		//~ console.time("select_tunning");
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
			$(div2).css("right","6px");
		});
		//~ console.timeEnd("select_tunning");
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
		// BEGIN NORMAL CODE
		var ckeditors=$("textarea[ckeditor=true]",obj);
		if($(ckeditors).length) {
			// GET COLORS
			var background=get_colors("ui-widget-header","background-color");
			// CREATE THE CKEDITORS
			CKEDITOR.config.toolbarCanCollapse=false;
			CKEDITOR.config.removePlugins="elementspath,entities,scayt,resize";
			CKEDITOR.config.extraPlugins="syntaxhighlight,aspell,autogrow";
			CKEDITOR.config.enterMode=CKEDITOR.ENTER_BR;
			CKEDITOR.config.shiftEnterMode=CKEDITOR.ENTER_BR;
			CKEDITOR.config.forcePasteAsPlainText=true;
			CKEDITOR.config.toolbar=[["Bold","Italic","Underline","Strike"],["Code","SpellCheck"],["NumberedList","BulletedList"],["Outdent","Indent"],["Link","Unlink"],["TextColor","BGColor"],["Undo","Redo"],["Maximize","Source"]];
			CKEDITOR.config.language=lang_default();
			CKEDITOR.config.uiColor=background;
			CKEDITOR.config.autoGrow_onStartup=true;
			$(ckeditors).each(function() {
				var padre=$(this).parent();
				var width=$(this).outerWidth()+"px";
				$(this).ckeditor(function() {
					make_tooltips(padre);
				},{
					width:width
				});
			});
		}
		//~ console.timeEnd("make_ckeditors");
	}

	function unmake_ckeditors() {
		//~ console.time("unmake_ckeditors");
		// REMOVE THE CKEDITORS (IMPORTANT ISSUE!!!)
		$("textarea[ckeditor=true]").each(function() {
			var name=$(this).attr("name");
			if(CKEDITOR.instances[name]) CKEDITOR.instances[name].destroy();
		});
		//~ console.timeEnd("unmake_ckeditors");
	}

	function make_tooltips(obj) {
		//~ console.time("make_tooltips");
		if(typeof(obj)=="undefined") var obj=$("body");
		// CREATE THE TOOLTIPS
		$("[title][title!='']",obj).each(function() {
			// GET THE TITLE VALUE
			var title=trim($(this).attr("title"));
			var update=false;
			// CHECK IF TITLE IS THE SAME THAT THE OBJECT TEXT
			if(title==$(this).text()) {
				title="";
				update=true;
			}
			// FIX SOME ISSUES
			if(strpos(title,"<")!==false || strpos(title,">")!==false) {
				title=str_replace(new Array("<",">"),new Array("&lt;","&gt;"),title);
				update=true;
			}
			// REPAIR LONG TITLES WITH FORCED BREAK LINES
			if(strlen(title)>100) {
				words=explode(" ",title);
				title="";
				count=0;
				$(words).each(function() {
					if(count>100) {
						title+="<br/>";
						count=0;
					}
					title+=this+" ";
					count+=strlen(this)+1;
				});
				update=true;
			}
			// CHECK THE UPDATE
			if(update) $(this).attr("title",title);
			// CHECK IF TITLE HAVE DATA
			if(!title) return;
			// CREATE THE TOOLTIP
			$(this).tooltip({
				showURL:false,
				delay:0,
				track:false,
				extraClass:"ui-state-highlight ui-corner-all nowrap"
			});
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
		// GET ALL TABLES OF THE TABLA CLASS
		$(".tabla",obj).each(function() {
			if($(".thead",this).length>0) {
				// FIXS FOR POSIBLE NEXT RECALLS
				var slave="input.slave[type=checkbox]";
				$("td",this).removeClass("ui-corner-tl ui-corner-tr ui-corner-bl ui-corner-br ui-widget-header ui-widget-content ui-state-default ui-state-highlight");
				$("tr",this).unbind();
				$(slave,this).unbind();
				$("a",this).unbind();
				// STYLING THE THEAD AND NODATA
				$(".thead",this).addClass("ui-widget-header");
				$(".nodata",this).addClass("ui-widget-content");
				// SOME VARIABLES
				var trs=$("tr:visible2",this);
				var tdshead=null;
				var tdsbody=null;
				var trimpar=1;
				$(trs).each(function() {
					// MORE VARIABLES
					var numhead=$(".thead",this).length;
					var numbody=$(".tbody",this).length;
					var numdata=$(".nodata",this).length;
					var numcell=$(".cell",this).length;
					// STYLING THE ROUNDED CORNERS AND BORDERS OF THE CELLS
					if(tdshead==null && numhead>0) {
						tdshead=this;
						tdsbody=this;
						$("td:not(:last)",this).css("border-right","0px");
						$("td:first",tdsbody).removeClass("ui-corner-bl");
						$("td:last",tdsbody).removeClass("ui-corner-br");
					} else if(tdshead!=null && numhead+numbody+numdata>0) {
						tdsbody=this;
						$("td:not(:last)",this).css("border-right","0px");
						$("td",this).css("border-top","0px");
						$("td:first",tdsbody).removeClass("ui-corner-bl");
						$("td:last",tdsbody).removeClass("ui-corner-br");
					} else if(tdshead!=null) {
						$("td:first",tdshead).addClass("ui-corner-tl");
						$("td:last",tdshead).addClass("ui-corner-tr");
						tdshead=null;
						$("td:first",tdsbody).addClass("ui-corner-bl");
						$("td:last",tdsbody).addClass("ui-corner-br");
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
					$("td:first",tdshead).addClass("ui-corner-tl");
					$("td:last",tdshead).addClass("ui-corner-tr");
					$("td:first",tdsbody).addClass("ui-corner-bl");
					$("td:last",tdsbody).addClass("ui-corner-br");
				}
				// MAKE CALCS OF THE TABLE CELLS
				var last=$("tr:last",this);
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
							$(this).html2(round(sum,2));
						} else if(value=="=count()") {
							$(this).html2(count);
						} else if(value=="=avg()") {
							var average=(count>0)?sum/count:0;
							$(this).html2(round(average,2));
						}
					}
				});
			}
		});
		//~ console.timeEnd("make_tables");
	}

	function make_contextmenu() {
		//~ console.time("make_contextmenu");
		// IF NOT EXISTS THE CONTEXT MENU, CREATE IT
		if($("#contextMenu").length==0) {
			$("body").append("<div id='contextMenu' style='display:none'><ul></ul></div>");
			$(document).bind("keydown",function(event) {
				if(is_escapekey(event)) hide_contextmenu();
			});
		}
		// GET COLORS AND FONT FAMILY
		var color1=get_colors("ui-widget-content","color");
		var background1=get_colors("ui-widget-content","background-color");
		var color2=get_colors("ui-state-highlight","color");
		var background2=get_colors("ui-state-highlight","background-color");
		var fontfamily=get_colors("ui-widget","font-family");
		// PROGRAM THE GENERAL CONTEXT MENU
		$(document).contextMenu("contextMenu",{
			menuStyle:{
				"border-radius":"5px",
				"-moz-border-radius":"5px",
				"-webkit-border-radius":"5px",
				"box-shadow":"5px 5px 10px rgba(0,0,0,0.4)",
				"-moz-box-shadow":"5px 5px 10px rgba(0,0,0,0.4)",
				"-webkit-box-shadow":"5px 5px 10px rgba(0,0,0,0.4)",
				"font-family":fontfamily,
				"padding":"5px 0px",
				"color":color1,
				"background":background1,
				"border-width":"1px",
				"border-color":color1,
				"width":"150px"
			},
			itemStyle:{
				"color":color1,
				"background":background1,
				"border":"none",
				"padding":"7px"
			},
			itemHoverStyle:{
				"color":color2,
				"background":background2,
				"border":"none"
			},
			shadow:false,
			onContextMenu:function(event) {
				$("#contextMenu ul").html2("");
				var parent=$(event.target).parent();
				var trs=$("tr",parent);
				var tds=$("td.actions",parent);
				if($(trs).length || !$(tds).length) tds=$(".contextmenu");
				var hashes=new Array();
				$(tds).each(function() {
					var onclick=$(this).attr("onclick");
					if(!onclick) onclick=$("a",this).attr("onclick");
					var clase=$("span",this).attr("class");
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
					var extra=disabled?"ui-state-disabled":"";
					var html="<li class='"+extra+"'><span class='"+clase+"'></span>&nbsp;"+texto+"</li>";
					var hash=md5(html);
					if(!in_array(hash,hashes)) {
						hashes.push(hash);
						$("#contextMenu ul").append(html);
						$("#contextMenu ul li:last").bind("click",function() {
							hide_contextmenu();
							eval(onclick);
						});
					}
				});
				return $("#contextMenu ul li").length>0;
			},
			onShowMenu:function(event,menu) {
				return menu;
			}
		});
		//~ console.timeEnd("make_contextmenu");
	}

	function hide_contextmenu() {
		$("#jqContextMenu").hide();
		$("#jqContextMenuShadow").hide();
	}

	var cache_colors=new Object();

	function get_colors(clase,param) {
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

	function reset_colors() {
		for(var hash in cache_colors) delete cache_colors[hash];
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

	function load_numbers() {
		// GET COLORS OF ERROR CLASS
		var color=rgb2hex(get_colors("ui-state-error","color"));
		var background=rgb2hex(get_colors("ui-state-error","background-color"));
		$("head").append("<link href='xml.php?action=number&format=css&bgcolor="+color+"&fgcolor="+background+"' rel='stylesheet' type='text/css'></link>");
	}

	function make_numbers(obj) {
		if(typeof(obj)=="undefined") var obj=$("body");
		// CONVERT THE NUMBERS INTO GRAPHS
		$("a.number",obj).each(function() {
			var html=$(this).html();
			if(substr(html,-1,1)==")") {
				var txt=strtok(html,"(");
				var num1=intval(strtok(")"));
				var num2=(num1>99)?99:num1;
				var span="<span class='number number-icon number-icon-"+num2+"' original='"+num1+"'></span>";
				$(this).html2(txt);
				$(this).append(span);
			}
		});
	}

	function unmake_numbers(obj) {
		if(typeof(obj)=="undefined") var obj=$("body");
		// CONVERT THE IMAGES INTO NUMBERS
		$("span.number",obj).each(function() {
			var num=$(this).attr("original");
			$(this).parent().append("("+num+")");
			$(this).remove2();
		});
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
						$(this).trigger("click");
						exists=true;
					}
				});
				if(exists) return false;
			}
		});
	}

	function make_toolbar(obj) {
		if(typeof(obj)=="undefined") var obj=$("body");
		$(".toolbar",obj).each(function() {
			var oldobj=$(this);
			$(oldobj).removeClass("toolbar");
			var hash=md5(microtime(true));
			$(oldobj).wrap("<div class='toolbar_"+hash+"'/>");
			oldobj=$(oldobj).parent();
			var newobj=$("<div class='toolbar_"+hash+"'/>");
			$(newobj).css("position","absolute");
			$(newobj).css("z-index",1);
			$(oldobj).parent().append(newobj);
			// MAKER FUNCTION
			var for_make=function() {
				if(!$(".toolbar_"+hash,obj).length) return false;
				if(!$(oldobj).is(":visible")) return true;
				$(newobj).width($(oldobj).width());
				$(newobj).height($(oldobj).height());
				$(newobj).append($(oldobj).children());
				$(oldobj).width($(newobj).width());
				$(oldobj).height($(newobj).height());
				return true;
			};
			// VARIABLES NEEDED BY SCROLL
			var background_on=str_replace(new Array("rgb",")"),new Array("rgba",",0.8)"),get_colors("ui-widget-header","background-color"));
			var color_on=get_colors("ui-widget-header","color");
			var background_off=get_colors("ui-widget-content","background-color");
			var color_off=get_colors("ui-widget-content","color");
			// SCROLL FUNCTION
			var for_scroll=function() {
				if(!$(".toolbar_"+hash,obj).length) return false;
				if(!$(oldobj).is(":visible")) return true;
				var pos=$(oldobj).position();
				var scroll=$(window).scrollTop();
				var pos0=$(oldobj).parent().position();
				var height=$(window).height();
				var max=height-pos0.top+scroll-$(oldobj).height()-4; // THE 4 FIX AN UNKNOWN UI CONSTRAIN!!!
				if(pos.top<scroll) {
					pos.top=scroll;
					var background=background_on;
					var color=color_on;
				} else if(pos.top>max) {
					pos.top=max;
					var background=background_on;
					var color=color_on;
				} else {
					var background=background_off;
					var color=color_off;
				}
				$(newobj).css("color",color).css("background-color",background).addClass("ui-corner-all");
				$(newobj).css("top",pos.top+"px");
				return true;
			};
			// UNMAKER FUNCTION
			var for_unmake=function() {
				if(!$(".toolbar_"+hash,obj).length) return false;
				if(!$(oldobj).is(":visible")) return true;
				$(oldobj).append($(newobj).children());
				$(oldobj).css("width","");
				$(oldobj).css("height","");
				return true;
			};
			// UNBIND EVENTS FUNCTION
			var fn_unbind=function(event) {
				$(window).unbind("scroll",fn_scroll);
				$(window).unbind("resize",fn_resize);
			};
			// SCROLL EVENT FUNCTION
			var is_scroll=0;
			var fn_scroll=function(event) {
				if(event.isTrigger) return;
				if(is_scroll) return;
				is_scroll=1;
				if(!for_scroll()) {
					fn_unbind();
				}
				is_scroll=0;
			};
			// RESIZE EVENT FUNCTION
			var is_resize=0;
			var fn_resize=function(event) {
				if(event.isTrigger) return;
				if(is_resize) return;
				is_resize=1;
				if(!for_unmake()) {
					fn_unbind();
					is_resize=0;
				} else {
					setTimeout(function() {
						if(!for_make()) {
							fn_unbind();
						} else {
							if(!for_scroll()) {
								fn_unbind();
							}
						}
						is_resize=0;
					},100);
				}
			};
			// INITIALIZE AND BIND EVENTS
			if(for_make()) {
				if(for_scroll()) {
					// ATTACH EVENTS
					$(window).bind("scroll",fn_scroll);
					$(window).bind("resize",fn_resize);
				}
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

	// TO PREVENT JQUERY THE ADD _=[TIMESTAMP] FEATURE
	jQuery.ajaxSetup({ cache:true });

	// WHEN DOCUMENT IS READY
	$(document).ready(function() {
		//~ console.time("document_ready fase 0");
		var menu=$(".ui-layout-west");
		saltos_login=($(menu).text()!="")?1:0;
		if(saltos_login) sync_cookies("start");
		make_notice();
		$("body > *").addClass("preloading");
		$("body").removeClass("preloading");
		loadingcontent();
		setTimeout(function() {
			//~ console.time("document_ready fase 1");
			init_history();
			make_dialog();
			make_contextmenu();
			make_shortcuts();
			make_abort();
			var header=$(".ui-layout-north");
			make_numbers(header);
			var menu=$(".ui-layout-west");
			make_menu(menu);
			make_numbers(menu);
			var screen=$(".ui-layout-center");
			make_tabs(screen);
			make_tables(screen);
			make_extras(screen);
			make_ckeditors(screen);
			$("body > *").removeClass("preloading");
			setTimeout(function() {
				//~ console.time("document_ready fase 2");
				load_numbers();
				make_toolbar(header);
				make_hovers(header);
				make_tooltips(header);
				make_draganddrop(header);
				make_toolbar(menu);
				make_hovers(menu);
				make_tooltips(menu);
				make_draganddrop(menu);
				make_toolbar(screen);
				make_hovers(screen);
				make_tooltips(screen);
				make_draganddrop(screen);
				make_focus();
				//~ console.timeEnd("document_ready fase 2");
			},100);
			unloadingcontent();
			//~ console.timeEnd("document_ready fase 1");
		},100);
		//~ console.timeEnd("document_ready fase 0");
	});

}
