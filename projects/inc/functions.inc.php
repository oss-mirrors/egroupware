<?php

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