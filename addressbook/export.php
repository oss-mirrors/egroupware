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

	$phpgw_info["flags"]["currentapp"] = "addressbook";
	$phpgw_info["flags"]["enable_contacts_class"] = True;
	include("../header.inc.php");

	$sep = SEP;

	if (!$convert) {
		$t = new Template($phpgw_info["server"]["app_tpl"]);
		$t->set_file(array("export" => "export.tpl"));

		$dir_handle=opendir($phpgw_info["server"]["app_root"].$sep."export");
		$i=0; $myfilearray="";
		while ($file = readdir($dir_handle)) {
			#echo "<!-- ".is_file($phpgw_info["server"]["app_root"].$sep."conv".$sep.$file)." -->";
			if ((substr($file, 0, 1) != ".") && is_file($phpgw_info["server"]["app_root"].$sep."export".$sep.$file) ) {
				$myfilearray[$i] = $file;
				$i++;
			}
		}
		closedir($dir_handle);
		sort($myfilearray);
		for ($i=0;$i<count($myfilearray);$i++) {
			$fname = ereg_replace('_',' ',$myfilearray[$i]);
			$conv .= '<OPTION VALUE="'.$myfilearray[$i].'">'.$fname.'</OPTION>';
		}

		$t->set_var("lang_cancel",lang("Cancel"));
		$t->set_var("cancel_url",$phpgw->link("/addressbook/index.php"));
		$t->set_var("navbar_bg",$phpgw_info["theme"]["navbar_bg"]);
		$t->set_var("navbar_text",$phpgw_info["theme"]["navbar_text"]);
		$t->set_var("export_text",lang("Export from Addressbook to CSV or LDIF"));
		$t->set_var("action_url",$phpgw->link("/addressbook/export.php"));
		$t->set_var("filename",lang("Export file name"));
		$t->set_var("conv",$conv);
		$t->set_var("debug",lang(""));
		$t->set_var("download",lang("Submit"));

		$t->pparse("out","export");

		$phpgw->common->phpgw_footer();
	} else {
		include ($phpgw_info["server"]["app_root"].$sep."export".$sep.$conv_type);
		$buffer=array();
		$this = new export_conv;

		// Read in user custom fields, if any
		$phpgw->preferences->read_repository();
		$customfields = array();
		while (list($col,$descr) = each($phpgw_info["user"]["preferences"]["addressbook"])) {
			if ( substr($col,0,6) == 'extra_' ) {
				$field = ereg_replace('extra_','',$col);
				$field = ereg_replace(' ','_',$field);
				$customfields[$field] = ucfirst($field);
			}
		}
 		$extrafields = array(
			"ophone"   => "ophone",
			"address2" => "address2",
			"address3" => "address3"
		);
		$this->qfields = $this->stock_contact_fields;# + $extrafields;# + $customfields;

		$buffer = $this->export_start_file($buffer);
		
		for ($i=0;$i<count($this->ids);$i++) {
			$this->id = $this->ids[$i];
			$buffer = $this->export_start_record($buffer);
			while( list($name,$value) = each($this->currentrecord) ) {
				$buffer = $this->export_new_attrib($buffer,$name,$value);
			}
			$buffer = $this->export_end_record($buffer);
		}

		$buffer = $this->export_end_file($buffer);

		$tsvfilename = $phpgw_info['server']['temp_dir'].$sep.$tsvfilename;

		if ($download == "Submit") {
			header("Content-disposition: attachment; filename=\"".$tsvfilename."\"");
			header("Content-type: application/octetstream");
			header("Pragma: no-cache");
			header("Expires: 0");
//			while(list($name,$value) = each($buffer)) {
				//echo $name.': '.$value."\n";
//			}
		} else {
			echo "<pre>\n";
			$i=0;
			while(list($name,$value) = each($buffer[$i])) {
				echo $i.' - '.$name.': '.$value."\n";
				$i++;
			}
			echo "\n</pre>\n";
			echo '<a href="'.$phpgw->link("/addressbook/index.php").'">'.lang("OK").'</a>';
			$phpgw->common->phpgw_footer();
		}
	}
?>
