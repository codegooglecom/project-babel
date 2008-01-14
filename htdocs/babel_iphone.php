<?php
/* Project Babel
 *
 * Author: Livid Liu <v2ex.livid@mac.com>
 * File: /htdocs/babel.php
 * Usage: Loader for Web
 * Format: 1 tab indent(4 spaces), LF, UTF-8, no-BOM
 *
 * Subversion Keywords:
 *
 * $Id: babel.php 287 2007-10-06 18:52:15Z v2ex.livid $
 * $LastChangedDate: 2007-10-07 02:52:15 +0800 (Sun, 07 Oct 2007) $
 * $LastChangedRevision: 287 $
 * $LastChangedBy: v2ex.livid $
 * $URL: https://project-babel.googlecode.com/svn/trunk/htdocs/babel.php $
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

DEFINE('V2EX_BABEL', 1);

require('core/iPhoneCore.php');

if (isset($_GET['m'])) {
	$m = strtolower(trim($_GET['m']));
} else {
	$m = 'home';
}

define('__PAGE__', $m);

switch ($m) {
	case 'home':
		break;
		 
	case 'node':
		break;
}
?>