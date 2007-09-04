<?php
/* Project Babel
 *
 * Author: Livid Liu <v2ex.livid@mac.com>
 * File: /htdocs/core/SimpleStorageCore.php
 * Usage: SimpleStorage Core
 * Description: The 1st new world core, acts as the basis for *new items* storage
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

class SimpleStorage {
	private $id;
	private $name;
	private $content;
	private $hash;
	private $owner;
	public $new;
	public $loaded;
	public $saved;
	public $size;

	public function __construct($name = '') {
		$name = strtolower(trim($name));
		$this->id = 0;
		$this->owner = 0;
		$this->loaded = false;
		$this->saved = 0;
		$this->size = 0;
		if ($name != '') {
			if (SimpleStorage::validateName($name)) {
				$sql = "SELECT ssp_id, ssp_owner, ssp_name, ssp_content, ssp_hash, ssp_saved FROM babel_storage_simple WHERE ssp_name = '{$name}'";
				$rs = mysql_query($sql);
				if (mysql_num_rows($rs) == 1) {
					$_tmp = mysql_fetch_array($rs);
					mysql_free_result($rs);
					$this->name = $name;
					$this->id = $_tmp['ssp_id'];
					$this->owner = $_tmp['ssp_owner'];
					$this->content = $_tmp['ssp_content'];
					$this->hash = $_tmp['ssp_hash'];
					$this->saved = $_tmp['ssp_saved'];
					$this->loaded = true;
					$this->new = false;
					$this->size = strlen($this->content);
					unset($_tmp);
				} else {
					$this->name = $name;
					$this->loaded = false;
					$this->new = true;
				}
			} else {
				throw new Exception('Illegal SimpleStorage piece name.');
			}
		} else {
			$this->name = '';
			$this->loaded = false;
			$this->new = true;
		}
	}
	
	public function __destruct() {
	}
	
	public function get() {
		return $this->content;
	}
	
	public function set($content) {
		$size = strlen($content);
		if ($size > SIMPLESTORAGE_MAX) {
			throw new Exception("Piece is too large.");
		} else {
			$this->size = $size;
			$this->content = $content;
			if ($size > 0) {
				$this->hash = md5($content);
			} else {
				$this->hash = '';
			}
		}
	}
	
	public function getHash() {
		return $this->hash;
	}
	
	public function save() {
		if ($this->name != '') {
			if ($this->new) {
				$content_sql = mysql_real_escape_string($this->content);
				$created = time();
				$sql = "INSERT INTO babel_storage_simple(ssp_name, ssp_content, ssp_hash, ssp_saved) VALUES('{$this->name}', '{$content_sql}', '{$this->hash}', {$created})";
				mysql_query($sql);
				if (mysql_affected_rows() == 1) {
					$this->saved = $created;
					if ($this->size == 0) {
						return true;
					} else {
						return $this->size;
					}
				} else {
					throw new Exception('Failed to create new SimpleStorage piece <strong>' . $this->name . '</strong> with hash value: ' . $this->hash);
				}
			} else {
				$content_sql = mysql_real_escape_string($this->content);
				$sql = "UPDATE babel_storage_simple SET ssp_content = '{$content_sql}', ssp_hash = '{$this->hash}', ssp_saved = UNIX_TIMESTAMP() WHERE ssp_id = {$this->id} LIMIT 1";
				mysql_query($sql);
				if (mysql_affected_rows() == 1) {
					$this->saved = time();
					if ($this->size > 0) {
						return $this->size;
					} else {
						return true;
					}
				} else {
					throw new Exception('SimpleStorage piece *' . $this->name . '* is not updated.');
				}
			}
		} else {
			throw new Exception('SimpleStorage piece name is not set.');
		}
	}
	
	public function saveAs($name) {
	}
	
	public function setOwner($ownerID) {
		$this->owner = $ownerID;
	}
	
	public function getOwner() {
		return $this->owner;
	}
	
	public function setName($name) {
		if ($this->new) {
			if (SimpleStorage::validateName($name)) {
				if ($this->name == '') {
					$this->name = $name;
				} else {
					if (!SimpleStorage::checkExistByName($name)) {
						$this->name = $name;
					} else {
						throw new Exception('Failed to rename the new piece from *' . $this->name . '* to *' . $name . '*, because the new piece name already exists.');
					}
				}
			} else {
				throw new Exception('Illegal SimpleStorage piece name.');
			}
		} else {
			throw new Exception('Please use SimpleStorage::saveAs($name) to make a copy.');
		}
	}
	
	public function getName() {
		return $this->name;
	}
	
	public static function validateName($name) {
		if (preg_match('/^[0-9a-z\.\-\_]+$/', $name)) {
			return true;
		} else {
			return false;
		}
	}
	
	public static function checkExistByName($name) {
		$sql = "SELECT ssp_id FROM babel_storage_simple WHERE ssp_name = '{$name}'";
		$rs = mysql_query($sql);
		if (mysql_num_rows($rs) == 1) {
			return mysql_result($rs, 0, 0);
		} else {
			return false;
		}
	}
}
?>