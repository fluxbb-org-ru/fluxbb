<?php

class Pages
{
	static function plugin()
	{
		static $plugin;
		if (!isset($plugin)) {
			$plugin = file_exists(PUN_ROOT . 'plugins/AMP_Pages.php')
				? 'AMP_Pages.php'
				: (file_exists(PUN_ROOT . 'plugins/AP_Pages.php')
					? 'AP_Pages.php'
					: ''
				  );
		}
		return $plugin;
	}

	static function canManage()
	{
		global $pun_user;
		static $canManage;
		if (!isset($canManage)) {
			if (!isset($pun_user))
				trigger_error('Include common.php first', E_USER_ERROR);
			$plugin = self::plugin();
			$canManage = ($pun_user['g_id'] == PUN_ADMIN  && $plugin != '')
                      || ($pun_user['g_moderator'] == '1' && $plugin == 'AMP_Pages.php');
		}
		return $canManage;
	}

	static function uri(&$query = '')
	{
		static $uri, $q;
		if (!isset($uri)) {
			$uri = $_SERVER['REQUEST_URI'];
			if (($p = strpos($uri, '?')) !== FALSE) {
				$q = substr($uri, $p);
				$uri = substr($uri, 0, $p);
			} else {
				$q = '';
			}
		}
		$query = $q;
		return $uri;
	}

	static function withoutPrefix($url)
	{
		global $pun_config;
		if ($url{0} == '/')
			$url = substr($url, strlen(dirname($_SERVER['SCRIPT_FILENAME'])) - strlen($_SERVER['DOCUMENT_ROOT']) + 2);
		else {
			if (!isset($pun_config))
				trigger_error('Include common.php first', E_USER_ERROR);
			if (strpos($url, $pun_config['o_base_url']) === 0 )
				$url = substr($url, strlen($pun_config['o_base_url']) + 1);
		}
		return $url;
	}

}
