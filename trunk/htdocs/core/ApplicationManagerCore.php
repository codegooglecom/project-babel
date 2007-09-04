<?php
/* Project Babel
 *
 * Author: Livid Liu <v2ex.livid@mac.com>
 * File: /htdocs/core/ApplicationManagerCore.php
 * Usage: ApplicationManager Core
 * Description: The 2nd new world core, providing functions for applications' shop
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
 
if (@V2EX_BABEL != 1) {
	die('<strong>Project Babel</strong><br /><br />Made by <a href="http://labs.v2ex.com/">V2EX</a> | software for internet');
}

class ApplicationManager {
	const APPLICATION_REPOSITORY = 'app';
	public static function getApplications() { // return: array
		$_applications = array();
		$dir_apps = BABEL_PREFIX . '/htdocs/' . ApplicationManager::APPLICATION_REPOSITORY;
		$_a = scandir($dir_apps);
		$_sys = array('.', '..', '.svn');
		foreach ($_a as $app) {
			if (!in_array($app, $_sys)) {
				$_applications[] = $app;
			}
		}
		unset($_sys); unset($_a);
		return $_applications;
	}
	
	public static function loadApplications($_apps = '') {
		if ($_apps == '') {
			$_apps = ApplicationManager::getApplications();
		}
		foreach ($_apps as $app) {
			require_once(ApplicationManager::APPLICATION_REPOSITORY . '/' . $app . '/manifest.php');
		}
	}
	
}
?>