<?php
  /**************************************************************************\
  * phpGroupWare - projects                                                  *
  * (http://www.phpgroupware.org)                                            *
  * Written by Bettina Gille [aeb@hansenet.de]                               *                               
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

  
  $db2 = $phpgw->db;
  
  
  if (! $submit) {
     ?>
      
   <?PHP
  	$t = new Template($phpgw_info["server"]["app_tpl"]);
  	
  	$t->set_var("actionurl",$phpgw->link("addactivity.php"));
  	$t->set_file(array( "activity_add" => "formactivity.tpl"));
  	
  	// ====================================================================
     	// create two seperate blocks, editblock will be cut off from template
     	// addblock contains the buttons needed
     	// ====================================================================
     	$t->set_block("activity_add", "add", "addhandle");
     	$t->set_block("activity_add", "edit", "edithandle");
  	
  	$t->set_var("lang_action",lang("activity list - add"));
	
	$common_hidden_vars = "<input type=\"hidden\" name=\"start\" value=\"$start\">\n"
        		. "<input type=\"hidden\" name=\"order\" value=\"$order\">\n"
        		. "<input type=\"hidden\" name=\"filter\" value=\"$filter\">\n"
        		. "<input type=\"hidden\" name=\"query\" value=\"$query\">\n"
        		. "<input type=\"hidden\" name=\"sort\" value=\"$sort\">\n"
        		. "<input type=\"hidden\" name=\"id\" value=\"$id\">";
        		
        $t->set_var("common_hidden_vars",$common_hidden_vars);
        $t->set_var("lang_num",lang("num"));
        
        $db2->query("select max(num) as max from p_activities");
        if($db2->next_record()) { 
        $t->set_var("num",(int)($db2->f("max"))+1);
        } else {                                                                                                                                              
           $t->set_var("num","1");	 
        }
        $t->set_var("lang_descr",lang("description"));
        $t->set_var("descr","");
        $t->set_var("lang_minperae",lang("minperae"));
        $t->set_var("minperae","");
        $t->set_var("lang_billperae",lang("billperae"));
        $t->set_var("billperae","");

        $t->set_var("lang_remarkreq",lang("remarkreq"));
	$remarkreq_list = "<option value=\"N\">" . lang("no") . "</option>\n"
           		. "<option value=\"Y\" selected>" . lang("yes") . "</option>\n";
        $t->set_var("remarkreq_list",$remarkreq_list);

        $t->set_var("lang_addsubmitb",lang("Add"));
        $t->set_var("lang_addresetb",lang("Clear Form"));
        
        $t->set_var("edithandle","");
    	$t->set_var("addhandle","");
    	$t->set_var("acthandle","");
    	$t->pparse("out","activity_add");
    	$t->pparse("addhandle","add");
?>

   <?php
  } else {

    $phpgw->db->query("insert into p_activities (num,descr,remarkreq," 
                . "billperae,minperae) "
                . "values ('$num','".addslashes($descr)."','$remarkreq',"
                . "'$billperae','$minperae')"); 

    Header("Location: " . $phpgw->link($phpgw_info["server"]["webserver_url"] . "/projects/activities.php",
           "cd=14&sort=$sort&order=$order&query=$query&start="
         . "$start&filter=$filter"));
  }
  include($phpgw_info["server"]["api_dir"] . "/footer.inc.php");