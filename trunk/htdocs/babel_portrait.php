<?php
define('V2EX_BABEL', 1);
require_once('core/Settings.php');
require_once('core/Utilities.php');
ini_set('include_path', BABEL_PREFIX . '/libs/zf/' . ZEND_FRAMEWORK_VERSION . PATH_SEPARATOR . ini_get('include_path'));
require_once('Zend/Cache.php');

header('Cache-Control: max-age=86400');
header('Expires: ' . date('r', time() + 86400));

if (isset($_GET['fn'])) {
	$fn = make_single_safe($_GET['fn']);
	if (ZEND_CACHE_MEMCACHED_ENABLED == 'yes') {
		$cache = Zend_Cache::factory('Core', 'Memcached', $ZEND_CACHE_OPTIONS_LONG_FRONTEND, $ZEND_CACHE_OPTIONS_MEMCACHED);
	} else {
		$cache = Zend_Cache::factory('Core', 'File', $ZEND_CACHE_OPTIONS_LONG_FRONTEND, $ZEND_CACHE_OPTIONS_LONG_BACKEND);
	}
	if ($o = $cache->load('user_portrait_' . $fn)) {
		header("Content-type: image/jpeg");
		header("X-Babel: Cache Hit!");
		echo $o;
	} else {
		if (@$db = mysql_connect(BABEL_DB_HOSTNAME . ':' . BABEL_DB_PORT, BABEL_DB_USERNAME, BABEL_DB_PASSWORD)) {
			mysql_select_db(BABEL_DB_SCHEMATA);
			mysql_query("SET NAMES utf8");
			mysql_query("SET CHARACTER SET utf8");
			mysql_query("SET COLLATION_CONNECTION='utf8_general_ci'");
			$fn = mysql_real_escape_string($fn);
			$sql = "SELECT urp_content FROM babel_user_portrait WHERE urp_filename = '{$fn}'";
			$rs = mysql_query($sql);
			if ($o = mysql_fetch_array($rs)) {
				header("Content-type: image/jpeg");
				header("X-Babel: Generated!");
				$cache->save($o['urp_content'], 'user_portrait_' . $fn);
				echo $o['urp_content'];
				unset($o);
			}
			mysql_free_result($rs);
			mysql_close($db);
		}
	}
}
?>
