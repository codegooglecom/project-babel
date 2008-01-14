<?php
/* Project Babel
 *
 * Author: Livid Liu <v2ex.livid@mac.com>
 * File: /htdocs/babel_iphone.php
 * Usage: Loader for iPhone
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