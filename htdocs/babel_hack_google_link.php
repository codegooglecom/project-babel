<?php
define('V2EX_BABEL', 1);
require_once('core/Settings.php');

/* 3rdparty PEAR cores */
ini_set('include_path', BABEL_PREFIX . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . 'pear' . PATH_SEPARATOR . ini_get('include_path'));
require_once('Cache/Lite.php');
require_once('HTTP/Request.php');
require_once('Crypt/Blowfish.php');

require_once('core/UserCore.php');
require_once('core/Utilities.php');

if (@$db = mysql_connect(BABEL_DB_HOSTNAME . ':' . BABEL_DB_PORT, BABEL_DB_USERNAME, BABEL_DB_PASSWORD)) {
	mysql_select_db(BABEL_DB_SCHEMATA);
	mysql_query("SET NAMES utf8");
	mysql_query("SET CHARACTER SET utf8");
	mysql_query("SET COLLATION_CONNECTION='utf8_general_ci'");
}

$User = new User('', '', $db);

if ($User->vxIsLogin()) {
	if ($User->usr_id == 1) {
		echo('Welcome.<br /><br />');
		if (isset($_GET['u']) && isset($_GET['g'])) {
			$u = fetch_single($_GET['u']);
			$g = fetch_single($_GET['g']);
			$us = mysql_real_escape_string($u);
			$gs = mysql_real_escape_string($g);
			$sql = "UPDATE babel_user SET usr_google_account = '{$gs}' WHERE usr_nick = '{$us}' LIMIT 1";
			mysql_query($sql);
			if (mysql_affected_rows($db) == 1) {
				echo("{$u}'s Google Account is updated.<br /><br />");
			} else {
				echo("Something is not OK.<br /><br />");
			}
		}
		echo('<form method="get">User: <input type="text" name="u" />&nbsp;&nbsp;Gtalk: <input type="text" name="g" /><br /><input type="submit" /></form>');
	} else {
		die('403');
	}
} else {
	die('403');
}
?>