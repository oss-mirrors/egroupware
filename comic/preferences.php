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
    $censor_label      = lang("Censorship Level");
    $comic_label       = lang("Comics");
    $action_label      = lang("Submit");
    $reset_label       = lang("Reset");
    $done_label        = lang("Done");
    $comic_size        = 8;
    
    $actionurl         = $phpgw->link('/comic/preferences.php');
    $doneurl           = $phpgw->link('/preferences/index.php');
	$returnmain        = intval(get_var('returnmain',array('GET','POST')));
	if ( $returnmain == 1 ) 
	{
		$doneurl       = $phpgw->link('/comic/index.php');
	}

    $message           = "";
    
    if ($_POST['submit'])
    {
        $message = lang("Comic Preferences Updated");

        if ($_POST['data_ids'])
        {
            $data_ids = implode($_POST['data_ids'],":");
        }
        else
        {
            $data_ids = "";
        }
        
		$scale_enabled = intval($_POST['scale_enabled']);
		$perpage = intval($_POST['perpage']);
		$frontpage = intval($_POST['frontpage']);
		$fpscale_enabled = intval($_POST['fpscale_enabled']);
		$censor_level = intval($_POST['censor_level']);
		$comic_template = intval($_POST['comic_template']);
		$comic_id = intval($_POST['comic_id']);

        $phpgw->db->lock("phpgw_comic");
        $phpgw->db->query("update phpgw_comic set "
                          ."comic_list='".$data_ids."', "
                          ."comic_scale='".$scale_enabled."', "
                          ."comic_perpage='".$perpage."', "
                          ."comic_frontpage='".$frontpage."', "
                          ."comic_fpscale='".$fpscale_enabled."', "
                          ."comic_censorlvl='".$censor_level."', "
                          ."comic_template='".$comic_template."' "
                          ."WHERE comic_id='".$comic_id."'");
        $phpgw->db->unlock();
    }

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

    $comic_id        = $phpgw->db->f("comic_id");
    $data_ids        = explode(":", $phpgw->db->f("comic_list"));
    $scale_enabled   = $phpgw->db->f("comic_scale");
    $perpage         = $phpgw->db->f("comic_perpage");
    $frontpage       = $phpgw->db->f("comic_frontpage");
    $fpscale_enabled = $phpgw->db->f("comic_fpscale");
    $censor_level    = $phpgw->db->f("comic_censorlvl");
    $comic_template  = $phpgw->db->f("comic_template");
    
    $indexlimit = count($data_ids);

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
              comic     => "option.common.tpl",
              censor    => "option.common.tpl"));

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

    for ($loop = 0; $loop < count($g_censor_level); $loop++)
    {
        $selected = "";
        if ($censor_level == $loop)
        {
            $selected = "selected";
        }
        
        $prefs_tpl->set_var(array(OPTION_SELECTED => $selected,
                                  OPTION_VALUE    => $loop,
                                  OPTION_NAME     => $g_censor_level[$loop]));
        $prefs_tpl->parse(censor_list, "censor", TRUE);
    }
    $censor_c = $prefs_tpl->get("censor_list");
    
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

    $phpgw->db->query("select * from phpgw_comic_data "
                      ."where data_enabled='T' order by data_name");

    $index = 0;
    
    asort($data_ids);
    
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
                   OPTION_NAME     => $phpgw->db->f("data_title")));
        $prefs_tpl->parse(fpage_list, "frontpage", TRUE);


        $selected = "";
        if ($phpgw->db->f("data_id") == $data_ids[$index])
        {
            $index++;
            
            $selected = "selected";
        }

        $name = sprintf("%s - %s",
                        $phpgw->db->f("data_resolve"),
                        $phpgw->db->f("data_title"));
        
        $prefs_tpl->set_var
            (array(OPTION_SELECTED => $selected,
                   OPTION_VALUE    => $phpgw->db->f("data_id"),
                   OPTION_NAME     => $name));
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
                 censor_label     => $censor_label,
                 censor_options   => $censor_c,
                 comic_options    => $comic_c,
                 comic_id         => $comic_id,
                 returnmain       => $returnmain,
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
