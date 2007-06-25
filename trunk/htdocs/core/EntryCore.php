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

class Entry {
	public function __construct($entry_id) {
		$sql = "SELECT bge_id, bge_pid, bge_uid, bge_title, bge_body, bge_comments, bge_trackbacks, bge_tags, bge_status, bge_mode, bge_revisions, bge_hash, bge_created, bge_lastupdated, bge_published FROM babel_weblog_entry WHERE bge_id = {$entry_id}";
		$rs = mysql_num_rows($sql);
		if (mysql_num_rows($rs) == 1) {
			$this->entry = true;
			$_entry = mysql_fetch_array($rs);
			$this->bge_id = intval($_entry['bge_id']);
			$this->bge_pid = intval($_entry['bge_id']);
			$this->bge_uid = intval($_entry['bge_id']);
			$this->bge_title = $_entry['bge_title'];
			$this->bge_body = $_entry['bge_body'];
			$this->bge_comments = intval($_entry['bge_comments']);
			$this->bge_trackbacks = intval($_entry['bge_trackbacks']);
			$this->bge_tags = $_entry['bge_tags'];
			$this->bge_status = intval($_entry['bge_status']);
			$this->bge_mode = intval($_entry['bge_mode']);
			$this->bge_revisions = intval($_entry['bge_revisions']);
			$this->bge_hash = $_entry['bge_hash'];
			$this->bge_created = intval($_entry['bge_created']);
			$this->bge_lastupdated = intval($_entry['bge_lastupdated']);
			$this->bge_published = intval($_entry['bge_published']);
		} else {
			$this->entry = false;
		}
	}
}
?>