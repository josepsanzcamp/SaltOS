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

if(typeof(__mobile__)=="undefined" && typeof(parent.__mobile__)=="undefined") {
	"use strict";
	var __mobile__=1;

	/* JQUERY FUNCTIONS */
	(function($){
		$.fn.html2=function(html) {
			$(this).children().remove2();
			$(this).html(html);
		};
		$.fn.html3=function(obj) {
			$(this).children().remove2();
			$(this).append($(obj).children());
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
			$("*",this).unbind().jqmRemoveData().removeData().removeProp().removeAttr().remove();
			$(this).unbind().jqmRemoveData().removeData().removeProp().removeAttr().remove();
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
	function dialog(title,message,buttons) {
		// CHECK SOME PARAMETERS
		if(typeof(message)=="undefined") var message="";
		if(typeof(buttons)=="undefined") var buttons=function() {};
		// SOME PREDEFINED ACTIONS
		if(title=="close") {
			if($(".ui-dialog:visible").length>0) {
				$("#dialog").hide();
				$("#dialog").page("destroy");
				if(typeof(message)=="function") message();
			}
			return false;
		}
		if(title=="isopen") {
			return $(".ui-dialog:visible").length>0;
		}
		if($(".ui-dialog:visible").length>0) {
			return false;
		}
		// IF MESSAGE EXISTS, OPEN IT
		if(message=="") return false;
		if($("#dialog").length==0) {
			$("body").append("<div data-role='dialog' id='dialog'></div>");
			$("#dialog").append("<div data-role='header' data-theme='e'><h1></h1></div>");
			$("#dialog").append("<div data-role='content' data-theme='e'><h4></h4><p></p></div>");
		}
		$("#dialog h1").html2(title);
		$("#dialog h4").html2(message);
		$("#dialog p").html2("");
		jQuery.each(buttons,function(btn,fn) {
			$("#dialog p").append("<a href='javascript:void(0)' data-role='button' data-mini='true' data-inline='true'>"+btn+"</a>");
			$("#dialog p a:last").bind("click",function() { dialog("close",fn); });
		});
		if($("#dialog p a").length==0) {
			$("#dialog p").append("<a href='javascript:void(0)' data-role='button' data-mini='true' data-inline='true'>"+lang_buttoncontinue()+"</a>");
			$("#dialog p a:last").bind("click",function() { dialog("close"); });
		}
		$("#dialog").page();
		$("#dialog").css("margin-top",$(document).scrollTop());
		$("#dialog").show();
		$("#dialog p a:first").trigger("focus");
		var interval=setInterval(function() {
			if(dialog("isopen")) {
				$("#dialog").css("margin-top",$(document).scrollTop());
			} else {
				clearInterval(interval);
			}
		},100);
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
	}

	function hide_popupnotice() {
		// UNUSED
	}

	function notice(title,message,arg1,arg2,arg3) {
		// CHECK SOME PARAMETERS
		var sticky=false;
		var action=function() {};
		var theme="ui-state-highlight";
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
		$(".ui-footer").prepend("<div class='ui-bar ui-bar-e ui-corner-all jGrowl-notification "+theme+"'>"+title+": "+message+"</div>");
		var div=$(".ui-footer div:first");
		var timeout=null;
		if(!sticky) {
			timeout=setTimeout(function() {
				$(div).remove2();
				action();
			},10000);
		}
		$(div).bind("click",function() {
			if(timeout) clearTimeout(timeout);
			$(div).remove2();
			action();
		});
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

	function setBoolCookie(name,value) {
		setIntCookie(name,value?1:0);
	}

	/* FOR BLOCK THE UI AND NOT PERMIT 2 REQUESTS AT THE SAME TIME */
	function loadingcontent(message) {
		// CHECK PARAMETERS
		if(typeof(message)=="undefined") var message=lang_loading();
		// CHECK IF EXIST ANOTHER BLOCKUI
		if(isloadingcontent()) {
			$(".ui-loader > h1").text(message);
			return false;
		}
		// ACTIVATE THE BLOCK UI FEATURE
		$.mobile.showPageLoadingMsg("e",message,true);
		return true;
	}

	function unloadingcontent() {
		$.mobile.hidePageLoadingMsg();
	}

	function isloadingcontent() {
		return $(".ui-loader:visible").length>0;
	}

	/* HELPERS FOR HISTORY MANAGEMENT */
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
		// CHECK FOR SOME FUCKED BROWSERS
		if(window.location.href!=url) {
			ignore_onhashchange=1;
			window.location.href=url;
		}
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
		// CHECK FOR SOME FUCKED BROWSERS
		if(window.location.href!=url) {
			ignore_onhashchange=1;
			window.location.replace(url);
		}
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
							fix_max_input_vars=base64_encode(implode("&",fix_max_input_vars));
							$(jqForm).append("<input type='hidden' name='fix_max_input_vars' value='"+fix_max_input_vars+"'/>");
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
				callback();
				loadcontent(data);
			},
			error:function(XMLHttpRequest,textStatus,errorThrown) {
				callback();
				errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
			}
		});
	}

	function opencontent(url,callback) {
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
				callback();
				loadcontent(data);
			},
			error:function(XMLHttpRequest,textStatus,errorThrown) {
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
					updatecontent(html);
				},
				error:function(XMLHttpRequest,textStatus,errorThrown) {
					errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
				}
			});
		} else if(xml) {
			var html=str2html(xml);
			if($("#page",html).text()!="") {
				updatecontent(html);
			} else {
				// IF THE RETURNED HTML CONTAIN A SCRIPT NOT UNBLOCK THE UI
				var screen=$("#page");
				if($("script",html).length!=0) {
					$(screen).append(html);
				} else {
					unloadingcontent();
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
	var saltos_login=1;
	var saltos_logout=0;

	function updatecontent(html) {
		var page=$("#page");
		$(page).hide();
		$(page).page("destroy");
		// UPDATE THE TITLE
		var divtitle=$("div[type=title]",html);
		document.title=$(divtitle).text();
		// UPDATE THE NORTH PANEL
		var header=$(".ui-layout-north");
		var header2=$(".ui-layout-north",html);
		$(header).html3(header2);
		make_extras(header);
		// CHECK FOR LOGIN AND LOGOUT
		var menu=$(".ui-layout-west");
		var menu2=$(".ui-layout-west",html);
		saltos_login=($(menu).text()=="" && $(menu2).text()!="")?1:0;
		saltos_logout=($(menu).text()!="" && $(menu2).text()=="")?1:0;
		// IF LOGIN
		if(saltos_login) sync_cookies("start");
		// UPDATE THE MENU
		$(menu).html3(menu2);
		make_extras(menu);
		// IF LOGOUT
		if(saltos_logout) sync_cookies("stop");
		// UPDATE THE CENTER PANE
		var screen=$(".ui-layout-center");
		var screen2=$(".ui-layout-center",html);
		$(screen).html3(screen2);
		make_extras(screen);
		$(page).page();
		$(page).show();
		unloadingcontent();
		$(document).scrollTop(1);
	}

	function make_extras(obj) {
		if(typeof(obj)=="undefined") var obj=$("body");
		// PROGRAM INTERGER TYPE CAST
		$("input[isinteger=true]",obj).each(function() {
			$(this).bind("keyup",function() { intval2(this); });
		});
		// PROGRAM FLOAT TYPE CAST
		$("input[isfloat=true]",obj).each(function() {
			$(this).bind("keyup",function() { floatval2(this); });
		});
		// ADD THE SELECT ALL FEATURE TO LIST
		var master="input.master[type=checkbox]";
		var slave="input.slave[type=checkbox]";
		$(master,obj).next().html2(function() { return lang_selectallcheckbox(); });
		$(slave,obj).next().html2(function() { return lang_selectonecheckbox(); });
		$(master,obj).bind("click",function() {
			var value=$(this).prop("checked");
			var ul=$(this).parent().parent().parent().parent();
			$(slave,ul).prop("checked",value).checkboxradio("refresh");
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
		// TO CLEAR AMBIGUOUS THINGS
		$(".nowrap.siwrap",obj).removeClass("nowrap siwrap");
		// TRICK FOR STYLING THE INFO NOTIFY
		$(".info",obj).addClass("ui-bar ui-bar-e ui-corner-all");
		// TRICK FOR STYLING THE TITLES
		$(".title",obj).addClass("ui-bar ui-bar-b ui-corner-all");
		// AUTO-GROWING TEXTAREA
		$("textarea[ckeditor!=true]",obj).autogrow();
		// STYLING IFRAME BORDER
		$(".preiframe",obj).textinput();
		// AUTO-GROWING IFRAMES
		$("iframe",obj).each(function() {
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
	}

	function select_tunning(obj) {
		// UNUSED
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
		// UNUSED
	}

	function make_tooltips(obj) {
		// UNUSED
	}

	var focused=null;

	function make_focus(obj) {
		// UNUSED
	}

	function make_tables(obj) {
		// UNUSED
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

	function make_numbers(obj) {
		// UNUSED
	}

	function unmake_numbers(obj) {
		// UNUSED
	}

	var jqxhr=null;

	function make_abort() {
		// UNUSED
	}

	// TO PREVENT JQUERY THE ADD _=[TIMESTAMP] FEATURE
	jQuery.ajaxSetup({ cache:true });

	// TO CONTROL AJAX IN JQUERY MOBILE
	$.mobile.autoInitializePage=false;
	$.mobile.ajaxEnabled=false;
	$.mobile.linkBindingEnabled=false;
	$.mobile.hashListeningEnabled=false;
	//~ $.mobile.page.prototype.options.keepNative="*";

	// TO DO COMPATIBLE WITH DESKTOP PLUGINS
	(function($){
		// JQUERY UI TABS
		$.fn.tabs=function() {
			// NOTHING TO DO
		};
		// JQUERY UI DATEPICKER
		$.datepicker=function() {
			// NOTHING TO DO
		};
		$.extend($.datepicker,{
			regional:function () {
				// NOTHING TO DO
			},
			setDefaults:function () {
				// NOTHING TO DO
			}
		});
		// JQUERY UI AUTOCOMPLETE
		$.fn.autocomplete=function() {
			// NOTHING TO DO
		};
		// JQUERY FAVICON PLUGIN
		$.favicon=function() {
			// NOTHING TO DO
		};
		$.extend($.favicon,{
			animate:function () {
				// NOTHING TO DO
			},
			unanimate:function () {
				// NOTHING TO DO
			}
		});
		// JQUERY JPLAYER
		$.fn.jPlayer=function() {
			// NOTHING TO DO
		};
	})(jQuery);

	// WHEN DOCUMENT IS READY
	$(document).ready(function() {
		var menu=$(".ui-layout-west");
		saltos_login=($(menu).text()!="")?1:0;
		if(saltos_login) sync_cookies("start");
		init_history();
		var header=$(".ui-layout-north");
		make_extras(header);
		var menu=$(".ui-layout-west");
		make_extras(menu);
		var screen=$(".ui-layout-center");
		make_extras(screen);
		$.mobile.initializePage();
	});

}
