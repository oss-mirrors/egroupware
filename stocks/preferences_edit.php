<?php
  /**************************************************************************\
  * phpGroupWare - Stock Quotes                                              *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  $phpgw_info["flags"] = array("noheader" => True,
                               "nonavbar" => True,
                               "enable_nextmatchs_class" => True);
  
  $phpgw_info["flags"]["currentapp"] = "stocks";
  include("../header.inc.php");
        
if ($edit) {
     $phpgw->preferences->delete("stocks",$sym);
     $phpgw->preferences->change("stocks",urlencode($symbol),urlencode($name));
     $phpgw->preferences->commit();
     Header("Location: " . $phpgw->link($phpgw_info["server"]["webserver_url"] . "/stocks/preferences.php"));
     $phpgw->common->phpgw_exit();
    }
 
     $phpgw->common->phpgw_header();
     echo parse_navbar();

     $t = new Template($phpgw_info["server"]["app_tpl"]);
     $t->set_file(array( "edit" => "preferences_edit.tpl"));
     $t->set_var("actionurl",$phpgw->link("preferences_edit.php"));
     $t->set_var("lang_action",lang("Stock Quote preferences"));     

    $common_hidden_vars =
   "<input type=\"hidden\" name=\"sym\" value=\"$sym\">\n";

     $t->set_var(common_hidden_vars,$common_hidden_vars);

     $t->set_var(th_bg,$phpgw_info["theme"][th_bg]);    
     $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);     
     $t->set_var("tr_color",$tr_color);     
     $t->set_var("h_lang_edit",lang("Edit stock"));
     $t->set_var("lang_symbol",lang("Symbol"));     
     $t->set_var("lang_company",lang("Company name"));


    while ($stock = each($phpgw_info["user"]["preferences"]["stocks"])) {
         if (rawurldecode($stock[0]) == $sym) {
     $t->set_var("symbol",rawurldecode($stock[0]));
     $t->set_var("name",rawurldecode($stock[1]));
       }
     }
     
     $t->set_var("lang_edit",lang("Edit"));

     $t->pparse("out", "edit");

    $phpgw->common->phpgw_footer();
?>
