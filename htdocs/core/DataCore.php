<?php
class Data {
	public static function vxDataByMonth($prefix, $type) {
		$sql = "SELECT {$prefix}_created FROM babel_{$type} ORDER BY {$prefix}_created ASC LIMIT 1";
		$rs = mysql_query($sql);
		$created_first = intval(mysql_result($rs, 0, 0));
		mysql_free_result($rs);
		$sql = "SELECT {$prefix}_created FROM babel_{$type} ORDER BY {$prefix}_created DESC LIMIT 1";
		$rs = mysql_query($sql);
		$created_last = intval(mysql_result($rs, 0, 0));
		mysql_free_result($rs);
		$duration = $created_last - $created_first;
		$month = 86400 * 30;
		$months = floor($duration / $month) + 1;
		$data = array();
		for ($i = 0; $i < $months; $i++) {
			$start = $created_first + ($i * $month);
			$end = $created_first + (($i + 1) * $month);
			$sql = "SELECT COUNT(*) FROM babel_{$type} WHERE {$prefix}_created >= {$start} AND {$prefix}_created < {$end}";
			$rs = mysql_query($sql);
			$count = mysql_result($rs, 0, 0);
			$data[$start] = $count;
			mysql_free_result($rs);
		}
		return $data;
	}
	
	public function vxDataTopicByNode() {
		$sql = "SELECT nod_topics, nod_title FROM babel_node WHERE nod_topics > 0 ORDER BY nod_topics DESC LIMIT 50";
		$rs = mysql_query($sql);
		$_nodes = array();
		while ($_node = mysql_fetch_array($rs)) {
			$_nodes[] = $_node;
		}
		mysql_free_result($rs);
		$o = '';
		$o .= '<chart>';
		$o .= '<series>';
		$i = 0;
		foreach ($_nodes as $_node) {
			$i++;
			$o .= '<value xid="' . $i . '">' . $_node['nod_title'] . '</value>';
		}
		$o .= '</series>';
		$o .= '<graphs>';
		$o .= '<graph gid="1">';
		$i = 0;
		foreach ($_nodes as $_node) {
			$i++;
			$o .= '<value xid="' . $i . '">' . $_node['nod_topics'] . '</value>';
		}
		$o .= '</graph>';
		$o .= '</graphs>';
		$o .= '</chart>';
		return $o;
	}
	
	public static function vxChartSettings($type) {
		$settings = file_get_contents(BABEL_PREFIX . '/res/chart_settings_' . $type . '.xml');
		return $settings;
	}
	
	public static function vxData2Amchart($data) {
		$o = '';
		$o .= '<chart>';
		$o .= '<series>';
		$i = 0;
		foreach ($data as $key => $value) {
			$i++;
			$o .= '<value xid="' . $i . '">' . date('Y-n', $key) . '</value>';
		}
		$o .= '</series>';
		$o .= '<graphs>';
		$o .= '<graph gid="1">';
		$i = 0;
		foreach ($data as $key => $value) {
			$i++;
			$o .= '<value xid="' . $i . '">' . $value . '</value>';
		}
		$o .= '</graph>';
		$o .= '<graph gid="2">';
		$i = 0;
		foreach ($data as $key => $value) {
			$i++;
			$o .= '<value xid="' . $i . '">' . $value . '</value>';
		}
		$o .= '</graph>';
		$o .= '</graphs>';
		$o .= '</chart>';
		return $o;
	}
}
?>