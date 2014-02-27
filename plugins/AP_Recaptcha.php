<?php

/**
 * Copyright (C) 2008-2010 FluxBB
 * based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 *
 * Plugin:
 * Copyright (C) 2010 artoodetoo
 *
 * ReCaptcha library:
 * Copyright (c) 2007 reCAPTCHA -- http://recaptcha.net
 * AUTHORS:
 *   Mike Crawford
 *   Ben Maurer
 */


// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
	exit;

// Load the admin_plugin_recaptcha.php language file
require PUN_ROOT.'lang/'.$admin_language.'/admin_plugin_recaptcha.php';

// Tell admin_loader.php that this is indeed a plugin and that it is loaded
define('PUN_PLUGIN_LOADED', 1);

//
// The rest is up to you!
//

// If the "Show text" button was clicked
if (isset($_POST['save']))
{
	$params = array();

	// Make sure something was entered
	if (trim($_POST['public_key']) == '' || trim($_POST['private_key']) == '') {
		$params['pubkey'] = $params['privkey'] = '';
		$message = $lang_admin_plugin_recaptcha['No keys'];
	} else {
		$params['pubkey'] = trim($_POST['public_key']);
		$params['privkey'] = trim($_POST['private_key']);
		$message = $lang_admin_plugin_recaptcha['Keys entered'];
	}
	$params['theme'] = trim($_POST['theme_name']);

	foreach ($params as $key => $value)
	{
	    $key = 'o_recaptcha_' . $key;
		$value = $db->escape($value);
		if (isset($pun_config[$key]))
		{
			$db->query('UPDATE '.$db->prefix.'config SET conf_value=\''.$value.'\' WHERE conf_name=\''.$key.'\'') or error('Unable to update board config', __FILE__, __LINE__, $db->error());
		}
		else
		{
			$db->query('INSERT INTO '.$db->prefix.'config(conf_name, conf_value) VALUES(\''.$key.'\', \''.$value.'\')') or error('Unable to insert into board config', __FILE__, __LINE__, $db->error());
		}
	}
	
	// Regenerate the config cache
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PUN_ROOT.'include/cache.php';

	generate_config_cache();

	message($message);

}

// Get values from config (if set)
foreach (array('pubkey', 'privkey', 'theme') as $key)
{
	$$key = isset($pun_config['o_recaptcha_' . $key]) ? $pun_config['o_recaptcha_' . $key] : '';
}

// Display the admin navigation menu
generate_admin_menu($plugin);

?>
	<div class="plugin blockform">
		<h2><span><?php echo $lang_admin_plugin_recaptcha['Plugin title'] ?></span></h2>
		<div class="box">
			<div class="inbox">
				<p><?php echo $lang_admin_plugin_recaptcha['Explanation 1'] ?></p>
				<p><?php echo $lang_admin_plugin_recaptcha['Explanation 2'] ?></p>
			</div>
		</div>

		<h2 class="block2"><span><?php echo $lang_admin_plugin_recaptcha['Form title'] ?></span></h2>
		<div class="box">
			<form method="post" action="<?php echo pun_htmlspecialchars($_SERVER['REQUEST_URI']) ?>&amp;foo=bar">
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_admin_plugin_recaptcha['Legend text'] ?></legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row"><?php echo $lang_admin_plugin_recaptcha['Public key'] ?></th>
									<td>
										<input type="text" name="public_key" size="40" value="<?php echo pun_htmlspecialchars($pubkey) ?>" tabindex="1" />
										<span><?php echo $lang_admin_plugin_recaptcha['Public content'] ?></span>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin_plugin_recaptcha['Private key'] ?></th>
									<td>
										<input type="text" name="private_key" size="40" value="<?php echo pun_htmlspecialchars($privkey) ?>" tabindex="2" />
										<span><?php echo $lang_admin_plugin_recaptcha['Private content'] ?></span>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin_plugin_recaptcha['Theme name'] ?></th>
									<td>
										<input type="text" name="theme_name" size="25"  value="<?php echo pun_htmlspecialchars($theme) ?>"tabindex="3" />
										<span><?php echo $lang_admin_plugin_recaptcha['Theme content'] ?></span>
									</td>
								</tr>
							</table>
						</div>
						<p><input type="submit" name="save" value="<?php echo $lang_admin_plugin_recaptcha['Submit button'] ?>" tabindex="4" /></p>
					</fieldset>
				</div>
			</form>
		</div>
	</div>
<?php

// Note that the script just ends here. The footer will be included by admin_loader.php
