<?php
class lang implements Language {
	public function lang() {
		return '简体中文';
	}
	
	public function login() {
		return '登录';
	}
	
	public function signed_in($site) {
		return $site . ' 登录成功';
	}
	
	public function logout() {
		return '登出';
	}
	
	public function now_auto_redirecting($site) {
		return '正在自动跳转到 <a href="/">' . $site . '</a> 首页，或者你可以 <a href="/">点击这里</a> 进行手动跳转';
	}
	
	public function sign_in_again() {
		return '重新登录';
	}
	
	public function you_have_signed_out($site) {
		return '你已经从 ' . $site . ' 登出';
	}
	
	public function privacy_ok() {
		return '没有任何个人信息被留在你现在使用过的计算机上，请对你的隐私放心。';
	}
	
	public function welcome_back_anytime() {
		return '欢迎随时回来！';
	}
	
	public function return_home($site) {
		return '返回 ' . $site . ' 首页';
	}
	
	public function shuffle_home() {
		return 'Shuffle 首页';
	}
	
	public function remix_home() {
		return 'Remix 首页';
	}
	
	public function home($site) {
		return $site . ' 首页';
	}
	
	public function help() {
		return '帮助';
	}
	
	public function new_features() {
		return '新功能介绍';
	}
	
	public function user_id() {
		return '用户名';
	}
	
	public function email() {
		return '电子邮件';
	}
	
	public function password() {
		return '密码';
	}
	
	public function password_again() {
		return '重复密码';
	}

	public function new_password() {
		return '新密码';
	}
	
	public function new_password_again() {
		return '重复新密码';
	}

	public function gender() {
		return '性别';
	}
	
	public function gender_categories() {
		return array(0 => '未知', 1 => '男性', 2 => '女性', 5 => '女性改（变）为男性', 6 => '男性改（变）为女性', 9 => '未说明');
	}
	
	public function confirmation_code() {
		return '确认码';
	}
	
	public function confirmation_code_tips() {
		return '<li>请按照上图输入确认码</li><li>确认码不区分大小写</li><li>确认码中不包含数字</li><li>专为人类设计</li>';
	}
	
	public function email_or_nick() {
		return '电子邮件或昵称';
	}
	
	public function register_agreement() {
		return '点击“注册新会员”，即表示你完全同意我们的 <a href="/terms.vx" class="regular">Terms of Use</a> 和 <a href="/privacy.vx" class="regular">Privacy Policy</a>，并且你不厌恶也不会反对我们的 <a href="/communtiy_guidelines.vx" class="regular">Community Guidelines</a>。';
	}
	
	public function take_a_tour() {
		return '游客';
	}
	
	public function about($site) {
		return '关于 ' . $site;
	}
	
	public function rss() {
		return 'RSS';
	}
	
	public function copper($i) {
		return '<small>' . $i . '</small> 铜币';
	}
	
	public function register() {
		return '注册';
	}
	
	public function search() {
		return '搜索';
	}
	
	public function ref_search() {
		return '参考文档搜索';
	}
	
	public function tools() {
		return '工具';
	}
	
	public function go_on() {
		return '继续';
	}
	
	public function members_total() {
		return '注册会员总数';
	}
	
	public function discussions() {
		return '讨论';
	}
	
	public function favorites() {
		return '收藏';
	}
	
	public function savepoints() {
		return '据点';
	}
	
	public function ing_updates() {
		return '印迹';
	}
	
	public function weblogs() {
		return '博客';
	}
	
	public function online_total() {
		return '在线会员总数';
	}
	
	public function online_now() {
		return '当前在线';
	}
	
	public function online_details() {
		return '';
	}
	
	public function disconnected() {
		return '当前不在线';
	}
	
	public function anonymous() {
		return '游客';
	}
	
	public function registered() {
		return '会员';
	}
	
	public function system_status() {
		return '系统状态';
	}
	
	public function online_count($i) {
		return $i . ' 人在线';
	}
	
	public function session_count($i) {
		return '本次访问了 <small>' . $i . '</small> 页';
	}
	
	public function login_history() {
		return '会员登录历史';
	}
	
	public function upload_portrait() {
		return '上传头像';
	}
	
	public function settings() {
		return '修改信息与设置';
	}
	
	public function set_location() {
		return '设置我的所在地';
	}
	
	public function password_recovery() {
		return '找回密码';
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
	
	public function posts($i) {
		return $i . ' 篇回复';
	}
	
	public function me() {
		return '我';
	}
	
	public function topic_creator() {
		return '楼主';
	}

	public function expenses() {
		return '消费记录';
	}	
	
	public function my_profile($site) {
		return '我的 ' . $site . ' 主页';
	}
	
	public function my_topics() {
		return '我创建的所有主题';
	}
	
	public function my_blogs() {
		return '我的博客网志';
	}
	
	public function my_messages() {
		return '我的消息';
	}
	
	public function my_friends() {
		return '我的朋友';
	}
	
	public function my_favorites() {
		return '我的收藏夹';
	}
	
	public function send_money() {
		return '汇款';
	}
	
	public function top_wealth() {
		return '社区财富排行';
	}
	
	public function top_topics() {
		return '最强主题排行';
	}
	
	public function latest_topics() {
		return '最新主题';
	}
	
	public function latest_replied() {
		return '最新被回复主题';
	}
	
	public function latest_unanswered() {
		return '最新无回复主题';
	}
	
	public function latest_members() {
		return '最新注册会员';
	}
	
	public function join_discussion() {
		return '参与讨论';
	}
	
	public function browse_node($name, $title) {
		return '浏览讨论区 <a href="/go/' . urlencode($name) . '" class="regular">' . make_plaintext($title) . '</a>';
	}
	
	public function more_hot_topics() {
		return '浏览更多最热主题';
	}
	
	public function hot_topics() {
		return '热门主题';
	}
	
	public function current_hottest_topic() {
		return '当前热门主题';
	}
	
	public function member_show() {
		return '会员头像展示';
	}
	
	public function member_list() {
		return '会员列表';
	}
	
	public function member_count($count) {
		return '共 ' . $count . ' 位注册会员';
	}
	
	public function create_new_topic() {
		return '创建新主题';
	}
	
	public function create_new_topic_in($title) {
		return '在' . $title . '创建新主题';
	}
	
	public function favorite_this_topic() {
		return '收藏本主题';
	}
	
	public function be_the_first_one_to_reply() {
		return '目前这个主题还没有回复，或许你可以帮楼主加盖一层？';
	}
	
	public function wanna_say_something() {
		return '看完之后有话想说？那就帮楼主加盖一层吧！';
	}
	
	public function you_can_only_answer_your_own() {
		return '这是一个自闭模式的讨论区，你可以且只能回复你自己创建的主题。';
	}
	public function this_is_an_autistic_node() {
		return '这是一个自闭模式的讨论区，你可能无法参与所有的主题。';
	}

	public function who_adds_me() {
		return '谁把我加为好友？';
	}

	public function you_cannot_reply_autistic() {
		return '你不能回复自闭模式讨论区中别人创建的主题。';
	}
	
	public function login_before_reply() {
		return '在回复之前你需要先进行登录';
	}
	
	public function please_check() {
		return '对不起，请检查一下你刚才的输入，有些错误需要解决';
	}

	public function go_to_top() {
		return '回到顶部';
	}
	
	public function switch_language() {
		return '切换语言';
	}

	public function no_reply_yet() {
		return '本主题目前尚无回复';
	}

	public function member_num($num) {
		return Vocabulary::site_name . ' 的第 <strong>' . $num . '</strong> 号会员';
	}
	
	public function one_s_savepoints($user) {
		return "{$user} 的网上据点";
	}

	public function one_s_friends($user) {
		return "{$user} 的朋友们";
	}
	
	public function one_s_recent_topics($user) {
		return "{$user} 最近创建的主题";
	}

	public function one_s_recent_discussions($user) {
		return "{$user} 最近参与的讨论";
	}
	
	public function one_s_components($user) {
		return "{$user} 的成分分析";
	}
	
	
}
?>
