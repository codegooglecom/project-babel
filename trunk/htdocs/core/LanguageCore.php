<?php
interface Language {
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
	public function my_profile($site_name);
	public function my_topics();
	public function my_blogs();
	public function send_money();
}
?>
