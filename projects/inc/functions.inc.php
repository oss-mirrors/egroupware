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

  function isprojectadmin() {
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


  $projectid_type = "hex";

  function add_leading_zero($num)  {                                                                      
     global $projectid_type;                                             
                                                                         
     if ($projectid_type == "hex") {                                     
        $num = hexdec($num);                                             
        $num++;                                                          
        $num = dechex($num);                                             
     } else {                                                             
        $num++;                                                          
     }                                                                   
                                                                         
     if (strlen($num) == 4)                                              
        $return = $num;                                                  
     if (strlen($num) == 3)                                              
        $return = "0$num";                                               
     if (strlen($num) == 2)                                              
        $return = "00$num";                                              
     if (strlen($num) == 1)                                              
        $return = "000$num";                                             
     if (strlen($num) == 0)                                              
        $return = "0001";                                                
                                                                         
     return strtoupper($return);                                         
  }

  $year = $phpgw->common->show_date(time(),"Y"); 
  function create_projectid($year)                                                                  
  {                                                                                                   
     global $phpgw;                                                                                    
     global $year;

//     $year = $phpgw->common->show_date(time(),"Y");

     $phpgw->db->query("select max(num) from p_projects where num like ('$year%')");         
     $phpgw->db->next_record();                                                                       
     $max = add_leading_zero(substr($phpgw->db->f(0),4));                                             
                                                                                                      
     return $year .$max; 
      
    }

?>