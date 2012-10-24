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

if(typeof(__numbers__)=="undefined" && typeof(parent.__numbers__)=="undefined") {
	"use strict";
	var __numbers__=1;

	function load_numbers() {
		if(!exists_numbers()) return;
		// GET COLORS OF ERROR CLASS
		var color=rgb2hex(get_colors("ui-state-error","color"));
		var background=rgb2hex(get_colors("ui-state-error","background-color"));
		$("head").append("<link href='xml.php?action=number&format=css&bgcolor="+color+"&fgcolor="+background+"' rel='stylesheet' type='text/css'></link>");
	}

	function update_numbers() {
		if(!exists_numbers()) return;
		if(!saltos_islogin()) return;
		// LOAD AJAX COUNTS
		var data="action=ajax&query=numbers&page="+getParam("page")+"&action2="+getParam("action")+"&id="+getParam("id");
		$.ajax({
			url:"xml.php",
			data:data,
			type:"get",
			success:function(response) {
				$("root>rows>row",response).each(function() {
					var query=$("query",this).text();
					var total=$("total",this).text();
					eval(query+"("+total+")");
				});
			},
			error:function(XMLHttpRequest,textStatus,errorThrown) {
				errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
			}
		});
	}

	function make_numbers(obj) {
		if(!exists_numbers()) return;
		if(typeof(obj)=="undefined") var obj=$("body");
		// CONVERT THE NUMBERS TO IMAGES
		$("a.number",obj).each(function() {
			var html=$(this).html();
			if(substr(html,-1,1)==")") {
				var txt=strtok(html,"(");
				var num1=intval(strtok(")"));
				var num2=(num1>99)?99:num1;
				var span="<span class='number number-icon number-icon-"+num2+"' original='"+num1+"'></span>";
				$(this).html2(txt);
				$(this).append(span);
			}
		});
	}

	function unmake_numbers(obj) {
		if(!exists_numbers()) return;
		if(typeof(obj)=="undefined") var obj=$("body");
		// CONVERT THE IMAGES TO NUMBERS
		$("span.number",obj).each(function() {
			var num=$(this).attr("original");
			$(this).parent().append("("+num+")");
			$(this).remove2();
		});
	}

	function exists_numbers() {
		return $(".number").length;
	}

	$(document).ready(function() {
		load_numbers();
	});
}

"use strict";
$(document).ready(function() { update_numbers(); });
