var calcOffsets = function(count) {
	offsets = new Array();
	for (i = 1; i <= count; i++) {
		offsets[i] = getObj("rss_entry_" + i).offsetTop - 10;
	}
	i++;
	offsets[i] = getObj("rss_bottom").offsetTop - 10;
	return offsets;
}

var getScrollTop = function() {
	if (document.documentElement) {
		return document.documentElement.scrollTop;
	} else {
		if (document.body) {
			return document.documentElement.scrollTop;
		} else {
			return window.pageYOffset;
		}
	}
}

var checkPosition = function(count, offsets) {
	pos_cur = getScrollTop();
	pos_pre = 220;
	set = 0;
	for (i = 1; i < (count + 1); i++) {
		pos_start = offsets[i] - pos_pre;
		pos_end = offsets[(i + 1)] - pos_pre;
		o = getObj("rss_" + i);
		if (i == count) {
			if (pos_cur > pos_start) {
				if (set == 0) {
					set = 1;
					setTimeout("setRSSEntryStyle(" + i + ", " + count + ")", 200);
				}
			} else {
				o.style.border = "2px solid #FFF";
			}
		} else {
			if ((pos_cur > pos_start) && (pos_cur < pos_end)) {
				if (set == 0) {
					set = 1;
					setTimeout("setRSSEntryStyle(" + i + ", " + count + ")", 200);
				}
			} else {
				o.style.border = "2px solid #FFF";
			}
		}
	}
	return true;
}

var setRSSEntryStyle = function(i, count) {
	var o = getObj("rss_" + i);
	o.style.border = "2px solid #CCC;";
	for (j = 1; j < (count + 1); j++) {
		if (j != i) {
			other = getObj("rss_" + j);
			other.style.border = "2px solid #FFF";
		}
	}
}
