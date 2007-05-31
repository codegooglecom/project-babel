<?php
define('V2EX_BABEL', 1);

require_once('core/Settings.php');
require_once('core/Utilities.php');
require_once('core/Shortcuts.php');
require_once('core/Vocabularies.php');

$_ing = false;

if (isset($_GET['ing_id'])) {
	$ing_id = intval($_GET['ing_id']);
} else {
	$ing_id = false;
	$_ing = false;
}

if ($ing_id) {
	$db = mysql_pconnect(BABEL_DB_HOSTNAME . ':' . BABEL_DB_PORT, BABEL_DB_USERNAME, BABEL_DB_PASSWORD);
	mysql_select_db(BABEL_DB_SCHEMATA, $db);
	mysql_query("SET NAMES utf8");
	mysql_query("SET CHARACTER SET utf8");
	mysql_query("SET COLLATION_CONNECTION='utf8_general_ci'");	
	$sql = "SELECT usr_id, usr_nick, usr_gender, usr_portrait, ing_id, ing_doing, ing_source, ing_created FROM babel_user, babel_ing_update WHERE ing_uid = usr_id AND ing_id = {$ing_id} LIMIT 1";
	$rs = mysql_query($sql);
	if (mysql_num_rows($rs) == 1) {
		$_ing = mysql_fetch_array($rs);
		$_ing['usr_nick_plain'] = make_plaintext($_ing['usr_nick']);
		$_ing['usr_nick_url'] = urlencode($_ing['usr_nick']);
		$_ing['img_p'] = $_ing['usr_portrait'] ? CDN_P . 'p/' . $_ing['usr_portrait'] . '_n.jpg' : CDN_P . 'p_' . $_ing['usr_gender'] . '_n.gif';		
		mysql_free_result($rs);
	} else {
		mysql_free_result($rs);
		$_ing = false;
	}
}

header('Content-Type: text/html; charset=UTF-8');
header('Etag: ' . sha1($ing_id));
?>
<html>
<head>
	<meta http-equiv="Content-type" content="text/html;charset=utf-8" />
	<meta name="Author" content="<?php echo make_single_return($_ing['usr_nick']); ?>" />
	<meta name="Generator" content="Project Babel" />
	<title><?php
	if ($_ing) {
		echo(strip_tags(format_ubb($_ing['ing_doing'])) . ' - ' . Vocabulary::site_name);
	} else {
		echo(Vocabulary::site_name . '::ING');
	}
	?></title>
	<link rel="stylesheet" type="text/css" href="/css/themes/<?php echo BABEL_THEME; ?>/css_babel.css" />
	<link rel="stylesheet" type="text/css" href="/css/themes/<?php echo BABEL_THEME; ?>/css_extra.css" />
<?php
if (MINT_LOCATION != '') {
	echo('<script src="' . MINT_LOCATION . '" type="text/javascript"></script>');
}
?>
</head>
<body>
<table width="100%" height="99%">
<tr>
<td height="20%" colspan="3"></td>
</tr>
<tr>
<td width="200" height="60%"></td>
<td height="60%">
<div class="blank">
<?php
if ($_ing) {
	$_sources = array(1 => 'web', 2 => 'ingc');
	echo('<span style="font-size: 14px;">');
	echo('<img src="' . $_ing['img_p'] . '" align="absmiddle" class="portrait" />');
	echo(' ' . format_ubb($_ing['ing_doing']) . '</span>');
	_v_hr();
	echo('<div align="right"><span class="tip_i">by <a href="/u/' . $_ing['usr_nick_url'] . '" class="t">' . $_ing['usr_nick_plain'] . '</a> at <small class="fade">' . date('Y-n-j G:i:s T', $_ing['ing_created']) . '</small><small> from ' . $_sources[$_ing['ing_source']] . ' | Powered by <a href="/ing">' . Vocabulary::site_name . '::ING</a></small></span></div>');
} else {
	echo('<span class="tip_i">');
	_v_ico_silk('hourglass');
	echo(' 指定的 <a href="/ing" class="t">' . Vocabulary::site_name . '::ING</a> 项目不存在 | ');
	_v_ico_silk('house');
	echo(' <a href="/" class="t">返回首页</a> | ');
	_v_ico_silk('user_go');
	echo(' <a href="/signup.html" class="t">注册</a> | ');
	_v_ico_silk('key');
	echo(' <a href="/login" class="t">登录</a></span>');
}
?>
</div>
</td>
<td width="200" height="60%"></td>
</tr>
<tr>
<td height="20%" colspan="3"></td>
</tr>
</table>
</body>
</html>