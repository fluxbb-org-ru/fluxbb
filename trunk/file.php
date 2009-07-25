<?php


define('PUN_ROOT', './');
require PUN_ROOT.'include/common.php';

// Include file & image support functions
require PUN_ROOT.'include/file_func.php';

if ($pun_user['g_read_board'] == '0')
	message($lang_common['No view']);


$action = isset($_GET['action']) ? $_GET['action'] : 'browse';
if (!in_array($action, array('upload', 'download', 'info', 'browse')))
	message($lang_common['Bad request']);

// Load the post required language files
require PUN_ROOT.'lang/'.$pun_user['language'].'/post.php';
require PUN_ROOT.'lang/'.$pun_user['language'].'/file.php';

// Start with a clean slate
$errors = array();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$forum_id = isset($_GET['forum_id']) ? intval($_GET['forum_id']) : 0;


if ($action == 'upload')
{
	// Do we have permission to post?
	if ($pun_user['is_guest'] || !($pun_user['g_id'] == PUN_ADMIN || $pun_user['g_upload'] == '1'))
		message($lang_common['No permission']);

	// Did someone just hit "Submit"
	if (isset($_POST['form_sent']))
	{
		// Flood protection
		if ($pun_user['last_post'] != '' && (time() - $pun_user['last_post']) < $pun_user['g_post_flood'])
			$errors[] = $lang_post['Flood start'].' '.$pun_user['g_post_flood'].' '.$lang_post['flood end'];

		$title = pun_trim($_POST['title']);

		if (pun_strlen($title) > 70)
			$errors[] = $lang_post['Too long subject'];
		else if ($pun_config['p_subject_all_caps'] == '0' && strtoupper($title) == $title && !$pun_user['is_admmod'])
			$title = ucwords(strtolower($title));

		$result = upload($_FILES['req_file'], $title);
		if (is_array($result))
			$errors = array_merge($errors, $result);
		else
			redirect($pun_config['o_base_url'].'/file.php?action=info&amp;id='.$result, $lang_file['Upload redirect']);

	}

} // action == upload

else if ($action == 'info')
{

	if ($id <= 0)
		message($lang_common['Bad request']);

	$result = $db->query('SELECT fi.*, t.id AS tid, t.subject FROM '.$db->prefix.'files AS fi LEFT JOIN '.$db->prefix.'posts AS p ON p.id=fi.post_id LEFT JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id WHERE fi.id='.$id) or error('Unable to fetch file info', __FILE__, __LINE__, $db->error());
	$file_info = $db->fetch_assoc($result);

	if (empty($file_info))
		message($lang_common['Bad request']);

	$page_title = pun_htmlspecialchars($pun_config['o_board_title']).' / '.$lang_file['File'];;

	$file_title = (strlen($file_info['title']) > 0) ? $file_info['title'] : $file_info['filename'];
	$file_title = str_replace(array('[', ']', '/'), '-', $file_title);

	// If image
	if ($file_info['width'] > 0)
	{
		function preview_id_to_link($item)
		{
			global $pun_config, $id, $cur_size;

			list($preview_id, $width, $height) = $item;
			if ($cur_size != $preview_id)
				$text = '<a href="'.$pun_config['o_base_url'].'/file.php?action=info&amp;id='.$id.'&amp;size='.$preview_id.'">'.$preview_id.'</a>';
			else
				$text = '<strong>' . $preview_id . '</strong>';
			return $text . ' (' . $width . 'x' . $height .')';
		}

		$cur_size = isset($_GET['size']) ? $_GET['size'] : 'square';

		$keys_with_original = $keys = array_keys($previews);
		$keys_with_original[] = 'original';
		if (!in_array($cur_size, $keys_with_original))
			message($lang_common['Bad request']);

		$preview_items = array();
		foreach ($keys as $preview_id)
		{
			$tmp = preview_metrix($preview_id, $file_info['width'], $file_info['height']);
			if (is_null($tmp))
			{
				$preview_items[] = array('original', $file_info['width'], $file_info['height']);
				break;
			}
			$preview_items[] = array($preview_id, $tmp[0], $tmp[1]);
		}

		if (is_null(preview_metrix($cur_size, $file_info['width'], $file_info['height'])))
			$cur_size = 'original';
		$preview_links = array_map('preview_id_to_link', $preview_items);

		$thmb_uri = preview_check($cur_size, $id, PUN_ROOT.$file_info['location'], $file_info['mime'], $file_info['width'], $file_info['height']);
		$bbcode = '[img='.$file_title.']'.$thmb_uri.'[/img]';
	}
	else
		$bbcode = '[url='.$pun_config['o_base_url'].'/file.php?action=download&id='.$id.']'.$file_title.'[/url]';

} // action == info

else if ($action == 'download')
{
	$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

	if ($id <= 0)
		message($lang_common['Bad request']);

	download($id);
	exit();

} // action == download

else if ($action == 'browse')
{
	if ($forum_id == 0)
	{
		$attached = false;

		$fields = 'fi.*, -1 AS cid, \'---\' AS cat_name, -1 AS fid, \'---\' AS forum_name, -1 AS tid, \'---\' AS subject';

		$sql =	'SELECT %s '.
				'FROM '.$db->prefix.'files AS fi '.
				'WHERE (fi.post_id=0)';

		$order = '';
	}
	else
	{
		$attached = true;

		$fields = 'fi.*, c.id AS cid, c.cat_name, f.id AS fid, f.forum_name, t.id AS tid, t.subject';

		$sql =	'SELECT %s '.
				'FROM '.	  $db->prefix.'files AS fi '.
				'INNER JOIN '.$db->prefix.'posts AS p ON fi.post_id=p.id '.
				'INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id '.
				'INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id '.
				'INNER JOIN '.$db->prefix.'categories AS c ON c.id=f.cat_id '.
				'LEFT JOIN '. $db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id) AND (fp.group_id=2) '.
				'WHERE (fp.read_forum IS NULL OR fp.read_forum=1)';

		if ($forum_id > 0)
			$sql .= ' AND (f.id='.$forum_id.')';

		$order = ' ORDER BY c.disp_position, c.id, f.disp_position, t.posted DESC, fi.filename';
	}

	if ($user_id > 0)
	{
		$sql .= ' AND (fi.poster_id='.$user_id.')';
	}


	if ($pun_user['g_upload'] == '1')
		$post_link = '<a href="file.php?action=upload">'.$lang_file['Upload'].'</a>';
	else
		$post_link = '&nbsp;';

	// Get count of records
	$result = $db->query(sprintf($sql, 'COUNT(*)')) or error('Unable to fetch file count', __FILE__, __LINE__, $db->error());
	list($num_files) = $db->fetch_row($result);

	// Determine the topic offset (based on $_GET['p'])
	$num_pages = ceil($num_files / $pun_user['disp_topics']);

	$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : intval($_GET['p']);
	$start_from = $pun_user['disp_topics'] * ($p - 1);

	// Generate paging links
	$paging_links = $lang_common['Pages'].': '.paginate($num_pages, $p, 'file.php?action=browse&amp;forum_id='.$forum_id.($user_id!=0?'&amp;user_id='.$user_id:''));

}


// Prepare breadcrumbs and footer
if ($action == 'browse' || $action == 'info' || ($action == 'upload' && !isset($_POST['form_sent'])))
{
	if ($action == 'upload')
		$user_id = $pun_user['id'];

	if ($action == 'info')
	{
		$user_id = $file_info['poster_id'];
		$forum_id = ($file_info['post_id'] == 0) ? 0 : -1;
	}

	$crumbs = array();
	$crumbs[] = '<a href="index.php">'.$lang_common['Index'].'</a>';

	if ($user_id < 0)
		message($lang_common['Bad request']);
	else if ($user_id == 0)
		$crumbs[] = '<a href="file.php?action=browse">'.$lang_file['All users'].'</a>';
	else if ($user_id == $pun_user['id'])
		$crumbs[] = '<a href="file.php?action=browse&amp;user_id='.$user_id.'">'.sprintf($lang_file['Files of'], pun_htmlspecialchars($pun_user['username'])).'</a>';
	else
	{
		$result = $db->query('SELECT username FROM '.$db->prefix.'users WHERE id='.$user_id) or error('Unable to fetch username', __FILE__, __LINE__, $db->error());
		if (!$db->num_rows($result))
			message($lang_common['Bad request']);
		list($tmp) = $db->fetch_row($result);
		$crumbs[] = '<a href="file.php?action=browse&amp;user_id='.$user_id.'">'.sprintf($lang_file['Files of'], pun_htmlspecialchars($tmp)).'</a>';
	}

	if ($forum_id == 0)
		$crumbs[] = $lang_file['Unattached files'];
	else
		$crumbs[] = $lang_file['Attached files'];
}
$footer_style = 'file';

if ($action == 'upload')
{
	// action == upload, form not sent or was errors

	$page_title = pun_htmlspecialchars($pun_config['o_board_title']).' / '.$lang_file['Post new file'];;
	$required_fields = array('req_file' => $lang_file['File']);
	$focus_element = array('post', 'req_file');

	require PUN_ROOT.'header.php';
?>
<div class="linkst">
	<div class="inbox">
		<ul><li><?php echo implode('</li><li>&nbsp;&raquo;&nbsp;', $crumbs) ?></a></li></ul>
		<div class="clearer"></div>
	</div>
</div>

<?php

	// If there are errors, we display them
	if (!empty($errors))
	{

?>
<div id="posterror" class="block">
	<h2><span><?php echo $lang_post['Post errors'] ?></span></h2>
	<div class="box">
		<div class="inbox">
			<p><?php echo $lang_post['Post errors info'] ?></p>
			<ul>
<?php

		while (list(, $cur_error) = each($errors))
			echo "\t\t\t\t".'<li><strong>'.$cur_error.'</strong></li>'."\n";
?>
			</ul>
		</div>
	</div>
</div>

<?php

	}


	$cur_index = 1;

?>
<div class="blockform">
	<h2><span><?php echo $lang_file['Post new file'] ?></span></h2>
	<div class="box">
		<form id="post" method="post" action="file.php?action=upload" onsubmit="return process_form(this)" enctype="multipart/form-data">
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_file['Post file legend'] ?></legend>
					<div class="infldset txtarea">
						<input type="hidden" name="form_sent" value="1" />
<?php if (defined('MAX_FILE_SIZE')): ?>
						<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MAX_FILE_SIZE ?>" />
<?php endif; ?>						<label><strong><?php echo $lang_file['File'] ?></strong><em class="req-text">*</em><br /><input type="file" name="req_file" size="70"  tabindex="<?php echo $cur_index++ ?>" /><br /></label>
						<label><strong><?php echo $lang_file['Title'] ?></strong><br /><input class="longinput" type="text" name="title" value="<?php if (isset($_POST['title'])) echo pun_htmlspecialchars($title); ?>" size="80" maxlength="70" tabindex="<?php echo $cur_index++ ?>" /><br /></label>
					</div>
				</fieldset>
			</div>
			<p><input type="submit" name="submit" value="<?php echo $lang_common['Submit'] ?>" tabindex="<?php echo $cur_index++ ?>" accesskey="s" /><a href="javascript:history.go(-1)"><?php echo $lang_common['Go back'] ?></a></p>
		</form>
	</div>
</div>

<?php

	require PUN_ROOT.'footer.php';

}

else if ($action == 'info')
{
	$page_title = pun_htmlspecialchars($pun_config['o_board_title'].' / '.$lang_file['File'].' '.$file_info['filename']);

	require PUN_ROOT.'header.php';

?>
<div class="linkst">
	<div class="inbox">
		<ul><li><?php echo implode('</li><li>&nbsp;&raquo;&nbsp;', $crumbs) ?></a></li></ul>
		<div class="clearer"></div>
	</div>
</div>

<div id="viewprofile" class="block">
	<h2><span><?php echo $lang_file['Info'] ?></span></h2>
	<div class="box">
		<div class="fakeform">
			<div class="inform">
				<fieldset>
				<legend><?php echo ' '.pun_htmlspecialchars($file_title).' ' ?></legend>
				<div class="infldset" style="position: relative">
					<dl>
					<dt><?php echo $lang_file['File'] ?>: </dt><dd><?php echo $file_info['filename'] ?></dd>
<?php if ($file_info['tid'] > 0): ?>
					<dt><?php echo $lang_common['Topic'] ?>: </dt><dd><a href="viewtopic.php?pid=<?php echo $file_info['post_id'].'#p'.$file_info['post_id'] ?>"><?php echo $file_info['subject'] ?></a></dd>
<?php endif; ?>
					<dt><?php echo $lang_file['Uploaded'] ?>: </dt><dd><?php echo format_time($file_info['posted']) ?> <?php echo $lang_common['by'] ?> <a href="profile.php?id=<?php echo $file_info['poster_id'] ?>"><?php echo pun_htmlspecialchars($file_info['poster']) ?></a></dd>
<?php if ($file_info['width'] > 0): ?>
					<dt><?php echo $lang_file['Mime-type'] ?>: </dt><dd><?php echo $file_info['mime'] ?>, <?php echo $lang_file['size'] ?>: <?php echo $file_info['width'].'x'.$file_info['height'] ?> pixels</dd>
					<dt><?php echo $lang_file['Preview'] ?>: </dt><dd><?php echo implode(' | ', $preview_links) ?>
					<br class="clearb" /><img src="<?php echo $thmb_uri ?>" /></dd>
<?php else: ?>
					<dt><?php echo $lang_file['Mime-type'] ?>: </dt><dd><?php echo $file_info['mime'] ?></dd>
<?php endif; ?>
					<dt><?php echo $lang_file['File size'] ?>: </dt><dd><?php echo $file_info['filesize'] ?> bytes</dd>
					<dt><?php echo $lang_file['Download'] ?>: </dt><dd><a rel="nofollow" href="file.php?action=download&id=<?php echo $id ?>"><?php echo $lang_file['link'] ?></a></dd>
					<br class="clearb" />
					<dt><?php echo $lang_common['BBCode'] ?>: </dt><dd><input type="text" size="80" name="bbcode" value="<?php echo $bbcode ?>" /></dd>
				</div>
				</fieldset>
			</div>
		</div>
	</div>
</div>

<?php

	require PUN_ROOT.'footer.php';

}

else if ($action == 'browse')
{
	$page_title = pun_htmlspecialchars($pun_config['o_board_title'].' / '.$lang_file['Files']);

	require PUN_ROOT.'header.php';

?>
<div class="linkst">
	<div class="inbox">
		<p class="pagelink conl"><?php echo $paging_links ?></p>
		<p class="postlink conr"><?php echo $post_link ?></p>
		<ul><li><?php echo implode('</li><li>&nbsp;&raquo;&nbsp;', $crumbs) ?></a></li></ul>
		<div class="clearer"></div>
	</div>
</div>

<?php

	$sql = sprintf($sql, $fields) . $order;
	$result = $db->query($sql) or error('Unable to fetch file records', __FILE__, __LINE__, $db->error());

	// If there are files on this condition.
	if ($db->num_rows($result))
	{
		$cur_category = 0;
		$cur_forum = 0;
		$cur_topic = 0;
		while ($cur_file = $db->fetch_assoc($result))
		{
			if ($cur_file['cid'] != $cur_category)	// A new category since last iteration?
			{
				if ($cur_category != 0)
				{

?>
			</body>
			</table>
		</div>
	</div>
</div>
<?php

				}
				//print_r($cur_file['cat_name']); die();

?>
<div class="blocktable">
	<h2><span><?php echo ($cur_file['cid'] > 0) ? pun_htmlspecialchars($cur_file['cat_name']) : $lang_file['Unattached files'] ?></span></h2>
	<div class="box">
		<div class="inbox">
<?php

				$cur_category = $cur_file['cid'];
			}

			if ($cur_file['fid'] != $cur_forum)	// A new forum since last iteration?
			{
				if ($cur_forum != 0)
				{

?>
			</tbody>
			</table>
<?php

				}
?>
			<table cellspacing="0">
			<thead>
				<tr>
<?php if ($cur_file['fid'] > 0): ?>			<td colspan="4""><?php echo $lang_common['Forum'] ?>: <strong><a href="viewforum.php?id=<?php echo $cur_file['fid'] ?>"><?php echo pun_htmlspecialchars($cur_file['forum_name']) ?></a></strong></td>
<?php endif; ?>
				</tr>
				<tr>
					<th class="tcl" scope="col"><?php echo $lang_file['File'].' / '.$lang_file['Title'] ?></th>
					<th class="tc2" scope="col"><?php echo $lang_file['Mime-type'] ?></th>
					<th class="tc3" scope="col"><?php echo $lang_file['Downloads'] ?></th>
					<th class="tcr" scope="col"><?php echo $lang_file['Uploaded'].' / '.$lang_file['Poster']?></th>
				</tr>
			</thead>
			<tbody>
<?php

				$cur_forum = $cur_file['fid'];
			}

			if ($cur_file['tid'] != $cur_topic)	// A new topic since last iteration?
			{
				if ($cur_file['tid'] > 0)
				{
?>
				<tr>
					<td colspan="4">
						<div class="tclcon">
							<h3><?php echo $lang_common['Topic'] ?>: <a href="viewtopic.php?id=<?php echo $cur_file['tid'] ?>"><?php echo pun_htmlspecialchars($cur_file['subject']) ?></a></h3>
						</div>
					</td>
				</tr>
<?php
				}

				$cur_topic = $cur_file['tid'];
			}

?>
				<tr>
					<td class="tcl">
						<div class="icon"><div class="nosize"><!-- --></div></div>
						<div class="tclcon">
						<a href="file.php?action=info&amp;id=<?php echo $cur_file['id'] ?>"><?php echo pun_htmlspecialchars($cur_file['filename']) ?></a>
						<p><?php echo pun_htmlspecialchars($cur_file['title']) ?></p>
						</div>
					</td>
					<td class="tc2">
						<?php echo $cur_file['mime'] ?>
					</td>
					<td class="tc3">
						<?php echo $cur_file['downloads'] ?>
					</td>
					<td class="tcr">
						<?php echo format_time($cur_file['posted']) ?><br />
						<a href="profile.php?id=<?php echo $cur_file['poster_id'] ?>"><?php echo pun_htmlspecialchars($cur_file['poster']) ?></a>
					</td>
				</tr>
<?php

		} // while

?>
			</tbody>
			</table>
		</div>
	</div>
</div>
<?php

	}
	else
	{
?>
<div class="block">
	<h2><span><?php echo $lang_file['No files'] ?></span></h2>
	<div class="box">
		<div class="inbox">
			<p><?php echo $lang_file['No files'] ?></td>
		</div>
	</div>
</div>
<?php
	}

?>

<div class="linksb">
	<div class="inbox">
		<p class="postlink conr"><?php echo $post_link ?></p>
		<p class="pagelink conl"><?php echo $paging_links ?></p>
		<ul><li><?php echo implode('</li><li>&nbsp;&raquo;&nbsp;', $crumbs) ?></a></li></ul>
		<div class="clearer"></div>
	</div>
</div>

<?php

	require PUN_ROOT.'footer.php';
}
