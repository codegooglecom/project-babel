<?php
/* Project Babel
 *
 * Author: Livid Liu <v2ex.livid@mac.com>
 * File: /htdocs/core/ImageCore.php
 * Usage: Image Class
 * Format: 1 tab indent(4 spaces), LF, UTF-8, no-BOM
 *
 * Subversion Keywords:
 *
 * $Id$
 * $Date$
 * $Revision$
 * $Author$
 * $URL$
 *
 * Copyright (C) 2006 Livid Liu <v2ex.livid@mac.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

if (@V2EX_BABEL != 1) {
	die('<strong>Project Babel</strong><br /><br />Made by <a href="http://www.v2ex.com/">V2EX</a> | software for internet');
}

define("OP_TO_FILE", 1);              // Output to file
define("OP_OUTPUT", 2);               // Output to browser
define("OP_NOT_KEEP_SCALE", 4);       // Free scale
define("OP_BEST_RESIZE_WIDTH", 8);    // Scale to width
define("OP_BEST_RESIZE_HEIGHT", 16);  // Scale to height

define("CM_DEFAULT",0);               // Clipping method: default
define("CM_LEFT_OR_TOP",1);           // Clipping method: left or top
define("CM_MIDDLE",2);                // Clipping method: middle
define("CM_RIGHT_OR_BOTTOM",3);       // Clipping method: right or bottom

require_once('Zend/Service/Flickr.php');

/* S Image class */

class Image {
	public function vxFlickrImageURL($text) {
		$p_img = '/http\:\/\/farm([0-9]+)\.static\.flickr\.com\/([a-zA-Z0-9]*)\/([a-zA-Z0-9]*)\_([a-zA-Z0-9]*)\_s\.jpg/';
		preg_match($p_img, $text, $img);
		if (intval($img[1]) == 1) {
			$host = '68.142.213.135';
		}
		if (intval($img[1]) == 2) {
			$host = '69.147.90.156';
		}
		$url_img = 'http://' . $host . '/' . $img[2] . '/' . $img[3] . '_' . $img[4] . '_s.jpg';
		$url = array('img' => $url_img);
		return $url;
	}
	
	public function vxFlickrBoardBlock($tag = 'beauty', $width = 1024, $colspan = 4) {
		switch ($width) {
			case 800:
				$b = 4;
				break;
			case 640:
				$b = 3;
				break;
			case 1024:
			default:
				$b = 7;
				break;
			case 1280:
				$b = 9;
				break;
			case 1400:
			case 1600:
			case 1920:
			case 2560:
				$b = 10;
				break;
		}
		
		$Flickr = new Zend_Service_Flickr(FLICKR_API_KEY);
		
		$_photos = $Flickr->tagSearch($tag);

		if (count($_photos) > 0) {
			$f = '';
			$f .= '<tr><td align="left" class="hf" colspan="' . $colspan . '" style="border-top: 1px solid #EEE;">';
			
			$f .= '<a href="http://www.flickr.com/photos/tags/' . $tag . '" target="_blank"><img src="/img/flickr_logo.gif" border="0" align="absmiddle" /></a>&nbsp;&nbsp;&nbsp;<span class="tip_i">These photos are taken by various authors on <a href="http://www.flickr.com/" target="_blank" class="regular">Flickr</a> and sharing under a Creative Commons license.</span>';
			$f .= '</td></tr>';
			
			$f .= '<tr><td align="left" class="hf" colspan="' . $colspan . '">';
			$i = 0;
			foreach ($_photos as $Photo) {
				$i++;
				if ($i > $b) {
					break;
				}
				$url = Image::vxFlickrImageURL($Photo->Square->uri);
				if (isset($Photo->Medium->clickUri)) {
					$f .= '<a href="' . $Photo->Medium->clickUri . '" class="friend" target="_blank"><img src="' . $url["img"] . '" align="absmiddle" class="portrait" /><br /><small>';
					if (mb_strlen($Photo->title, 'UTF-8') > 10) {
						$f .= mb_substr($Photo->title, 0, 10, 'UTF-8') . ' ...';
					} else {
						$f .= $Photo->title;
					}
					$f .= '</small></a>';
				} else {
					$f .= '<a href="' . $Photo->Square->clickUri . '" class="friend" target="_blank"><img src="' . $url["img"] . '" align="absmiddle" class="portrait" /><br /><small>';
					if (mb_strlen($Photo->title, 'UTF-8') > 10) {
						$f .= mb_substr($Photo->title, 0, 10, 'UTF-8') . ' ...';
					} else {
						$f .= $Photo->title;
					}
					$f .= '</small></a>';
				}
			}
			
			$f .= '</td></tr>';
			
			$f .= '<tr><td align="left" class="hf" colspan="' . $colspan . '"><span class="tip_i">Thanks them for sharing these great moments, for more great shots please visit <a href="http://www.flickr.com/" target="_blank" class="regular">Flickr.com</a> <img src="/img/fico_flickr.gif" align="absmiddle" border="0" /></span></td></tr>';
		} else {
			$f = '';
			$f .= '<tr><td align="left" class="hf" colspan="' . $colspan . '" style="border-top: 1px solid #EEE;">';
			
			$f .= '<a href="http://www.flickr.com/photos/tags/' . $tag . '" target="_blank"><img src="/img/flickr_logo.gif" border="0" align="absmiddle" /></a>&nbsp;&nbsp;&nbsp;<span class="tip_i">' . Vocabulary::site_name . ' 支持从 Flickr 聚合照片！想让你的照片显示在这？给照片加上 [ ' . $tag . ' ] 标签即可。</span>';
			$f .= '</td></tr>';
		}		
		return $f;
	}
	
	public function vxLividark($filename) {
		exec(IM_CMD . ' -type grayscale -quality ' . IM_QUALITY . ' ' . $filename);
		exec(IM_CMD . ' -level 32%,40%,1.0 -quality ' . IM_QUALITY . ' ' . $filename);
		return true;
	}
	
	public function vxMemory($filename) {
		exec(IM_CMD . ' -charcoal 9 -quality ' . IM_QUALITY . ' ' . $filename);
		return true;
	}

	/**
	 * vxResize
	 *
	 * @param string $srcFile source file
	 * @param string $srcFile destination file
	 * @param int $dstW width of destination file (pixel)
	 * @param int $dstH height of destination file (pixel)
	 * @param int $option options, you add multiple options like 1+2(or 1|2), this utilize function 1 & 2
	 *      1: default，output to file 2: output to browser stream 4: free scale
	 *      8：scale to width 16：scale to height
	 * @param int $cutmode clipping method 0: default 1: left or top 2: middle 3: right or bottom
	 * @param int $startX start X axis (pixel)
	 * @param int $startY start Y axis (pixel)
	 * @return array return[0]=0: OK; return[0] error code return[1] string: error description
	 */
	
	public function vxResize($srcFile, $dstFile, $dstW, $dstH, $option=OP_TO_FILE, $cutmode=CM_DEFAULT, $startX=0, $startY=0) {
		$img_type = array(1=>"gif", 2=>"jpeg", 3=>"png");
		$type_idx = array("gif"=>1, "jpg"=>2, "jpeg"=>2, "jpe"=>2, "png"=>3);

		if (!file_exists($srcFile)) {
			return array(-1, "Source file not exists: $srcFile.");
		}

		$path_parts = @pathinfo($dstFile);
		$ext = strtolower ($path_parts["extension"]);
	
		if ($ext == "") {
			return array(-5, "Can't detect output image's type.");
		}
	
		$func_output = "image" . $img_type[$type_idx[$ext]];
	
		if (!function_exists ($func_output)) {
			return array(-2, "Function not exists for output：$func_output.");
		}
	
		$data = @GetImageSize($srcFile);
		$func_create = "imagecreatefrom" . $img_type[$data[2]];
	
		if (!function_exists ($func_create)) {
			return array(-3, "Function not exists for create：$func_create.");
		}
	
		$im = @$func_create($srcFile);
	
		$srcW = @ImageSX($im);
		$srcH = @ImageSY($im);
		$srcX = 0;
		$srcY = 0;
		$dstX = 0;
		$dstY = 0;
	
		if ($option & OP_BEST_RESIZE_WIDTH) {
			$dstH = round($dstW * $srcH / $srcW);
		}
	
		if ($option & OP_BEST_RESIZE_HEIGHT) {
			$dstW = round($dstH * $srcW / $srcH);
		}
	
		$fdstW = $dstW;
		$fdstH = $dstH;
	
		if ($cutmode != CM_DEFAULT) { // clipping method 1: left or top 2: middle 3: right or bottom
	
			$srcW -= $startX;
			$srcH -= $startY;
	
			if ($srcW*$dstH > $srcH*$dstW) { 
				$testW = round($dstW * $srcH / $dstH);
				$testH = $srcH;
			} else {
				$testH = round($dstH * $srcW / $dstW);
				$testW = $srcW;
			}
			
			switch ($cutmode) {
				case CM_LEFT_OR_TOP: $srcX = 0; $srcY = 0; break;
				case CM_MIDDLE: $srcX = round(($srcW - $testW) / 2);
								$srcY = round(($srcH - $testH) / 2); break;
				case CM_RIGHT_OR_BOTTOM: $srcX = $srcW - $testW;
										 $srcY = $srcH - $testH;
			}
	
			$srcW = $testW;
			$srcH = $testH;
			$srcX += $startX;
			$srcY += $startY;
	
		} else {
			if (!($option & OP_NOT_KEEP_SCALE)) {
				if ($srcW*$dstH>$srcH*$dstW) { 
					$fdstH=round($srcH*$dstW/$srcW); 
					$dstY=floor(($dstH-$fdstH)/2); 
					$fdstW=$dstW;
				} else { 
					$fdstW=round($srcW*$dstH/$srcH); 
					$dstX=floor(($dstW-$fdstW)/2); 
					$fdstH=$dstH;
				}
	
				$dstX=($dstX<0)?0:$dstX;
				$dstY=($dstX<0)?0:$dstY;
				$dstX=($dstX>($dstW/2))?floor($dstW/2):$dstX;
				$dstY=($dstY>($dstH/2))?floor($dstH/s):$dstY;
	
			}
		}
	
		if( function_exists("imagecopyresampled") and 
			function_exists("imagecreatetruecolor") ){
			$func_create = "imagecreatetruecolor";
			$func_resize = "imagecopyresampled";
		} else {
			$func_create = "imagecreate";
			$func_resize = "imagecopyresized";
		}
	
		$newim = @$func_create($dstW,$dstH);
		$black = @ImageColorAllocate($newim, 0,0,0);
		$back = @imagecolortransparent($newim, $black);
		@imagefilledrectangle($newim,0,0,$dstW,$dstH,$black);
		@$func_resize($newim,$im,$dstX,$dstY,$srcX,$srcY,$fdstW,$fdstH,$srcW,$srcH);
	
		if ($option & OP_TO_FILE) {
			switch ($type_idx[$ext]) {
				case 1:
				case 3:
					@$func_output($newim,$dstFile);
					break;
				case 2:
					@$func_output($newim,$dstFile,100);
					break;
			}
		}
	
		if ($option & OP_OUTPUT) {
			if (function_exists("headers_sent")) {
				if (headers_sent()) {
					return array(-4, "HTTP already sent, can't output image to browser.");
				}
			}
			header("Content-type: image/" . $img_type[$type_idx[$ext]]);
			@$func_output($newim);
		}
	
		@imagedestroy($im);
		@imagedestroy($newim);
	
		return array(0, "OK");
	}
	
	public function vxGenConfirmCode() {
		$az = array('q', 'w', 'r', 't', 'y', 'p', 's', 'd', 'f', 'g', 'h', 'j', 'k', 'z', 'x', 'c', 'v', 'b', 'n', 'm', 'th', 'wr');
		$aa = array('a', 'e', 'u');
		$len = rand(4,8);
		$str = '';

		for ($i = 2; $i <= $len; $i++) {
			if ($i % 2 == 0) {
				$c = strtoupper($az[rand(0, 19)]);
				$str = $str . $c;
			} else {
				$c = strtoupper($aa[rand(0, 2)]);
				$str = $str . $c;	
			}
		}
		
		// put it to session
		
		$_SESSION['c'] = $str;

		// create the image
		$fn = BABEL_PREFIX . '/res/wbg' . rand(1,3) . '.png';
		$im = imagecreatefrompng($fn);

		// create some colors
		$fg = imagecolorallocate($im, 240, 240, 230);
		$bg = imagecolorallocate($im, 120, 140, 190);
		$bbg = imagecolorallocate($im, 20, 40, 40);

		$fonts = array(0 => 'zt', 1 => 'cgn', 2 => 'carbon');
		$font = BABEL_PREFIX . '/fonts/' . $fonts[rand(0,2)] . '.ttf';

		// add some shadow to the text
		$x = rand(10, 60);	

		// add the text
		imagettftext($im, 22, 0, $x+2, 32, $bg, $font, $str);
		imagettftext($im, 22, 0, $x+1, 31, $bg, $font, $str);
		imagettftext($im, 22, 0, $x-1, 29, $bg, $font, $str);
		imagettftext($im, 22, 0, $x-2, 28, $bbg, $font, $str);
		imagettftext($im, 22, 0, $x, 30, $fg, $font, $str);
		

		imagepng($im, BABEL_PREFIX . '/htdocs/img/c/' . session_id() . '.png');
	}
	
	public function vxGenConfirmCodeLuxi() {
		$az = array('q', 'w', 'r', 't', 'y', 'p', 's', 'd', 'f', 'g', 'h', 'j', 'k', 'z', 'x', 'c', 'v', 'b', 'n', 'm');
		$aa = array('a', 'e', 'u');
		$len = rand(5,7);
		$str = '';

		for ($i = 2; $i <= $len; $i++) {
			if ($i % 2 == 0) {
				$c = rand(0,1) ? strtoupper($az[rand(0, 19)]):$az[rand(0,19)];
				$str = $str . $c;
			} else {
				$c = rand(0,1) ? strtoupper($aa[rand(0, 2)]):$aa[rand(0,2)];
				$str = $str . $c;	
			}
		}
		
		// put it to session
		
		$_SESSION['c'] = $str;

		// create the image
		$fn = BABEL_PREFIX . '/res/bg' . rand(1,10) . '.png';
		$im = imagecreatefrompng($fn);

		// create some colors
		$shadow = imagecolorallocate($im, 0, 0, 0);
		$fg = imagecolorallocate($im, 255, rand(160, 216), 0);

		$fonts = array(0 => 'luxisbi', 1 => 'luximbi', 2 => 'ga');
		$font = BABEL_PREFIX . '/fonts/' . $fonts[rand(0,2)] . '.ttf';

		// add some shadow to the text
		$x = rand(10, 60);
		
		// add the shadow
		imagettftext($im, 22, 0, $x, 31, $shadow, $font, $str);

		// add the text
		imagettftext($im, 22, 0, $x-1, 30, $fg, $font, $str);

		// draw the noise pixels

		for ($x = 0; $x < 200; $x++) {
			for ($y = 0; $y < 40; $y++) {
				if (rand(0,15) == 1) {
					$n = rand(0, 64);
					$noise = imagecolorallocate($im, $n, $n, $n);
					imagesetpixel($im, $x, $y, $noise);
				}
			}
		}

		// draw the noise lines

		$noise = imagecolorallocate($im, 0, 0, 0);
		for ($x = 0; $x < 8; $x++) {
			$x1 = $x * 25;
			$x2 = ($x + 1)*25 - 1;
			$y1 = 0;
			$y2 = 39;
			imageline($im, $x1, $y1, $x2, $y2, $noise);
			imageline($im, $x1, $y1, ($x2 - 25*2), $y2, $noise);
		}
		
		imageline($im, 199, 0, 174, 39, $noise);
		imagepng($im, BABEL_PREFIX . '/htdocs/img/c/' . session_id() . '.png');
	}
	
	public static function vxGenUserPortraits($ext, $user_id, $db) {
		global $ZEND_CACHE_OPTIONS_LONG_FRONTEND;
		global $ZEND_CACHE_OPTIONS_LONG_BACKEND;
		global $ZEND_CACHE_OPTIONS_MEMCACHED;
		if (ZEND_CACHE_MEMCACHED_ENABLED == 'yes') {
			$cache = Zend_Cache::factory('Core', 'Memcached', $ZEND_CACHE_OPTIONS_LONG_FRONTEND, $ZEND_CACHE_OPTIONS_MEMCACHED);
		} else {
			$cache = Zend_Cache::factory('Core', ZEND_CACHE_TYPE_LONG, $ZEND_CACHE_OPTIONS_LONG_FRONTEND, $ZEND_CACHE_OPTIONS_LONG_BACKEND[ZEND_CACHE_TYPE_LONG]);
		}
		Image::vxResize(BABEL_PREFIX . '/tmp/' . $user_id . $ext, BABEL_PREFIX . '/htdocs/img/p_static/' . $user_id . '.' . BABEL_PORTRAIT_EXT, 75, 75, 1|4, 2);
		$content = addslashes(file_get_contents(BABEL_PREFIX . '/htdocs/img/p_static/' . $user_id . '.' . BABEL_PORTRAIT_EXT));
		Image::vxUpdateUserPortrait($user_id, $content, $db);
		$cache->remove('user_portrait_' . $user_id);
		Image::vxResize(BABEL_PREFIX . '/tmp/' . $user_id . $ext, BABEL_PREFIX . '/htdocs/img/p_static/' . $user_id . '_s.' . BABEL_PORTRAIT_EXT, 32, 32, 1|4, 2);
		$content = addslashes(file_get_contents(BABEL_PREFIX . '/htdocs/img/p_static/' . $user_id . '_s.' . BABEL_PORTRAIT_EXT));
		Image::vxUpdateUserPortrait($user_id . '_s', $content, $db);
		$cache->remove('user_portrait_' . $user_id . '_s');
		Image::vxResize(BABEL_PREFIX . '/tmp/' . $user_id . $ext, BABEL_PREFIX . '/htdocs/img/p_static/' . $user_id . '_n.' . BABEL_PORTRAIT_EXT, 16, 16, 1|4, 2);
		$content = addslashes(file_get_contents(BABEL_PREFIX . '/htdocs/img/p_static/' . $user_id . '_n.' . BABEL_PORTRAIT_EXT));
		Image::vxUpdateUserPortrait($user_id . '_n', $content, $db);
		$cache->remove('user_portrait_' . $user_id . '_n');
		$cache = null;
	}
	
	public static function vxUpdateUserPortrait($filename, $content, $db) {
		$sql = "SELECT COUNT(*) FROM babel_user_portrait WHERE urp_filename = '{$filename}'";
		$count = mysql_result(mysql_query($sql, $db), 0, 0);
		if ($count == 1) {
			$sql = "UPDATE babel_user_portrait SET urp_content = '{$content}' WHERE urp_filename = '{$filename}'";
			mysql_query($sql, $db);
			return true;
		} else {
			$sql = "INSERT INTO babel_user_portrait(urp_filename, urp_content) VALUE('{$filename}', '{$content}')";
			mysql_query($sql, $db) or die(__LINE__ . mysql_error());
			return true;
		}
	}
	
	public static function vxGenBlogPortraits($ext, $weblog_id, $db) {
		global $ZEND_CACHE_OPTIONS_LONG_FRONTEND;
		global $ZEND_CACHE_OPTIONS_LONG_BACKEND;
		global $ZEND_CACHE_OPTIONS_MEMCACHED;
		if (ZEND_CACHE_MEMCACHED_ENABLED == 'yes') {
			$cache = Zend_Cache::factory('Core', 'Memcached', $ZEND_CACHE_OPTIONS_LONG_FRONTEND, $ZEND_CACHE_OPTIONS_MEMCACHED);
		} else {
			$cache = Zend_Cache::factory('Core', ZEND_CACHE_TYPE_LONG, $ZEND_CACHE_OPTIONS_LONG_FRONTEND, $ZEND_CACHE_OPTIONS_LONG_BACKEND[ZEND_CACHE_TYPE_LONG]);
		}
		Image::vxResize(BABEL_PREFIX . '/tmp/' . $weblog_id . $ext, BABEL_PREFIX . '/htdocs/img/b/' . $weblog_id . '.' . BABEL_PORTRAIT_EXT, 100, 100, 1|4, 2);
		$content = addslashes(file_get_contents(BABEL_PREFIX . '/htdocs/img/b/' . $weblog_id . '.' . BABEL_PORTRAIT_EXT));
		Image::vxUpdateBlogPortrait($weblog_id, $content, $db);
		$cache->remove('weblog_portrait_' . $weblog_id);
		Image::vxResize(BABEL_PREFIX . '/tmp/' . $weblog_id . $ext, BABEL_PREFIX . '/htdocs/img/b/' . $weblog_id . '_s.' . BABEL_PORTRAIT_EXT, 32, 32, 1|4, 2);
		$content = addslashes(file_get_contents(BABEL_PREFIX . '/htdocs/img/b/' . $weblog_id . '_s.' . BABEL_PORTRAIT_EXT));
		Image::vxUpdateBlogPortrait($weblog_id . '_s', $content, $db);
		$cache->remove('weblog_portrait_' . $weblog_id . '_s');
		Image::vxResize(BABEL_PREFIX . '/tmp/' . $weblog_id . $ext, BABEL_PREFIX . '/htdocs/img/b/' . $weblog_id . '_n.' . BABEL_PORTRAIT_EXT, 16, 16, 1|4, 2);
		$content = addslashes(file_get_contents(BABEL_PREFIX . '/htdocs/img/b/' . $weblog_id . '_n.' . BABEL_PORTRAIT_EXT));
		Image::vxUpdateBlogPortrait($weblog_id . '_n', $content, $db);
		$cache->remove('weblog_portrait_' . $weblog_id . '_n');
		$cache = null;
	}
	
	public static function vxUpdateBlogPortrait($filename, $content, $db) {
		$sql = "SELECT COUNT(*) FROM babel_weblog_portrait WHERE bgp_filename = '{$filename}'";
		$count = mysql_result(mysql_query($sql, $db), 0, 0);
		if ($count == 1) {
			$sql = "UPDATE babel_weblog_portrait SET bgp_content = '{$content}' WHERE bgp_filename = '{$filename}'";
			mysql_query($sql, $db);
			return true;
		} else {
			$sql = "INSERT INTO babel_weblog_portrait(bgp_filename, bgp_content) VALUE('{$filename}', '{$content}')";
			mysql_query($sql, $db);
			return true;
		}
	}
}

/* E Image class */

?>
