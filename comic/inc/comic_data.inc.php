<?php
  /**************************************************************************\
  * phpGroupWare - Daily Comic Data Functions                                *
  * http://www.phpgroupware.org                                              *
  * This file written by Sam Wynn <neotexan@wynnsite.com>                    *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

function comic_table($order, $sort, $filter, $start, $query, $qfield, &$table_c)
{
    global $phpgw, $phpgw_info, $g_censor_level;
    
    $edit_label   = lang("Edit");
    $delete_label = lang("Delete");
    $searchobj = array(array("data_title",     "Title"),
                       array("data_class",     "Genre"),
                       array("data_censorlvl", "Rated"),
                       array("data_parser",    "Parser"),
                       array("data_resolve",   "Resolve"));
    if ($order)
    {
        $ordermethod = "order by $order $sort ";
    }
    else
    {
        $ordermethod = "order by data_title asc ";
    }
    
    if (! $sort)
    {
        $sort = "desc";
    }
    
    if (! $start)
    {
        $start = 0;
    }
    
    if (! $filter)
    {
        $filter = "none";
    }
    
    $limit = $phpgw->db->limit($start);

    $likeness = "like";
    $myquery  = "%$query%";
    
    if ($qfield == "data_censorlvl")
    {
        while(list($key,$value) = each($g_censor_level))
        {
            if (ucwords($query) == $value)
            {
                $myquery = $key;
                $likeness = "=";
                break;
            }
        }
    }
    
    if (!$query)
    {
        $phpgw->db->query("select count(*) from phpgw_comic_data "
                          .$ordermethod);
    }
    else
    {
        $phpgw->db->query("select count(*) from phpgw_comic_data "
                          ."WHERE $qfield $likeness '$myquery' "
                          .$ordermethod);
    }
    
    $phpgw->db->next_record();

    if ($phpgw->db->f(0) >
        $phpgw_info["user"]["preferences"]["common"]["maxmatchs"])
    {
        $match_comment = 
            lang("showing x - x of x",($start + 1),
                 ($start +
                  $phpgw_info["user"]["preferences"]["common"]["maxmatchs"]),
                 $phpgw->db->f(0));
    }
    else
    {
        $match_comment = lang("showing x",$phpgw->db->f(0));
    }
    
    $match_bar =
        $phpgw->nextmatchs->show_tpl("/comic/admin_comics.php",
                                     $start,$phpgw->db->f(0), "",
                                     "85%", $phpgw_info["theme"]["th_bg"],
                                     $searchobj,0);
    $comic_label = 
        $phpgw->nextmatchs->show_sort_order($sort,"data_title",$order,
                                            "/comic/admin_comics.php",
                                            lang("Title"));
    $comic_parser_label = 
        $phpgw->nextmatchs->show_sort_order($sort,"data_parser",$order,
                                            "/comic/admin_comics.php",
                                            lang("Parser"));
    $comic_resolve_label = 
        $phpgw->nextmatchs->show_sort_order($sort,"data_resolve",$order,
                                            "/comic/admin_comics.php",
                                            lang("Resolve"));
    $comic_class_label = 
        $phpgw->nextmatchs->show_sort_order($sort,"data_class",$order,
                                            "/comic/admin_comics.php",
                                            lang("Genre"));

    $comic_censor_label = 
        $phpgw->nextmatchs->show_sort_order($sort,"data_censorlvl",$order,
                                            "/comic/admin_comics.php",
                                            lang("Rated"));
     
    if (! $query)
    {
        $phpgw->db->query("select * from phpgw_comic_data "
                          .$ordermethod
                          .$limit);
    }
    else
    {
        $phpgw->db->query("select * from phpgw_comic_data "
                          ."WHERE $qfield $likeness '$myquery' "
                          .$ordermethod
                          .$limit);
    }
    
    $table_tpl =
        CreateObject('phpgwapi.Template',
                     $phpgw->common->get_tpl_dir('comic'));
    $table_tpl->set_unknowns("remove");
    $table_tpl->set_file(array(table => "table.comics.tpl",
                               row   => "row.comics.tpl"));
    
    while ($phpgw->db->next_record()) 
    {
        $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
        
        $comic_id = $phpgw->db->f("data_id");
        $comic_encoded = urlencode($comic_id);
        
        $comic_title = $phpgw->db->f("data_title");
        if (! $comic_title)
        {
            $comic_title = "&nbsp;";
        }

        $comic_parser = $phpgw->db->f("data_parser");
        if (! $comic_parser)
        {
            $comic_parser = "&nbsp;";
        }

        $comic_resolve = $phpgw->db->f("data_resolve");
        if (! $comic_resolve)
        {
            $comic_resolve = "&nbsp;";
        }

        $comic_class = $phpgw->db->f("data_class");
        if (! $comic_class)
        {
            $comic_class = "&nbsp;";
        }

        $comic_censor = $g_censor_level[$phpgw->db->f("data_censorlvl")];
        
        $table_tpl->
            set_var(
                array(row_color    => $tr_color,
                      comic_name   => $comic_title,
                      comic_parser => $comic_parser,
                      comic_resolve=> $comic_resolve,
                      comic_class  => $comic_class,
                      comic_censor => $comic_censor,
                      edit_url     => $phpgw->link("/comic/admin_comics.php",
                                                   "con=".$comic_encoded
                                                   ."&act=edit"
                                                   ."&start=$start"
                                                   ."&order=$order"
                                                   ."&filter=$filter"
                                                   ."&sort=$sort"
                                                   ."&query="
                                                   .urlencode($query) 
                                                   ."&qfield=$qfield"),
                      edit_label   => $edit_label,
                      delete_url   => $phpgw->link("/comic/admin_comics.php",
                                                   "con=".$comic_encoded
                                                   ."&act=delete"
                                                   ."&start=$start"
                                                   ."&order=$order"
                                                   ."&filter=$filter"
                                                   ."&sort=$sort"
                                                   ."&query="
                                                   .urlencode($query) 
                                                   ."&qfield=$qfield"),
                      delete_label => $delete_label));
        $table_tpl->parse(comic_rows, "row", True);
    }
    
    $table_tpl->
        set_var(array
                (th_bg                => $phpgw_info["theme"]["th_bg"],

                 total_matchs         => $match_comment,
                 next_matchs          => $match_bar,
                 
                 comic_label          => $comic_label,
                 comic_parser_label   => $comic_parser_label,
                 comic_resolve_label  => $comic_resolve_label,
                 comic_class_label    => $comic_class_label,
                 comic_censor_label   => $comic_censor_label,
                 
                 edit_label           => $edit_label,
                 delete_label         => $delete_label,
                 
                 action_url           => $action_url,
                 action_label         => lang($act),
                 reset_label          => lang("Reset")));
    
    $table_tpl->parse(table_part, "table");
    $table_c = $table_tpl->get("table_part");
}


function comic_entry($con, $act, $order, $sort, $filter, $start, $query, $qfield, &$form_c)
{
    global $phpgw, $phpgw_info;

    $action_url   = $phpgw->link("/comic/admin_comics.php",
                                 "act=$act"
                                 ."&start=$start&order=$order&filter=$filter"
                                 ."&sort=$sort"
                                 ."&query=".urlencode($query) 
                                 ."&qfield=$qfield");
    
    switch($act)
    {
      case "add":
        $bg_color = $phpgw_info["theme"]["th_bg"];
        break;
      case "delete":
        $bg_color = $phpgw_info["theme"]["bg07"];
        break;
      default:
        $bg_color = $phpgw_info["theme"]["table_bg"];
        break;
    }

    $comic_name = "";
    
    if ($con != "")
    {
        $phpgw->db->query("select * from phpgw_comic_data where data_id=$con");

        $phpgw->db->next_record();

        $comic_name = $phpgw->db->f("data_title");
    }
        
    $modify_tpl =
        CreateObject('phpgwapi.Template',
                     $phpgw->common->get_tpl_dir('comic'));
    $modify_tpl->set_unknowns("remove");
    $modify_tpl->set_file(form, "form.comics.tpl");
    
    $modify_tpl->
        set_var(array
                (bg_color         => $bg_color,
                 
                 comic_id        => $con,
                 comic_label     => lang("Title"),
                 comic_name      => $comic_name,
                 
                 action_url       => $action_url,
                 action_label     => lang($act),
                 reset_label      => lang("Reset")));
    
    $modify_tpl->parse(form_part, "form");
    $form_c = $modify_tpl->get("form_part");
}

?>
