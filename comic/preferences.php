<?php
    /**************************************************************************\
    * phpGroupWare - Weather Request Preferences                               *
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
    $phpgw_info["flags"]["currentapp"] = "comic";

    include("../header.inc.php");

    $title             = lang("Daily Comic Preferences");
    $layout_label      = lang("Display Layout");
    $template_label    = lang("Template");
    $option_label      = lang("Display Options");
    $perpage_label     = lang("Comics Per Page");
    $scale_label       = lang("Comics Scaled");
    $frontpage_label   = lang("Front Page Comic");
    $fpscale_label     = lang("Front Page Comic Scaled");
    $comic_label       = lang("Comics");
    $action_label      = lang("Submit");
    $reset_label       = lang("Reset");
    $done_label        = lang("Done");
    $comic_size        = 8;
    
    $actionurl         = $phpgw->link($phpgw_info["server"]["webserver_url"]
        ."/comic/preferences.php");
    $doneurl           = $phpgw->link($phpgw_info["server"]["webserver_url"]
        ."/preferences/index.php");
    $message           = "";
    
    if ($submit)
    {
        $message = lang("Comic Preferences Updated");

        if ($data_ids)
        {
            $data_ids = implode($data_ids,":");
        }
        else
        {
            $data_ids = "";
        }
        
        $phpgw->db->lock("phpgw_comic");
        $phpgw->db->query("update phpgw_comic set "
                          ."comic_list='".$data_ids."', "
                          ."comic_scale='".$scale_enabled."', "
                          ."comic_perpage='".$perpage."', "
                          ."comic_frontpage='".$frontpage."', "
                          ."comic_fpscale='".$fpscale_enabled."', "
                          ."comic_template='".$comic_template."' "
                          ."WHERE comic_id='".$comic_id."'");
        $phpgw->db->unlock();
    }

    $phpgw->db->query("select * from phpgw_comic "
                      ."WHERE comic_owner='"
                      .$phpgw_info["user"]["userid"]."'");

    $indexlimit = 0;
    
    if($phpgw->db->next_record())
    {
        $comic_id        = $phpgw->db->f("comic_id");
        $data_ids        = explode(":", $phpgw->db->f("comic_list"));
        $scale_enabled   = $phpgw->db->f("comic_scale");
        $perpage         = $phpgw->db->f("comic_perpage");
        $frontpage       = $phpgw->db->f("comic_frontpage");
        $fpscale_enabled = $phpgw->db->f("comic_fpscale");
        $comic_template  = $phpgw->db->f("comic_template");

        $indexlimit = count($data_ids);
    }

    if ($scale_enabled == 1)
    {
        $scale_checked = "checked";
    }

    if ($fpscale_enabled == 1)
    {
        $fpscale_checked = "checked";
    }

    template_options($comic_template, &$t_options_c, &$t_images_c);
    
    $prefs_tpl = $phpgw->template;
    $prefs_tpl->set_unknowns("remove");
    $prefs_tpl->set_file(
        array(message   => "message.common.tpl",
              prefs     => "prefs.body.tpl",
              perpage   => "option.common.tpl",
              frontpage => "option.common.tpl",
              comic     => "option.common.tpl"));

    for ($loop = 1; $loop <= 15; $loop++)
    {
        $selected = "";
        if ($loop == $perpage)
        {
            $selected = "selected";
        }
        
        $prefs_tpl->set_var(array(OPTION_SELECTED => $selected,
                                  OPTION_VALUE    => $loop,
                                  OPTION_NAME     => $loop));
        $prefs_tpl->parse(option_list, "perpage", TRUE);
    }
    $perpage_c = $prefs_tpl->get("option_list");

    for ($loop = -1; $loop < 1; $loop++)
    {
        $selected = "";
        if ($loop == $frontpage)
        {
            $selected = "selected";
        }

        switch ($loop)
        {
          case -1:
            $name = "None";
            break;
          case 0:
            $name = "Random";
            break;
        }
        
        $prefs_tpl->set_var(array(OPTION_SELECTED => $selected,
                                  OPTION_VALUE    => $loop,
                                  OPTION_NAME     => lang($name)));
        $prefs_tpl->parse(fpage_list, "frontpage", TRUE);
    }

    $phpgw->db->query("select * from phpgw_comic_data order by data_name");

    while ($phpgw->db->next_record())
    {
        $selected = "";
        if ($phpgw->db->f("data_id") == $frontpage)
        {
            $selected = "selected";
        }

        $prefs_tpl->set_var
            (array(OPTION_SELECTED => $selected,
                   OPTION_VALUE    => $phpgw->db->f("data_id"),
                   OPTION_NAME     => $phpgw->db->f("data_name")));
        $prefs_tpl->parse(fpage_list, "frontpage", TRUE);

        for ($index = 0; $index < $indexlimit; $index++)
        {
            $selected = "";
            if ($phpgw->db->f("data_id") == $data_ids[$index])
            {
                $selected = "selected";
                break;
            }
            
        }
        
        $prefs_tpl->set_var
            (array(OPTION_SELECTED => $selected,
                   OPTION_VALUE    => $phpgw->db->f("data_id"),
                   OPTION_NAME     => $phpgw->db->f("data_name")));
        $prefs_tpl->parse(comic_list, "comic", TRUE);
    }
    
    $frontpage_c = $prefs_tpl->get("fpage_list");
    $comic_c     = $prefs_tpl->get("comic_list");
        
    $prefs_tpl->
        set_var(array
                (messagename      => $message,
                 title            => $title,
                 action_url       => $actionurl,
		 action_label     => $action_label,
                 done_url         => $doneurl,
		 done_label       => $done_label,
		 reset_label      => $reset_label,
                 layout_label     => $layout_label,
                 template_label   => $template_label,
                 option_label     => $option_label,
                 perpage_label    => $perpage_label,
                 scale_label      => $scale_label,
                 scale_checked    => $scale_checked,
                 frontpage_label  => $frontpage_label,
                 fpscale_label    => $fpscale_label,
                 fpscale_checked  => $fpscale_checked,
                 comic_label      => $comic_label,
                 comic_size       => $comic_size,
                 template_options => $t_options_c,
                 template_images  => $t_images_c,
                 perpage_options  => $perpage_c,
                 frontpage_options=> $frontpage_c,
                 comic_options    => $comic_c,
                 comic_id         => $comic_id,
                 th_bg            => $phpgw_info["theme"]["th_bg"],
                 th_text          => $phpgw_info["theme"]["th_text"]));

    $prefs_tpl->parse(message_part, "message");
    $message_c = $prefs_tpl->get("message_part");

    $prefs_tpl->parse(body_part, "prefs");
    $body_c = $prefs_tpl->get("body_part");
    
    /**************************************************************************
     * pull it all together
     *************************************************************************/
    $body_tpl = $phpgw->template;
    $body_tpl->set_unknowns("remove");
    $body_tpl->set_file(body, "prefs.common.tpl");
    $body_tpl->set_var(array(preferences_message => $message_c,
                             preferences_body    => $body_c));
    $body_tpl->parse(BODY, "body");
    $body_tpl->p("BODY");

    $phpgw->common->phpgw_footer();
}

?>
