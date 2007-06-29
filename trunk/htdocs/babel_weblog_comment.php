<?php
define('V2EX_BABEL', 1);
require_once('core/Settings.php');

/* 3rdparty PEAR cores */
ini_set('include_path', BABEL_PREFIX . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . 'pear' . PATH_SEPARATOR . ini_get('include_path'));
require_once('Crypt/Blowfish.php');

/* 3rdparty Zend Framework cores */
ini_set('include_path', BABEL_PREFIX . '/libs/zf/' . ZEND_FRAMEWORK_VERSION . PATH_SEPARATOR . ini_get('include_path'));
require_once('Zend/Cache.php');

require_once('core/Utilities.php');
require_once('core/Shortcuts.php');
require_once('core/ValidatorCore.php');
require_once('core/UserCore.php');
require_once('core/WeblogCore.php');
require_once('core/EntryCore.php');

if (isset($_GET['entry_id'])) {
	$entry_id = intval($_GET['entry_id']);
} else {
	$entry_id = 0;
}

if (@$db = mysql_connect(BABEL_DB_HOSTNAME . ':' . BABEL_DB_PORT, BABEL_DB_USERNAME, BABEL_DB_PASSWORD)) {
	mysql_select_db(BABEL_DB_SCHEMATA);
	mysql_query("SET NAMES utf8");
	mysql_query("SET CHARACTER SET utf8");
	mysql_query("SET COLLATION_CONNECTION='utf8_general_ci'");
}

$c = Zend_Cache::factory('Core', ZEND_CACHE_TYPE_TINY, $ZEND_CACHE_OPTIONS_TINY_FRONTEND, $ZEND_CACHE_OPTIONS_TINY_BACKEND[ZEND_CACHE_TYPE_TINY]);

session_start();

$User = new User('', '', $db);

if ($entry_id != 0) {
	$Entry = new Entry($entry_id);
	if ($Entry->entry) {
		$Weblog = new Weblog($Entry->bge_pid);
	} else {
		$entry_id = 0;
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US">
<head>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<title>V2EX Weblog Project</title>
<link href="/weblog-static/common/weblog_comment.css" rel="stylesheet" type="text/css" />
<script src="/weblog-static/common/weblog.js" type="text/javascript"> </script>
</head>
<body>

<div id="header"><div style="float: right; font-size: 10px;">&#187; <a href="javascript:window.close();" class="white">Close this window</a></div>
<?php
$img_p = ($Weblog->blg_portrait == '') ? '/img/p_blog_s.png' : '/img/b/' . $Weblog->blg_portrait . '_s.jpg';
?>
<img src="<?php echo $img_p; ?>" align="absmiddle" class="blog_portrait" /> <?php echo make_plaintext($Weblog->blg_title); ?> &nbsp;<span class="desc"><?php echo make_plaintext($Weblog->blg_description); ?></span>
</div>

<div id="title">
<?php _v_ico_silk('comments'); ?> <?php echo $Entry->bge_comments; ?> responses to <strong><?php echo make_plaintext($Entry->bge_title); ?></strong>
</div>

<!--<div class="comment">
Comment here
<div class="author">By <a href="">Livid</a> at 2007</div>
</div>-->
<?php
if ($Entry->bge_comment_permission > 0) {
	if (isset($_COOKIE['babel_weblog_comment'])) {
		$_comment = unserialize(fetch_multi($_COOKIE['babel_weblog_comment']));
	} else {
		$_comment = array();
		if ($User->vxIsLogin()) {
			$_comment['nick'] = $User->usr_nick;
			$_comment['email'] = $User->usr_email;
			$_comment['url'] = '';
		} else {
			$_comment['nick'] = '';
			$_comment['email'] = '';
			$_comment['url'] = '';
		}
		setcookie('babel_weblog_comment', serialize($_comment), (time() + (86400 * 30)), '/');
	}
	if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
		$Validator = new Validator($db, $User);
		$rt = $Validator->vxBlogCommentCheck();
		echo('<div class="notify">');
		if ($rt['errors'] > 0) {
			_v_ico_silk('exclamation');
			echo(' Please check the comment you just submitted.');
			var_dump($rt);
		} else {
			if ($User->vxIsLogin()) {
				$user_id = $User->usr_id;
			} else {
				$user_id = 0;
			}
			$Validator->vxBlogCommentInsert($user_id, $entry_id, $rt['bce_nick_value'], $rt['bce_email_value'], $rt['bce_url_value'], $rt['bce_body_value']);
			_v_ico_silk('accept');
			echo(' Your comment is saved and holding for moderation.');
		}
		echo('</div>');
	}
?>
<div class="form">
<table width="500" cellspacing="0" cellpadding="2" border="0">
<form action="/blog/comment?entry_id=<?php echo $entry_id; ?>" method="post" id="form_blog_comment">
<tr>
<td width="300">Nick name</td><td width="200" rowspan="7"></td>
</tr>
<tr>
<td width="300"><input type="text" name="bec_nick" class="sl" value="<?php echo make_single_return($_comment['nick'], 0); ?>" /></td>
</tr>
<tr>
<td width="300">E-mail&nbsp;&nbsp;<span class="tip"><small>This will not be published.</small></span></td>
</tr>
<tr>
<td width="300"><input type="text" name="bec_email" class="sl" value="<?php echo make_single_return($_comment['email'], 0); ?>" /></td>
</tr>
<tr>
<td width="300">Website URL</td>
</tr>
<tr>
<td width="300"><input type="text" name="bec_url" class="sl" value="<?php echo make_single_return($_comment['url'], 0); ?>" /></td>
</tr>
<tr>
<td width="300">Comment&nbsp;&nbsp;<span class="tip"><small>Some HTML is OK.</small></span></td>
</tr>
<tr>
<td width="500" colspan="2"><textarea class="ml" rows="10" name="bec_body"></textarea></td>
</tr>
<tr>
<td width="500" colspan="2" align="left"><span class="info"><input class="cb" type="checkbox" /> <small>Remember me on this computer.</info></td>
</tr>
<tr>
<td width="500" colspan="2" align="left"><?php _v_btn_f('Post', 'form_blog_comment'); ?></td>
</tr>
</form>
</table>
</div>
<?php
} else {
?>
<div class="form"><span class="info"><?php _v_ico_silk('information'); ?> Comment for this entry is closed.</span></div>
<?php
}
?>

</body>
</html>