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
	
	public function user_fullname() {
		return '真实姓名';
	}
	
	public function user_introduction() {
		return '自我介绍';
	}
	
	public function email() {
		return '电子邮件';
	}
	
	public function registered_email() {
		return '注册邮箱';
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
	
	public function change_password_tips() {
		return '如果你不打算修改密码的话，就不要在密码框处填入任何信息';
	}

	public function gender() {
		return '性别';
	}
	
	public function gender_categories() {
		return array(0 => '未知', 1 => '男性', 2 => '女性', 5 => '女性改（变）为男性', 6 => '男性改（变）为女性', 9 => '未说明');
	}
	
	public function religion() {
		return '信仰';
	}
	
	public function religion_categories() {
		return array();
	}
	
	public function publicise_my_religion() {
		return '是否公开我的信仰';
	}
	
	public function preferred_screen_width() {
		return '常用屏幕宽度';
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
	
	public function online_details($onl_created, $onl_lastmoved) {
		return '于 ' . make_descriptive_time($onl_created) . '进入 ' . Vocabulary::site_name . '，最后活动时间是在 ' . make_descriptive_time($onl_lastmoved);
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
	
	public function upload_portrait_tips() {
		return '为了更好的效果推荐你选择一张尺寸大于 100 x 100 像素的图片，支持 GIF/PNG/JPG 格式';
	}
	
	public function current_portrait() {
		return '当前头像';
	}
	
	public function choose_a_picture() {
		return '选择一张图片';
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
	
	public function password_recovery_tips() {
		return '你可以通过输入注册时候填入的电子邮件地址来找回密码。如果你输入的电子邮件地址确实存在的话，我们将向其发送一封包含特殊指令的邮件，点击邮件中的地址将即可复位密码，在每 24 小时内，复位密码功能（包括发送邮件）只能使用 5 次。';
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
	
	public function my_inventory() {
		return '我的物品栏';
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
	
	public function hottest_topics() {
		return '最热主题';
	}
	
	public function hottest_discussion_boards() {
		return '最热讨论区';
	}
	
	public function random_discussion_boards() {
		return '随机讨论区';
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
	
	public function latest_favorites() {
		return '最新的被收藏的项目';
	}
	
	public function join_discussion() {
		return '参与讨论';
	}
	
	public function browse_node($name, $title) {
		return '浏览讨论区 <a href="/go/' . urlencode($name) . '" class="regular">' . make_plaintext($title) . '</a>';
	}
	
	public function more_favorites() {
		return '更多收藏';
	}
	
	public function more_updates() {
		return '浏览更多更新';
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
	
	public function new_topic() {
		return '创建新主题';
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
	
	public function one_s_most_favorite_artists($user) {
		return "{$user} 最喜欢的艺术家";
	}
	
	public function one_s_recent_listened_tracks($user) {
		return "{$user} 最近听过的音乐";
	}
	
	public function title() {
		return '标题';
	}
	
	public function category() {
		return '分类';
	}
	
	public function description() {
		return '简介';
	}
	
	public function content() {
		return '内容';
	}
	
	public function publish() {
		return '发布';
	}
	
	public function upload() {
		return '上传';
	}
	
	public function modify() {
		return '修改';
	}
	
	public function return_to_discussion_board() {
		return '返回讨论区';
	}
	
	public function return_to_section() {
		return '返回大区域';
	}
	
	public function board_stats_topics($count) {
		return '本讨论区共有 <strong>' . $count . '</strong> 个主题';
	}
	
	public function board_stats_favs($count, $name) {
		return '共有 <a href="/who/fav/node/' . $name . '" class="regular"><strong>' . $count . '</strong></a> 人收藏了本讨论区';
	}
	
	public function board_stats_favs_zero() {
		return '无人收藏此讨论区';
	}
	
	public function remix_mode() {
		return 'REMIX 模式';
	}
	
	public function related_sites() {
		return '相关网站';
	}
	
	public function related_favs() {
		return '相关收藏';
	}
	
	public function no_related_channel() {
		return '无相关频道';
	}
	
	public function update() {
		return '更新';
	}
	
	public function last_signed_in() {
		return '最近登录';
	}
	
	public function logins($count) {
		return "{$count} 次登录";
	}
	
	public function location() {
		return '所在地';
	}
	
	public function current_location() {
		return '当前所在地';
	}
	
	public function people_in_the_same_area() {
		return '在同一区域的人数';
	}
	
	public function personal_information_and_preferences() {
		return '个人信息与偏好设置';
	}
	
	public function set_location_tips() {
		return '如果你之前没有进行过任何设置，那么默认所在地就是地球';
	}
	
	public function blog_compose() {
		return '撰写新文章';
	}
	
	public function blog_create() {
		return '创建新的博客网站';
	}
	
	public function blog_manage_articles() {
		return '管理文章';
	}
	
	public function blog_manage_links() {
		return '管理链接';
	}
	
	public function blog_rebuild() {
		return '重新构建';
	}
	
	public function blog_destroy() {
		return '彻底关闭';
	}
	
	public function blog_icon() {
		return '图标';
	}
	
	public function blog_theme() {
		return '主题';
	}
	
	public function blog_settings() {
		return '设置';
	}
	
	public function blog_view() {
		return '查看';
	}
	
	public function blog_format() {
		return '格式';
	}
	
	public function blog_comment_permission() {
		return '评论权限';
	}
	
	public function shop() {
		return '商店';
	}
	
	public function top_wealth_ranking() {
		return '社区财富排行';
	}
	
	public function shuffle_cloud() {
		return 'Shuffle 首页上的云';
	}
	
	public function sidebar_friends() {
		return '右侧边栏的好友列表';
	}
	
	public function v2ex_shell() {
		return 'V2EX Shell';
	}
	
	public function notify_mine() {
		return '邮件通知我的主题的新回复';
	}
	
	public function notify_all() {
		return '邮件通知我参与过的主题的新回复';
	}
	
	public function notify_email() {
		return '用于接收通知的邮箱';
	}
	
	public function publicise() {
		return '公开';
	}
	
	public function not_to_publicise() {
		return '不公开';
	}
	
	public function publicise_to_same_religion() {
		return '只向同样信仰者公布';
	}
	
	public function on() {
		return '开启';
	}
	
	public function off() {
		return '关闭';
	}
	
	public function participate() {
		return '参加';
	}
}
?>