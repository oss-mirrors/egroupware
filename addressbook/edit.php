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

  if ($submit || ! $ab_id) {
     $phpgw_info["flags"] = array("noheader" => True, "nonavbar" => True);
  }

  $phpgw_info["flags"]["currentapp"] = "addressbook";
  $phpgw_info["flags"]["enable_addressbook_class"] = True;
  include("../header.inc.php");
  
  $t = new Template($phpgw_info["server"]["app_tpl"]);
  $t->set_file(array( "edit"	=> "edit.tpl"));

  if (! $ab_id) {
     Header("Location: " . $phpgw->link($phpgw_info["server"]["webserver_url"]. "/addressbook/",
	       "cd=16&order=$order&sort=$sort&filter=$filter&start=$start&query=$query"));
     $phpgw->common->phpgw_exit();
  }

  if (! $submit) {
     $phpgw->db->query("SELECT * FROM addressbook WHERE ab_owner='"
                     . $phpgw_info["user"]["account_id"] . "' AND ab_id=$ab_id");
     $phpgw->db->next_record();

     $fields = array('ab_id' => $phpgw->db->f("ab_id"),
		'owner'      => $phpgw->db->f("ab_owner"),
		'access'     => $phpgw->db->f("ab_access"),
		'firstname'  => $phpgw->db->f("ab_firstname"),
		'lastname'   => $phpgw->db->f("ab_lastname"),
		'title'      => $phpgw->db->f("ab_title"),
		'email'      => $phpgw->db->f("ab_email"),
		'hphone'     => $phpgw->db->f("ab_hphone"),
		'wphone'     => $phpgw->db->f("ab_wphone"),
		'fax'        => $phpgw->db->f("ab_fax"),
		'pager'      => $phpgw->db->f("ab_pager"),
		'mphone'     => $phpgw->db->f("ab_mphone"),
		'ophone'     => $phpgw->db->f("ab_ophone"),
		'street'     => $phpgw->db->f("ab_street"),
		'address2'   => $phpgw->db->f("ab_address2"),
		'city'       => $phpgw->db->f("ab_city"),
		'state'      => $phpgw->db->f("ab_state"),
		'zip'        => $phpgw->db->f("ab_zip"),
		'bday'       => $phpgw->db->f("ab_bday"),
		'company'    => $phpgw->db->f("ab_company"),
		'company_id' => $phpgw->db->f("ab_company_id"),
		'notes'      => $phpgw->db->f("ab_notes"),
		'url'        => $phpgw->db->f("ab_url")
          );

     form("","edit.php","Edit",$fields);

  } else {
     if ($url == "http://") {
        $url = "";
     }
  
     if (! $bday_month && ! $bday_day && ! $bday_year) {
        $bday = "";
     } else {
        $bday = "$bday_month/$bday_day/$bday_year";
     }
     
    if ($access != "private" && $access != "public") {
       $access = $phpgw->accounts->array_to_string($access,$n_groups);
    }

    if($phpgw_info["apps"]["timetrack"]["enabled"]) {
      $sql = "UPDATE addressbook set ab_email='" . addslashes($email)
           . "', ab_firstname='"  . addslashes($firstname)
	       . "', ab_lastname='"   . addslashes($lastname)
           . "', ab_title='"      . addslashes($title)
   	    . "', ab_hphone='" 	. addslashes($hphone)
	       . "', ab_wphone='" 	. addslashes($wphone)
   	    . "', ab_fax='"        . addslashes($fax)
	       . "', ab_pager='"      . addslashes($pager)
   	    . "', ab_mphone='" 	. addslashes($mphone)
	       . "', ab_ophone='" 	. addslashes($ophone)
   	    . "', ab_street='" 	. addslashes($street)
           . "', ab_address2='"   . addslashes($address2)
	       . "', ab_city='" 	  . addslashes($city)
   	    . "', ab_state='" 	 . addslashes($state)
	       . "', ab_zip='" 	   . addslashes($zip)
   	    . "', ab_bday='"       . addslashes($bday)
	       . "', ab_notes='"      . addslashes($notes)
   	    . "', ab_company_id='" . addslashes($company)
	       . "', ab_access='" 	. addslashes($access)
	       . "', ab_url='"    	. addslashes($url)
   	    . "'  WHERE ab_owner='" . $phpgw_info["user"]["account_id"] . "' AND ab_id=$ab_id";
     } else {
      $sql = "UPDATE addressbook set ab_email='" . addslashes($email)
            . "', ab_firstname='". addslashes($firstname)
            . "', ab_lastname='" . addslashes($lastname)
            . "', ab_title='"    . addslashes($title)
            . "', ab_hphone='"   . addslashes($hphone)
            . "', ab_wphone='"   . addslashes($wphone)
            . "', ab_fax='"      . addslashes($fax)
            . "', ab_pager='"    . addslashes($pager)
            . "', ab_mphone='"   . addslashes($mphone)
            . "', ab_ophone='"   . addslashes($ophone)
            . "', ab_street='"   . addslashes($street)
            . "', ab_address2='" . addslashes($address2)
            . "', ab_city='"     . addslashes($city)
            . "', ab_state='"    . addslashes($state)
            . "', ab_zip='"      . addslashes($zip)
            . "', ab_bday='"     . addslashes($bday)
            . "', ab_notes='"    . addslashes($notes)
            . "', ab_company='"  . addslashes($company)
            . "', ab_access='"   . addslashes($access)
            . "', ab_url='"      . addslashes($url)
            . "'  WHERE ab_owner='" . $phpgw_info["user"]["account_id"] . "' AND ab_id=$ab_id";
     }

     $phpgw->db->query($sql);

     Header("Location: " . $phpgw->link("view.php","&ab_id=$ab_id&order=$order&sort=$sort&filter="
 	     . "$filter&start=$start"));
     $phpgw->common->phpgw_exit();
  }

  $t->set_var("ab_id",$ab_id);
  $t->set_var("sort",$sort);
  $t->set_var("order",$order);
  $t->set_var("filter",$filter);
  $t->set_var("start",$start);
  $t->set_var("lang_ok",lang("ok"));
  $t->set_var("lang_clear",lang("clear"));
  $t->set_var("lang_cancel",lang("cancel"));
  $t->set_var("lang_delete",lang("delete"));
  $t->set_var("lang_submit",lang("submit"));
  $t->set_var("cancel_link",'<form action="'.$phpgw->link("index.php","sort=$sort&order=$order&filter=$filter&start=$start") . '">');
  $t->set_var("delete_link",'<form action="'.$phpgw->link("delete.php","ab_id=$ab_id") . '">');

  $t->parse("out","edit");
  $t->pparse("out","edit");


  $phpgw->common->phpgw_footer();
?>
