<?php
if (@V2EX_BABEL != 1) {
	die('<strong>Project Babel</strong><br /><br />Made by <a href="http://www.v2ex.com/">V2EX</a> | software for internet');
}

class Project {
	public $db;
	
	public $zpr_id;
	public $zpr_uid;
	public $zpr_private;
	public $zpr_title;
	public $zpr_title_plain;
	public $zpr_progress;
	public $zpr_type;
	public $zpr_type_grid;
	public $zpr_tasks;
	public $zpr_notes;
	public $zpr_dbs;
	public $zpr_created;
	public $zpr_lastupdated;
	public $zpr_lasttouched;
	public $zpr_completed;	
	public $usr_id;
	public $usr_nick;
	public $usr_nick_plain;
	public $usr_geo;
	public $usr_gender;
	public $usr_portrait;
	
	public function __construct($project_id, $db) {
		$this->db =& $db;
		
		$sql = "SELECT zpr_id, zpr_uid, zpr_private, zpr_title, zpr_progress, zpr_type, zpr_tasks, zpr_notes, zpr_dbs, zpr_created, zpr_lasttouched, zpr_lastupdated, zpr_completed, usr_id, usr_nick, usr_geo, usr_gender, usr_portrait FROM babel_zen_project, babel_user WHERE zpr_uid = usr_id AND zpr_id = {$project_id}";
		$rs = mysql_query($sql, $this->db);
		if ($_p = mysql_fetch_array($rs)) {
			$this->zpr_id = $_p['zpr_id'];
			$this->zpr_uid = $_p['zpr_uid'];
			$this->zpr_private = $_p['zpr_private'];
			$this->zpr_title = $_p['zpr_title'];
			$this->zpr_title_plain = make_plaintext($_p['zpr_title']);
			$this->zpr_progress = $_p['zpr_progress'];
			$this->zpr_type = $_p['zpr_type'];
			$this->zpr_type_grid = 'zen2_grid_' . $_p['zpr_type'] . '_s';
			$this->zpr_tasks = $_p['zpr_tasks'];
			$this->zpr_notes = $_p['zpr_notes'];
			$this->zpr_dbs = $_p['zpr_dbs'];
			$this->zpr_created = $_p['zpr_created'];
			$this->zpr_lastupdated = $_p['zpr_lastupdated'];
			$this->zpr_lasttouched = $_p['zpr_lasttouched'];
			$this->zpr_completed = $_p['zpr_completed'];
			$this->usr_id = $_p['usr_id'];
			$this->usr_nick = $_p['usr_nick'];
			$this->usr_nick_plain = make_plaintext($_p['usr_nick']);
			$this->usr_geo = $_p['usr_geo'];
			$this->usr_gender = $_p['usr_gender'];
			$this->usr_portrait = $_p['usr_portrait'];
			unset($_p);
		} else {
			$this->zpr_id = 0;
			$this->zpr_uid = 0;
			$this->zpr_private = 1;
			$this->zpr_title = '';
			$this->zpr_title_plain = '';
			$this->zpr_progress = 0;
			$this->zpr_type = 0;
			$this->zpr_type_grid = 0;
			$this->zpr_tasks = 0;
			$this->zpr_notes = 0;
			$this->zpr_dbs = 0;
			$this->zpr_created = 0;
			$this->zpr_lastupdated = 0;
			$this->zpr_lasttouched = 0;
			$this->zpr_completed = 0;
			$this->usr_id = 0;
			$this->usr_nick = '';
			$this->usr_nick_plain = '';
			$this->usr_geo = 'earth';
			$this->usr_gender = 9;
			$this->usr_portrait = '';
		}
	}
	
	public function __destruct() {
	}
}
?>