<?php
  /**************************************************************************\
  * phpGroupWare - projects                                                  *
  * (http://www.phpgroupware.org)                                            *
  * Written by Bettina Gille  [aeb@hansenet.de]                              *
  *          & Jens Lentfoehr <sw@lf.shlink.de>                              *
  * -------------------------------------------------------                  *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/
/* $Id$ */
  
 if ($submit) {
     $phpgw_info["flags"] = array("noheader" => True, 
                                  "nonavbar" => True);
  }

  $phpgw_info["flags"]["currentapp"] = "projects";
  include("../header.inc.php");
  if (! $id)
     Header("Location: " . $phpgw->link($phpgw_info["server"]["webserver_url"] . "/projects/"
	  . "sort=$sort&order=$order&query=$query&start=$start"
	  . "&filter=$filter"));


  $common_hidden_vars =
   "<input type=\"hidden\" name=\"sort\" value=\"$sort\">\n"
 . "<input type=\"hidden\" name=\"order\" value=\"$order\">\n"
 . "<input type=\"hidden\" name=\"query\" value=\"$query\">\n"
 . "<input type=\"hidden\" name=\"start\" value=\"$start\">\n"
 . "<input type=\"hidden\" name=\"filter\" value=\"$filter\">\n"
 . "<input type=\"hidden\" name=\"id\" value=\"$id\">\n";

  if (! $submit) {

     $phpgw->db->query("select * from p_activities "
		 . "WHERE id='$id'");
     $phpgw->db->next_record();

     $t = new Template($phpgw_info["server"]["app_tpl"]);
     $t->set_file(array( "activity_edit" => "formactivity.tpl"));
     $t->set_block("activity_edit", "add", "addhandle");
     $t->set_block("activity_edit", "edit", "edithandle");
     
   if (isset($phpgw_info["user"]["preferences"]["common"]["currency"])) {                                                                                                                
   $currency = $phpgw_info["user"]["preferences"]["common"]["currency"];                                                                                                                 
   $t->set_var("error","");                                                                                                                                                              
   }                                                                                                                                                                                     
   else {                                                                                                                                                                                
   $t->set_var("error",lang("Please select your currency in preferences!"));                                                                                                             
   }     

     $t->set_var("currency",$currency);
     $t->set_var("actionurl",$phpgw->link("editactivity.php"));
     $t->set_var("deleteurl",$phpgw->link("deleteactivity.php"));
     $t->set_var("lang_action",lang("Edit activity"));
     $t->set_var("common_hidden_vars",$common_hidden_vars);
     $t->set_var("lang_num",lang("Activity ID"));
     $t->set_var("num",$phpgw->strip_html($phpgw->db->f("num")));
     $t->set_var("lang_descr",lang("Description"));
     $descr  = $phpgw->strip_html($phpgw->db->f("descr"));                                                                                                                                
     if (! $descr)  $descr  = "&nbsp;";
     $t->set_var("descr",$descr);
     $t->set_var("lang_remarkreq",lang("Remark required"));
     if ($phpgw->db->f("remarkreq")=="N"):
         $stat_sel[0]=" selected";
     elseif ($phpgw->db->f("remarkreq")=="Y"):
         $stat_sel[1]=" selected";
     endif;

     $remarkreq_list = "<option value=\"N\"".$stat_sel[0].">" . lang("No") . "</option>\n"
                  . "<option value=\"Y\"".$stat_sel[1].">" . lang("Yes") . "</option>\n";
     $t->set_var("remarkreq_list",$remarkreq_list);
     $t->set_var("lang_billperae",lang("Bill per workunit"));
     $t->set_var("billperae",$phpgw->db->f("billperae"));
     $t->set_var("lang_minperae",lang("Minutes per workunit"));
     $t->set_var("minperae",$phpgw->db->f("minperae"));

    $t->set_var("lang_editsubmitb",lang("Edit"));
    $t->set_var("lang_editdeleteb",lang("Delete"));
    $t->set_var("lang_atvititiesb",lang("Activities"));
    
    $t->set_var("edithandle","");
    $t->set_var("addhandle","");
    $t->pparse("out","activity_edit");
    $t->pparse("edithandle","edit");
    } 
  else {
    $num = addslashes($num);
    $descr = addslashes($descr);
    $phpgw->db->query("update p_activities set num='$num',remarkreq='$remarkreq',descr='$descr',billperae='$billperae', "
                    . "minperae='$minperae' where id='$id'");

    Header("Location: " . $phpgw->link($phpgw_info["server"]["webserver_url"] . "/projects/activities.php",
	   "cd=15&sort=$sort&order=$order&query=$query&start="
	 . "$start&filter=$filter"));
    }
    $phpgw->common->phpgw_footer();
?>
