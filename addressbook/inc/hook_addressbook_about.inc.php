<?php

	function about_app()
	{
		global $phpgw, $phpgw_info;
		$appname = 'addressbook';

		$imgfile = $phpgw->common->get_image_dir($appname) . '/' . $appname . '.gif';
		if (file_exists($imgfile))
		{
			$imgpath = $phpgw->common->get_image_path($appname) . '/' . $appname . '.gif';
		}
		else
		{
			$imgfile = $phpgw->common->get_image_dir($appname) . '/navbar.gif';
			if (file_exists($imgfile))
			{
				$imgpath = $phpgw->common->get_image_path($appname) . '/navbar.gif';
			}
			else
			{
				$imgpath = '';
			}
		}

		$browser = CreateObject('phpgwapi.browser');
		$os      = $browser->get_platform();
		$agent   = ucfirst(strtolower($browser->get_agent()));
		$version = $browser->get_version();

		$tpl = new Template($phpgw->common->get_tpl_dir("addressbook"));

		$tpl->set_file(array("body" => "about.tpl"));

		$tpl->set_var("about_addressbook",'This is the phpgroupware core addressbook application.  It makes use of the phpgroupware contacts class to store and retrieve contact information via SQL or LDAP.');

		$tpl->set_var("url",$phpgw->link('/addressbook'));
		$tpl->set_var("image",$imgpath);
		$tpl->set_var("version",$version);
		$tpl->set_var("agent",$agent);
		$tpl->set_var("platform",$os);
		$tpl->set_var("appear",lang('You appear to be running'));
		$tpl->set_var("on",lang('on'));

		return $tpl->parse("out","body");
	}
