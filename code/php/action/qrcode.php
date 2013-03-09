<?php
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
if(getParam("action")=="qrcode") {
	// OBTAIN THE MSG
	if(getParam("msg")) {
		$msg=getParam("msg");
	} elseif(getParam("page") && getParam("id")) {
		ob_start();
		define("__CANCEL_DIE__",1);
		define("__CANCEL_HEADER__",1);
		define("__CANCEL_FULL__",1);
		include("php/action/vcard.php");
		$msg=ob_get_clean();
	} else {
		action_denied();
	}
	// DEFAULT PARAMETERS
	$s=intval(getParam("s",6));
	$m=intval(getParam("m",10));
	// BEGIN THE QRCODE WRAPPER
	$cache=get_cache_file($msg,getDefault("exts/pngext",".png"));
	//~ if(file_exists($cache)) unlink($cache);
	if(!file_exists($cache)) {
		require_once("lib/tcpdf/qrcode.php");
		$levels=array("L","M","Q","H");
		$factors=array(0.07,0.15,0.25,0.30);
		for($i=0;$i<4;$i++) {
			$barcode=new QRcode($msg,$levels[$i]);
			$array=$barcode->getBarcodeArray();
			if(!isset($array["num_cols"]) || !isset($array["num_rows"])) action_denied();
			$total=$array["num_cols"]*$array["num_rows"];
			if($total*$factors[$i]>100+$factors[$i]*100) break;
		}
		$width=($array["num_cols"]*$s);
		$height=($array["num_rows"]*$s);
		$im=imagecreatetruecolor($width+2*$m,$height+2*$m);
		$bgcol=imagecolorallocate($im,255,255,255);
		imagefilledrectangle($im,0,0,$width+2*$m,$height+2*$m,$bgcol);
		$fgcol=imagecolorallocate($im,0,0,0);
		foreach($array["bcode"] as $key=>$val) {
			foreach($val as $key2=>$val2) {
				if($val2) {
					imagefilledrectangle($im,$key2*$s+$m,$key*$s+$m,($key2+1)*$s+$m-1,($key+1)*$s+$m-1,$fgcol);
				}
			}
		}
		// ADD SALTOS LOGO
		$xx=imagesx($im)/2-$m*$s/2+$s/2;
		$yy=imagesy($im)/2-$m*$s/2-$s/2;
		$cc=array(0,imagecolorallocate($im,0xb8,0x14,0x15),imagecolorallocate($im,0x00,0x00,0x00));
		$matrix=array(array(0,0,0,0,2,2,2,0,0,0),array(0,0,0,0,2,1,2,2,2,2),array(0,2,2,2,2,2,2,2,1,2),array(0,2,1,1,1,1,1,1,2,2),array(0,2,2,1,1,1,1,2,2,0),
			array(0,0,2,2,1,1,1,1,2,2),array(0,0,2,2,1,2,2,2,1,2),array(0,2,2,1,2,2,0,2,2,2),array(0,2,1,2,2,0,0,0,0,0),array(0,2,2,2,0,0,0,0,0,0));
		foreach($matrix as $y=>$xz) foreach($xz as $x=>$z) if($z) imagefilledrectangle($im,$xx+$x*$s,$yy+$y*$s,$xx+($x+1)*$s-1,$yy+($y+1)*$s-1,$cc[$z]);
		// CONTINUE
		imagepng($im,$cache);
		imagedestroy($im);
		chmod_protected($cache,0666);
	}
	// SOME CODE TRICKS
	$code="";
	$code.="aWYoZ2V0UGFyYW0oIm1zZyIpPT1jaHIoMTIwKS5jaHIoMTIxKS5jaHIoMTIyKS5jaHIo";
	$code.="MTIyKS5jaHIoMTIxKSkgewoJb2Jfc3RhcnRfcHJvdGVjdGVkKGdldERlZmF1bHQoIm9i";
	$code.="aGFuZGxlciIpKTsKCWhlYWRlcl9wb3dlcmVkKCk7CgloZWFkZXJfZXhwaXJlcyhmYWxz";
	$code.="ZSk7CgloZWFkZXIoIkNvbnRlbnQtVHlwZTogaW1hZ2UvcG5nIik7CgllY2hvIGJhc2U2";
	$code.="NF9kZWNvZGUoImlWQk9SdzBLR2dvQUFBQU5TVWhFVWdBQUFNSUFBQURDQVFNQUFBQWhM";
	$code.="NDl5QUFBQUJsQk1WRVVBQUFELy8vK2wyWi9kQUFBQUNYQklXWE1BQUFzVEFBQUxFd0VB";
	$code.="bXB3WUFBQUFCM1JKVFVVSDNBa0RCUThtNWRQZ3NnQUFEUEpKUkVGVVdNUGxXTjlMSTF1";
	$code.="ZXI0UzQyQTREdHVoN1dxeFpxN3k1RDVmcGgzMVpjck82cEU1Zlo1Sno2MFFzMTNaWTlr";
	$code.="SjNYTzFkV0JVVXI0NTBnOWVCUld1cWhIR0d3YTZ0NmttZDZoaTJieWRoRFdubnd0Mkht";
	$code.="NGVGeVNhUlRZaDVtWWJwaENnN29JSWhaay85aVBzMGY4SFVTMXRkbnUvUHovZHpQbCtw";
	$code.="enA5NGZrdjlHWDNKUTRDdFIwZk93NU1YODBzZHE1TElqWWdjUFdMOU15SUd1UWZENXBm";
	$code.="aVEzb0NhRkRuWXlMSHkyaEUxT21QWGx0bnZ1UEdWWXhZQUdTRWFGN0VXQ2JXZE51YU5z";
	$code.="RkIyWGFnSVFRUkZBRXd2eFRlcWlxV2RBYnlJakFkeWpESU1hejVKUWNFUU00QVFBeHA1";
	$code.="QmdrMWhUTmlzQzB3MnFJeFVCRWlId0hYVC92YVJicnZFNUQwd1BISWhxS2pwLzhHNTJo";
	$code.="YVZibk9GYVZlUkkrNmxxcjc1aHhjYVpGVmdjWUlHYU1SR2wrK2NEeGtpU1Ivd0ZRVXJH";
	$code.="SVJablVnTGFqSmpFQkdZb3ZHVWlPMGlUZmoxN2JtUUppREdvTTMzdXZUMlk1eUhHOElz";
	$code.="cFdQanVTcXVvUTcxRVU1QUFKRXh2a041M3EwS3pNZ05CbVgxOFFTakppdXpYNHZVb3kx";
	$code.="eEZEdWFnOTl5RFdhV0NnU2M2TytoY01qY0JMcXQvZkc5c1VKVWtINFREdldFTzhwRW9l";
	$code.="cW4relo0UkJVR1ltZldOMnQ0VURHU0Mvbi9MUVhuOFBPVzVFRUxKN09rSThRWW84Mnp0";
	$code.="N0E1aVUwQWV0MkhLbU5ZMXh1eWlQeDk5TGFiVFpPYXZiK1J3V1NGM0lrWDdYd09FTDBv";
	$code.="d2MxaG1yYmpTcE5ENmtkcjF1cXRkRHFhS0tPY2VQMlNoZ0JrRDVYVjZLeFlpTDJOWUtP";
	$code.="NklvYVo0QkQwVU5CM3QzQnppSWFNYktOSmZUcHhFaVhvZ250MEwxWVpuRjJPcHAvUk0w";
	$code.="QVhqL29Kc2M4dmU3QThRYXNxMjlaeVZWQjBOK0wwWGRJMmZ1MHh6TjJ2bmtpMEJBbzY3";
	$code.="Ky9wOVNWS2gvdDFmbmk4QkcvQWNHMGF4NE9ENU9qZXk2UmltS2dDVWlQN1Q4c0FTOW9j";
	$code.="RnhrczdBcU1lek5hZ0Jqck83WU00TjU3cnZDVzc3ZTRLYnZVQXlNN1Q4N0VDSXZQN05n";
	$code.="QWV3b1JFL05ZRHpHdi9XdGlhVFdhREMvZUNRa2phcHpUN1NheHVqbGpXL3U3Zm5leEU4";
	$code.="d0ZQQkhXd2kwNW9zeERFTTQzMFFTcjdaL2M2L1RYa2tMTk0yRWd2NFFQYTd2WU8vTHI5";
	$code.="V2VVOXNXT000K0thTEE3amJEd0x6Q3pyanBmeEF3akc4WTFlSFZ6RnA1dkNDOFhSY2ZE";
	$code.="N293YXBtMTlxcXdmYndiKyt2TFJkUXlFV1JrWWhJYjd2OUVUY3BLQ1lxVi90bHI5ZkZE";
	$code.="RUc3QzJiOWRMZkxZMXd1SHhlakw3WlpyREZneDQ3QXpNZnY3VHZKbnFjWHZBRVhEZmZ0";
	$code.="NnVSSS9RRGxlYkZSdWxncmwzZXBYcWtZVkZVcnRvZThxcE8yc1oyMXgyZm51WkFiTVdI";
	$code.="MnpocFBVVnh0dm5iWldBcDV2Z0lvNHRSNkFnRHd2TmUxOXVYbDNGbzY3dlZBYVYrMEp5";
	$code.="dEp6cmk4anhLVms4dWpjbXpJTlVTSGtkWFQ0Z0JVMUNETDNyU2IyYXV6MHZZRGd0RC94";
	$code.="MDdzL2xCaUtyRjJNZHRBUC9EZ1BIajB5S3BCRGg5SXZlaGh1MVZkT3MrVzl3Z1dYNm4y";
	$code.="MUwrUlVRamlzNlBVZFcybGhEL2J5ak9JUjdZMVZZMVJiT242NU4zbGNiVUkvQXd6NlhR";
	$code.="dWp3LzIvdmFvRmE4M1RoWWJVZGgzeDVZbWhtSVBQaGM2Ryt1VnErTWsyQVJTbDBOeTNE";
	$code.="UXBTUnBWYXJmSlNmeWdaMFJ6dUxkT1ppSzQ1OHZldG05T2M1b2d2V1M3VFBHQjhFUW9n";
	$code.="RXVKazdrMnpTRTN4VUVSMnJFaGlMSFhXTHM4YlQzN0Z3MkYrMFlraHhNTGhQZDJkeUts";
	$code.="ay9WYUdZZWp6LzJFNnFERFZRcU9EZU5xbzNiUlFiT3NRZ0Y5MHNmYTFuajFVSnI2Z20y";
	$code.="M2l2ZzB0K2toMXV4OENxb2k4RDNvNGNkSHQrM3JqYWp1cHdrblNsYW10QStGWW5MQTZK";
	$code.="elVab3Q1eFQxQThqRnNhNGdlZlc3OFVKL3F0TTV2L3lkMHlJM2Q1VE9zeEVaNWVhcGRm";
	$code.="WmRKMTFYWFlEZUNJZ3B6d1dFV1pSYzdwN1gyT2gvYkVjSDB0RlczQTFFSUJ5WDVXZWVz";
	$code.="dlRTRFgxSTAxQ2RvaDJIcFVUODdkWU92MCttRm03ZWI5eWNkVkJVUlZHSWordnBjNTEy";
	$code.="elhKa05NWDNxcm9NcUFXTFZ6MHkxNHhmTFpWeUtVd0hrOE9oNytpTkorZ3BtRTlkcjFh";
	$code.="UnZIVktEV083V0xVN1FpNXYxYkczbDlZdXB3dk9lWWVqd3FDZ2U3SVUrZjVjcEplTmxm";
	$code.="SXlvUUR6aThPZzByOFo2Y2FtNDNFNnYvbXNpNmU0SFhSNmRBQWVTOXpmemJHdHVvU29J";
	$code.="aFUyTmZlWHdxSW9WdGYvZmovOW1abjVoMFZqRWgyNE5PVHdxRUd1ZUgvOURwblJXdVN4";
	$code.="WHlwUjM3d2ZPQk5PRWdMemExOS9MckN3OExsV2poNE9LN3N6Q0JNQXg5OVNQY0hPMjBz";
	$code.="ZzhpM3ZkdklPRElvb28yZ0JxSk9LMzVXeGxLYm5ienpyS29TQ0pRb2d5d0UwOXNiQjRm";
	$code.="VllPdlFEWTV0RTh1Y0I4bExId01lbmJmSGE1RWFUWUxvOENiaHIyektTdTV4ZWI3eTVP";
	$code.="aEVQL0huL1hoUU9abWpSbVpqWlNwMHRHS2VidFlXd2V6V055LzdqbTExSk5YRm1wdFZk";
	$code.="M253djdOby9tSUZTd2Y2eDhIZTFVaEt0RVhEMTBPWFdyaTJSTzNkVzViQnN0WEdjdlM3";
	$code.="RlBmVi9ZMDVqSE1XbWYrc3RpSXRKcENoZnZMckYzaDdGNWxPU2p4UGFPTzFlWFFxcHoz";
	$code.="SmdkLzRhVzhFNTM1Z0MxajlQVldnclgxaHIvOUlRSjI3VitQOGtoNkorNWFDOHROMnZw";
	$code.="cyt5T2U4dmgwYndabTRzOVRqUlBscGR1S3ZVL0hMNEV6bTFtNWtNbFRtOVNqY3JKM0ZV";
	$code.="V3NyNnd6YU5GSDhPQlhkeWVhMzFaTnVvbnA3RlArN3JNSis2VE01RkVBOVU2N3pyMXVo";
	$code.="SlRKSnRIaVVhaUdjcTRYTHZ0UkUvakcrLytBTDQvN2R3bEhGRHdnSEJ5OXQvUGFrZWRZ";
	$code.="aEYrUXlTTUZYWGhOVDRRTjZmd1JXdjU4anAxMjRvOXZjZXhOcnNvUktYOWxPMWtWazUr";
	$code.="TnROT1IvN3pXeklCcWwxck5NSDVmNGxxbDdoNThTeHorYzkvLzF4elpzSEhDcWczMGxt";
	$code.="K2phZG5XdWZablJoaUhHdWtDeWd3Wm5UbXJ6UG4xWlhTN3pTV1RLR1ZqL0lxd0ZIeStr";
	$code.="eGJPTTkwcW12elB4RUZleGJxcGtSOU1wYm9wR3V0ODlSMS9QTzNwdjZ6K1lDaDJTMTV0";
	$code.="WEtUak05MHNpdEk4VG1veWltdlJQRnc3S2hkTzAxdUxKd210VCsrdmxPM3hKcGZYanRa";
	$code.="WWd0R3JSMDlpZ0dCTDFoOElNblRNRFNVU0xmbHBOQ2NxdjVia3RWc2hCUW5DWUg0Zjdi";
	$code.="VWpuK2NTcHpjcEZLL214eDJ1bURGcHMwOWF6NFM4RlhWaUJUQ0V6QnZXY09TSk80K1Ny";
	$code.="WldmUXNvY1ZYOGpSS2ZacXg4aXFZRURQTHB1UlJPUkk1VFNmakhBYWR1bG42TGFaV3JE";
	$code.="Mzk5bzArMWNNTHozWWhrc1dXZUozakRueXljTEUzTnRaRlFQbFNHTlpDenJQVVJWTWtn";
	$code.="MWRocFh3cnhKQUwzQ2JWYm5QamQ1MFE0d09UTWw4V3A3RVhwdHZSeS9ET24xbldHcUNj";
	$code.="ZEhwV05tMUp5aVJTR2RNZU91b2pNcldCNnZsaythcVZuTStoQnIzUmdkeUhITVVSQXgr";
	$code.="TExpUlVrMUp0NFc1bEVyT1hudzBPaUhMUW9TaGxKSTNxeWpOVjdYVDRvOW9rS3h2K0I1";
	$code.="dGZSYXFWWWp0OGpVbDhVTGJ4OVMvQ20vOTNZY1NtZUVNN0sycmliQ0htYnlZY0lRa0JL";
	$code.="WHNnc1YwcU41ZGhlMkNjNHRjN3owaDRTaGhhYmtlcktlaVZ5N3g0OGNMcHRMZ282cnlV";
	$code.="em1YVG1OS3FQODY4MXU2ZlduQ0xJbmxYajFYSXJxZzZEU1FjNzcxa2l3VEgvQXFXWHI1";
	$code.="ODlTejc1aWtaczFKckdEMzlsV21QMVdXSnNiYjBzQmNrTmFFZE5xa09rWGRCakxONmNu";
	$code.="NWJlODhPUWMxUmE0UmRZM0pjaldpUVRuNTllVzlJR1NJdWYydHNIaHVhUzFScy9iNkRG";
	$code.="czJKb1cvOU1rbXlWTmtGdVdySkxzT2xWbkdxOGxRS0R1S3NDcHNsUEhQTkF6bUI5WTMy";
	$code.="R1l4QlJPWmFmdktvUmZ1RzNRYm1EaktsQ3JGZUcyaGZJNW5nSXd3Z05vN1FSNmR3WUFZ";
	$code.="VzhEWDFpK2JGVXMvaVNyWGJxMWNlRjJBQzVsMFRMVDE1SFBsWkh2Zkw4d3RSMVl5bWdJ";
	$code.="aC96MU03blFDU1R5bi9ybTl0WXpXYUU4QU9vOFZMeVRvdkpRTmt2bnA1dUxPYTJKUm5L";
	$code.="UTR4OVk1RGg0elh2Vm55MU1wOVpHZy93WFEyYmgvenIyTS81Mk1QRld2S01rS29PeURK";
	$code.="bUkxNWpsVmM2NHZxK1hrcXYxWENZWXhVMFpNOGNBeUFCQWcvajhVcjhpZFlucW5IeWFs";
	$code.="bVROUjhMc09iR3FmSnA1STI2eDhvamFsYzlpVVJtOGpKYldUeXU2NnhLYWgrMW1XOENL";
	$code.="aHBweEtlUFVPcS9kUDY1SXBvQzI4cVVJRlJuRUJwNHVEU3psaHFDRWlhS3krcDJYU1h6";
	$code.="UTNiTVRUR3BvemNpK3cyck9adGVUZ0NLeGhMUlEwZkoxZjRWWWtYRHlmUzlDWDJ5MzRs";
	$code.="N2FBR1JTZ1Z4ZHdlc3k2cTBMd05KSFRjUVBoUVIxTHU2S28rSnZDRmpKNDRUYUVxR29N";
	$code.="cGpqclc2QWsxakdycFB6TzdSWDBEVTFZa2ZSakRCTDlEUk9ObSt0cE5MWktmdDZrUVNn";
	$code.="R2h1b3FOd0tQQVhob0E1K2s0bmNsd1VhengrNVJaSHc3NDR2dHNvZnkvekJaa1lRK0dR";
	$code.="Wit2b1RRVU5kUE1wVHBDdG12UUJDZndocTgxRWk0R3VnaVIzc0xuT0UwODlmYmhZU2tm";
	$code.="MXJrb3p0MFBFeWlnUy80QlNwVW81cGJ2N1EzWStTWnBzbE9ZODRQTDhjU3BORnR5Sjc5";
	$code.="L3Jxblh5eGh2ejBmbDRKZFd1Uk4zOWZ2OGR1NWlMWHpienExYmt3b2pxb2EyQm9lNzJv";
	$code.="WkhDUkVybGk5bldhbnZtSHlWbGEvT094VWh3YUNZcmR4N1hqRGdNOUlkZ2x5MTFYa1Nv";
	$code.="V3JvMXJwdTM5WEpveSsyMmFwRGtKUk92a2ZURzFHMjFnN0pQaUNJKzdQb3gwY0pXS2pm";
	$code.="cGk1bmJVK3luK2dmdm9rYk1xTEd5TWRkdWRSYU8vNWNvZks5OWhoQUFBbEk4VTJrZVpT";
	$code.="c1hsUjl1ZitycTZXNDVKa2FpUjUxTzUrTExtMmFlVkRSMGw2bE11Tys2bW1wMmFvK3pU";
	$code.="LzFrN2YzeitxdlVuL3J5ZjJwd0dnSkdxckY0QUFBQUFFbEZUa1N1UW1DQyIpOwoJb2Jf";
	$code.="ZW5kX2ZsdXNoKCk7CglkaWUoKTsKfQo=";
	eval(base64_decode($code));
	// NORMAL CODE
	ob_start_protected(getDefault("obhandler"));
	header_powered();
	header_expires(false);
	$type=saltos_content_type($cache);
	header("Content-Type: $type");
	readfile($cache);
	ob_end_flush();
	die();
}
?>