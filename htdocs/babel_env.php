<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>Babel Environment</title>
<link rel="stylesheet" type="text/css" href="/css/errors/style.css" />
</head>
<body>
<div class="error">
<h1>Babel Environment</h1>
<?php
$score = 0;
$version = PHP_VERSION;
if (substr($version, 0, 1) == '5') {
	$score++;
	echo('PHP Version: <strong>' . PHP_VERSION . '</strong> <strong class="green">Supported</strong>');
} else {
	echo('PHP Version: <strong>' . PHP_VERSION . '</strong> <strong class="red">Unsupported</strong>');
}
echo('<br />');
if (function_exists('apc_cache_info')) {
	$score++;
	echo('... Alternative PHP Cache: <strong class="green">Supported</strong>');
} else {
	echo('... Alternative PHP Cache: <strong class="red">Unsupported</strong>');
}
echo('<br />');
if (@$mmc = new Memcache) {
	$score++;
	echo('... Memcache: <strong class="green">Supported</strong>');
} else {
	echo('... Memcache: <strong class="red">Unsupported</strong>');
}
echo('<br />');
if (function_exists('apache_get_version')) {
	$score++;
	echo('Runtime Environment: <strong><small>' . apache_get_version() . '</small></strong>');
	echo('<br />');
	$_modules = apache_get_modules();
	if (in_array('mod_rewrite', $_modules)) {
		$score++;
		echo('... mod_rewrite: <strong class="green">Supported</strong>');
	} else {
		echo('... mod_rewrite: <strong class="red">Missing</strong>');
	}
} else {
	echo('Runtime Environment: CGI/FastCGI');
}
echo('<br />');
if (function_exists('mysql_connect')) {
	$score++;
	echo('MySQL Client API: <strong class="green">Supported</strong>');
} else {
	echo('MySQL Client API: <strong class="red">Missing</strong>');
}
echo('<br />');
if (file_exists('core/Settings.php')) {
	$score++;
	echo('Configuration File: <strong class="green">Found</strong>');
	echo('<br /><span class="tip">... Now proceed to parse the configuration file.</span>');
	define('V2EX_BABEL', 1);
	include('core/Settings.php');
	echo('<br /><span class="tip">... Configuration file loaded.</span>');
	echo('<br /><span class="tip">... Prepare to connect the database.</span>');
	$db = @mysql_connect(BABEL_DB_HOSTNAME . ':' . BABEL_DB_PORT, BABEL_DB_USERNAME, BABEL_DB_PASSWORD);
	echo('<br />');
	if ($db) {
		$score++;
		echo('Database Connection: <strong class="green">Established</strong>');
		$mysql_version = mysql_get_server_info();
		echo('<br />');
		$mysql_version_major = intval(substr($mysql_version, 0, 1));
		$mysql_version_minor = intval(substr($mysql_version, 2, 1));
		echo('Database Version: <strong>' . $mysql_version . '</strong>');
		if ($mysql_version_major == 4) {
			if ($mysql_version_minor > 0) {
				$score++;
				echo(' <strong class="green">Supported</strong>');
			} else {
				echo(' <strong class="red">Unsupported</strong>');
			}
		} else {
			if ($mysql_version_major > 4) {
				$score++;
				echo(' <strong class="green">Supported</strong>');
			} else {
				echo(' <strong class="red">Unsupported</strong>');
			}
		}
		mysql_close($db);
	} else {
		echo('Database Connection: <strong class="red">Failed</strong>');
	}
} else {
	echo('Configuration file: <strong class="red">not found</strong>');
}

echo('<br />');
echo('Overall Score: <strong>' . $score . '</strong>');
?>
<span class="ver">&copy; 2007 <a href="http://www.v2ex.com/">V2EX</a> | Project Babel v0.5 Monster Inc</span>
</div>
</body>
</html>