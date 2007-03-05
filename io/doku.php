<?php
/*
 * 
 * $Id: doku.php 21 2006-10-27 00:11:23Z livid $
 *
 */

$_start = microtime(true);

require_once ('inc/functions.php');
require_once ('inc/config.php');

// Loads all entries:

$base = './data';
$data = opendir($base);

$_o = new stdClass();
$_o->sets = array();

$f = 0;

while (($set = readdir($data)) !== false) {
	if (is_valid_set($set)) {
		$_path_output = './htdocs/' . $set;
		if (!file_exists($_path_output)) {
			mkdir($_path_output);
		}
		$_set = get_set_meta($base . '/' . $set . '/meta');
		$_o->sets[$_set['title']] = $_set;
		$_entries = array();
		$dh = opendir($base . '/' . $set);
		while (($entry = readdir($dh)) !== false) {
			if (is_valid_entry($entry)) {
				$_entries = make_entry($base, $_set, $entry, $_path_output, $_entries);
			}
		}
		make_set($_entries, $_set, $_path_output);
		closedir($dh);
	}
}

closedir($data);

make_site($_o);

$_over = microtime(true);

echo ("[INFO] - " . substr(strval(floatval($_over - $_start)), 0, 5) . " seconds elapsed.\n");
echo ('[INFO] - ' . strval($f) . " files created.\n");
?>
