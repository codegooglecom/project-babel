<?php
define('V2EX_BABEL', 1);
require_once('core/Settings.php');

/* 3rdparty PEAR cores */
ini_set('include_path', BABEL_PREFIX . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . 'pear' . PATH_SEPARATOR . ini_get('include_path'));
require_once('Cache/Lite.php');
require_once('HTTP/Request.php');
require_once('Crypt/Blowfish.php');

/* 3rdparty Zend Framework cores */
ini_set('include_path', BABEL_PREFIX . '/libs/zf/' . ZEND_FRAMEWORK_VERSION . PATH_SEPARATOR . ini_get('include_path'));
require_once('Zend/Cache.php');

require_once('core/Utilities.php');
require_once('core/Shortcuts.php');
require_once('core/UserCore.php');
require_once('core/URLCore.php');
require_once('core/DashboardCore.php');

if (@$db = mysql_connect(BABEL_DB_HOSTNAME . ':' . BABEL_DB_PORT, BABEL_DB_USERNAME, BABEL_DB_PASSWORD)) {
	mysql_select_db(BABEL_DB_SCHEMATA);
	mysql_query("SET NAMES utf8");
	mysql_query("SET CHARACTER SET utf8");
	mysql_query("SET COLLATION_CONNECTION='utf8_general_ci'");
}


$c = Zend_Cache::factory('Core', ZEND_CACHE_TYPE_TINY, $ZEND_CACHE_OPTIONS_TINY_FRONTEND, $ZEND_CACHE_OPTIONS_TINY_BACKEND[ZEND_CACHE_TYPE_TINY]);

session_start();

$User = new User('', '', $db);
if (!$User->vxIsLogin()) {
	URL::vxToRedirect(URL::vxGetLogin('/dashboard'));
}

$_friends = array();
$sql = "SELECT frd_fid FROM babel_friend WHERE frd_uid = {$User->usr_id}";
$rs = mysql_query($sql);
while ($_f = mysql_fetch_array($rs)) {
	$_friends[] = $_f['frd_fid'];
}
$_all = $_friends;
$_all[] = $User->usr_id;

$all_sql = implode(',', $_all);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN">
<head>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<meta http-equiv="cache-control" content="no-cache" />
<meta name="keywords" content="V2EX, Babel, Livid, PHP, " />
<title>V2EX Dashboard</title>
<link href="/favicon.ico" rel="shortcut icon" />
<link href="/d/css/style.css" rel="stylesheet" type="text/css" />
<?php
if (MINT_LOCATION != '') {
	echo('<script src="' . MINT_LOCATION . '" type="text/javascript"></script>');
}
?>
</head>
<body>
<div id="top">
	<div class="top_right">
<?php
echo($User->usr_nick . ' <small>&lt;' . $User->usr_email . '&gt;</small>');
echo(' | <a href="/user/modify.vx" class="white">修改个人信息与设置</a> | <a href="/logout" class="white">登出</a>');
?>
	</div>
<a href="/dashboard" title="V2EX Dashboard"><img src="/d/img/logo.png" border="0" alt="V2EX Dashboard" /></a>
</div>
<div id="container">
<div id="left">
	<div id="applications">
		<div class="cur"><?php _v_ico_silk('cog'); ?> Everything</div>
		<div class="item"><?php _v_ico_silk('comments'); ?> 主题和回复</div>
		<div class="item"><?php _v_ico_silk('email'); ?> 短消息</div>
		<div class="item"><?php _v_ico_silk('hourglass'); ?> 朋友们的状态</div>
		<div class="item"><?php _v_ico_silk('clock'); ?> 朋友们在做的事情</div>
		<div class="item"><?php _v_ico_silk('feed'); ?> 我收藏的频道</div>
		<div class="sep"></div>
		<div class="item"><?php _v_ico_silk('add'); ?> 更多应用</div>
		<div class="item"><?php _v_ico_silk('user'); ?> 我的个人设置</div>
	</div>
</div>
<div id="right">
	<div id="contacts">
		<?php
		$tag_cache = 'babel_dashboard_contacts_' . $User->usr_id;
		if ($o = $c->load($tag_cache)) {
		} else {
			$o = '';
			$tag_cache_online = 'babel_dashboard_contacts_' . $User->usr_id . '_online';
			$tag_cache_offline = 'babel_dashboard_contacts_' . $User->usr_id . '_offline';
			if (($contacts_online = $c->load($tag_cache_online)) && ($contacts_offline = $c->load($tag_cache_offline))) {
				$_contacts_online = unserialize($contacts_online);
				$_contacts_offline = unserialize($contacts_offline);
				$total = count($_contacts_online) + count($_contacts_offline);
				$o .= '<h2 class="dark">&nbsp;' . _vo_ico_silk('group') . '&nbsp;&nbsp;我的 ' . $total . ' 个联系人</h2>';
			} else {
				$sql = "SELECT usr_id, usr_nick, usr_telephone FROM babel_user, babel_friend WHERE usr_id = frd_fid AND frd_uid = {$User->usr_id} ORDER BY usr_nick ASC";
				$rs = mysql_query($sql);
				$o .= '<h2 class="dark">&nbsp;' . _vo_ico_silk('group') . '&nbsp;&nbsp;我的 ' . mysql_num_rows($rs) . ' 个联系人</h2>';
				$i = 0;
				$_contacts_online = array();
				$_contacts_offline = array();
				while ($_contact = mysql_fetch_array($rs)) {
					$nick = mysql_real_escape_string($_contact['usr_nick']);
					$sql = "SELECT onl_hash FROM babel_online WHERE onl_nick = '{$nick}'";
					$rs_online = mysql_query($sql);
					if (mysql_num_rows($rs_online) > 0) {
						$_contacts_online[] = $_contact;
					} else {
						$_contacts_offline[] = $_contact;
					}
					mysql_free_result($rs_online);
				}
				mysql_free_result($rs);
				$c->save(serialize($_contacts_online), $tag_cache_online);
				$c->save(serialize($_contacts_offline), $tag_cache_offline);
			}
			$i = 0;
			foreach ($_contacts_online as $_contact) {
				$i++;
				$css_class = ($i % 2 == 0) ? 'even' : 'odd';
				$o .= '<div class="c_' . $css_class . '"><img src="/d/img/status/available.png" align="absmiddle" /> <a href="/u/' . urlencode($_contact['usr_nick']) . '" class="white">' . make_plaintext($_contact['usr_nick']) . '</a></div>';
			}
			foreach ($_contacts_offline as $_contact) {
				$i++;
				$css_class = ($i % 2 == 0) ? 'even' : 'odd';
				$o .= '<div class="c_' . $css_class . '"><img src="/d/img/status/offline.png" align="absmiddle" /> <a href="/u/' . urlencode($_contact['usr_nick']) . '" class="white">' . make_plaintext($_contact['usr_nick']) . '</a></div>';
			}
			$c->save($o, $tag_cache);
		}
		echo $o;
		?>
		<h2 class="dark">&nbsp;<?php _v_ico_silk('add'); ?>&nbsp;&nbsp;添加新联系人</h2>
		<div id="contact_new">
		<table width="160" cellpadding="0" cellspacing="0">
			<tr>
				<td align="right" width="60" height="25">名字&nbsp;</td>
				<td align="left" width="100" height="25"><input type="text" class="sl" /></td>
			</tr>
			<tr>
				<td align="right" width="60" height="25">移动电话&nbsp;</td>
				<td align="left" width="100" height="25"><input type="text" class="sl" /></td>
			</tr>
			<tr>
				<td align="right" width="60" height="25">电子邮件&nbsp;</td>
				<td align="left" width="100" height="25"><input type="text" class="sl" /></td>
			</tr>
			<tr>
				<td align="right" width="60" height="25"></td>
				<td align="left" width="100" height="25">
				<input type="button" value="添加" />
				</td>
			</tr>
		</table>
		</div>
		<h2 class="dark">&nbsp;<?php _v_ico_silk('application_form_edit'); ?>&nbsp;&nbsp;管理联系人</h2>
		<h2 class="dark">&nbsp;<?php _v_ico_silk('email_go'); ?>&nbsp;&nbsp;邀请</h2>
		<div id="contact_new">
		<table width="160" cellpadding="0" cellspacing="0">
			<tr>
				<td align="right" width="60" height="25">电子邮件&nbsp;</td>
				<td align="left" width="100" height="25"><input type="text" class="sl" /></td>
			</tr>
			<tr>
				<td align="right" width="60" height="25"></td>
				<td align="left" width="100" height="25">
				<input type="button" value="发送邀请" />
				</td>
			</tr>
		</table>
		</div>
	</div>
</div>
<div id="main">
	<div id="timeline">
		<div class="top_right"><?php _v_ico_silk('feed'); ?> <a href="#;">通过 RSS 订阅</a></div>
		<h1>Everything</h1>
		<div class="sub"><span class="tip"><small>Timeline of the latest topics and ING updates of mine and my friends.</small></span>
		<div class="toolbar">
			<?php
			_v_ico_tango_16('actions/document-new');
			echo(' <a href="#;">发布新消息</a>');
			echo('&nbsp;&nbsp;');
			_v_ico_tango_16('actions/appointment-new');
			echo(' <a href="#;">创建新事件</a>');
			echo('&nbsp;&nbsp;');
			_v_ico_tango_16('actions/view-refresh');
			echo(' <a href="#;" onclick="location.reload();">刷新</a>');
			?>
		</div>
		</div>
		<?php
		$tag_cache = 'babel_dashboard_timeline_' . $User->usr_id;
		if ($timeline_cached = $c->load($tag_cache)) {
			$_timeline = unserialize($timeline_cached);
		} else {
			/* Create a new empty array for timeline. */
			$_timeline = array();
			/* Get 30 new topics and convert them. */
			$sql = "SELECT usr_id, usr_nick, usr_gender, usr_portrait, tpc_id, tpc_title, tpc_hits, tpc_posts, tpc_created, nod_id, nod_name, nod_title FROM babel_topic, babel_user, babel_node WHERE tpc_uid IN ({$all_sql}) AND tpc_uid = usr_id AND tpc_pid = nod_id ORDER BY tpc_created DESC LIMIT 30";
			$rs = mysql_query($sql) or die ($sql . mysql_error());
			while ($_topic = mysql_fetch_array($rs)) {
				$_timeline[$_topic['tpc_created']] = array();
				$_timeline[$_topic['tpc_created']]['type'] = 'topic';
				$_timeline[$_topic['tpc_created']]['link'] = '/topic/view/' . $_topic['tpc_id'] . '.html';
				$_timeline[$_topic['tpc_created']]['weight'] = Dashboard::vxCalcWeightFromTopic($_topic['tpc_hits'], $_topic['tpc_posts']);
				$_timeline[$_topic['tpc_created']]['title'] = $_topic['tpc_title'];
				$_timeline[$_topic['tpc_created']]['usr_nick'] = $_topic['usr_nick'];
				$_timeline[$_topic['tpc_created']]['img_p'] = ($_topic['usr_portrait'] == '') ? '/img/p_' . $_topic['usr_gender'] . '_n.gif' : '/img/p/' . $_topic['usr_portrait'] . '_n.jpg';
				$_timeline[$_topic['tpc_created']]['nod_name'] = $_topic['nod_name'];
				$_timeline[$_topic['tpc_created']]['nod_title'] = $_topic['nod_title'];
			}
			mysql_free_result($rs);
			/* Get 20 new ING updates and convert them. */
			$sql = "SELECT usr_id, usr_nick, usr_gender, usr_portrait, ing_id, ing_doing, ing_created FROM babel_ing_update, babel_user WHERE usr_id = ing_uid AND ing_uid IN ({$all_sql}) ORDER BY ing_created DESC LIMIT 20";
			$rs = mysql_query($sql);
			while ($_ing = mysql_fetch_array($rs)) {
				$_timeline[$_ing['ing_created']] = array();
				$_timeline[$_ing['ing_created']]['type'] = 'ing';
				$_timeline[$_ing['ing_created']]['link'] = '/ing-' . $_ing['ing_id'] . '.html';
				$_timeline[$_ing['ing_created']]['weight'] = 12;
				$_timeline[$_ing['ing_created']]['title'] = $_ing['ing_doing'];
				$_timeline[$_ing['ing_created']]['usr_nick'] = $_ing['usr_nick'];
				$_timeline[$_ing['ing_created']]['img_p'] = ($_ing['usr_portrait'] == '') ? '/img/p_' . $_ing['usr_gender'] . '_n.gif' : '/img/p/' . $_ing['usr_portrait'] . '_n.jpg';
			}
			/* Sort the timeline. */
			krsort($_timeline);
			$c->save(serialize($_timeline), $tag_cache);
		}
		foreach ($_timeline as $time => $event) {
			echo('<div class="object">');
			switch ($event['type']) {
				case 'topic':
					echo('<div class="head"><img src="' . $event['img_p'] . '" align="absmiddle" border="0" />&nbsp;&nbsp;<a href="/u/' . urlencode($event['usr_nick']) . '" class="var" style="color: ' . rand_color() . '; font-weight: bold;">' . make_plaintext($event['usr_nick']) . '</a><span class="tip"> ... ' . make_descriptive_time($time) . '在 <a href="/go/' . urlencode($event['nod_name']) . '" class="var" style="color: ' . rand_color() . '; font-weight: bold;">' . make_plaintext($event['nod_title']) . '</a> 发表了新主题:</span></div>');
					echo('<div class="body"><a href="' . $event['link'] . '" style="font-size: ' . $event['weight'] . 'px">' . make_plaintext($event['title']) . '</a></div>');
					break;
				case 'ing':
					echo('<div class="head"><img src="' . $event['img_p'] . '" align="absmiddle" border="0" />&nbsp;&nbsp;<a href="/u/' . urlencode($event['usr_nick']) . '" class="var" style="color: ' . rand_color() . '; font-weight: bold;">' . make_plaintext($event['usr_nick']) . '</a><span class="tip"> ... ' . make_descriptive_time($time) . '说:</span></div>');
					echo('<div class="body"><a href="' . $event['link'] . '" style="font-size: ' . $event['weight'] . 'px">' . format_ubb($event['title']) . '</a></div>');
					break;
			}
			echo('</div>');
		}
		?>
	</div>
</div>
</div>
<div id="bottom">
	<div id="bottom_inside">
		&copy; 2007 V2EX<br />
		<a href="http://www.v2ex.com/" class="white">V2EX</a> | <a href="http://www.livid.cn/" class="white">Blog</a> | <a href="http://labs.v2ex.com/" class="white">Labs</a> | <a href="http://code.google.com/" class="white">Developers</a><br />
		<small>V2EX Dashboard is a prototype design of future V2EX frontpage, it changes by time.</small>
	</div>
</div>
</body>
</html>