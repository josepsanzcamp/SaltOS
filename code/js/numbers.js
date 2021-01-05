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

if(typeof(__numbers__)=="undefined" && typeof(parent.__numbers__)=="undefined") {
	"use strict";
	var __numbers__=1;

	function update_numbers() {
		var page=getParam("page");
		var action=getParam("action");
		var id=getParam("id");
		if(!isset(page) || !isset(action) || !isset(id)) return;
		var data="action=ajax&query=numbers&page="+page+"&action2="+action+"&id="+id;
		$.ajax({
			url:"index.php",
			data:data,
			type:"get",
			success:function(response) {
				$(response["rows"]).each(function() {
					eval(this["query"]+"("+this["total"]+")");
				});
			},
			error:function(XMLHttpRequest,textStatus,errorThrown) {
				errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
			}
		});
	}

	function make_numbers(obj,num) {
		unmake_numbers(obj);
		var clase="none d-none";
		num=max(min(num,99),0)
		if(num>0) clase="number"+Math.ceil(Math.log10(num+1));
		var span="<span class='number "+clase+" badge badge-danger'>"+num+"</span>";
		$(obj).append(span);
	}

	function unmake_numbers(obj) {
		if(typeof(obj)=="undefined") var obj=$("body");
		// CONVERT THE IMAGES TO NUMBERS
		$("span.number",obj).remove();
	}

}

"use strict";
$(function() {
	update_numbers();
});
