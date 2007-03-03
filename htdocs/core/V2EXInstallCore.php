<?php
/* Project Babel
 *
 * Author: Livid Liu <v2ex.livid@mac.com>
 * File: /htdocs/core/InstallCore.php
 * Usage: a Quick and Dirty script for fast installation
 * Format: 1 tab indent(4 spaces), LF, UTF-8, no-BOM
 *
 * Subversion Keywords:
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 * $URL$
 *
 * Copyright (C) 2006 Livid Liu <v2ex.livid@mac.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

define('V2EX_BABEL', 1);
require('Settings.php');

class Install {
	var $db;
	
	public function __construct() {
		$this->db = mysql_connect(BABEL_DB_HOSTNAME, BABEL_DB_USERNAME, BABEL_DB_PASSWORD);
		mysql_select_db(BABEL_DB_SCHEMATA, $this->db);
		mysql_query("SET NAMES utf8", $this->db);
		mysql_query("SET CHARACTER SET utf8", $this->db);
		mysql_query("SET COLLATION_CONNECTION='utf8_general_ci'", $this->db);
		header('Content-type: text/html;charset=UTF-8');
		echo('Install Core init<br /><br />');
	}
	
	public function __destruct() {
		mysql_close($this->db);
	}
	
	public function vxSetupWeightV2EX() {
		mysql_unbuffered_query("UPDATE babel_node SET nod_weight = 100 WHERE nod_name = 'limbo'");
		mysql_unbuffered_query("UPDATE babel_node SET nod_weight = 1000 WHERE nod_name = 'mechanus'");
		mysql_unbuffered_query("UPDATE babel_node SET nod_weight = 10000 WHERE nod_name = 'thegraywaste'");
		mysql_unbuffered_query("UPDATE babel_node SET nod_weight = 10 WHERE nod_name = 'sigil'");
		mysql_unbuffered_query("UPDATE babel_node SET nod_weight = 1 WHERE nod_name = 'elysium'");
	}
	
	public function vxSetupSections() {
		$this->vxSetupSection("UPDATE babel_node SET nod_sid = 1, nod_level = 0, nod_title = '异域', nod_header = '异域', nod_footer = '' WHERE nod_id = 1 LIMIT 1");
		$this->vxSetupSection("UPDATE babel_node SET nod_sid = 1, nod_title = '混沌海', nod_header = '', nod_footer = '' WHERE nod_id = 2 LIMIT 1");
		$this->vxSetupSection("UPDATE babel_node SET nod_sid = 1, nod_title = '机械境', nod_header = '', nod_footer = '' WHERE nod_id = 3 LIMIT 1");
		$this->vxSetupSection("UPDATE babel_node SET nod_sid = 1, nod_title = '灰色荒野', nod_header = '', nod_footer = '' WHERE nod_id = 4 LIMIT 1");
	}
	
	public function vxSetupSectionExtra($name, $title, $description = '', $header = '', $footer = '') {
		$sql = "SELECT nod_id FROM babel_node WHERE nod_name = '{$name}' LIMIT 1";
		$rs = mysql_query($sql);
		if (mysql_num_rows($rs) == 1) {
			$_t = time();
			$sql = "UPDATE babel_node SET nod_title = '{$title}', nod_description = '{$description}', nod_header = '{$header}', nod_footer = '{$footer}', nod_lastupdated = {$_t} WHERE nod_name = '{$name}' LIMIT";
			mysql_query($sql, $this->db);
			if (mysql_affected_rows($this->db) == 1) {
				echo ('OK: ' . $sql . '<br />');
				return true;
			} else {
				echo('NU: ' . $sql . '<br />');
				return false;
			}
		} else {
			$_t = time();
			$sql = "INSERT INTO babel_node(nod_pid, nod_uid, nod_sid, nod_level, nod_name, nod_title, nod_description, nod_header, nod_footer, nod_created, nod_lastupdated) VALUES(1, 1, 5, 1, '{$name}', '{$title}', '{$description}', '{$header}', '{$footer}', {$_t}, {$_t})";
			mysql_query($sql, $this->db);
			if (mysql_affected_rows($this->db) == 1) {
				echo ('OK: ' . $sql . '<br />');
				return true;
			} else {
				echo ('NU: ' . $sql . '<br />');
				return false;
			}
		}
	}
	
	public function vxSetupSection($stmt) {
		$sql = $stmt;
		mysql_query($sql);
		if (mysql_affected_rows() == 1) {
			echo 'OK: ' . $sql . '<br />';
		} else {
			echo 'NU ' . mysql_affected_rows() . ': ' . $sql . '<br />';
		}
	}
	
	public function vxSetupChannelById($board_id, $url) {
		$url = mysql_real_escape_string($url);
		$t = time();
		$sql = "INSERT INTO babel_channel(chl_pid, chl_url, chl_created) VALUES({$board_id}, '{$url}', {$t})";
		$sql_exist = "SELECT chl_id FROM babel_channel WHERE chl_url = '{$url}' AND chl_pid = {$board_id}";
		$rs = mysql_query($sql_exist);
		if (mysql_num_rows($rs) == 0) {
			mysql_query($sql) or die(mysql_error());
			if (mysql_affected_rows() == 1) {
				echo('OK: ' . $sql . '<br />');
			} else {
				echo('FD: ' . $sql . '<br />');
			}
		} else {
			echo('EX: ' . $sql . '<br />');
		}
	}
	
	public function vxSetupChannelByName($board_name, $url) {
		$url = mysql_real_escape_string($url);
		$t = time();
		$sql = "SELECT nod_id FROM babel_node WHERE nod_name = '{$board_name}' LIMIT 1";
		$board_id = mysql_result(mysql_query($sql), 0, 0);
		$sql = "INSERT INTO babel_channel(chl_pid, chl_url, chl_created) VALUES({$board_id}, '{$url}', {$t})";
		$sql_exist = "SELECT chl_id FROM babel_channel WHERE chl_url = '{$url}' AND chl_pid = {$board_id}";
		$rs = mysql_query($sql_exist);
		if (mysql_num_rows($rs) == 0) {
			mysql_query($sql) or die(mysql_error());
			if (mysql_affected_rows() == 1) {
				echo('OK: ' . $sql . '<br />');
			} else {
				echo('FD: ' . $sql . '<br />');
			}
		} else {
			echo('EX: ' . $sql . '<br />');
		}
	}
	
	public function vxSetupRelatedByName($board_name, $url, $title) {
		$url = mysql_real_escape_string($url);
		$title = mysql_real_escape_string($title);
		$_t = time();
		$sql = "SELECT nod_id FROM babel_node WHERE nod_name = '{$board_name}' LIMIT 1";
		$board_id = mysql_result(mysql_query($sql), 0, 0);
		$sql = "INSERT INTO babel_related(rlt_pid, rlt_url, rlt_title, rlt_created) VALUES({$board_id}, '{$url}', '{$title}', {$_t})";
		$sql_exist = "SELECT rlt_id FROM babel_related WHERE rlt_url = '{$url}' AND rlt_pid = {$board_id}";
		$rs = mysql_query($sql_exist);
		if (mysql_num_rows($rs) == 0) {
			mysql_query($sql) or die(mysql_error());
			if (mysql_affected_rows() == 1) {
				echo('OK: ' . $sql . '<br />');
			} else {
				echo('FD: ' . $sql . '<br />');
			}
		} else {
			echo('EX: ' . $sql . '<br />');
		}
	}
	
	public function vxSetupKijijiChannels() {
		$cities = array('beijing','shanghai','guangzhou','changchun','chengdu','chongqing','dalian','guiyang','hangzhou','harbin','hefei','jinan','kunming','lanzhou','nanchang','nanjing','qingdao','shantou','shenyang','shenzhen','shijiazhuang','suzhou','taiyuan','tianjin','wuhan','xiamen','xian','yantai','zhengzhou');
		$ids = array(401, 4078, 4014, 4072, 4058, 4041, 4088, 4082);
		
		foreach ($cities as $city) {
			$sql = "SELECT nod_id FROM babel_node WHERE nod_name = '{$city}'";
			$rs = mysql_query($sql);
			if (mysql_num_rows($rs) == 1) {
				$Node = mysql_fetch_object($rs);
				mysql_free_result($rs);
				foreach ($ids as $cid) {
					$url = 'http://' . $city . '.kijiji.com.cn/f-SearchAdRss?RssFeedType=rss_2.0&CatId=' . $cid;
					$this->vxSetupChannel($Node->nod_id, $url);
				}
				$Node = null;
			} else {
				mysql_free_result($rs);
			}
		}
	}
	
	public function vxSetupBoard($board_name, $board_title, $board_pid, $board_sid, $board_uid, $board_level, $board_header = '', $board_footer = '', $board_description = '') {
		$board_name = mysql_real_escape_string($board_name);
		$board_title = mysql_real_escape_string($board_title);
		$board_header = mysql_real_escape_string($board_header);
		$board_footer = mysql_real_escape_string($board_footer);
		$board_description = mysql_real_escape_string($board_description);
		$board_created = time();
		$board_lastupdated = time();
		
		$sql = "INSERT INTO babel_node(nod_name, nod_title, nod_pid, nod_sid, nod_uid, nod_level, nod_header, nod_footer, nod_description, nod_created, nod_lastupdated) VALUES('{$board_name}', '{$board_title}', {$board_pid}, {$board_sid}, {$board_uid}, {$board_level}, '{$board_header}', '{$board_footer}', '{$board_description}', {$board_created}, {$board_lastupdated})";
		$sql_exist = "SELECT nod_id FROM babel_node WHERE nod_name = '{$board_name}'";
		$rs = mysql_query($sql_exist);
		if (mysql_num_rows($rs) > 0) {
			$Node = mysql_fetch_object($rs);
			mysql_free_result($rs);
			$sql_update = "UPDATE babel_node SET nod_title = '{$board_title}', nod_pid = {$board_pid}, nod_sid = {$board_sid}, nod_uid = {$board_uid}, nod_level = {$board_level}, nod_header = '{$board_header}', nod_footer = '{$board_footer}', nod_description = '{$board_description}' WHERE nod_id = {$Node->nod_id}";
			mysql_query($sql_update);
			if (mysql_affected_rows() == 1) {
				echo 'UD: ' . $sql_update . '<br />';
			} else {
				echo 'EX: ' . $sql_update . '<br />';
			}
		} else {
			mysql_query($sql) or die(mysql_error());
			if (mysql_affected_rows() == 1) {
				echo 'OK: ' . $sql . '<br />';
			} else {
				echo 'FD: ' . $sql . '<br />';
			}
		}
	}
}

$i = new Install();
$i->vxSetupWeightV2EX();
// elyisum
//$i->vxSetupSectionExtra('elysium', '极乐境');
$i->vxSetupBoard('music', '爱听音乐', 220, 220, 1, 2, '喜爱音乐的孩子不会变坏', '');
	$i->vxSetupRelatedByName('music', 'http://last.fm/', 'Last.fm');
$i->vxSetupBoard('gnr', "Guns N' Roses", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('lacrimosa', "Lacrimosa", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('gorillaz', "Gorillaz", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('jamesblunt', "James Blunt", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('metallica', "Metallica", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('radiohead', "Radiohead", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('blur', "Blur", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('u2', "U2", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('korn', "Korn", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('linkinpark', "Linkin Park", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('fortminor', "Fort Minor", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('feeder', "Feeder", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('dido', "Dido", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('cranberries', "The Cranberries", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('current93', "Current 93", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('scorpions', "Scorpions", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('50cent', "50 Cent", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('persephone', "Persephone", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('avrillavigne', "Avril Lavigne", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('bonjovi', "Bon Jovi", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('suede', "Suede", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('rhapsody', "Rhapsody", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('nirvana', "Nirvana", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('lakeoftears', "Lake of Tears", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('aqua', "Aqua", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('simpleplan', "Simple Plan", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('oasis', "Oasis", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('rem', "REM", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('pinkfloyd', "Pink Floyd", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('thedoors', "The Doors", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('police', "Police", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('limpbizkit', "Limp Bizkit", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('eagles', "Eagles", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('beatles', "The Beatles", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('coldplay', "Coldplay", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('christinaaguilera', 'Christina Aguilera', 220, 220, 1, 2, '', '');
$i->vxSetupBoard('damienrice', 'Damien Rice', 220, 220, 1, 2, '', '');
$i->vxSetupBoard('jaychou', "周杰伦", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('fayewong', "王菲", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('xuwei', "许巍", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('cheer', "陈绮贞", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('beyond', "Beyond", 220, 220, 1, 2, '', '');
$i->vxSetupBoard('davidtao', "陶喆", 220, 220, 1, 2, '', '');
	$i->vxSetupRelatedByName('davidtao', 'http://www.davidtao.com/', 'DavidTao.com');

//$i->vxSetupSectionExtra('sigil', '法印城');
// sigil
$i->vxSetupBoard('levis', "Levi's", 71, 71, 1, 2, '', '');
	$i->vxSetupRelatedByName('levis', 'http://www.levi.com.cn/', "Levi's 中国官方网站");
	$i->vxSetupRelatedByName('levis', 'http://www.levisstore.com/', "Levi's Store");
$i->vxSetupBoard('g-star', "G-STAR", 71, 71, 1, 2, 'RAW', '');
	$i->vxSetupRelatedByName('g-star', 'http://www.g-star.com/', "G-STAR RAW");
$i->vxSetupBoard('converse', "Converse", 71, 71, 1, 2, '', '');
	$i->vxSetupRelatedByName('converse', 'http://www.conslive.com/', '匡威网上专卖店');
$i->vxSetupBoard('gas', "GAS", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('nike', "Nike", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('superlovers', "SUPER LOVERS", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('izzue', "izzue", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('adidas', "Adidas", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('puma', "Puma", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('uniqlo', "UNIQLO", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('prada', "PRADA", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('fcuk', "FCUK", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('rbk', "Reebok", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('ck', "Calvin Klein", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('dior', "Dior", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('espirit', "Espirit", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('lee', "Lee", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('5thstreet', "5th Street", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('vans', "VANS", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('diesel', "DIESEL", 71, 71, 1, 2, '', '');
	$i->vxSetupRelatedByName('diesel', 'http://www.diesel.com/', 'D I E S E L');
$i->vxSetupBoard('kappa', "Kappa", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('westwood', "Vivienne Westwood", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('givenchy', "Givenchy", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('gucci', "Gucci", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('chanel', "Chanel", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('lanvin', "Lanvin", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('ysl', "Yves Saint Laurent", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('valentino', "Valentino", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('armani', "Giorgio Armani", 71, 71, 1, 2, '', '');
	$i->vxSetupRelatedByName('armani', 'http://www.armaniexchange.com/', 'Armani Exchange');
$i->vxSetupBoard('umbro', "Umbro", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('timberland', "Timberland", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('newbalance', "New Balance", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('cabbeen', "Cabbeen", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('zegna', "Ermenegildo Zegna", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('burberry', "Burberry", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('mango', "Mango", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('exr', "EXR", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('chevignon', "CHEVIGNON", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('colombia', "Colombia", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('northface', "North Face", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('cat', "CAT", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('montblanc', "Mont Blanc", 71, 71, 1, 2, '', '');
$i->vxSetupBoard('abercrombie', "Abercrombie & Fitch", 71, 71, 1, 2);

// limbo
$i->vxSetupBoard('kunming', '昆明', 2, 2, 1, 2, '', '');
	$i->vxSetupRelatedByName('kunming', 'http://www.ynu.edu.cn/', '云南大学');
	$i->vxSetupRelatedByName('kunming', 'http://kunming.kijiji.cn/', 'Kijiji - 昆明');
$i->vxSetupBoard('xiamen', '厦门', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('yangzhou', '扬州', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('shanghai', '上海', 2, 2, 1, 2, '', '');
	$i->vxSetupRelatedByName('shanghai', 'http://www.sjtu.edu.cn/', '上海交通大学');
	$i->vxSetupRelatedByName('shanghai', 'http://www.fudan.edu.cn/', '复旦大学');
	$i->vxSetupRelatedByName('shanghai', 'http://shanghai.kijiji.cn/', 'Kijiji - 上海');
$i->vxSetupBoard('harbin', '哈尔滨', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('qingdao', '青岛', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('weihai', '威海', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('haikou', '海口', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('sanya', '三亚', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('guilin', '桂林', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('fuzhou', '福州', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('rushan', '乳山', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('dongguan', '东莞', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('foshan', '佛山', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('lasa', '拉萨', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('jinan', '济南', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('shenzhen', '深圳', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('beijing', '北京', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('tianjin', '天津', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('changsha', '长沙', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('shenyang', '沈阳', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('zhengzhou', '郑州', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('yantai', '烟台', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('suzhou', '苏州', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('taiyuan', '太原', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('hefei', '合肥', 2, 2, 1, 2, '', '');
	$i->vxSetupRelatedByName('hefei', 'http://www.ahu.edu.cn/', '安徽大学');
$i->vxSetupBoard('shantou', '汕头', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('wulumuqi', '乌鲁木齐', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('qujing', '曲靖', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('taiyuan', '太原', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('shijiazhuang', '石家庄', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('wuhan', '武汉', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('tianjin', '天津', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('hongkong', '香港', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('macau', '澳门', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('taipei', '台北', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('chongqing', '重庆', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('chengdu', '成都', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('hangzhou', '杭州', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('xian', '西安', 2, 2, 1, 2, '', '');
	$i->vxSetupRelatedByName('xian', 'http://www.nwu.edu.cn/', '西北大学');
$i->vxSetupBoard('lijiang', '丽江', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('guangzhou', '广州', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('zhuhai', '珠海', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('ningbo', '宁波', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('nanjing', '南京', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('nanning', '南宁', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('nanchang', '南昌', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('tokyo', '东京', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('osaka', '大阪', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('london', 'London', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('toronto', 'Toronto', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('sydney', 'Sydney', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('paris', 'Paris', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('nyc', 'New York', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('chicago', 'Chicago', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('dali', '大理', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('guiyang', '贵阳', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('vatican', 'Vatican', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('dubai', 'Dubai', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('jerusalem', 'Jerusalem', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('tibet', '西藏', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('wenzhou', '温州', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('berlin', 'Berlin', 2, 2, 1, 2, '', '');
$i->vxSetupBoard('seoul', 'Seoul', 2, 2, 1, 2, '', '');

// mechanus
$i->vxSetupBoard('3dsmax', '3dsmax', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('maya', 'Maya', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('dreamweaver', 'Dreamweaver', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('fireworks', 'Fireworks', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('flash', 'Flash', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('photoshop', 'Photoshop', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('eva', 'Neon Genesis Evangelion', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('c', 'C/C++', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('csharp', 'C#', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('delphi', 'Delphi', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('logo', 'Logo', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('pascal', 'Pascal', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('wordpress', 'WordPress', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('bbpress', 'bbPress', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('adobe', 'Adobe', 3, 3, 1, 2, 'Adobe 产品讨论专区', '');
$i->vxSetupBoard('js', 'JavaScript', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('html', 'HTML', 3, 3, 1, 2, 'HTML 语言技术讨论专区', '');
$i->vxSetupBoard('mono', 'Mono', 3, 3, 1, 2, '', '');
	$i->vxSetupRelatedByName('mono', 'http://www.mono-project.com/', 'Mono');
	$i->vxSetupRelatedByName('mono', 'http://www.monodevelop.com/', 'MonoDevelop');
$i->vxSetupBoard('json', 'JavaScript Object Notation', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('yaml', "YAML Ain't Markup Language", 3, 3, 1, 2, '', '');
$i->vxSetupBoard('firefox', 'Mozilla Firefox', 3, 3, 1, 2, '<script type="text/javascript"><!--
google_ad_client = "pub-9823529788289591";
google_ad_output = "textlink";
google_ad_format = "ref_text";
google_cpa_choice = "CAAQqcu1_wEaCF2H5Hv651t_KOm84YcB";
google_ad_channel = "";
//--></script>
<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>', '');
$i->vxSetupBoard('thunderbird', 'Mozilla Thunderbird', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('fortran', 'Fortran', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('compiler', '编译器技术', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('zune', 'Zune', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('opera', 'Opera', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('seo', '搜索引擎优化', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('lucene', 'Apache Lucene', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('asm', '汇编语言', 3, 3, 1, 2, 'Be a real programmer.', 'x86 | arm | sparc | mips | ppc | s390');
$i->vxSetupBoard('c', 'C/C++', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('zope', 'Zope/Plone', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('portable', '移动设备技术', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('httpd', 'Apache HTTP Server', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('tomcat', 'Apache Tomcat', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('geronimo', 'Apache Geronimo', 3, 3, 1, 2, '', 'Welcome to Apache Geronimo, the J2EE server project of the Apache Software Foundation.');
$i->vxSetupBoard('db4o', 'db4o', 3, 3, 1, 2, 'db4o :: Native Java & .NET Object Database :: Open Source', 'db4objects');
$i->vxSetupBoard('sqlserver', 'SQL Server', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('firebird', 'Firebird', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('innodb', 'InnoDB', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('bdb', 'Berkeley DB', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('sybase', 'Sybase', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('db2', 'DB2', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('sqlite', 'SQLite', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('postgresql', 'PostgreSQL', 3, 3, 1, 2, "The world's most advanced open source database", '');
$i->vxSetupBoard('mysql', 'MySQL', 3, 3, 1, 2, 'All About MySQL', '够用就好的可爱数据库');
	$i->vxSetupRelatedByName('mysql', 'http://www.phpmyadmin.net/home_page/index.php', 'phpMyAdmin');
	$i->vxSetupRelatedByName('mysql', 'http://dev.mysql.com/', 'MySQL Developer Zone');
	$i->vxSetupChannelByName('mysql', 'http://www.planetmysql.org/rss20.xml');
	$i->vxSetupChannelByName('mysql', 'http://www.primebase.com/xt/pbxt.rss');
$i->vxSetupBoard('babel', 'Project Babel', 3, 3, 1, 2, 'way to explore | way too extreme', 'V2EX | software for internet');
	$i->vxSetupChannelByName('babel', 'http://www.osnews.com/files/recent.xml');
	$i->vxSetupChannelByName('babel', 'http://rss.slashdot.org/Slashdot/slashdot');
	$i->vxSetupChannelByName('babel', 'http://www.betanews.com/rss2');
	$i->vxSetupChannelByName('babel', 'http://webkit.opendarwin.org/blog/?feed=rss2');
$i->vxSetupBoard('zen', 'Project Zen', 3, 3, 1, 2, 'When time matters!', 'V2EX | software for internet');
$i->vxSetupBoard('olpc', 'One Laptop Per Child', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('mac', 'Mac', 3, 3, 1, 2, 'We are APPLEOHOLICS!', '');
	$i->vxSetupChannelByName('mac', 'http://www.sinomac.com/rss.php');
	$i->vxSetupChannelByName('mac', 'http://feeds.feedburner.com/com/WvuX');
	$i->vxSetupChannelByName('mac', 'http://macslash.org/rss/macslash.xml');
	$i->vxSetupChannelByName('mac', 'http://feeds.macworld.com/macworld/all');
	$i->vxSetupChannelByName('mac', 'http://www.macintouch.com/rss.xml');
	$i->vxSetupChannelByName('mac', 'http://www.macnn.com/macnn.rss');
	$i->vxSetupRelatedByName('mac', 'http://www.apple.com/', 'Apple');
$i->vxSetupBoard('palm', 'Palm', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('quantum', '量子物理', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('machine', '硬件讨论区', 3, 3, 1, 2, '乐趣无穷的计算机硬件，我们探索无限可能性！', '');
	$i->vxSetupChannelByName('machine', 'http://cn.engadget.com/rss.xml');
	$i->vxSetupRelatedByName('machine', 'http://www.newegg.com.cn/', '新蛋网');
$i->vxSetupBoard('solaris', 'Solaris', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('macosx', 'Mac OS X', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('plan9', 'Plan 9', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('beos', 'BeOS', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('dos', 'DOS', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('qnx', 'QNX', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('zeta', 'Zeta', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('syllable', 'Syllable', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('live', 'Windows Live', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('vista', 'Windows Vista', 3, 3, 1, 2, '', '');
	$i->vxSetupChannelByName('vista', 'http://windowsvistablog.com/blogs/MainFeed.aspx');
$i->vxSetupBoard('win2003', 'Windows 2003', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('winxp', 'Windows XP', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('office2003', 'Office 2003', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('office2007', 'Office 2007', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('ooo', 'OpenOffice.org', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('reactos', 'ReactOS', 3, 3, 1, 2, '', '');
	$i->vxSetupRelatedByName('reactos', 'http://www.reactos.org/', 'ReactOS');
$i->vxSetupBoard('darwin', 'Darwin', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('qt', 'QT', 3, 3, 1, 2, '少编程，多创造', 'CODE LESS. CREATE MORE.');
$i->vxSetupBoard('postfix', 'Postfix', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('osdev', 'OSDEV', 3, 3, 1, 2, '操作系统开发研究试验室', 'V2EX');
$i->vxSetupBoard('netbsd', 'NetBSD', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('freebsd', 'FreeBSD', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('openbsd', 'OpenBSD', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('svn', 'Subversion', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('cg', '计算机图形学', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('imagemagick', 'ImageMagick', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('3g', '3G', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('rss', 'RSS', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('samba', 'Samba', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('intype', 'Intype', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('pageflakes', 'Pageflakes 飞鸽', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('vmware', 'VMware', 3, 3, 1, 2, '', '');
	$i->vxSetupRelatedByName('vmware', 'http://www.vmware.com/', 'VMware.com');
$i->vxSetupBoard('linux', 'Linux', 3, 3, 1, 2, 'Better Work, Better Play', '');
	$i->vxSetupChannelByName('linux', 'http://www.linux.com/index.rss');
	$i->vxSetupChannelByName('linux', 'http://gnomefiles.org/gnomefiles.xml');
	$i->vxSetupChannelByName('linux', 'http://fridge.ubuntu.com/atom/feed');
	$i->vxSetupChannelByName('linux', 'http://www.howtoforge.com/node/feed');
	$i->vxSetupChannelByName('linux', 'http://blog.linux.org.tw/~jserv/index.xml');
	$i->vxSetupChannelByName('linux', 'http://linuxtoy.org/?feed=rss2');
	$i->vxSetupChannelByName('linux', 'http://www.markshuttleworth.com/feed/');
$i->vxSetupBoard('emacs', 'Emacs', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('vi', 'vi', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('php', 'PHP', 3, 3, 1, 2, '', '');
	$i->vxSetupRelatedByName('php', 'http://www.phpmyadmin.net/home_page/index.php', 'phpMyAdmin');
	$i->vxSetupChannelByName('php', 'http://feeds.feedburner.com/ZendDeveloperZone');
$i->vxSetupBoard('nokia', 'Nokia', 3, 3, 1, 2, 'Nokia 手机玩家用家科学家的家', '');
$i->vxSetupBoard('ruby', 'Ruby', 3, 3, 1, 2, 'Happy Hacking!', 'Enjoy Life!');
$i->vxSetupBoard('rexx', 'REXX', 3, 3, 1, 2, 'Happy Hacking!', 'Enjoy Life!');
$i->vxSetupBoard('rebol', 'Rebol', 3, 3, 1, 2, 'Happy Hacking!', 'Enjoy Life!');
$i->vxSetupBoard('smalltalk', 'Smalltalk', 3, 3, 1, 2, 'Happy Hacking!', 'Enjoy Life!');
$i->vxSetupBoard('haskell', 'Haskell', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('sql', 'SQL', 3, 3, 1, 2, 'Happy Hacking!', 'Standard Query Language');
$i->vxSetupBoard('basic', 'Basic', 3, 3, 1, 2, 'Happy Hacking!', 'Enjoy Life!');
$i->vxSetupBoard('eiffel', 'Eiffel', 3, 3, 1, 2, 'Happy Hacking!', 'Enjoy Life!');
$i->vxSetupBoard('perl', 'Perl', 3, 3, 1, 2, "There's more than one way to do it.", 'Get a life!');
	$i->vxSetupRelatedByName('perl', 'http://www.cpan.org/', 'CPAN');
$i->vxSetupBoard('cf', 'ColdFusion', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('python', 'Python', 3, 3, 1, 2, 'Happy Hacking!', 'Enjoy Life!');
	$i->vxSetupChannelByName('python', 'http://www.python.org/channews.rdf');
$i->vxSetupBoard('java', 'Java', 3, 3, 1, 2, 'Everywhere!', '');
$i->vxSetupBoard('ideas', 'Ideas', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('startup', '互联网创业', 3, 3, 1, 2, '尽情讨论我们的发财计划吧，哈哈！', '');
	$i->vxSetupChannelByName('startup', 'http://feed.feedsky.com/iblogbeta');
	$i->vxSetupChannelByName('startup', 'http://feeds.feedburner.com/Wappblog');
	$i->vxSetupChannelByName('startup', 'http://www.cnbeta.com/backend.php');
	$i->vxSetupChannelByName('startup', 'http://www.wangtam.com/index.rss');
$i->vxSetupBoard('webdesigner', '网页设计师', 3, 3, 1, 2, '网页设计师的圈子，欢迎你的加入，期待看到你的精彩作品！', '我们有精湛的技术，我们有闪亮的生活。');
	$i->vxSetupChannelByName('webdesigner', 'http://blog.blueidea.com/rss');
	$i->vxSetupChannelByName('webdesigner', 'http://www.seaspace.cn/index.xml');
	$i->vxSetupRelatedByName('webdesigner', 'http://dev.opera.com/', 'Dev Opera');
$i->vxSetupBoard('asimo', 'ASIMO', 3, 3, 1, 2, "", '');
$i->vxSetupBoard('ipod', 'iPod', 3, 3, 1, 2, "What's on your iPod?", '');
$i->vxSetupBoard('psp', 'PlayStation Portable', 3, 3, 1, 2, "", '');
$i->vxSetupBoard('nds', 'Nintendo DS', 3, 3, 1, 2, "", '');
$i->vxSetupBoard('casio', 'CASIO', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('siemens', 'Siemens', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('benq', 'BenQ', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('asus', 'ASUS', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('dell', 'Dell', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('alienware', 'Alienware', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('3dfx', '3DFX', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('nvidia', 'nVIDIA', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('ati', 'ATI', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('intel', 'Intel', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('amd', 'AMD', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('ibm', 'IBM', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('canon', 'Canon', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('nikon', 'Nikon', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('olympus', 'Olympus', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('hp', 'HP', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('nec', 'NEC', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('apple', 'Apple', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('logitech', 'Logitech', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('kodak', 'Kodak', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('gnome', 'GNOME', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('samsung', 'Samsung', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('motorola', 'Motorola', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('seiko', 'SEIKO', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('opengl', 'OpenGL', 3, 3, 1, 2, "", '');
$i->vxSetupBoard('coder', '程序员', 3, 3, 1, 2, "", '');
	$i->vxSetupChannelByName('coder', 'http://feeds.feedburner.com/vitaminmasterfeed');
	$i->vxSetupRelatedByName('coder', 'http://www.sun.com/', 'Sun');
	$i->vxSetupRelatedByName('coder', 'http://www.tigris.org/', 'Tigris.org');
	$i->vxSetupRelatedByName('coder', 'http://www.microsoft.com/', 'Microsoft');
	$i->vxSetupRelatedByName('coder', 'http://msdn.microsoft.com/', 'MSDN');
	$i->vxSetupRelatedByName('coder', 'http://www.codegear.com/', 'CodeGear');
$i->vxSetupBoard('oracle', 'Oracle', 3, 3, 1, 2, "", '');
$i->vxSetupBoard('skype', 'Skype', 3, 3, 1, 2, "", '');
$i->vxSetupBoard('bmw', 'BMW', 3, 3, 1, 2, "", '');
$i->vxSetupBoard('audi', 'Audi', 3, 3, 1, 2, "", '');
$i->vxSetupBoard('citroen', 'CITROEN', 3, 3, 1, 2, "雪铁龙车友会", '');
$i->vxSetupBoard('ferrari', 'Ferrari', 3, 3, 1, 2, "", '');
$i->vxSetupBoard('astonmartin', 'Aston Martin', 3, 3, 1, 2, "", '');
$i->vxSetupBoard('maserati', 'Maserati', 3, 3, 1, 2, "", '');
$i->vxSetupBoard('chevrolet', 'Chevrolet', 3, 3, 1, 2, "", '');
$i->vxSetupBoard('volkswagen', 'Volkswagen', 3, 3, 1, 2, "", '');
$i->vxSetupBoard('toyota', 'TOYOTA', 3, 3, 1, 2, "", '');
$i->vxSetupBoard('lexus', 'LEXUS', 3, 3, 1, 2, "", '');
	$i->vxSetupRelatedByName('lexus', 'http://www.lexus.com/', 'Lexus.com Official USA Site');
$i->vxSetupBoard('nissan', 'NISSAN', 3, 3, 1, 2, "", '');
$i->vxSetupBoard('mitsubishi', 'Mitsubishi', 3, 3, 1, 2, "", '');
$i->vxSetupBoard('hummer', 'HUMMER', 3, 3, 1, 2, "", '');
$i->vxSetupBoard('ford', 'Ford', 3, 3, 1, 2, "", '');
$i->vxSetupBoard('volvo', 'Volvo', 3, 3, 1, 2, "", '');
$i->vxSetupBoard('landrover', 'Land Rover', 3, 3, 1, 2, "", '');
$i->vxSetupBoard('jaguar', 'Jaguar', 3, 3, 1, 2, "", '');
$i->vxSetupBoard('cadillac', 'Cadillac', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('chrysler', 'Chrysler', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('honda', 'Honda', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('jeep', 'JEEP', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('subaru', 'Subaru', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('ps3', 'PlayStation 3', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('wii', 'Wii', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('xbox360', 'Xbox 360', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('xbox', 'Xbox', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('simcity', 'SimCity', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('diablo', 'Diablo', 3, 3, 1, 2, '', '');
$i->vxSetupBoard('wow', 'World of Warcraft', 3, 3, 1, 2, '魔兽世界', '');
$i->vxSetupBoard('warcraft', 'Warcraft', 3, 3, 1, 2, '魔兽争霸', '');
$i->vxSetupBoard('starcraft', 'Starcraft', 3, 3, 1, 2, '星际争霸', '');
$i->vxSetupBoard('jetty', 'Jetty', 3, 3, 1, 2, "", '');
$i->vxSetupBoard('liferay', 'Liferay', 3, 3, 1, 2, "", '');
$i->vxSetupBoard('zend', 'Zend', 3, 3, 1, 2, "Zend 产品讨论专区", '');
	$i->vxSetupRelatedByName('zend', 'http://www.zend.com/', 'Zend.com');
	$i->vxSetupRelatedByName('zend', 'http://framework.zend.com/', 'Zend Framework');
$i->vxSetupBoard('eclipse', 'Eclipse', 3, 3, 1, 2, "", '');
	$i->vxSetupRelatedByName('eclipse', 'http://www.eclipse.org/', 'Eclipse.org');
	$i->vxSetupChannelByName('eclipse', 'http://www.eclipse.org/home/eclipseinthenews.rss');
$i->vxSetupBoard('xml', 'XML', 3, 3, 1, 2, "", '');
$i->vxSetupBoard('syncml', 'SyncML', 3, 3, 1, 2, "", '');

// thegraywaste
$i->vxSetupBoard('vivi', 'vivi', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('livid', 'Livid', 4, 4, 1, 2, 'All About Livid', 'Livid is My Name');
	$i->vxSetupChannelByName('livid', 'http://www.livid.cn/rss.php');
	$i->vxSetupChannelByName('livid', 'http://moon.livid.cn/?feed=rss2');
	$i->vxSetupRelatedByName('livid', 'http://www.livid.cn/', 'LIVID & REV');
	$i->vxSetupRelatedByName('livid', 'http://www.lividot.org/', 'Lividot');
	$i->vxSetupRelatedByName('livid', 'http://www.lividict.org/', 'Lividict');
	$i->vxSetupRelatedByName('livid', 'http://www.epeta.org/', 'ePeta');
$i->vxSetupBoard('elfe', 'Elfe', 4, 4, 1, 2, '', '');
	$i->vxSetupChannelByName('elfe', 'http://elfe.cn/?feed=rss2');
	$i->vxSetupRelatedByName('elfe', 'http://www.elfe.cn/', '阳光艾芙');
$i->vxSetupBoard('sai', 'SAi', 4, 4, 1, 2, '', '');
	$i->vxSetupRelatedByName('sai', 'http://blog.orzotl.com/?1', 'Nothing but SAi');
	$i->vxSetupChannelByName('sai', 'http://blog.orzotl.com/1/action_rss.html');
$i->vxSetupBoard('harukimurakami', '村上春树', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('m2099', 'm2099', 4, 4, 1, 2, '', '');
	$i->vxSetupRelatedByName('m2099', 'http://www.m2099.com/', 'm2099');
$i->vxSetupBoard('triangle', '三角地', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('fish-culture', '养鱼', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('stock', '证券投资', 4, 4, 1, 2, '', '');
	$i->vxSetupRelatedByName('stock', 'http://www.stockstar.com/', '证券之星');
	$i->vxSetupRelatedByName('stock', 'http://www.gf.com.cn/', '广发证券');
	$i->vxSetupRelatedByName('stock', 'http://www.jrj.com/', '金融界');
	$i->vxSetupRelatedByName('stock', 'http://www.hexun.com/', '和讯');
$i->vxSetupBoard('money', '投资与理财', 4, 4, 1, 2, '', '');
	$i->vxSetupRelatedByName('money', 'http://www.cmbchina.com/', '中国招商银行');
	$i->vxSetupRelatedByName('money', 'http://www.cmbc.com.cn/', '中国民生银行');
	$i->vxSetupRelatedByName('money', 'http://www.icbc.com.cn/', '中国工商银行');
	$i->vxSetupRelatedByName('money', 'http://www.ccb.com.cn/', '中国建设银行');
	$i->vxSetupRelatedByName('money', 'http://www.bank-of-china.com/', '中国银行');
$i->vxSetupBoard('8cups', '每天要喝八杯水', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('lottery', '彩票研究', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('patriotism', '爱国主义教育基地', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('epeta', 'ePeta', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('nintendo', 'Nintendo', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('microsoft', 'Microsoft', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('sony', 'SONY', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('picturestory', '图片的故事', 4, 4, 1, 2, '', '');
	$i->vxSetupRelatedByName('picturestory', 'http://www.flickr.com/', 'Flickr');
$i->vxSetupBoard('sega', 'SEGA', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('capcom', 'CAPCOM', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('konami', 'KONAMI', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('blizzard', 'Blizzard', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('snk', 'SNK', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('civ', 'Civilization', 4, 4, 1, 2, '', '');
	$i->vxSetupRelatedByName('civ', 'http://www.2kgames.com/civ4/home.htm', 'Civilization IV');
$i->vxSetupBoard('punk', '我们坐车不买票', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('paranoid', '偏执狂', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('pointless', '无要点', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('knack', '诀窍', 4, 4, 1, 2, '或许是小聪明，或许是大智慧', '');
$i->vxSetupBoard('exchange', '以物换物', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('qna', '问与答', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('8r8c', '八荣八耻', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('english', 'English', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('branding', '品牌建立', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('1kg', '多背一公斤', 4, 4, 1, 2, '', '');
	$i->vxSetupRelatedByName('1kg', 'http://www.1kg.cn/', '多背一公斤');
$i->vxSetupBoard('blogbus', 'BlogBus', 4, 4, 1, 2, '', '');
	$i->vxSetupChannelByName('blogbus', 'http://hengge.blogbus.com/index.rdf');
	$i->vxSetupChannelByName('blogbus', 'http://blogbus.blogbus.com/index.rdf');
	$i->vxSetupChannelByName('blogbus', 'http://ittalks.blogbus.com/index.rdf');
	$i->vxSetupRelatedByName('blogbus', 'http://www.blogbus.com/', 'BlogBus');
$i->vxSetupBoard('blogger', 'Blogger', 4, 4, 1, 2, '', '');
	$i->vxSetupChannelByName('blogger', 'http://feeds.feedburner.com/TechCrunch');
	$i->vxSetupChannelByName('blogger', 'http://feeds.feedburner.com/PoseShow');
	$i->vxSetupChannelByName('blogger', 'http://feeds.feedburner.com/PlayinWithIt');
	$i->vxSetupChannelByName('blogger', 'http://feeds.feedburner.com/boingboing/iBag');
	$i->vxSetupChannelByName('blogger', 'http://feeds.feedburner.com/wangxiaofeng');
	$i->vxSetupChannelByName('blogger', 'http://www.caobian.info/?feed=rss2');
$i->vxSetupBoard('ecshop', 'ECShop', 4, 4, 1, 2, '', '');
	$i->vxSetupRelatedByName('ecshop', 'http://bbs.ecshop.com/', 'ECShop 支持论坛');
	$i->vxSetupRelatedByName('ecshop', 'http://www.ecshop.com/', 'ECShop 官方网站');
$i->vxSetupBoard('story', '我们一起讲故事', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('human', '人之初', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('digg', 'digg', 4, 4, 1, 2, '', '');
	$i->vxSetupRelatedByName('digg', 'http://www.digg.com/', 'digg');
	$i->vxSetupChannelByName('digg', 'http://www.digg.com/rss/containertechnology.xml');
$i->vxSetupBoard('verycd', 'VeryCD', 4, 4, 1, 2, '', '');
	$i->vxSetupRelatedByName('verycd', 'http://www.verycd.com/', 'VeryCD');
	$i->vxSetupChannelByName('verycd', 'http://blog.verycd.com/dash/req=syndicate');
	$i->vxSetupChannelByName('verycd', 'http://www.xdanger.com/feed/');
$i->vxSetupBoard('douban', '豆瓣', 4, 4, 1, 2, '', '');
	$i->vxSetupRelatedByName('douban', 'http://www.douban.com/', '豆瓣');
	$i->vxSetupChannelByName('douban', 'http://www.douban.com/feed/review/book');
	$i->vxSetupChannelByName('douban', 'http://www.douban.com/feed/review/movie');
	$i->vxSetupChannelByName('douban', 'http://www.douban.com/feed/review/music');
	$i->vxSetupChannelByName('douban', 'http://www.douban.com/feed/group/v2ex/discussion');
	$i->vxSetupChannelByName('douban', 'http://blog.douban.com/feed/');
	$i->vxSetupChannelByName('douban', 'http://weekly.douban.org/index.php/feed/');
$i->vxSetupBoard('movie', '爱看电影', 4, 4, 1, 2, '用一百分钟切换到别人的生活', '');
	$i->vxSetupRelatedByName('movie', 'http://www.youtube.com/', 'YouTube');
$i->vxSetupBoard('superstar', '明星八卦', 4, 4, 1, 2, '', '');
	$i->vxSetupChannelByName('superstar', 'http://blog.sina.com.cn/myblog/index_rss.php?uid=1190363061');
	$i->vxSetupChannelByName('superstar', 'http://blog.sina.com.cn/myblog/index_rss.php?uid=1191258123');
	$i->vxSetupChannelByName('superstar', 'http://blog.sina.com.cn/myblog/index_rss.php?uid=1188552450');
	$i->vxSetupChannelByName('superstar', 'http://blog.sina.com.cn/myblog/index_rss.php?uid=1173538795');
	$i->vxSetupChannelByName('superstar', 'http://blog.sina.com.cn/myblog/index_rss.php?uid=1210603055');
	$i->vxSetupChannelByName('superstar', 'http://blog.sina.com.cn/myblog/index_rss.php?uid=1173544654');

$i->vxSetupBoard('cnbloggercon', '中文网志年会', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('wikipedia', 'Wikipedia', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('psychology', '心理学', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('noexam', '拒绝高考', 4, 4, 1, 2, '拒绝高考是我们的选择，也是我们的权利，恐怕更是我们的必然！', '');
$i->vxSetupBoard('getlaidtonight', '出活日当午', 4, 4, 1, 2, 'All we need is to get laid tonight.', 'But there is still hope.');
$i->vxSetupBoard('kijiji', 'Kijiji', 4, 4, 1, 2, '分类改变生活', '');
	$i->vxSetupChannelByName('kijiji', 'http://feeds.feedburner.com/wangjianshuo');
	$i->vxSetupChannelByName('kijiji', 'http://feeds.feedburner.com/livid');
	$i->vxSetupChannelByName('kijiji', 'http://www.bumo.cn/blog/feed/');
	$i->vxSetupChannelByName('kijiji', 'http://bulaoge.com/rss2.blg?uid=2');
	$i->vxSetupChannelByName('kijiji', 'http://echotao123.spaces.live.com/feed.rss');
	$i->vxSetupChannelByName('kijiji', 'http://feed.hejiachen.com/');
	$i->vxSetupChannelByName('kijiji', 'http://www.sundengjia.com/wordpress/feed/');
	$i->vxSetupChannelByName('kijiji', 'http://www.zhuhequn.com/?feed=rss2');
	$i->vxSetupChannelByName('kijiji', 'http://blog.kijiji.com.cn/index.xml');
	$i->vxSetupChannelByName('kijiji', 'http://feeds.feedburner.com/adolfpan');
	$i->vxSetupChannelByName('kijiji', 'http://spaces.msn.com/titi1017/feed.rss');
	$i->vxSetupChannelByName('kijiji', 'http://spaces.msn.com/emmetxu/feed.rss');
	$i->vxSetupChannelByName('kijiji', 'http://www.zishu.cn/blogrss1.asp');
	$i->vxSetupChannelByName('kijiji', 'http://home.wangjianshuo.com/index.xml');
	$i->vxSetupChannelByName('kijiji', 'http://www.shiweitao.cn/?feed=rss2');
	$i->vxSetupChannelByName('kijiji', 'http://www.shanjiajie.com/?feed=rss2');
	$i->vxSetupRelatedByName('kijiji', 'http://www.kijiji.cn/', 'Kijiji.cn');
	$i->vxSetupRelatedByName('kijiji', 'http://www.kijiji.co.jp/', 'Kijiji.jp');
	$i->vxSetupRelatedByName('kijiji', 'http://www.kijiji.co.kr/', 'Kijiji.kr');
	$i->vxSetupRelatedByName('kijiji', 'http://www.kijiji.de/', 'Kijiji.de');
	$i->vxSetupRelatedByName('kijiji', 'http://www.kijiji.fr/', 'Kijiji.fr');
	$i->vxSetupRelatedByName('kijiji', 'http://www.kijiji.ca/', 'Kijiji.ca');
	$i->vxSetupRelatedByName('kijiji', 'http://www.kijiji.it/', 'Kijiji.it');
	$i->vxSetupRelatedByName('kijiji', 'http://www.kijiji.com/', 'Kijiji.com');
	$i->vxSetupRelatedByName('kijiji', 'http://www.kijiji.tw/', 'Kijiji.tw');
	$i->vxSetupRelatedByName('kijiji', 'http://www.gumtree.com/', 'Gumtree');
	$i->vxSetupRelatedByName('kijiji', 'http://www.marktplaats.nl/', 'Marktplaats');
	$i->vxSetupRelatedByName('kijiji', 'http://www.kijiji.in/', 'Kijiji.in');
	$i->vxSetupRelatedByName('kijiji', 'http://www.kijiji.ch/', 'Kijiji.ch');
	$i->vxSetupRelatedByName('kijiji', 'http://www.kijiji.at/', 'Kijiji.at');
	$i->vxSetupRelatedByName('kijiji', 'http://www.slando.ru/', 'Slando');
	$i->vxSetupRelatedByName('kijiji', 'http://www.intoko.com.tr/', 'Intoko');
	$i->vxSetupRelatedByName('kijiji', 'http://www.loquo.com/', 'Loquo');
$i->vxSetupBoard('adsense', 'AdSense', 4, 4, 1, 2, '', '');
	$i->vxSetupRelatedByName('adsense', 'http://www.google.com/adsense', 'Google AdSense');
$i->vxSetupBoard('adwords', 'AdWords', 4, 4, 1, 2, '', '');
	$i->vxSetupRelatedByName('adwords', 'http://www.google.com/adwords', 'Google AdWords');
$i->vxSetupBoard('ebay', 'eBay', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('google', 'Google', 4, 4, 1, 2, '', '');
	$i->vxSetupChannelByName('google', 'http://googlechinablog.com/atom.xml');
	$i->vxSetupChannelByName('google', 'http://code.google.com/feeds/updates.xml');
	$i->vxSetupChannelByName('google', 'http://googleblog.blogspot.com/atom.xml');
	$i->vxSetupChannelByName('google', 'http://googlewebmastercentral.blogspot.com/atom.xml');
	$i->vxSetupChannelByName('google', 'http://googlebase.blogspot.com/atom.xml');
	$i->vxSetupRelatedByName('google', 'http://www.google.com/reader', 'Reader');
	$i->vxSetupRelatedByName('google', 'http://pages.google.com/', 'Pages');
	$i->vxSetupRelatedByName('google', 'http://maps.google.com/', 'Maps');
	$i->vxSetupRelatedByName('google', 'http://www.google.com/base', 'Base');
	$i->vxSetupRelatedByName('google', 'http://www.writely.com/', 'Writely');
	$i->vxSetupRelatedByName('google', 'http://www.orkut.com/', 'Orkut');
$i->vxSetupBoard('myspace', 'MySpace', 4, 4, 1, 2, 'We all love Tom!', '');
$i->vxSetupBoard('baidu', '百度', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('yahoo', 'Yahoo!', 4, 4, 1, 2, '', '');
	$i->vxSetupRelatedByName('yahoo', 'http://www.yahoo.com.cn/', 'Y! China');
	$i->vxSetupRelatedByName('yahoo', 'http://www.yahoo.com/', 'Yahoo!');
	$i->vxSetupChannelByName('yahoo', 'http://ysearchblog.cn/index.xml');
$i->vxSetupBoard('msn', 'MSN Spaces', 4, 4, 1, 2, '', '');
	$i->vxSetupChannelByName('msn', 'http://feeds.feedburner.com/poisoned');
$i->vxSetupBoard('lang', '学外语', 4, 4, 1, 2, '为了更好的沟通', 'For better communications.');
$i->vxSetupBoard('health', '玩电脑有害健康', 4, 4, 1, 2, '关掉你的电脑，多去大自然呼吸新鲜空气吧！', '每天都要减少那些不必要的计算机使用。');
$i->vxSetupBoard('nodrug', '吸毒有害健康', 4, 4, 1, 2, '', 'Marijuana Joint Hemp Cannabis Heroin Cocaine Hallucinogen');
$i->vxSetupBoard('wc2006', '2006 德国世界杯', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('wc2010', '2010 南非世界杯', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('doha2006', '2006 多哈亚运会', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('homo', 'homo', 4, 4, 1, 2, '我们的爱', 'all my love');
$i->vxSetupBoard('homme', 'homme', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('play', '努力工作拼命玩', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('photo', '摄影爱好者', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('mood', '你今天心情好吗？', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('math', '数学', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('physics', '物理', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('chemistry', '化学', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('delicious', '美食 . 好酒 . 生活', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('travel', '龙门客栈', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('dnd', '龙与地下城', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('jobs', '我要找份好工作', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('bulaoge', '不老歌', 4, 4, 1, 2, '', '');
	$i->vxSetupRelatedByName('bulaoge', 'http://www.bulaoge.com/', '不老歌');
	$i->vxSetupChannelByName('bulaoge', 'http://bulaoge.com/rss2.blg?uid=2');
	$i->vxSetupChannelByName('bulaoge', 'http://bulaoge.com/rss2.blg?uid=939');
$i->vxSetupBoard('news', 'NeWs', 4, 4, 1, 2, 'News for nerds, stuff that matters', '');
$i->vxSetupBoard('galaxy', '银河系漫游指南', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('reading', '爱读书', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('20s', '二十年代', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('30s', '三十年代', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('40s', '四十年代', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('50s', '五十年代', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('60s', '六十年代', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('70s', '七十年代', 4, 4, 1, 2, '', '');
	$i->vxSetupRelatedByName('70s', 'http://www.houhai.com/', '后海');
	$i->vxSetupRelatedByName('70s', 'http://www.i70s.com/', '柒零派');
$i->vxSetupBoard('80s', '八十年代', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('90s', '九十年代', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('middle-year-crisis', '中年危机', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('2006', '2006', 4, 4, 1, 2, '2006', '');
$i->vxSetupBoard('2007', '2007', 4, 4, 1, 2, '2007', '');
$i->vxSetupBoard('electronic-guitar', "电吉他", 4, 4, 1, 2, '', '');
$i->vxSetupBoard('ynsdfz', '云南师大附中', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('gxsdfz', '广西师大附中', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('gezhi', '上海格致中学', 4, 4, 1, 2, '', '');
$i->vxSetupBoard('lomo', 'LOMO', 4, 4, 1, 2, '.: 我们的乐摸生活 :.', '');
$i->vxSetupBoard('v2ex', 'V2EX', 4, 4, 1, 2, 'Latest from V2EX', 'V2EX | software for internet');
	$i->vxSetupRelatedByName('v2ex', 'http://www.v2ex.org/', 'V2EX Blog');
	$i->vxSetupChannelByName('v2ex', 'http://v2ex.org/?feed=rss2');
	$i->vxSetupChannelByName('v2ex', 'http://www.clockwork.cn/?feed=rss2');
	$i->vxSetupChannelByName('v2ex', 'http://www.v2ex.com/feed/v2ex.rss');
	$i->vxSetupRelatedByName('v2ex', 'http://io.v2ex.com/', 'V2EX::IO');
$i->vxSetupBoard('io', 'IO', 4, 4, 1, 2, '', '');
	$i->vxSetupRelatedByName('io', 'http://io.v2ex.com/', 'V2EX::IO');
$i->vxSetupBoard('autistic', '自言自语', 4, 4, 1, 2, '在这里我们自己和自己玩，不欢迎别人的回帖。', '');
$i->vxSetupBoard('show', 'SHOW', 4, 4, 1, 2, '欢迎你在这里贴自己的照片！', '');
$i->vxSetupBoard('50ren', '50人杂志', 4, 4, 1, 2, '一本神奇杂志的诞生，需要你的好奇……', '你是否跟我一样，正在关注并创造着《50人》？');
	$i->vxSetupChannelByName('50ren', 'http://www.uuzone.com/rss/blog_category/yezi/22563.xml');
$i->vxSetupBoard('skypacer', '尔曼', 4, 4, 1, 2, '尔曼，外号用了10年，超重了10年，奋斗目标是外号用 100 年，明年不超重', '');
	$i->vxSetupChannelByName('skypacer', 'http://news.baidu.com/n?cmd=4&class=civilnews&pn=1&tn=rss');
	$i->vxSetupChannelByName('skypacer', 'http://news.baidu.com/n?cmd=4&class=internews&pn=1&tn=rss');
	$i->vxSetupChannelByName('skypacer', 'http://news.baidu.com/n?cmd=4&class=sportnews&pn=1&tn=rss');
	$i->vxSetupChannelByName('skypacer', 'http://news.baidu.com/n?cmd=4&class=enternews&pn=1&tn=rss');
	$i->vxSetupChannelByName('skypacer', 'http://news.baidu.com/n?cmd=4&class=internet&pn=1&tn=rss');
	$i->vxSetupChannelByName('skypacer', 'http://news.baidu.com/n?cmd=4&class=technnews&pn=1&tn=rss');
	$i->vxSetupChannelByName('skypacer', 'http://news.baidu.com/n?cmd=4&class=finannews&pn=1&tn=rss');
	$i->vxSetupChannelByName('skypacer', 'http://news.baidu.com/n?cmd=4&class=socianews&pn=1&tn=rss');
	$i->vxSetupChannelByName('skypacer', 'http://rss.xinhuanet.com/rss/native.xml');
	$i->vxSetupChannelByName('skypacer', 'http://rss.xinhuanet.com/rss/world.xml');
	$i->vxSetupChannelByName('skypacer', 'http://rss.xinhuanet.com/rss/fortune.xml');
	$i->vxSetupChannelByName('skypacer', 'http://rss.xinhuanet.com/rss/sports.xml');
	$i->vxSetupChannelByName('skypacer', 'http://rss.xinhuanet.com/rss/mil.xml');
	$i->vxSetupChannelByName('skypacer', 'http://rss.xinhuanet.com/rss/it.xml');
	$i->vxSetupChannelByName('skypacer', 'http://rss.xinhuanet.com/rss/science.xml');
	$i->vxSetupChannelByName('skypacer', 'http://rss.xinhuanet.com/rss/ent.xml');
	$i->vxSetupChannelByName('skypacer', 'http://rss.xinhuanet.com/rss/edu.xml');
	$i->vxSetupChannelByName('skypacer', 'http://rss.xinhuanet.com/rss/photos.xml');
	$i->vxSetupChannelByName('skypacer', 'http://rss.xinhuanet.com/rss/legal.xml');
	$i->vxSetupChannelByName('skypacer', 'http://www.365key.com/rss/keso/');
	$i->vxSetupChannelByName('skypacer', 'http://blog.guykawasaki.com/rss.xml');
	$i->vxSetupChannelByName('skypacer', 'http://blog.verycd.com/dash/req=syndicate');
	$i->vxSetupChannelByName('skypacer', 'http://divx.thu.cn/rss/rss_feed.php');
	$i->vxSetupChannelByName('skypacer', 'http://www.donews.net/mainfeed.aspx');
	$i->vxSetupChannelByName('skypacer', 'http://www.donews.com/rss.xml');
	$i->vxSetupChannelByName('skypacer', 'http://www.donews.com/GroupFeed.aspx?G=5B1D5178-138D-4D42-B370-5198FDF5AF34');
	$i->vxSetupChannelByName('skypacer', 'http://www.donews.com/GroupFeed.aspx?G=481BCC18-7F72-40E3-953E-5BB6545B3828');
	$i->vxSetupChannelByName('skypacer', 'http://www.donews.com/GroupFeed.aspx?G=E10F17D2-05A1-4724-B488-E0B29E4C0E94');
	$i->vxSetupChannelByName('skypacer', 'http://www.donews.com/GroupFeed.aspx?G=EE56026E-534D-4B37-BA7F-19AE41B09904');
	$i->vxSetupChannelByName('skypacer', 'http://www.flypig.org/index.xml');
	$i->vxSetupChannelByName('skypacer', 'http://googlechinablog.com/atom.xml');
	$i->vxSetupChannelByName('skypacer', 'http://google.blognewschannel.com/index.php/feed/');
	$i->vxSetupChannelByName('skypacer', 'http://blog.podlook.com/rss.aspx');
	$i->vxSetupChannelByName('skypacer', 'http://feeds.feedburner.com/laobaisBlog');
	$i->vxSetupChannelByName('skypacer', 'http://www.seovista.com/rss.xml');
	$i->vxSetupChannelByName('skypacer', 'http://electricpulp.com/blog/feed/atom/');
	$i->vxSetupChannelByName('skypacer', 'http://home.wangjianshuo.com/index.xml');
	$i->vxSetupChannelByName('skypacer', 'http://spaces.msn.com/members/mranti/feed.rss');
	$i->vxSetupChannelByName('skypacer', 'http://lydon.yculblog.com/rss.xml');
	$i->vxSetupChannelByName('skypacer', 'http://blog.donews.com/keso/rss.aspx');
	$i->vxSetupChannelByName('skypacer', 'http://podcast.kijiji.com.cn/podcast.xml');
	$i->vxSetupChannelByName('skypacer', 'http://blog.donews.com/liuren/Rss.aspx');
	$i->vxSetupChannelByName('skypacer', 'http://blog.donews.com/maitian99/rss.aspx');
	$i->vxSetupChannelByName('skypacer', 'http://feeds.feedburner.com/wangjianshuo');
	$i->vxSetupChannelByName('skypacer', 'http://blog.donews.com/chinabright/rss.aspx');
	$i->vxSetupChannelByName('skypacer', 'http://rss.sina.com.cn/news/marquee/ddt.xml');
	$i->vxSetupChannelByName('skypacer', 'http://www.mpdaogou.com/Discount/Emporium/rss.xml');
	$i->vxSetupChannelByName('skypacer', 'http://www.weamax.com/xml/rss_weamax_news.php');
	$i->vxSetupChannelByName('skypacer', 'http://blog.donews.com/bingshu/Rss.aspx');
	$i->vxSetupChannelByName('skypacer', 'http://www.luoyonghao.net/Blog/RssHandler.ashx?id=laoluo');
	$i->vxSetupChannelByName('skypacer', 'http://blog.kijiji.com.cn/index.xml');
	$i->vxSetupChannelByName('skypacer', 'http://www.lifepop.com/asp/rss.asp?domain=yyse');
	$i->vxSetupChannelByName('skypacer', 'http://zhaomu.blog.sohu.com/rss');
	$i->vxSetupChannelByName('skypacer', 'http://feeds.feedburner.com/livid');
	$i->vxSetupChannelByName('skypacer', 'http://www.hi-pda.com/drupal//?q=node/feed');
	$i->vxSetupChannelByName('skypacer', 'http://www.bumo.cn/blog/feed/');
	$i->vxSetupChannelByName('skypacer', 'http://www.boingboing.net/index.rdf');
?>
