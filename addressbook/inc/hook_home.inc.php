<?php
  /**************************************************************************\
  * phpGroupWare - E-Mail                                                    *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

	/* $Id$ */

	$d1 = strtolower(substr($phpgw_info['server']['app_inc'],0,3));
	if($d1 == 'htt' || $d1 == 'ftp' )
	{
		echo "Failed attempt to break in via an old Security Hole!<br>\n";
		$phpgw->common->phpgw_exit();
	}
	unset($d1);

	$tmp_app_inc = $phpgw_info['server']['app_inc'];
	$phpgw_info['server']['app_inc'] = $phpgw->common->get_inc_dir('addressbook');

	if ($phpgw_info["user"]["apps"]["addressbook"]
		&& $phpgw_info["user"]["preferences"]["addressbook"]["mainscreen_showbirthdays"])
	{
		echo "\n<!-- Birthday info -->\n";

		$c = CreateObject('phpgwapi.contacts');
		$qfields = array(
			"n_given"  => "n_given",
			"n_family" => "n_family",
			"bday"     => "bday"
		);

		$today = $phpgw->common->show_date(mktime(0,0,0,
		$phpgw->common->show_date(time(),"m"),
		$phpgw->common->show_date(time(),"d"),
		$phpgw->common->show_date(time(),"Y")),"n/d" );
		//echo $today."\n";

		$bdays = $c->read(0,15,$qfields,$today,'tid=n','','',$phpgw_info["user"]["account_id"]);

		while(list($key,$val) = @each($bdays))
		{
			$tmp = "<a href=\""
				. $phpgw->link("/addressbook/view.php","ab_id=" .  $val["id"]) . "\">"
				. $val["n_given"] . " " . $val["n_family"]."</a>";
			echo "<tr><td align=\"left\">" . lang("Today is x's birthday!", $tmp) . "</td></tr>\n";
		}

		$tomorrow = $phpgw->common->show_date(mktime(0,0,0,
		$phpgw->common->show_date(time(),"m"),
		$phpgw->common->show_date(time(),"d")+1,
		$phpgw->common->show_date(time(),"Y")),"n/d" );
		//echo $tomorrow."\n";

		$bdays = $c->read(0,15,$qfields,$tomorrow,'tid=n','','',$phpgw_info["user"]["account_id"]);

		while(list($key,$val) = @each($bdays))
		{
			$tmp = "<a href=\""
				. $phpgw->link("/addressbook/view.php","ab_id=" .  $val["id"]) . "\">"
				. $val["n_given"] . " " . $val["n_family"]."</a>";
			echo "<tr><td align=\"left\">" . lang("Tomorrow is x's birthday.", $tmp) . "</td></tr>\n";
		}
		echo "\n<!-- Birthday info -->\n";
	}

	$phpgw_info["server"]["app_inc"] = $tmp_app_inc;
?>
