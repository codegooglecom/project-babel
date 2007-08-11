<?php
class lang implements Language {
	public function lang() {
		return 'Polski';
	}
	
	public function login() {
		return 'Logowanie';
	}
	
	public function signed_in($site) {
		return 'Zostałeś zalogowany na stronie ' . $site;
	}
	
	public function logout() {
		return 'Wyloguj';
	}
	
	public function now_auto_redirecting($site) {
		return '<small>Za chwile zostaniesz przeniesiony na stronę główną portalu <a href="/">' . $site . '</a>. Możesz to również zrobić kilkając <a href="/">tutaj</a>.</small>';
	}
	
	public function sign_in_again() {
		return 'Zaloguj się ponownie';
	}
	
	public function you_have_signed_out($site) {
		return 'Zostałeś wylogowany ze strony ' . $site;
	}
	
	public function privacy_ok() {
		return '<small>Żadne informacje osobiste nie są teraz przetrzymywane na Twoim komputerze.</small>';
	}
	
	public function welcome_back_anytime() {
		return 'Wróć do nas szybko!';
	}
	
	public function return_home($site) {
		return 'Powrót na stronę główną';
	}
	
	public function shuffle_home() {
		return 'Shuffle Front Page';
	}
	
	public function remix_home() {
		return 'Remix Front Page';
	}
	
	public function home($site) {
		return 'Strona główna';
	}
	
	public function help() {
		return 'Pomoc';
	}
	
	public function new_features() {
		return 'Nowe możliwości';
	}
	
	public function user_id() {
		return 'ID';
	}
	
	public function email() {
		return 'E-mail';
	}
	
	public function password() {
		return 'Hasło';
	}
	
	public function password_again() {
		return 'Password Again';
	}
	
	public function email_or_nick() {
		return 'E-mail or Nickname';
	}
	
	public function take_a_tour() {
		return 'Powrót';
	}
	
	public function about($site) {
		return 'O ' . $site;
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
		return 'Rejestracja';
	}
	
	public function search() {
		return 'Szukaj';
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
		return '' . $i . ' Pages Visted';
	}
	
	
	
	public function login_history() {
		return 'Login History';
	}
	
	public function upload_portrait() {
		return 'Upload Portrait';
	}
	
	public function settings() {
		return 'Ustawienia';
	}
	
	public function set_location() {
		return 'Set Location';
	}
	
	public function password_recovery() {
		return 'Odzyskiwanie hasła';
	}
	
	public function timtowtdi() {
		return "Jest więcej niż jedna możliwość by to zrobić";
	}
	
	public function reply() {
		return 'Odpowiedz';
	}
	
	public function login_and_reply() {
		return 'Zaloguj się i odpowiedz';
	}
	
	public function switch_description() {
		return 'Zmień opis';
	}
	
	public function jump_to_replies() {
		return 'Skocz do odpowiedzi';
	}
	
	public function hits($i) {
		return $i . ' trafień';
	}
	
	public function posts($i) {
		return $i . ' odpowiedzi';
	}
	
	public function me() {
		return 'Me';
	}
	
	public function topic_creator() {
		return 'Topic Creator';
	}
	
	public function expenses() {
		return 'Koszty';
	}
	
	public function my_profile($site) {
		return 'Mój profil';
	}
	
	public function my_topics() {
		return 'Moje odpowiedzi';
	}
	
	public function my_blogs() {
		return 'Moje blogi';
	}
	
	public function my_messages() {
		return 'Moje wiadomości';
	}
	
	public function my_friends() {
		return 'Moi przyjaciele';
	}
	
	public function my_favorites() {
		return 'Moje ulubione';
	}
	
	public function send_money() {
		return 'Wesprzyj nas';
	}
	
	public function top_wealth() {
		return 'Najbogatsi';
	}
	
	public function top_topics() {
		return 'Najpopularniejsze tematy';
	}
	
	public function latest_topics() {
		return 'Ostatnie tematy';
	}
	
	public function latest_replied() {
		return 'Latest Replied';
	}
	
	public function latest_unanswered() {
		return 'Latest Unanswered';
	}
	
	public function latest_members() {
		return 'Ostatnio zarejestrowani';
	}
	
	public function join_discussion() {
		return 'Przyłącz się do dyskusji';
	}
	
	public function browse_node($name, $title) {
		return 'Przeglądaj <a href="/go/' . urlencode($name) . '" class="regular">' . make_plaintext($title) . '</a>';
	}
	
	public function more_hot_topics() {
		return 'Więcej popularnych tematów';
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
		return 'Załóż nowy temat';
	}
	
	public function create_new_topic_in($title) {
		return '<small>Create New Topic in ' . $title . '</small>';
	}
	
	public function favorite_this_topic() {
		return 'Dodaj temat do ulubionych';
	}
	
	public function be_the_first_one_to_reply() {
		return 'Brak odpowiedzi. Chcesz być pierwszy?';
	}
	
	public function wanna_say_something() {
		return 'Wanna say something?';
	}
	
	public function you_can_only_answer_your_own() {
		return 'You can only reply to your own topics in this *autistic* node.';
	}
	public function this_is_an_autistic_node() {
		return 'This is an autistic node, you may only reply to your own topics.';
	}
	
	public function who_adds_me() {
		return '<small>Kto dodał mnie do przyjaciół?</small>';
	}
	
	public function login_before_reply() {
		return 'Please sign in first before you reply to the topic';
	}
}
?>
