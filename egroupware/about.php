<?php
  /**************************************************************************\
  * phpGroupWare                                                             *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	$phpgw_info["flags"]["currentapp"] = "about";
	include("header.inc.php");

	if ($app)
	{
		$included = $phpgw->common->hook_single("about",$app);
	}
	else
	{
		$api_only = True;
	}

	$tpl = CreateObject('phpgwapi.Template',$phpgw->common->get_tpl_dir('phpgwapi'));
	$tpl->set_file(array(
		"phpgw_about"         => "about.tpl",
		"phpgw_about_unknown" => "about_unknown.tpl"
	));

	$tpl->set_var("webserver_url",$phpgw->common->get_image_path('phpgwapi'));
	$tpl->set_var("phpgw_version","phpGroupWare API version " . $phpgw_info["server"]["versions"]["phpgwapi"]);
	if ($included)
	{
		$tpl->set_var("phpgw_app_about",about_app("",""));
		//about_app($tpl,"phpgw_app_about");
	}
	else
	{
		if ($api_only)
		{
			$tpl->set_var("phpgw_app_about","");
		}
		else
		{
			$tpl->set_var("app_header",$app);
			$tpl->parse("phpgw_app_about","phpgw_about_unknown");
		}
	}

	$tpl->pparse("out","phpgw_about");
	$phpgw->common->phpgw_footer();
?>
