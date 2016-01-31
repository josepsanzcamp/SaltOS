/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2016 by Josep Sanz Campderrós
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

	function init_calculator() {
		setTimeout(function() {
			var div=$(".ui-layout-west > .calculator > div");
			if(!$(div).length) return;
			if($("textarea",div).length) return;
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
			$(div).css("padding","15px 0px 0px 15px");
			$(div).height(100);
			$(div).parent().bind("accordionactivate",function() {
				if($(div).is(":visible")) {
					var width=$(div).width()-30;
					$("textarea",div).width(width);
					var height=$(div).height()-30;
					$("textarea",div).height(height);
				}
			}).trigger("accordionactivate");
			// PROGRAM CALCULATOR BUTTON
			$("textarea",div).bind("keydown",function(event) {
				if(is_enterkey(event)) {
					var text=$("textarea",div).val();
					text=explode("\n",text);
					text=trim(text.pop());
					if(text!="") {
						__calculator_disable();
						var data="action=calculator&text="+rawurlencode(text);
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
			// FINISH PLUGUIN INIT
			make_hovers(div);
		},100);
	}

}

"use strict";
$(function() { init_calculator(); });
