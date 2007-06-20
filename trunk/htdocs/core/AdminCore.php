<?php
/* Project Babel
 *
 * Author: Livid Liu <v2ex.livid@mac.com>
 * File: /htdocs/core/AdminCore.php
 * Usage: V2EX Administrator Console Core Class
 * Format: 1 tab indent(4 spaces), LF, UTF-8, no-BOM
 *  
 * Subversion Keywords:
 *
 * $Id$
 * $Date$
 * $Revision$
 * $Author$
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
 
if (V2EX_BABEL == 1) {
	/* The most important file */
	require_once('core/Settings.php');
	require_once('core/Limits.php');
	require_once('core/Features.php');
	
	/* 3rdparty PEAR cores */
	ini_set('include_path', BABEL_PREFIX . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . 'pear' . PATH_SEPARATOR . ini_get('include_path'));
	require_once('Cache/Lite.php');
	require_once('HTTP/Request.php');
	require_once('Crypt/Blowfish.php');
	require_once('Net/Dict.php');
	require_once('Mail.php');
	if (BABEL_DEBUG) {
		require_once('Benchmark/Timer.php');
	}
	
	/* 3rdparty Zend Framework cores */
	ini_set('include_path', BABEL_PREFIX . '/libs/zf/' . ZEND_FRAMEWORK_VERSION . PATH_SEPARATOR . ini_get('include_path'));
	require_once('Zend/Search/Lucene.php');
	require_once('Zend/Cache.php');
	require_once('Zend/Feed.php');
	require_once('Zend/Feed/Rss.php');
	require_once('Zend/Http/Client.php');
	
	/* 3rdparty cores */
	require_once(BABEL_PREFIX . '/libs/magpierss/rss_fetch.inc');
	require_once(BABEL_PREFIX . '/libs/smarty/libs/Smarty.class.php');
	require_once(BABEL_PREFIX . '/libs/kses/kses.php');
	
	/* built-in cores */
	require_once('core/Vocabularies.php');
	require_once('core/Utilities.php');
	require_once('core/Shortcuts.php');
	require_once('core/AirmailCore.php');
	require_once('core/UserCore.php');
	require_once('core/LanguageCore.php');
	require_once('core/NodeCore.php');
	require_once('core/GeoCore.php');
	require_once('core/ProjectCore.php');
	require_once('core/TopicCore.php');
	require_once('core/ChannelCore.php');
	require_once('core/URLCore.php');
	require_once('core/ZenCore.php');
	require_once('core/FunCore.php');
	require_once('core/WidgetCore.php');
	require_once('core/ImageCore.php');
	require_once('core/ValidatorCore.php');
	require_once('core/BookmarkCore.php');
} else {
	die('<strong>Project Babel</strong><br /><br />Made by <a href="http://labs.v2ex.com/">V2EX</a> | software for internet');
}

class Admin {
	public function __construct() {
	}
	
	public function __destruct() {
	}
	
	public function vxHome() {
	}
}
?>