<?php
class lang implements Language {
	public function lang() {
		return 'Deutsch';
	}
	
	public function login() {
		return 'Einloggen';
	}
	
	public function signed_in($site) {
		return $site . ' Angemeldet';
	}
	
	public function logout() {
		return 'Abmelden';
	}
	
	public function now_auto_redirecting($site) {
		return '<small>Jetzt automatisch zur <a href="/">' . $site . '</a> Startseite umzuleiten，oder du kannst hier <a href="/">Klicken</a> manuell umzuleiten</small>';
	}
	
	public function sign_in_again() {
		return 'Wieder anzumelden';
	}
	
	public function you_have_signed_out($site) {
		return 'Du hast schon vom ' . $site . ' abgemeldet';
	}
	
	public function privacy_ok() {
		return '<small>Keine persöliche Informationen werden jetzt in diesem Rechner gespeichert.</small>';
	}
	
	public function welcome_back_anytime() {
		return 'wir freuen uns darauf, dass Sie wiederkommen!';
	}
	
	public function return_home($site) {
		return 'zur ' . $site . ' Startseite';
	}
	
	public function shuffle_home() {
		return 'Shuffle Front Page';
	}
	
	public function remix_home() {
		return 'Remix Front Page';
	}
	
	public function home($site) {
		return $site . ' Startseite';
	}
	
	public function help() {
		return 'Hilfe';
	}
	
	public function new_features() {
		return 'Neue Funktionen';
	}
	
	public function user_id() {
		return 'Benutzername';
	}
	
	public function password() {
		return 'Passwort';
	}
	
	public function email_or_nick() {
		return 'E-mail or Nickname';
	}
	
	public function take_a_tour() {
		return 'Gast';
	}
	
	public function about($site) {
		return 'Über ' . $site;
	}
	
	public function rss() {
		return 'RSS';
	}
	
	public function copper($i) {
		return '<small>' . $i . '</small> Münze';
	}
	
	public function register() {
		return 'Anmelden';
	}
	
	public function search() {
		return 'Suchen';
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
		return 'Einstellungen';
	}
	
	public function set_location() {
		return 'Set Location';
	}
	
	public function password_recovery() {
		return 'Passwort vergessen?';
	}
	
	public function timtowtdi() {
		return "Es gibt viele Wege um das zu machen";
	}
	
	public function reply() {
		return 'Antworten';
	}
	
	public function login_and_reply() {
		return 'Anmelden um zu antworten';
	}
	
	public function switch_description() {
		return 'Schaltet zur Beschreibungsmode um';
	}
	
	public function jump_to_replies() {
		return 'Zur Antworten';
	}
	
	public function hits($i) {
		return $i . ' Treffer';
	}
	
	public function posts($i) {
		return $i . ' Antworten';
	}
	
	public function expenses() {
		return 'Verbraucherauflistung';
	}
	
	public function my_profile($site) {
		return 'Meine ' . $site . ' Homepage';
	}
	
	public function my_topics() {
		return 'Meine Themen';
	}
	
	public function my_blogs() {
		return 'Meine Blogseinträge';
	}
	
	public function my_messages() {
		return 'Meine Nachrichten';
	}
	
	public function my_friends() {
		return 'Meine Freunde';
	}
	
	public function my_favorites() {
		return 'Meine Sammlung';
	}
	
	public function send_money() {
		return 'Überweisen';
	}
	
	public function top_wealth() {
		return 'Vermögensrank';
	}
	
	public function top_topics() {
		return 'Themensrank';
	}
	
	public function latest_topics() {
		return 'Neueste Themen';
	}
	
	public function latest_replied() {
		return 'Latest Replied';
	}
	
	public function latest_unanswered() {
		return 'Latest Unanswered';
	}
	
	public function latest_members() {
		return 'Neuste Benutzer';
	}
	
	public function join_discussion() {
		return 'Kommentieren mit';
	}
	
	public function browse_node($name, $title) {
		return 'kategorie <a href="/go/' . urlencode($name) . '" class="regular">' . make_plaintext($title) . '</a>';
	}
	
	public function more_hot_topics() {
		return 'Heißeste Themen besuchen';
	}
	
	public function hot_topics() {
		return 'Hot Topics'; // TODO
	}
	
	public function member_show() {
		return 'Member Show';
	}
	
	public function create_new_topic() {
		return 'Neues Thema anlegen';
	}
	
	public function create_new_topic_in($title) {
		return '<small>Create New Topic in ' . $title . '</small>';
	}
	
	public function favorite_this_topic() {
		return 'Das Thema sammeln';
	}
	
	public function be_the_first_one_to_reply() {
		return 'Momentan wird das Thema noch nicht antwortet. Vielleicht möchten Sie es tun?';
	}
	
	public function who_adds_me() {
		return '<small>wer hat mich in die Freundliste hinzugefügt?</small>';
	}
}
?>
