<?php
class lang implements Language {
	public function lang() {
		return '한국어';
	}
	
	public function login() {
		return '로그인';
	}
	
	public function signed_in($site) {
		return $site . ' 로그인 성공';
	}
	
	public function logout() {
		return '로그아웃';
	}
	
	public function now_auto_redirecting($site) {
		return '<a href="/">' . $site . '</a> 첫페이지로 이동중입니다, 또는 <a href="/">여기</a>를 클릭하여 이동할수도 있습니다';
	}
	
	public function sign_in_again() {
		return '다시 로그인하기';
	}
	
	public function you_have_signed_out($site) {
		return $site . ' 에서 로그아웃 하실수 있습니다';
	}
	
	public function privacy_ok() {
		return '모든 쿠키가 컴퓨터에서 삭제되였습니다.';
	}
	
	public function welcome_back_anytime() {
		return '다시 돌아오기를 환영합니다！';
	}
	
	public function return_home($site) {
		return $site . ' 첫페이지로 돌아가기';
	}
	
	public function shuffle_home() {
		return 'Shuffle Front Page';
	}
	
	public function remix_home() {
		return 'Remix Front Page';
	}
	
	public function home($site) {
		return $site . ' 첫페이지';
	}
	
	public function help() {
		return '도움';
	}
	
	public function new_features() {
		return '새 기능소개';
	}
	
	public function user_id() {
		return '이름';
	}
	
	public function password() {
		return '페스워드';
	}
	
	public function take_a_tour() {
		return '손님';
	}
	
	public function about($site) {
		return $site . ' 소개';
	}
	
	public function rss() {
		return 'RSS';
	}
	
	public function copper($i) {
		return '<small>' . $i . '</small> 동전';
	}
	
	public function register() {
		return '회원가입';
	}
	
	public function search() {
		return '검색';
	}
	
	public function ref_search() {
		return 'References Search';
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
		return 'Online Total';
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
		return '' . $i . ' Pages Visited';
	}
	
	public function login_history() {
		return 'Login History';
	}
	
	public function upload_portrait() {
		return 'Upload Portrait';
	}
	
	public function settings() {
		return '개인정보와 개인설정 변경';
	}
	
	public function set_location() {
		return 'Set Location';
	}
	
	public function password_recovery() {
		return '페스워드 다시설정';
	}

	public function timtowtdi() {
		return "There's more than one way to do it";
	}
	
	public function reply() {
		return '댓글달기';
	}
	
	public function login_and_reply() {
		return '로그인후 댓글달기';
	}
	
	public function switch_description() {
		return '소개 페이지로 이동';
	}
	
	public function jump_to_replies() {
		return '댓글로 이동';
	}
	
	public function hits($i) {
		return $i . ' 번 클릭';
	}
	
	public function posts($i) {
		return $i . ' 개 댓글';
	}

	public function expenses() {
		return '소비기록';
	}	
	
	public function my_profile($site) {
		return '나의 ' . $site . ' 페이지';
	}
	
	public function my_topics() {
		return '나의 포스트';
	}
	
	public function my_blogs() {
		return '나의 블로그';
	}
	
	public function my_messages() {
		return '나의 쪼지';
	}
	
	public function my_friends() {
		return '내친구';
	}
	
	public function my_favorites() {
		return '나의 저장함';
	}
	
	public function send_money() {
		return '송금';
	}
	
	public function top_wealth() {
		return '포럼부자';
	}
	
	public function top_topics() {
		return '최강포스트';
	}
	
	public function latest_topics() {
		return '최근 글';
	}
	
	public function latest_replied() {
		return 'Latest Replied';
	}
	
	public function latest_unanswered() {
		return 'Latest Unanswered';
	}
	
	public function latest_members() {
		return '최근 가입한 회원';
	}
	
	public function join_discussion() {
		return '토론에 참가하기';
	}
	
	public function browse_node($name, $title) {
		return '포럼 <a href="' . urlencode($name) . '" class="regular">' . make_plaintext($title) . '</a> 보기';
	}
	
	public function more_hot_topics() {
		return '더 많은 화제보기';
	}
	
	public function member_show() {
		return 'Member Show';
	}
	
	public function create_new_topic() {
		return '새글쓰기';
	}
	
	public function create_new_topic_in($title) {
		return '<small>Create New Topic in ' . $title . '</small>';
	}
	
	public function favorite_this_topic() {
		return '이 글 저장하기';
	}
	
	public function be_the_first_one_to_reply() {
		return '이 글에는 아직 댓글이없습니다,댓글 써주세요>.<';
	}
	
	public function who_adds_me() {
		return '누가 나를 친구로 하고 있는가?';
	}
}
?>
