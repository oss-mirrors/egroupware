<?php
/**************************************************************************\
* phpGroupWare - addressbook                                               *
* http://www.phpgroupware.org                                              *
* Written by Joseph Engo <jengo@phpgroupware.org>                          *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

/* $Id$ */

	$phpgw_info["flags"] = array(
		"noheader" => True,
		"nonavbar" => True,
		"currentapp" => "addressbook",
		"enable_contacts_class" => True,
		"enable_nextmatchs_class" => True
	);

	include("../header.inc.php");

	$this = CreateObject("phpgwapi.contacts");

	// First, make sure they have permission to this entry
	$check = addressbook_read_entry($ab_id,array('owner' => 'owner'));
	$perms = $this->check_perms($this->grants[$check[0]['owner']],PHPGW_ACL_READ);

	if ( (!$perms) && ($check[0]['owner'] != $phpgw_info['user']['account_id']) )
	{
		Header("Location: "
			. $phpgw->link('/addressbook/index.php',"cd=16&order=$order&sort=$sort&filter=$filter&start=$start&query=$query&cat_id=$cat_id"));
		$phpgw->common->phpgw_exit();
	}

	if (!$ab_id) {
		Header("Location: " . $phpgw->link("/addressbook/index.php"));
	} elseif (!$submit && $ab_id) {
		$phpgw->common->phpgw_header();
		echo parse_navbar();
	}

	$t = new Template($phpgw->common->get_tpl_dir("addressbook"));
	$t->set_file(array(
		"view"	      => "view.tpl",
		"view_header" => "view_header.tpl",
		"view_row"    => "view_row.tpl",
		"view_footer" => "view_footer.tpl"
	));

	while ($column = each($this->stock_contact_fields)) {
		if (isset($phpgw_info["user"]["preferences"]["addressbook"][$column[0]]) &&
			$phpgw_info["user"]["preferences"]["addressbook"][$column[0]]) {
			$columns_to_display[$column[0]] = True;
			$colname[$column[0]] = $column[0];
		}
	}

	// No prefs?
	if (!$columns_to_display ) {
		$columns_to_display = array(
			"n_given"    => "n_given",
			"n_family"   => "n_family",
			"org_name"   => "org_name",
			"tel_work"   => "tel_work",
			"tel_home"   => "tel_home",
			"email"      => "email",
			"email_home" => "email_home"
		);
		while ($column = each($columns_to_display)) {
			$colname[$column[0]] = $column[1];
		}
		$noprefs=  " - " . lang("Please set your preferences for this app");
	}

	// merge in extra fields
 	$extrafields = array(
		"ophone"   => "ophone",
		"address2" => "address2",
		"address3" => "address3"
	);
	$qfields = $this->stock_contact_fields + $extrafields;

	$fields  = addressbook_read_entry($ab_id,$qfields);

	$record_owner  = $fields[0]["owner"];

	if ($fields[0]["access"] == 'private')
	{
		$access_check = lang('private');
	}
	else
	{
		$access_check = lang('public');
	}

	$t->set_var('lang_viewpref',lang("Address book - view") . $noprefs);

	reset($columns_to_display);
	while (list($column,$null) = each($qfields)) { // each entry column
		if(display_name($colname[$column]))
		{
			$t->set_var('display_col',display_name($colname[$column]));
		}
		else
		{
			$t->set_var('display_col',display_name($column));
		}
		$ref = $data = "";
		$coldata = $fields[0][$column];
		// Some fields require special formatting.       
		if ( ($column == "note" || $column == "label" || $column == "pubkey") && $coldata )
		{
			$datarray = explode ("\n",$coldata);
			if ($datarray[1])
			{
				while (list($key,$info) = each ($datarray))
				{
					if ($key)
					{
						$data .= "</td></tr><tr><td></td><td>" .$info . "</td></tr>";
					}
					else
					{	// First row, don't close td/tr
						$data .= $info . "</td></tr>";
					}
				}
			}
			else
			{
				$data = $coldata;
			}
		}
		elseif ($column == "url" && $coldata)
		{
			$ref = '<a href="' . $coldata . '" target="_new">';
			$data = $coldata . '</a>';
		}
		elseif ( (($column == "email") || ($column == "email_home")) && $coldata)
		{
			if ($phpgw_info["user"]["apps"]["email"])
			{
				$ref='<a href="' . $phpgw->link("/email/compose.php","to="
					. urlencode($coldata)) . '" target="_new">';
			}
			else
			{
				$ref = '<a href="mailto:'.$coldata.'">';
			}
			$data = $coldata."</a>";
		}
		else
		{ // But these do not
			$ref = ""; $data = $coldata;
		}

		if (!$data)
		{
			$t->set_var('ref_data',"&nbsp;");
		}
		else
		{
			$t->set_var('ref_data',$ref . $data);
		}
		$t->parse("cols","view_row",True);
	}

	$cat = CreateObject('phpgwapi.categories');
	$catinfo  = $cat->return_single($fields[0]['cat_id']);
	$catname  = $catinfo[0]["name"];
	if ($fields[0]['cat_id']) { $cat_id = $fields[0]['cat_id']; }

	$cat->app_name = "phpgw";
	$catinfo  = $cat->return_single($fields[0]['cat_id']);
	$catname .= $catinfo[0]["name"];
	if ($fields[0]['cat_id']) { $cat_id = $fields[0]['cat_id']; }

	if (!$catname) { $catname = lang('none'); }

	// These are in the footer
	$t->set_var('lang_owner',lang("Record owner"));
	$t->set_var('owner',$phpgw->common->grab_owner_name($record_owner));
	$t->set_var('lang_access',lang("Record access"));
	$t->set_var('access',$access_check);
	$t->set_var('lang_category',lang("Category"));
	$t->set_var('catname',$catname);

	$sfields = rawurlencode(serialize($fields[0]));

	if (($this->grants[$record_owner] & PHPGW_ACL_EDIT) || ($record_owner == $phpgw_info['user']['account_id']))
	{
		if ($referer)
		{
			$t->set_var('edit_link','<form method="POST" action="' . $phpgw->link("/addressbook/edit.php",'referer='.urlencode($referer)).'">');
		}
		else
		{
			$t->set_var('edit_link','<form method="POST" action="' . $phpgw->link("/addressbook/edit.php").'">');
		}
		$t->set_var('edit_button','<input type="submit" name="edit" value="' . lang('Edit') . '">');
	}

	$copylink  = '<form method="POST" action="' . $phpgw->link("/addressbook/add.php").'">';
	$vcardlink = '<form method="POST" action="' . $phpgw->link("/addressbook/vcardout.php").'">';
	if ($referer)
	{
		$referer = ereg_replace('/phpgroupware','',$referer);
		$donelink  = '<form method="POST" action="' . $phpgw->link($referer).'">';
	}
	else
	{
		$donelink  = '<form method="POST" action="' . $phpgw->link("/addressbook/index.php").'">';
	}

	$t->set_var("access_link",$access_link);
	$t->set_var("ab_id",$ab_id);
	$t->set_var("sort",$sort);
	$t->set_var("order",$order);
	$t->set_var("filter",$filter);
	$t->set_var("start",$start);
	$t->set_var("cat_id",$cat_id);

	$t->parse("header","view_header");
	$t->parse("footer","view_footer");
	
	$t->set_var("lang_ok",lang("ok"));
	$t->set_var("lang_done",lang("done"));
	$t->set_var("lang_copy",lang("copy"));
	$t->set_var("copy_fields",$sfields);
	$t->set_var("lang_submit",lang("submit"));
	$t->set_var("lang_vcard",lang("vcard"));
	$t->set_var("done_link",$donelink);
	$t->set_var("copy_link",$copylink);
	$t->set_var("vcard_link",$vcardlink);

	$t->pfp("out","view");

	$phpgw->common->phpgw_footer();
?>
