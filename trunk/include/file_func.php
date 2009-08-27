<?php

// TO DO: pun these params to $pun_config
define('MAX_FILE_SIZE', 10485760);		// 10M
define('MAX_FILES_IN_FOLDER', 1000);	// rational limit for FAT
define('PREVIEW_QUALITY', 80);			// good quality
define('IMAGE_FILL_COLOR', 0xFFFFFF);	// white background
define('FILE_CHUNK', 10240);			// download by 10K parts


//
// Calculate width & height for given file in preview size
// Returns array($width, $height, $do_cut, $x_ratio, $y_ratio)
// or null if requested preview is too big
//
function preview_metrix($preview_id, $file_width, $file_height)
{	global $pun_config;

	$previews = unserialize($pun_config['f_previews']);
	if (!isset($previews[$preview_id]))
		return null;

	list($thumb_width, $thumb_height, $do_cut) = $previews[$preview_id];
	if (($file_width <= $thumb_width) && ($file_height <= $thumb_height))
		return null;
	else
	{
		// calculate fit ratio
		$x_ratio = $file_width / $thumb_width;
		$y_ratio = $file_height / $thumb_height;

		if ($do_cut)
		{
			$width  = min($file_width, $thumb_width);
			$height = min($file_height, $thumb_height);
		}
		else
		{
			if ($x_ratio < $y_ratio)
			{
				$width  = round($file_width / $y_ratio);
				$height = $thumb_height;
			}
			else
			{
				$width  = $thumb_width;
				$height = round($file_height / $x_ratio);
			}
		}
	}

	return array($width, $height, $do_cut, $x_ratio, $y_ratio);
}


//
// Preview URI
//
function preview($preview_id, $file_id)
{
	global $pun_config;

	$previews = unserialize($pun_config['f_previews']);
	if (!isset($previews[$preview_id]))
		return $pun_config['o_base_url'].'/file.php?action=download&amp;id='.$file_id;

	$dir = sprintf('%04d', intval($file_id / MAX_FILES_IN_FOLDER + 1));
	return $pun_config['o_base_url'].'/img/preview/'.$preview_id.'/'.$dir.'/'.$file_id.'.jpg';
}


//
// Build preview file (if needed)
// Returns preview URI
//
function preview_check($preview_id, $file_id, $src_file, $mime, $src_w, $src_h)
{	global $pun_config;

	$params = preview_metrix($preview_id, $src_w, $src_h);
	// Return link to original file, when requested preview is too big
	if (is_null($params))
		return preview('original', $file_id);

	list($dst_w, $dst_h, $do_cut, $x_ratio, $y_ratio) = $params;

	// Every sub-folder can contain no more than MAX_FILES_IN_FOLDER.
	$dir = sprintf('%04d', intval($file_id / MAX_FILES_IN_FOLDER + 1));
	if (!is_dir(PUN_ROOT.'img/preview/'.$preview_id.'/'.$dir))
	{
		@mkdir(PUN_ROOT.'img/preview/'.$preview_id.'/'.$dir);
		copy(PUN_ROOT.'img/index.html', PUN_ROOT.'img/preview/'.$preview_id.'/'.$dir.'/index.html');
	}
	$dst_file = PUN_ROOT.'img/preview/'.$preview_id.'/'.$dir.'/'.$file_id.'.jpg';

	if (file_exists($dst_file))
		return preview($preview_id, $file_id);

   	$src_x = $src_y = 0;
	if ($do_cut)
	{
		if ($x_ratio < $y_ratio)
		{
			$src_y = round(($src_h - $dst_h * $x_ratio) / 2);
			$src_h = $dst_h * $x_ratio;
		}
		else
		{			$src_x = round(($src_w - $dst_w * $y_ratio) / 2);
			$src_w = $dst_w * $y_ratio;
		}
    }

	// Load original image
	$format = strtolower(substr($mime, strpos($mime, '/')+1));
	$icfunc = 'imagecreatefrom' . $format;
	if (!function_exists($icfunc)) return false;
	$src_img = $icfunc($src_file);

	// Create preview image
	$dst_img = imagecreatetruecolor($dst_w, $dst_h);
	imagefill($dst_img, 0, 0, IMAGE_FILL_COLOR);
	imagecopyresampled($dst_img, $src_img, 0, 0, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

	// Save preview image
	imagejpeg($dst_img, $dst_file, PREVIEW_QUALITY);
	//flush();

	// Free resources
	imagedestroy($src_img);
	imagedestroy($dst_img);

	return preview($preview_id, $file_id);
}


//
// Upload file
// Returns new file id on success
// or array of error messages
//
function upload_file($file_request, $title)
{
	global $db, $pun_user, $lang_file;	static $max_id, $dir;

	$errors = array();

	if (empty($max_id))
	{
		// Select sub-folder to upload. Every sub-folder can contain no more than MAX_FILES_IN_FOLDER.
		$result = $db->query('SELECT MAX(id) FROM '.$db->prefix.'files') or error('Unable to fetch maximum file id', __FILE__, __LINE__, $db->error());
		list($max_id) = $db->fetch_row($result);
		$dir = sprintf('%04d', intval($max_id / MAX_FILES_IN_FOLDER + 1));
		if (!is_dir(PUN_ROOT. 'upload/'. $dir))
			@mkdir(PUN_ROOT. 'upload/'. $dir);
	}

	set_time_limit(60);
	switch ($file_request['error']) {
		case UPLOAD_ERR_OK:
			$filename = $file_request['name'];
			$filesize = $file_request['size'];

			while (true)
			{
				$name = substr(md5(mt_rand()), 16);
				$location = 'upload/'. $dir . '/' . $name . '.dat';
				if (!file_exists(PUN_ROOT.$location))
					break;
			}

			$mime = $file_request['type'];
			$width = $height = 0;
			if (strpos($mime, 'image/') === 0)
			{
				if($tmp = @getimagesize($file_request['tmp_name']))
				{
					$width = $tmp[0];
					$height = $tmp[1];
					$mime = $tmp['mime'];
				}
			}
			//else
			//	$mime = 'application/octet-stream';

			if (!move_uploaded_file($file_request['tmp_name'], PUN_ROOT.$location))
				$errors[] = $lang_file['Error not moved'];

			break;
		case UPLOAD_ERR_INI_SIZE:
			$errors[] = $lang_file['Error ini size'];
			break;
		case UPLOAD_ERR_FORM_SIZE:
			$errors[] = $lang_file['Error form size'];
			break;
		case UPLOAD_ERR_PARTIAL:
			$errors[] = $lang_file['Error partially'];
			break;
		case UPLOAD_ERR_NO_FILE:
			$errors[] = $lang_file['Error no file'];
			break;
		default:
			$errors[] = sprintf($lang_file['Error unknown'], $file_request['error']);
			break;
	}

	$now = time();

	// Did everything go according to plan?
	if (empty($errors))
	{
		// Create the file record
		$db->query('INSERT INTO '.$db->prefix.'files (poster, poster_id, poster_ip, title, posted, filename, filesize, mime, location, width, height) VALUES(\''.
			$db->escape($pun_user['username']).'\', '.
			$pun_user['id'].', \''.
			get_remote_address().'\', \''.
			$db->escape($title).'\', '.
			$now.', \''.
			$db->escape($filename).'\', '.
			$filesize.', \''.
			$mime.'\', \''.
			$location.'\', '.
			$width.', '.
			$height.')') or error('Unable to store files record', __FILE__, __LINE__, $db->error());
		return $db->insert_id();
	}

	return $errors;
}


//
// Delete files
// $file_ids is CSV or array
//
function delete_files($file_ids, $check = false)
{
	global $db;

	if (empty($file_ids))
		return;
	else if (is_array($file_ids))
		$file_ids = implode(',', $file_ids);

	$pun_config = $GLOBALS['pun_config'];
	$pun_user = $GLOBALS['pun_user'];
	$preview_ids = array_keys(unserialize($pun_config['f_previews']));

	// Prevent attack
	if ($check)
		$sql = 'SELECT a.id, a.location, a.poster_id, f.moderators, fp.upload FROM '.$db->prefix.'files AS a LEFT JOIN '.$db->prefix.'posts AS p ON p.id=a.post_id LEFT JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE a.id IN ('.$file_ids.')';
	else
		$sql = 'SELECT a.id, a.location, a.poster_id, \'\' AS moderators, 0 AS upload FROM '.$db->prefix.'files AS a WHERE a.id IN ('.$file_ids.')';
	$result = $db->query($sql) or error('Unable to fetch files to delete', __FILE__, __LINE__, $db->error());

	$file_ids = array();
	while(list($file_id, $location, $poster_id, $moderators, $upload) = $db->fetch_row($result))
	{
		if ($check)
			; // TO DO: check user privileges

		$file_ids[] = $file_id;
		// Remove file
		@unlink(PUN_ROOT.$location);
		// Remove previews
		foreach($preview_ids as $preview_id)
		{
			$preview_dir = sprintf('%04d', intval($file_id / MAX_FILES_IN_FOLDER + 1));
			@unlink(PUN_ROOT.'img/preview/'.$preview_id.'/'.$preview_dir.'/'.$file_id.'.jpg');
		}
	}

	// Delete records from table
	if (!empty($file_ids))
		$db->query('DELETE FROM '.$db->prefix.'files WHERE id IN ('.implode(',', $file_ids).')') or error('Unable delete files', __FILE__, __LINE__, $db->error());
}


//
// Delete files attached to posts
// $post_ids is CSV or array
//
function delete_post_files($post_ids, $check = false)
{
	global $db;

	if (empty($post_ids))
		return;
	else if (is_array($post_ids))
		$post_ids = implode(',', $post_ids);

	// Delete attached files
	$file_ids = array();
	$result = $db->query('SELECT id FROM '.$db->prefix.'files WHERE post_id IN('.$post_ids.')') or error('Unable to fetch files', __FILE__, __LINE__, $db->error());
	while ($row = $db->fetch_row($result))
		$file_ids[] = $row[0];
	if (!empty($file_ids))
		delete_files($file_ids, $check);
}


//
// Download file.
//
function download($id)
{	global $db, $pun_config, $pun_user;

	$result = $db->query('SELECT * FROM '.$db->prefix.'files WHERE id='.$id) or error('Unable to fetch file info', __FILE__, __LINE__, $db->error());
	$file_info = $db->fetch_assoc($result);

	if (empty($file_info))
	{		header ('HTTP/1.0 404 Not Found');
		echo '404 File not found';
		exit;
    }

	$can_download = true;

	if ($pun_user['g_read_board'] == '0')
	{
		$can_download = false;
	}
    else if ($pun_user['g_id'] != PUN_ADMIN)
    {
	    // For unattached file check user group permission
    	if ($file_info['post_id'] == '0')
	    {
			$can_download = $pun_user['g_download'] == '1';
	    }
	    // For attached files we have to check permissions for forum
		else
		{
			$result = $db->query('SELECT f.moderators, fp.download FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND (p.id='.$file_info['post_id'].')') or error('Unable to fetch forum permissions', __FILE__, __LINE__, $db->error());
			if (!$db->num_rows($result))
				// fp.read_forum=0
				$can_download = false;
			else
			{
				$cur_posting = $db->fetch_assoc($result);

				// Sort out who the moderators are and if we are currently a moderator (or an admin)
				$mods_array = ($cur_posting['moderators'] != '') ? unserialize($cur_posting['moderators']) : array();
				$is_admmod = ($pun_user['g_id'] == PUN_ADMIN || ($pun_user['g_moderator'] == '1' && array_key_exists($pun_user['username'], $mods_array))) ? true : false;

				$can_download = $is_admmod || (($cur_posting['download'] == '' && $pun_user['g_download'] == '1') || $cur_posting['download'] == '1');
			}
		}
	}

	if (!$can_download)
    {
		header ('HTTP/1.0 403 Forbidden');
		echo '403 Access denied';
		exit;
    }

	//Update download counter
	if ($pun_user['id'] != $file_info['poster_id'])
		$db->query('UPDATE '.$db->prefix.'files SET downloads=downloads+1 WHERE id='.$id) or error('Unable to update download counter', __FILE__, __LINE__, $db->error());

	// We don't need database anymore
	$db->end_transaction();
	$db->close();

	$disposition = $file_info['width'] > 0 ? 'inline' : 'attachment';

	header('HTTP/1.1 200 OK');
	header('Content-type: '.$file_info['mime']);
	header('Content-Disposition: '.$disposition.'; filename="'.$file_info['filename'].'";');
	header('Last-Modified: '.date('D, d M Y H:i:s T', $file_info['posted']));
	header('Content-Length: '.$file_info['filesize']);

	$file_handler = @fopen(PUN_ROOT.$file_info['location'], 'rb');
	@set_time_limit(0);
	while (!feof($file_handler) && !connection_status())
	{
		echo fread($file_handler, FILE_CHUNK);
		// Just in case flush the output buffer
		@ob_flush();
		@flush();
		// Prevent server high load
		//sleep(1);
	}
	fclose($file_handler);
}


//
// Produce valid class name for file link
//
function mime_to_class($mime)
{	return 'file '.str_replace('/',' ', $mime);
}


//
// Produce array of links from
//
function array_to_links($arr)
{
	$result = array();
	foreach ($arr as $item)
		$result[] = '<a href="file.php?action=info&amp;id='.$item['id'].'" title="'.pun_htmlspecialchars($item['title']=='' ? $item['filename'] : $item['title']).'" class="'.mime_to_class($item['mime']).'">'.pun_htmlspecialchars($item['filename']).'</a>';
	return $result;
}


//
// Extract file item from $_FILES['name'] structure
//
function file_item($name, $key)
{	return array(
		'name'		=> $_FILES[$name]['name'][$key],
		'type'		=> $_FILES[$name]['type'][$key],
		'tmp_name'	=> $_FILES[$name]['tmp_name'][$key],
		'error'		=> $_FILES[$name]['error'][$key],
		'size'		=> $_FILES[$name]['size'][$key]
	);
}