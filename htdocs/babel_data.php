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
require_once('core/DataCore.php');

if (@$db = mysql_connect(BABEL_DB_HOSTNAME . ':' . BABEL_DB_PORT, BABEL_DB_USERNAME, BABEL_DB_PASSWORD)) {
	mysql_select_db(BABEL_DB_SCHEMATA);
	mysql_query("SET NAMES utf8");
	mysql_query("SET CHARACTER SET utf8");
	mysql_query("SET COLLATION_CONNECTION='utf8_general_ci'");
}

$c = Zend_Cache::factory('Core', ZEND_CACHE_TYPE_TINY, $ZEND_CACHE_OPTIONS_TINY_FRONTEND, $ZEND_CACHE_OPTIONS_TINY_BACKEND[ZEND_CACHE_TYPE_TINY]);

if (isset($_GET['m'])) {
	$m = strtolower(fetch_single($_GET['m']));
} else {
	$m = 'empty';
}

define('__PAGE__', $m);

$c = Zend_Cache::factory('Core', ZEND_CACHE_TYPE_TINY, $ZEND_CACHE_OPTIONS_TINY_FRONTEND, $ZEND_CACHE_OPTIONS_TINY_BACKEND[ZEND_CACHE_TYPE_TINY]);

switch ($m) {
	case 'empty':
	default:
		break;
		
	case 'chart_data_user':
		if ($o = $c->load(__PAGE__)) {
		} else {
			$o = Data::vxData2Amchart(Data::vxDataByMonth('usr', 'user'));
			$c->save($o, __PAGE__);
		}
		echo $o;
		break;
		
	case 'chart_data_topic':
		if ($o = $c->load(__PAGE__)) {
		} else {
			$o = Data::vxData2Amchart(Data::vxDataByMonth('tpc', 'topic'));
			$c->save($o, __PAGE__);
		}
		echo $o;
		break;
		
	case 'chart_data_topic_node':
		if ($o = $c->load(__PAGE__)) {
		} else {
			$o = Data::vxDataTopicByNode();
			$c->save($o, __PAGE__);
		}
		echo $o;
		break;
		
	case 'chart_data_post':
		if ($o = $c->load(__PAGE__)) {
		} else {
			$o = Data::vxData2Amchart(Data::vxDataByMonth('pst', 'post'));
			$c->save($o, __PAGE__);
		}
		echo $o;
		break;
		
	case 'chart_settings_user':
		echo Data::vxChartSettings('user');
		break;
		
	case 'chart_settings_topic':
		echo Data::vxChartSettings('topic');
		break;
		
	case 'chart_settings_topic_node':
		echo Data::vxChartSettings('topic_node');
		break;
		
	case 'chart_settings_post':
		echo Data::vxChartSettings('post');
		break;
}

mysql_close($db);
?>