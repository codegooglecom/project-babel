var switchAdsReply = function() {
	var _o_large = getObj("ggad_60");
	var _o_small = getObj("ggad_15");
	if (_o_large.style.display == "block") {
		_o_large.style.display = "none";
	} else {
		_o_large.style.display = "block";
	}
	if (_o_small.style.display == "inline") {
		_o_small.style.display = "none";
	} else {
		_o_small.style.display = "inline";
	}
}

var initAdsReply = function() {
	var _o_large = getObj("ggad_60");
	var _o_small = getObj("ggad_15");
	_o_large.style.display = "block";
	_o_small.style.display = "inline";
}
