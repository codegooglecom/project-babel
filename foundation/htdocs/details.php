<?php
require('config.php');
// Currency conversions(according to Apple Dashboard):

$currency_usd2cny = 7.74;

$xml = simplexml_load_file('../data/' . $project_current . '.xml');
$amount_total = 0;
$donation_count = 0;
foreach ($xml->donater as $donater) {
	if (strval($donater->amount) != '') {
		if (strval($donater->currency != 'CNY')) {
			switch (strval($donater->currency)) {
				case 'USD':
					$donater->amount_converted = intval($donater->amount) * $currency_usd2cny;
				break;
			}
			$amount_total = $amount_total + $donater->amount_converted;
		} else {
			$amount_total = $amount_total + intval($donater->amount);
		}
		$donation_count++;
	}
}
$server_name = strtolower($_SERVER['SERVER_NAME']);
$request_uri = $_SERVER['REQUEST_URI'];
if (!in_array($server_name, array('foundation.v2ex.com', 'f-dev.v2ex.com'))) {
	header('Location: http://' . $server_name . $request_uri);
	die();
}
$project_current_title = 'V2EX 2007-2008 在 (mt) 的 hosting 费用募捐';
$goal = 8000;
$percentage = $amount_total / $goal;
$progress_width = 400 * $percentage;
$percentage_text = vsprintf('%.1f', 100 * $percentage) . '%';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>V2EX Foundation | 捐助</title>
		<meta name="SubversionID" content="$Id: index.php 110 2007-02-25 08:27:53Z livid $" />
		<link href="/favicon.ico" rel="shortcut icon" />
		<link rel="stylesheet" href="style.css" type="text/css" />
	</head>
	<body>
		<div align="center">
			<img src="img/logo.png" alt="V2EX Foundation" width="235" height="37" />
			<div class="f_box">
				<div class="f_box_t" align="left">当前募捐进度 | <?php echo $project_current_title; ?></div>
				<div class="f_box_m" align="center">
					<div style="width: 400px; padding: 1px 1px 5px 1px;" align="right">
						<div style="width: 400px; color: #333; font-size: 12px;" align="center"><?php echo $percentage_text; ?> 共 <?php echo $amount_total; ?> 元已完成，来自于 <a href="details.php"><?php echo $donation_count; ?></a> 次捐助</div>
					</div>
					
					<div style="width: 400px; height: 20px; background-color: #FFF; border: 1px solid #FF6000; padding: 1px; -moz-border-radius: 3px;" align="left">
						
						<div style="width: <?php echo $progress_width; ?>px; height: 20px; background-color: #0ECB00; background-image: url('img/progress.png'); -moz-border-radius: 3px; position: relative; top: 0px; left: 0px;"></div>
						
					</div>
					
					<div style="width: 400px; padding: 5px 1px 1px 1px;" align="right">
						<div style="color: #333; font-size: 10px;">RMB 8000</div>
					</div>
					
					<hr size="1" color="#EEE" style="color: #EEE; background-color: #EEE; height: 1px; border: 0; -moz-border-radius: 3px;" />
					
					<div align="left"><span class="tip">本进度信息根据多个捐助源汇总得出，并非实时更新，<a href="#donate">立刻捐助</a>！</span></div>
				</div>
			</div>
			<div class="f_box">
				<div class="f_box_t" align="left">本页是什么？</div>
				<div class="f_box_m" align="left">
				你现在看到的是 <span class="v2ex_logo">V2EX</span> <span class="red">foundation</span> 的首页，本页上有关于 <span class="v2ex_logo"><a href="http://www.v2ex.com/" target="_blank">V2EX</a></span> 网站的各种费用募捐方面的信息。<br /><br />
				<span class="v2ex_logo">V2EX</span> 是一个非商业的综合信息交流平台，基于开放源代码程序 Project Babel 搭建，开始于 2006 年 4 月。运营费用主要来自于会员们的捐助。更多关于 <span class="v2ex_logo">V2EX</span> 网站的信息，可以在以下页面获得。
				
				<ul class="sq">
					<li><a href="http://www.v2ex.com/new_features.html" target="_blank">http://www.v2ex.com/new_features.html</a></li>
					<li><a href="http://blogsearch.google.com/blogsearch?hl=en&q=v2ex" target="_blank">http://blogsearch.google.com/blogsearch?hl=en&q=v2ex</a></li>
					<li><a href="http://www.technorati.com/search/v2ex" target="_blank">http://www.technorati.com/search/v2ex</a></li>
				</ul>
				
				如果你想了解关于 Project Babel 的更多信息，请至 <a href="http://labs.v2ex.com/" target="_blank">V2EX Labs</a> 。
				</div>
			</div>
			<div class="f_box">
				<div class="f_box_t" align="left">FAQ | 常规问题解答</div>
				<div class="f_box_m" align="left"></div>
			</div>
			<div class="f_box">
				<div class="f_box_t" align="left"><a name="donate"></a> 捐助方式</div>
				<div class="f_box_m" align="left">
					<table width="100%" cellpadding="0" cellspacing="0" border="0">
						
						<tr>
							<td width="23%" align="right" valign="top" class="t_b">
							<img src="img/alipay.png" />
							<br />
							<span class="tip"><a href="http://www.alipay.com/" target="_blank">支付宝</a><small class="cn"> (中国大陆)</small></span></td>
							
							<td width="27%" align="left" style="padding-left: 5px;" class="t_b">
							<img src="img/e_l.png" alt="livid@livid.cn" /><br />
							<img src="img/btn_alipay.png" alt="立刻通过支付宝捐助" />
							</td>
							
							<td width="23%" align="right" valign="top" class="t_b">
							<img src="img/99bill.png" />
							<br />
							<span class="tip"><a href="http://www.99bill.com/" target="_blank">快钱</a><small class="cn"> (中国大陆)</small></span></td>
							
							<td width="27%" align="left" style="padding-left: 5px;" class="t_b" valign="top">
							<img src="img/e_l.png" alt="livid@livid.cn" /><br />
							<a href='https://www.99bill.com/webapp/donateAction.do?ad=88990702245866107&buttonid=27784&mac=E1FAD21898385E27F8635F0E54967BDA' target='_blank'><img border=0 alt='V2EX' src='img/btn_99bill.png' align="absmiddle" /></a>
							</td>
						</tr>
						<tr>
							<td width="23%" align="right" valign="top">
							<img src="img/paypalcn.png" />
							<br />
							<span class="tip"><a href="http://www.paypal.com.cn/" target="_blank">贝宝</a><small class="cn"> (中国大陆)</small></span>
							</td>
							
							<td width="27%" align="left" style="padding-left: 5px;" valign="top">
							<img src="img/e_l.png" alt="livid@livid.cn" /><br />
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="display: inline;">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="image" src="img/btn_paypalcn.png" border="0" name="submit" alt="请使用贝宝付款，这是快捷、免费和安全的付款方式！" align="absmiddle">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
</form>
							</td>
							
							<td width="23%" align="right" valign="top">
							<img src="img/paypal.png" />
							<br />
							<span class="tip"><a href="http://www.paypal.com/" target="_blank"><small>PayPal</small></a><small class="cn"> (全世界)</small></span>
							</td>

							<td width="27%" align="left" style="padding-left: 5px;">
							<img src="img/e_v.png" alt="v2ex.livid@mac.com" /><br />
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="display: inline;">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="image" src="img/btn_paypal.png" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!" align="absmiddle">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" align="absmiddle">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----MIIHLwYJKoZIhvcNAQcEoIIHIDCCBxwCAQExggEwMIIBLAIBADCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwDQYJKoZIhvcNAQEBBQAEgYBT3gRzR6+xW38Yc7pNg2v4sLD6Qg7JsOADyuXChsZkW2lP7aD9lvOoGzfaX+lR9DeMcFgLdScwYHvDPVc+I90scCLIH4Y0RT+Dy0JV9XvJwe0QizJY8aATeu/GGsWf32pbc/5hAsYt1I6D/BuvBpQa6XZUSp0W8UAWwAM7aYhYvDELMAkGBSsOAwIaBQAwgawGCSqGSIb3DQEHATAUBggqhkiG9w0DBwQIQMjV83XRS/eAgYiyXK39816F1iygcso4bfshYG0L2bF4eTc3pzL6OmGFFighbGeaI9NvZmKeQyDX/aDyFfMVU+dsk7oBW9r7kS+zx80/SuOPnQa5v0phiZvxmFUW3AFa5V97pjGr2Mzhr478IZxIp0jWHc+/iFART7FsuVOCphs6m4Pe6M/61gxxzXr6r8MYyblBoIIDhzCCA4MwggLsoAMCAQICAQAwDQYJKoZIhvcNAQEFBQAwgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tMB4XDTA0MDIxMzEwMTMxNVoXDTM1MDIxMzEwMTMxNVowgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDBR07d/ETMS1ycjtkpkvjXZe9k+6CieLuLsPumsJ7QC1odNz3sJiCbs2wC0nLE0uLGaEtXynIgRqIddYCHx88pb5HTXv4SZeuv0Rqq4+axW9PLAAATU8w04qqjaSXgbGLP3NmohqM6bV9kZZwZLR/klDaQGo1u9uDb9lr4Yn+rBQIDAQABo4HuMIHrMB0GA1UdDgQWBBSWn3y7xm8XvVk/UtcKG+wQ1mSUazCBuwYDVR0jBIGzMIGwgBSWn3y7xm8XvVk/UtcKG+wQ1mSUa6GBlKSBkTCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb22CAQAwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQUFAAOBgQCBXzpWmoBa5e9fo6ujionW1hUhPkOBakTr3YCDjbYfvJEiv/2P+IobhOGJr85+XHhN0v4gUkEDI8r2/rNk1m0GA8HKddvTjyGw/XqXa+LSTlDYkqI8OwR8GEYj4efEtcRpRYBxV8KxAW93YDWzFGvruKnnLbDAF6VR5w/cCMn5hzGCAZowggGWAgEBMIGUMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbQIBADAJBgUrDgMCGgUAoF0wGAYJKoZIhvcNAQkDMQsGCSqGSIb3DQEHATAcBgkqhkiG9w0BCQUxDxcNMDcwMjI0MTExNDEyWjAjBgkqhkiG9w0BCQQxFgQUawUqh31o72+nq0OpOhHhnX3zS4UwDQYJKoZIhvcNAQEBBQAEgYC2TUGENu06v5fuD26Gmzx7VTRVvN3cRsoRyUgvPuKauLvIGslng0dCiUVldyqkScps0NeEXGFjBh+a/Xc7JATUKOyIFvX9rqDru9Fo1B1MVHFGUCPg0Y1z2DihSrSePn2hBxVGRt8VRZR2TjNeWChHOjHFjNq7v+LLgiYjx/SHOA==-----END PKCS7-----
">
</form>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="copyright">
			&copy; 2007 <span class="v2ex_logo"><a href="http://www.v2ex.com/" target="_blank">V2EX</a></span>
			</div>
		</div>
	</body>
</html>