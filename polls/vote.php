<?php
  if ($submit) {
     $phpgw_info["flags"] = array("noheader" => True, "nonavbar" => True);
  }

  $phpgw_info["flags"]["currentapp"] = "polls";
  $phpgw_info["flags"]["enable_nextmatchs_class"] = True;
  include("../header.inc.php");

  if ($submit) {
//     if (! $phpgw->acl->check("polls_single_vote",2)) {
echo "PASSED ACL";
        if (! verify_uservote($poll_id)) {
           Header("Location: " . $phpgw->link($phpgw_info["server"]["webserver_url"] . "/polls/vote.php","show_results=$poll_id"));
           exit;
        }
//     }
//        $phpgw->db->lock(array("polls_data","polls_user"));
        $phpgw->db->query("UPDATE phpgw_polls_data SET option_count=option_count+1 WHERE "
                        . "poll_id='$poll_id' AND vote_id='$poll_voteNr'",__LINE__,__FILE__);
        $phpgw->db->query("insert into phpgw_polls_user values ('$poll_id','','"
                        . $phpgw_info["user"]["account_id"] . "','" . time() . "')",__LINE__,__FILE__);
//        $phpgw->db->unlock();
//     }
     Header("Location: " . $phpgw->link($phpgw_info["server"]["webserver_url"] . "/polls/vote.php","show_results=$poll_id"));
     exit;
  }
  if ($show_results) {
     poll_viewResults($show_results);
  }
?>
