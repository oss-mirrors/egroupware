<?php
    /**************************************************************************\
    * phpGroupWare - Daily Comics                                              *
    * http://www.phpgroupware.org                                              *
    * This file written by Sam Wynn <neotexan@wynnsite.com>                    *
    * --------------------------------------------                             *
    *  This program is free software; you can redistribute it and/or modify it *
    *  under the terms of the GNU General Public License as published by the   *
    *  Free Software Foundation; either version 2 of the License, or (at your  *
    *  option) any later version.                                              *
    \**************************************************************************/

    /* $Id$ */
{
    $phpgw_info["flags"] = array("currentapp" => "comic",
                                 "enable_nextmatchs_class" => True,
                                 "enable_network_class" => True);

    include("../header.inc.php");
    
    $phpgw->db->query("select * from phpgw_comic "
                      ."WHERE comic_owner='"
                      .$phpgw_info["user"]["account_id"]."'");

    if ($phpgw->db->num_rows() == 0)
    {
        $phpgw->db->query("insert into phpgw_comic (comic_owner) values ".
                          "('".$phpgw_info["user"]["account_id"]."')");
        $phpgw->db->query("select * from phpgw_comic "
                          ."WHERE comic_owner='"
                          .$phpgw_info["user"]["account_id"]."'");
    }

    $phpgw->db->next_record();

    $comic_list     = explode(":", $phpgw->db->f("comic_list"));
    $comic_scale    = $phpgw->db->f("comic_scale");
    $comic_perpage  = $phpgw->db->f("comic_perpage");
    $user_censorlvl = $phpgw->db->f("comic_censorlvl");
    
    $template_id    = $phpgw->db->f("comic_template");
    
    if (!$page_number)
    {
        $page_number = 0;
    }
    
    comic_display($comic_list, $comic_scale, $comic_perpage, $user_censorlvl,
                  $start, &$comic_left_c, &$comic_right_c, &$comic_center_c,
                  &$matchs_c);

    /**************************************************************************
     * determine the output template
     *************************************************************************/
    $template_format     = sprintf("format%02d", $template_id);
    if (!(file_exists($phpgw_info["server"]["app_tpl"]
                      ."/".$template_format.".comic.tpl")))
    {
        $template_format = "format00";
    }
        
    /**************************************************************************
     * pull it all together
     *************************************************************************/
    $body_tpl = $phpgw->template;
    $body_tpl->set_unknowns("remove");
    $body_tpl->set_file(body, $template_format.".comic.tpl");
    $body_tpl->set_var(array(title        => lang("PhpGroupWare Daily Comics"),
                             matchs       => $matchs_c,
                             comic_left   => $comic_left_c,
                             comic_center => $comic_center_c,
                             comic_right  => $comic_right_c));
    $body_tpl->parse(BODY, "body");
    $body_tpl->p("BODY");
        
    $phpgw->common->phpgw_footer();
}

?>
