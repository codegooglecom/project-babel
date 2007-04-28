<?php
define('V2EX_BABEL', 1);

require_once('core/Settings.php');

if (isset($_GET['m'])) {
	$m = trim($m);
	if ($m == '') {
		$m = 'void';
	}
} else {
	$m = 'void';
}

if ($m == 'void') {
} else {
	$db = mysql_connect(BABEL_DB_HOSTNAME . ':' . BABEL_DB_PORT, BABEL_DB_USERNAME, BABEL_DB_PASSWORD);
	mysql_select_db(BABEL_DB_SCHEMATA);
	
	switch ($m) {
		case 'input':
			break;
		case 'output_personal':
			break;
		case 'output_friends':
			break;
		case 'output_public':
			break;
		default:
			break;
	}
	
	mysql_close($db);
}
?>