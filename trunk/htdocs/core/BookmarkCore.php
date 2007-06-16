<?php
class Bookmark {
	public static function vxSync($User, $db, $posts) {
		$count = count($posts);
		return $count;
	}
	
	public static function vxParse($query) {
		/*
		 * input: url=http%3A%2F%2Fv-dev.v2ex.com%2F;title=V2EX%20%7C%20Project%20Babel
		 * output: array[2] {
		 		'url' => 'http://v-dev.v2ex.com/',
		 		'title' => 'V2EX | Project Babel'
		 	}
		 *
		 */
		$parameters = array();
		$sections = explode(';', $query);
		foreach ($sections as $section) {
			$pair = explode('=', $section);
			$parameters[$pair[0]] = urldecode($pair[1]);
		}
		return $parameters;
	}
	
	public static function vxValidate() {
		$rt = array();
		$rt['errors'] = 0;
		
		$rt['url_value'] = '';
		$rt['url_hash'] = '';
		$rt['url_maxlength'] = 240;
		$rt['url_error'] = 0;
		$rt['url_error_msg'] = array(1 => '你没有填写 URL', 2 => '你填写的 URL 过长，不能超过 ' . $rt['url_maxlength'] . ' 个字符');
		
		if (isset($_GET['url'])) {
			$rt['url_value'] = fetch_single($_GET['url']);
			if ($rt['url_value'] == '') {
				$rt['errors']++;
				$rt['url_error'] = 1;
			} else {
				if (mb_strlen($rt['url_value'], 'UTF-8') > $rt['url_maxlength']) {
					$rt['errors']++;
					$rt['url_errors'] = 2;
				}
			}
		} else {
			$rt['errors']++;
			$rt['url_error'] = 1;
		}
		
		if ($rt['url_error'] == 0) {
			$rt['url_hash'] = md5($rt['url_value']);
		}
		
		$rt['title_value'] = '';
		$rt['title_maxlength'] = 200;
		$rt['title_error'] = 0;
		$rt['title_error_msg'] = array(1 => '你没有填写标题', 2 => '你填写的标题过长，不能超过 ' . $rt['title_maxlength'] . ' 个字符');
		
		if (isset($_GET['title'])) {
			$rt['title_value'] = fetch_single($_GET['title']);
			if ($rt['title_value'] == '') {
				$rt['errors']++;
				$rt['title_error'] = 1;
			} else {
				if (mb_strlen($rt['title_value'], 'UTF-8') > $rt['title_maxlength']) {
					$rt['errors']++;
					$rt['title_errors'] = 2;
				}
			}
		} else {
			$rt['errors']++;
			$rt['title_error'] = 1;
		}
		
		$rt['notes_value'] = '';
		$rt['notes_maxlength'] = 200;
		$rt['notes_error'] = 0;
		$rt['notes_error_msg'] = array(2 => '你填写的备注过长，不能超过 ' . $rt['notes_maxlength'] . ' 个字符');
		
		if (isset($_GET['notes'])) {
			$rt['notes_value'] = fetch_single($_GET['notes']);
			if (mb_strlen($rt['notes_value'], 'UTF-8') > $rt['notes_maxlength']) {
				$rt['errors']++;
				$rt['notes_errors'] = 2;
			}
		}
		
		return $rt;
	}
}
?>