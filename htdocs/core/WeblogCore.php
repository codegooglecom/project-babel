<?php
class Weblog {
	const DEFAULT_ACTION = 'list';
	
	public function __construct($weblog_id) {
		$sql = "SELECT blg_id, blg_uid, blg_name, blg_title, blg_description, blg_portrait, blg_theme, blg_mode, blg_entries, blg_comments, blg_comment_permission, blg_builds, blg_dirty, blg_created, blg_lastupdated, blg_lastbuilt, blg_expire, usr_id, usr_nick, usr_gender, usr_portrait, usr_created, usr_brief FROM babel_weblog, babel_user WHERE blg_uid = usr_id AND blg_id = {$weblog_id}";
		$rs = mysql_query($sql);
		if (mysql_num_rows($rs) == 1) {
			$this->weblog = true;
			$_weblog = mysql_fetch_array($rs);
			$this->blg_id = intval($_weblog['blg_id']);
			$this->blg_uid = intval($_weblog['blg_uid']);
			$this->blg_name = $_weblog['blg_name'];
			$this->blg_title = $_weblog['blg_title'];
			$this->blg_description = $_weblog['blg_description'];
			$this->blg_portrait = $_weblog['blg_portrait'];
			$this->blg_theme = $_weblog['blg_theme'];
			$this->blg_mode = intval($_weblog['blg_mode']);
			$this->blg_entries = intval($_weblog['blg_entries']);
			$this->blg_comments = intval($_weblog['blg_comments']);
			$this->blg_comment_permission = intval($_weblog['blg_comment_permission']);
			$this->blg_builds = intval($_weblog['blg_builds']);
			$this->blg_dirty = intval($_weblog['blg_dirty']);
			$this->blg_created = intval($_weblog['blg_created']);
			$this->blg_lastupdated = intval($_weblog['blg_lastupdated']);
			$this->blg_lastbuilt = intval($_weblog['blg_lastbuilt']);
			$this->blg_expire = intval($_weblog['blg_expire']);
			$this->usr_id = $_weblog['usr_id'];
			$this->usr_nick = $_weblog['usr_nick'];
			mysql_free_result($rs);
			unset($_weblog);
		} else {
			$this->weblog = false;
		}
	}
	
	public function __destruct() {
	}
	
	public function vxAddBuild() {
		$sql = "UPDATE babel_weblog SET blg_builds = blg_builds + 1 WHERE blg_id = {$this->blg_id}";
		mysql_unbuffered_query($sql);
	}
	
	public function vxTouchBuild() {
		$now = time();
		$sql = "UPDATE babel_weblog SET blg_lastbuilt = {$now}, blg_dirty = 0 WHERE blg_id = {$this->blg_id}";
		mysql_unbuffered_query($sql);
	}
	
	public function vxUpdateEntries() {
		$sql = "SELECT COUNT(*) FROM babel_weblog_entry WHERE bge_pid = {$this->blg_id}";
		$count = mysql_result(mysql_query($sql), 0, 0);
		$sql = "UPDATE babel_weblog SET blg_entries = {$count} WHERE blg_id = {$this->blg_id}";
		mysql_unbuffered_query($sql);
		return true;
	}
	
	public function vxSetDirty() {
		$sql = "UPDATE babel_weblog SET blg_dirty = 1 WHERE blg_id = {$this->blg_id}";
		mysql_unbuffered_query($sql);
	}
	
	public static function vxMatchWeblogPermission($user_id, $weblog_id) {
		$sql = "SELECT blg_uid FROM babel_weblog WHERE blg_id = {$weblog_id}";
		$rs = mysql_query($sql);
		if (mysql_num_rows($rs) == 1) {
			$blg_uid = mysql_result($rs, 0, 0);
			mysql_free_result($rs);
			if ($blg_uid == $user_id) {
				return true;
			} else {
				return false;
			}
		} else {
			mysql_free_result($rs);
			return false;
		}
	}
	
	public static function vxMakeTagLink($tags) {
		$_tags = explode(' ', $tags);
		$o = '';
		foreach ($_tags as $tag) {
			$o .= '&nbsp;&nbsp;<a href="tag-' . $tag . '.html">' . $tag . '</a>';
		}
		return $o;
	}
	
	public static function vxMatchEntryPermission($user_id, $entry_id) {
		$sql = "SELECT bge_uid FROM babel_weblog_entry WHERE bge_id = {$entry_id}";
		$rs = mysql_query($sql);
		if (mysql_num_rows($rs) == 1) {
			$bge_uid = mysql_result($rs, 0, 0);
			mysql_free_result($rs);
			if ($bge_uid == $user_id) {
				return true;
			} else {
				return false;
			}
		} else {
			mysql_free_result($rs);
			return false;
		}
	}
	
	public static function vxGetEditorModes() {
		$_modes = array(
			0 => '纯文本 / Plain Text',
			1 => '超文本 / HTML',
			2 => 'UBB',
			3 => 'Textile'
		);
		return $_modes;
	}
	
	public static function vxGetDefaultEditorMode() {
		return 0;
	}
	
	public static function vxGetCommentPermissions() {
		$_permissions = array(
			0 => '评论禁止',
			1 => '任何人都可以评论',
			2 => '我在 V2EX 上的好友可以评论'
		);
		return $_permissions;
	}
	
	public static function vxGetDefaultCommentPermission() {
		return 0;
	}
	
	public static function vxBuild($user_id, $weblog_id) {
		$start = microtime(true);
		$Weblog = new Weblog($weblog_id);
		if (($start - $Weblog->blg_lastbuilt) < BABEL_WEBLOG_BUILD_INTERVAL) {
			$_SESSION['babel_message_weblog'] = _vo_ico_silk('clock') . ' 距离上次构建时间尚不足 ' . BABEL_WEBLOG_BUILD_INTERVAL . ' 秒，本次操作取消，请等待 ' . (BABEL_WEBLOG_BUILD_INTERVAL - intval($start - $Weblog->blg_lastbuilt)) . ' 秒之后再试验';
		} else {
			require_once(BABEL_PREFIX . '/libs/textile/classTextile.php');
			require_once(BABEL_PREFIX . '/libs/htmlpurifier/library/HTMLPurifier.auto.php');
			$purifier_config = HTMLPurifier_Config::createDefault();
			$purifier_config->set('Core', 'Encoding', 'UTF-8');
			$purifier_config->set('HTML', 'Doctype', 'XHTML 1.0 Transitional');
			$purifier = new HTMLPurifier($purifier_config);
			$Textile = new Textile();

			$bytes = 0;
			$files = 0;
			
			/* check user home directory */
			$usr_dir = BABEL_WEBLOG_PREFIX . '/htdocs/' . $Weblog->blg_name;
			if (!file_exists($usr_dir)) {
				mkdir($usr_dir);
			}
			
			/* clean old files */
			foreach (glob($usr_dir . '/*.html') as $filename) {
				unlink($filename);
			}
			foreach (glob($usr_dir . '/*.css') as $filename) {
				unlink($filename);
			}
			foreach (glob($usr_dir . '/*.rss') as $filename) {
				unlink($filename);
			}
			
			$s = new Smarty();
			$s->template_dir = BABEL_PREFIX . '/res/weblog/themes/' . $Weblog->blg_theme;
			$s->compile_dir = BABEL_PREFIX . '/tplc';
			$s->cache_dir = BABEL_PREFIX . '/cache/smarty';
			$s->config_dir = BABEL_PREFIX . '/cfg';
			
			$s->assign('ico_feed', 'http://' . BABEL_WEBLOG_SITE_STATIC . '/img/icons/silk/feed.png');
			
			$s->assign('site_theme', $Weblog->blg_theme);
			$s->assign('site_static', BABEL_WEBLOG_SITE_STATIC);
			$s->assign('site_babel', BABEL_DNS_NAME);
			$s->assign('site_weblog_root', 'http://' . BABEL_WEBLOG_SITE . '/' . $Weblog->blg_name . '/');
			$s->assign('site_url', 'http://' . BABEL_WEBLOG_SITE . '/' . $Weblog->blg_name . '/');
			$s->assign('site_title', make_plaintext($Weblog->blg_title));
			$s->assign('site_description', make_plaintext($Weblog->blg_description));
			$s->assign('site_category', make_plaintext(Vocabulary::site_name));
			$s->assign('site_lang', 'en');
			
			$s->assign('built', date('Y-n-j G:i:s T', time()));
			
			$s->assign('user_nick', $Weblog->usr_nick);
			
			$sql = "SELECT DISTINCT bet_tag FROM babel_weblog_entry_tag WHERE bet_eid IN (SELECT bge_id FROM babel_weblog_entry WHERE bge_pid = {$Weblog->blg_id}) ORDER BY bet_tag ASC";
			
			$rs = mysql_query($sql);
			
			$_tags = array();
			while ($_tag = mysql_fetch_array($rs)) {
				$_tags[] = $_tag;
			}
			mysql_free_result($rs);
			
			$s->assign('tags', $_tags);
			
			/* S: index.smarty */
			
			$sql = "SELECT bge_id, bge_title, bge_body, bge_mode, bge_tags, bge_comments, bge_trackbacks, bge_comment_permission, bge_published, usr_id, usr_nick FROM babel_weblog_entry, babel_user WHERE bge_uid = usr_id AND bge_uid = {$Weblog->usr_id} AND bge_pid = {$Weblog->blg_id} AND bge_status = 1 ORDER BY bge_published DESC LIMIT 10";
			$rs = mysql_query($sql);
			$_entries = array();
			$i = 0;
			while ($_entry = mysql_fetch_array($rs)) {
				$i++;
				$_entries[$_entry['bge_id']] = $_entry;
				$_entries[$_entry['bge_id']]['bge_title_plain'] = make_plaintext($_entry['bge_title']);
				$_entries[$_entry['bge_id']]['usr_nick_plain'] = make_plaintext($_entry['usr_nick']);
				$_entries[$_entry['bge_id']]['usr_nick_url'] = urlencode($_entry['usr_nick']);
				$_entries[$_entry['bge_id']]['bge_published_plain'] = date('Y-n-j G:i:s T', $_entry['bge_published']);
				switch (intval($_entry['bge_mode'])) {
					case 0: // plain text
						$_entries[$_entry['bge_id']]['bge_body_plain'] = make_plaintext(trim($_entry['bge_body']));
						break;
					case 1: // html
						$_entries[$_entry['bge_id']]['bge_body_plain'] = $purifier->purify($_entry['bge_body']);
						break;
					case 2: // ubb
						$_entries[$_entry['bge_id']]['bge_body_plain'] = format_ubb($_entry['bge_body']);
						break;
					case 3: // textile
						$_entries[$_entry['bge_id']]['bge_body_plain'] = $purifier->purify($Textile->TextileThis($_entry['bge_body']));
						break;
				}
				$_entries[$_entry['bge_id']]['bge_body_plain_rss'] = htmlspecialchars($_entries[$_entry['bge_id']]['bge_body_plain']);
				if ($_entry['bge_tags'] == '') {
					$_entries[$_entry['bge_id']]['bge_tags_plain'] = '';
				} else {
					$_entries[$_entry['bge_id']]['bge_tags_plain'] = Weblog::vxMakeTagLink($_entry['bge_tags']);
				}
			}
			mysql_free_result($rs);
			
			$s->assign('entries', $_entries);

			$file_index = $usr_dir . '/index.html';
			$o_index = $s->fetch('index.smarty');
			$files++;
			$bytes += file_put_contents($file_index, $o_index);
			
			$file_feed_main = $usr_dir . '/index.rss';
			$o_feed_main = $s->fetch('feed.smarty');
			$files++;
			$bytes += file_put_contents($file_feed_main, $o_feed_main);
			
			/* E: index.smarty */
			
			/* S: tag.smarty */
			
			foreach ($_tags as $tag) {
				$s->assign('tag_cur', $tag['bet_tag']);
				$tag_sql = mysql_real_escape_string($tag['bet_tag']);
				$sql = "SELECT bge_id, bge_title, bge_body, bge_tags, bge_comments, bge_trackbacks, bge_mode, bge_comment_permission, bge_published, usr_id, usr_nick FROM babel_weblog_entry, babel_user WHERE bge_uid = usr_id AND bge_uid = {$Weblog->usr_id} AND bge_pid = {$Weblog->blg_id} AND bge_status = 1 AND bge_id IN (SELECT bet_eid FROM babel_weblog_entry_tag WHERE bet_tag = '{$tag_sql}') ORDER BY bge_published DESC";
				$rs = mysql_query($sql);
				$_entries = array();
				$i = 0;
				while ($_entry = mysql_fetch_array($rs)) {
					$i++;
					$_entries[$_entry['bge_id']] = $_entry;
					$_entries[$_entry['bge_id']]['bge_title_plain'] = make_plaintext($_entry['bge_title']);
					$_entries[$_entry['bge_id']]['usr_nick_plain'] = make_plaintext($_entry['usr_nick']);
					$_entries[$_entry['bge_id']]['usr_nick_url'] = urlencode($_entry['usr_nick']);
					$_entries[$_entry['bge_id']]['bge_published_plain'] = date('Y-n-j G:i:s T', $_entry['bge_published']);
					switch (intval($_entry['bge_mode'])) {
						case 0: // plain text
							$_entries[$_entry['bge_id']]['bge_body_plain'] = make_plaintext(trim($_entry['bge_body']));
							break;
						case 1: // html
							$_entries[$_entry['bge_id']]['bge_body_plain'] = $purifier->purify($_entry['bge_body']);
							break;
						case 2: // ubb
							$_entries[$_entry['bge_id']]['bge_body_plain'] = format_ubb($_entry['bge_body']);
							break;
						case 3: // textile
							$_entries[$_entry['bge_id']]['bge_body_plain'] = $purifier->purify($Textile->TextileThis($_entry['bge_body']));
							break;
					}
					$_entries[$_entry['bge_id']]['bge_body_plain_rss'] = htmlspecialchars($_entries[$_entry['bge_id']]['bge_body_plain']);
					if ($_entry['bge_tags'] == '') {
						$_entries[$_entry['bge_id']]['bge_tags_plain'] = '';
					} else {
						$_entries[$_entry['bge_id']]['bge_tags_plain'] = Weblog::vxMakeTagLink($_entry['bge_tags']);
					}
				}
				mysql_free_result($rs);
				
				$s->assign('entries', $_entries);
	
				$file_tag = $usr_dir . '/tag-' . $tag['bet_tag'] . '.html';
				$o_tag = $s->fetch('tag.smarty');
				$files++;
				$bytes += file_put_contents($file_tag, $o_tag);
				
				$file_feed_tag = $usr_dir . '/tag-' . $tag['bet_tag'] . '.rss';
				$o_feed_tag = $s->fetch('feed_tag.smarty');
				$files++;
				$bytes += file_put_contents($file_feed_tag, $o_feed_tag);
			}
			
			/* E: tag.smarty */
			
			/* S: entry.smarty */
			
			$sql = "SELECT bge_id, bge_title, bge_body, bge_tags, bge_comments, bge_trackbacks, bge_mode, bge_comment_permission, bge_published, usr_id, usr_nick FROM babel_weblog_entry, babel_user WHERE bge_uid = usr_id AND bge_uid = {$Weblog->usr_id} AND bge_pid = {$Weblog->blg_id} AND bge_status = 1 ORDER BY bge_published DESC";
			$rs = mysql_query($sql);
			$i = 0;
			while ($_entry = mysql_fetch_array($rs)) {
				$_entry['bge_title_plain'] = make_plaintext($_entry['bge_title']);
				$_entry['usr_nick_plain'] = make_plaintext($_entry['usr_nick']);
				$_entry['usr_nick_url'] = urlencode($_entry['usr_nick']);
				$_entry['bge_body_plain'] = make_plaintext($_entry['bge_body']);
				$_entry['bge_published_plain'] = date('Y-n-j G:i:s T', $_entry['bge_published']);
				switch (intval($_entry['bge_mode'])) {
					case 0: // plain text
						$_entry['bge_body_plain'] = make_plaintext(trim($_entry['bge_body']));
						break;
					case 1: // html
						$_entry['bge_body_plain'] = $purifier->purify($_entry['bge_body']);
						break;
					case 2: // ubb
						$_entry['bge_body_plain'] = format_ubb($_entry['bge_body']);
						break;
					case 3: // textile
						$_entry['bge_body_plain'] = $purifier->purify($Textile->TextileThis($_entry['bge_body']));
						break;
				}
				if ($_entry['bge_tags'] == '') {
					$_entry['bge_tags_plain'] = '';
				} else {
					$_entry['bge_tags_plain'] = Weblog::vxMakeTagLink($_entry['bge_tags']);
				}
				$file_entry = $usr_dir . '/entry-' . $_entry['bge_id'] . '.html';
				$s->assign('entry', $_entry);
				$o_entry = $s->fetch('entry.smarty');
				$files++;
				$bytes += file_put_contents($file_entry, $o_entry);
			}
			
			/* style.smarty */
			$file_style = $usr_dir . '/style.css';
			$s->left_delimiter = '[';
			$s->right_delimiter = ']';
			$o_style = $s->fetch('style.smarty');
			$files++;
			$bytes += file_put_contents($file_style, $o_style);
			$s->left_delimiter = '{';
			$s->right_delimiter = '}';
			$Weblog->vxAddBuild();
			$Weblog->vxTouchBuild();
			$end = microtime(true);
			$elapsed = $end - $start;
			$_SESSION['babel_message_weblog'] = _vo_ico_silk('tick') . ' 博客网站 ' . make_plaintext($Weblog->blg_title) . ' 重新构建成功，' . $files . ' 个文件共写入了 ' . $bytes . ' 字节，共耗时 <small>' . $elapsed . '</small> 秒，<a href="http://' . BABEL_WEBLOG_SITE . '/' . $Weblog->blg_name . '" class="t" target="_blank">现在查看</a> <img src="/img/ext.png" align="absmiddle" />';
		}
	}
	
	public static function vxDestroy($user_id, $weblog_id) {
		$Weblog = new Weblog($weblog_id);
		
		/* 1: Unlink all files */
		
		$usr_dir = BABEL_WEBLOG_PREFIX . '/htdocs/' . $Weblog->blg_name;
		
		if (file_exists($usr_dir)) {
			foreach (glob($usr_dir . '/*.html') as $filename) {
				unlink($filename);
			}
			foreach (glob($usr_dir . '/*.css') as $filename) {
				unlink($filename);
			}
			foreach (glob($usr_dir . '/*.rss') as $filename) {
				unlink($filename);
			}
			rmdir($usr_dir);
		}
		
		/* 2: If it has portraits */
		
		if ($Weblog->blg_portrait != '') {
			unlink(BABEL_PREFIX . '/htdocs/img/b/' . $Weblog->blg_portrait . '.' . BABEL_PORTRAIT_EXT);
			unlink(BABEL_PREFIX . '/htdocs/img/b/' . $Weblog->blg_portrait . '_s.' . BABEL_PORTRAIT_EXT);
			unlink(BABEL_PREFIX . '/htdocs/img/b/' . $Weblog->blg_portrait . '_n.' . BABEL_PORTRAIT_EXT);
			mysql_unbuffered_query("DELETE FROM babel_weblog_portrait WHERE bgp_filename = '{$Weblog->blg_portrait}'");
			mysql_unbuffered_query("DELETE FROM babel_weblog_portrait WHERE bgp_filename = '{$Weblog->blg_portrait}_s'");
			mysql_unbuffered_query("DELETE FROM babel_weblog_portrait WHERE bgp_filename = '{$Weblog->blg_portrait}_n'");
		}
		
		/* 3: Clean all database records */
		
		mysql_unbuffered_query("DELETE FROM babel_weblog_entry_tag WHERE bet_eid IN (SELECT bge_id FROM babel_weblog_entry WHERE bge_pid = {$weblog_id})");
		
		mysql_unbuffered_query("DELETE FROM babel_weblog_entry WHERE bge_pid = {$weblog_id}");
		
		mysql_unbuffered_query("DELETE FROM babel_weblog WHERE blg_id = {$weblog_id}");
		
		$_SESSION['babel_message_weblog'] = '博客网站 <strong>' . make_plaintext($Weblog->blg_title) . '</strong> 已经彻底关闭，全部相关数据清除完毕';
	}
}
?>