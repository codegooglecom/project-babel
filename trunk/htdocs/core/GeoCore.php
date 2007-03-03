<?php
/* Project Babel
*  Author: Livid Torvalds
*  File: /htdocs/core/GeoCore.php
*  Usage: Geo Class
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

if (@V2EX_BABEL != 1) {
	die('<strong>Project Babel</strong><br /><br />Made by <a href="http://www.v2ex.com/">V2EX</a> | software for internet');
}

/* S Geo class */

class Geo {
	public $map;
	public $path;
	public $geo;
	
	public function __construct($geo, $map_loaded = false) {
		if ($map_loaded) {
			$this->map = $map_loaded;
		} else {
			$xml = simplexml_load_file(BABEL_PREFIX . '/geo/map.xml');
			$this->map = array();
			
			$this->map['path'] = array();
			foreach ($xml->xpath('//location') as $node) {
				$this->map['path'][strval($node->geo)] = strval($node->at) . strval($node->geo);
			}
			
			$this->map['name'] = array();
			foreach ($xml->xpath('//location') as $node) {
				$this->map['name'][strval($node->geo)] = strval($node->cn);
			}
			
			$xml = null;
		}
		$this->geo = new stdClass();
		$this->geo->geo = $geo;
		if (array_key_exists($geo, $this->map['path'])) {
			$this->geo->at = $this->map['path'][$geo];
		} else {
			$this->geo->geo = false;
		}
		if ($this->geo->geo != false) {
			$this->geo->xml = $this->vxRead($geo);
			$this->geo->name = new stdClass();
			$this->geo->name->cn = strval($this->geo->xml->name->cn);
			$this->geo->name->en = strval($this->geo->xml->name->en);
			$this->geo->description = new stdClass();
			$this->geo->description->cn = strval($this->geo->xml->description->cn);
			if (isset($this->geo->xml->description->via)) {
				$this->geo->description->via = strval($this->geo->xml->description->via);
			} else {
				$this->geo->description->via = '';
			}
		}
	}
	
	public function __destruct() {
	}
	
	public function vxRead($geo) {
		$xml = simplexml_load_file(BABEL_PREFIX . '/geo' . $this->map['path'][$geo] . '/data.xml');
		return $xml;
	}
	
	public function vxIsExist($geo = '') {
		if ($geo == '') {
			return false;
		} else {
			if (array_key_exists($geo, $this->map['name'])) {
				return true;
			} else {
				return false;
			}
		}
	}
	
	public static function vxAddUsageSimple($Geo, $db) {
		$_t = time();
		$sql = "UPDATE babel_geo_usage_simple SET gus_hits = gus_hits + 1, gus_lastupdated = {$_t} WHERE gus_geo = '{$Geo->geo}'";
		mysql_query($sql, $db);
		if (mysql_affected_rows($db) == 1) {
			return true;
		} else {
			$Geo->name->cn_real = mysql_real_escape_string($Geo->name->cn, $db);
			$Geo->name->en_real = mysql_real_escape_string($Geo->name->en, $db);
			$sql = "INSERT INTO babel_geo_usage_simple(gus_geo, gus_name_cn, gus_name_en, gus_hits, gus_lastupdated) VALUES('{$Geo->geo}', '{$Geo->name->cn_real}', '{$Geo->name->en_real}', 1, $_t)";
			mysql_query($sql, $db);
			if (mysql_affected_rows($db) == 1) {
				return true;
			} else {
				return false;
			}
		}
	}
	
	public function vxGetParallelArray($geo = '') {
		if ($geo == '') {
			$geo = $this->geo->geo;
		}
		$_path = substr($this->map['path'][$geo], 0, strlen($this->map['path'][$geo]) - strlen($geo));
		$A = array();
		$_base = BABEL_PREFIX . '/geo' . $_path;
		$dh = opendir($_base);
		while (($_geo = readdir($dh)) !== false) {
			if (is_valid_geo($_geo)) {
				if ($geo != $_geo) {
					$A[$_geo] = $this->map['name'][$_geo];
				}
			}
		}
		closedir($dh);
		ksort($A);
		return $A;
	}
	
	public function vxGetChildrenArray($geo = '') {
		if ($geo == '') {
			$geo = $this->geo->geo;
		}
		$_path = $this->map['path'][$geo];
		$A = array();
		$_base = BABEL_PREFIX . '/geo' . $_path;
		$dh = opendir($_base);
		while (($_geo = readdir($dh)) !== false) {
			if (is_valid_geo($_geo)) {
				$A[$_geo] = $this->map['name'][$_geo];
			}
		}
		closedir($dh);
		ksort($A);
		return $A;
	}
	
	public function vxGetRecursiveChildrenArray($geo = '', $sql = false) {
		if ($geo == '') {
			$geo = $this->geo->geo;
		}
		$A = array();
		$prefix = $this->map['path'][$geo];
		$prefix_length = mb_strlen($prefix, 'UTF-8');
		foreach ($this->map['path'] as $g => $p) {
			if (mb_substr($p, 0, $prefix_length) == $prefix && $g != $geo) {
				if ($sql) {
					$A[] = "'" . mysql_real_escape_string($g) . "'";
				} else {
					$A[$g] = $this->map['name'][$g];
				}
			}
		}
		if ($sql) {
			$A[] = "'" . mysql_real_escape_string($geo) . "'";
		}
		ksort($A);
		return $A;
	}
	
	public function vxGetParentObject($geo = '') {
		if ($geo == '') {
			$geo = $this->geo->geo;
		}
		$_path = $this->map['path'][$geo];
		$_path_a = explode('/', $_path);
		if (count($_path_a) == 2) {
			return false;
		} else {
			$_o = new stdClass();
			$_o->geo = $_path_a[count($_path_a) - 2];
			$_o->name = new stdClass();
			$_o->name->cn = $this->map['name'][$_o->geo];
			return $_o;
		}
	}
	
	public function vxGetRoute($geo = '') {
		if ($geo == '') {
			$geo = $this->geo->geo;
		}
		$route = array();
		$map = $this->map['path'][$geo];		
		preg_match_all('/\//', $map, $seps);
		if (count($seps[0]) > 1) {
			$root = false;
		} else {
			$root = true;
		}
		if ($root) {
			$route[$geo] = $this->map['name'][$geo];
		} else {
			$path = explode('/', substr($map, 1, strlen($map)));
			$i = 0;
			foreach ($path as $g) {
				$i++;
				if ($g != $geo) {
					$route[$g] = $this->map['name'][$g];
				}
			}
			$route[$geo] = $this->map['name'][$geo];
		}
		return $route;
	}
}

/* E Geo class */
?>
