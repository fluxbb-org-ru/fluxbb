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
define('PLUGIN_VERSION',1.0);

function RoundSigDigs($number, $sigdigs) {
   $multiplier = 1;
   while ($number < 0.1) {
       $number *= 10;
       $multiplier /= 10;
   }
   while ($number >= 1) {
       $number /= 10;
       $multiplier *= 10;
   }
   return round($number, $sigdigs) * $multiplier;
}

if (file_exists(PUN_ROOT.'lang/'.$pun_user['language'].'/plugin_languages_and_styles.php')) {
	require PUN_ROOT.'lang/'.$pun_user['language'].'/plugin_languages_and_styles.php';
} else {
	require PUN_ROOT.'lang/English/plugin_languages_and_styles.php';
}

if (isset($_POST['lang']))
{
	// Do Post
	$db->query('UPDATE '.$db->prefix.'users SET language=\''.$_POST['form']['language'].'\' WHERE id>1') or error('Unable to set lang settings', __FILE__, __LINE__, $db->error());
	message($lang_plug_ls['Languages Reset']);
}
elseif (isset($_POST['style']))
{
	// Do Post
	$db->query('UPDATE '.$db->prefix.'users SET style=\''.$_POST['form']['style'].'\' WHERE id>1') or error('Unable to set style settings', __FILE__, __LINE__, $db->error());
	message($lang_plug_ls['Styles Reset']);
}
else	// If not, we show the form
{
	// Display the admin navigation menu
	generate_admin_menu($plugin);

?>
	<div class="block">
		<h2><span><?php echo sprintf($lang_plug_ls['Plugin title'], PLUGIN_VERSION) ?></span></h2>
		<div class="box">
			<div class="inbox">
				<p><?php echo $lang_plug_ls['Plugin desc'] ?></p>
			</div>
		</div>
	</div>
	<div class="blockform">
		<h2 class="block2"><span><?php echo $lang_plug_ls['Languages'] ?></span></h2>
		<div class="box">
			<form id="lang" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_plug_ls['Languages'] ?></legend>
						<div class="infldset">
						<table class="aligntop" cellspacing="0">
													<tr>
								<th scope="row"><?php echo $lang_plug_ls['Language Usage'] ?></th>
								<td>
<?php
	$result = $db->query('SELECT language, count(*) as number FROM '.$db->prefix.'users WHERE id > 1 GROUP BY language  ORDER BY number') or error('Unable to fetch lang settings', __FILE__, __LINE__, $db->error());
	$number = $db->num_rows($db->query('SELECT username from '.$db->prefix.'users WHERE id > 1'));
	while ($cur_lang = $db->fetch_assoc($result)) {
		echo RoundSigDigs($cur_lang['number'] / $number * 100,3).'% '.str_replace('_',' ',$cur_lang['language']).'<br>';
	}
?>
								</td>
							</tr>

							<tr>
								<th scope="row"><?php echo $lang_plug_ls['Language'] ?></th>
								<td>
<?php
		$languages = array();
		$d = dir(PUN_ROOT.'lang');
		while (($entry = $d->read()) !== false)
		{
			if ($entry != '.' && $entry != '..' && is_dir(PUN_ROOT.'lang/'.$entry))
				$languages[] = $entry;
		}
		$d->close();

?>
									<select name="form[language]">
<?php

		while (list(, $temp) = @each($languages))
		{
				echo "\t\t\t\t\t\t\t\t".'<option value="'.$temp.'">'.$temp.'</option>'."\n";
		}

?>
									</select>
									<span><?php echo $lang_plug_ls['Languages desc'] ?></span>
								</td>
							</tr>
						</table>
						</div>
					</fieldset>
				</div>
			<p class="submitend"><input type="submit" name="lang" value="<?php echo $lang_plug_ls['Reset'] ?>" tabindex="2" /></p>
			</form>
		</div>
	</div>
	<div class="blockform">
		<h2 class="block2"><span><?php echo $lang_plug_ls['Styles'] ?></span></h2>
		<div class="box">
			<form id="style" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_plug_ls['Styles'] ?></legend>
						<div class="infldset">
						<table class="aligntop" cellspacing="0">
													<tr>
								<th scope="row"><?php echo $lang_plug_ls['Style Usage'] ?></th>
								<td>
<?php
	$result = $db->query('SELECT style, count(*) as number FROM '.$db->prefix.'users WHERE id > 1 GROUP BY style ORDER BY number') or error('Unable to fetch style settings', __FILE__, __LINE__, $db->error());
	$number = $db->num_rows($db->query('SELECT username from '.$db->prefix.'users WHERE id > 1'));
	while ($cur_lang = $db->fetch_assoc($result)) {
		echo RoundSigDigs($cur_lang['number'] / $number * 100,3).'% '.str_replace('_',' ',$cur_lang['style']).'<br>';
	}
?>
								</td>
							</tr>

							<tr>
								<th scope="row"><?php echo $lang_plug_ls['Style'] ?></th>
								<td>
<?php
		$styles = array();
		$d = dir(PUN_ROOT.'style');
		while (($entry = $d->read()) !== false)
		{
			if (substr($entry, strlen($entry)-4) == '.css')
				$styles[] = substr($entry, 0, strlen($entry)-4);
		}
		$d->close();


?>
									<select name="form[style]">
<?php

		while (list(, $temp) = @each($styles))
		{
			echo "\t\t\t\t\t\t\t\t".'<option value="'.$temp.'">'.str_replace('_', ' ', $temp).'</option>'."\n";
		}

?>
									</select>
									<span><?php echo $lang_plug_ls['Styles desc'] ?></span>
								</td>
							</tr>
						</table>
						</div>
					</fieldset>
				</div>
			<p class="submitend"><input type="submit" name="style" value="<?php echo $lang_plug_ls['Reset'] ?>" tabindex="2" /></p>
			</form>
		</div>
	</div>
<?php
}
?>