<?php
// PunBB/FluxBB internationalization tests
// (c) 2010 artoodetoo. http://fluxbb.org.ru/
// Free for use. NO WARRANTIES!
//
// Place this file in forum root
//
// It looks for:
// - lang file wich haven't mirror in another lang
// - lost keys in one of the mirrors
// - undefined $lang_* variables
// - undefined keys in $lang_* variables
// - lang keys never used
//

class languageDir
{
	public
		$files;
	public function __construct($dir)
	{
		$this->files = array();
	    $d = dir($dir);
	    while (($entry = $d->read()) !== FALSE)
	    {
	        // skip hidden files, . and ..
	        if ($entry{0} != '.' && is_file($dir . $entry) && strrchr($entry, '.') == '.php')
	        {
				$this->files[basename($entry, '.php')] = $this->inspectLangFile($dir . $entry);
	        }
	    }
	    $d->close();
	}

	private function inspectLangFile($file)
	{
		$text = file_get_contents($file);
		$tokens = token_get_all($text);
		$var = '';
		$keys = array();
		foreach ($tokens as $token) {			if (is_array($token) && $token[0] == T_VARIABLE) {				$var = substr($token[1], 1);
				break;
			}
		}
		unset($tokens);
		if (!empty($var)) {
			include $file;
			foreach ($$var as $key => $dummy) {				$keys[$key] = 0;
			}
			unset($$var);
			ksort($keys);
		} else {			echo '*** ' . substr($file, strrpos($file, 'lang/')). " define no variable!\n";
		}
		return array('var' => '$'.strtolower($var), 'keys' => $keys);
	}}

class allLanguages
{
	public
		$languages;

	public function __construct($dir)
	{
		$this->languages = array();	    $d = dir($dir . 'lang/');
	    while (($entry = $d->read()) !== FALSE)
	    {
	        // skip hidden files, . and ..
	        if ($entry{0} != '.' && is_dir($dir . 'lang/' . $entry))
	        {	        	$this->languages[$entry] = new languageDir($dir . 'lang/' . $entry . '/');
	        }
	    }
	    $d->close();
	}

	public function compareLanguages($langNameA, $langNameB)
	{		$langA = $this->languages[$langNameA];
		$langB = $this->languages[$langNameB];

		$errorCount = 0;
		$a = array_keys($langA->files);
		$b = array_keys($langB->files);		if (count($d = array_diff($a, $b))) {			echo "*** {$langNameB} miss file(s): " . implode(', ', $d) . "\n";
			++$errorCount;
		}
		if (count($d = array_diff($b, $a))) {
			echo "*** {$langNameA} miss file(s): " . implode(', ', $d) . "\n";
			++$errorCount;
		}

		foreach (array_intersect($a, $b) as $file) {			$af = $langA->files[$file];
			$bf = $langB->files[$file];
			if ($af['var'] != $bf['var']) {				echo "*** {$file} has different variable names\n";
				++$errorCount;
				continue;
			}
			$afk = array_keys($af['keys']);
			$bfk = array_keys($bf['keys']);
			if (count($d = array_diff($afk, $bfk))) {
				echo "*** {$langNameB} {$file} miss key(s): '" . implode("', '", $d) . "'\n";
				++$errorCount;
			}
			if (count($d = array_diff($bfk, $afk))) {
				echo "*** {$langNameA} {$file} miss key(s): '" . implode("', '", $d) . "'\n";
				++$errorCount;
			}
		}
		return $errorCount;
	}
}

class fileInspector
{
	private
		$langFiles;

	public
		$unknownVars,
		$unknownKeys;

	public function __construct($langObject)
	{
		$this->langFiles =& $langObject->files;
		$this->unknownVars = array();
		$this->unknownKeys = array();
	}

	public function scan($dir)
	{		$errorCount = 0;	    $d = dir($dir);
	    while (($entry = $d->read()) !== FALSE) {
	        if ($entry{0} != '.' && $entry != 'lang') {	        	$e = $dir . $entry;	        	if (is_dir($e)) {	        	 	$errorCount += $this->scan($e . '/');
	        	} else if (strrchr($e, '.') == '.php') {					$errorCount += $this->checkFile($e);
	        	}	        }
	    }
	    $d->close();
	    return $errorCount;
	}

	private function checkFile($fileName)
	{
		$errorCount = 0;
		$tokens = token_get_all(file_get_contents($fileName));
		$prevToken = NULL;
		foreach ($tokens as $token) {			if (is_array($token) && $token[0] == T_VARIABLE) {				if (stripos($token[1], '$lang_') !== 0) {					unset($ref);
					continue;
				}				$var = strtolower($token[1]);				if (in_array($var, $this->unknownVars)) {					continue;
				}
				$exists = FALSE;
				foreach ($this->langFiles as $f => $params) {					if ($params['var'] == $var) {						$exists = TRUE;
						$ref = $f;
						break;
					}
				}
				if (!$exists) {					$this->unknownVars[basename($fileName, '.php')][] = $var;
					++$errorCount;
					continue;
				}

			} else if (is_array($token) && $token[0] == T_CONSTANT_ENCAPSED_STRING && $prevToken == '[' && isset($ref)) {				$key = substr($token[1], 1, -1);
				$keys =& $this->langFiles[$ref]['keys'];
				if (isset($keys[$key])) {					++$keys[$key];
				} else {
					$this->unknownKeys[$ref][] = $key;
				}				unset($ref);
			}
			$prevToken = $token;
		}
		return $errorCount;
	}
}


header('Content-type: text/plain; charset=utf-8');

$baseDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME'])) . '/';

$o = new allLanguages($baseDir);

$notEnglish = $o->languages;
unset($notEnglish['English']);
foreach ($notEnglish as $langName => $dummy) {
	echo "Compare English to {$langName}:\n";
	if ($o->compareLanguages('English', $langName) == 0) {		echo "Ok\n";
	}
	echo "----------------\n";
}

$i = new fileInspector($o->languages['English']);

echo "Scan for variable usage:\n";
$i->scan($baseDir);
if (!empty($i->unknownVars)) {	foreach ($i->unknownVars as $fileName => $vars) {		echo "*** {$fileName} has unknown variable(s): " . implode(', ', $vars) . "\n";
	}
	echo "----------------\n";
}
if (!empty($i->unknownKeys)) {
	foreach ($i->unknownKeys as $fileName => $keys) {
		echo "*** {$fileName} has not key(s): '" . implode("', '", $keys) . "'\n";
	}
	echo "----------------\n";
}

foreach ($o->languages['English']->files as $fileName => $params) {	$unused = array_filter($params['keys'], 'zero');
	if (!empty($unused)) {		echo "*** {$fileName}'s never used key(s): '" . implode("', '", array_keys($unused)) . "'\n";
	}
}

function zero($val)
{	return empty($val);
}