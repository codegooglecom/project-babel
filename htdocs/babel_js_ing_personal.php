<?php
/* Project Babel
 *
 * Author: Livid Liu <v2ex.livid@mac.com>
 *
 * I do care about the global warming.
 *
 */

define('V2EX_BABEL', 1);

require_once('core/Settings.php');
require_once('core/Vocabularies.php');
require_once('core/Utilities.php');

if (isset($_GET['oe'])) {
	$oe = strtolower(fetch_single($_GET['oe']));
	if ($oe != 'gbk') {
		$oe = 'utf-8';
	}
} else {
	$oe = "utf-8";
}

if ($oe != 'utf-8') {
	header('Content-type: text/javascript; charset=gbk');
} else {
	header('Content-type: text/javascript; charset=utf-8');
}

header('Cache-control: no-cache, must-revalidate');

if (isset($_GET['u'])) {
	$user_nick = fetch_single($_GET['u']);
	if ($user_nick == '') {
		$o = "document.writeln('<small style=\"font-size: 11px;\"><a href=\"http://" . BABEL_DNS_NAME . "/ing\" target=\"_blank\">" . Vocabulary::site_name . "::ING</a></small> 输出失败 - 没有指定会员昵称');";
	} else {
		$db = mysql_pconnect(BABEL_DB_HOSTNAME . ':' . BABEL_DB_PORT, BABEL_DB_USERNAME, BABEL_DB_PASSWORD);
		mysql_select_db(BABEL_DB_SCHEMATA, $db);
		mysql_query("SET NAMES utf8");
		mysql_query("SET CHARACTER SET utf8");
		mysql_query("SET COLLATION_CONNECTION='utf8_general_ci'");
		$user_nick = mysql_real_escape_string($user_nick);
		$sql = "SELECT usr_id FROM babel_user WHERE usr_nick = '{$user_nick}'";
		$rs = mysql_query($sql);
		if (mysql_num_rows($rs) == 1) {
			$user_id = mysql_result($rs, 0, 0);
			mysql_free_result($rs);
			$sql = "SELECT ing_doing, ing_created FROM babel_ing_update WHERE ing_uid = {$user_id} ORDER BY ing_created DESC LIMIT 1";
			$rs = mysql_query($sql);
			if (mysql_num_rows($rs) == 1) {
				$_up = mysql_fetch_array($rs);
				mysql_free_result($rs);
				$doing = format_ubb($_up['ing_doing']);
				$when = make_desc_time($_up['ing_created']) . ' ago';
			} else {
				mysql_free_result($rs);
				$doing = '(void)';
				$when = 'the moment';
			}
			$user_nick_url = urlencode($user_nick);
			$o = "document.writeln(\"<span style='color: \" + babel_ing_color_prefix + \";'>\" + babel_ing_prefix + \"</span> " . $doing . " <small style='font-size: 11px; color: \" + babel_ing_color_time + \";'>at " . $when .  " via <a href='http://" . BABEL_DNS_NAME . "/ing/" . $user_nick_url . "' target='_blank'>" . Vocabulary::site_name . "::ING</a></small>\");";
		} else {
			mysql_free_result($rs);
			$o = "document.writeln('<small style=\"font-size: 11px;\"><a href=\"http://" . BABEL_DNS_NAME . "/ing\" target=\"_blank\">" . Vocabulary::site_name . "::ING</a></small> 输出失败 - 指定的会员没有找到');";
		}
	}
} else {
	$o = "document.writeln('<small style=\"font-size: 11px;\"><a href=\"http://" . BABEL_DNS_NAME . "/ing\" target=\"_blank\">" . Vocabulary::site_name . "::ING</a></small> 输出失败 - 没有指定会员昵称');";
}

if ($oe == 'utf-8') {
	echo $o;
} else {
	echo mb_convert_encoding($o, 'gbk', 'utf-8');
}
?>