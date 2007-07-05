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
		if ($Entry->bge_comment_permission > 0) {
			if (isset($_COOKIE['babel_weblog_comment_default'])) {
				$_default = unserialize(fetch_multi($_COOKIE['babel_weblog_comment_default']));
			} else {
				$_default = array();
				if ($User->vxIsLogin()) {
					$_default['nick'] = $User->usr_nick;
					$_default['email'] = $User->usr_email;
					$_default['url'] = '';
				} else {
					$_default['nick'] = '';
					$_default['email'] = '';
					$_default['url'] = '';
				}
				setcookie('babel_weblog_comment_default', serialize($_default), (time() + (86400 * 30)), '/');
			}
			if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
				$Validator = new Validator($db, $User);
				$rt = $Validator->vxBlogCommentCheck();
				if ($rt['errors'] == 0) {
					if ($User->vxIsLogin()) {
						$user_id = $User->usr_id;
						$status = 1;
						$_SESSION['babel_message_comment'] = 'Your comment is saved.';
					} else {
						$user_id = 0;
						$status = 0;
						$_SESSION['babel_message_comment'] = 'Your comment is saved and holding for moderation.';
					}
					$Validator->vxBlogCommentInsert($user_id, $entry_id, $rt['bec_nick_value'], $rt['bec_email_value'], $rt['bec_url_value'], $rt['bec_body_value'], $status);
					$Entry->vxUpdateComments();
					$_default['nick'] = $rt['bec_nick_value'];
					$_default['email'] = $rt['bec_email_value'];
					$_default['url'] = $rt['bec_url_value'];
					setcookie('babel_weblog_comment_default', serialize($_default), (time() + (86400 * 30)), '/');
					header('Location: /blog/comment?entry_id=' . $Entry->bge_id);
					die();
				}
			}
		}

	} else {
		$entry_id = 0;
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US">
<head>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<?php
if ($Entry->entry) {
	echo('<title>' . $Entry->bge_comments . ' responses to ' . $Entry->bge_title_plain . '</title>');
} else {
	echo('<title>ERROR: Entry Not Found</title>');
}
?>
<link href="/weblog-static/common/weblog_comment.css" rel="stylesheet" type="text/css" />
<script src="/weblog-static/common/weblog.js" type="text/javascript"> </script>
</head>
<body>
<?php
echo('<div id="header"><div style="float: right; font-size: 10px;">&#187; <a href="javascript:window.close();" class="white">Close this window</a></div>');
if ($Entry->entry) {
	$img_p = ($Weblog->blg_portrait == '') ? '/img/p_blog_s.png' : '/img/b/' . $Weblog->blg_portrait . '_s.jpg';
	echo('<img src="' . $img_p . '" align="absmiddle" class="blog_portrait" /> ' . $Weblog->blg_title_plain . ' &nbsp;<span class="desc">' . make_plaintext($Weblog->blg_description) . '</span>');
} else {
	echo('ERROR: Entry Not Found');
}
echo('</div>');
if ($Entry->entry) {
	echo('<div id="title">');
	_v_ico_silk('comments');
	echo(' ' . $Entry->bge_comments . ' responses to <strong>' . $Entry->bge_title_plain . '</strong>');
	echo('</div>');
	$sql = "SELECT bec_id, bec_nick, bec_body, bec_url, bec_created FROM babel_weblog_entry_comment WHERE bec_eid = {$entry_id} ORDER BY bec_created ASC";
	$rs = mysql_query($sql);
	while ($_comment = mysql_fetch_array($rs)) {
		echo('<div class="comment">');
		echo(nl2br($_comment['bec_body']));
		echo('<div class="author">By ');
		if ($_comment['bec_url'] != '') {
			echo('<a href="' . $_comment['bec_url'] . '" target="_blank" rel="nofollow external">' . make_plaintext($_comment['bec_nick'])) . '</a>';
		} else {
			echo(make_plaintext($_comment['bec_nick']));
		}
		echo(' at ' . date('r', $_comment['bec_created']) . '</div>');
		echo('</div>');
	}
	mysql_free_result($rs);
}
if ($Entry->entry) {
	if ($Entry->bge_comment_permission > 0) {
		if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
			if ($rt['errors'] > 0) {
				echo('<div class="notify">');
				_v_ico_silk('exclamation');
				echo(' Please check the comment you just submitted.');
				echo('</div>');
				echo('<div class="form">');
				echo('<table width="500" cellspacing="0" cellpadding="2" border="0">');
				echo('<form action="/blog/comment?entry_id=' . $entry_id . '" method="post" id="form_blog_comment">');
				echo('<tr><td width="300">Nick name</td><td width="200" rowspan="7"></td></tr>');
				echo('<tr><td width="300"><input type="text" name="bec_nick" class="sl" value="' . make_single_return($rt['bec_nick_value'], 0) . '" /></td></tr>');
				echo('<tr><td width="300">E-mail&nbsp;&nbsp;<span class="tip"><small>This will not be published.</small></span></td></tr>');
				echo('<tr><td width="300"><input type="text" name="bec_email" class="sl" value="' . make_single_return($rt['bec_email_value'], 0) . '" /></td></tr>');
				echo('<tr><td width="300">Website URL</td></tr>');
				echo('<tr><td width="300"><input type="text" name="bec_url" class="sl" value="' . make_single_return($rt['bec_url_value'], 0) . '" /></td></tr>');
				echo('<tr><td width="300">Comment&nbsp;&nbsp;<span class="tip"><small>Some HTML is OK.</small></span></td></tr>');
				echo('<tr><td width="500" colspan="2"><textarea class="ml" rows="10" name="bec_body">' . make_multi_return($rt['bec_nick_value'], 0) . '</textarea></td></tr>');
				echo('<tr><td width="500" colspan="2" align="left"><span class="info"><input class="cb" type="checkbox" /> <small>Remember me on this computer.</info></td></tr>');
				echo('<tr><td width="500" colspan="2" align="left">');
				_v_btn_f('Post', 'form_blog_comment');
				echo('</td></tr>');
				echo('</form>');
				echo('</table>');
				echo('</div>');
			}
		} else {
			if (isset($_SESSION['babel_message_comment'])) {
				if (trim($_SESSION['babel_message_comment']) != '') {
					echo('<div class="notify">');
					_v_ico_silk('accept');
					echo(' ' . $_SESSION['babel_message_comment']);
					echo('</div>');
					$_SESSION['babel_message_comment'] = '';
				}
			} else {
				$_SESSION['babel_message_comment'] = '';
			}
			echo('<div class="form">');
			echo('<table width="500" cellspacing="0" cellpadding="2" border="0">');
			echo('<form action="/blog/comment?entry_id=' . $entry_id . '" method="post" id="form_blog_comment">');
			echo('<tr><td width="300">Nick name</td><td width="200" rowspan="7"></td></tr>');
			echo('<tr><td width="300"><input type="text" name="bec_nick" class="sl" value="' . make_single_return($_default['nick'], 0) . '" /></td></tr>');
			echo('<tr><td width="300">E-mail&nbsp;&nbsp;<span class="tip"><small>This will not be published.</small></span></td></tr>');
			echo('<tr><td width="300"><input type="text" name="bec_email" class="sl" value="' . make_single_return($_default['email'], 0) . '" /></td></tr>');
			echo('<tr><td width="300">Website URL</td></tr>');
			echo('<tr><td width="300"><input type="text" name="bec_url" class="sl" value="' . make_single_return($_default['url'], 0) . '" /></td></tr>');
			echo('<tr><td width="300">Comment&nbsp;&nbsp;<span class="tip"><small>Some HTML is OK.</small></span></td></tr>');
			echo('<tr><td width="500" colspan="2"><textarea class="ml" rows="10" name="bec_body"></textarea></td></tr>');
			echo('<tr><td width="500" colspan="2" align="left"><span class="info"><input class="cb" type="checkbox" /> <small>Remember me on this computer.</info></td></tr>');
			echo('<tr><td width="500" colspan="2" align="left">');
			_v_btn_f('Post', 'form_blog_comment');
			echo('</td></tr>');
			echo('</form>');
			echo('</table>');
			echo('</div>');
		}
	} else {
		echo('<div class="form"><span class="info">');
		_v_ico_silk('information');
		echo(' Comment for this entry is closed.</span></div>');
	}
}
echo('</body></html>');
?>