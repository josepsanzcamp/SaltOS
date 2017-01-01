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

if(typeof(__addtextos__)=="undefined" && typeof(parent.__addtextos__)=="undefined") {
	"use strict";
	var __addtextos__=1;

	function add_textos_textarea(from,to) {
		var objfrom=$("select[name$="+from+"]");
		var id=$(objfrom).val();
		if(!id) return;
		var data="action=ajax&query=textos&id="+id;
		$.ajax({
			url:"index.php",
			data:data,
			type:"get",
			success:function(response) {
				var objto=$("textarea[name$="+to+"]");
				var extra=$(objto).val().length>0?"\n\n":"";
				var texto=response["rows"][0]["texto"];
				$(objto).val($(objto).val()+extra+texto);
				$(objfrom).val("");
			},
			error:function(XMLHttpRequest,textStatus,errorThrown) {
				errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
			}
		});
	}

	function add_textos_ckeditor(from,to) {
		var objfrom=$("select[name$="+from+"]");
		var id=$(objfrom).val();
		if(!id) return;
		var data="action=ajax&query=textos&id="+id;
		$.ajax({
			url:"index.php",
			data:data,
			type:"get",
			success:function(response) {
				var objto=$("textarea[name$="+to+"]");
				var texto=response["rows"][0]["texto"];
				objto.ckeditorGet().insertHtml(nl2br(texto));
				$(objfrom).val("");
			},
			error:function(XMLHttpRequest,textStatus,errorThrown) {
				errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
			}
		});
	}

}
