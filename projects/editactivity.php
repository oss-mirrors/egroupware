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
     
     // ====================================================================
     // create two seperate blocks, addblock will be cut off from template
     // editblock contains the buttons and forms for edit
     // ====================================================================
     $t->set_block("activity_edit", "add", "addhandle");
     $t->set_block("activity_edit", "edit", "edithandle");
     
     $t->set_var("actionurl",$phpgw->link("editactivity.php"));
     $t->set_var("deleteurl",$phpgw->link("deleteactivity.php"));
     $t->set_var("lang_action",lang("activity list - edit"));
     $t->set_var("common_hidden_vars",$common_hidden_vars);
     $t->set_var("lang_num",lang("num"));
     $t->set_var("num", stripslashes($phpgw->db->f("num")));
     $t->set_var("lang_descr",lang("description"));
     $t->set_var("descr", stripslashes($phpgw->db->f("descr")));
     $t->set_var("lang_remarkreq",lang("remarkreq"));
     if ($phpgw->db->f("remarkreq")=="N"):
         $stat_sel[0]=" selected";
     elseif ($phpgw->db->f("remarkreq")=="Y"):
         $stat_sel[1]=" selected";
     endif;

     $remarkreq_list = "<option value=\"N\"".$stat_sel[0].">" . lang("no") . "</option>\n"
                  . "<option value=\"Y\"".$stat_sel[1].">" . lang("yes") . "</option>\n";
     $t->set_var("remarkreq_list",$remarkreq_list);
     $t->set_var("lang_billperae",lang("billperae"));
     $t->set_var("billperae", stripslashes($phpgw->db->f("billperae")));
     $t->set_var("lang_minperae",lang("minperae"));
     $t->set_var("minperae", stripslashes($phpgw->db->f("minperae")));

    $t->set_var("lang_editsubmitb",lang("Edit"));
    $t->set_var("lang_editdeleteb",lang("Delete"));
    $t->set_var("lang_atvititiesb",lang("Activities"));
    
    $t->set_var("edithandle","");
    $t->set_var("addhandle","");
    $t->pparse("out","activity_edit");
    $t->pparse("edithandle","edit");
   ?>

   <?
  } else {
    // Create function to take care of this

    $phpgw->db->query("update p_activities set num='$num'," 
                   . "remarkreq='$remarkreq',descr='"
                   . addslashes($descr) . "',billperae='$billperae',minperae='$minperae' "
                   . " where id='$id'");

    Header("Location: " . $phpgw->link($phpgw_info["server"]["webserver_url"] . "/projects/activities.php",
	   "cd=15&sort=$sort&order=$order&query=$query&start="
	 . "$start&filter=$filter"));
  }
?>
