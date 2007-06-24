<?php
/* Project Babel
 *
 * Author: Livid Liu <v2ex.livid@mac.com>
 * File: /htdocs/core/EntryCore.php
 * Usage: Weblog Entry Core Class
 * Format: 1 tab indent(4 spaces), LF, UTF-8, no-BOM
 *  
 * Subversion Keywords:
 *
 * $Id: V2EXCore.php 155 2007-06-23 22:46:15Z v2ex.livid $
 * $Date: 2007-06-24 06:46:15 +0800 (Sun, 24 Jun 2007) $
 * $Revision: 155 $
 * $Author: v2ex.livid $
 * $URL: https://project-babel.googlecode.com/svn/trunk/htdocs/core/V2EXCore.php $
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

class Entry {
	public function __construct($entry_id) {
		$sql = "SELECT bge_id FROM babel_weblog_entry WHERE bge_id = {$entry_id}";
		$rs = mysql_num_rows($sql);
		if (mysql_num_rows($rs) == 1) {
			$this->entry = true;
			$_entry = mysql_fetch_array($rs);
			$this->bge_id = $_entry['bge_id'];
		} else {
			$this->entry = false;
		}
	}
}
?>