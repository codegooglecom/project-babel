<?php
/* Project Babel
 *
 * Author: Xin, Liu (a.k.a Livid)
 * File: /htdocs/core/IngCore.php
 * Usage: Ing Logic
 * Format: 1 tab indent(4 spaces), LF, UTF-8, no-BOM
 *  
 * Subversion Keywords:
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
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
	require('core/Settings.php');
	
	/* 3rdParty PEAR cores */
	ini_set('include_path', BABEL_PREFIX . '/libs/pear' . PATH_SEPARATOR . ini_get('include_path'));
	require_once('Cache/Lite.php');
	require_once('HTTP/Request.php');
	require_once('Crypt/Blowfish.php');
	
	/* 3rdparty Zend Framework cores */
	ini_set('include_path', BABEL_PREFIX . '/libs/zf/' . ZEND_FRAMEWORK_VERSION . PATH_SEPARATOR . ini_get('include_path'));
	require_once('Zend/Json.php');
	require_once('Zend/Cache.php');

	/* built-in cores */
	require('core/Vocabularies.php');
	require('core/Utilities.php');
	require('core/UserCore.php');
	require('core/NodeCore.php');
	require('core/TopicCore.php');
	require('core/ZenCore.php');
	require('core/GeoCore.php');
	require('core/ChannelCore.php');
	require('core/URLCore.php');
	require('core/ImageCore.php');
	require('core/ValidatorCore.php');
} else {
	die('<strong>Project Babel</strong><br /><br />Made by V2EX | software for internet');
}

class Ing {
	public $User;
	
	public $db;
	
	/* S module: constructor and destructor */
	
	public function __construct() {
		check_env();
		
		$this->db = mysql_connect(BABEL_DB_HOSTNAME . ':' . BABEL_DB_PORT, BABEL_DB_USERNAME, BABEL_DB_PASSWORD);
		mysql_select_db(BABEL_DB_SCHEMATA);
		mysql_query("SET NAMES utf8");
		mysql_query("SET CHARACTER SET utf8");
		mysql_query("SET COLLATION_CONNECTION='utf8_general_ci'");
		session_set_cookie_params(2592000);
		session_start();
		$this->User = new User('', '', $this->db);
		$this->Validator =  new Validator($this->db, $this->User);
		if (!isset($_SESSION['babel_ua'])) {
			$_SESSION['babel_ua'] = $this->Validator->vxGetUserAgent();
		}
		$this->URL = new URL();
		global $CACHE_LITE_OPTIONS_SHORT;
		$this->cs = new Cache_Lite($CACHE_LITE_OPTIONS_SHORT);
		global $CACHE_LITE_OPTIONS_LONG;
		$this->cl = new Cache_Lite($CACHE_LITE_OPTIONS_LONG);
	}
	
	public function __destruct() {
		if ($this->db) {
			mysql_close($this->db);
		}
	}
	
	/* E module: constructor and destructor */

	public function vxPersonal($User) {
	}
	
	public function vxPublic() {
	}
}
?>
