<?

function getLanguages()
{
	GLOBAL $settings;
	
	$languages = array();
	
	$path = $settings->_rootDir . "languages/";
	$handle = opendir($path);
	
	while ($entry = readdir($handle) )
	{
		if ($entry == ".." || $entry == ".")
			continue;
		else if (is_dir($path . $entry))
			array_push($languages, $entry);
	}
	closedir($handle);
	
	return $languages;
}

include $settings->_rootDir . "languages/" . $settings->_language . "/lang.inc";

function getMLText($key, $replace = array())
{
	GLOBAL $settings, $text;

	if (!isset($text[$key]))
		return "Error getting Text: " . $key . " (" . $settings->_language . ")";
	
	$tmpText = $text[$key];
	if (count($replace) == 0)
		return $GLOBALS['egw']->translation->convert($tmpText,'iso-8859-1',$displayCharset);
	
	$keys = array_keys($replace);
	foreach ($keys as $key)
		$tmpText = str_replace("[".$key."]", $replace[$key], $tmpText);

	return $GLOBALS['egw']->translation->convert($tmpText,'iso-8859-1',$displayCharset);
}

function printMLText($key, $replace = array())
{
	print getMLText($key, $replace);
}

?>