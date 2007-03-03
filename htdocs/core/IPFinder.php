<?php
if (@V2EX_BABEL != 1) {
	die('<strong>Project Babel</strong><br /><br />Made by <a href="http://www.v2ex.com/">V2EX</a> | software for internet');
}

function ip_get_str(&$fp) {
	while (1) {
		$c = fgetc($fp);
		if (ord($c[0]) == 0) break;
		$str .= $c;
	}
	return $str;
}

function ip_ip2buf(&$ipadd) {
	$array = explode('.', $ipadd);
	$int[0] = chr($array[3]);
	$int[1] = chr($array[2]);
	$int[2] = chr($array[1]);
	$int[3] = chr($array[0]);
	$int2 = $int[0] . $int[1] . $int[2] . $int[3];
	return $int2;
}

function ip_buf2ip(&$int) {
	$array[0] = ord($int[3]);
	$array[1] = ord($int[2]);
	$array[2] = ord($int[1]);
	$array[3] = ord($int[0]);
	$int2 = implode('.', $array);
	return $int2;
}

function ip_buf2off(&$str) {
	$set = ord($str[0]) + (ord($str[1]) * 256) + (ord($str[2]) * 256 * 256);
	return $set;
}

function ip_count(&$buf) {
	$ipcount = ord($buf[0]) + (ord($buf[1])*256) + (ord($buf[2]) * 256 * 256) + (ord($buf[3]) * 256 * 256 * 256);
	return $ipcount;
}

function ip_tell($ip) {
	$fp = fopen(BABEL_IP_DB_LOCATION, 'r');
	$start_buf = fread($fp, 4);

	$start_off = ip_count($start_buf);
	$end_buf = fread($fp, 4);
	$end_off = ip_count($end_buf);
	$ip_off = ip_count(ip_ip2buf($ip));
	$record_count = (floor(($end_off - $start_off) / 7) + 1);

	$oip = ip_ip2buf($ip);
	$oip2 = ip_count($oip);
	$x1 = 0;
	$x2 = $record_count;

	do {
		$x3 = floor(($x2 + $x1) / 2);
		$ob_off = $start_off + $x3 * 7;
		fseek($fp, $ob_off, SEEK_SET);
		$ip_buf = fread($fp, 4);

		$buf = fread($fp,3);
		$ip_buf2 = fread($fp,4);
		$ip_off = ip_count($ip_buf);
		$ip_off2 = ip_count($ip_buf2);
		if (($ip_off < $oip2) && ($ip_off2 > $oip2)) break;
		if ($ip_off > $oip2) $x2 = $x3;
		elseif ($ip_off < $oip2) $x1 = $x3;
		else break;
	} while(1);

	$off = ip_buf2off($buf);
	fseek($fp, $off, SEEK_SET);
	$ip_buf = fread($fp, 4);
	$flag = ord(fread($fp, 1));

	switch ($flag) {
		case 2:
			$buf = fread($fp,3);
			$local = ip_get_str($fp);
			$off = ip_buf2off($buf);
			fseek ($fp, $off, SEEK_SET);
			$country = ip_get_str($fp);
			break;
		case 0:
			$country = '未知地址';
			$local = '';
			break;
		case 1:
			$buf = fread($fp,3);
			$off = ip_buf2off($buf);
			fseek($fp, $off, SEEK_SET);
			$flag = ord(fread($fp,1));
			if ($flag !== 2) {
				fseek($fp, $off, SEEK_SET);
				$country = ip_get_str($fp);
				$local = ip_get_str($fp);
			} else {
				$buf = fread($fp,3);
				$country_off = ip_buf2off($buf);
				$flag=ord(fread($fp,1));
				if ($flag == 1 || $flag == 2) {
					$buf = fread($fp, 3);
					$off = ip_buf2off($buf);
					fseek($fp, $off, SEEK_SET);
					$local = ip_get_str($fp);
				} else {
					@fseek($fp, $off+4, SEEK_SET);
					$local = ip_get_str($fp);
				}
				fseek($fp, $country_off, SEEK_SET);
				$country = ip_get_str($fp);
			}
			break;
		default:
			@fseek($fp, $off + 4, SEEK_SET);
			$country = ip_get_str($fp);
			$local = ip_get_str($fp);
			break;
		}


	$ip2 = ip_buf2ip($ip_buf);

	fclose($fp);
	
	if (bin2hex($local) == '0211') {
		$local = '';
	}
	
	if (strtolower(trim($local)) == 'cz88.net') {
		$local = '';
	}
	
	if ($local == '') {
		$result = array(
						'ip' => $ip2,
						'country' => trim(mb_convert_encoding($country, 'UTF-8', 'GBK')),
						'local' => ''
					);
	} else {
		
		$result = array(
						'ip' => $ip2,
						'country' => trim(mb_convert_encoding($country, 'UTF-8', 'GBK')),
						'local' => trim(mb_convert_encoding($local, 'UTF-8', 'GBK'))
					);
	}

	return $result;
}
?>