<?php
/***********************************************************************

  Copyright (C) 2005  Connor Dunn (Connorhd@mypunbb.com)

  This software is free software; you can redistribute it and/or modify it
  under the terms of the GNU General Public License as published
  by the Free Software Foundation; either version 2 of the License,
  or (at your option) any later version.

  This software is distributed in the hope that it will be useful, but
  WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston,
  MA  02111-1307  USA

************************************************************************/
// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
    exit;

// Tell admin_loader.php that this is indeed a plugin and that it is loaded
define('PUN_PLUGIN_LOADED', 1);
define('PLUGIN_VERSION',1.3);

if (file_exists(PUN_ROOT.'lang/'.$pun_user['language'].'/plugin_user_management.php')) {
	require PUN_ROOT.'lang/'.$pun_user['language'].'/plugin_user_management.php';
} else {
	require PUN_ROOT.'lang/English/plugin_user_management.php';
}

if (isset($_POST['prune']))
{
	// Make sure something was entered
	if ((trim($_POST['days']) == '') || trim($_POST['posts']) == '')
		message($lang_plug_um['Need settings']);

	$prune = ($_POST['prune_by'] == 1) ? 'u.registered' : 'u.last_visit';
	if ($_POST['verified'] != 2 && $_POST['admods_delete']) {
		$prune_sql = 'DELETE u FROM '.$db->prefix.'users AS u WHERE (u.num_posts < %s) AND ('.$prune.' < %s) AND (u.id > 2) AND (u.group_id NOT IN('.PUN_UNVERIFIED.','.PUN_ADMIN.','.PUN_GUEST.'))';
	} else {
		if ($_POST['verified'] == 1)
			$ver_cond = 'u.group_id NOT IN('.PUN_ADMIN.','.PUN_GUEST.')';
		elseif ($_POST['verified'] == 0)
			$ver_cond = 'u.group_id NOT IN('.PUN_UNVERIFIED.','.PUN_ADMIN.','.PUN_GUEST.')';
		else
			$ver_cond = 'u.group_id = '.PUN_UNVERIFIED;

		$prune_sql = 'DELETE u FROM '.$db->prefix.'users AS u LEFT JOIN rc3_groups AS g ON g.g_id=u.group_id WHERE (g.g_moderator=0) AND (u.num_posts < %s) AND ('.$prune.' < %s) AND (u.id > 2) AND ('.$ver_cond.')';
	}

	$user_time = time() - ($_POST['days'] * 86400);
	$result = $db->query(sprintf($prune_sql, intval($_POST['posts']), $user_time), true) or error('Unable to delete users', __FILE__, __LINE__, $db->error());
	$users_pruned = $db->affected_rows();
	message(sprintf($lang_plug_um['Pruning complete'], $users_pruned));
}
elseif (isset($_POST['add_user']))
{
	require PUN_ROOT.'lang/'.$pun_user['language'].'/prof_reg.php';
	require PUN_ROOT.'lang/'.$pun_user['language'].'/register.php';
	$username = pun_trim($_POST['username']);
	$email1 = mb_strtolower(trim($_POST['email']));
	$email2 = mb_strtolower(trim($_POST['email']));

	if ($_POST['random_pass'] == '1')
	{
		$password1 = random_pass(8);
		$password2 = $password1;
	}
	else
	{
		$password1 = trim($_POST['password']);
		$password2 = trim($_POST['password']);
	}

	// Convert multiple whitespace characters into one (to prevent people from registering with indistinguishable usernames)
	$username = preg_replace('#\s+#s', ' ', $username);

	// Validate username and passwords
	if (mb_strlen($username) < 2)
		message($lang_prof_reg['Username too short']);
	else if (pun_strlen($username) > 25)	// This usually doesn't happen since the form element only accepts 25 characters
	    message($lang_common['Bad request']);
	else if (mb_strlen($password1) < 4)
		message($lang_prof_reg['Pass too short']);
	else if ($password1 != $password2)
		message($lang_prof_reg['Pass not match']);
	else if (!strcasecmp($username, 'Guest') || !strcasecmp($username, $lang_common['Guest']))
		message($lang_prof_reg['Username guest']);
	else if (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $username))
		message($lang_prof_reg['Username IP']);
	else if ((strpos($username, '[') !== false || strpos($username, ']') !== false) && strpos($username, '\'') !== false && strpos($username, '"') !== false)
		message($lang_prof_reg['Username reserved chars']);
	else if (preg_match('#\[b\]|\[/b\]|\[u\]|\[/u\]|\[i\]|\[/i\]|\[color|\[/color\]|\[quote\]|\[quote=|\[/quote\]|\[code\]|\[/code\]|\[img\]|\[/img\]|\[url|\[/url\]|\[email|\[/email\]#i', $username))
		message($lang_prof_reg['Username BBCode']);

	// Check username for any censored words
	if ($pun_config['o_censoring'] == '1')
	{
		// If the censored username differs from the username
		if (censor_words($username) != $username)
			message($lang_register['Username censor']);
	}

	// Check that the username (or a too similar username) is not already registered
	$result = $db->query('SELECT username FROM '.$db->prefix.'users WHERE username=\''.$db->escape($username).'\' OR username=\''.$db->escape(preg_replace('/[^\w]/', '', $username)).'\'') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());

	if ($db->num_rows($result))
	{
		$busy = $db->result($result);
		message($lang_register['Username dupe 1'].' '.pun_htmlspecialchars($busy).'. '.$lang_register['Username dupe 2']);
	}


	// Validate e-mail
	require PUN_ROOT.'include/email.php';

	if (!is_valid_email($email1))
		message($lang_common['Invalid e-mail']);

	// Check if someone else already has registered with that e-mail address
	$dupe_list = array();

	$result = $db->query('SELECT username FROM '.$db->prefix.'users WHERE email=\''.$email1.'\'') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
	if ($db->num_rows($result))
	{
		while ($cur_dupe = $db->fetch_assoc($result))
			$dupe_list[] = $cur_dupe['username'];
	}

	$timezone = '0';
	$language = isset($_POST['language']) ? $_POST['language'] : $pun_config['o_default_lang'];

	$email_setting = intval(1);

	// Insert the new user into the database. We do this now to get the last inserted id for later use.
	$now = time();

	$intial_group_id = ($_POST['random_pass'] == '0') ? $pun_config['o_default_user_group'] : PUN_UNVERIFIED;
	$password_hash = pun_hash($password1);

	// Add the user
	$db->query('INSERT INTO '.$db->prefix.'users (username, group_id, password, email, email_setting, timezone, language, style, registered, registration_ip, last_visit) VALUES(\''.$db->escape($username).'\', '.$intial_group_id.', \''.$password_hash.'\', \''.$email1.'\', '.$email_setting.', '.$timezone.' , \''.$language.'\', \''.$pun_config['o_default_style'].'\', '.$now.', \''.get_remote_address().'\', '.$now.')') or error('Unable to create user', __FILE__, __LINE__, $db->error());
	$new_uid = $db->insert_id();

	// Should we alert people on the admin mailing list that a new user has registered?
	if ($pun_config['o_regs_report'] == '1')
	{
		$mail_subject = 'Alert - New registration';
		$mail_message = 'User \''.$username.'\' registered in the forums at '.$pun_config['o_base_url']."\n\n".'User profile: '.$pun_config['o_base_url'].'/profile.php?id='.$new_uid."\n\n".'-- '."\n".'Forum Mailer'."\n".'(Do not reply to this message)';

		pun_mail($pun_config['o_mailing_list'], $mail_subject, $mail_message);
	}

	// Must the user verify the registration or do we log him/her in right now?
	if ($_POST['random_pass'] == '1')
	{
		// Load the "welcome" template
		$mail_tpl = trim(file_get_contents(PUN_ROOT.'lang/'.$pun_user['language'].'/mail_templates/welcome.tpl'));

		// The first row contains the subject
		$first_crlf = strpos($mail_tpl, "\n");
		$mail_subject = trim(substr($mail_tpl, 8, $first_crlf-8));
		$mail_message = trim(substr($mail_tpl, $first_crlf));
		$mail_subject = str_replace('<board_title>', $pun_config['o_board_title'], $mail_subject);
		$mail_message = str_replace('<base_url>', $pun_config['o_base_url'].'/', $mail_message);
		$mail_message = str_replace('<username>', $username, $mail_message);
		$mail_message = str_replace('<password>', $password1, $mail_message);
		$mail_message = str_replace('<login_url>', $pun_config['o_base_url'].'/login.php', $mail_message);
		$mail_message = str_replace('<board_mailer>', $pun_config['o_board_title'].' '.$lang_common['Mailer'], $mail_message);
		pun_mail($email1, $mail_subject, $mail_message);
	}

	message($lang_plug_um['User Created']);
}
else
{
	// Display the admin navigation menu
	generate_admin_menu($plugin);

	$header2 = sprintf($lang_plug_um['User management'], PLUGIN_VERSION);
?>
	<div class="block">
		<h2><span><?php echo $header2 ?></span></h2>
		<div class="box">
			<div class="inbox">
<?php echo $lang_plug_um['Mod intro'] ?>
			</div>
		</div>
	</div>
	<div class="blockform">
		<h2 class="block2"><span><?php echo $lang_plug_um['User Prune'] ?></span></h2>
		<div class="box">
			<form id="example" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_plug_um['Prune legend'] ?></legend>
						<div class="infldset">
						<table class="aligntop" cellspacing="0">
						<!--Thanks to wiseage for this function -->
							<tr>
								<th scope="row"><?php echo $lang_plug_um['Prune by'] ?></th>
								<td>
									<input type="radio" name="prune_by" value="1" checked="checked" />&nbsp;<strong><?php echo $lang_plug_um['by date'] ?></strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="prune_by" value="0" />&nbsp;<strong><?php echo $lang_plug_um['by login'] ?></strong>
									<span><?php echo $lang_plug_um['Prune-by legend'] ?></span>
								</td>
							</tr>
						<!--/Thanks to wiseage for this function -->
							<tr>
								<th scope="row"><?php echo $lang_plug_um['Minimum days'] ?></th>
								<td>
									<input type="text" name="days" value="28" size="25" tabindex="1" />
									<span><?php echo $lang_plug_um['Days legend'] ?></span>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php echo $lang_plug_um['Maximum posts'] ?></th>
								<td>
									<input type="text" name="posts" value="1"  size="25" tabindex="1" />
									<span><?php echo $lang_plug_um['Posts legend'] ?></span>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php echo $lang_plug_um['Delete mods'] ?></th>
								<td>
									<input type="radio" name="admods_delete" value="1" />&nbsp;<strong><?php echo $lang_admin_common['Yes'] ?></strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="admods_delete" value="0" checked="checked" />&nbsp;<strong><?php echo $lang_admin_common['No'] ?></strong>
									<span><?php echo $lang_plug_um['Mods legend'] ?></span>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php echo $lang_plug_um['User status'] ?></th>
								<td>
									<input type="radio" name="verified" value="1" />&nbsp;<strong><?php echo $lang_plug_um['any'] ?></strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="verified" value="0" checked="checked" />&nbsp;<strong><?php echo $lang_plug_um['verified'] ?></strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="verified" value="2" />&nbsp;<strong><?php echo $lang_plug_um['unverified'] ?></strong>
									<span><?php echo $lang_plug_um['Status legend'] ?></span>
								</td>
							</tr>
						</table>
						</div>
					</fieldset>
				</div>
			<p class="submitend"><input type="submit" name="prune" value="<?php echo $lang_admin_common['Delete'] ?>" tabindex="2" /></p>
			</form>
		</div>

		<h2 class="block2"><span><?php echo $lang_plug_um['Add user'] ?></span></h2>
		<div class="box">
			<form id="example" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_plug_um['Add legend'] ?></legend>
						<div class="infldset">
						<table class="aligntop" cellspacing="0">
							<tr>
								<th scope="row"><?php echo $lang_plug_um['Username'] ?></th>
								<td>
									<input type="text" name="username" size="25" tabindex="3" />
								</td>
							</tr>
							<tr>
								<th scope="row"><?php echo $lang_plug_um['Email'] ?></th>
								<td>
									<input type="text" name="email" size="50" tabindex="3" />
								</td>
							</tr>
							<tr>
								<th scope="row"><?php echo $lang_plug_um['Random password'] ?></th>
								<td>
									<input type="radio" name="random_pass" value="1" />&nbsp;<strong><?php echo $lang_admin_common['Yes'] ?></strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="random_pass" value="0" checked="checked" />&nbsp;<strong><?php echo $lang_admin_common['No'] ?></strong>
									<span><?php echo $lang_plug_um['Random legend'] ?></span>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php echo $lang_plug_um['Password'] ?></th>
								<td>
									<input type="text" name="password" size="25" tabindex="3" />
									<span><?php echo $lang_plug_um['Password legend'] ?></span>
								</td>
							</tr>
						</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend"><input type="submit" name="add_user" value="<?php echo $lang_admin_common['Add'] ?>" tabindex="4" /></p>
			</form>
		</div>
	</div>

<?php
}
?>