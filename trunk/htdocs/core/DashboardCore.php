<?php
class Dashboard {
	public static function vxCalcWeightFromTopic($hits, $posts) {
		$hits_score = floor($hits / 7);
		$posts_score = floor($posts / 2);
		$score = $hits_score + $posts_score;
		if ($score < 12) $score = 12;
		if ($score > 25) $score = 25;
		return $score;
	}
}
?>