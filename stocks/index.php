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

    $phpgw_info["flags"] = array("currentapp" => "stocks", 
                               "enable_network_class" => True);

    include("../header.inc.php");
        
    $t = CreateObject('phpgwapi.Template',$phpgw->common->get_tpl_dir('stocks'));                                                                                                                                     
    $t->set_file(array( "quotes_list" => "main.tpl"));
     
    if ($phpgw_info["user"]["preferences"]["stocks"]["disabled"]) { $t->set_var("quotes",return_quotes($quotes)); }
    else { $t->set_var("quotes",""); }
  
    $t->pparse("out", "quotes_list");
  
    $phpgw->common->phpgw_footer();
?>