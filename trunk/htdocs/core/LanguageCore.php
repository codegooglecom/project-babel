<?php
interface Language {
	public function lang();
	public function login();
	public function logout();
	public function copper($i);
	public function register();
	public function settings();
	public function password_recovery();
	public function new_features();
	public function timtowtdi();
	public function reply();
	public function login_and_reply();
	public function switch_description();
	public function jump_to_replies();
	public function hits($i);
	public function posts($i);
	public function expenses();
	public function my_profile($site);
	public function my_topics();
	public function my_blogs();
	public function my_messages();
	public function my_friends();
	public function send_money();
	public function join_discussion();
	public function browse_node($name, $title);
	public function more_hot_topics();
	public function create_new_topic();
	public function favorite_this_topic();
	public function my_favorites();
}
?>
