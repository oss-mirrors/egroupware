<?php
	/**************************************************************************\
	* eGroupWare - wiki - XML Import & Export                                  *
	* http://www.egroupware.org                                                *
	* Written and (c) by Ralf Becker <RalfBecker@outdoor-training.de>          *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	if (!function_exists('var2xml'))
	{
		if (file_exists(PHPGW_API_INC.'class.xmltool.inc.php'))
		{
			include_once(PHPGW_API_INC.'class.xmltool.inc.php');
		}
		else
		{
			include_once(PHPGW_INCLUDE_ROOT.'/etemplate/inc/class.xmltool.inc.php');
		}
	}

	class xmlwiki
	{
		var $public_functions = array(
			'export' => True,
		);

		function xmlwiki()
		{
			if (!is_object($GLOBALS['pagestore']))
			{
				$this->so = CreateObject('wiki.sowiki');
			}
			else
			{
				$this->so = &$GLOBALS['pagestore'];
			}
		}

		function export($name='',$lang='')
		{
			if (!$name) $name = $_GET['page'];
			if (!$lang) $lang = $_GET['lang'];
			if (!is_array($lang))
			{
				$lang = $lang ? explode(',',$lang) : False;
			}
			header('Content-Type: text/xml; charset=utf-8');

			$xml_doc = new xmldoc();
			$xml_doc->add_comment('$'.'Id$');
			$xml_doc->add_comment("eGroupWare wiki-pages matching '$name%', exported ".date('Y-m-d h:m'));

			$xml_wiki = new xmlnode('wiki');

			foreach($this->so->find($name.'%','name') as $page)
			{
				if ($lang && !in_array($page['lang'],$lang)) continue;

				$page = $this->so->page($page);	// read the complete page
				$page->read();
				$page = $page->as_array();
				unset($page['wiki_id']);		// we dont export the wiki-id

				$GLOBALS['phpgw']->translation->convert($page,$GLOBALS['phpgw']->translation->charset(),'utf-8');

				$xml_page = new xmlnode('page');
				foreach($page as $attr => $val)
				{
					if ($attr != 'text')
					{
						$xml_page->set_attribute($attr,$val);
					}
					else
					{
						$xml_page->set_value($val);
					}
				}
				$xml_wiki->add_node($xml_page);
			}
			$xml_wiki->set_attribute('exported',date('Y-m-d h:m:i'));
			if ($lang)
			{
				$xml_wiki->set_attribute('languages',implode(',',$lang));
			}
			if ($name)
			{
				$xml_wiki->set_attribute('matching',$name.'%');
			}
			$xml_doc->add_root($xml_wiki);
			$xml = $xml_doc->export_xml();

			//if ($this->debug)
			{
				//echo "<pre>\n" . htmlentities($xml) . "\n</pre>\n";
				echo $xml;
			}
			return $xml;
		}
	}
