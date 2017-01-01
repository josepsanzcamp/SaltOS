/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2017 by Josep Sanz Campderrós
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

if(typeof(__feedstest__)=="undefined" && typeof(parent.__feedstest__)=="undefined") {
	"use strict";
	var __feedstest__=1;

	function feeds_test() {
		var url=$("#feeds_new_0_url").val();
		if(url=="") url="null";
		loadingcontent();
		var data="action=feeds&url="+encodeURIComponent(url);
		$.ajax({
			url:"index.php",
			data:data,
			type:"get",
			success:function(response) {
				$(response["rows"]).each(function() {
					$("#feeds_new_0_url").val(this["url"]);
					$("#feeds_new_0_title").val(this["title"]);
					$("#feeds_new_0_description").val(this["description"]);
					$("#feeds_new_0_iframe").attr("src",this["image"]);
					$("#feeds_new_0_image").val(this["image"]);
					$("#feeds_new_0_link").val(this["link"]);
				});
				if($("#feeds_new_0_title").val()!="") {
					$("#feeds_new_0_add").removeClass("ui-state-disabled");
				} else {
					$("#feeds_new_0_add").addClass("ui-state-disabled");
				}
				unloadingcontent();
			},
			error:function(XMLHttpRequest,textStatus,errorThrown) {
				errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
			}
		});
	}

}
