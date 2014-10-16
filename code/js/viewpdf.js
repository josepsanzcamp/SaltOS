/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2014 by Josep Sanz Campderrós
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

if(typeof(__viewpdf__)=="undefined" && typeof(parent.__viewpdf__)=="undefined") {
	"use strict";
	var __viewpdf__=1;

	// ORIGINAL IDEA FROM pdf.js/examples/text-selection/js/minimal.js
	function viewpdf(data) {
		loadingcontent(lang_view2opening());
		var data="action=viewpdf&"+data;
		$.ajax({
			url:"index.php",
			data:data,
			type:"get",
			beforeSend:function(XMLHttpRequest) {
				make_abort_obj=XMLHttpRequest;
			},
			success:function(response) {
				// CHECK FOR VALID XML STRUCTURE
				if($("root>rows>row",response).length==0) {
					var hash=md5(0);
					unloadingcontent();
					var br="<br/>";
					dialog(lang_error(),lang_view2error()+br+br+lang_view2hash()+hash,{});
					return;
				}
				// CONTINUE
				$("root>rows>row",response).each(function() {
					// GET REQUESTED DATA
					var title=$("title",this).text();
					var hash=$("hash",this).text();
					var data=$("data",this).text();
					// CHECK FOR VALID DATA
					if(!strlen(data)) {
						unloadingcontent();
						var br="<br/>";
						dialog(lang_error(),lang_view2error()+br+br+lang_view2hash()+hash,{});
						return;
					}
					// CONVERT DATA TO BINARY
					var data=base64_decode(data);
					var array=new ArrayBuffer(data.length);
					var bytes=new Uint8Array(array);
					for(var i=0,len=data.length;i<len;i++) bytes[i]=data.charCodeAt(i);
					// CREATE PDFDOC
					PDFJS.workerSrc="lib/pdfjs/pdf.worker.min.js";
					PDFJS.getDocument(array).then(function(pdf) {
						unloadingcontent();
						// CHECK FOR NUMPAGES>0
						if(!pdf.numPages) {
							var br="<br/>";
							dialog(lang_error(),lang_view2error()+br+br+lang_view2hash()+hash,{});
							return;
						}
						// BEGIN OPEN DIALOG
						dialog(lang_view2()+" - "+title);
						var dialog2=$("#dialog");
						$(dialog2).html("");
						// PROGRAM RESIZE EVENT
						$(dialog2).dialog("option","resizeStop",function(event,ui) {
							setIntCookie("saltos_viewpdf_width",$(dialog2).dialog("option","width"));
							setIntCookie("saltos_viewpdf_height",$(dialog2).dialog("option","height"));
						});
						// PROGRAM CLOSE EVENT
						$(dialog2).dialog("option","close",function(event,ui) {
							$(dialog2).dialog("option","resizeStop",function() {});
							$(dialog2).dialog("option","close",function() {});
							$("*",dialog2).each(function() { $(this).remove(); });
						});
						// UPDATE SIZE AND POSITION
						var width=getIntCookie("saltos_viewpdf_width");
						if(!width) width=800;
						$(dialog2).dialog("option","width",width);
						var height=getIntCookie("saltos_viewpdf_height");
						if(!height) height=450;
						$(dialog2).dialog("option","height",height);
						// END OPEN DIALOG
						$(dialog2).dialog("option","position",{ my:"center",at:"center",of:window });
						$(dialog2).dialog("open");
						// PAINT ALL PAGES
						var numPage=0;
						var width=$(dialog2).dialog("option","width")-60;
						var fn=function() {
							numPage++;
							if(numPage<=pdf.numPages) {
								pdf.getPage(numPage).then(function(page) {
									if(!$(dialog2).is(":visible")) return;
									// CALCULATE SIZE
									var viewport=page.getViewport(1);
									var scale=width/viewport.width;
									viewport=page.getViewport(scale);
									// CREATE CANVAS
									var $canvas=$("<canvas></canvas>");
									var canvas=$canvas.get(0);
									var context=canvas.getContext("2d");
									var outputScale=getOutputScale(context);
									canvas.width=(Math.floor(viewport.width)*outputScale.sx)|0;
									canvas.height=(Math.floor(viewport.height)*outputScale.sy)|0;
									canvas.style.width=Math.floor(viewport.width)+'px';
									canvas.style.height=Math.floor(viewport.height)+'px';
									$(dialog2).append($canvas);
									// CREATE DIV
									var $div=jQuery("<div></div>");
									var div=$div.get(0);
									$div.addClass("textLayer")
									$div.css("height",canvas.style.height)
									$div.css("width",canvas.style.width)
									var canvasOffset=$canvas.offset();
									var dialogOffset=$(dialog2).offset();
									var dialogScrollTop=$(dialog2).scrollTop();
									$div.offset({
										top:Math.floor(canvasOffset.top-dialogOffset.top+dialogScrollTop),
										left:Math.floor(canvasOffset.left-dialogOffset.left)
									});
									//~ $div.css("background-color","#336699");
									//~ $div.css("opacity",".5");
									context._scaleX=outputScale.sx;
									context._scaleY=outputScale.sy;
									if(outputScale.scaled) {
										context.scale(outputScale.sx,outputScale.sy);
									}
									$(dialog2).append($div);
									// FIX FOR FIRST PAGE POSITION
									if(numPage==1) $(dialog2).scrollTop(0);
									// RENDER PAGE
									page.getTextContent().then(function(textContent) {
										var textLayer=new TextLayerBuilder({
											textLayerDiv:div,
											viewport:viewport,
											pageIndex:numPage-1
										});
										textLayer.setTextContent(textContent);
										page.render({
											canvasContext:context,
											viewport:viewport
										}).promise.then(fn);
									});
								});
							}
						};
						fn();
					},function(message,exception) {
						errorcontent(0,message);
					});
				});
			},
			error:function(XMLHttpRequest,textStatus,errorThrown) {
				errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
			}
		});
	}

}
