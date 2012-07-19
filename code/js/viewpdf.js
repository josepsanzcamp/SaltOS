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

if(typeof(__viewpdf__)=="undefined" && typeof(parent.__viewpdf__)=="undefined") {
	"use strict";
	var __viewpdf__=1;

	function viewpdf(data) {
		loadingcontent(lang_view2opening());
		data="action=viewpdf&"+data;
		$.ajax({
			url:"xml.php",
			data:data,
			type:"get",
			beforeSend:function(XMLHttpRequest) {
				jqxhr=XMLHttpRequest;
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
					data=base64_decode(data);
					var array=new ArrayBuffer(data.length);
					var bytes=new Uint8Array(array);
					for(var i=0,len=data.length;i<len;i++) bytes[i]=data.charCodeAt(i);
					// CREATE PDFDOC
					PDFJS.disableWorker=true;
					PDFJS.getDocument(array).then(function(pdf) {
						// CHECK FOR NUMPAGES>0
						if(!pdf.numPages) {
							unloadingcontent();
							var br="<br/>";
							dialog(lang_error(),lang_view2error()+br+br+lang_view2hash()+hash,{});
							return;
						}
						// BEGIN OPEN DIALOG
						unloadingcontent();
						dialog(lang_view2()+" - "+title);
						var dialog2=$("#dialog");
						$(dialog2).html2("");
						// PROGRAM RESIZE EVENT
						$(dialog2).dialog("option","resizeStop",function(event,ui) {
							setIntCookie("saltos_viewpdf_width",$(dialog2).dialog("option","width"));
							setIntCookie("saltos_viewpdf_height",$(dialog2).dialog("option","height"));
						});
						// PROGRAM CLOSE EVENT
						$(dialog2).dialog("option","close",function(event,ui) {
							$(dialog2).dialog("option","resizeStop",function() {});
							$(dialog2).dialog("option","close",function() {});
							$("*",dialog2).each(function() { $(this).remove2(); });
						});
						// UPDATE SIZE AND POSITION
						var width=getIntCookie("saltos_viewpdf_width");
						if(!width) width=800;
						$(dialog2).dialog("option","width",width);
						var height=getIntCookie("saltos_viewpdf_height");
						if(!height) height=450;
						$(dialog2).dialog("option","height",height);
						// END OPEN DIALOG
						$(dialog2).dialog("option","position","center");
						$(dialog2).dialog("open");
						// PAINT ALL PAGES
						var numPage=0;
						var width=$(dialog2).dialog("option","width")-60;
						var fn=function() {
							numPage++;
							if(numPage<=pdf.numPages && $(dialog2).is(":visible")) {
								pdf.getPage(numPage).then(function(page) {
									var div=document.createElement('div');
									div.className='textLayer';
									$(dialog2).append(div);
									var viewport=page.getViewport(1);
									var scale=width/viewport.width;
									viewport=page.getViewport(scale);
									var canvas=document.createElement("canvas");
									var context=canvas.getContext("2d");
									canvas.width=viewport.width;
									canvas.height=viewport.height;
									$(dialog2).append(canvas);
									var textLayer=new TextLayerBuilder(div);
									var renderContext={
										canvasContext:context,
										viewport:viewport,
										textLayer:textLayer
									};
									page.render(renderContext).then(fn);
								});
							}
						};
						fn();
					});
				});
			},
			error:function(XMLHttpRequest,textStatus,errorThrown) {
				errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
			}
		});
	}

	// COPIED FROM PDF.JS/VIEWER.JS

	// optimised CSS custom property getter/setter
	var CustomStyle = (function CustomStyleClosure() {

		// As noted on: http://www.zachstronaut.com/posts/2009/02/17/
		//							animate-css-transforms-firefox-webkit.html
		// in some versions of IE9 it is critical that ms appear in this list
		// before Moz
		var prefixes = ['ms', 'Moz', 'Webkit', 'O'];
		var _cache = { };

		function CustomStyle() {
		}

		CustomStyle.getProp = function get(propName, element) {
			// check cache only when no element is given
			if (arguments.length == 1 && typeof _cache[propName] == 'string') {
				return _cache[propName];
			}

			element = element || document.documentElement;
			var style = element.style, prefixed, uPropName;

			// test standard property first
			if (typeof style[propName] == 'string') {
				return (_cache[propName] = propName);
			}

			// capitalize
			uPropName = propName.charAt(0).toUpperCase() + propName.slice(1);

			// test vendor specific properties
			for (var i = 0, l = prefixes.length; i < l; i++) {
				prefixed = prefixes[i] + uPropName;
				if (typeof style[prefixed] == 'string') {
					return (_cache[propName] = prefixed);
				}
			}

			//if all fails then set to undefined
			return (_cache[propName] = 'undefined');
		}

		CustomStyle.setProp = function set(propName, element, str) {
			var prop = this.getProp(propName);
			if (prop != 'undefined')
				element.style[prop] = str;
		}

		return CustomStyle;
	})();

	var TextLayerBuilder = function textLayerBuilder(textLayerDiv) {
		this.textLayerDiv = textLayerDiv;

		this.beginLayout = function textLayerBuilderBeginLayout() {
			this.textDivs = [];
			this.textLayerQueue = [];
		};

		this.endLayout = function textLayerBuilderEndLayout() {
			var self = this;
			var textDivs = this.textDivs;
			var textLayerDiv = this.textLayerDiv;
			var renderTimer = null;
			var renderingDone = false;
			var renderInterval = 0;
			var resumeInterval = 500; // in ms

			// Render the text layer, one div at a time
			function renderTextLayer() {
				if (textDivs.length === 0) {
					clearInterval(renderTimer);
					renderingDone = true;
					return;
				}
				var textDiv = textDivs.shift();
				if (textDiv.dataset.textLength > 0) {
					textLayerDiv.appendChild(textDiv);

					if (textDiv.dataset.textLength > 1) { // avoid div by zero
						// Adjust div width to match canvas text
						// Due to the .offsetWidth calls, this is slow
						// This needs to come after appending to the DOM
						var textScale = textDiv.dataset.canvasWidth / textDiv.offsetWidth;
						CustomStyle.setProp('transform' , textDiv,
							'scale(' + textScale + ', 1)');
						CustomStyle.setProp('transformOrigin' , textDiv, '0% 0%');
					}
				} // textLength > 0
			}
			renderTimer = setInterval(renderTextLayer, renderInterval);

			// Stop rendering when user scrolls. Resume after XXX milliseconds
			// of no scroll events
			var scrollTimer = null;
			function textLayerOnScroll() {
				if (renderingDone) {
					window.removeEventListener('scroll', textLayerOnScroll, false);
					return;
				}

				// Immediately pause rendering
				clearInterval(renderTimer);

				clearTimeout(scrollTimer);
				scrollTimer = setTimeout(function textLayerScrollTimer() {
					// Resume rendering
					renderTimer = setInterval(renderTextLayer, renderInterval);
				}, resumeInterval);
			}; // textLayerOnScroll

			window.addEventListener('scroll', textLayerOnScroll, false);
		}; // endLayout

		this.appendText = function textLayerBuilderAppendText(text,
																													fontName, fontSize) {
			var textDiv = document.createElement('div');

			// vScale and hScale already contain the scaling to pixel units
			var fontHeight = fontSize * text.geom.vScale;
			textDiv.dataset.canvasWidth = text.canvasWidth * text.geom.hScale;
			textDiv.dataset.fontName = fontName;

			textDiv.style.fontSize = fontHeight + 'px';
			textDiv.style.left = (10 + text.geom.x) + 'px';
			textDiv.style.top = (10 + text.geom.y - fontHeight) + 'px';
			textDiv.textContent = PDFJS.bidi(text, -1);
			textDiv.dir = text.direction;
			textDiv.dataset.textLength = text.length;
			this.textDivs.push(textDiv);
		};
	};

}
