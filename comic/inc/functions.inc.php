<?php
  /**************************************************************************\
  * phpGroupWare - Comic Functions                                           *
  * http://www.phpgroupware.org                                              *
  * This file written by Sam Wynn <neotexan@wynnsite.com>                    *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

define("COMIC_STATIC",  0);
define("COMIC_SNARFED", 1);

define("STD_SUCCESS",   0);
define("STD_ERROR",     1);
define("STD_WARNING",   2);
define("STD_FORBIDDEN", 3);

$g_image_type = array(COMIC_PNG => "png",
                      COMIC_GIF => "gif");
$g_censor_level = array(0 => 'G',
                        1 => 'PG',
                        2 => 'R');

function comic_initialize_admin()
{
    global $phpgw;

    $phpgw->db->query("insert into phpgw_comic_admin values (0,0,0,0,'G')");
}

function comic_resolve_url($coded_url, &$comic_url)
{
    $status = STD_SUCCESS;
    
    if (preg_match_all("/{[A-Za-z0-9\-]*}/", $coded_url, $strings))
    {
        $comic_url  = $coded_url;
        $timeoffset = 0;
        
        /**********************************************************************
         * replace matches
         *********************************************************************/
        for ($loop = 0; $loop < sizeof($strings[0]); $loop++)
        {
            switch($strings[0][$loop])
            {
              case "{y}":
                $CurrentYear  = date("y", time() - $timeoffset);

                $comic_url =
                    str_replace($strings[0][$loop], $CurrentYear,
                                $comic_url);
                break;
              case "{Ml}":
                $CurrentMonth = date("M", time() - $timeoffset);
                $CurrentMonth = strtolower($CurrentMonth);

                $comic_url =
                    str_replace($strings[0][$loop], $CurrentMonth,
                                $comic_url);
                break;
              case "{Y}":
                $CurrentYear = date("Y", time() - $timeoffset);
                $comic_url =
                    str_replace($strings[0][$loop], $CurrentYear,
                                $comic_url);
                break;
              case "{ym}":
                $CurrentYearMonth = date("ym", time() - $timeoffset);
                $comic_url = 
                    str_replace($strings[0][$loop], $CurrentYearMonth,
                                $comic_url);
                break;
              case "{ymd}":
                $CurrentYearMonthDay = date("ymd", time() - $timeoffset);
                $comic_url = 
                    str_replace($strings[0][$loop], $CurrentYearMonthDay,
                                $comic_url);
                break;
              case "{-1d}":
                $timeoffset =  (1*3600*24);
                $comic_url =
                    str_replace($strings[0][$loop], "", $comic_url);
                break;
              case "{-14d}":
                $timeoffset =  (14*3600*24);
                $comic_url =
                    str_replace($strings[0][$loop], "", $comic_url);
                break;
                
              default:
                $status = STD_ERROR;
                break;
            }
        }
    }
    return $status;
}

function comic_snarf(&$comic_url)
{
    $status = STD_SUCCESS;
    
    return $status;
}

function comic_error($gdenabled, $image_type, $image_location,
                     $status, &$comic_url)
{
    global $phpgw_info;

    switch ($image_location)
    {
      case 'L':
      case 'R':
        $image_size = "_sm";
        break;
      default:
        $image_size = "";
        break;
    }
    
    $comic_url = $phpgw_info["server"]["webserver_url"]."/"
        .$phpgw_info["flags"]["currentapp"]."/images/template"
        .$image_size.".png";
}

function comic_match_bar($start, $end, $indexlimit, &$matchs_c)
{
    global $phpgw, $phpgw_info;

    switch ($indexlimit)
    {
      case 0:
      case 1:
        {
            $showstring =
                lang("showing x", $indexlimit);
        }
        break;

      default:
        {
            $showstring =
                lang("showing x - x of x",
                     ($start + 1), $end, $indexlimit);
        }
        break;
    }

    $matchs_tpl = $phpgw->template;
    $matchs_tpl->set_unknowns("remove");
    $matchs_tpl->set_file(matchs, "matchs.comic.tpl");
    $matchs_tpl->
        set_var
        (array(next_matchs_left  =>
               $phpgw->nextmatchs->left($PHP_SELF,$start,$indexlimit,""),
               next_matchs_label => $showstring,
               next_matchs_right =>
               $phpgw->nextmatchs->right($PHP_SELF,$start,$indexlimit,""),
               navbar_bg         => $phpgw_info["theme"]["navbar_bg"],
               navbar_text       => $phpgw_info["theme"]["navbar_text"]));
    $matchs_tpl->parse(MATCHS, "matchs");
    $matchs_c = $matchs_tpl->get("MATCHS");
}

function comic_display($comic_id, $comic_perpage, $start,
                       &$comic_left_c, &$comic_right_c, &$comic_center_c,
                       &$matchs_c)
{
    global $phpgw, $phpgw_info;

    $indexlimit = count($comic_id);

    /**************************************************************************
     * no reason to generate data if don't have any comics
     *************************************************************************/
    if ($indexlimit > 0)
    {
        if (!$start)
        {
            $start = 0;
        }

        $end = $start + $comic_perpage;
        
        if ($end > $indexlimit)
        {
            $end = $indexlimit;
        }
    
        /**********************************************************************
         * do the next matchs bar
         *********************************************************************/
        $temp = $phpgw_info["user"]["preferences"]["common"]["maxmatchs"];
        $phpgw_info["user"]["preferences"]["common"]["maxmatchs"] = $comic_perpage;
        comic_match_bar($start, $end, $indexlimit, &$matchs_c);
        $phpgw_info["user"]["preferences"]["common"]["maxmatchs"] = $temp;

        /**********************************************************************
         * get the admin settings
         *********************************************************************/
        $phpgw->db->query("select * from phpgw_comic_admin");
        if ( $phpgw->db->num_rows())
        {
            comic_initialize_admin();

            $phpgw->db->query("select * from phpgw_comic_admin");
        }
        $phpgw->db->next_record();
        
        $image_src  = $phpgw->db->f("admin_imgsrc");
        $gdenabled  = $phpgw->db->f("admin_gdenabled");
        $image_type = $phpgw->db->f("admin_gdtype");
        

        /**********************************************************************
         * start our template
         *********************************************************************/
        $comic_tpl = $phpgw->template;
        $comic_tpl->set_unknowns("remove");
        $comic_tpl->set_file(
            array(tableleft   => "table.left.tpl",
                  tableright  => "table.right.tpl",
                  tablecenter => "table.center.tpl",
                  row         => "row.common.tpl"));
        
        /**********************************************************************
         * step through the comics
         *********************************************************************/
        for ($index=$start; $index < $end; $index++)
        {
            /******************************************************************
             * get the comic data
             *****************************************************************/
            $phpgw->db->query("select * from phpgw_comic_data "
                              ."WHERE data_id='"
                              .$comic_id[$index]."'");

            if ($phpgw->db->num_rows())
            {
                $phpgw->db->next_record();

                $image_location = $phpgw->db->f("data_location");
                $comic_url      = "";
                
                /**************************************************************
                 * resolve the url
                 *************************************************************/
                $status = comic_resolve_url($phpgw->db->f("data_baseurl"),
                                               &$comic_url);
            
                /**************************************************************
                 * snarf the image
                 *************************************************************/
                if (($image_src == COMIC_SNARFED) && ($status == STD_SUCCESS))
                {
                    $status = comic_snarf(&$comic_url);
                }

                /**************************************************************
                 * if no image available, then give error image
                 *************************************************************/
                if ($status != STD_SUCCESS)
                {
                    comic_error($gdenabled, $image_type, $image_location,
                                $status, &$comic_url);
                }

                /**************************************************************
                 * image scaling might be a good option...should evaluate
                 * that as a user option...would be all or nothing...[neotexan]
                 *************************************************************/
                
                /**************************************************************
                 * find which template set (left/center/right) gets comic
                 *************************************************************/
                switch($image_location)
                {
                  case 'L':
                    $side = "left";
                    $image_width = 280;
                    break;
                  case 'R':
                    $side = "right";
                    $image_width = 280;
                    break;
                  case 'C':
                    $side = "center";
                    $image_width = 580;
                    break;
                }

                $name = lang("%1 by %2",
                             $phpgw->db->f("data_title"),
                             $phpgw->db->f("data_author"));
                $comment =  lang("Visit %1", $phpgw->db->f("data_linkurl"));
                
                $comic_tpl->
                    set_var
                    (array
                     (image_url   => $comic_url,
                      image_width => $image_width,
                      link_url    => $phpgw->db->f("data_linkurl"),
                      comment     => $comment,
                      name        => $name,
                      th_bg       => $phpgw_info["theme"]["th_bg"],
                      th_text     => $phpgw_info["theme"]["th_text"]));
                $comic_tpl->parse($side."_part", "row", TRUE);
            }
        }
        
        /**********************************************************************
         * finish our template
         *********************************************************************/
        $comic_tpl->parse(TABLELEFT,   "tableleft");
        $comic_tpl->parse(TABLERIGHT,  "tableright");
        $comic_tpl->parse(TABLECENTER, "tablecenter");
        $comic_left_c   = $comic_tpl->get("TABLELEFT");
        $comic_right_c  = $comic_tpl->get("TABLERIGHT");
        $comic_center_c = $comic_tpl->get("TABLECENTER");
    }
}

function template_options($app_template, &$options_c, &$images_c)
{
    global $phpgw, $phpgw_info;

    $appname = $phpgw_info["flags"]["currentapp"];
    
    $directory = opendir($phpgw_info["server"]["app_tpl"]);

    $index=0;

    while ($filename = readdir($directory))
    {
        if (eregi("format[0-9]{2}.$appname.tpl", $filename, $match))
        {
            $file_ar[$index] = $match[0];
            $index++;
        }
    }

    closedir($directory);

    for ($loop=0; $loop < $index; $loop++)
    {
        eregi("[0-9]{2}", $file_ar[$loop], $tid);
        eregi("format[0-9]{2}", $file_ar[$loop], $tname);

        $template_id = "$tid[0]";
        $template_name["$template_id"] = $tname[0];
    }

    asort($template_name);

    /**************************************************************************
     * start our template
     *************************************************************************/
    $image_tpl = $phpgw->template;
    $image_tpl->set_unknowns("remove");
    $image_tpl->set_file(
        array(options => "option.common.tpl",
              rows    => "row.images.tpl",
              cells   => "cell.images.tpl"));

    while (list($value, $name) = each($template_name))
    {
        $selected = "";
        if ((int)$value == $app_template)
        {
            $selected = "selected";
        }

        $image_tpl->set_var(array(OPTION_SELECTED => $selected,
                                  OPTION_VALUE    => (int)$value,
                                  OPTION_NAME     => $name));
        
        $image_tpl->parse(option_list, "options", TRUE);
    }
    $options_c = $image_tpl->get("option_list");
    
    reset($template_name);
    $counter = 0;

    while (list($value, $name) = each($template_name))
    {
        $index--;
        
        $imgname = $name.".gif";

        $filename_f =
            $phpgw->common->get_image_dir($appname)."/".$imgname;
        $filename_a =
            $phpgw_info["server"]["app_images"]."/".$imgname;

        if (file_exists($filename_f))
        {
            $counter++;

            $image_tpl->set_var(array(image_number => $name,
                                      image_url    => $filename_a));
            $image_tpl->parse(image_row, "cells", TRUE);
        }
        
        if (($counter == 5) || ($index == 0))
        {
            $cells_c = $image_tpl->get("image_row");
            
            $image_tpl->set_var(image_cells, $cells_c);
            $image_tpl->parse(IMAGE_ROWS, rows, TRUE);
            
            $counter = 0;
        }
    }
    $images_c = $image_tpl->get("IMAGE_ROWS");
}

?>
