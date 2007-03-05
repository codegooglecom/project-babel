<?php
function is_valid_entry($name) {
	if (substr($name, 0, 1) == '.') {
		return false;
	} else {
		if ($name == 'meta') {
			return false;
		} else {
			return true;
		}
	}
}

function is_valid_set($name) {
	if (substr($name, 0, 1) == '.') {
		return false;
	} else {
		if ($name == 'meta') {
			return false;
		} else {
			return true;
		}
	}
}

function get_set_meta($file) {
	$fh = fopen($file, 'r');
	$i = 0;
	$_meta = array();
	while (!feof($fh)) {
		$line = fgets($fh, 8192);
		$i++;
		if ($i < 7) {
			$_line = trim($line);
			$_attribute = explode('|', $line);
			$_meta[trim($_attribute[0])] = trim($_attribute[1]);
		}
	}
	fclose($fh);
	return $_meta;
}

function make_entry($base, $set, $entry, $output, $entries) {
	global $f;
	$_fh = fopen($base . '/' . $set['title'] . '/' . $entry, 'r');
	$_i = 0;
	$_meta = array();
	$_content = '';
	$_final = '';
	while (!feof($_fh)) {
		$_line = fgets($_fh, 8192);
		$_i++;
		if ($_i < 8) {
			$_line = trim($_line);
			$_attribute = explode('|', $_line);
			$_meta[trim($_attribute[0])] = trim($_attribute[1]);
		} else {
			$_content .= $_line;
		}
	}
	
	$_final = file_get_contents('./tpl/' . $_meta['model'] . '.html');
	$_final = str_replace('[set]', '<a href="../index.html">' . SITE_NAME . '</a> &gt; <a href="index.html">' . $set['title-full'] . '</a> &gt; ' . $_meta['title-full'], $_final);
	$_final = str_replace('[title]', $_meta['title'], $_final);
	$_final = str_replace('[title-full]', $_meta['title-full'], $_final);
	$_final = str_replace('[author]', '<a href="' . $_meta['writer-url'] . '">' . $_meta['writer-name'] . '</a>', $_final);
	$_final = str_replace('[writer-name]', $_meta['writer-name'], $_final);
	$_final = str_replace('[writer-url]', $_meta['writer-url'], $_final);
	$_final = str_replace('[adsense-client]', $_meta['adsense-client'], $_final);
	$_final = str_replace('[adsense-channel]', $_meta['adsense-channel'], $_final);
	$_final = str_replace('[content]', $_content, $_final);
	$_final = str_replace('[built]', date('r', time()), $_final);
	
	$_w = file_put_contents($output . '/' . $_meta['title'] . '.html', $_final);
	$f++;
	fclose($_fh);
	
	echo('[CREATED] - [' . $set['title-full'] . '] - ' . $_meta['title-full'] . " ({$_w} bytes)\n");
	$entries[$_meta['title']] = $_meta['title-full'];
	return $entries;
}

function make_set($entries, $set, $output) {
	global $f;
	$_content = '';
	$_content .= '<p>' . $set['description'] . '</p>';
	$_content .= '<p><ul>';
	foreach ($entries as $_title => $_title_full) {
		$_content .= '<li><a href="' . $_title . '.html">' . $_title_full . '</a></li>';
	}
	$_content .= '</ul></p>';
	
	$_final = file_get_contents('./tpl/' . $set['model'] . '.html');
	$_final = str_replace('[set]', '<a href="../index.html">' . SITE_NAME . '</a> &gt; ' . $set['title-full'], $_final);
	$_final = str_replace('[title]', $set['title'], $_final);
	$_final = str_replace('[title-full]', $set['title-full'], $_final);
	$_final = str_replace('[author]', 'various authors', $_final);
	$_final = str_replace('[adsense-client]', $set['adsense-client'], $_final);
	$_final = str_replace('[adsense-channel]', $set['adsense-channel'], $_final);
	$_final = str_replace('[content]', $_content, $_final);
	$_final = str_replace('[built]', date('r', time()), $_final);
	$_w = file_put_contents($output . '/index.html', $_final);
	$f++;
	
	echo('[CREATED] - [' . $set['title-full'] . "] ({$_w} bytes)\n");
}

function make_site($site) {
	global $f;
	$_site = get_set_meta('./data/meta');
	
	$_content = '';
	$_content .= '<p>' . $_site['description'] . '</p>';
	$_content .= '<p><ul>';
	foreach ($site->sets as $_set) {
		$_content .= '<li><a href="' . $_set['title'] . '/index.html">' . $_set['title-full'] . '</a> - <span class="tip">' . $_set['description'] . '</span></li>';
	}
	$_content .= '</ul></p>';

	$_final = file_get_contents('./tpl/' . $_site['model'] . '.html');
	$_final = str_replace('[set]', $_site['title-full'], $_final);
	$_final = str_replace('[title]', $_site['title'], $_final);
	$_final = str_replace('[title-full]', $_site['title-full'], $_final);
	$_final = str_replace('[author]', 'various authors', $_final);
	$_final = str_replace('[adsense-client]', $_site['adsense-client'], $_final);
	$_final = str_replace('[adsense-channel]', $_site['adsense-channel'], $_final);
	$_final = str_replace('[content]', $_content, $_final);
	$_final = str_replace('[built]', date('r', time()), $_final);
	$_w = file_put_contents('./htdocs/index.html', $_final);
	$f++;
	
	echo('[CREATED] - [' . $_site['title-full'] . "] ({$_w} bytes)\n");
}
?>
