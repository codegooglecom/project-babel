<?php
/* Project Babel
*  Author: Livid Torvalds
*  File: /htdocs/core/NodeCore.php
*  Usage: Node Class
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

/* S Node class */

class Node {
	var $db;

	var $nod_id;
	var $nod_pid;
	var $nod_uid;
	var $nod_sid;
	var $nod_level;
	var $nod_name;
	var $nod_title;
	var $nod_description;
	var $nod_header;
	var $nod_footer;
	var $nod_topics;
	var $nod_favs;
	var $nod_created;
	var $nod_lastupdated;
	
	var $usr_id;
	var $usr_nick;
	
	public function __construct($node_id, $db) {
		$this->db =& $db;
		$sql = "SELECT nod_id, nod_pid, nod_uid, nod_sid, nod_level, nod_name, nod_title, nod_description, nod_header, nod_footer, nod_topics, nod_favs, nod_created, nod_lastupdated, usr_id, usr_nick FROM babel_node, babel_user WHERE nod_uid = usr_id AND nod_id = {$node_id}";
		$rs = mysql_query($sql, $this->db);
		$O = mysql_fetch_object($rs);
		mysql_free_result($rs);
		$this->nod_id = $O->nod_id;
		$this->nod_pid = $O->nod_pid;
		$this->nod_uid = $O->nod_uid;
		$this->nod_sid = $O->nod_sid;
		$this->nod_level = $O->nod_level;
		$this->nod_name = $O->nod_name;
		$this->nod_title = $O->nod_title;
		$this->nod_description = $O->nod_description;
		$this->nod_header = $O->nod_header;
		$this->nod_footer = $O->nod_footer;
		$this->nod_topics = $O->nod_topics;
		$this->nod_favs = $O->nod_favs;
		$this->nod_created = $O->nod_created;
		$this->nod_lastupdated = $O->nod_lastupdated;
		$this->usr_id = $O->usr_id;
		$this->usr_nick = $O->usr_nick;
		$O = null;
	}
	
	public function __destruct() {
	}
	
	public function vxGetNodeInfo($node_id) {
		$sql = "SELECT nod_id, nod_name, nod_title FROM babel_node WHERE nod_id = {$node_id}";
		$rs = mysql_query($sql, $this->db);
		$Node = mysql_fetch_object($rs);
		mysql_free_result($rs);
		return $Node;
	}
	
	public function vxUpdateTopics($board_id = '') {
		if ($board_id == '') {
			$board_id = $this->nod_id;
		}
		$_t = time();
		$sql = "SELECT COUNT(tpc_id) FROM babel_topic WHERE tpc_pid = {$board_id}";
		$rs = mysql_query($sql, $this->db);
		$nod_topics = mysql_result($rs, 0, 0);
		mysql_free_result($rs);
		$sql = "UPDATE babel_node SET nod_topics = {$nod_topics}, nod_lastupdated = {$_t} WHERE nod_id = {$board_id} LIMIT 1";
		mysql_query($sql, $this->db);
		if (mysql_affected_rows($this->db) == 1) {
			return true;
		} else {
			return false;
		}
	}
	
	public function vxUpdateFavs($board_id = '') {
		if ($board_id == '') {
			$board_id = $this->nod_id;
		}
		
		$sql = "SELECT COUNT(fav_id) FROM babel_favorite WHERE fav_res = '{$board_id}' AND fav_type = 1";
		
		$rs = mysql_query($sql, $this->db);
		$nod_favs = mysql_result($rs, 0, 0);
		mysql_free_result($rs);
		
		$sql = "UPDATE babel_node SET nod_favs = {$nod_favs} WHERE nod_id = {$board_id} LIMIT 1";
		
		mysql_query($sql, $this->db);
		if (mysql_affected_rows($this->db) == 1) {
			return true;
		} else {
			return false;
		}
	}
	
	public function vxGetNodeChildren($section_id = '') {
		if ($section_id == '') {
			$section_id = $this->nod_id;
		}
		
		$sql = "SELECT nod_id, nod_name, nod_title FROM babel_node WHERE nod_pid = {$section_id} ORDER BY nod_topics DESC";
		$rs = mysql_query($sql, $this->db);
		if (mysql_num_rows($rs) > 0) {
			return $rs;
		} else {
			return false;
		}
	}
	
	public function vxDrawChannels($board_id = '', $exclude = 0) {
		echo ('<span class="chl">');
		if ($board_id == '') {
			$board_id = $this->nod_id;
		}
		
		$sql = "SELECT chl_id, chl_title, chl_url FROM babel_channel WHERE chl_pid = {$board_id} ORDER BY chl_title";
		
		$rs = mysql_query($sql, $this->db);
		$count = mysql_num_rows($rs);
		if ($count > 0) {
			echo($exclude != 0 ? '，' . $count . ' 个其他相关频道<br />':'，' . $count . ' 个相关频道</span>');
			_v_hr();
			echo('<div class="channels">');
			while ($Channel = mysql_fetch_object($rs)) {
				if (trim($Channel->chl_title) == '') {
					$Channel->chl_title = $Channel->chl_url;
				}
				if ($Channel->chl_id == $exclude) {
					echo('<strong class="p_cur"><img src="' . CDN_UI . 'img/icons/silk/bullet_feed.png" align="absmiddle" />' . make_plaintext($Channel->chl_title) . '</strong> ');
				} else {
					$css_color = rand_color();
					echo('<img src="' . CDN_UI . 'img/icons/silk/bullet_feed.png" align="absmiddle" /><a href="/channel/view/' . $Channel->chl_id . '.html" class="var" style="color: ' . $css_color . '">' . make_plaintext($Channel->chl_title) . '</a> ');
				}
			}
			mysql_free_result($rs);
			echo('</div>');
			return true;
		} else {
			echo('</span>');
			mysql_free_result($rs);
			return false;
		}
	}
	
	public function vxDrawAlsoFav($c) {
		$board_id = $this->nod_id;
		
		if ($o = $c->load('babel_node_fav_also_' . $board_id)) {
		} else {
			$sql = "SELECT fav_uid FROM babel_favorite WHERE fav_res = {$board_id} AND fav_type = 1";
			$rs = mysql_query($sql);
			$_users = array();
			while ($_user = mysql_fetch_array($rs)) {
				$_users[] = $_user['fav_uid'];
			}
			mysql_free_result($rs);
			$_nodes = array();
			$o = '';
			if (count($_users) > 0) {
				foreach ($_users as $usr_id) {
					$sql = "SELECT fav_res FROM babel_favorite WHERE fav_uid = {$usr_id} AND fav_type = 1";
					$rs = mysql_query($sql);
					if (mysql_num_rows($rs) > 0) {
						while ($_node = mysql_fetch_array($rs)) {
							if (array_key_exists(intval($_node['fav_res']), $_nodes)) {
								$_nodes[$_node['fav_res']]++;
							} else {
								$_nodes[$_node['fav_res']] = 1;
							}
						}
					}
					mysql_free_result($rs);
				}
			}
			if (count($_nodes) > 0) {
				arsort($_nodes);
				$_nodes_keys = array_keys($_nodes);
				$sql = "SELECT nod_id, nod_title, nod_name FROM babel_node WHERE nod_id IN (" . implode(',', $_nodes_keys) . ")";
				$rs = mysql_query($sql);
				if (mysql_num_rows($rs) > 0) {
					while ($_node = mysql_fetch_array($rs)) {
						$_nodes_names[$_node['nod_id']] = $_node['nod_name'];
						$_nodes_titles[$_node['nod_id']] = $_node['nod_title'];
					}
					$o .= _vo_hr();
					$o .= '<span class="tip">' . make_plaintext($this->nod_title) . '</span> <span class="tip_i">的收藏者也同时收藏了</span> ';
					$i = 0;
					if (count($_nodes_keys) > 7) {
						$max = 8;
					} else {
						$max = count($_nodes_keys);
					}
					while ($i < $max) {
						$i++;
						$css_color = rand_color();
						$o .= '<a href="/go/' . $_nodes_names[$_nodes_keys[$i]] . '" class="var" style="color: ' . $css_color . '">' . make_plaintext($_nodes_titles[$_nodes_keys[$i]]) . '</a> <small>(' . $_nodes[$_nodes_keys[$i]] . ')</small> ';
					}
				} else {
					$o = '';
				}
				mysql_free_result($rs);
			} else {
				$o = '';
			}
			$c->save($o, 'babel_node_fav_also_' . $board_id);
		}
		echo $o;
	}
	
	public function vxDrawStock($c) {
		if (!BABEL_FEATURE_NODE_STOCK) {
			return false;
		} else {
			if ($o = $c->load('babel_node_stock_' . $this->nod_id)) {
				if ($o == '') {
					return false;
				} else {
					echo $o;
					return true;
				}
			} else {
				$special = false;
				
				$fix = '';
				
				if (preg_match('/^6([0-9]{5})$/', $this->nod_name) || $this->nod_name == 'sh000001') {
					$special = 'sh';
					if ($this->nod_name == 'sh000001') {
						$this->nod_name = '000001';
						$fix = '+' . urlencode('-深发展');
					}
				}
				
				if ((preg_match('/^0([0-9]{5})$/', $this->nod_name) || $this->nod_name == '399001' || preg_match('/^3([0-9]{5})$/', $this->nod_name)) && $special == false) {
					$special = 'sz';
				}
				
				$o = '';
				
				if ($special == 'sh' || $special == 'sz') {
					$o .= '<tr><td align="center" class="hf" colspan="4" style="border-top: 1px solid #EEE;">';
					$o .= '<script type="text/javascript"><!--
google_ad_client = "pub-9823529788289591";
google_alternate_color = "FFFFFF";
google_ad_width = 728;
google_ad_height = 90;
google_ad_format = "728x90_as";
google_ad_type = "text_image";
//2007-06-14: V2EX
google_ad_channel = "0814641667";
google_color_border = "FFFFFF";
google_color_bg = "FFFFFF";
google_color_link = "999999";
google_color_text = "000000";
google_color_url = "00CC00";
//-->
</script>
<script type="text/javascript"
  src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>';
					$o .= '</td></tr>';
					
					$news = fetch_rss('http://news.google.com/news?hl=zh-CN&ned=cn&q=' . $this->nod_name . '+%7C+' . urlencode($this->nod_title) . $fix . '&ie=UTF-8&output=rss');
					
					$blogs = fetch_rss('http://blogsearch.google.com/blogsearch_feeds?hl=en&q=' . $this->nod_name . '+%7C+' . urlencode($this->nod_title) . $fix . '&ie=utf-8&num=10&output=rss');
					
					$tn = fetch_rss('http://feeds.technorati.com/search/' . $this->nod_name);
					
					$o .= '<tr><td align="left" class="hf" colspan="4" style="border-top: 1px solid #EEE;">';
					$o .= '<span class="tip_i">';
					$o .= '<img src="/img/pico_right.gif" align="absmiddle" /> ';
					$o .= $this->nod_header . ' <a href="#stock_chart" class="t">行情图表</a> | <a href="#stock_blogs" class="t"><img src="/img/googleblogsearch.gif" align="absmiddle" alt="Google Blog Search results for ' . $this->nod_title . '" border="0" /></a> | <a href="#stock_news" class="t">新闻资讯</a> | <a href="#stock_tn"><img src="/img/technorati.gif" align="absmiddle" alt="Technorati results for ' . $this->nod_title . '" border="0" /></a></span>';
					$o .= _vo_hr();
					$o .= '<div class="notify" style="margin-bottom: 5px;"><div style="float: right;"><a href="#;" onclick="window.scrollTo(0, 0);">回到顶部</a></div><span style="font-size: 14px;">';
					$o .= _vo_ico_silk('chart_line');
					$o .= ' ' . $this->nod_header . ' 的行情图表 <a name="stock_chart"></a></span></div>';
					$o .= '<div align="center">';
					$o .= '<script type="text/javascript" src="/js/babel_stock_switcher.js"> </script>';
					$o .= '<script type="text/javascript">market = "' . $special . '"; code = "' . $this->nod_name . '";</script>';
					$o .= '<span class="tip_i">图表切换<img src="/img/pico_right.gif" align="absmiddle" /> <a href="#;" onclick="stock_get_realtime();" class="t">分时行情</a> | <a href="#;" onclick="stock_get_k_min5();" class="t">5 分钟 K 线</a> | <a href="#;" onclick="stock_get_k_daily();" class="t">日 K 线</a> | <a href="#;" onclick="stock_get_k_weekly();" class="t">周 K 线</a> | <a href="#;" onclick="stock_get_k_monthly();" class="t">月 K 线</a> | <a href="#;" onclick="stock_get_rsi();" class="o">RSI</a> | <a href="#;" onclick="stock_get_macd();" class="o">MACD</a> | <a href="#;" onclick="stock_get_kdj();" class="o">KDJ</a> | <a href="#;" onclick="stock_get_mike();" class="o">MIKE</a></span><br />';
					$o .= '<img id="stock_chart" src="http://image.sinajs.cn/newchart/min/n/' . $special . $this->nod_name . '.gif?' . time() . '" class="code" /></div>';
					$o .= _vo_hr();
					$o .= '<div class="notify"><div style="float: right;"><a href="#;" onclick="window.scrollTo(0, 0);">回到顶部</a></div><span style="font-size: 14px;">';
					$o .= _vo_ico_silk('comments');
					$o .= ' 来自 Google Blog Search 的关于 ' . $this->nod_header . ' 的最新消息 <a name="stock_blogs"></a></span></div>';
					$i = 0;
					foreach ($blogs->items as $blog) {
						$i++;
						$css_class = $i % 2 == 0 ? 'even' : 'odd';
						$d = str_replace('<b>', '', $blog['description']);
						$d = str_replace('</b>', '', $d);
						
						$t = str_replace('<b>', '', $blog['title']);
						$t = str_replace('</b>', '', $t);
						
						$o .= '<div class="geo_home_entry_' . $css_class . '">';
						$o .= '<span style="font-size: 13px; display: block; margin-bottom: 5px;">';
						$o .= _vo_ico_silk('bullet_blue');
						$o .= ' <a href="' . $blog['link'] . '" class="var" style="color: ' . rand_color() . '">' . $t . '</a></span>';
						$o .= $d;
						$o .= '</div>';
						unset($blog);
					}
					$o .= _vo_hr();
					$o .= '<div class="notify"><div style="float: right;"><a href="#;" onclick="window.scrollTo(0, 0);">回到顶部</a></div><span style="font-size: 14px;">';
					$o .= _vo_ico_silk('world');
					$o .= ' 来自互联网的关于 ' . $this->nod_header . ' 的最新资讯 <a name="stock_news"></a></span></div>';
					$i = 0;
					foreach ($news->items as $item) {
						$i++;
						$css_class = $i % 2 == 0 ? 'even' : 'odd';
						
						$n = str_replace('<br><table border=0 width= valign=top cellpadding=2 cellspacing=7>', '<table border=0 width= valign=top cellpadding=0 cellspacing=2>', $item['description']);
						$n = str_replace('<font color=#CC0033>' . $this->nod_title . '</font>', $this->nod_title, $n);
						$n = str_replace('<font color=#CC0033>' . $this->nod_title . $this->nod_name . '</font>', $this->nod_title . $this->nod_name, $n);
						$n = str_replace('<font color=#CC0033>' . $this->nod_name . $this->nod_title . '</font>', $this->nod_name . $this->nod_title, $n);
						$n = str_replace('<font color=#CC0033>' . $this->nod_name . '</font>', $this->nod_name, $n);
						$n = preg_replace('/<a href="([^"]+)" target=_blank>([^<]+)<\/a><br>/', '<span style="font-size: 13px; display: block; margin-bottom: 5px;">' . _vo_ico_silk('bullet_black') . ' <a class="var" style="color: ' . rand_color() . '" rel="nofollow" href="$1" target="_blank">$2</a></span>',$n);
						$n = str_replace('<a class=p', '<img src="/img/pico_right.gif" align="absmiddle" /> <a class="t"', $n);
						$n = str_replace('<font size=-1>', '<font style="font-size: 12px;">', $n);
						$o .= '<div class="geo_home_entry_' . $css_class . '">' . $n . '</div>';
						unset($item);
					}
					$o .= _vo_hr();
					$o .= '<div class="notify"><div style="float: right;"><a href="#;" onclick="window.scrollTo(0, 0);">回到顶部</a></div><span style="font-size: 14px;">';
					$o .= _vo_ico_silk('comments');
					$o .= ' 来自 Technorati 的关于 ' . $this->nod_header . ' 的最新消息 <a name="stock_tn"></a></span></div>';
					$i = 0;
					foreach ($tn->items as $blog) {
						$i++;
						$css_class = $i % 2 == 0 ? 'even' : 'odd';
						$d = str_replace('<br /><br /><img width="1" height="1"', '<img width="1" height="1"', $blog['description']);
						$d = str_replace('Posted in', '<span class="tip"><small>Posted in</small></span>', $d);
						
						$t = $blog['title'];
						
						$o .= '<div class="geo_home_entry_' . $css_class . '">';
						$o .= '<span style="font-size: 13px; display: block; margin-bottom: 5px;">';
						$o .= _vo_ico_silk('bullet_blue');
						$o .= ' <a href="' . $blog['link'] . '" class="var" style="color: ' . rand_color() . '">' . $t . '</a></span>';
						$o .= $d;
						$o .= '</div>';
						unset($blog);
					}
					$o .= _vo_hr();
					$o .= '<span class="tip_i">各大财经网站关于 ' . $this->nod_title . ' (' . $this->nod_name . ') 的相关信息<img src="/img/pico_right.gif" align="absmiddle" /> ';
					$o .= '<a href="http://finance.sina.com.cn/realstock/company/' . $special . $this->nod_name . '/nc.shtml" class="var" style="color: ' . rand_color() . '" rel="external nofollow">新浪</a> | ';
					$o .= '<a href="http://stockdata.stock.hexun.com/dynamic/default.aspx?stockid=' . $this->nod_name . '" class="var" style="color: ' . rand_color() . '" rel="external nofollow">和讯</a> | ';
					$o .= '<a href="http://hq.eastmoney.com/' . $this->nod_name . '.html" class="var" style="color: ' . rand_color() . '" rel="external nofollow">东方财富网</a> | ';
					$o .= '<a href="http://quote.stockstar.com/stock/external_quote.asp?code=' . $special . 'ag' . $this->nod_name . '" class="var" style="color: ' . rand_color() . '" rel="external nofollow">证券之星</a> | ';
					$o .= '<a href="http://share.jrj.com.cn/cominfo/ggxw_' . $this->nod_name . '.htm" class="var" style="color: ' . rand_color() . '" rel="external nofollow">金融界</a>';
					$o .= '</span>';
					$o .= _vo_hr();
					$o .= '<a href="#;" onclick="window.scrollTo(0, 0)">回到顶部</a>';
					$o .= '<script type="text/javascript">stock_charts_preload();</script>';
					$o .= '</td></tr>';
					echo $o;
					$c->save($o, 'babel_node_stock_' . $this->nod_id);
					return true;
				} else {
					$c->save($o, 'babel_node_stock_' . $this->nod_id);
					return false;
				}
			}
		}
	}
	
	private function vxTrimKijijiTitle($title) {
		if (mb_ereg_match('最新的客齐集广告', $title)) {
			mb_ereg('最新的客齐集广告 所在地：(.+) 分类：(.+)', $title, $m);
			return $m[2];
		} else {
			return $title;
		}
	}
}

/* E Node class */
?>
