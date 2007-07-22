<?php

class lang implements Language {
	
	public function login() {
		return 'Sign In';
	}
	
	public function logout() {
		return 'Sign Out';
	}
	
	public function copper($i) {
		if ($i > 10000) {
			$g = floor($i / 10000);
			$r = $i - (10000 * $g);
			if ($r > 100) {
				$s = floor($r / 100);
				$r2 = $r - (100 * $s);
				if ($r2 > 0) {
					return '<small>' . $g . '</small>g <small>' . $s . '</small>s <small>' . $r2 . '</small>c';
				} else {
					return '<small>' . $g . '</small>g <small>' . $s . '</small>s';
				}
			} else {
				return '<small>' . $i . '</small> c';
			}
		} else {
			if ($i > 100) {
				$s = floor($i / 100);
				$r = $i - (100 * $s);
				if ($r > 0) {
					return '<small>' . $s . '</small> s <small>' . $r . '</small> c';
				} else {
					return '<small>' . $s . '</small> s';
				}
			} else {
				return '<small>' . $i . '</small> c';
			}
		}
	}
	
	public function register() {
		return 'Register';
	}
	
	public function settings() {
		return 'Settings';
	}
	
	public function password_recovery() {
		return 'Password Recovery';
	}
	
	public function new_features() {
		return 'New Features';
	}
	
	public function timtowtdi() {
		return "There's more than one way to do it";
	}
	
	public function reply() {
		return 'Reply';
	}
	
	public function login_and_reply() {
		return 'Sign in and reply';
	}
	
	public function switch_description() {
		return 'Switch description';
	}
	
	public function jump_to_replies() {
		return 'Jump to replies';
	}
	
	public function hits($i) {
		return $i . ' hits';
	}
	
	public function my_profile($site_name) {
		return 'My ' . $site_name . ' profile';
	}
	
	public function my_topics() {
		return 'My topics';
	}
	
	public function my_blogs() {
		return 'My weblogs';
	}
	
	public function send_money() {
		return 'Send money';
	}
}

?>
