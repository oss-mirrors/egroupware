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

function comic_initialize_admin()
{
    global $phpgw;

    $phpgw->db->query("insert into phpgw_comic_admin values (0,0,0)");
}

function comic_resolve_url($coded_url, &$comic_url)
{
    $status = STD_SUCCESS;
    
    if (preg_match_all("/{[A-Za-z]*}/", $coded_url, $strings))
    {
        $comic_url = $coded_url;
        
        /**********************************************************************
         * replace matches
         *********************************************************************/
        for ($loop = 0; $loop <= sizeof($strings[0]); $loop++)
        {
            switch($strings[0][$loop])
            {
              case "{yM}":
                $CurrentMonth = date("M");
                $CurrentYear  = date("y");
                $CurrentMonth = strtolower($CurrentMonth);

                $comic_url =
                    str_replace($strings[0][$loop], $CurrentYear.$CurrentMonth,
                                $comic_url);
                break;
              case "{Y}":
                $CurrentYear = date("Y");
                $comic_url =
                    str_replace($strings[0][$loop], $CurrentYear,
                                $comic_url);
                break;
              case "{ym}":
                $CurrentYearMonth = date("ym");
                $comic_url = 
                    str_replace($strings[0][$loop], $CurrentYearMonth,
                                $comic_url);
                break;
              default:
                $status = STD_ERROR;
                break;
            }
        }

        /**********************************************************************
         * connect and parse
         *********************************************************************/
        $DateToday = date("Ymd");
        
    }
    return $status;
}

function comic_snarf()
{}

function comic_error()
{}

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

                /**************************************************************
                 * resolve the url
                 *************************************************************/
                $status = comic_resolve_url($phpgw->db->f("data_imageurl"),
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
                    comic_error($gdenabled, $image_type, $status, &$comic_url);
                }

                /**************************************************************
                 * image scaling might be a good option...should evaluate
                 * that as a user option...would be all or nothing...[neotexan]
                 *************************************************************/
                
                /**************************************************************
                 * find which template set (left/center/right) gets comic
                 *************************************************************/
                switch($phpgw->db->f("data_location"))
                {
                  case 'L':
                    $side = "left";
                    break;
                  case 'R':
                    $side = "right";
                    break;
                  case 'C':
                    $side = "center";
                    break;
                }

                $comic_tpl->
                    set_var
                    (array
                     (image_url => $comic_url,
                      link_url  => $phpgw->db->f("data_linkurl"),
                      comment   => lang($phpgw->db->f("data_comment")),
                      name      => $phpgw->db->f("data_name"),
                      th_bg     => $phpgw_info["theme"]["th_bg"],
                      th_text   => $phpgw_info["theme"]["th_text"]));
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


?>
