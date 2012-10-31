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

if(typeof(__quickform__)=="undefined" && typeof(parent.__quickform__)=="undefined") {
	"use strict";
	var __quickform__=1;

	function update_quickform() {
		if(!$("#selectquick").length) return;
		var data="action=quickform&page="+getParam("page")+"&id="+getParam("id")+"&id_folder="+getParam("id_folder")+"&is_fichero="+getParam("is_fichero")+"&is_buscador="+getParam("is_buscador");
		$.ajax({
			url:"xml.php",
			data:data,
			type:"get",
			success:function (response) {
				var value2=$("root>value",response).text();
				var options="";
				$("root>rows>row",response).each(function() {
					var label=str_replace(new Array("<",">"),new Array("&lt;","&gt;"),$("label",this).text());
					var value=$("value",this).text();
					var selected=(value==value2)?"selected='selected'":"";
					options+="<option value='"+value+"' "+selected+">"+label+"</option>";
				});
				$("#selectquick").html(options);
				if($("root>first",response).text()) $("#firstquick").removeClass("ui-state-disabled");
				if($("root>previous",response).text()) $("#previousquick").removeClass("ui-state-disabled");
				if($("root>next",response).text()) $("#nextquick").removeClass("ui-state-disabled");
				if($("root>last",response).text()) $("#lastquick").removeClass("ui-state-disabled");
			},
			error:function(XMLHttpRequest,textStatus,errorThrown) {
				errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
			}
		});
	}

}

"use strict";
$(function() { update_quickform(); });
