<?php

{
    
    $d1 = strtolower(substr($phpgw_info["server"]["app_inc"],0,3));
    if($d1 == "htt" || $d1 == "ftp" )
    {
        echo "Failed attempt to break in via an old Security Hole!<br>\n";
        $phpgw->common->phpgw_exit();
    } unset($d1);

    $tmp_app_inc = $phpgw_info["server"]["app_inc"];
    $phpgw_info["server"]["app_inc"] = $phpgw_info["server"]["server_root"]."/comic/inc";

    $phpgw->db->query("select * from phpgw_comic "
                      ."WHERE comic_owner='"
                      .$phpgw_info["user"]["userid"]."'");

    if ($phpgw->db->num_rows())
    {
        $phpgw->db->next_record();

        $data_id      = $phpgw->db->f("comic_frontpage");
        $scale        = $phpgw->db->f("comic_fpscale");
        $censor_level = $phpgw->db->f("comic_censorlvl");
        
        if ($data_id != -1)
        {
            include($phpgw_info["server"]["app_inc"].'/functions.inc.php');

            comic_display_frontpage($data_id, $scale, $censor_level);
        }
    }
    
    $phpgw_info["server"]["app_inc"] = $tmp_app_inc;
}

?>
