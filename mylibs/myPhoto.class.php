<?php

class myPhoto {

	//不支持bmp
	function makeThumb($srcFile, $dstFile, $thumb_size, $quality=85, $cut = 0, $addext=false, $wm=null) {

		if (!is_file($srcFile))
			return false;

		if ($wm) {
			$wm_mode = @$wm['wm_mode'];
			$wm_text = @$wm['wm_text'];
			$wm_size = @$wm['wm_size'];
			$wm_photo = @$wm['wm_photo'];
			$wm_rand = @$wm['wm_rand'];
		}
		else {
			$wm_mode = '';
			$wm_text = '';
			$wm_size = 8;
			$wm_photo = '';
			$wm_rand = false;
		}

		list($dstW, $dstH) = explode('x', $thumb_size);

		$wm_alpha = 30; //水印透明度
		$wm_angle = 0; //水印文字角度
		$wm_x = 0;  //水印x坐标
		$wm_y = 0;  //水印y坐标
		$wm_color = "#FFFFFF"; //水印颜色
		$wm_fontfile = MYLIBS . "font" . DS . "Tahoma.ttf"; //水印字体文件


		$wm_photo = MYLIBS . "water_photo" . DS . $wm_photo; //水印文件
		$color = '';
		$im = '';
		$rvl = '';

		$dstFile = str_replace('\\', '/', $dstFile);
		mkdirs($dstFile);
		if ($addext) {
			$path_info = pathinfo($dstFile);
			$file_arr = explode('.', $path_info['basename']);
			array_pop($file_arr);
			$dstFile = $path_info['dirname'] . '/' . implode($file_arr, '.') . $addext . '.' . $path_info['extension'];
		}
		$im = $this->_createIm($srcFile);
		if (!$im)
			return false;

		$srcW = ImageSX($im);
		$srcH = ImageSY($im);
		$rvl = $dstW;
		if ($dstW > $srcW)
			$rvl = $srcW;

		$fdstW = $srcW;
		$fdstH = $srcH;
		$srcX = 0;
		$srcY = 0;

		if ($cut == 0) {
			if ($fdstH > $dstH || $fdstW > $dstW) {
				//判断比例
				if ($srcW * $dstH > $srcH * $dstW) {
					$fdstH = round($srcH * $dstW / $srcW);
					$fdstW = $dstW;
				}
				else {
					$fdstW = round($srcW * $dstH / $srcH);
					$fdstH = $dstH;
				}
			}
		}
		else {

			if ($fdstH > $dstH || $fdstW > $dstW) {

				//判断比例
				if ($srcW * $dstH > $srcH * $dstW) {

					$w = $srcH * $dstW / $dstH;
					$srcX = floor(($srcW - $w) / 2);
					$srcW = $w;
				}
				else {
					$h = $srcW * $dstH / $dstW;
					$srcY = floor(($srcH - $h) / 2);
					$srcH = $h;
				}
			}

			$fdstH = $dstH;
			$fdstW = $dstW;
		}

		$ni = ImageCreateTrueColor($fdstW, $fdstH);
		$white = ImageColorAllocate($ni, 255, 255, 255);

		imagefilledrectangle($ni, 0, 0, $dstW, $dstH, $white); // 填充背景色
		imagecopyresampled($ni, $im, 0, 0, $srcX, $srcY, $fdstW, $fdstH, $srcW, $srcH);

		//加水印

		if ($wm_mode == 'text') {
			if ($wm_text) {
				$text_position = array(array('x' => 5, 'y' => $fdstH - $wm_size),
					array('x' => 5, 'y' => 5 + $wm_size),
					array('x' => $fdstW - $wm_size * strlen($wm_text) + $wm_size, 'y' => 5 + $wm_size),
					array('x' => $fdstW - $wm_size * strlen($wm_text) + $wm_size, 'y' => $fdstH - $wm_size));

				if (!$wm_rand) {
					$wm_x = $text_position[0]['x'];
					$wm_y = $text_position[0]['y'];
				}
				else {
					$round = rand(0, 3);
					$wm_x = $text_position[$round]['x'];
					$wm_y = $text_position[$round]['y'];
				}

				if (preg_match("/([a-f0-9][a-f0-9])([a-f0-9][a-f0-9])([a-f0-9][a-f0-9])/i", $wm_color, $color)) {
					$red = hexdec($color[1]);
					$green = hexdec($color[2]);
					$blue = hexdec($color[3]);
				}
				$wm_color = imagecolorallocatealpha($ni, $red, $green, $blue, $wm_alpha);
				imagettftext($ni, $wm_size, $wm_angle, $wm_x, $wm_y, $wm_color, $wm_fontfile, $wm_text);
			}
		}
		elseif ($wm_mode == 'photo') {
			//设定混合模式
			imagealphablending($im, true);
			//读取水印文件
			if (strpos($wm_photo, 'gif') !== false)
				$im_wm = imagecreatefromgif($wm_photo);
			if (strpos($wm_photo, 'png') !== false)
				$im_wm = imagecreatefrompng($wm_photo);
			if (strpos($wm_photo, 'bmp') !== false)
				$im_wm = imagecreatefrombmp ($wm_photo);
			$waterw = imagesx($im_wm); //取得水印图片的宽
			$waterh = imagesy($im_wm); //取得水印图片的高

			$position = array(array('x' => 5, 'y' => $fdstH - $waterh - 5),
				array('x' => 5, 'y' => $waterh - 5),
				array('x' => $fdstW - $waterw - 5, 'y' => $waterh - 5),
				array('x' => $fdstW - $waterw - 5, 'y' => $fdstH - $waterh - 5));

			if (!$wm_rand) {
				$wimgx = $position[0]['x'];
				$wimgy = $position[0]['y'];
			}
			else {
				$round = rand(0, 3);
				$wimgx = $position[$round]['x'];
				$wimgy = $position[$round]['y'];
			}

			//拷贝水印到目标文件
			imagecopy($ni, $im_wm, $wimgx, $wimgy, 0, 0, $waterw, $waterh);
		}

		$data = exif_imagetype($srcFile);

		switch ($data) {
			case IMAGETYPE_GIF:
				if (!function_exists('imagegif'))
					die("No GIF image support in this PHP server");
				imagegif($ni, $dstFile);
				break;

			case IMAGETYPE_JPEG:
				if (!function_exists('imagejpeg'))
					die("No JPEG image support in this PHP server");
				imagejpeg($ni, $dstFile, $quality);
				break;

			case IMAGETYPE_PNG:
				if (!function_exists('imagepng'))
					die("No PNG image support in this PHP server");
				imagepng($ni, $dstFile);
				break;
		}

		imagedestroy($im);
		imagedestroy($ni);
		return true;
	}

	//支持bmp(统一转为jpg)
	function makeThumbMagick($srcFile, $dstFile, $thumb_size, $quality=85, $cut=0) {

		list($dstW, $dstH) = explode('x', $thumb_size);
		list($srcW, $srcH, $type, $attr) = getimagesize($srcFile);

		@unlink($dstFile);

		//0 - 长和宽均超过目标值   1 - 只有一方超过目标值
		$cut_mode = 0;
		$is_width = false;
		if ($srcH > $dstH && $srcW > $dstW) {

			//判断比例
			if ($srcW * $dstH > $srcH * $dstW) {
				//图片比较宽
				$is_width = true;
			}
		}
		elseif ($srcH > $dstH || $srcW > $dstW) {

			$cut_mode = 1;
			if ($srcW > $dstW) {
				$is_width = true;
			}
			else {
				$is_width = false;
			}
		}
		else {
			//拷贝源图
			system("convert $srcFile $dstFile");
			return;
		}
		if (!$cut) {
			if ($is_width)
				system("convert -quality {$quality} -thumbnail " . $dstW . "x {$srcFile} {$dstFile}");
			else
				system("convert -quality {$quality} -thumbnail x" . $dstH . " {$srcFile} {$dstFile}");
		}else {

			if ($cut_mode == 0) {
				if ($is_width) {
					//居中截图逻辑
					system("convert -thumbnail x" . ($dstH + 1) . " {$srcFile} {$dstFile}");
					list($w, $h, $type, $attr) = GetImageSize($srcFile);
					$offset = ($dstW - $w) / 2;
					if ($offset < 0)
						$offset = 0;
					system("convert -quality {$quality} -crop {$thumb_size}+{$offset}+0 {$dstFile} {$dstFile}");
				}else {
					//居中截图逻辑
					list($w, $h, $type, $attr) = GetImageSize($srcFile);
					$offset = ($dstH - $h) / 2;
					if ($offset < 0)
						$offset = 0;
					system("convert -thumbnail " . ($dstW + 1) . "x {$srcFile} {$dstFile}");
					system("convert -quality {$quality} -crop {$thumb_size}+0+{$offset} {$dstFile} {$dstFile}");
				}
			}else {
				if ($is_width) {
					$offset = ($srcW - $dstW) / 2;

					system("convert -quality {$quality} -crop {$thumb_size}+{$offset}+0 {$srcFile} {$dstFile}");
				}
				else {
					$offset = ($srcH - $dstH) / 2;
					system("convert -quality {$quality} -crop {$thumb_size}+0+{$offset} {$srcFile} {$dstFile}");
				}
			}
		}
	}

	//处理圆角
	function rounded_corner($image, $size) {

		$topleft = true;
		$bottomleft = true;
		$bottomright = true;
		$topright = true;
		$corner_source = imagecreatefrompng('rounded_corner.png');
		$corner_width = imagesx($corner_source);
		$corner_height = imagesy($corner_source);
		$corner_resized = ImageCreateTrueColor($this->corner_radius, $this->corner_radius);
		ImageCopyResampled($corner_resized, $corner_source, 0, 0, 0, 0, $this->corner_radius, $this->corner_radius, $corner_width, $corner_height);
		$corner_width = imagesx($corner_resized);
		$corner_height = imagesy($corner_resized);
		$white = ImageColorAllocate($image, 255, 255, 255);
		$black = ImageColorAllocate($image, 0, 0, 0);

		//顶部左圆角
		if ($topleft == true) {
			$dest_x = 0;
			$dest_y = 0;
			imagecolortransparent($corner_resized, $black);
			imagecopymerge($image, $corner_resized, $dest_x, $dest_y, 0, 0, $corner_width, $corner_height, 100);
		}

		//下部左圆角
		if ($bottomleft == true) {
			$dest_x = 0;
			$dest_y = $size - $corner_height;
			$rotated = imagerotate($corner_resized, 90, 0);
			imagecolortransparent($rotated, $black);
			imagecopymerge($image, $rotated, $dest_x, $dest_y, 0, 0, $corner_width, $corner_height, 100);
		}

		//下部右圆角
		if ($bottomright == true) {
			$dest_x = $size - $corner_width;
			$dest_y = $size - $corner_height;
			$rotated = imagerotate($corner_resized, 180, 0);
			imagecolortransparent($rotated, $black);
			imagecopymerge($image, $rotated, $dest_x, $dest_y, 0, 0, $corner_width, $corner_height, 100);
		}

		//顶部右圆角
		if ($topright == true) {
			$dest_x = $size - $corner_width;
			$dest_y = 0;
			$rotated = imagerotate($corner_resized, 270, 0);
			imagecolortransparent($rotated, $black);
			imagecopymerge($image, $rotated, $dest_x, $dest_y, 0, 0, $corner_width, $corner_height, 100);
		}
		$image = imagerotate($image, $this->angle, $white);
		return $image;
	}

	//不支持bmp
	function mockup($srcFile, $maskFile='', $mockupObj=array(), $size) {

		require MYCONFIGS . 'font.php';
		$srcFile = ROOT . DS . APP_DIR . DS . 'webroot' . $srcFile;
		if ($maskFile)
			$maskFile = ROOT . DS . APP_DIR . DS . 'webroot' . $maskFile;

		$dstSrcFile = '/tmp/' . md5($srcFile) . '_src';
		$dstFile = '/tmp/' . md5($srcFile);

		//$this->makeThumbMagick($srcFile, $dstSrcFile, $size, 90, 1); //将源图缩减到指定尺寸

		$iback = $this->_createIm($srcFile);
		unlink($dstSrcFile);

		if (!$iback)
			return False;

		if ($maskFile) {
			//$this->makeThumbMagick($maskFile, $dstSrcFile, $size, 90, 1); //将mask缩减到指定尺寸

			$imask = $this->_createIm($maskFile);
			unlink($dstSrcFile);
		}

		//加mockup
		if (!$mockupObj)
			$mockupObj = array();
		foreach ($mockupObj as $obj) {

			if ($obj['type'] == 'text') {

				$wm_alpha = 0; //文字透明度
				$wm_angle = 0; //文字角度
				$wm_x = $obj['left'] + @$obj['left_fix'];  //x坐标
				$wm_y = $obj['top'] + @$obj['top_fix'];  //y坐标
				$wm_color = $obj['color']; //颜色

				$wm_fontfile = ROOT . DS . MYLIBS . "font" . DS . $FONT[$obj['font']] . ".ttf"; //字体文件

				$wm_text = $obj['text'];
				$wm_size = $obj['height'];

				if ($wm_text) {

					if (preg_match("/([a-f0-9][a-f0-9])([a-f0-9][a-f0-9])([a-f0-9][a-f0-9])/i", $wm_color, $color)) {
						$red = hexdec($color[1]);
						$green = hexdec($color[2]);
						$blue = hexdec($color[3]);
					}

					$itext = imagecreatetruecolor($obj['width'], $obj['height']);
					$wm_color = imagecolorallocatealpha($itext, $red, $green, $blue, $wm_alpha);
					//text mockup需要浮在model上面
					if($imask)
						imagettftext($imask, $wm_size, $wm_angle, $wm_x, $wm_y + $obj['height'], $wm_color, $wm_fontfile, $wm_text);
					else
						imagettftext($iback, $wm_size, $wm_angle, $wm_x, $wm_y + $obj['height'], $wm_color, $wm_fontfile, $wm_text);
				}
			}
			elseif ($obj['type'] == 'photo') {
				//设定混合模式
				imagealphablending($iback, true);

				//读取mockup图片
				$db = new MockupPhoto();
				$filepath = $db->field('filepath', array('id' => $obj['img_id']));
				$filepath = ROOT . DS . APP_DIR . DS . 'webroot' . $filepath;
				if (strpos($filepath, 'gif') !== false)
					$im_wm = imagecreatefromgif($filepath);
				if (strpos($filepath, 'png') !== false)
					$im_wm = imagecreatefrompng($filepath);
				if (strpos($filepath, 'jpg') !== false || strpos($filepath, 'jpeg') !== false)
					$im_wm = imagecreatefromjpeg($filepath);

				$waterw = imagesx($im_wm); //取得图片的宽
				$waterh = imagesy($im_wm); //取得图片的高
				//拷贝到目标文件
				imagecopyresampled($iback, $im_wm, $obj['left'] + @$obj['left_fix'], $obj['top'] + @$obj['top_fix'], 0, 0, $obj['width'], $obj['height'], $waterw, $waterh);
			}
		}

		$srcW = ImageSX($iback);
		$srcH = ImageSY($iback);

		//融合mask background
		if (@$imask) {
			imagealphablending($imask, true);
			imagesavealpha($imask, true);
			$trans_colour = imagecolorallocatealpha($imask, 0, 0, 0, 127);
			imagefill($imask, 0, 0, $trans_colour);

			imagecopy($iback, $imask, 0, 0, 0, 0, $srcW, $srcH);
		}

		//如果用户自己上传图片的尺寸不符合规格，则会按照实际比例缩放到mockup区域
		//list($tarW, $tarH) = explode('x', $size);//此处会导致变形
		$io = ImageCreateTrueColor($srcW, $srcH);

		//将背景设为白色
		$white = imagecolorallocate($io, 255, 255, 255);
		imagefill($io, 0, 0, $white);
		imagecopyresampled($io, $iback, 0, 0, 0, 0, $srcW, $srcH, $srcW, $srcH);

		imagepng($io, $dstFile);

		@imagedestroy($iback);
		@imagedestroy($imask);
		@imagedestroy($imockup);

		$this->makeThumbMagick($dstFile, $dstFile.'.jpg', $size, 85, 1);
		$content = file_get_contents($dstFile.'.jpg');
		//echo $content;die();
		unlink($dstFile.'.jpg');

		return $content;
	}

	function _createIm($filepath = '') {

		if (!@$filepath || !is_file(@$filepath))
			return;
		$data = exif_imagetype($filepath);

		switch ($data) {
			case IMAGETYPE_GIF:
				if (!function_exists('ImageCreateFromGIF'))
					die("No GIF image support in this PHP server");
				$im = ImageCreateFromGIF($filepath);
				break;
			case IMAGETYPE_JPEG:
				if (!function_exists('ImageCreateFromJPEG'))
					die("No JPEG image support in this PHP server");
				$im = ImageCreateFromJPEG($filepath);
				break;
			case IMAGETYPE_PNG:
				if (!function_exists('ImageCreateFromPNG'))
					die("No PNG image support in this PHP server");
				$im = ImageCreateFromPNG($filepath);
				break;
			case IMAGETYPE_WBMP:
				if (!function_exists('imageCreateFromWBMP'))
					die("No wbmp image support in this PHP server");
				$im = imageCreateFromWBMP($filepath);
				break;
		}

		return $im;
	}

}

?>