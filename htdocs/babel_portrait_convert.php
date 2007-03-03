<?php
$start = microtime(true);
define('V2EX_BABEL', 1);
require_once('core/Settings.php');
require_once('core/Utilities.php');
if (@$db = mysql_connect(BABEL_DB_HOSTNAME . ':' . BABEL_DB_PORT, BABEL_DB_USERNAME, BABEL_DB_PASSWORD)) {
	mysql_select_db(BABEL_DB_SCHEMATA);
	mysql_query("SET NAMES utf8");
	mysql_query("SET CHARACTER SET utf8");
	mysql_query("SET COLLATION_CONNECTION='utf8_general_ci'");
	
	$j = 0;
	foreach (glob(BABEL_PREFIX . '/htdocs/img/p_static/*.jpg') as $fn) {
		preg_match('/\/([0-9\_ns]+)\.jpg/', $fn, $m);
		$f = $m;
		$i = addslashes(file_get_contents($fn));
		$sql = "INSERT INTO babel_user_portrait(urp_filename, urp_content) VALUE('{$m[1]}', '{$i}')";
		mysql_unbuffered_query($sql) or die(mysql_error());
		$j++;
		echo ('.');
		if (($j % 100) == 0) {
			echo ('<br />');
		}
	}
	
	mysql_close($db);
	$end = microtime(true);
	$duration = $end - $start;
	echo('<hr />' . $j . ' files are converted, ' . $duration . ' seconds elapsed.');
}
?>
