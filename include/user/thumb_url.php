<?php

if (!preg_match('#img/thumb/(?<crop>c)?(?<width>\d+)x(?<height>\d+)/(?<id>\d+)\.jpg$#', $page_uri, $matches)) 
	fileNotFound();
extract($matches);

/*
require PUN_ROOT.'include/common.php';

$result = $db->query("SELECT fi.* FROM {$db->prefix}files AS fi WHERE fi.id={$id}") or error('Unable to fetch files', __FILE__, __LINE__, $db->error());
if ($db->num_rows($result) == 0) {
	header('HTTP/1.0 404 Not Found');
	die('File not found');
}
$row = $db->fetch_assoc($result);
*/
$src = "upload/{$id}.jpg";
$dst = "img/thumb/{$crop}{$width}x{$height}/{$id}.jpg";

if (!file_exists(PUN_ROOT.$dst)) {
	if (!is_dir(dirname($dst)))
		fileNotFound();
	set_time_limit(120);
	copy(PUN_ROOT.'img/thumb/wrong.jpg', PUN_ROOT.$dst);
	makeThumbnail(PUN_ROOT.$src, PUN_ROOT.$dst, intval($width), intval($height), !empty($crop));
}

header('Content-Type: image/jpeg'); 
readfile(PUN_ROOT.$dst);
exit();


function makeThumbnail($srcFile, $dstFile, $thumbWidth, $thumbHeight, $crop = FALSE)
{
	$rgb = 0xFFFFFF;
	$quality = 80;
	$size = @getimagesize($srcFile);
	$offsetX = $offsetY = 0;

	if ($size === FALSE) return FALSE;

	$format = strtolower(substr($size['mime'], strpos($size['mime'], '/') + 1));
	$icfunc = 'imagecreatefrom' . $format;
	if (!function_exists($icfunc)) return FALSE;

	$origImg = $icfunc($srcFile);
	if (($size[0] <= $thumbWidth) && ($size[1] <= $thumbHeight)) {
		// use original size
		$width  = $size[0];
		$height = $size[1];
	} else {
		$width  = $thumbWidth;
		$height = $thumbHeight;

		// calculate fit ratio
		$ratioX = $size[0] / $thumbWidth;
		$ratioY = $size[1] / $thumbHeight;

		if ($ratioX < $ratioY) {
			if ($crop) {
				$offsetY = ($size[1] - $thumbHeight * $ratioX) / 2;
				$size[1] = $thumbHeight * $ratioX;
			} else {
				$width  = $size[0] / $ratioY;
				$height = $thumbHeight;
			}
		} else {
			if ($crop) {
				$offsetX = ($size[0] - $thumbWidth * $ratioY) / 2;
				$size[0] = $thumbWidth * $ratioY;
			} else {
				$width  = $thumbWidth;
				$height = $size[1] / $ratioX;
			}
		}
	}

	$thumImg = imagecreatetruecolor($width, $height);
	imagefill($thumImg, 0, 0, $rgb);
	imagecopyresampled($thumImg, $origImg, 0, 0, $offsetX, $offsetY, $width, $height, $size[0], $size[1]);

	imagejpeg($thumImg, $dstFile, $quality);
	imagedestroy($origImg);
	imagedestroy($thumImg);

	return TRUE;
}
