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
define("STD_CENSOR",    4);
define("STD_NOREMOTE",  5);
define("STD_CURRENT",   6);
define("STD_TEMPLATE",  7);

$g_censor_level = array(0 => 'G',
                        1 => 'PG',
                        2 => 'R');

$g_image_source = array(0 => 'STATIC',
                        1 => 'SNARFED');

function comic_initialize_admin()
{
    global $phpgw;

    $phpgw->db->query("insert into phpgw_comic_admin values (0,0,0,0)");
}

function comic_resolve_url($remote_enabled, &$comic_url, &$comic_day)
{
    global $phpgw;
    
    $status = STD_SUCCESS;

    /**************************************************************************
     * first check to see if we have the url already for today
     *************************************************************************/
    if ($phpgw->db->f("data_date") == (int)date("Ymd"))
    {
        $status = STD_CURRENT;
        $comic_url = $phpgw->db->f("data_imageurl");
    }
    else
    {
        /**********************************************************************
         * resolve our urls
         *********************************************************************/
        switch ($phpgw->db->f("data_resolve"))
        {
          case "Static":
            $status = comic_resolve_static(&$comic_url, &$comic_day);
            break;
          case "Remote":
            $status = comic_resolve_remote($remote_enabled,
                                           &$comic_url, &$comic_day);
            break;
        }
    }
    
    return $status;
}

function comic_resolve_static(&$comic_url, &$comic_day)
{
    global $phpgw;
    
    $status       = STD_SUCCESS;
    $comic_time   = time();
    $comic_url    = $phpgw->db->f("data_baseurl");

    /**************************************************************************
     * rather straight forward resolve of the url
     *************************************************************************/
    $status       = comic_resolver(&$comic_url, &$comic_time);

    $comic_day    = substr(date("D", $comic_time),0,2);

    return $status;
}

function comic_resolve_remote($remote_enabled, &$comic_url, &$comic_day)
{
    global $phpgw;
    
    $status       = STD_SUCCESS;
    $comic_time   = time();

    /**************************************************************************
     * generate the resolver components
     *************************************************************************/
    switch ($phpgw->db->f("data_parser"))
    {
      case "United":
        break;
      case "King":
        break;
      case "Comics":
        break;
      case "Comiczone7":
        break;
      case "Comiczone10":
        break;
      case "Creators":
        break;
      default:
        $baseurl      = $phpgw->db->f("data_baseurl");
        $parseurl     = $phpgw->db->f("data_parseurl");
        $parse_expr   = $phpgw->db->f("data_parsexpr");
        
        $status = comic_resolver(&$baseurl, &$comic_time);

        if ($status == STD_SUCCESS)
        {
            $status = comic_resolver(&$parseurl, &$comic_time);
        }
        if ($status == STD_SUCCESS)
        {
            $status = comic_resolver(&$parse_expr, &$comic_time);
        }
        if ($status == STD_SUCCESS)
        {
            $fetch_url    = $baseurl . $parseurl;
        }
        printf("URL: %s<br>PARSE: %s<br>", $fetch_url, $parse_expr);
        break;
    }
    
    /**************************************************************************
     * try to call the parser on the html we resolved to get our url
     *************************************************************************/
    if ($status == STD_SUCCESS)
    {
        if ($remote_enabled)
        {
            $status = comic_parser($baseurl, $fetch_url, $parse_expr,
                                   &$comic_url);
        }
        else
        {
            $status = STD_NOREMOTE;
        }
    }
    
    $comic_day    = substr(date("D", $comic_time),0,2);
    
    return $status;
}
    
function comic_resolver(&$myurl, &$comic_time)
{
    $status = STD_SUCCESS;
    
    /**************************************************************************
     * get all of our resolver fields
     *************************************************************************/
    if (preg_match_all("/{[A-Za-z0-9\-]*}/", $myurl, $strings))
    {
        /**********************************************************************
         * replace matches
         *********************************************************************/
        for ($loop = 0; $loop < sizeof($strings[0]); $loop++)
        {
            $repl_str = "";
            
            switch($strings[0][$loop])
            {
              /****************************************************************
               * date components of the url
               ***************************************************************/
              case "{y}":
                $repl_str  = date("y", $comic_time);
                break;
              case "{Ml}":
                $repl_str = date("M", $comic_time);
                $repl_str = strtolower($repl_str);
                break;
              case "{Y}":
                $repl_str = date("Y", $comic_time);
                break;
              case "{ym}":
                $repl_str = date("ym", $comic_time);
                break;
              case "{ymd}":
                $repl_str = date("ymd", $comic_time);
                break;
              case "{Ymd}":
                $repl_str = date("Ymd", $comic_time);
                break;
                
              /****************************************************************
               * base urls should have the age, not parse urls
               * and it should be the first parsing element
               ***************************************************************/
              case "{-1d}":
                $comic_time -= (1*3600*24);
                break;
              case "{-7d}":
                $comic_time -= (7*3600*24);
                break;
              case "{-10d}":
                $comic_time -= (10*3600*24);
                break;
              case "{-14d}":
                $comic_time -= (14*3600*24);
                break;
                
              default:
                $status = STD_ERROR;
                break;
            }
            if ($status != STD_ERROR)
            {
                $myurl =
                    str_replace($strings[0][$loop], $repl_str, $myurl);
            }
            else
            {
                break;
            }
        }
    }
    return $status;
}

function comic_parser($baseurl, $fetch_url, $parse_expr, &$comic_url)
{
    global $phpgw;

    $status = STD_SUCCESS;
    
    /**************************************************************************
     * get the file to parse
     *************************************************************************/
    if ($file  = $phpgw->network->gethttpsocketfile($fetch_url))
    {
        $lines = count($file);

        /**********************************************************************
         * if succeed grok to find file or error
         *********************************************************************/
        for($index=0;($index < $lines && (!$status));$index++)
        {
            if (eregi("forbidden", $file[$index]))
            {
                $status = STD_FORBIDDEN;
                break;
            }

            if (ereg($parse_expr, $file[$index], $elements))
            {
                $comic_url = $baseurl . $elements[0];
                break;
            }
        }
    }
    else
    {
        $status = STD_ERROR;
    }
    
    return $status;
}

function comic_snarf(&$comic_url)
{
    global $phpgw, $phpgw_info;
    
    $status = STD_SUCCESS;

    $filename = "images/" . $phpgw->db->f("data_name") . substr($comic_url,-4);
    
    /**************************************************************************
     * get our image or fail
     *************************************************************************/
    // if($file  = $phpgw->network->gethttpsocketfile($comic_url))

    if($fpread = @fopen($comic_url, 'r'))
    {
        // $lines = count($file);
        
        /**********************************************************************
         * if succeed grok it for errors
         *********************************************************************/
        // for($index=0;($index < 10 && (!$status));$index++)
        // {
            // if (eregi("forbidden", $file[$index]))
            // {
                // $status = STD_FORBIDDEN;
            // }
        // }
        
        // if (!$status)
        {
            $file = fread($fpread, 60000);
            
            /******************************************************************
             * if succeed, put it in our local file
             *****************************************************************/
	    if ($fp = fopen($filename,"w"))
            {
                fwrite($fp, $file);
                
                fclose($fp);
            
                $comic_url = $phpgw_info["server"]["webserver_url"]
                    ."/".$phpgw_info["flags"]["currentapp"]
                    ."/"
                    .$filename;
            }
            else
            {
                $status = STD_ERROR;
            }
            fclose($fpread);
        }
    }
    else
    {
        $status == STD_ERROR;
    }
    
    return $status;
}

function comic_error($image_location, $status, &$comic_url)
{
    global $phpgw_info;

    /**************************************************************************
     * the image should either be a side or center
     *************************************************************************/
    switch ($image_location)
    {
      case 'S':
        $image_size = "_sm";
        break;
      default:
        $image_size = "";
        break;
    }

    /**************************************************************************
     * our image will be dressed with some pertinent error message
     *************************************************************************/
    switch ($status)
    {
      case STD_CENSOR:
        $label = "_censor";
        break;
      case STD_FORBIDDEN:
        $label = "_forbid";
        break;
      default:
        $label = "";
        break;
    }
    
    /**************************************************************************
     * compose the error comic url
     *************************************************************************/
    $comic_url = $phpgw_info["server"]["webserver_url"]
        ."/".$phpgw_info["flags"]["currentapp"]
        ."/images/template"
        .$label
        .$image_size
        .".png";
}

function comic_match_bar($start, $end, $indexlimit,
                         $comics_displayed, &$matchs_c)
{
    global $phpgw, $phpgw_info;

    switch ($indexlimit)
    {
      case 0:
      case 1:
        {
            $showstring =
                lang("showing x", $comics_displayed);
        }
        break;

      default:
        {
            $showstring =
                lang("showing %1 (%2 - %3 of %4)",
                     $comics_displayed,
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

function comic_display($comic_list, $comic_scale, $comic_perpage,
                       $user_censorlvl, $start, &$comic_left_c,
                       &$comic_right_c, &$comic_center_c, &$matchs_c)
{
    global $phpgw, $phpgw_info;

    /**************************************************************************
     * how many potential comics
     *************************************************************************/
    $indexlimit = count($comic_list);
    
    /**************************************************************************
     * number of comics displayed
     *************************************************************************/
    $comics_displayed = 0;
    
    /**************************************************************************
     * no reason to generate data if don't have any comics
     *************************************************************************/
    if ($indexlimit > 0)
    {

        /**********************************************************************
         * get the admin settings
         *********************************************************************/
        $phpgw->db->query("select * from phpgw_comic_admin");
        if (!$phpgw->db->num_rows())
        {
            comic_initialize_admin();

            $phpgw->db->query("select * from phpgw_comic_admin");
        }
        $phpgw->db->next_record();
        
        $image_src       = $phpgw->db->f("admin_imgsrc");
        $admin_censorlvl = $phpgw->db->f("admin_censorlvl");
        $censor_override = $phpgw->db->f("admin_coverride");
        $remote_enabled  = $phpgw->db->f("admin_rmtenabled");
        
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
         * where to start and end
         *********************************************************************/
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
         * step through the comics
         *********************************************************************/
        for ($index=$start; (($index < $end) && ($index < $indexlimit));
             $index++)
        {
            /******************************************************************
             * get the comic data
             *****************************************************************/
            $phpgw->db->query("select * from phpgw_comic_data "
                              ."WHERE data_id='"
                              .$comic_list[$index]."'");

            if (($phpgw->db->next_record()) && ($phpgw->db->f("data_enabled")))
            {

                $image_location  = $phpgw->db->f("data_location");
                $comic_censorlvl = $phpgw->db->f("data_censorlvl");
                $comic_url       = "";
                $comic_day       = substr(date("D", time()),0,2);   /* today */

                /**************************************************************
                 * if user meets censorship criteria
                 *************************************************************/
                if (($comic_censorlvl <= $user_censorlvl) &&
                    (($comic_censorlvl <= $admin_censorlvl) ||
                     ($censor_override == 1)))
                {
                    /**********************************************************
                     * resolve the url
                     *********************************************************/
                    $status = comic_resolve_url($remote_enabled,
                                                &$comic_url, &$comic_day);
                    
                    $link_url = $phpgw->db->f("data_linkurl");
                }
                else
                {
                    /**********************************************************
                     * otherwise have been censored
                     *********************************************************/
                    $status = STD_CENSOR;

                    /**********************************************************
                     * need to break the link url
                     *********************************************************/
                    $link_url = $phpgw->link($PHP_SELF);
                }

                /**************************************************************
                 * if comic_day is not in days allowed flag error
                 *************************************************************/
                if (!strstr($phpgw->db->f("data_pubdays"), $comic_day))
                {
                    $status = STD_WARNING;
                    $end++;
                }
                
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
                if (($status != STD_SUCCESS) &&
                    ($status != STD_WARNING) &&
                    ($status != STD_CURRENT))
                {
                    comic_error($image_location, $status, &$comic_url);
                    $status = STD_TEMPLATE;
                }

                /**************************************************************
                 * effectively have something to display
                 *************************************************************/
                if (($status == STD_SUCCESS) ||
                    ($status == STD_CURRENT) ||
                    ($status == STD_TEMPLATE))
                {
                    /**********************************************************
                     * image scaling
                     *********************************************************/
                    switch ($comic_scale)
                    {
                      case 1:
                        switch ($image_location)
                        {
                          case 'S':
                            $image_width   = "280";
                            break;
                          default:
                            $image_width   = "580";
                            break;
                        }
                        break;
                      default:
                        $image_width       = "100%";
                        break;
                    }

                    /**********************************************************
                     * find which template set (left/center/right) gets comic
                     *********************************************************/
                    switch($image_location)
                    {
                      case 'S':
                        switch ($side)
                        {
                          case "left":
                            $side = "right";
                            break;
                          default:
                            $side = "left";
                            break;
                        }
                        break;
                      default:
                        $side = "center";
                        break;
                    }

                    $name = lang("%1 by %2",
                                 $phpgw->db->f("data_title"),
                                 $phpgw->db->f("data_author"));
                    $comment =  lang("Visit %1", $link_url);
                    
                    $comic_tpl->
                        set_var
                        (array
                         (image_url   => $comic_url,
                          image_width => $image_width,
                          link_url    => $link_url,
                          comment     => $comment,
                          name        => $name,
                          th_bg       => $phpgw_info["theme"]["th_bg"],
                          th_text     => $phpgw_info["theme"]["th_text"]));
                    $comic_tpl->parse($side."_part", "row", TRUE);

                    $comics_displayed++;

                    /**********************************************************
                     * put the url and date in the database
                     *********************************************************/
                    if ($status == STD_SUCCESS)
                    {
                        $phpgw->db->lock("phpgw_comic_data");
                        $phpgw->db->query("update phpgw_comic_data set "
                          ."data_date='".(int)date("Ymd")."', "
                          ."data_imageurl='".$comic_url."' "
                          ."WHERE data_id='".$comic_list[$index]."'");
                        $phpgw->db->unlock();
                    }
                }
            }
            else
            {
                /**************************************************************
                 * was unable to fetch a comic
                 *************************************************************/
                $end++;
            }
        }
        
        /**********************************************************************
         * get the template body
         *********************************************************************/
        $comic_tpl->parse(TABLELEFT,   "tableleft");
        $comic_tpl->parse(TABLERIGHT,  "tableright");
        $comic_tpl->parse(TABLECENTER, "tablecenter");
        $comic_left_c   = $comic_tpl->get("TABLELEFT");
        $comic_right_c  = $comic_tpl->get("TABLERIGHT");
        $comic_center_c = $comic_tpl->get("TABLECENTER");
        
        if ($end > $indexlimit)
        {
            $end = $indexlimit;
        }
    
        /**********************************************************************
         * finish out the template with the next matchs bar
         *********************************************************************/
        $temp = $phpgw_info["user"]["preferences"]["common"]["maxmatchs"];
        $phpgw_info["user"]["preferences"]["common"]["maxmatchs"]
            = $comic_perpage;
        comic_match_bar($start, $end, $indexlimit,
                        $comics_displayed,
                        &$matchs_c);
        $phpgw_info["user"]["preferences"]["common"]["maxmatchs"]
            = $temp;
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
