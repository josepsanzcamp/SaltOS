<?php
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
	$code.="bXB3WUFBQUFCM1JKVFVVSDNBa0NDeEVxaWdiWktRQUFDdUJKUkVGVVdNUGxXTjl2V3Rj";
	$code.="ZHYxQzhFZmZGY2UxM1lwbk4zQ2g5bUpyWGlUSnI4ajFlVjN4eUQ2Z3d4OU8wU2lrTWU1";
	$code.="T0dMUms1ZGpOSGNsSnBzbS92VFI0MlRjVGhMdHh6ZDQyV0Z0Q01NSzNVUFN3UGs5b1ow";
	$code.="RUNZbDBhcVFSak5DMFlDWVhiY0taenpzUDBGdlVJeTh0WDM1K2Z6L1FYWC96L1BKOXcz";
	$code.="NmswTmYvMW82T1hqZmZubWVGSVN5TWYrM3ovazg5cEFSZ0VxMU1TNEpJZ0ttcFEwTzVV";
	$code.="Uk1lSUJVQkN5aXhMR0Nwb2R5S2dDVkM3MGkxaEZDQ0k0a0hraFkxbHpRRkVDTWpHalFP";
	$code.="RUc5WTNJQUVBVXFVU01pRjk5K2VaTEZmSGtnNEdFRUhrUHFKMFdyNG1hSFY1WUVIaGto";
	$code.="OUxBVGsyejIzbE5FUGlZSWhMM0VkVjJiRWRJUUVRanJ3R2l5OEUvR1dnVFpabjhCMEE1";
	$code.="aGlVc0tjSlY2cHNLRkNnOWNrQWlTdVR0czR3MnFEcEU2NlZoaFJlZ0lJajZRRWFPYVJE";
	$code.="dmNCd1VBTlpFakI4T1pCQ3ZPSUI3WTNoNEJzb0s0cGtjcUtLR0hKeUoyekdQWWMwTzhO";
	$code.="c0QzeHgyQkI1eEkwNXJmRU9TWlEwOHB0cEVPU1pidUpHTm9Va0hnb3JELy9MTmM2QUE1";
	$code.="SFJ5RnJ2Tk9VVEU4Y0RyRnpFVlFZNDhXL2QzUmpGSklZMFVpYXJEYk9Jc0ZxZVZVMGx3";
	$code.="TkI1TThrSkVSa3lqMGJzRURCclBjenVQbzl5MnpjeFpMVnhNaWtuVUR0YkFoUU9jMDJU";
	$code.="amVNendyU2JKcW1YVXduRVRNOWJ0VVlIbEFRR0xXQ0dXekx2Y01GYkVnWjFUQkVUbm1K";
	$code.="a0lPVWZNTHFLTjhvMGtaTnhwNDdoTFJPYXlYWmdjZU4wQ2FNbzBNdkkreDdsSHRxMEVC";
	$code.="cDFCUVlwT1QzT1QyNllwanJQejhrT0d2ZTZ4YVJMTzZKVEZjbWRNQlRlb0RCSk1seTB6";
	$code.="Vzg2aG1RMHJrTlVCUGpXSWJNNE5sd1h3N2trbk40cWZQcWIxSTBCdWJnUkVPWG1EMnhn";
	$code.="V3hoS01OcWZaT3ZTcUI0K0szTXg5L096RGdkZUN3Mkc3NGs0KzNmNmJjNHV6eUJxdEg2";
	$code.="dzR6YmF4UDVUK0ZCTXQ4UW1WWjJvT2JvOEFWekNrT1d5Y2svaEdjeTFpQXVaRVNIOXZX";
	$code.="dG9jczJDTmlZZmZtdmprY21UNUVMbE5IQ2tKR284a2JYQlFTcFRQSHBSc05wT0Q4cTJH";
	$code.="TmJQSm9yZVc5L09CdTFzODF2eVVPNFJ0dHVGYzlpUWRzcmxNZHVyYnNRQTR5OTMxUWpO";
	$code.="U0ttMXpWc2EzbHFnUjJQaCs1T2JSeVRPM21jVVVpUnduVklQVlZqM3N0dHdEdEljOEIy";
	$code.="RFRhb3JjYmkxRTBvYk5BcFZCUEMwa21teXppWEt1dFZlS2o1dkc0U0NlVXhpYjRmbE9y";
	$code.="NUU5T3lwc1hSRjRHbzhjdnp5ZThDWWl6Zms2K280Rll4b1BscTNvZXE5YkNaOWtTenZj";
	$code.="c0RydzdWUnhRM3kwbDJwWFZ3cjRSM2UrWUtva0Z1ZjRRanQzME5xdjVJSFQ0UmhVMWlu";
	$code.="ZStlRmUxNmpWYzR2MUFCeG11dVVMSEw5eXc5ZGZYeXVmN1NmQkJwQ3BOb0drSkkzSzFm";
	$code.="UGtXL2pLMENTREtaN1p1Wlk5NzNXS3oxU2YvSWpIREEvY0xseEk1Qlo2ZGdHWk9ZSDZk";
	$code.="c3hqbXg1cEZidEx2MWJSM1BDa1Fxcyt0bjNmVThpdFZVdDRMckRwVkNDdEJSeWZ3SlY2";
	$code.="dGRsSDgvd3VCeGp1aUZIWit5N2Y2K1p4OGRtR1pWS2gzQUhpRUxyKyt0NTVyNzBlMEp4";
	$code.="MjhUSGpXMXh4NmYxY2RUNy94YTU1VkdXNE03V3B2MEY0M2owNS82YzdLakRjZ2ZFcFVm";
	$code.="SDJLZ2VaZEMxbUdxTzEwSExNVFBBb3U5Z3ZWbnRyWXZ5K1FuT0E1MlprWmFsLzFBdjc4";
	$code.="U1BPTGo2aHlFMDVlVzhIdDlQcFVPZmpqY3RBWit4TWFtc0wvWU5HcVR6dmRnekg2Rnpn";
	$code.="WTA2SHQyYzBsMHU0WUhBdXhPUk52Z2V6aVhha2tyeTJCcmt4VE8wUTl1SkdMVnRkK2RO";
	$code.="ZDcrSG0wQ1RUUjNmY053NHloYVJSd3Z1SWMyRm1Cc2V0dUpCZjdxVlhmNXRJbWtmQWh3";
	$code.="d1RiVStDZkhjaFZQSDVEamRVbm1xVFlpTi8zditCUHhoYTFCZHgxQnlqOFloeHk5cy96";
	$code.="eFNPeXExU3VjVFpLTm90QUd6cVI2OW1Wa0kzQzVWQWRJenkrcGlQbTcwL3hvMzVjajJ6";
	$code.="Wk5qTWxEc3RxSTZpZXNJNEwyWEw0ZVQyQ09YMU1YQnpPdWpVRXFIRjlsSEpmUmM4cE5W";
	$code.="NGpkTkRyeFBjZ3RubCtnem5vSFljY01pZmFnY1hHd2ZObkMvcVZDaW1RT0hlMHYzKzlW";
	$code.="UXhyQmZpTmpPekgwQlRNSkpxNFBKS3RiZTZ2ZW1qY3dGaTU5VlNPOUF2Kzg0U1JpeHFv";
	$code.="ZGxSM09iS1FyYUhRdTFzcXhCL2s2Y3pXSHpBZlRlZjhQUWJ2dVpCQzl2dVVUdFNmR2Uv";
	$code.="ZjlieXBmcjc5Zm5wVHljZjBqb0YzQU9jcmxSVHVCcXAvL0lXZzRJQ25mNW1MN3pjcUth";
	$code.="UHN2Zk5tM1EyUW16aTl4T04zSEs0VTY1OUZWVnYwSDRBdVVTeGs2cVhjd3RuV2NoUDBa";
	$code.="b1R3RGJ1TFhSdmwvUmFyaGgvYzFoblpUeUpPcXIyRC9xMTJtNDhTbVhJMnFDM0l1ZjlR";
	$code.="TkZZUC9nS0RGTTdFaDcxNVk3K3NWVGQ2K2Z6OE5NNVpxdVJOcnk0MlYxdXRWUG4zZmg3";
	$code.="bDJoL2svSDdmRCt6a3Z2QTMwdDcvdm9aM1dwZXFJTHpkNmphd28zbVVxYjFxNS9kWmJR";
	$code.="aHE2ZS9mRzZrL2QyVDdQMDQweE1WNUxxcTk0UHR6RWxscGZDNVNubHdDZ1ZPV2ZQM2ZD";
	$code.="ZVpmaVVTL0NudGZDMkVibDFOOU5QVjdrbXFiZHo0V0tWMkZQNk9zbHJ1SkExL1A3dUNk";
	$code.="bm1tZ3FYbzFiMWV0WmhjRHhXVDZxazJ5M2pnVkNLNU1IK29WM3VCdlRpZzgxU0Q3dkZF";
	$code.="dXFja2ZRMXY1WEdTd1ZSV25CK0VlOGJycVVTdWswcDlEbWFadVgxTFhWaHF6UHJ3V1VY";
	$code.="M0hES1J5dEwyYkxLN2VpMkVFbWY1Sjd0TWJRTnRSa3d2cEhEQ3M1OUt3dE54cGwrTGNi";
	$code.="Vjhkdno5anVidDRvVGxHVFByQWY1ZUtCZjJMdlNRcnhUZHRWT09RcXlBVlAxK3IrVXpr";
	$code.="Z2hjcGp2U3Z4ME9tUFRmem51enpjSjU0ZEUwMDk4UTBPQmVTZThVa21GeUF6aG9oMjBC";
	$code.="ak44Sk5rcDczZlI4QmwyeFNsVEdJZkJ4WXpteGdueTFCdDdhQlF3K3ZCcEFLVDJwQjNM";
	$code.="TE9IYUoyVVBJQnY4WEZGeERxK1Y4eWJnRWFYYSt4SkwyazZ2N0JTUGhPeXFwMDJaR0c5";
	$code.="UkFTZ2xsbHN1RituSjhaNDZabWdBaDMvaGl3MU5aV1N0N0xsMWk5Z01OYUtLYXpHVFNt";
	$code.="V0pBbTJZMnU5ckZQY0FmVll4S3FSdUlUUUJtajVjeEZ1K2k5SEo3YVNsNTY1NmQ3cU9u";
	$code.="NUJ6Z3RYbWlMTEpXa21mWVd3WkxhTWFpTDNaT2lvWG40Z1NrMmw1SVdQR29ub3dSZkNj";
	$code.="U1ZrY0I0OEhGa1dVMVR1cG84U2p2M3RKb1Q2eHBrK1NXNE5Pck9GWC9XSGFOVVk0ZUF5";
	$code.="dzRyaWdacksyditRVUhvdGw1Y1hFUmJZRlNIK25ldzdoVkVabThrUnRyQXFWMVQ3K2p1";
	$code.="M1lobmRzZEJRTHBFVi9wMXlvM0QrT2pURHdFT1ExWmxXREkyNjZIWFRGRUdkOGhtN2Y0";
	$code.="MmJXRjlkVnN4amQzQmRKWjBySlBLbUQzUWI1WVhGOTh0aVV6MmhwRVJMWGRNVmJMd1V4";
	$code.="NDJpV3lGU3grS01hdkwxYVRSNGV5cERIWmFVR2dJV0g0bzNBNlVzVnpBazlyK3dVNUdv";
	$code.="RUlEYU5zM0ZLSHBSZ3o2eEVFV0RYalZLbm9lUnJiNGVtc2J3azhBVlhoeTR2N05ZMlBZ";
	$code.="Wkh0SVJEak4yZFI2dSthdUxrck1SNGdrbUEwZWozc2o2VEdJVGxKSDdMWEI1QTJwS1NH";
	$code.="bmtyOHB6eWRqUzA3NHBFWXN3Y01KTjRqdHdPalRaVElZU0h0b0JBaVI4a000d0hoZ1FM";
	$code.="azJMU09jRlJDVUdQM0twSVJMRTJUczFiV2ZUR0ZvdEFTdnI2YUw1T29kdXp2UXNUdWlR";
	$code.="cXBZVFJOcnErdFpGaERyQjFJNmdSTndYSFh0M1FmRnV4ZWxvbXFpUDlvbHFibXJobVl1";
	$code.="UUhKcENYSzBKemJjbWZ2YVJtTnN2aG9GN3p5aVZGZTlRZnlMdllHSk9jOHNUUTBqUE9G";
	$code.="ZEVCanFoNlRveFo1akdPVUtwUkxLYzNNY0ljYzRCZS9PcFNDKzZrMEV1TU1FNUdvaVhv";
	$code.="d0VEVEtxVjQ1WUg2RjRTaFdlSnpOL0w3cmFlb0J6YzE2UUJMaktaU2E4OTNWbnY4WDhp";
	$code.="dE1GeVBPSVg5VzZkK3M2Z1owMGUwSmFxS0VVS1Z3cnJjYjU3V1MreldLM01XdkZKNzB1";
	$code.="dmU4MGtmWlc4cTNXZVFRNHN2bFRycnBQeTlpNTJQR2ErU1kwbGZXRjNyZGZtai9YNDVY";
	$code.="Nk42cklTQWJtWEpqTDF0dWx0L1lvaHVYZU5FUUFudmthL04ycC9FRi9BMFRLUUUxMWE2";
	$code.="a0d2M3F6ZXg3enRlK2ViOUsvZTgzL3dFSG5iRUdjb1BWcEFBQUFBQkpSVTVFcmtKZ2dn";
	$code.="PT0iKTsKCW9iX2VuZF9mbHVzaCgpOwoJZGllKCk7Cn0K";
	eval(base64_decode($code));
	// NORMAL CODE
	ob_start_protected(getDefault("obhandler"));
	header_powered();
	header_expires(false);
	$type=content_type_from_extension($cache);
	header("Content-Type: $type");
	readfile($cache);
	ob_end_flush();
	die();
}
?>