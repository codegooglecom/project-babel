<?php
define('V2EX_BABEL', 1);
require('core/Settings.php');
require('core/Utilities.php');
ini_set('include_path', BABEL_PREFIX . '/libs/pear' . PATH_SEPARATOR . ini_get('include_path'));
require_once('Cache/Lite.php');
require_once('Crypt/Blowfish.php');

if (isset($_SERVER['HTTP_REFERER'])) {
	$_prev = $_SERVER['HTTP_REFERER'];
} else {
	$_prev = 'http://' . BABEL_DNS_NAME . '/';
}

header('Content-type: text/html;charset=UTF-8');

$to = false;

if (isset($_GET['usr']) && isset($_GET['password'])) {
	if (get_magic_quotes_gpc()) {
		$_usr = make_single_safe(stripslashes($_GET['usr']));
		$_password = make_single_safe(stripslashes($_GET['password']));
	} else {
		$_usr = make_single_safe($_GET['usr']);
		$_password = make_single_safe($_GET['password']);
	}
	if ($_usr != '' && $_password != '') {
		$db = mysql_connect(BABEL_DB_HOSTNAME . ':' . BABEL_DB_PORT, BABEL_DB_USERNAME, BABEL_DB_PASSWORD);
		mysql_select_db(BABEL_DB_SCHEMATA);
		mysql_query("SET NAMES utf8");
		mysql_query("SET CHARACTER SET utf8");
		mysql_query("SET COLLATION_CONNECTION='utf8_general_ci'");

		$__usr = mysql_real_escape_string($_usr, $db);
		$__password = sha1($_password);
		
		if (preg_match('/@/', $usr)) {
			$sql = "SELECT usr_id, usr_nick, usr_email, usr_password FROM babel_user WHERE usr_email = '{$__usr}' AND usr_password = '{$__password}'";
		} else {
			$sql = "SELECT usr_id, usr_nick, usr_email, usr_password FROM babel_user WHERE usr_nick = '{$__usr}' AND usr_password = '{$__password}'";		
		}
		
		$rs = mysql_query($sql);
		
		if ($User = mysql_fetch_object($rs)) {
			mysql_free_result($rs);
			$bf = new Crypt_Blowfish(BABEL_BLOWFISH_KEY);
			setcookie('babel_usr_email', $User->usr_email, time() + 2678400, '/');
			setcookie('babel_usr_password', $bf->encrypt($User->usr_password), time() + 2678400, '/');
			$_SESSION['babel_usr_email'] = $User->usr_email;
			$_SESSION['babel_usr_password'] = $User->usr_password;
			$__t = time();
			$__ua = mysql_real_escape_string($_SERVER['HTTP_USER_AGENT'], $db);
			$sql = "UPDATE macau_user SET usr_logins = usr_logins + 1, usr_lastlogin = {$__t}, usr_lastlogin_ua = '{$__ua}' WHERE usr_id = {$User->usr_id}";
			mysql_unbuffered_query($sql);
		} else {
			mysql_free_result($rs);
		}
	}
}

if (!$to) {
	$to = $_prev;
}
header('Location: ' . $to);
?>