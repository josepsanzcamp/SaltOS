/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2021 by Josep Sanz Campderrós
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

if(typeof(__pagerlist__)=="undefined" && typeof(parent.__pagerlist__)=="undefined") {
	"use strict";
	var __pagerlist__=1;

	function update_pagerlist() {
		if(!$("#selectpager").length) return;
		var data="action=pagerlist&page="+getParam("page");
		$.ajax({
			url:"index.php",
			data:data,
			type:"get",
			success:function (response) {
				var value2=response["value"];
				var options="";
				$(response["rows"]).each(function() {
					var selected=(this["value"]==value2)?"selected='selected'":"";
					options+="<option value='"+this["value"]+"' "+selected+">"+this["label"]+"</option>";
				});
				$("#selectpager").html(options).trigger("refresh");
				if(response["first"]) $("#firstpager").removeClass("ui-state-disabled");
				if(response["previous"]) $("#previouspager").removeClass("ui-state-disabled");
				if(response["next"]) $("#nextpager").removeClass("ui-state-disabled");
				if(response["last"]) $("#lastpager").removeClass("ui-state-disabled");
				$(".infopager").html(response["info"]);
			},
			error:function(XMLHttpRequest,textStatus,errorThrown) {
				errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
			}
		});
	}

}

"use strict";
$(function() { update_pagerlist(); });
