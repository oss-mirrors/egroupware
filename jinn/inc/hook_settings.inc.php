<?php
   /**************************************************************************\
   * eGroupWare - Jinn Preferences                                            *
   * http://egroupware.org                                                    *
   * Written by Pim Snel <pim@egroupware.org>                                 *
   * --------------------------------------------                             *
   *  This program is free software; you can redistribute it and/or modify it *
   *  under the terms of the GNU General Public License as published by the   *
   *  Free Software Foundation; version 2 of the License.                     *
   \**************************************************************************/

   // In the future these settings go to the plugin file 

   /* $id$ */

   create_section('Image Plugin');

   $prev_img = Array(
	  'no' => lang('Never'),
	  'only_tn' => lang('Only if thumnails exits'),
	  'yes' => lang('Yes')
   );

   create_select_box('Preview thumbs or images in form','prev_img',$prev_img,"When you choose 'Never', only links to the images are displayed; when you choose 'Only if thumnails exists' previews are  shown if an thumbnail of the image exists; if you choose 'Yes' all images are shown");

   $max_prev=array(
	  "1"=>"1",
	  "2"=>"2",
	  "3"=>"3",
	  "4"=>"4",
	  "5"=>"5",
	  "10"=>"10",
	  "20"=>"20",
	  "30"=>"30",
	  "-1"=>lang("No max. number")
   );

   create_select_box('Max. number of previews in form','max_prev',$max_prev,'When a lot of images are attached to a record, the form can load very slow. You can set a maximum number of images that is show in the form.');

   create_section('WYSIWYG plugin');

   $yes_no = Array(
	  'no' => lang('No'),
	  'yes' => lang('Yes')
   );

   create_select_box('Disable the WYSIWYG/HTMLArea Plugin','disable_htmlarea',$yes_no,"The WYSIWYG plugin makes you edit text like you do in a program like OpenOffice Writer or Word. Some people don't like this feature though, so you can force JiNN not to use it.");

   create_input_box('Number of records per page','default_record_num',"How many records do you want to list per page?");

   create_section('List view');
   $default_col_num = Array(
	  "3"=>"3",
	  ""=>lang('4 (default)'),
	  "5"=>"5",
	  "6"=>"6",
	  "7"=>"7",
	  "8"=>"8",
	  "9"=>"9",
	  "10"=>"10",
	  "20"=>"20",
	  "-1"=>lang("Show all colums, always")
   );

     create_select_box('Default number of visable columns ','default_col_num',$default_col_num,"How many columns do you want to be visible by default in List View?");

   if ($GLOBALS['phpgw_info']['user']['apps']['admin'])
   {
	  create_section('JiNN Developer Settings');

	  create_select_box('Show extra table debugging information','table_debugging_info',$yes_no,"When this is enables information like field length and field type is shown when editing record");

	  create_select_box('Activate experimental features which are in development','experimental',$yes_no,'Only activate this if you know what your doing. You can destroy your data using experimental features.');

	  create_select_box('Show SQL-statements in msgbox','debug_sql',$yes_no,'This is for debugging purposes.');
	  create_select_box('Show site_arr in msgbox','debug_site_arr',$yes_no,'This is for debugging purposes.');  
	  create_select_box('Show object_arr in msgbox','debug_object_arr',$yes_no,'This is for debugging purposes.');
   }
