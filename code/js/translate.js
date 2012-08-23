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

if(typeof(__translate__)=="undefined" && typeof(parent.__translate__)=="undefined") {
	"use strict";
	var __translate__=1;

	function init_translate() {
		setTimeout(function() {
			var div=$(".ui-layout-west > .translate > div");
			if(!$(div).length) return;
			if($("textarea",div).length) return;
			// ADD ELEMENTS
			var clase="class='ui-state-default ui-corner-all'";
			$(div).append("<textarea "+clase+" spellcheck='false'></textarea>");
			$(div).append("<br/>");
			$(div).append("<select "+clase+"></select>");
			var down="<span class='saltos-icon saltos-icon-down'></span>";
			var up="<span class='saltos-icon saltos-icon-up'></span>";
			$(div).append("<a href='javascript:void(0)' "+clase+">"+down+"</a>");
			$(div).append("<a href='javascript:void(0)' "+clase+">"+up+"</a>");
			$(div).append("<br/>");
			$(div).append("<textarea "+clase+" spellcheck='false'></textarea>");
			// SOME NEEDEDS FUNCTIONS
			function __translate_get_reverse() {
				var langs=$("select",div).val();
				var option=$("option[value='"+langs+"']",div);
				langs=$(option).attr("reverse");
				return langs;
			}
			function __translate_ui_reverse() {
				var langs=__translate_get_reverse();
				if(langs) {
					$("a:last",div).removeAttr("disabled");
					$("a:last",div).removeClass("ui-state-disabled");
				} else {
					$("a:last",div).attr("disabled",true);
					$("a:last",div).addClass("ui-state-disabled");
				}
			}
			function __translate_enable() {
				$("textarea,select,a",div).removeAttr("disabled");
				$("textarea,select,a",div).removeClass("ui-state-disabled");
				__translate_ui_reverse();
			};
			function __translate_disable() {
				$("textarea,select,a",div).attr("disabled",true);
				$("textarea,select,a",div).addClass("ui-state-disabled");
			};
			function __translate_get_cookie() {
				var langs=getCookie("saltos_translate_langs");
				var option=$("option[value='"+langs+"']",div);
				if($(option).length) $("select",div).val(langs);
			}
			function __translate_set_cookie() {
				var langs=$("select",div).val();
				setCookie("saltos_translate_langs",langs);
			}
			// ADD LANGS
			var data="action=translate&langs=auto";
			$.ajax({
				url:"xml.php",
				data:data,
				type:"get",
				success:function(response) {
					if(!response) {
						__translate_disable();
						return;
					}
					$("select",div).append(response);
					// PROGRAM SELECT PERSISTENCE USING A COOKIE
					__translate_get_cookie();
					__translate_ui_reverse();
					$("select",div).change(function() {
						__translate_set_cookie();
						__translate_ui_reverse();
					});
				},
				error:function(XMLHttpRequest,textStatus,errorThrown) {
					errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
				}
			});
			// PROGRAM SIZES OF ELEMENTS
			$(div).css("padding","15px 0px 0px 15px");
			$(div).height(200);
			$(div).parent().bind("accordionchange",function() {
				if($(div).is(":visible")) {
					var width=$(div).width()-30;
					$("textarea",div).width(width);
					var width_a_first=$("a:first",div).width();
					var width_a_last=$("a:last",div).width();
					$("select",div).width(width-width_a_first-width_a_last-20);
					var height=$(div).height()-50;
					var height_select=$("select",div).height();
					$("textarea",div).height((height-height_select)/2);
				}
			}).trigger("accordionchange");
			// PROGRAM AUTODETECT LANG
			var oldtext="";
			$("textarea:first").change(function() {
				if($(this).hasClass("ui-state-disabled")) return;
				var text=$("textarea:first",div).val();
				if(levenshtein(oldtext,text)<strlen(oldtext)/2) return;
				oldtext=text;
				__translate_disable();
				var data="action=translate&langs=auto&text="+rawurlencode(text);
				$.ajax({
					url:"xml.php",
					data:data,
					type:"post",
					success:function(response) {
						$("option",div).remove();
						$("select",div).append(response);
						__translate_enable();
						__translate_get_cookie();
						__translate_ui_reverse();
					},
					error:function(XMLHttpRequest,textStatus,errorThrown) {
						__translate_enable();
						errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
					}
				});
			});
			// PROGRAM TRANSLATE BUTTON
			$("a:first",div).bind("click",function() {
				if($(this).hasClass("ui-state-disabled")) return;
				var langs=$("select",div).val();
				var text=$("textarea:first",div).val();
				__translate_set_cookie();
				__translate_disable();
				var data="action=translate&langs="+langs+"&text="+rawurlencode(text);
				$.ajax({
					url:"xml.php",
					data:data,
					type:"post",
					success:function(response) {
						$("textarea:last",div).val(response);
						__translate_enable();
						$("textarea:last",div).trigger("focus");
					},
					error:function(XMLHttpRequest,textStatus,errorThrown) {
						__translate_enable();
						errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
					}
				});
			});
			$("a:last",div).bind("click",function() {
				if($(this).hasClass("ui-state-disabled")) return;
				var langs=__translate_get_reverse();
				var text=$("textarea:last",div).val();
				__translate_set_cookie();
				__translate_disable();
				var data="action=translate&langs="+langs+"&text="+rawurlencode(text);
				$.ajax({
					url:"xml.php",
					data:data,
					type:"post",
					success:function(response) {
						$("textarea:first",div).val(response);
						__translate_enable();
						$("textarea:first",div).trigger("focus");
					},
					error:function(XMLHttpRequest,textStatus,errorThrown) {
						__translate_enable();
						errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
					}
				});
			});
			// FINISH PLUGUIN INIT
			make_hovers(div);
			// TUNNING THE SELECTS
			select_tunning(div);
		},100);
	}

}

"use strict";
$(document).ready(function() { init_translate(); });
