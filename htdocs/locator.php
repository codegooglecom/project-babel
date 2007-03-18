<?php
define('V2EX_BABEL', 1);
require('core/Settings.php');
require('core/Utilities.php');

if (isset($_SERVER['HTTP_REFERER'])) {
	$_prev = $_SERVER['HTTP_REFERER'];
} else {
	$_prev = 'http://' . BABEL_DNS_NAME . '/';
}
header('Content-type: text/html;charset=UTF-8');
$to = false;

if (isset($_GET['go'])) {
	$go = make_single_safe($_GET['go']);
	if ($go != '') {
		
		if ($go == '/' | $go == '..') {
			$to = '/';
			header('Location: ' . $to);
			die();
		}
		
		if ($go == './' | $go == '.') {
			$to = $_prev;
			header('Location: ' . $to);
			die();
		}
		
		if ($go == 'profile' | $go == 'settings') {
			$to = '/user/modify.vx';
			header('Location: ' . $to);
			die();
		}
		
		if ($go == 'zen' | $go == 'z') {
			$to = '/zen';
			header('Location: ' . $to);
			die();
		}
		
		if ($go == 'ing') {
			$to = '/ing';
			header('Location: ' . $to);
			die();
		}
		
		if ($go == 'expense' | $go == 'expenses' | $go == 'e') {
			$to = '/expense/view.vx';
			header('Location: ' . $to);
			die();
		}
		
		if ($go == 'status' | $go == 's') {
			$to = '/status.vx';
			header('Location: ' . $to);
			die();
		}
		
		if ($go == 'me' | $go == 'i') {
			$to = '/me';
			header('Location: ' . $to);
			die();
		}
		
		if (mb_substr(strtolower($go), 0, 2, 'UTF-8') == 'q:') {
			$go = mb_substr($go, 2, mb_strlen($go, 'UTF-8') - 2, 'UTF-8');
			$to = '/q/' . $go;
			header('Location: ' . $to);
			die();
		}
		
		if (preg_match('/^[0-9]{11}$/', $go)) {
			$to = '/mobile/' . $go;
			header('Location: ' . $to);
			die();
		}
		
		if (mb_substr(strtolower($go), 0, 4, 'UTF-8') == 'ref:') {
			$to = '/ref/' . mb_substr($go, 4, mb_strlen($go, 'UTF-8') - 4, 'UTF-8');
			header('Location: ' . $to);
			die();
		}
		
		$db = mysql_connect(BABEL_DB_HOSTNAME . ':' . BABEL_DB_PORT, BABEL_DB_USERNAME, BABEL_DB_PASSWORD);
		mysql_select_db(BABEL_DB_SCHEMATA);
		mysql_query("SET NAMES utf8");
		mysql_query("SET CHARACTER SET utf8");
		mysql_query("SET COLLATION_CONNECTION='utf8_general_ci'");
		
		if (mb_substr(mb_strtolower($go, 'UTF-8'), 0, 4, 'UTF-8') == 'rss:') {
			$_go = mb_substr($go, 4, mb_strlen($go, 'UTF-8') - 4, 'UTF-8');
			if (get_magic_quotes_gpc()) {
				$q = mysql_real_escape_string(stripslashes($_go), $db);
			} else {
				$q = mysql_real_escape_string($_go, $db);
			}
			$sql = "SELECT chl_id FROM babel_channel WHERE chl_title LIKE '%{$q}%' OR chl_url LIKE '%{$q}%' LIMIT 1";
			$rs = mysql_query($sql) or die(mysql_error());
			if ($o = mysql_fetch_object($rs)) {
				mysql_free_result($rs);
				$to = '/channel/view/' . $o->chl_id . '.html';
				$o = null;
			} else {
				mysql_free_result($rs);
				$sql = "SELECT nod_id, nod_name FROM babel_node WHERE nod_name = '{$q}' OR nod_title = '{$q}' LIMIT 1";
				$rs = mysql_query($sql);
				if ($o = mysql_fetch_object($rs)) {
					mysql_free_result($rs);
					$to = '/go/' . $o->nod_name;
					$o = null;
				} else {
					mysql_free_result($rs);
					$to = '/q/' . $q;
				}
			}
			header('Location: ' . $to);
			die();
		}
		
		if (get_magic_quotes_gpc()) {
			$q = mysql_real_escape_string(stripslashes($go), $db);
		} else {
			$q = mysql_real_escape_string($go, $db);
		}
		$sql = "SELECT nod_id, nod_name FROM babel_node WHERE nod_name = '{$q}' OR nod_title = '{$q}' LIMIT 1";
		$rs = mysql_query($sql);
		if ($o = mysql_fetch_object($rs)) {
			mysql_free_result($rs);
			$to = '/go/' . $o->nod_name;
			$o = null;
		} else {
			mysql_free_result($rs);
			$to = '/q/' . $go;
		}
	} else {
		$to = $_prev;
	}
} else {
	$to = $_prev;
}

if (isset($db)) {
	mysql_close($db);
}

header('Location: ' . $to);
?>