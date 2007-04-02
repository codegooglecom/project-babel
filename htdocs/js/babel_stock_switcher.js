var stock_get_realtime = function() {
	c = getObj("stock_chart");
	c.src = "http://image.sinajs.cn/newchart/min/n/" + market + code + ".gif";
}

var stock_get_k_min5 = function() {
	c = getObj("stock_chart");
	c.src = "http://image.sinajs.cn/newchart/mink5/n/" + market + code + ".gif";
}

var stock_get_k_daily = function() {
	c = getObj("stock_chart");
	c.src = "http://image.sinajs.cn/newchart/daily/n/" + market + code + ".gif";
}

var stock_get_k_weekly = function() {
	c = getObj("stock_chart");
	c.src = "http://image.sinajs.cn/newchart/weekly/n/" + market + code + ".gif";
}

var stock_get_k_monthly = function() {
	c = getObj("stock_chart");
	c.src = "http://image.sinajs.cn/newchart/monthly/n/" + market + code + ".gif";
}

var stock_get_rsi = function() {
	c = getObj("stock_chart");
	c.src = "http://image.sinajs.cn/newchart/rsi/n/" + market + code + ".gif";
}

var stock_get_rsi = function() {
	c = getObj("stock_chart");
	c.src = "http://image.sinajs.cn/newchart/rsi/n/" + market + code + ".gif";
}

var stock_get_macd = function() {
	c = getObj("stock_chart");
	c.src = "http://image.sinajs.cn/newchart/macd/n/" + market + code + ".gif";
}

var stock_get_kdj = function() {
	c = getObj("stock_chart");
	c.src = "http://image.sinajs.cn/newchart/kdj/n/" + market + code + ".gif";
}

var stock_get_mike = function() {
	c = getObj("stock_chart");
	c.src = "http://image.sinajs.cn/newchart/mike/n/" + market + code + ".gif";
}

var stock_charts_preload = function() {
	var rt = new Image(545, 300);
	rt.src = "http://image.sinajs.cn/newchart/min/n/" + market + code + ".gif";
	var kd = new Image(545, 300);
	kd.src = "http://image.sinajs.cn/newchart/daily/n/" + market + code + ".gif";
	var kw = new Image(545, 300);
	kw.src = "http://image.sinajs.cn/newchart/weekly/n/" + market + code + ".gif";
	var km = new Image(545, 300);
	km.src = "http://image.sinajs.cn/newchart/monthly/n/" + market + code + ".gif";
	var rsi = new Image(545, 300);
	rsi.src = "http://image.sinajs.cn/newchart/rsi/n/" + market + code + ".gif";
	var macd = new Image(545, 300);
	macd.src = "http://image.sinajs.cn/newchart/macd/n/" + market + code + ".gif";
	var kdj = new Image(545, 300);
	kdj.src = "http://image.sinajs.cn/newchart/kdj/n/" + market + code + ".gif";
	var mike = new Image(545, 300);
	mike.src = "http://image.sinajs.cn/newchart/mike/n/" + market + code + ".gif";
	var kmin5 = new Image(545, 300);
	kmin5.src = "http://image.sinajs.cn/newchart/mink5/n/" + market + code + ".gif";
}