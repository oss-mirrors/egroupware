<?php
  /**************************************************************************\
  * phpGroupWare - projects/projectbilling                                   *
  * (http://www.phpgroupware.org)                                            *
  * Written by Bettina Gille  [aeb@hansenet.de]                              * 
  * --------------------------------------------------------                 *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/
/* $Id$ */
  
  $phpgw_info["flags"] = array("currentapp" => "projects",                                                                                                                            
                               "noheader" => True,                                                                                                                                 
                               "nonavbar" => True,
                               "noappheader" => True,
                               "noappfooter" => True);

  include("../header.inc.php"); 

    if ($submit) {
	$phpgw->preferences->change("projects","tax");
	$phpgw->preferences->change("projects","abid");
	$phpgw->preferences->commit(True);     
     
    Header("Location: " . $phpgw->link('/preferences/index.php'));
    $phpgw->common->phpgw_exit();     
    }

    $phpgw->common->phpgw_header();                                                                                                                                                       
    echo parse_navbar();  

    if ($totalerrors) {                                                                                                                                                               
    echo "<p><center>" . $phpgw->common->error_list($errors) . "</center>";                                                                                                        
    }     

    $t = CreateObject('phpgwapi.Template',$phpgw->common->get_tpl_dir('projects'));
    $t->set_file(array('prefs' => 'preferences.tpl'));
     
     
    $t->set_var("actionurl",$phpgw->link("/projects/preferences.php"));
    $t->set_var("addressbook_link",$phpgw->link("/projects/addressbook.php","query="));
    
    $t->set_var("lang_action",lang("Project preferences"));
    $t->set_var("lang_select_tax",lang("Select tax for work hours"));
    $t->set_var("lang_select",lang("Select per button !"));
     
    $tax = $phpgw_info["user"]["preferences"]["projects"]["tax"];
    $t->set_var("tax",$tax);
     
    $t->set_var("lang_address",lang("Select your address"));                                                                                                                                         
     
    $d = CreateObject("phpgwapi.contacts");
    if (isset($phpgw_info["user"]["preferences"]["projects"]["abid"])) {
    $abid = $phpgw_info["user"]["preferences"]["projects"]["abid"];                                                                                                                     
    $cols = array('n_given' => 'n_given',                                                                                                                                          
                  'n_family' => 'n_family',                                                                                                                                         
                  'org_name' => 'org_name');                                                                                                                                        
                                                                                                                                                                                    
    $entry = $d->read_single_entry($abid,$cols);
    $t->set_var('name',$entry[0]['org_name'] . " [ " . $entry[0]['n_given'] . " " . $entry[0]['n_family'] . " ]");
    }
    else {
    $t->set_var("abid",$abid);
    $t->set_var("name",$name);
    }


    $t->set_var("lang_editsubmitb",lang("Edit"));
    
    $t->pparse("out","prefs");
    
    $phpgw->common->phpgw_footer();
?>

