<?php
class lang implements Language {
	public function lang() {
		return 'English';
	}
	
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
	
	public function search() {
		return 'Search';
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
	
	public function posts($i) {
		return $i . ' replies';
	}
	
	public function expenses() {
		return 'Expenses';
	}
	
	public function my_profile($site) {
		return 'My profile';
	}
	
	public function my_topics() {
		return 'My topics';
	}
	
	public function my_blogs() {
		return 'My weblogs';
	}
	
	public function my_messages() {
		return 'My messages';
	}
	
	public function my_friends() {
		return 'My friends';
	}
	
	public function my_favorites() {
		return 'My favorites';
	}
	
	public function send_money() {
		return 'Send money';
	}
	
	public function top_wealth() {
		return 'Top wealth';
	}
	
	public function top_topics() {
		return 'Top topics';
	}
	
	public function latest_topics() {
		return 'Latest topics';
	}
	
	public function latest_members() {
		return 'Latest members';
	}
	
	public function join_discussion() {
		return 'Join discussion';
	}
	
	public function browse_node($name, $title) {
		return 'Browse <a href="' . urlencode($name) . '" class="regular">' . make_plaintext($title) . '</a>';
	}
	
	public function more_hot_topics() {
		return 'More hot topics';
	}
	
	public function create_new_topic() {
		return 'Create new topic';
	}
	
	public function favorite_this_topic() {
		return 'Favorite this topic';
	}
	
	public function be_the_first_one_to_reply() {
		return 'No reply yet. Be the first one to reply?';
	}
	
	public function who_adds_me() {
		return '<small>Who adds me as friend?</small>';
	}
}
?>
