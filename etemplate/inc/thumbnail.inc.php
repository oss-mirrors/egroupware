<?php
	$GLOBALS['egw_info']['flags'] = array(
		'currentapp'	=>	'infolog',
		'noheader'	=>	true,
		'nonavbar'	=>	true
	);
	include ('../../header.inc.php');

	$file = $_GET['image'];
	$g_srcfile = $GLOBALS['egw_info']['server']['files_dir'] . $file;
	$g_dstfile = $GLOBALS['egw_info']['server']['temp_dir'] . $file;

	// Check for existing thumbnail
	if(file_exists($g_dstfile) && filemtime($g_dstfile) >= filemtime($g_srcfile)) {
		include $g_dstfile;
		return;
	}

	$thumbnail = get_thumbnail($file, true);

	if($thumbnail) {
		header('Content-Type: image/png');
		imagepng( $thumbnail );
		imagedestroy($thumbnail);
	}



	/**
	* Private function to get a thumbnail image for a linked image file.
	*
	* This function creates a thumbnail of the given image, if possible, and stores it in $GLOBALS['egw_info']['server']['temp_dir'].
	* Returns the image, or false if the file could not be thumbnailed.  Thumbnails are PNGs.
	*
	* @param array $file VFS File array to thumbnail
	* @return image or false
	*
	* @author Nathan Gray
	*/
	function get_thumbnail($file, $return_data = true) {
		$max_width = $max_height =	$GLOBALS['egw_info']['server']['link_list_thumbnail'];
		if($max_width == 0) {
			// thumbnailing disabled
			return false;
		} elseif( !gdVersion() ) {
			// GD disabled or not installed
			return false;
		}

		// Quality
		$g_imgcomp=55;

		$g_srcfile = $GLOBALS['egw_info']['server']['files_dir'] . $file;
		$g_dstfile = $GLOBALS['egw_info']['server']['temp_dir'] . $file;

		$dir_array = explode(DIRECTORY_SEPARATOR, $g_dstfile);
		array_pop($dir_array);
		$dest_dir = implode(DIRECTORY_SEPARATOR, $dir_array);
		@mkdir($dest_dir, 0700, true);

		if(file_exists($g_srcfile)) {
			$bolink = CreateObject('phpgwapi.bolink');
			$read = $bolink->vfs->acl_check(array(
				'string'	=>	$file,
				'relatives'	=>	array (),
				'operation'	=>	EGW_ACL_READ
			));
			if(!$read) {
				$im = @imagecreatetruecolor(strlen(lang('access not permitted')) * 7, 20);
				$text_color = imagecolorallocate($im, 233, 14, 91);
				imagestring($im, 2,5,3, lang('access not permitted'), $text_color);
				return $return_data ? $im : false;
			}

			$g_is=getimagesize($g_srcfile);
			if($g_is[0] < $max_width && $g_is[1] < $max_height) {
				$g_iw = $g_is[0];
				$g_ih = $g_is[1];
			} elseif(($g_is[0]-$max_width)>=($g_is[1]-$max_height)) {
				$g_iw=$max_width;
				$g_ih=($max_width/$g_is[0])*$g_is[1];
			} else {
				$g_ih=$max_height;
				$g_iw=($g_ih/$g_is[1])*$g_is[0];   
			}

			// Get mime type
			$info = $bolink->vfs->ls(array(
				'string'	=>	$file
			));
			list($type, $image_type) = explode('/', $info[0]['mime_type']);
			if($type != 'image') {
				return false;
			}

			switch ($image_type) {
				case 'png':
					$img_src = imagecreatefrompng($g_srcfile);
					break;
				case 'jpg':
				case 'jpeg':
					$img_src = imagecreatefromjpeg($g_srcfile);
					break;
				case 'gif':
					$img_src = imagecreatefromgif($g_srcfile);
					break;
				case 'bmp':
					$img_src = imagecreatefromwbmp($g_srcfile);
					break;
				default:
					return false;
			}
			if(!($gdVersion = gdVersion())) {
				return false;
			} elseif ($gdVersion >= 2) {
				$img_dst=imagecreatetruecolor($g_iw,$g_ih);
				imageSaveAlpha($img_dst, true);
				$trans_color = imagecolorallocatealpha($img_dst, 0, 0, 0, 127);
				imagefill($img_dst, 0, 0, $trans_color);
			} else {
				$img_dst = imagecreate($g_iw, $g_ih);
			}

			imagecopyresampled($img_dst, $img_src, 0, 0, 0, 0, $g_iw, $g_ih, $g_is[0], $g_is[1]);
			imagepng($img_dst, $g_dstfile);
			return $return_data ? $img_dst : $g_dstfile;
		} else {
			if(file_exists($g_dstfile)) {
				unlink($g_dstfile);
			}
			return false;
		}
	}

	/**
	* Get which version of GD is installed, if any.
	*
	* Returns the version (1 or 2) of the GD extension.
	* Off the php manual page, thanks Hagan Fox
	*/
	function gdVersion($user_ver = 0)
	{
		if (! extension_loaded('gd')) { return; }
		static $gd_ver = 0;

		// Just accept the specified setting if it's 1.
		if ($user_ver == 1) { $gd_ver = 1; return 1; }

		// Use the static variable if function was called previously.
		if ($user_ver !=2 && $gd_ver > 0 ) { return $gd_ver; }

		// Use the gd_info() function if possible.
		if (function_exists('gd_info')) {
			$ver_info = gd_info();
			preg_match('/\d/', $ver_info['GD Version'], $match);
			$gd_ver = $match[0];
			return $match[0];
		}

		// If phpinfo() is disabled use a specified / fail-safe choice...
		if (preg_match('/phpinfo/', ini_get('disable_functions'))) {
			if ($user_ver == 2) {
				$gd_ver = 2;
				return 2;
			} else {
				$gd_ver = 1;
				return 1;
			}
		}
		// ...otherwise use phpinfo().
		ob_start();
		phpinfo(8);
		$info = ob_get_contents();
		ob_end_clean();
		$info = stristr($info, 'gd version');
		preg_match('/\d/', $info, $match);
		$gd_ver = $match[0];
		return $match[0];
	}
?>
