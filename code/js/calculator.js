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

if(typeof(__calculator__)=="undefined" && typeof(parent.__calculator__)=="undefined") {
	"use strict";
	var __calculator__=1;

	function calculator() {
		// BEGIN OPEN DIALOG
		dialog(lang_calculator());
		var dialog2=$("#dialog");
		$(dialog2).html("<div id='calculator'></div>");
		// PROGRAM RESIZE EVENT
		$(dialog2).dialog("option","resizeStop",function(event,ui) {
			setIntCookie("saltos_calculator_width",$(dialog2).dialog("option","width"));
			setIntCookie("saltos_calculator_height",$(dialog2).dialog("option","height"));
			__calculator_resize(0);
		});
		// PROGRAM CLOSE EVENT
		$(dialog2).dialog("option","close",function(event,ui) {
			$(dialog2).dialog("option","resizeStop",function() {});
			$(dialog2).dialog("option","close",function() {});
			$("*",dialog2).each(function() { $(this).remove(); });
		});
		// UPDATE SIZE AND POSITION
		var width=getIntCookie("saltos_calculator_width");
		if(!width) width=300;
		$(dialog2).dialog("option","width",width);
		var height=getIntCookie("saltos_calculator_height");
		if(!height) height=400;
		$(dialog2).dialog("option","height",height);
		// END OPEN DIALOG
		$(dialog2).dialog("option","position",{ my:"center",at:"center",of:window });
		$(dialog2).dialog("open");
		// CONTINUE
		var div=$("#calculator");
		// ADD ELEMENTS
		var clase="class='ui-state-default ui-corner-all'";
		$(div).append("<textarea "+clase+" spellcheck='false'></textarea>");
		// SOME NEEDEDS FUNCTIONS
		function __calculator_enable() {
			$("textarea",div).removeAttr("disabled");
			$("textarea",div).removeClass("ui-state-disabled");
		};
		function __calculator_disable() {
			$("textarea",div).attr("disabled",true);
			$("textarea",div).addClass("ui-state-disabled");
		};
		// PROGRAM SIZES OF ELEMENTS
		$(div).css("padding","10px 0 0 0");
		function __calculator_resize(arg) {
			$("textarea",dialog2).width($(dialog2).dialog("option","width")-50);
			$("textarea",dialog2).height($(dialog2).dialog("option","height")-70-arg);
		}
		__calculator_resize(5);
		// PROGRAM CALCULATOR BUTTON
		$("textarea",div).on("keydown",function(event) {
			if(is_enterkey(event)) {
				var text=$("textarea",div).val();
				text=explode("\n",text);
				text=trim(text.pop());
				if(text!="") {
					__calculator_disable();
					var data="action=calculator&text="+encodeURIComponent(text);
					$.ajax({
						url:"index.php",
						data:data,
						type:"post",
						success:function(response) {
							var text=$("textarea",div).val();
							$("textarea",div).val(text+"="+response+"\n");
							__calculator_enable();
							$("textarea",div).trigger("focus");
						},
						error:function(XMLHttpRequest,textStatus,errorThrown) {
							__calculator_enable();
							errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
						}
					});
					return false;
				}
			}
		});
		$("textarea:first",div).trigger("focus");
	}

}
