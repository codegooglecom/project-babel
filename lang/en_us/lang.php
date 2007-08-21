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
	
	public function shuffle_home() {
		return 'Shuffle Front Page';
	}
	
	public function remix_home() {
		return 'Remix Front Page';
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
	
	public function email() {
		return 'E-mail';
	}
	
	public function password() {
		return 'Password';
	}
	
	public function password_again() {
		return 'Password Again';
	}
	
	public function new_password() {
		return 'New Password';
	}
	
	public function new_password_again() {
		return 'New Password Again';
	}
	
	public function gender() {
		return 'Gender';
	}
	
	public function gender_categories() {
		return array(0 => 'Unknown', 1 => 'Male', 2 => 'Female', 5 => 'Female changed to Male', 6 => 'Male changed to Female', 9 => 'Not to tell');
	}
	
	public function confirmation_code() {
		return 'Confirmation Code';
	}
	
	public function confirmation_code_tips() {
		return '<li>Please type what you read</li><li>Case insensitive</li><li>No numbers</li><li>Only for human</li>';
	}
	
	public function email_or_nick() {
		return 'E-mail or Nickname';
	}
	
	public function register_agreement() {
		return 'By clicking "Sign Up", you\'re agreeing to our <a href="/terms.vx" class="regular">terms of use</a>, <a href="/privacy.vx" class="regular">privacy policy</a> and <a href="/community_guidelines.vx" class="regular">community guidelines</a>.';
	}
	
	public function take_a_tour() {
		return 'Take a Tour';
	}
	
	public function about($site) {
		return 'About ' . $site;
	}
	
	public function rss() {
		return 'RSS';
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
	
	public function ref_search() {
		return 'References Search';
	}
	
	public function tools() {
		return 'Tools';
	}
	
	public function go_on() { // Because "continue" is a reserved keyword.
		return 'Continue';
	}
	
	public function members_total() {
		return 'Members Total';
	}
	
	public function discussions() {
		return 'Discussions';
	}
	
	public function favorites() {
		return 'Favorites';
	}
	
	public function savepoints() {
		return 'Savepoints';
	}
	
	public function ing_updates() {
		return 'Ing Updates';
	}
	
	public function weblogs() {
		return 'Weblogs';
	}
	
	public function online_total() {
		return 'Online Total';
	}
	
	public function online_now() {
		return 'Online Now';
	}
	
	public function online_details($onl_created, $onl_lastmoved) {
		return 'signed in at ' . make_descriptive_time($onl_created) . ', last moved at ' . make_descriptive_time($onl_lastmoved);
	}
	
	public function disconnected() {
		return 'Disconnected';
	}
	
	public function anonymous() {
		return 'Anonymous';
	}
	
	public function registered() {
		return 'Registered';
	}
	
	public function system_status() {
		return 'System Status';
	}
	
	public function online_count($i) {
		return $i . ' Online';
	}
	
	public function session_count($i) {
		return '' . $i . ' Pages Visted';
	}
	
	public function login_history() {
		return 'Login History';
	}
	
	public function upload_portrait() {
		return 'Upload Portrait';
	}
	
	public function settings() {
		return 'Settings';
	}
	
	public function set_location() {
		return 'Set Location';
	}
	
	public function password_recovery() {
		return 'Password Recovery';
	}
	
	public function password_recovery_tips() {
		return 'Please input your registered E-mail address to get your password. If the address does exist in our system, then a mail containing instructions will be sent to you, click the address in the mail and set a new password.';
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
	
	public function me() {
		return 'Me';
	}
	
	public function topic_creator() {
		return 'Topic Creator';
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
	
	public function hottest_topics() {
		return 'Hottest Topics';
	}
	
	public function hottest_discussion_boards() {
		return 'Hottest Discussion Boards';
	}
	
	public function random_discussion_boards() {
		return 'Random Discussion Boards';
	}
	
	public function latest_topics() {
		return 'Latest Topics';
	}
	
	public function latest_replied() {
		return 'Latest Replied';
	}
	
	public function latest_unanswered() {
		return 'Latest Unanswered';
	}
	
	public function latest_members() {
		return 'Latest Members';
	}
	
	public function join_discussion() {
		return 'Join Discussion';
	}
	
	public function browse_node($name, $title) {
		return 'Browse <a href="/go/' . urlencode($name) . '" class="regular">' . make_plaintext($title) . '</a>';
	}
	
	public function more_hot_topics() {
		return 'More Hot Topics';
	}
	
	public function hot_topics() {
		return 'Hot Topics';
	}
	
	public function current_hottest_topic() {
		return 'Current Hottest Topic';
	}
	
	public function member_show() {
		return 'Member Show';
	}
	
	public function member_list() {
		return 'Member List';
	}
	
	public function member_count($count) {
		return $count . ' members total';
	}
	
	public function create_new_topic() {
		return 'Create New Topic';
	}
	
	public function create_new_topic_in($title) {
		return '<small>Create New Topic in ' . $title . '</small>';
	}
	
	public function favorite_this_topic() {
		return 'Favorite This Topic';
	}
	
	public function be_the_first_one_to_reply() {
		return 'No reply yet. Be the first one to reply?';
	}
	
	public function wanna_say_something() {
		return 'Wanna say something?';
	}
	
	public function you_can_only_answer_your_own() {
		return 'You can only reply to your own topics in this *autistic* node.';
	}

	public function you_cannot_reply_autistic() {
		return 'You cannot reply to other\'s topics in *autistic* node.';
	}	

	public function this_is_an_autistic_node() {
		return 'This is an autistic node, you may only reply to your own topics.';
	}
	
	public function who_adds_me() {
		return '<small>Who adds me as friend?</small>';
	}
	
	public function login_before_reply() {
		return 'Please sign in before you reply to the topic';
	}
	
	public function please_check() {
		return 'Sorry, please check your input, something needs to be corrected';
	}

	public function new_topic() {
		return 'New Topic';
	}
	
	public function go_to_top() {
		return 'Go to Top';
	}
	
	public function switch_language() {
		return 'Switch Language';
	}

	public function no_reply_yet() {
		return 'No reply yet';
	}

	public function member_num($num) {
		return '#<strong>' . $num . '</strong> Member of ' . Vocabulary::site_name;
	}
	
	public function one_s_savepoints($user) {
		return "{$user}'s Savepoints";
	}

	public function one_s_friends($user) {
		return "{$user}'s Friends";
	}
	
	public function one_s_recent_topics($user) {
		return "{$user}'s Recent Topics";
	}
	
	public function one_s_recent_discussions($user) {
		return "{$user}'s Recent Discussions";
	}
	
	public function title() {
		return 'Title';
	}
	
	public function description() {
		return 'Description';
	}
	
	public function content() {
		return 'Content';
	}

	public function publish() {
		return 'Publish';
	}
	
	public function return_to_discussion_board() {
		return 'Return to Discussion Board';
	}
	
	public function board_stats_topics($count) {
		return '<small>This discussion has got <strong>' . $count . '</strong> topics</small>';
	}
	
	public function board_stats_favs($count, $name) {
		return '<small><a href="/who/fav/node/' . $name . '" class="regular"><strong>' . $count . '</strong></a> people\'s favorite</small>';
	}
	
	public function board_stats_favs_zero() {
		return '<small>No one favorite this</small>';
	}
	
	public function remix_mode() {
		return 'REMIX Mode';
	}
}
?>
