<?php
/* Project Babel
*  Author: Livid Torvalds
*  File: /htdocs/core/Utility.php
*  Usage: Utility functions
*  Format: 1 tab indent(4 spaces), LF, UTF-8, no-BOM
*
*  Subversion Keywords:
*
*  $Id$
*  $LastChangedDate$
*  $LastChangedRevision$
*  $LastChangedBy$
*  $URL$
*/

if (@V2EX_BABEL != 1) {
	die('<strong>Project Babel</strong><br /><br />Made by <a href="http://www.v2ex.com/">V2EX</a> | software for internet');
}

require_once(BABEL_PREFIX . '/res/pointless.php');

$dirs = array('/tmp', '/tplc', '/cache', '/cache/120', '/cache/360', '/cache/7200', '/cache/rss', '/cache/dict', '/cache/smarty', '/htdocs/img/c', '/htdocs/img/n', '/htdocs/img/s', '/htdocs/img/p_static', '/htdocs/feed');

// return: func
function check_env() {
	if (BABEL_ENABLED == 'yes') {
		global $dirs;
		foreach ($dirs as $dir) {
			if (!is_writable(BABEL_PREFIX . $dir)) {
				return exception_message('permission');
			}
		}
	} else {
		return exception_message('off');
	}
}

// return: bool (true => access permited / false => access denied)
function check_node_permission($node_id, $User, $restricted) {
	if (in_array($node_id, $restricted->nodes_restricted)) {
		if ($User->vxIsLogin()) {
			if (in_array($User->usr_id, $restricted->users_permitted[$node_id])) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	} else {
		return true;
	}
}

function get_mem_info() {
	$info = file_get_contents('/proc/meminfo');
	preg_match('/MemTotal:([\s]+)([0-9]+) kB/i', $info, $z);
	$mem_total = $z[2];
	preg_match('/MemFree:([\s]+)([0-9]+) kB/i', $info, $z);
	$mem_free = $z[2];
	$mem_info = array();
	$mem_info['total'] = intval($mem_total);
	$mem_info['free'] = intval($mem_free);
	$mem_info['used'] = intval($mem_total) - intval($mem_free);
	return $mem_info;
}

// return: object
function get_restricted($c) {
	if ($o = $c->get('nodes_restricted')) {
		$o = unserialize($o);
	} else {
		$xml = simplexml_load_file(BABEL_PREFIX . '/res/restricted.xml');
		$o = new stdClass();
		$nodes_restricted = array();
		$users_permitted = array();
		$i = 0;
		foreach ($xml->nodes->node as $node) {
			$i++;
			$nodes_restricted[] = intval($node['id']);
			$users_permitted[intval($node['id'])] = array();
			foreach ($node->users->user as $user) {
				$users_permitted[intval($node['id'])][] = intval($user['id']);
			}
		}
		$o->nodes_restricted = $nodes_restricted;
		$o->users_permitted = $users_permitted;
		$c->save(serialize($o), 'nodes_restricted');
	}
	return $o;
}

function js_alert($msg, $dst, $header = true) {
	if ($header) {
		header('Content-type: text/html; charset=UTF-8');
	}
	echo('<script type="text/javascript">alert("' . make_multi_return($msg) . '"); location.href = "' . $dst . '";</script>');
}

// return: array
function read_xml_religions() {
	$_religions = array();
	$xml = simplexml_load_file(BABEL_PREFIX . '/res/religions.xml');
	foreach ($xml->religion as $religion) {
		$_religions[] = $religion->name;
	}
	return $_religions;
}

// return: void
function exception_message($func = '') {
	header('Content-type: text/html;charset=UTF-8');
	$o = '<html><head><title>Project Babel</title><meta http-equiv="content-type: text/html;charset=UTF-8" /></head>';
	$o .= '<link rel="stylesheet" type="text/css" href="/css/errors/style.css" />';
	$o .= '<body>';
	$o .= '<div class="error">';
	switch ($func) {
		case 'off':
			$o .= '<h1>Babel Temporary Unavailable</h1>';
			$o .= 'Babel 正在进行大型升级，因此现在站点暂时不可用。请过一段时间再回来。谢谢！<br /><br />';
			$o .= 'Babel 正在進行重要的升級改版程序，因此網站會中斷一些時間。請稍後再回來。謝謝。<br /><br />';
			$o .= 'Babel is performing a major upgrade now, the service is temporary unavailable, please check back later, thank you!<br /><br />';
			$o .= 'Babel wird ein wichtiges Update durchgefuehrt. Das Service ist momentan leider nicht erreichbar. Bitte versuchen Sie es spaeter noch mal. Wir bitten um Verstaendnis. Danke.<br /><br />';
			$o .= 'Babel は只今システムのアップグレード中ですので、現在ご利用になれません。しばらく後でアクセスしてください。ご迷惑をお掛け致します。<br /><br />';
			$o .= "¡Babel ahora está realizando una mejora importante, el servicio es inasequible temporal, comprueba por favor detrás más adelante, gracias!<br /><br />";
			$o .= '지금 Bable을 대현 진집중입니다. 사용을 할 수없습니다.조금만 기다리십시오. 감사합니다.';
			break;
		case 'permission':
			$o .= '<h1>Babel Permission Problem</h1>';
			global $dirs;
			$o .= 'Babel 启动失败，请确认以下目录存在，并且可以被 web server 进程写入:<ul style="list-style: square; font-size: 15px; font-family: monospace">';
			foreach ($dirs as $dir) {
				$s_tmp = is_writable(BABEL_PREFIX . $dir) ? '<em class="green">ok</em>' : '<strong class="red">access denied</strong>';
				$o .= '<li>' . BABEL_PREFIX . $dir . ' ... ' . $s_tmp . '</li>';
			}
			$o .= '</ul>如果是在 Unix 操作系统上运行 Babel，你可以使用 chmod 777 方式来更改目录权限，或将以上目录的所有者更改为 web server 进程用户。';
			break;
		case 'db':
			$o .= '<h1>Babel Database Problem</h1>';
			$o .= 'Babel 启动失败，数据库连接无法建立。数据库返回以下错误信息：';
			$o .= '<div class="info">' . mysql_error() . '</div>';
			break;
		case 'world':
			$o .= '<h1>Babel World Does Not Exist</h1>';
			$o .= 'Babel 世界数据尚未导入，请创建你自己的 InstallCore.php 并运行一次。这样你将拥有一个最初的 Babel 世界。';
	}
	$o .= '<span class="ver">&copy; 2007 <a href="http://www.v2ex.com/">V2EX</a> | Project Babel v0.5 Monster Inc</span>';
	$o .= '</div></body></html>';
	die ($o);
}

// return: int
function format_api_date($sd) {
	$a = explode(' ', $sd);
	
	$day = $a[0];
	$month = $a[1];
	$year = $a[2];
	$time = $a[3];
	
	$a = explode(':', $time);
	
	$hour = $a[0];
	$minute = $a[1];
	$second = $a[2];
	
	return mktime($hour, $minute, $second, $month, $day, $year);
}

function format_def($def) {
	$o = htmlspecialchars(trim($def));
	$o = explode("\n", $o);
	$o[0] = '<span class="text_large">' . $o[0] . '</span>';
	$o = nl2br(implode("\n", $o));
	return $o;
}

function format_ubb($text, $emoticon = true) {
	$text = str_replace('<', '&lt;', $text);
	$text = str_replace('>', '&gt;', $text);
	
	$p[0] = '#\[img\]([\w]+?://[\w\#$%&~/.\-;:=,' . "'" . '?@\[\]+]*?)\[/img\]#is';
	
	// matches a [url]xxxx://www.livid.cn[/url] code..
	$p[1] = "#\[url\]([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*?)\[/url\]#is";
	
	// [url]www.livid.cn[/url] code.. (no xxxx:// prefix).
	$p[2] = "#\[url\]((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]+]*?)\[/url\]#is";
	
	// [url=xxxx://www.phpbb.com]phpBB[/url] code..
	$p[3] = "#\[url=([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*?)\]([^?\n\r\t].*?)\[/url\]#is";
	
	// [url=www.phpbb.com]phpBB[/url] code.. (no xxxx:// prefix).
	$p[4] = "#\[url=((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]+]*?)\]([^?\n\r\t].*?)\[/url\]#is";
	
	// [media,width,height]xxxx://www.livid.cn/example.mp3[/media] code..
	$p[5] = "#\[media,([0-9]*),([0-9]*)\]([\w]+?://[\w\#$%&~/.\-;:=,?!@\(\)\[\]+]*?)\[\/media\]#is";
	
	// [email]user@domain.tld[/email] code..
	$p[6] = "#\[email\]([a-z0-9&\-_.]+?@[\w\-]+\.([\w\-\.]+\.)?[\w]+)\[/email\]#si";
	
	$p[7] = '/\[url\]([^?].*?)\[\/url\]/i';
	
	$p[8] = '/\[b\](.*?)\[\/b\]/i';
	
	$p[9] = '/\[strong\](.*?)\[\/strong\]/i';
	
	$p[10] = '/\[i\](.*?)\[\/i\]/i';
	
	$p[11] = '/\[em\](.*?)\[\/em\]/i';
	
	$p[12] = '/\[go=([a-zA-Z_\-0-9]+)\](.*?)\[\/go\]/';
	
	$p[13] = '/\[s\](.*?)\[\/s\]/i';
	
	$p[14] = '/\[youtube\]([a-zA-Z0-9\_\-]+)\[\/youtube\]/i';
	
	$r[0] = '<img class="code" src="$1" border="0" />';
	$r[1] = '<a href="$1" rel="nofollow external" class="tpc">$1</a>';
	$r[2] = '<a href="http://$1" rel="nofollow external" class="tpc">http://$1</a>';
	$r[3] = '<a href="$1" rel="nofollow external" class="tpc">$2</a>';
	$r[4] = '<a href="http://$1" rel="nofollow external" class="tpc">$2</a>';
	$r[5] = '<embed width="$1" height="$2" src="$3" autostart="true" loop="false" />';
	$r[6] = '<a class="tpc" href="mailto:$1">$1</a>';
	$r[7] = '<a class="tpc" href="$1">$1</a>';
	$r[8] = '<strong>$1</strong>';
	$r[9] = '<strong>$1</strong>';
	$r[10] = '<em>$1</em>';
	$r[11] = '<em>$1</em>';
	$r[12] = '讨论区 [ <a href="/go/$1" class="tpc">$2</a> ]';
	$r[13] = '<strike>$1</strike>';
	$r[14] = '<object width="425" height="350"><param name="movie" value="http://www.youtube.com/v/$1"></param><param name="wmode" value="transparent"></param><embed src="http://www.youtube.com/v/$1" type="application/x-shockwave-flash" wmode="transparent" width="425" height="350"></embed></object>';
	
	$text = preg_replace($p, $r, $text);
	
	preg_match('/\[code\]/i', $text, $_m_code_open);
	preg_match('/\[\/code\]/i', $text, $_m_code_close);
	
	preg_match('/\[quote\]/i', $text, $_m_quote_open);
	preg_match('/\[\/quote\]/i', $text, $_m_quote_close);
	
	$text = nl2br($text);
	
	if (count($_m_code_open) == count($_m_code_close)) {
		$text = str_ireplace('[code]', '<div class="code">', $text);
		$text = str_ireplace('[/code]', '</div>', $text);
	}
	
	if (count($_m_quote_open) == count($_m_quote_close)) {
		$text = str_ireplace("[quote]\n", '[quote]', $text);
		$text = str_ireplace("\n[/quote]", '[/quote]', $text);
		$text = str_ireplace('[quote]', '<div class="quote">', $text);
		$text = str_ireplace('[/quote]', '</div>', $text);
	}

	$text = str_ireplace('</div><br />', '</div>', $text);
	
	// smiles:
	if ($emoticon) {
		$text = str_ireplace(':)', '<img src="http://' . BABEL_DNS_DOMAIN . '/img/icons/silk/emoticon_smile.png" align="absmiddle" style="padding: 0px 2px 0px 2px;" border="0" />', $text);
		$text = str_ireplace(':-)', '<img src="http://' . BABEL_DNS_DOMAIN . '/img/icons/silk/emoticon_smile.png" align="absmiddle" style="padding: 0px 2px 0px 2px;" border="0" />', $text);
		$text = str_ireplace(':o', '<img src="http://' . BABEL_DNS_DOMAIN . '/img/icons/silk/emoticon_surprised.png" align="absmiddle" style="padding: 0px 2px 0px 2px;" border="0" />', $text);
		$text = str_ireplace(':-o', '<img src="http://' . BABEL_DNS_DOMAIN . '/img/icons/silk/emoticon_surprised.png" align="absmiddle" style="padding: 0px 2px 0px 2px;" border="0" />', $text);
		$text = str_ireplace(':(', '<img src="http://' . BABEL_DNS_DOMAIN . '/img/icons/silk/emoticon_unhappy.png" align="absmiddle" style="padding: 0px 2px 0px 2px;" border="0" />', $text);
		$text = str_ireplace(':-(', '<img src="http://' . BABEL_DNS_DOMAIN . '/img/icons/silk/emoticon_unhappy.png" align="absmiddle" style="padding: 0px 2px 0px 2px;" border="0" />', $text);
		$text = str_replace(':D', '<img src="http://' . BABEL_DNS_DOMAIN . '/img/icons/silk/emoticon_grin.png" align="absmiddle" style="padding: 0px 2px 0px 2px;" border="0" />', $text);
		$text = str_replace(':-D', '<img src="http://' . BABEL_DNS_DOMAIN . '/img/icons/silk/emoticon_grin.png" align="absmiddle" style="padding: 0px 2px 0px 2px;" border="0" />', $text);
		$text = str_ireplace(':p', '<img src="http://' . BABEL_DNS_DOMAIN . '/img/icons/silk/emoticon_tongue.png" align="absmiddle" style="padding: 0px 2px 0px 2px;" border="0" />', $text);
		$text = str_ireplace('^_^', '<img src="http://' . BABEL_DNS_DOMAIN . '/img/icons/silk/emoticon_happy.png" align="absmiddle" style="padding: 0px 2px 0px 2px;" border="0" />', $text);
		$text = str_ireplace('^-^', '<img src="http://' . BABEL_DNS_DOMAIN . '/img/icons/silk/emoticon_happy.png" align="absmiddle" style="padding: 0px 2px 0px 2px;" border="0" />', $text);
		$text = str_ireplace('^o^', '<img src="http://' . BABEL_DNS_DOMAIN . '/img/icons/silk/emoticon_happy.png" align="absmiddle" style="padding: 0px 2px 0px 2px;" border="0" />', $text);
		$text = str_ireplace('^^', '<img src="http://' . BABEL_DNS_DOMAIN . '/img/icons/silk/emoticon_happy.png" align="absmiddle" style="padding: 0px 2px 0px 2px;" border="0" />', $text);
	}
	return $text;
}

function filter_tags($input) {
	$output = trim($input);
	$output = str_ireplace(chr(10), '', $output);
	$output = str_ireplace(chr(13), '', $output);
	$output = str_ireplace("'", '', $output);
	$output = str_ireplace('"', '', $output);
	$output = str_ireplace('\\', '', $output);
	$output = str_ireplace('/', '', $output);
	$output = str_ireplace('(', '', $output);
	$output = str_ireplace(')', '', $output);
	$output = str_ireplace('[', '', $output);
	$output = str_ireplace(']', '', $output);
	$output = str_ireplace(',', '', $output);
	$output = str_ireplace('.', '', $output);
	$output = str_ireplace('！', '', $output);
	$output = str_ireplace('。', '', $output);
	$output = str_ireplace('，', '', $output);
	$output = str_ireplace('^', '', $output);
	$output = str_ireplace('{', '', $output);
	$output = str_ireplace('}', '', $output);
	$output = str_ireplace('<', '', $output);
	$output = str_ireplace('>', '', $output);
	$output = str_ireplace('#', '', $output);
	$output = str_ireplace('`', '', $output);
	$output = trim($input);
	return $output;
}

function filter_html($input) {
	$output = strip_tags($input, '<p><br><br /><a><img><b><i><span><h1><h2>');
	return $output;
}

function fetch_single($input) { // $input must be an element in GPC
	$output = trim($input);
	if (get_magic_quotes_gpc()) {
		$output = stripslashes($output);
	}
	$output = str_ireplace(chr(10), '', $output);
	$output = str_ireplace(chr(13), '', $output);
	return $output;
}

function fetch_multi($input) { // $input must be an element in GPC
	$output = trim($input);
	if (get_magic_quotes_gpc()) {
		$output = stripslashes($output);
	}
	return $output;
}

function make_spaces($count) {
	$o;
	while ($i < $count) {
		$o = $o . '&nbsp;';
		$i++;
	}
	return $o;
}

// return: int
function make_today_unix() {
	$today = getdate(time());
	return mktime(0, 0, 0, $today['mon'], $today['mday'], $today['year']);
}

function make_safe_display($txt) {
	$txt = str_ireplace(' width="100%"', ' ', $txt);
	return $txt;
}

function make_single_return($value, $strip = 1) {
	if (function_exists('get_magic_quotes_gpc')) {
		if (get_magic_quotes_gpc()) {
			if ($strip == 1) {
				$value = stripslashes($value);
			}
			return str_replace('"', '&quot;', $value);
		} else {
			return str_replace('"', '&quot;', $value);
		}
	} else {
		return str_replace('"', '&quot;', $value);
	}
}

function make_multi_return($value, $strip = 1) {
	if ($strip == 1) {
		if (function_exists('get_magic_quotes_gpc')) {
			if (get_magic_quotes_gpc()) {
				$value = stripslashes($value);
			}
		}
	}
	$value = str_replace('<', '&lt;', $value);
	$value = str_replace('>', '&gt;', $value);
	return $value;
}

function make_single_safe($value) {
	$value = trim($value);
	$value = str_replace(chr(10), '', $value);
	$value = str_replace(chr(13), '', $value);
	return $value;
}

function make_multi_safe($value) {
	$value = trim($value);
	return $value;
}

function make_plaintext($text) {
	$text = str_replace('<', '&lt;', $text);
	$text = str_replace('>', '&gt;', $text);
	$text = nl2br($text);
	return $text;
}

function calc_geo_break($avg) {
	switch ($avg) {
		case 2:
		default:
			$br = 12;
			break;
		case 3:
			$br = 10;
			break;
		case 4;
			$br = 8;
			break;
		case 5:
			$br = 6;
			break;
		case 6:
			$br = 4;
			break;
	}
	return $br;
}

function trim_br($text) {
	$text = trim($text);
	if (substr($text, 0, 5) == '&#13;') {
		$text = substr($text, 6, (strlen($text) - 6));
	}
	$text = trim($text);
	if (substr($text, 0, 6) == '<br />') {
		$text = substr($text, 6, (strlen($text) - 6));
	}
	return $text;
}

function make_excerpt_man($man, $query, $style) {
	$excerpt = trim($man);
	$len = strlen($excerpt);
	$query = str_replace('(', '', $query);
	$query = str_replace(')', '', $query);
	$_tmp = explode(' ', $query);
	$keywords = array();
	foreach ($_tmp as $keyword) {
		$keyword = trim($keyword);
		if (in_array(substr($keyword, 0, 1), array('-', '+'))) {
			$keyword = substr($keyword, 1, (strlen($keyword) - 1));
		}
		$keywords[] = $keyword;
		$start = mb_strpos($excerpt, $keyword, 0, 'UTF-8');
		if ($start != false) {
			break;
		} else {
			$start = 0;
		}
	}
	if ($start != 0) {
		if ($start < 100) {
			$start = 0;
		} else {
			$start = $start - 100;
		}
	}
	$o = mb_substr($excerpt, $start, 300, 'UTF-8');
	$excerpt = make_highlight($o, $keywords, STR_HIGHLIGHT_SKIPLINKS, $style);
	if (strlen($excerpt) < $len) {
		$excerpt = $excerpt . ' ...';
	}
	return $excerpt;
}

function make_excerpt_ad($ad, $keywords, $style) {
	$o = trim(strip_tags($ad));
	
	$o = make_highlight($o, $keywords, STR_HIGHLIGHT_SKIPLINKS, $style);
	
	return $o;
}

function make_excerpt_home($Topic) {
	$len_content = strlen($Topic->tpc_content);
	$len_desc = strlen($Topic->tpc_description);
	$excerpt = '';
	$excerpt_c = '';
	$excerpt_d = '';
	if ($len_content > 0) {
		$excerpt_c = format_ubb($Topic->tpc_content);
		$excerpt_c = trim($excerpt_c);
	} else {
		$excerpt_d = format_ubb($Topic->tpc_description);
		$excerpt_d = trim($excerpt_d);
	}
	if (strlen($excerpt_c) > 0) {
		$stage = 1;
		$excerpt = $excerpt_c;
	} else {
		if (strlen($excerpt_d) > 0) {
			$stage = 2;
			$excerpt = $excerpt_d;
		} else {
			$stage = 3;
			$excerpt = $Topic->tpc_title;
		}
	}
	$start = 0;
	return $excerpt;
}

function make_excerpt_topic($Topic, $keywords, $style) {
	$len_content = strlen($Topic->tpc_content);
	$len_desc = strlen($Topic->tpc_description);
	$excerpt = '';
	$excerpt_c = '';
	$excerpt_d = '';
	if ($len_content > 0) {
		$excerpt_c = format_ubb($Topic->tpc_content);
		$excerpt_c = trim(strip_tags($excerpt_c));
	} else {
		$excerpt_d = format_ubb($Topic->tpc_description);
		$excerpt_d = trim(strip_tags($excerpt_d));
	}
	if (strlen($excerpt_c) > 0) {
		$stage = 1;
		$excerpt = $excerpt_c;
	} else {
		if (strlen($excerpt_d) > 0) {
			$stage = 2;
			$excerpt = $excerpt_d;
		} else {
			$stage = 3;
			$excerpt = $Topic->tpc_title;
		}
	}
	foreach ($keywords as $keyword) {
		$start = mb_strpos($excerpt, $keyword, 0, 'UTF-8');
		if ($start != false) {
			break;
		} else {
			$start = 0;
		}
	}
	if ($start != 0) {
		if ($start < 100) {
			$start = 0;
		} else {
			$start = $start - 100;
		}
	}
	$o = mb_substr($excerpt, $start, 300, 'UTF-8');
	$excerpt = make_highlight($o, $keywords, STR_HIGHLIGHT_SKIPLINKS, $style);
	if ($stage != 3) {
		if (strlen($excerpt) < $len_content) {
			$excerpt = $excerpt . ' ...';
		}
	}
	return $excerpt;
}

function make_masked_ip($ip) {
	return preg_replace('/([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)/', '$1.$2.$3.*', $ip);
}

function make_wide_ip($ip) {
	return preg_replace('/([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)/', '$1.$2.$3.0', $ip);
}

function make_desc_time($unix_timestamp) {
	$now = time();
	$diff = $now - $unix_timestamp;
	
	if ($diff > 86400) {
		$d_span = intval($diff / 86400);
		$h_diff = $diff % 86400;
		if ($h_diff > 3600) {
			$h_span = intval($h_diff / 3600);
			return $d_span . 'd ' . $h_span . 'h';
		} else {
			return $d_span . 'd';
		}
	}
	
	if ($diff > 3600) {
		$h_span = intval($diff / 3600);
		$m_diff = $diff % 3600;
		if ($m_diff > 60) {
			$m_span = intval($m_diff / 60);
			return $h_span . 'h ' . $m_span . 'm';
		} else {
			return $h_span . 'h';
		}
	}
	
	if ($diff > 60) {
		$span = intval($diff / 60);
		return $span . 'm';
	}
	
	return $diff . 's';
}

function make_descriptive_time($unix_timestamp) {
	$now = time();
	$diff = $now - $unix_timestamp;
	
	if ($diff > (86400 * 30)) {
		$m_span = intval($diff / (86400 * 30));
		$d_diff = $diff % ($m_span * (86400 * 30));
		if ($d_diff > 86400) {
			$d_span = intval($d_diff / 86400);
			return $m_span . ' 月 ' . $d_span . ' 天前';
		} else {
			return $m_span . ' 月前';
		}
	}
	
	if ($diff > 86400) {
		$d_span = intval($diff / 86400);
		$h_diff = $diff % 86400;
		if ($h_diff > 3600) {
			$h_span = intval($h_diff / 3600);
			return $d_span . ' 天 ' . $h_span . ' 小时前';
		} else {
			return $d_span . ' 天前';
		}
	}
	
	if ($diff > 3600) {
		$h_span = intval($diff / 3600);
		$m_diff = $diff % 3600;
		if ($m_diff > 60) {
			$m_span = intval($m_diff / 60);
			return $h_span . ' 小时 ' . $m_span . ' 分钟前';
		} else {
			return $h_span . ' 小时前';
		}
	}
	
	if ($diff > 60) {
		$span = floor($diff / 60);
		$secs = $diff % 60;
		if ($secs > 0) {
			return $span . ' 分 ' . $secs . ' 秒前';
		} else {
			return $span . ' 分钟前';
		}
	}
	
	return $diff . ' 秒前';
}

function rand_color($color_start = 0, $color_end = 3) {
	$color = array(0 => '0', 1 => '3', 2 => '6', 3 => '9', 4 => 'C', 5 => 'F');
	while (($o ='#' . $color[rand($color_start, $color_end)] . $color[rand($color_start, $color_end)] . $color[rand($color_start, $color_end)]) != '#FFF') {
		return $o;
	}
}

function rand_gray($color_start = 1, $color_end = 3) {
	$color = array(0 => '0', 1 => '3', 2 => '6', 3 => '9', 4 => 'C', 5 => 'F');
	$g = $color[rand($color_start, $color_end)];
	while (($o = '#' . $g . $g . $g) != '#FFF') {
		return $o;
	}
}

function rand_font() {
	$font = array(0 => 'Tahoma', 1 => 'sans', 2 => 'Times', 3 => 'fantasy', 4 => 'mono', 5 => 'serif', 6 => 'Verdana', 7 => '"Times New Roman"', 8 => 'Lucida Grande', 9 => 'Arial', 10 => 'Georgia', 11 => 'Geneva');
	return $font[rand(0, 11)];
}

function strip_quotes($text) {
	$text = str_replace('"', '', $text);
	$text = str_replace("'", '', $text);
	return $text;
}

function microtime_float() {
	$ms = explode(' ', microtime());
	$usec = $ms[0];
	$sec = $ms[1];
	return ((float)$usec + (float)$sec);
}

function mbox_to_pattern($text) {
	$text = str_replace('.', '\.', $text);
	$text = '/' . $text . '/is';
	return $text;
}

function is_valid_geo($child) {
	$sysfiles = array('.', '..', '.svn', 'data.xml', 'map.xml', '.DS_Store');
	if (in_array($child, $sysfiles)) {
		return false;
	} else {
		return true;
	}
}

function is_valid_blog_name($name) {
	if (preg_match('/^([a-z0-9\_\-]+)$/i', $name)) {
		return true;
	} else {
		return false;
	}
}

function is_valid_email($email) {
	$regex = '/^[A-Z0-9._-]+@[A-Z0-9][A-Z0-9.-]{0,61}[A-Z0-9]\.[A-Z.]{2,6}$/i';
	return (preg_match($regex, $email));
}

function is_valid_url($url) {
	if (preg_match ("/^(http\:\/\/)?([a-z0-9][a-z0-9\-]+\.)?[a-z0-9][a-z0-9\-]+[a-z0-9](\.[a-z]{2,4})+(\/[a-z0-9\.\,\-\_\%\#\?\=\&]?)?$/i", $url)) {
		return true;
	} else {
		return false;
	}
}

function is_valid_nick($nick) {
	$regex = "/[\\\\<>\\n\\t\\a\\r\\s\"'\\/\\.,\\-~!@#\$%^&*()_+=|\\[\\]\{\};:?]+/";
	if (preg_match($regex, $nick)) {
		return false;
	} else {
		$bad_words = array('。', '，', '？', '～', '！', '刘', '昕', '刘昕', '客齐集', '管', 'admin', 'fuck', 'kijiji', 'public', 'portal', 'all', 'home', 'new', 'save', 'modify', 'post', 'add', 'write', 'update', 'own', 'private', 'static', 'protected', 'final', 'go', 'view', 'special', 'featured', 'staff', '斑竹', '版', '主', 'rss', 'v2ex', 'babel', 'project', 'page', 'goto');
		foreach ($bad_words as $w) {
			$pos = stripos($nick, $w);
			if ($pos === false) {
				$i = 1;
			} else {
				$i = 0;
				return false;
			}
		}
		if ($i == 1) {
			return true;
		}
	}
}

function make_google_account_chain_code() {
	$c1 = chr(rand(65, 90));
	$c2 = chr(rand(65, 90));
	$c3 = chr(rand(65, 90));
	$c4 = chr(rand(65, 90));
	$c5 = chr(rand(65, 90));
	$c6 = chr(rand(65, 90));
	$c7 = chr(rand(65, 90));
	$c8 = chr(rand(65, 90));
	return $c1 . $c2 . $c3 . $c4 . $c5 . $c6 . $c7 . $c8;
}

/**
* Perform a simple text replace
* This should be used when the string does not contain HTML
* (off by default)
*/
define('STR_HIGHLIGHT_SIMPLE', 1);

/**
* Only match whole words in the string
* (off by default)
*/
define('STR_HIGHLIGHT_WHOLEWD', 2);

/**
* Case sensitive matching
* (on by default)
*/
define('STR_HIGHLIGHT_CASESENS', 4);

/**
* Don't match text within link tags
* This should be used when the replacement string is a link
* (off by default)
*
* Doesn't work as yet - can't have variable length lookbehind sets
*/
define('STR_HIGHLIGHT_SKIPLINKS', 8);

/**
* Highlight a string in text without corrupting HTML tags
*
* @param       string          $text           Haystack - The text to search
* @param       array|string    $needle         Needle - The string to highlight
* @param       bool            $options        Bitwise set of options
* @param       array           $highlight      Replacement string
* @return      Text with needle highlighted
*/
function make_highlight($text, $needle, $options = null, $highlight = null)
{
    // Default highlighting
    if ($highlight === null) {
        $highlight = '<strong>\1</strong>';
    }

    // Select pattern to use
    if ($options & STR_HIGHLIGHT_SIMPLE) {
        $pattern = '#(%s)#';
    } elseif ($options & STR_HIGHLIGHT_SKIPLINKS) {
        // This is not working yet
        $pattern = '#(?!<.*?)(%s)(?![^<>]*?>)#';
    } else {
        $pattern = '#(?!<.*?)(%s)(?![^<>]*?>)#';
    }

    // Case sensitivity
    if ($options ^ STR_HIGHLIGHT_CASESENS) {
        $pattern .= 'i';
    }

    $needle = (array) $needle;
    foreach ($needle as $needle_s) {
        $needle_s = preg_quote($needle_s);

        // Escape needle with optional whole word check
        if ($options & STR_HIGHLIGHT_WHOLEWD) {
            $needle_s = '\b' . $needle_s . '\b';
        }

        $regex = sprintf($pattern, $needle_s);
        $text = preg_replace($regex, $highlight, $text);
    }

    return $text;
}

function make_pages($pages, $p, $prefix, $suffix) {
	if ($pages > 1) {
		echo('<div class="pages">');
		if ($p > 1) {
			echo('<a href="' . $prefix . ($p - 1) . $suffix . '" class="nextprev">&#171; previous</a>');
		} else {
			echo('<span class="nextprev">&#171; previous</span>');
		}
		$max = $pages + 1;
		if ($pages < 20) {
			for ($i = 1; $i < $max; $i++) {
				if ($p == $i) {
					echo('<span class="current">' . $i . '</span>');
				} else {
					echo('<a href="' . $prefix . $i . $suffix . '">'. $i . '</a>');
				}
			}
		} else {
			if ($p < 21) {
				for ($i = 1; $i < 21; $i++) {
					if ($p == $i) {
						echo('<span class="current">' . $i . '</span>');
					} else {
						echo('<a href="' . $prefix . $i . $suffix . '">'. $i . '</a>');
					}
				}
				echo('<a href="' . $prefix . $pages . $suffix . '">'. $pages . '</a>');
			} else {
				echo('<a href="' . $prefix . '1' . $suffix . '">1</a>');
				$left = $p - 7;
				$right = $p + 7;
				if ($right > $pages) {
					$right = $pages;
				}
				$max = $right + 1;
				for ($i = $left; $i < $max; $i++) {
					if ($p == $i) {
						echo('<span class="current">' . $i . '</span>');
					} else {
						echo('<a href="' . $prefix . $i . $suffix . '">'. $i . '</a>');
					}
				}
				if ($right != $pages) {
					echo('<a href="' . $prefix . $pages . $suffix . '">'. $pages . '</a>');
				}
			}
		}
		if ($p < $pages) {
			echo('<a href="' . $prefix . ($p + 1) . $suffix . '" class="nextprev">next &#187;</a>');
		} else {
			echo('<span class="nextprev">next &#187;</span>');
		}
		echo('<br /></div>');
	}
}
?>
