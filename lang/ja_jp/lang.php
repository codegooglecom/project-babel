<?php
class lang implements Language {
	public function lang() {
		return '日本語';
	}
	
	public function login() {
		return 'ログイン';
	}
	
	public function signed_in($site) {
		return $site . ' ログイン成功';
	}
	
	public function logout() {
		return 'ログアウト';
	}
	
	public function now_auto_redirecting($site) {
		return '<a href="/">' . $site . '</a> ホームに自動転送されますが、また <a href="/">クリックして</a> 転送します';
	}
	
	public function sign_in_again() {
		return 'もう一度ログインする';
	}
	
	public function you_have_signed_out($site) {
		return $site . 'からログアウトされました';
	}
	
	public function privacy_ok() {
		return '現在お使いになる端末には個人情報など保存されていません。';
	}
	
	public function welcome_back_anytime() {
		return 'またお越しください！';
	}
	
	public function return_home($site) {
		return $site . ' ホームに移動';
	}
	
	public function shuffle_home() {
		return 'Shuffle Front Page';
	}
	
	public function remix_home() {
		return 'Remix Front Page';
	}
	
	public function home($site) {
		return $site . ' ホーム';
	}
	
	public function help() {
		return 'ヘルプ';
	}
	
	public function new_features() {
		return '新機能';
	}
	
	public function user_id() {
		return 'ユーザ名';
	}
	
	public function password() {
		return 'パスワード';
	}
	
	public function email_or_nick() {
		return '電子メールかニックネーム';
	}
	
	public function take_a_tour() {
		return '匿名ユーザ';
	}
	
	public function about($site) {
		return 'プレス ' . $site;
	}
	
	public function rss() {
		return 'RSS';
	}
	
	public function copper($i) {
		return '<small>' . $i . '</small> 銅';
	}
	
	public function register() {
		return '登録';
	}
	
	public function search() {
		return '検索';
	}
	
	public function ref_search() {
		return 'Reference Search';
	}
	
	public function tools() {
		return 'Tools';
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
	
	public function Savepoints() {
		return 'Savepoints';
	}
	
	public function ing_updates() {
		return 'Ing Updates';
	}
	
	public function weblogs() {
		return 'Weblogs';
	}
	
	public function online_total() {
		return 'オンラインの総計';
	}
	
	public function anonymous() {
		return '匿名';
	}
	
	public function registered() {
		return '会員登録する';
	}
	
	public function system_status() {
		return 'システムの状態';
	}
	
	public function online_count($i) {
		return $i . ' 人オンライン';
	}
	
	public function session_count($i) {
		return '' . $i . ' Pages Visted';
	}
	
	public function login_history() {
		return 'Login History';
	}
	
	public function upload_portrait() {
		return '肖像アップロード';
	}
	
	public function settings() {
		return 'プロフィル更新';
	}
	
	public function set_location() {
		return 'Set Location';
	}
	
	public function password_recovery() {
		return 'パスワード再設定';
	}
	
	public function timtowtdi() {
		return "There's more than one way to do it";
	}
	
	public function reply() {
		return 'コメントする';
	}
	
	public function login_and_reply() {
		return 'ログインしてからコメントする';
	}
	
	public function switch_description() {
		return 'ディスプレイモデル変更';
	}
	
	public function jump_to_replies() {
		return 'コメントに移動';
	}
	
	public function hits($i) {
		return $i . ' 回クリックした';
	}
	
	public function posts($i) {
		return $i . ' のレス';
	}
	
	public function me() {
		return '自分';
	}
	
	public function topic_creator() {
		return '記事の所有者';
	}
	
	public function expenses() {
		return '消費記録';
	}
	
	public function my_profile($site) {
		return '私のホームページ ' . $site;
	}
	
	public function my_topics() {
		return '私が作成したトピック';
	}
	
	public function my_blogs() {
		return '私のブログ';
	}
	
	public function my_messages() {
		return '私のメール';
	}
	
	public function my_friends() {
		return '私の友人';
	}
	
	public function my_favorites() {
		return '私のお気に入り';
	}
	
	public function send_money() {
		return '振込';
	}
	
	public function top_wealth() {
		return '財産ランキング';
	}
	
	public function top_topics() {
		return 'トピックランキング';
	}
	
	public function latest_topics() {
		return '最新トピック';
	}
	
	public function latest_replied() {
		return 'Latest Replied';
	}
	
	public function latest_unanswered() {
		return '最新テーマに返答していません';
	}
	
	public function latest_members() {
		return '新規登録会員';
	}
	
	public function join_discussion() {
		return 'トピックに参加';
	}
	
	public function browse_node($name, $title) {
		return 'カテゴリをみる <a href="/go/' . urlencode($name) . '" class="regular">' . make_plaintext($title) . '</a>';
	}
	
	public function more_hot_topics() {
		return '人気トピックをみる';
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
	
	public function create_new_topic() {
		return 'トピックを新規作成';
	}
	
	public function create_new_topic_in($title) {
		return '<small>Create New Topic in ' . $title . '</small>';
	}
	
	public function favorite_this_topic() {
		return 'このトピックをお気に入りに登録する';
	}
	
	public function be_the_first_one_to_reply() {
		return 'またレスがありませんが、コメントしてあげましょう？';
	}
	
	public function wanna_say_something() {
		return 'この記事を読んだ後何か言いたい事がありますか。それならこの記事に投稿してください。';
	}
	
	public function you_can_only_answer_your_own() {
		return 'この掲示板は自閉的なモードです。あなたは自分で投稿した記事に投稿することしかできません。';
	}
	public function this_is_an_autistic_node() {
		return 'この掲示板は自閉的なモードです。あなたはすべての記事に投稿することができません。';
	}
	
	public function who_adds_me() {
		return '誰に友人リストに登録された？';
	}
	
	public function login_before_reply() {
		return '返答の前にあなたは登録をまず進めなければなりません';
	}
}
?>
