<?php
class lang implements Language {
	
	public function login() {
		return '登录';
	}
	
	public function logout() {
		return '登出';
	}
	
	public function copper($i) {
		return '<small>' . $i . '</small> 铜币';
	}
	
	public function register() {
		return '注册';
	}
	
	public function settings() {
		return '修改信息与设置';
	}
	
	public function password_recovery() {
		return '找回密码';
	}
	
	public function new_features() {
		return '新功能';
	}
	
	public function timtowtdi() {
		return "There's more than one way to do it";
	}
	
	public function reply() {
		return '回复主题';
	}
	
	public function login_and_reply() {
		return '登录后回复主题';
	}
	
	public function switch_description() {
		return '切换简介显示';
	}
	
	public function jump_to_replies() {
		return '跳到回复';
	}
	
	public function hits($i) {
		return $i . ' 次点击';
	}
	
	public function my_profile($site_name) {
		return '我的 ' . $site_name . ' 主页';
	}
	
	public function my_topics() {
		return '我创建的所有主题';
	}
	
	public function my_blogs() {
		return '我的博客网志';
	}
	
	public function send_money() {
		return '转账';
	}
}

?>
