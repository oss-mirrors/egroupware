<?php
    /**************************************************************************\
    * phpGroupWare - Daily Comic Admin Link Data                               *
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
                                 "admin_header" => TRUE);

    include("../header.inc.php");
    include("inc/comic_data.inc.php");
    
    $title             = lang("Daily Comics Data");

    $done_label        = lang("Done");
    $doneurl           = $phpgw->link('/admin/index.php');

    $message           = "";
    
    if ($submit)
    {
        switch($act)
        {
          case "edit":
            $message = "modification";
            break;
          case "delete":
            $message = "deletion";
            break;
          case "add":
            $message = "addition";
            break;
        }
        $message = lang("Performed %1 of element", $message);
    }

    $other_c           = "";

    switch($act)
    {
      case "edit":
        if ($submit)
        {
            $phpgw->db->lock("phpgw_comic_data");
            $phpgw->db->query("update phpgw_comic_data set "
                              ."comic_name='".$comic_name."' "
                              ."where data_id='".$data_id."'");
            $phpgw->db->unlock();

            comic_table($order, $sort, $filter, $start, $query, $qfield, $table_c);
            comic_entry('', "add", $order, $sort, $filter,
                         $start, $query, $qfield, $add_c);
        }
        else
        {
            comic_table($order, $sort, $filter, $start, $query, $qfield, $table_c);
            comic_entry('', "add", $order, $sort, $filter,
                         $start, $query, $qfield, $add_c);
            comic_entry($con, $act, $order, $sort, $filter,
                         $start, $query, $qfield, $other_c);
        }
        break;
      case "delete":
        if ($submit)
        {
            $phpgw->db->lock("phpgw_comic_data");
            $phpgw->db->query("delete from phpgw_comic_data "
                              ."where data_id='".$data_id."'");
            $phpgw->db->unlock();

            comic_table($order, $sort, $filter, $start, $query, $qfield, $table_c);
            comic_entry('', "add", $order, $sort, $filter,
                         $start, $query, $qfield, $add_c);
        }
        else
        {
            comic_table($order, $sort, $filter, $start, $query, $qfield, $table_c);
            comic_entry('', "add", $order, $sort, $filter,
                         $start, $query, $qfield, $add_c);
            comic_entry($con, $act, $order, $sort, $filter,
                         $start, $query, $qfield, $other_c);
        }
        break;
      case "add":
        if ($submit)
        {
            $phpgw->db->lock("phpgw_comic_data");
            $phpgw->db->query("insert into phpgw_comic_data (comic_name)"
                              ."values ('"
                              .$comic_name."')");
            $phpgw->db->unlock();
        }
        comic_table($order, $sort, $filter, $start, $query, $qfield, $table_c);
        comic_entry('', "add", $order, $sort, $filter,
                     $start, $query, $qfield, $add_c);
        break;
      default:
        comic_table($order, $sort, $filter, $start, $query, $qfield, $table_c);
        comic_entry('', "add", $order, $sort, $filter,
                     $start, $query, $qfield, $add_c);
        break;
    }
    
    $comics_tpl =
        CreateObject('phpgwapi.Template',
                     $phpgw->common->get_tpl_dir('comic'));
    $comics_tpl->set_unknowns("remove");
    $comics_tpl->set_file(
        array(message   => "message.common.tpl",
              comics    => "admin.datalist.tpl"));
    $comics_tpl->
        set_var(array
                (messagename      => $message,
                 title            => $title,

                 done_url         => $doneurl,
		 done_label       => $done_label,

                 data_table       => $table_c,
                 add_form         => $add_c,
                 other_form       => $other_c
                 ));

    $comics_tpl->parse(message_part, "message");
    $message_c = $comics_tpl->get("message_part");

    $comics_tpl->parse(body_part, "comics");
    $body_c = $comics_tpl->get("body_part");
    
    /**************************************************************************
     * pull it all together
     *************************************************************************/
    $body_tpl =
        CreateObject('phpgwapi.Template',
                     $phpgw->common->get_tpl_dir('comic'));
    $body_tpl->set_unknowns("remove");
    $body_tpl->set_file(body, "admin.common.tpl");
    $body_tpl->set_var(array(admin_message => $message_c,
                             admin_body    => $body_c));
    $body_tpl->parse(BODY, "body");
    $body_tpl->p("BODY");

    $phpgw->common->phpgw_footer();
}

?>
