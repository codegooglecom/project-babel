<?php
class lang implements Language {
	public function lang() {
		return 'US English';
	}
	
	public function login() {
		return 'Sign In';
	}
	
	public function signed_in($site) {
		return 'You have signed in ' . $site;
	}
	
	public function logout() {
		return 'Sign Out';
	}
	
	public function now_auto_redirecting($site) {
		return '<small>Now auto redirecting to <a href="/">' . $site . '</a> home page, or you can <a href="/">click here</a> to manual redirect</small>';
	}
	
	public function sign_in_again() {
		return 'Sign In Again';
	}
	
	public function you_have_signed_out($site) {
		return 'You have signed out from ' . $site;
	}
	
	public function privacy_ok() {
		return '<small>No personal information is stored on this computer now.</small>';
	}
	
	public function welcome_back_anytime() {
		return 'Welcome back anytime!';
	}
	
	public function return_home($site) {
		return 'Return Home';
	}
	
	public function home($site) {
		return $site . ' Home';
	}
	
	public function help() {
		return 'Help';
	}
	
	public function new_features() {
		return 'New Features';
	}
	
	public function user_id() {
		return 'User ID';
	}
	
	public function password() {
		return 'Password';
	}
	
	public function take_a_tour() {
		return 'Take a Tour';
	}
	
	public function about($site) {
		return 'About ' . $site;
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
		return 'Sign Up';
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
	
	public function timtowtdi() {
		return "There's more than one way to do it";
	}
	
	public function reply() {
		return 'Reply';
	}
	
	public function login_and_reply() {
		return 'Sign In and Reply';
	}
	
	public function switch_description() {
		return 'Switch Description';
	}
	
	public function jump_to_replies() {
		return 'Jump to Replies';
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
		return 'My Profile';
	}
	
	public function my_topics() {
		return 'My Topics';
	}
	
	public function my_blogs() {
		return 'My Weblogs';
	}
	
	public function my_messages() {
		return 'My Messages';
	}
	
	public function my_friends() {
		return 'My Friends';
	}
	
	public function my_favorites() {
		return 'My Favorites';
	}
	
	public function send_money() {
		return 'Send Money';
	}
	
	public function top_wealth() {
		return 'Top Wealth';
	}
	
	public function top_topics() {
		return 'Top Topics';
	}
	
	public function latest_topics() {
		return 'Latest Topics';
	}
	
	public function latest_members() {
		return 'Latest Members';
	}
	
	public function join_discussion() {
		return 'Join Discussion';
	}
	
	public function browse_node($name, $title) {
		return 'Browse <a href="' . urlencode($name) . '" class="regular">' . make_plaintext($title) . '</a>';
	}
	
	public function more_hot_topics() {
		return 'More Hot Topics';
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
