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

if(typeof(__importaciones__)=="undefined" && typeof(parent.__importaciones__)=="undefined") {
	"use strict";
	var __importaciones__=1;

	function import_submit1() {
		if(getParam("action")!="form") return;
		if(getParam("id")=="0") return;
		var id=getParam("id");
		var buscar=$("input[name$=buscar]").val();
		var limit=getParam("limit");
		var offset=getParam("offset");
		var data="page=importaciones&action=import&id="+id+"&buscar="+encodeURIComponent(buscar)+"&limit="+limit+"&offset="+offset;
		loadingcontent();
		$.ajax({
			url:"index.php",
			data:data,
			type:"get",
			success:function(response) {
				unloadingcontent();
				var obj=$(".importdata");
				var offset=$("div",obj).scrollLeft();
				$(obj).html("<div></div>");
				var width=$(obj).width();
				$(obj).html("<div>"+response+"</div>");
				$("div",obj).css("overflow","auto");
				$("div",obj).width(width);
				$("div",obj).scrollLeft(offset);
				$("input[name$=buscar]").trigger("focus");
			},
			error:function(XMLHttpRequest,textStatus,errorThrown) {
				unloadingcontent();
				errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
			}
		});
	};

	function import_pager(info,page,pages,first,previous,next,last) {
		$(".infopager").html(info);
		var options="";
		for(var i=1;i<=pages;i++) {
			var selected=(i==page)?"selected='selected'":"";
			options+="<option value='"+i+"' "+selected+">"+i+"</option>";
		};
		$("select[id$=selectpager]").html(options).trigger("refresh");
		if(!first) $("a[id$=firstpager]").addClass("ui-state-disabled");
		if(!previous) $("a[id$=previouspager]").addClass("ui-state-disabled");
		if(!next) $("a[id$=nextpager]").addClass("ui-state-disabled");
		if(!last) $("a[id$=lastpager]").addClass("ui-state-disabled");
		if(first) $("a[id$=firstpager]").removeClass("ui-state-disabled");
		if(previous) $("a[id$=previouspager]").removeClass("ui-state-disabled");
		if(next) $("a[id$=nextpager]").removeClass("ui-state-disabled");
		if(last) $("a[id$=lastpager]").removeClass("ui-state-disabled");
	}

	function import_buscar() {
		setParam("offset",0);
		import_submit1();
	}

	function import_limpiar() {
		$("input[name$=buscar]").val("");
		setParam("offset",0);
		import_submit1();
	}

	function import_first() {
		import_page1($("select[id$=selectpager]").prop("selectedIndex",0).val());
	}

	function import_previous() {
		import_page1($("select[id$=selectpager]").prop("selectedIndex",$("select[id$=selectpager]").prop("selectedIndex")-1).val());
	}

	function import_next() {
		import_page1($("select[id$=selectpager]").prop("selectedIndex",$("select[id$=selectpager]").prop("selectedIndex")+1).val());
	}

	function import_last() {
		import_page1($("select[id$=selectpager]").prop("selectedIndex",$("select[id$=selectpager]").prop("options").length-1).val());
	}

	function import_page1(num) {
		setParam("offset",(intval(num)-1)*intval(getParam("limit")));
		import_submit1();
	}

	function import_limit1(num) {
		setParam("limit",intval(num));
		setParam("offset",0);
		import_submit1();
	}

}

"use strict";
$(function() {
	if(getParam("action")!="form") return;
	if(getParam("id")==0) return;
	import_submit1();
});
