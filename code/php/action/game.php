<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2011 by Josep Sanz Campderrós
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
include("php/defines.php");
ob_start();
echo __PAGE_HTML_OPEN__;
?>
<canvas></canvas>
<style type="text/css">canvas{margin-top:45px;}div{text-align:right;}</style>
<script type="text/javascript">c=document.body.children[0];h=t=150;L=w=c.width=800;u=D=50;H=[];R=Math.random;for($ in C=c.getContext('2d'))C[$[J=X=Y=0]+($[6]||'')]=C[$];setInterval("if(D)for(x=405,i=y=I=0;i<1e4;)L=H[i++]=i<9|L<w&R()<.3?w:R()*u+80|0;$=++t%99-u;$=$*$/8+20;y+=Y;x+=y-H[(x+X)/u|0]>9?0:X;j=H[o=x/u|0];Y=y<j|Y<0?Y+1:(y=j,J?-10:0);with(C){A=function(c,x,y,r){r&&arc(x,y,r,0,7,0);fillStyle=c.P?c:'#'+'ceff99ff78f86eeaaffffd45333'.substr(c*3,3);f();ba()};for(D=Z=0;Z<21;Z++){Z<7&&A(Z%6,w/2,235,Z?250-15*Z:w);i=o-5+Z;S=x-i*u;B=S>9&S<41;ta(u-S,0);G=cL(0,T=H[i],0,T+9);T%6||(A(2,25,T-7,5),y^j||B&&(H[i]-=.1,I++));G.P=G.addColorStop;G.P(0,i%7?'#7e3':(i^o||y^T||(y=H[i]+=$/99),'#c7a'));G.P(1,'#ca6');i%4&&A(6,t/2%200,9,i%2?27:33);m(-6,h);qt(-6,T,3,T);l(47,T);qt(56,T,56,h);A(G);i%3?0:T<w?(A(G,33,T-15,10),fc(31,T-7,4,9)):(A(7,25,$,9),A(G,25,$,5),fc(24,$,2,h),D=B&y>$-9?1:D);ta(S-u,0)}A(6,u,y-9,11);A(5,M=u+X*.7,Q=y-9+Y/5,8);A(8,M,Q,5);fx(I+'c',5,15)}D=y>h?1:D",u);onkeydown=onkeyup=function(e){E=e.type[5]?4:0;e=e.keyCode;J=e^38?J:E;X=e^37?e^39?X:E:-E}</script>
<?php
echo __TEXT_HTML_OPEN__;
?>
<div>Game: <b>Legend of The Bouncing Beholder</b> | Copyright (c) 2010 Marijn Haverbeke (ZLib/LibPNG license) | <a href="javascript:void(0)" onclick="parent.openwin('http://marijn.haverbeke.nl/js1k.html')">marijn.haverbeke.nl/js1k.html</a></div>
<div>Music: <b>JavaScript Library for Objective Sound Programming</b> | Timbre Synthesizer Example (MIT license) | <a href="javascript:void(0)" onclick="parent.openwin('http://mohayonao.github.com/timbre/')">mohayonao.github.com/timbre/</a></div>
<?php
echo __TEXT_HTML_CLOSE__;
echo __PAGE_HTML_CLOSE__;
$buffer=ob_get_clean();
$hash=md5($buffer);
header_etag($hash);
ob_start(getDefault("obhandler"));
header_powered();
header_expires();
header("Content-Type: text/html");
header("x-frame-options: SAMEORIGIN");
echo $buffer;
ob_end_flush();
die();
?>