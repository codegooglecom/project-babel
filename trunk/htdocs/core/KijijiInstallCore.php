<?php
/* Project Babel
*  Author: Livid Torvalds
*  File: /htdocs/core/InstallCore.php
*  Usage: a Quick and Dirty script
*  Format: 1 tab indent(4 spaces), LF, UTF-8, no-BOM
*
*  Subversion Keywords:
*
*  $Id: KijijiInstallCore.php 60 2007-02-05 08:00:48Z livid $
*  $LastChangedDate: 2007-02-05 16:00:48 +0800 (Mon, 05 Feb 2007) $
*  $LastChangedRevision: 60 $
*  $LastChangedBy: livid $
*  $URL: http://svn.cn.v2ex.com/svn/babel/trunk/htdocs/core/KijijiInstallCore.php $
*/

class Install {
	var $db;
	
	public function __construct() {
		$this->db = mysql_connect('localhost', 'babel', 'YourPasswordHere');
		mysql_select_db('babel', $this->db);
		mysql_query("SET NAMES utf8");
		mysql_query("SET CHARACTER SET utf8");
		mysql_query("SET COLLATION_CONNECTION='utf8_general_ci'");
		header('Content-type: text/html;charset=UTF-8');
		echo('Install Core init<br /><br />');
	}
	
	public function __destruct() {
		mysql_close($this->db);
	}
	
	public function vxSetupSections() {
		$this->vxSetupSection("UPDATE babel_node SET nod_sid = 1, nod_level = 0, nod_title = '世界之树', nod_header = '世界之树', nod_footer = '闪亮生活 / 美好心情 / 就在客齐集社区' WHERE nod_id = 1 LIMIT 1");
		$this->vxSetupSection("UPDATE babel_node SET nod_sid = 1, nod_title = '生活百科', nod_header = '在这里你可以找到一切的时尚生活资讯！', nod_footer = '闪亮生活 / 美好心情 / 就在客齐集社区' WHERE nod_id = 2 LIMIT 1");
		$this->vxSetupSection("UPDATE babel_node SET nod_sid = 1, nod_title = '娱乐现场', nod_header = '你最中意的明星，电影，歌曲，DVD 全都到齐了！', nod_footer = '闪亮生活 / 美好心情 / 就在客齐集社区' WHERE nod_id = 3 LIMIT 1");
		$this->vxSetupSection("UPDATE babel_node SET nod_sid = 1, nod_title = '电脑玩家', nod_header = '在客齐集电脑玩家社区，我们研究科技，我们享受科技！', nod_footer = '闪亮生活 / 美好心情 / 就在客齐集社区' WHERE nod_id = 4 LIMIT 1");
		$this->vxSetupSection("UPDATE babel_node SET nod_sid = 1, nod_title = 'WhAteVEr', nod_header = '我们大家都到齐啦，开始在客齐集社区找到惊喜！', nod_footer = '闪亮生活 / 美好心情 / 就在客齐集社区' WHERE nod_id = 5 LIMIT 1");
		$this->vxSetupSection("UPDATE babel_node SET nod_level = 1, nod_sid = 1, nod_name = 'un', nod_title = '大学联盟', nod_header = '在客齐集社区找到你的同学和惊喜！', nod_footer = '闪亮生活 / 美好心情 / 就在客齐集社区' WHERE nod_id = 7 LIMIT 1");
		$this->vxSetupSection("UPDATE babel_node SET nod_title = 'Project Babel', nod_name = 'babel', nod_header = '欢迎你在这里就客齐集社区的所有方面展开讨论！', nod_footer = '有建设性的生活最健康 / 我们欢迎一切有建设性的言论' WHERE nod_id = 6 LIMIT 1");
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
	
	public function vxSetupChannel($board_id, $url) {
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
	
	public function vxSetupChannels() {
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
$i->vxSetupChannels();
$i->vxSetupChannel(103, 'http://www.livid.cn/rss.php');
$i->vxSetupChannel(118, 'http://feeds.feedburner.com/wangjianshuo');
$i->vxSetupChannel(118, 'http://feeds.feedburner.com/PlayinWithIt');
$i->vxSetupChannel(118, 'http://www.livid.cn/rss.php');
$i->vxSetupChannel(118, 'http://www.wespoke.com/index.rdf');
$i->vxSetupChannel(118, 'http://blog.douban.com/feed/');


$i->vxSetupChannel(9, 'http://cn.engadget.com/rss.xml');
$i->vxSetupChannel(9, 'http://cn.autoblog.com/rss.xml');
/*
$i->vxSetupSections();
// life
$i->vxSetupBoard('showyourself', '秀出你自己', 2, 2, 1, 2, '自拍爱好者的大本营，大家快来贴啊！', '无数双眼睛期待你发的好图！');
$i->vxSetupBoard('being', '为人处世', 2, 2, 1, 2);
$i->vxSetupBoard('pet', '宠物我爱', 2, 2, 1, 2);
$i->vxSetupBoard('love', '关于爱', 2, 2, 1, 2);
$i->vxSetupBoard('hiker', '徒步旅行者', 2, 2, 1, 2);
$i->vxSetupBoard('volkswagen', '大众车友会', 2, 2, 1, 2);
$i->vxSetupBoard('heihei', '嘿嘿', 2, 2, 1, 2);
$i->vxSetupBoard('story', '心情故事', 2, 2, 1, 2);
$i->vxSetupBoard('dzh', '大杂烩', 2, 2, 1, 2);
$i->vxSetupBoard('beijing', '北京', 2, 2, 1, 2);
$i->vxSetupBoard('shanghai', '上海', 2, 2, 1, 2);
$i->vxSetupBoard('guangzhou', '广州', 2, 2, 1, 2);
$i->vxSetupBoard('changchun', '长春', 2, 2, 1, 2);
$i->vxSetupBoard('changsha', '长沙', 2, 2, 1, 2);
$i->vxSetupBoard('chengdu', '成都', 2, 2, 1, 2);
$i->vxSetupBoard('chongqing', '重庆', 2, 2, 1, 2);
$i->vxSetupBoard('dalian', '大连', 2, 2, 1, 2);
$i->vxSetupBoard('guiyang', '贵阳', 2, 2, 1, 2);
$i->vxSetupBoard('hangzhou', '杭州', 2, 2, 1, 2);
$i->vxSetupBoard('haerbin', '哈尔滨', 2, 2, 1, 2);
$i->vxSetupBoard('hefei', '合肥', 2, 2, 1, 2);
$i->vxSetupBoard('jinan', '济南', 2, 2, 1, 2);
$i->vxSetupBoard('kunming', '昆明', 2, 2, 1, 2);
$i->vxSetupBoard('lanzhou', '兰州', 2, 2, 1, 2);
$i->vxSetupBoard('nanchang', '南昌', 2, 2, 1, 2);
$i->vxSetupBoard('nanjing', '南京', 2, 2, 1, 2);
$i->vxSetupBoard('qingdao', '青岛', 2, 2, 1, 2);
$i->vxSetupBoard('shantou', '汕头', 2, 2, 1, 2);
$i->vxSetupBoard('shenyang', '沈阳', 2, 2, 1, 2);
$i->vxSetupBoard('shenzhen', '深圳', 2, 2, 1, 2);
$i->vxSetupBoard('shijiazhuang', '石家庄', 2, 2, 1, 2);
$i->vxSetupBoard('suzhou', '苏州', 2, 2, 1, 2);
$i->vxSetupBoard('taiyuan', '太原', 2, 2, 1, 2);
$i->vxSetupBoard('handan', '邯郸', 2, 2, 1, 2);
$i->vxSetupBoard('tianjin', '天津', 2, 2, 1, 2);
$i->vxSetupBoard('wuhan', '武汉', 2, 2, 1, 2);
$i->vxSetupBoard('xiamen', '厦门', 2, 2, 1, 2);
$i->vxSetupBoard('xian', '西安', 2, 2, 1, 2);
$i->vxSetupBoard('yantai', '烟台', 2, 2, 1, 2);
$i->vxSetupBoard('zhengzhou', '郑州', 2, 2, 1, 2);
$i->vxSetupBoard('haikou', '海口', 2, 2, 1, 2);
$i->vxSetupBoard('wuxi', '无锡', 2, 2, 1, 2);
$i->vxSetupBoard('anshan', '鞍山', 2, 2, 1, 2);
*/
$i->vxSetupBoard('kijijipetactivity', '爱心客，齐集！ 活动发布区', 2, 2, 1, 2);

/*
$i->vxSetupBoard('kactivity', '客齐集中央情报局', 2, 2, 1, 2, '你租房子，我补贴。来客齐集多快好省！', '我的活动，我做主');

// ent
$i->vxSetupBoard('supergirl', '超级女声 / 想唱就唱', 3, 3, 1, 2);
$i->vxSetupBoard('sglyc', '李宇春', 3, 3, 1, 2);
$i->vxSetupBoard('sgzly', '张靓颖', 3, 3, 1, 2);
$i->vxSetupBoard('sghj', '何洁', 3, 3, 1, 2);
$i->vxSetupBoard('sgzbc', '周笔畅', 3, 3, 1, 2);
$i->vxSetupBoard('sgjmj', '纪敏佳', 3, 3, 1, 2);
$i->vxSetupBoard('sgyyq', '叶一茜', 3, 3, 1, 2);
$i->vxSetupBoard('sghyl', '黄雅莉', 3, 3, 1, 2);
$i->vxSetupBoard('sgyh', '易惠', 3, 3, 1, 2);
$i->vxSetupBoard('sgzy', '朱妍', 3, 3, 1, 2);
$i->vxSetupBoard('sgzjy', '赵静怡', 3, 3, 1, 2);
$i->vxSetupBoard('sgzhy', '张含韵', 3, 3, 1, 2);
$i->vxSetupBoard('dvd', 'DVD 收藏家', 3, 3, 1, 2);
$i->vxSetupBoard('musicres', '音乐资源贡献区', 3, 3, 1, 2);
$i->vxSetupBoard('movies', '看电影', 3, 3, 1, 2);
$i->vxSetupBoard('musicfans', '爱音乐的孩子不会变坏', 3, 3, 1, 2);
$i->vxSetupBoard('homevideo', '搞笑家庭电影', 3, 3, 1, 2);
$i->vxSetupBoard('iloveflash', '大家一起闪呀闪', 3, 3, 1, 2);
$i->vxSetupBoard('pinmin', '平民动漫', 3, 3, 1, 2);
$i->vxSetupBoard('kijijimusicsinger', '音乐客，齐集！ 歌手评论区', 3, 3, 1, 2);
$i->vxSetupBoard('kijijimusicactivity', '音乐客，齐集！ 活动发布区', 3, 3, 1, 2);

// geek
$i->vxSetupBoard('debian', 'Debian', 4, 4, 1, 2);
$i->vxSetupBoard('ubuntu', 'Ubuntu', 4, 4, 1, 2);
$i->vxSetupBoard('gentoo', 'Gentoo', 4, 4, 1, 2);
$i->vxSetupBoard('plan9', 'Plan 9', 4, 4, 1, 2);
$i->vxSetupBoard('macosx', 'Mac OS X', 4, 4, 1, 2);
$i->vxSetupBoard('solaris', 'Solaris', 4, 4, 1, 2);
$i->vxSetupBoard('freebsd', 'FreeBSD', 4, 4, 1, 2);
$i->vxSetupBoard('netbsd', 'NetBSD', 4, 4, 1, 2);
$i->vxSetupBoard('openbsd', 'OpenBSD', 4, 4, 1, 2);

$i->vxSetupBoard('ipod', 'iPod & iTunes', 4, 4, 1, 2, 'iPod & iTunes，天生一对');
$i->vxSetupBoard('machw', 'Macintosh 硬件讨论', 4, 4, 1, 2);
$i->vxSetupBoard('macsw', 'Macintosh 软件讨论', 4, 4, 1, 2);
$i->vxSetupBoard('palm', 'Palm', 4, 4, 1, 2);
$i->vxSetupBoard('pocketpc', 'Pocket PC', 4, 4, 1, 2);
$i->vxSetupBoard('smartphone', 'Smartphone', 4, 4, 1, 2);
$i->vxSetupBoard('s60', 'Symbian S60', 4, 4, 1, 2);
$i->vxSetupBoard('uiq', 'Symbian UIQ', 4, 4, 1, 2);

$i->vxSetupBoard('laptop', '笔记本电脑讨论', 4, 4, 1, 2);

$i->vxSetupBoard('opengl', 'OpenGL 编程讨论', 4, 4, 1, 2);
$i->vxSetupBoard('directx', 'DirectX 编程讨论', 4, 4, 1, 2);
$i->vxSetupBoard('linuxdev', 'Linux 编程讨论', 4, 4, 1, 2);
$i->vxSetupBoard('windev', 'Windows 编程讨论', 4, 4, 1, 2);
$i->vxSetupBoard('macdev', 'Macintosh 编程讨论', 4, 4, 1, 2);
$i->vxSetupBoard('webdev', 'Web 编程讨论', 4, 4, 1, 2);

$i->vxSetupBoard('pythondev', 'Python 编程讨论', 4, 4, 1, 2);
$i->vxSetupBoard('perldev', 'Perl 编程讨论', 4, 4, 1, 2);
$i->vxSetupBoard('phpdev', 'PHP 编程讨论', 4, 4, 1, 2);
$i->vxSetupBoard('cppdev', 'C/C++ 编程讨论', 4, 4, 1, 2);
$i->vxSetupBoard('rubydev', 'Ruby 编程讨论', 4, 4, 1, 2);
$i->vxSetupBoard('delphidev', 'Pascal/Delphi 编程讨论', 4, 4, 1, 2);
$i->vxSetupBoard('javadev', 'Java 编程讨论', 4, 4, 1, 2);
$i->vxSetupBoard('csharpdev', 'C# 编程讨论', 4, 4, 1, 2);
$i->vxSetupBoard('basicdev', 'Basic/Gambas 编程讨论', 4, 4, 1, 2);
$i->vxSetupBoard('xmldev', 'XML/XSL 编程讨论', 4, 4, 1, 2);

$i->vxSetupBoard('mmfl', 'Macromedia Flash', 4, 4, 1, 2);
$i->vxSetupBoard('mmdw', 'Macromedia Dreamweaver', 4, 4, 1, 2);
$i->vxSetupBoard('mmfw', 'Macromedia Fireworks', 4, 4, 1, 2);
$i->vxSetupBoard('mmcf', 'Macromedia ColdFusion', 4, 4, 1, 2);
$i->vxSetupBoard('adboeps', 'Adobe Photoshop', 4, 4, 1, 2);
$i->vxSetupBoard('adobepr', 'Adboe Premier', 4, 4, 1, 2);
$i->vxSetupBoard('dc3dsmax', 'Discreet 3dsmax', 4, 4, 1, 2);
$i->vxSetupBoard('dccbs', 'Discreet Combustion', 4, 4, 1, 2);
$i->vxSetupBoard('applefcp', 'Apple Final Cut Pro', 4, 4, 1, 2);
$i->vxSetupBoard('applelogic', 'Apple Logic Pro', 4, 4, 1, 2);
$i->vxSetupBoard('applemotion', 'Apple Motion', 4, 4, 1, 2);
$i->vxSetupBoard('appleshake', 'Apple Shake', 4, 4, 1, 2);
$i->vxSetupBoard('applest', 'Apple Soundtrack', 4, 4, 1, 2);
$i->vxSetupBoard('appledsp', 'Apple DVD Studio Pro', 4, 4, 1, 2);

$i->vxSetupBoard('office', 'Office 办公应用', 4, 4, 1, 2);
$i->vxSetupBoard('iwork', 'iWork', 4, 4, 1, 2);

$i->vxSetupBoard('wow', '魔兽世界', 4, 4, 1, 2);
$i->vxSetupBoard('warcraft', '魔兽争霸', 4, 4, 1, 2);

$i->vxSetupBoard('pcgamer', 'PC 游戏玩家', 4, 4, 1, 2);

// snda
$i->vxSetupBoard('bnb', '泡泡堂', 4, 4, 1, 2);
$i->vxSetupBoard('mir', '热血传奇', 4, 4, 1, 2);
$i->vxSetupBoard('wool', '传奇世界', 4, 4, 1, 2);

$i->vxSetupBoard('playstation', 'PlayStation 软硬件讨论', 4, 4, 1, 2);
$i->vxSetupBoard('xbox', 'Xbox 软硬件讨论', 4, 4, 1, 2);
$i->vxSetupBoard('ngc', 'NGC 软硬件讨论', 4, 4, 1, 2);
$i->vxSetupBoard('psp', '掌机玩家', 4, 4, 1, 2, 'GB / GBA / PSP / NDS 亮出你最爱的掌机，让我们玩出精彩！', '努力工作拼命玩~');
$i->vxSetupBoard('dc', '色友大本营', 4, 4, 1, 2);

$i->vxSetupBoard('blogger', 'Blogger', 4, 4, 1, 2);
$i->vxSetupBoard('foss', '自由软件', 4, 4, 1, 2);
$i->vxSetupBoard('mysql', 'MySQL', 4, 4, 1, 2, 'The most popular free database on the planet.');
$i->vxSetupBoard('pgsql', 'PostgreSQL', 4, 4, 1, 2);
$i->vxSetupBoard('mssql', 'SQL Server', 4, 4, 1, 2);
$i->vxSetupBoard('oracle', 'Oracle', 4, 4, 1, 2);
// whatever
$i->vxSetupBoard('jerusalem', 'Project Jerusalem', 5, 5, 1, 2, 'Welcome to Jerusalem!');

$i->vxSetupBoard('livid', 'Livid\'s Playground', 5, 5, 1, 2);

$i->vxSetupBoard('sunwest', '太阳以西', 5, 5, 1, 2);
$i->vxSetupBoard('bordersouth', '国境以南', 5, 5, 1, 2);

$i->vxSetupBoard('kijijichina', '客齐集', 5, 5, 1, 2);
$i->vxSetupBoard('kijijichinacustomerservice', '客齐集客户服务中心', 5, 5, 1, 2, '这里有我们为你整理的常规问题解答，同时也欢迎你提出任何在使用客齐集服务中所遇到的问题', '闪亮生活 / 美好心情 / 就在客齐集社区');
$i->vxSetupBoard('kijijivolunteer', '客齐集青年志愿者交流区', 5, 5, 1, 2);
$i->vxSetupBoard('classifield', '分类网站模式讨论区', 5, 5, 1, 2);
*/

//$i->vxSetupBoard('qxmx', '情系母校', 5, 5, 1, 2);
//$i->vxSetupChannel(164, 'http://blog.livid.cn/qxmx/?feed=rss2');

$i->vxSetupBoard('kijijipodcast', '客齐集广播', 5, 5, 1, 2);
$i->vxSetupBoard('kijijijobs', '加入客齐集', 5, 5, 1, 2, '一份好工作 / 一份好生活');
//$i->vxSetupChannel(167, 'http://info.kijiji.com.cn/podcast/podcast.xml');


/*
// un
$i->vxSetupBoard('shu', '上海大学', 7, 7, 1, 2);
$i->vxSetupBoard('shisu', '上海外国语大学', 7, 7, 1, 2);
$i->vxSetupBoard('fudan', '复旦大学', 7, 7, 1, 2);
$i->vxSetupBoard('sjtu', '上海交通大学', 7, 7, 1, 2);
$i->vxSetupBoard('tongji', '同济大学', 7, 7, 1, 2);
$i->vxSetupBoard('shufe', '上海财经大学', 7, 7, 1, 2);
$i->vxSetupBoard('ecupl', '华东政法学院', 7, 7, 1, 2);
$i->vxSetupBoard('ecust', '华东理工大学', 7, 7, 1, 2);
$i->vxSetupBoard('ecnu', '华东师范大学', 7, 7, 1, 2);
$i->vxSetupBoard('sues', '上海工程技术大学', 7, 7, 1, 2);
$i->vxSetupBoard('shtu', '上海师范大学', 7, 7, 1, 2);
$i->vxSetupBoard('dhu', '东华大学', 7, 7, 1, 2);
$i->vxSetupBoard('pku', '北京大学', 7, 7, 1, 2);
$i->vxSetupBoard('tsinghua', '清华大学', 7, 7, 1, 2);
$i->vxSetupBoard('whu', '武汉大学', 7, 7, 1, 2);
$i->vxSetupBoard('ujs', '江苏大学', 7, 7, 1, 2);
$i->vxSetupBoard('ecsi', '江苏科技大学', 7, 7, 1, 2);
$i->vxSetupBoard('kijijiuf', '客齐集高校论坛', 7, 7, 1, 2);
$i->vxSetupBoard('nju', '南京大学', 7, 7, 1, 2);
$i->vxSetupBoard('xmu', '厦门大学', 7, 7, 1, 2);
$i->vxSetupBoard('bnu', '北京师范大学', 7, 7, 1, 2);
*/
$i->vxSetupBoard('sdu', '上海杉达学院', 7, 7, 1, 2);
?>