<?php
  /**************************************************************************\                                            
  * phpGroupWare - projects/projectdelivery                                  *                                            
  * (http://www.phpgroupware.org)                                            *                                            
  * Written by Bettina Gille  [aeb@hansenet.de]                              *                                            
  *          & Jens Lentfoehr <sw@lf.shlink.de>                              *                                            
  * --------------------------------------------------------                 *                                            
  *  This program is free software; you can redistribute it and/or modify it *                                            
  *  under the terms of the GNU General Public License as published by the   *                                            
  *  Free Software Foundation; either version 2 of the License, or (at your  *                                            
  *  option) any later version.                                              *                                            
  \**************************************************************************/
/* $Id$ */

// returns wether the current acount is in group projectAdmin or not.

  function isprojectadmin() 
  {
  global $phpgw;
  global $phpgw_info;
  $phpgw->db->query("select group_id from groups where group_name = 'projectAdmin'");
  if ($phpgw->db->next_record()) {
    $group_id = $phpgw->db->f("group_id");
    $phpgw->db->query("select account_id from accounts where account_groups like '%,$group_id%' and account_id='".($phpgw_info["user"]["account_id"])."'");
    $phpgw->db->next_record();
    if($phpgw->db->f("account_id") == $phpgw_info["user"]["account_id"])
       return 1;
    }
   return 0;
  }

?>