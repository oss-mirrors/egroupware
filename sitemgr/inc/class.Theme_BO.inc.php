<?php
	/**************************************************************************\
	* eGroupWare SiteMgr - Web Content Management                              *
	* http://www.egroupware.org                                                *
	* Written and (c) by RalfBecker@outdoor-training.de                        *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	class Theme_BO
	{
		function getAvailableThemes()
		{
			$templates_dir = $GLOBALS['Common_BO']->sites->current_site['site_dir'] . SEP . 'templates' . SEP;
			$result_array=array();

			if ($handle = @opendir($templates_dir))
			{
				while ($file = readdir($handle))
				{
					if (is_dir($templates_dir . $file) && $file != '..' && $file != '.' && $file != 'CVS' && $file != '.svn')
					{
						if ($info = $this->getThemeInfos($file))
						{
							$result_array[$file] = $info;
						}
					}
				}
				closedir($handle);

				uksort($result_array,'strcasecmp');
			}
			//echo "<p>Theme_BO::getAvailableThemes('$templates_dir')=".print_r(array_keys($result_array),true)."</p>";
			return $result_array ? $result_array : array(array('value'=>'','display'=>lang('No templates found.')));
		}

		function getThemeInfos($theme)
		{
			//echo "<p>Theme_BO::getThemeInfos('$theme')</p>";
			$templates_dir = $GLOBALS['Common_BO']->sites->current_site['site_dir'] . SEP . 'templates' . SEP;
			$info = False;
			if (!is_dir($dir = $templates_dir . $theme))
			{
				return False;
			}
			if (file_exists($dir . SEP . 'main.tpl'))
			{
				$info = array(
					'value' => $theme,
					'type'  => 'SiteMgr',
				);
			}
			elseif (file_exists($dir . SEP . 'index.php') && file_exists($xml_details = $dir . SEP . 'templateDetails.xml'))
			{
				$info = array(
					'value'=> $theme,
					'type'  => 'Mambo',
				);
			}
			if ($info)
			{
				if (file_exists($xml_details) && ($details = file_get_contents($xml_details)))
				{
					if (preg_match_all('/<(description|author|authorEmail|authorUrl|copyright|version|name|creationDate)>([^>]+)</',$details,$matches))
					{
						foreach($matches[1] as $n => $name)
						{
							$info[$name] = $matches[2][$n];
						}
						$info['title'] = $info['description'];
						if ($info['authorUrl'] && substr($info['authorUrl'],0,4) != 'http')
						{
							$info['authorUrl'] = 'http://'.$info['authorUrl'];
						}
						// test for joomla 1.0 or 1.5
						if (preg_match('/<install version="([0-9.]+)" type="template">/',$details,$matches))
						{
							$info['type'] = 'Joomla '.$matches[1];
						}
					}
				}
				if (file_exists($dir . SEP . 'template_thumbnail.png'))
				{
					$info['thumbnail'] = $GLOBALS['Common_BO']->sites->current_site['site_url']."templates/$theme/template_thumbnail.png";
				}
				if (!isset($info['name']) || !$info['name'])
				{
					$info['name'] = $info['value'];
				}
				// "create" some nicer names
				$info['name'] = ucwords(str_replace('_',' ',$info['name']));
				$info['display'] = $info['name'] . " ($info[type])";
			}
			return $info;
		}
	}
?>
