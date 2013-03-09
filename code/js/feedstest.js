/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2013 by Josep Sanz Campderrós
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

if(typeof(__feedstest__)=="undefined" && typeof(parent.__feedstest__)=="undefined") {
	"use strict";
	var __feedstest__=1;

	function feeds_test() {
		var url=$("#feeds_new_0_url").val();
		if(url=="") url="null";
		loadingcontent();
		var data="action=feeds&url="+rawurlencode(url);
		$.ajax({
			url:"xml.php",
			data:data,
			type:"get",
			success:function(response) {
				$("root>rows>row",response).each(function() {
					$("#feeds_new_0_url").val($("url",this).text());
					$("#feeds_new_0_title").val($("title",this).text());
					$("#feeds_new_0_description").val($("description",this).text());
					$("#feeds_new_0_iframe").attr("src",$("image",this).text());
					$("#feeds_new_0_image").val($("image",this).text());
					$("#feeds_new_0_link").val($("link",this).text());
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
