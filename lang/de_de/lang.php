<?php
class lang implements Language {
	public function lang() {
		return 'Deutsch';
	}
	
	public function login() {
		return 'Einloggen';
	}
	
	public function signed_in($site) {
		return 'Du hast dich auf ' . $site . ' eingeloggt';
	}
	
	public function logout() {
		return 'Ausloggen';
	}
	
	public function now_auto_redirecting($site) {
		return '<small>Du wirst nun automatisch auf <a href="/">' . $site . '</a> weitergeleitet. <a href="/">Klicke hier</a> um manuell zur Homepage zu gelangen.</small>';
	}
	
	public function sign_in_again() {
		return 'Wieder Einloggen';
	}
	
	public function you_have_signed_out($site) {
		return 'Du hast dich von ' . $site . ' abgemeldet';
	}
	
	public function privacy_ok() {
		return '<small>Alle Cookies wurden gelöscht.</small>';
	}
	
	public function welcome_back_anytime() {
		return 'Komm wieder, wann immer du magst!';
	}
	
	public function return_home($site) {
		return 'Zurück zu Home';
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
		return 'Hilfe';
	}
	
	public function new_features() {
		return 'Neue Features';
	}
	
	public function user_id() {
		return 'User ID';
	}
	
	public function user_fullname() {
		return 'Voller Name';
	}
	
	public function user_introduction() {
		return 'Vorstellung';
	}
	
	public function email() {
		return 'E-mail';
	}
	
	public function registered_email() {
		return 'Registrierte E-mail';
	}
	
	public function password() {
		return 'Passwort';
	}
	
	public function password_again() {
		return 'Passwort (Nochmal)';
	}
	
	public function new_password() {
		return 'Neues Passwort';
	}
	
	public function new_password_again() {
		return 'Neues Passwort (Nochmal)';
	}
	
	public function change_password_tips() {
		return "Falls du dein Passwort nicht ändern möchtest, lass die Eingabefelder leer.";
	}
	
	public function gender() {
		return 'Geschlecht';
	}
	
	public function gender_categories() {
		return array(0 => 'Unbekannt', 1 => 'Männlich', 2 => 'Weiblich', 5 => 'Frau -> Mann', 6 => 'Mann -> Frau', 9 => 'Keine Angabe');
	}
	
	public function religion() {
		return 'Religion';
	}
	
	public function religion_categories() {
		return array();
	}
	
	public function publicise_my_religion() {
		return 'Veröffentliche Meine Religion';
	}
	
	public function preferred_screen_width() {
		return 'Bevorzugte Bildschirmbreite';
	}
	
	public function confirmation_code() {
		return 'Bestätigungscode';
	}
	
	public function confirmation_code_tips() {
		return '<li>Bitte gib ein, was du liest</li><li>Groß- und Kleinschreibung beachten</li><li>Keine Zahlen</li><li>Nur für Menschen</li>';
	}
	
	public function email_or_nick() {
		return 'E-mail oder Nickname';
	}
	
	public function register_agreement() {
		return 'Durch das Anklicken von "Anmelden", stimmst du unseren <a href="/terms.vx" class="regular">Nutzungsbedingungen</a>, <a href="/privacy.vx" class="regular">Datenschutzrichtlinien</a> und <a href="/community_guidelines.vx" class="regular">Communityregeln</a> zu.';
	}
	
	public function take_a_tour() {
		return 'Mach eine Tour';
	}
	
	public function about($site) {
		return 'Über ' . $site;
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
		return 'Anmelden';
	}
	
	public function search() {
		return 'Suchen';
	}
	
	public function ref_search() {
		return 'Suche Ähnliches';
	}
	
	public function tools() {
		return 'Tools';
	}
	
	public function go_on() { // Because "continue" is a reserved keyword.
		return 'Weiter';
	}
	
	public function members_total() {
		return 'Mitglieder Insgesamt';
	}
	
	public function discussions() {
		return 'Diskussionen';
	}
	
	public function favorites() {
		return 'Favoriten';
	}
	
	public function savepoints() {
		return 'Speicherpunkte';
	}
	
	public function ing_updates() {
		return 'Ing Updates';
	}
	
	public function weblogs() {
		return 'Weblogs';
	}
	
	public function online_total() {
		return 'Online Insgesamt';
	}
	
	public function online_now() {
		return 'Online Jetzt';
	}
	
	public function online_details($onl_created, $onl_lastmoved) {
		return 'angemeldet seit ' . make_descriptive_time($onl_created) . ', zuletzt bewegt um ' . make_descriptive_time($onl_lastmoved);
	}
	
	public function disconnected() {
		return 'Offline';
	}
	
	public function anonymous() {
		return 'Anonym';
	}
	
	public function registered() {
		return 'Registriert';
	}
	
	public function system_status() {
		return 'System Status';
	}
	
	public function online_count($i) {
		return $i . ' Online';
	}
	
	public function session_count($i) {
		return '' . $i . ' Seiten besucht';
	}
	
	public function login_history() {
		return 'Login Verlauf';
	}
	
	public function upload_portrait() {
		return 'Uploade ein Portrait';
	}
	
	public function upload_portrait_tips() {
		return 'Für einen besseren Effekt wähle bitte ein Bild, dass größer ist als 100x100 Pixel, JPG/GIF/PNG werden unterstützt';
	}
	
	public function current_portrait() {
		return 'Aktuelles Portrait';
	}
	
	public function choose_a_picture() {
		return 'Wähle ein Bild';
	}
	
	public function settings() {
		return 'Einstellungen';
	}
	
	public function set_location() {
		return 'Umgebung festlegen';
	}
	
	public function password_recovery() {
		return 'Passwort Vergessen?';
	}
	
	public function password_recovery_tips() {
		return 'Gib bitte die E-mail Adresse an, mit der du dich registriert hast um dein Passwort zugeschickt zu bekommen. Falls die Adresse nicht in unserem System ist, erhältst du eine Mail mit weiteren Anweisungen, klicke auf den Link in der Mail um ein neues Passwort festzulegen.';
	}
	
	public function timtowtdi() {
		return "Es gibt mehr als einen Weg";
	}
	
	public function reply() {
		return 'Antworten';
	}
	
	public function login_and_reply() {
		return 'Einloggen und Antworten';
	}
	
	public function switch_description() {
		return 'Beschreibung wechseln';
	}
	
	public function jump_to_replies() {
		return 'Zu den Antworten';
	}
	
	public function hits($i) {
		return $i . ' Aufrufe';
	}
	
	public function posts($i) {
		return $i . ' Antworten';
	}
	
	public function me() {
		return 'Ich';
	}
	
	public function topic_creator() {
		return 'Themenstarter';
	}
	
	public function expenses() {
		return 'Kosten';
	}
	
	public function my_profile($site) {
		return 'Mein Profil';
	}
	
	public function my_topics() {
		return 'Meine Themen';
	}
	
	public function my_blogs() {
		return 'Meine Weblogs';
	}
	
	public function my_messages() {
		return 'Meine Nachrichten';
	}
	
	public function my_friends() {
		return 'Meine Freunde';
	}
	
	public function my_favorites() {
		return 'Meine Favoriten';
	}
	
	public function my_inventory() {
		return 'Mein Inventar';
	}
	
	public function send_money() {
		return 'Geld verschicken';
	}
	
	public function top_wealth() {
		return 'Reichste User';
	}
	
	public function top_topics() {
		return 'Topthemen';
	}
	
	public function hottest_topics() {
		return 'Heißeste Themen';
	}
	
	public function hottest_discussion_boards() {
		return 'Heißeste Diskussionsforen';
	}
	
	public function random_discussion_boards() {
		return 'Zufällige Diskussionsforen';
	}
	
	public function latest_topics() {
		return 'Neueste Themen';
	}
	
	public function latest_replied() {
		return 'Neueste Antwort';
	}
	
	public function latest_unanswered() {
		return 'Unbeantwortete Themen';
	}
	
	public function latest_members() {
		return 'Neuestes Mitglied';
	}
	
	public function latest_favorites() {
		return 'Neueste Favoriten';
	}
	
	public function join_discussion() {
		return 'Diskussion beitreten';
	}
	
	public function browse_node($name, $title) {
		return 'Browse <a href="/go/' . urlencode($name) . '" class="regular">' . make_plaintext($title) . '</a>';
	}
	
	public function more_favorites() {
		return 'Weitere Favoriten';
	}
	
	public function more_updates() {
		return 'Weitere Updates';
	}
	
	public function more_hot_topics() {
		return 'Weitere heiße Themen';
	}
	
	public function hot_topics() {
		return 'Heiße Themen';
	}
	
	public function current_hottest_topic() {
		return 'Aktuell heißestes Thema';
	}
	
	public function member_show() {
		return 'Mitgliederanzeige';
	}
	
	public function member_list() {
		return 'Mitgliederliste';
	}
	
	public function member_count($count) {
		return $count . ' Mitglieder Gesamt';
	}
	
	public function create_new_topic() {
		return 'Erstelle ein neues Thema';
	}
	
	public function create_new_topic_in($title) {
		return '<small>Neues Thema in ' . $title . '</small>';
	}
	
	public function favorite_this_topic() {
		return 'Füge dieses Thema zu meinen Favoriten hinzu';
	}
	
	public function be_the_first_one_to_reply() {
		return 'Noch keine Antworten. Möchtest du vielleicht eine schreiben?';
	}
	
	public function wanna_say_something() {
		return 'Willst du etwas sagen?';
	}
	
	public function you_can_only_answer_your_own() {
		return 'Im *autistischer* Node kannst du nur in deinen eigenen Themen antworten.';
	}

	public function you_cannot_reply_autistic() {
		return 'Im *autistischer* Node kannst du nicht auf die Themen von anderen antworten.';
	}	

	public function this_is_an_autistic_node() {
		return 'Dies ist ein autistischer Node, du kannst nur in deinen eigenen Themen antworten.';
	}
	
	public function who_adds_me() {
		return '<small>Wer hat mich als Freund hinzugefügt?</small>';
	}
	
	public function login_before_reply() {
		return 'Bitte logge dich ein bevor du antwortest';
	}
	
	public function please_check() {
		return 'Bitte überprüfe deine Eingabe, es liegt ein Fehler vor';
	}

	public function new_topic() {
		return 'Neues Thema';
	}
	
	public function go_to_top() {
		return 'Nach oben';
	}
	
	public function switch_language() {
		return 'Sprache ändern';
	}

	public function no_reply_yet() {
		return 'Noch keine antwort';
	}

	public function member_num($num) {
		return '#<strong>' . $num . '</strong> Mitglied von ' . Vocabulary::site_name;
	}
	
	public function one_s_savepoints($user) {
		return "{$user}'s Speicherpunkte";
	}

	public function one_s_friends($user) {
		return "{$user}'s Freunde";
	}
	
	public function one_s_recent_topics($user) {
		return "{$user}'s aktuelle Themen";
	}
	
	public function one_s_recent_discussions($user) {
		return "{$user}'s aktuelle Diskussionen";
	}
	
	public function one_s_most_favorite_artists($user) {
		return "{$user}'s Lieblingskünstler";
	}
	
	public function one_s_recent_listened_tracks($user) {
		return "{$user}'s zuletztgehörte Songs";
	}
	
	public function title() {
		return 'Titel';
	}
	
	public function category() {
		return 'Kategorie';
	}
	
	public function description() {
		return 'Beschreibung';
	}
	
	public function content() {
		return 'Inhalt';
	}

	public function publish() {
		return 'Veröffentlichen';
	}
	
	public function upload() {
		return 'Upload';
	}
	
	public function modify() {
		return 'Editieren';
	}
	
	public function return_to_discussion_board() {
		return 'Zurück zum Diskussionsforum';
	}
	
	public function return_to_section() {
		return 'Zurück zur Sektion';
	}
	
	public function board_stats_topics($count) {
		return '<small>Diese Diskussion enthält <strong>' . $count . '</strong> Themen</small>';
	}
	
	public function board_stats_favs($count, $name) {
		return '<small><a href="/who/fav/node/' . $name . '" class="regular"><strong>' . $count . '</strong></a> mal favorisiert</small>';
	}
	
	public function board_stats_favs_zero() {
		return '<small>Niemand favorisiert dies</small>';
	}
	
	public function remix_mode() {
		return 'REMIX Modus';
	}
	
	public function related_sites() {
		return 'Ähnliche Seiten';
	}
	
	public function related_favs() {
		return 'Ähnliche Favoriten';
	}
	
	public function no_related_channel() {
		return '<small>kein ähnlicher Kanal</small>';
	}
	
	public function update() {
		return 'Update';
	}
	
	public function last_signed_in() {
		return 'Zuletzt eingeloggt am';
	}
	
	public function logins($count) {
		return "{$count} logins";
	}
	
	public function location() {
		return 'Umgebung';
	}
	
	public function current_location() {
		return 'Aktuelle Umgebung';
	}
	
	public function people_in_the_same_area() {
		return 'Leute in meiner Gegend';
	}
	
	public function personal_information_and_preferences() {
		return 'Persönliche Informationen und Einstellungen';
	}
	
	public function set_location_tips() {
		return 'Deine Standardumgebung ist die Erde';
	}
	
	public function blog_compose() {
		return 'Verfassen';
	}
	
	public function blog_create() {
		return 'Erstelle einen neuen Weblog';
	}
	
	public function blog_manage_articles() {
		return 'Artikel';
	}
	
	public function blog_manage_links() {
		return 'Links';
	}
	
	public function blog_rebuild() {
		return 'Neuaufbauen';
	}
	
	public function blog_destroy() {
		return 'Löschen';
	}
	
	public function blog_icon() {
		return 'Icon';
	}
	
	public function blog_theme() {
		return 'Thema';
	}
	
	public function blog_settings() {
		return 'Einstellungen';
	}
	
	public function blog_view() {
		return 'Ansehen';
	}
	
	public function blog_format() {
		return 'Format';
	}
	
	public function blog_comment_permission() {
		return 'Kommentarerlaubnis';
	}
	
	public function shop() {
		return 'Shop';
	}
	
	public function top_wealth_ranking() {
		return 'Meistes Geld Ranking';
	}
	
	public function shuffle_cloud() {
		return 'Wolke Mischen';
	}
	
	public function sidebar_friends() {
		return 'Freundesliste Sidebar';
	}
	
	public function v2ex_shell() {
		return 'V2EX Shell';
	}
	
	public function notify_mine() {
		return 'Benachrichtige mich bei Antworten zu meinen eigenen';
	}
	
	public function notify_all() {
		return 'Benachrichtige mich bei Antworten zu diesem Thema';
	}
	
	public function notify_email() {
		return 'Sende Benachrichtung via E-mail';
	}
	
	public function publicise() {
		return 'Veröffentlichen';
	}
	
	public function not_to_publicise() {
		return 'Nicht Veröffentlichen';
	}
	
	public function publicise_to_same_religion() {
		return 'Für selbe Religion veröffentlichen';
	}
	
	public function on() {
		return 'An';
	}
	
	public function off() {
		return 'Aus';
	}
	
	public function participate() {
		return 'Teilnehmen';
	}
}
?>