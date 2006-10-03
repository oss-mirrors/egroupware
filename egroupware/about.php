<?php
   /**************************************************************************\
   * eGroupWare                                                               *
   * http://www.egroupware.org                                                *
   * --------------------------------------------                             *
   *  This program is free software; you can redistribute it and/or modify it *
   *  under the terms of the GNU General Public License as published by the   *
   *  Free Software Foundation; either version 2 of the License, or (at your  *
   *  option) any later version.                                              *
   \**************************************************************************/

   /* $Id$ */

	$GLOBALS['egw_info'] = array(
		'flags' => array(
			'currentapp' => isset($_GET['app']) && $_GET['app'] != 'eGroupWare' ? $_GET['app'] : 'about',
			'disable_Template_class' => True,
			'noheader' => True,
		)
	);
	include('header.inc.php');

	$app = isset($_GET['app']) && $_GET['app'] != 'eGroupWare' ? basename($_GET['app']) : 'about';

	if ($app)
	{
		if (!($included = $GLOBALS['egw']->hooks->single('about',$app)))
		{
			$api_only = !($included = file_exists(EGW_INCLUDE_ROOT . "/$app/setup/setup.inc.php"));
		}
	}
	else
	{
		$api_only = True;
	}

	$tpl =& CreateObject('phpgwapi.Template',$GLOBALS['egw']->common->get_tpl_dir('phpgwapi'));
	$tpl->set_file(array(
		'phpgw_about'         => 'about.tpl',
		'phpgw_about_unknown' => 'about_unknown.tpl'
	));

	$title = isset($GLOBALS['egw_info']['apps'][$app]) ? $GLOBALS['egw_info']['apps'][$app]['title'] : 'eGroupWare';
	$GLOBALS['egw_info']['flags']['app_header'] = lang('About %1',$title);
	$GLOBALS['egw']->common->egw_header();

	$tpl->set_block('phpgw_about', 'egroupware','egroupware');
	$tpl->set_block('phpgw_about', 'application','application');

	if ($included)
	{
		$tpl->set_var('phpgw_app_about', about_app());
		$tpl->pparse('phpgw_about', 'application', True);
	}
	else
	{
		if ($api_only)
		{
			$tpl->set_var('phpgw_logo',$GLOBALS['egw']->common->image('phpgwapi','logo.gif'));
			$tpl->set_var('phpgw_version',lang('eGroupWare API version %1',$GLOBALS['egw_info']['server']['versions']['phpgwapi']));
			$tpl->set_var('phpgw_message',lang('%1eGroupWare%2 is a multi-user, web-based groupware suite written in %3PHP%4.',
			'<a href="http://www.eGroupWare.org" target="_blank">','</a>','<a href="http://www.php.net" target="_blank">','</a>'));
			$tpl->pparse('out', 'egroupware');

			$tpl->set_var('phpgw_app_about',about_template());
			$tpl->pparse('phpgw_about', 'application', True);
		}
		else
		{
			$tpl->set_var('app_header',$app);

			$tpl->pparse('out','phpgw_about_unknown');
		}
	}

	$GLOBALS['egw']->common->egw_footer();

	function about_app()
	{
		$app = basename($_GET['app']);
		include(EGW_INCLUDE_ROOT . "/$app/setup/setup.inc.php");
		$info = $setup_info[$app];
		$info['icon'] = $GLOBALS['egw']->common->image($app,array('navbar','nonav'));
		$info['title'] = $GLOBALS['egw_info']['apps'][$app]['title'];
		return about_display($info);
	}

	function about_template()
	{
		$template = $GLOBALS['egw']->common->get_tpl_dir('phpgwapi');

		include ($template . "/setup/setup.inc.php");
		$s = "";
		$template_info[] = $GLOBALS['egw_info']['template'][$GLOBALS['egw_info']['user']['preferences']['common']['template_set']];
		foreach($template_info as $info)
		{
			$s .= about_display($info);
		}
		return $s;
	}

	function about_display($info)
	{
		$other_infos = array(
			'author'     => lang('Author'),
			'maintainer' => lang('Maintainer'),
			'version'    => lang('Version'),
			'license'    => lang('License'),
		);
		if($info[icon])
		{
			$icon = $info[icon];
		}
		$s = "<table width='70%' cellpadding='4'>\n";
		if(trim($icon) != "")
		{
			$s.= "<tr>
			<td align='left'><img src='$icon' alt=\"$info[title]\" /></td><td align='left'><h2>$info[title]</h2></td></tr>";
		}
		else
		{
			$s .= "<tr>
			<td align='left'></td><td align='left'><h2>$info[title]</h2></td></tr>";
		}
		if ($info['description'])
		{
			$info['description'] = lang($info['description']);
			$s .= "<tr><td colspan='2' align='left'>$info[description]</td></tr>\n";
			if ($info['note'])
			{
				$info['note'] = lang($info['note']);
				$s .= "<tr><td colspan='2' align='left'><i>$info[note]</i></td></tr>\n";
			}

		}
		foreach ($other_infos as $key => $val)
		{
			if (isset($info[$key]))
			{
				$s .= "<tr><td width='1%' align='left'>$val</td><td>";
				$infos = $info[$key];
				for ($n = 0; is_array($info[$key][$n]) && ($infos = $info[$key][$n]) || !$n; ++$n)
				{
					if (!is_array($infos) && isset($info[$key.'_email']))
					{
						$infos = array('email' => $info[$key.'_email'],'name' => $infos);
					}
					elseif(!is_array($infos) && isset($info[$key.'_url']))
					{
						$infos = array('url' => $info[$key.'_url'],'name' => $infos);
					}
					if (is_array($infos))
					{
						if ($infos['email'])
						{
							$names = explode('<br>',$infos['name']);
							$emails = split('@|<br>',$infos['email']);
							if (count($names) < count($emails)/2)
							{
								$names = '';
							}
							$infos = '';
							while (list($user,$domain) = $emails)
							{
								if ($infos) $infos .= '<br>';
								$name = $names ? array_shift($names) : $user;
								$infos .= "<a href='mailto:$user at $domain'><span onClick=\"document.location='mailto:$user'+'@'+'$domain'; return false;\">$name</span></a>";
								array_shift($emails); array_shift($emails);
							}
						}
						elseif($infos['url'])
						{
							$img = $info[$key.'_img'];
							if ($img)
							{
								$img_url = $GLOBALS['egw']->common->image('phpgwapi',$img);
								if (!$img_url)
								{
									$img_url = $GLOBALS['egw']->common->image($info['name'],$img);
								}
								$infos = '<table border="0"><tr><td style="text-align:center;"><a href="'.$infos['url'].'"><img src="'.$img_url.'" border="0"><br>'.$infos['name'].'</a></td></tr></table>';
							}
							else
							{
								$infos = '<a href="'.$infos['url'].'">'.$infos['name'].'</a>';
							}
						}
					}
					$s .= ($n ? '<br>' : '') . $infos;
				}
				$s .= "</td></tr>\n";
			}
		}

		if ($info['extra_untranslated'])
		{
			$s .= "<tr><td colspan='2' align='left'>$info[extra_untranslated]</td></tr>\n";
		}

		$s .= "</table>\n";

		return $s;
	}
?>
