<?php
class Data {
	public static function vxDataByMonth($prefix, $type) {
		$sql = "SELECT {$prefix}_created FROM babel_{$type} ORDER BY {$prefix}_created ASC LIMIT 1";
		$rs = mysql_query($sql);
		$created_first = getdate(intval(mysql_result($rs, 0, 0)));
		mysql_free_result($rs);
		$sql = "SELECT {$prefix}_created FROM babel_{$type} ORDER BY {$prefix}_created DESC LIMIT 1";
		$rs = mysql_query($sql);
		$created_last = getdate(intval(mysql_result($rs, 0, 0)));
		mysql_free_result($rs);
		if ($created_last['year'] > $created_first['year']) {
			$years = 1 + ($created_last['year'] - $created_first['year']);
		} else {
			$years = 1;
		}
		if ($years > 1) {
			$_months = array();
			$i = $created_first['mon'];
			$j = $created_first['year'];
			while (1) {
				if ($i == 13) {
					$i = 1;
					$j++;
				}
				$timestamp = mktime(0, 0, 0, $i, 1, $j);
				if ($timestamp < $created_last[0]) {
					$_months[] = $timestamp;
					$i++;
				} else {
					break;
				}
			}
		} else {
			$months = 1 + ($created_last['mon'] - $created_first['mon']);
			$_months = array();
			for ($i = 0; $i < $months; $i++) {
				$timstamp = mktime(0, 0, 0, ($created_first['mon'] + $i), 1, $created_first['year']);
				$_months[] = $timstamp;
			}
		}
		$count_months = count($_months);
		for ($i = 0; $i < $count_months; $i++) {
			$start = $_months[$i];
			if (($i + 1) < $count_months) {
				$end = ($_months[$i + 1]) - 86400;
			} else {
				$end = $_months[$i] + (86400 * 30);
			}
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