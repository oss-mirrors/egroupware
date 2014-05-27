<?php
// $Id$

if (!function_exists('parseText'))
{
	include('parse/main.php');
}
// Add a page to a list of categories.
function add_to_category($page, $catlist)
{
	global $pagestore, $Entity, $UserName, $REMOTE_ADDR, $FlgChr;

	// Parse the category list for category names.
	$parsed = parseText($catlist, array('parse_wikiname', 'parse_freelink'), '');
	$pagenames = array();
	preg_replace_callback('/' . $FlgChr . '(\\d+)' . $FlgChr . '/', function($matches) use (&$pagenames)
	{
		global $Entity;
		return $pagenames[] = $Entity[$matches[1]][1];
	}, $parsed);
	$page = get_name($page);

	if(validate_page($page) == 2)
		{ $page = '((' . $page . '))'; }

	// Add it to each category.
	foreach($pagenames as $category)
	{
		$pg = $pagestore->page($category);

		$pg->read();
		if($pg->exists)
		{
			if(preg_match('/\\[\\[!.*\\]\\]/', $pg->text))
			{
				if(!preg_match("/\\[\\[!.*$page.*\\]\\]/", $pg->text))
				{
					$pg->text = preg_replace('/(\\[\\[!.*)\\]\\]/',
																	 "\\1 $page]]", $pg->text);
				}
				else
					{ continue; }
			}
			else
				{ $pg->text = $pg->text . "\n[[! $page]]\n"; }

			$pg->text = str_replace("\\", "\\\\", $pg->text);
			$pg->text = str_replace("'", "\\'", $pg->text);

			$pg->version++;
			$pg->comment  = '';
			$pg->hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
			$pg->username = $UserName;

			$pg->write();
		}
	}
}
?>
