<?php
/* Project Babel
*  Author: Livid Torvalds
*  File: /htdocs/core/DryCore.php
*  Usage: V2EX Dry Core Class
*  Format: 1 tab indent(4 spaces), LF, UTF-8, no-BOM
*  
*  Subversion Keywords:
*
*  $Id$
*  $LastChangedDate$
*  $LastChangedRevision$
*  $LastChangedBy$
*  $URL$
*/

if (V2EX_BABEL == 1) {
	/* The most important file */
	require('core/Settings.php');
	
	/* 3rdparty PEAR cores */
	ini_set('include_path', BABEL_PREFIX . '/libs/pear' . PATH_SEPARATOR . ini_get('include_path'));
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
	
	/* 3rdparty cores */
	require(BABEL_PREFIX . '/libs/magpierss/rss_fetch.inc');
	require(BABEL_PREFIX . '/libs/smarty/libs/Smarty.class.php');
	require(BABEL_PREFIX . '/libs/kses/kses.php');
	
	/* built-in cores */
	require('core/Vocabularies.php');
	require('core/Utilities.php');
	require('core/AirmailCore.php');
	require('core/UserCore.php');
	require('core/NodeCore.php');
	require('core/TopicCore.php');
	require('core/ChannelCore.php');
	require('core/URLCore.php');
	require('core/FunCore.php');
	require('core/ImageCore.php');
	require('core/ValidatorCore.php');
} else {
	die('<strong>Project Babel</strong><br /><br />Made by V2EX | software for internet');
}

class Dry {
	
}
?>