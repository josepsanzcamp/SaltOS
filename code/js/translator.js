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

if(typeof(__translator__)=="undefined" && typeof(parent.__translator__)=="undefined") {
	"use strict";
	var __translator__=1;

	function translator() {
		// BEGIN OPEN DIALOG
		dialog(lang_translator());
		var dialog2=$("#dialog");
		$(dialog2).html("<div id='translator'></div>");
		// PROGRAM RESIZE EVENT
		$(dialog2).dialog("option","resizeStop",function(event,ui) {
			setIntCookie("saltos_translator_width",$(dialog2).dialog("option","width"));
			setIntCookie("saltos_translator_height",$(dialog2).dialog("option","height"));
			__translator_resize(0);
		});
		// PROGRAM CLOSE EVENT
		$(dialog2).dialog("option","close",function(event,ui) {
			$(dialog2).dialog("option","resizeStop",function() {});
			$(dialog2).dialog("option","close",function() {});
			$("*",dialog2).each(function() { $(this).remove(); });
		});
		// UPDATE SIZE AND POSITION
		var width=getIntCookie("saltos_translator_width");
		if(!width) width=600;
		$(dialog2).dialog("option","width",width);
		var height=getIntCookie("saltos_translator_height");
		if(!height) height=400;
		$(dialog2).dialog("option","height",height);
		// END OPEN DIALOG
		$(dialog2).dialog("option","position",{ my:"center",at:"center",of:window });
		$(dialog2).dialog("open");
		// CONTINUE
		var div=$("#translator");
		// ADD ELEMENTS
		var clase="class='ui-state-default ui-corner-all'";
		$(div).append("<textarea "+clase+" spellcheck='false'></textarea>");
		$(div).append("<br/>");
		$(div).append("<select "+clase+"></select>");
		var down="<span class='fa fa-arrow-down'></span>";
		var up="<span class='fa fa-arrow-up'></span>";
		$(div).append("<a href='javascript:void(0)' "+clase+">"+down+"</a>");
		$(div).append("<a href='javascript:void(0)' "+clase+">"+up+"</a>");
		$(div).append("<br/>");
		$(div).append("<textarea "+clase+" spellcheck='false'></textarea>");
		$("select",div).selectmenu({
			width:"auto"
		}).on("selectmenuchange",function() {
			$(this).trigger("change");
		}).on("refresh change",function() {
			$(this).selectmenu("refresh");
		});
		// SOME NEEDEDS FUNCTIONS
		function __translator_get_reverse() {
			var langs=$("select",div).val();
			var option=$("option[value='"+langs+"']",div);
			langs=$(option).attr("reverse");
			return langs;
		}
		function __translator_ui_reverse() {
			var langs=__translator_get_reverse();
			if(langs) {
				$("a:last",div).removeAttr("disabled");
				$("a:last",div).removeClass("ui-state-disabled");
			} else {
				$("a:last",div).attr("disabled",true);
				$("a:last",div).addClass("ui-state-disabled");
			}
		}
		function __translator_enable() {
			$("textarea,select,a",div).removeAttr("disabled");
			$("textarea,select,a",div).removeClass("ui-state-disabled");
			__translator_ui_reverse();
		};
		function __translator_disable() {
			$("textarea,select,a",div).attr("disabled",true);
			$("textarea,select,a",div).addClass("ui-state-disabled");
		};
		function __translator_get_cookie() {
			var langs=getCookie("saltos_translator_langs");
			var option=$("option[value='"+langs+"']",div);
			if($(option).length) $("select",div).val(langs).trigger("refresh");
			else $("option:first",div).prop("selected","selected").parent().trigger("refresh");
		}
		function __translator_set_cookie() {
			var langs=$("select",div).val();
			setCookie("saltos_translator_langs",langs);
		}
		// ADD LANGS
		var data="action=translator&langs=auto";
		$.ajax({
			url:"index.php",
			data:data,
			type:"get",
			success:function(response) {
				if(!response) {
					__translator_disable();
					return;
				}
				$("select",div).append(response);
				// PROGRAM SELECT PERSISTENCE USING A COOKIE
				__translator_get_cookie();
				__translator_ui_reverse();
				$("select",div).on("change",function() {
					__translator_set_cookie();
					__translator_ui_reverse();
				});
			},
			error:function(XMLHttpRequest,textStatus,errorThrown) {
				errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
			}
		});
		// PROGRAM SIZES OF ELEMENTS
		$(div).css("padding","10px 0 0 0");
		function __translator_resize(arg) {
			var width=$(dialog2).dialog("option","width")-50;
			$("textarea",dialog2).width(width);
			//~ var width_a_first=$("a:first",dialog2).width();
			//~ var width_a_last=$("a:last",dialog2).width();
			//~ $("select",dialog2).width(width-width_a_first-width_a_last-28);
			var height=$(dialog2).dialog("option","height")-90-arg;
			var height_select=$("select",dialog2).height();
			$("textarea",dialog2).height((height-height_select)/2);
		}
		__translator_resize(5);
		// PROGRAM translator BUTTON
		$("a:first",div).on("click",function() {
			if($(this).hasClass("ui-state-disabled")) return;
			var langs=$("select",div).val();
			var text=$("textarea:first",div).val();
			__translator_set_cookie();
			__translator_disable();
			var data="action=translator&langs="+langs+"&text="+encodeURIComponent(text);
			$.ajax({
				url:"index.php",
				data:data,
				type:"post",
				success:function(response) {
					$("textarea:last",div).val(response);
					__translator_enable();
					$("textarea:last",div).trigger("focus");
				},
				error:function(XMLHttpRequest,textStatus,errorThrown) {
					__translator_enable();
					errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
				}
			});
		});
		$("a:last",div).on("click",function() {
			if($(this).hasClass("ui-state-disabled")) return;
			var langs=__translator_get_reverse();
			var text=$("textarea:last",div).val();
			__translator_set_cookie();
			__translator_disable();
			var data="action=translator&langs="+langs+"&text="+encodeURIComponent(text);
			$.ajax({
				url:"index.php",
				data:data,
				type:"post",
				success:function(response) {
					$("textarea:first",div).val(response);
					__translator_enable();
					$("textarea:first",div).trigger("focus");
				},
				error:function(XMLHttpRequest,textStatus,errorThrown) {
					__translator_enable();
					errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
				}
			});
		});
		$("textarea:first",div).trigger("focus");
	}

}
