<?php
  /**************************************************************************\
  * phpGroupWare - Email's Sidebox-Menu for idots-template                   *
  * http://www.phpgroupware.org                                              *
  * Written by edave <bigmudcake@hotmail.com>                                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */
{

 /*
	This hookfile is for generating an app-specific side menu used in the idots 
	template set.

	$menu_title speaks for itself
	$file is the array with link to app functions

	display_sidebox can be called as much as you like
 */

// get current mail account used
$sidebox_mailacct = $GLOBALS['phpgw']->msg->get_acctnum();




function CreateSidebox_MenuLink($mailacct,$mailfolder='INBOX',$mailpage='email.uiindex.index')
{
    $return_link = $GLOBALS['phpgw']->link('/index.php',array(
                        'menuaction' => $mailpage,
                        'fldball[folder]' => $GLOBALS['phpgw']->msg->prep_folder_out($mailfolder),
                        'fldball[acctnum]' => $acctnum));
    return $return_link;
}



function CreateSidebox_EmailMenu($mailacct)
{
	// Check to see if mailserver supports folders.
	$has_folders = $GLOBALS['phpgw']->msg->get_mailsvr_supports_folders();

	// Create Links for all the menu items
	$compose_link = $GLOBALS['phpgw']->link('/index.php',array(
						'menuaction' => 'email.uicompose.compose',
						// this data tells us where to return to after sending a message
						'fldball[folder]' => $GLOBALS['phpgw']->msg->prep_folder_out(),
						'fldball[acctnum]' => $mailacct,
						'sort' => $GLOBALS['phpgw']->msg->get_arg_value('sort'),
						'order' => $GLOBALS['phpgw']->msg->get_arg_value('order'),
						'start' => $GLOBALS['phpgw']->msg->get_arg_value('start')));
	// going to the folder list page, we only need log into the INBOX folder
	$folders_link = CreateSidebox_MenuLink($mailacct,'INBOX','email.uifolder.folder');
	$search_link = CreateSidebox_MenuLink($mailacct,'','email.uisearch.form');
	$filters_link = CreateSidebox_MenuLink($mailacct,'','email.uifilters.filters_list');
	$accounts_link = $GLOBALS['phpgw']->link('/index.php','menuaction=email.uipreferences.ex_accounts_list');
	$email_prefs_link = $GLOBALS['phpgw']->link('/index.php',array(
						'menuaction' => 'email.uipreferences.preferences',
						'ex_acctnum' => $mailacct));					


    // Create Language specific titles for all the menu items
    $compose_title = lang('Compose');
    $folders_title = lang('Folders');
    $search_title = lang('Search');
    $filters_title = lang('Filters');
    $accounts_title = lang('Accounts');
    $email_prefs_title = lang('Settings');

    // Construct the Menu Contents in array $file
    $file = array();
	$file[$compose_title] = $compose_link;
	if($has_folders) { 
        $file[$folders_title] = $folders_link; 
	    $file[$search_title] = $search_link;
    }
	$file[$filters_title] = $filters_link;
	$file[$accounts_title] = $account_link;
    $file[$email_prefs_title] = $email_prefs_link;
	//	$file[] = '_NewLine_'; // give a newline
    return $file;
}
// ****** end of function CreateSidebox_EmailMenu



  	$menu_title = $GLOBALS['phpgw_info']['apps'][$appname]['title'] . ' '. lang('Menu');
	display_sidebox($appname,$menu_title,CreateSidebox_EmailMenu($sidebox_mailacct));





function CreateSidebox_FolderMenu($mailacct)
{
    $folder_list = $GLOBALS['phpgw']->msg->get_arg_value('folder_list', $mailacct);
    $delimiter = $GLOBALS['phpgw']->msg->get_arg_value('mailsvr_delimiter', $acctnum);

    // note: what format should these folder name options (sent and trash) be held in
    // i.e. long or short name form, in the prefs database
	$send_folder_name = $GLOBALS['phpgw']->msg->get_pref_value('sent_folder_name', $mailacct);

    // get trash folder info or leave blank if no trash folder or trash folder pref is off
    $trash_folder_long = $GLOBALS['phpgw']->msg->get_arg_value('verified_trash_folder_long', $mailacct);

    // get sent folder info or leave blank if no sent folder or sent folder pref is off
    if ($GLOBALS['phpgw']->msg->get_isset_pref('use_sent_folder', $mailacct) == False) {
        $send_folder_long = '';
    } else {
        $send_folder_long = '';
	    for ($i=0; (($i<count($folder_list))&&($send_folder_long == ''));$i++) {
                if( ($folder_list[$i]['acctnum'] == $mailacct) &&
                    (($folder_list[$i]['folder_short'] == $send_folder_name) || 
                     ($folder_list[$i]['folder_long'] == $send_folder_name)) ) {
                        $send_folder_long = $folder_list[$i]['folder_long'];
                }
        }  
    }

    // Create Language specific titles for all the menu items
    $inbox_title = $GLOBALS['phpgw']->msg->get_common_langs('lang_inbox');
    $trash_title = $GLOBALS['phpgw']->msg->get_pref_value('trash_folder_name', $mailacct);
    $send_title = $GLOBALS['phpgw']->msg->get_pref_value('sent_folder_name', $mailacct);

    // Add new mail indicator to INBOX title
	$tmp_fldball = array();
	$tmp_fldball['folder'] = 'INBOX';
	$tmp_fldball['acctnum'] = $mailacct;
	$folder_status = $GLOBALS['phpgw']->msg->get_folder_status_info($tmp_fldball);
	$folder_newcount = number_format($folder_status['number_new']);
	if($folder_newcount > 0) {
        $inbox_title .= ' ('.$folder_newcount.')';
    }


    // Construct the Menu Contents in array $file
    $file = array();
    $file[$inbox_title] = CreateSidebox_MenuLink($acctnum);
    if($trash_folder_long != '') {
        $file[$trash_title] = CreateSidebox_MenuLink($acctnum,$trash_folder_long);
    }
    if($send_folder_long != '') {
        $file[$send_title] = CreateSidebox_MenuLink($acctnum,$send_folder_long);
    }

    // Generate Full folder list as menu items in sidebar.
    $subfolder_name = '';
    for ($i=0; $i<count($folder_list);$i++) {
        $folder_suffix = '';
        $folder_page = 'email.uiindex.index';
        if($folder_list[$i]['acctnum'] == $mailacct) {
            if( (($trash_folder_long != '') && ($folder_list[$i]['folder_long'] == $trash_folder_long)) ||
                (($send_folder_long != '') && ($folder_list[$i]['folder_long'] == $send_folder_long)) ||
                 ($folder_list[$i]['folder_long'] == 'INBOX') ) {
                      // dont add the special reserved folders as they were already added first;
            } else {
                $folder_title = $folder_list[$i]['folder_short'];
                $subfolder_pos = strpos($folder_title,$delimiter);
                if($subfolder_pos === false) {
                    // normal folder, doesn't contain subfolders.
                    $folder_suffix = '';
                    $subfolder_name = '';
                } else {
                    $folder_title = substr($folder_title,0,$subfolder_pos);
                    if($folder_title == $subfolder_name) {
                        // skip subfolder if already included once.
                        $folder_title = '';
                    } else {
                        $folder_suffix = ' [f]';
                        $subfolder_name = $folder_title;
                        $folder_page = 'email.uifolder.folder';
                    }
                }
                // skip folders with no titles
                if($folder_title != '') {                 
                    $folder_title = substr($folder_title,0,16).$folder_suffix;
                    $folder_long = $folder_list[$i]['folder_long'];
                    $file[$folder_title] = CreateSidebox_MenuLink($acctnum,$folder_long,$folder_page);
                }
            }
        }
   }
   return $file;
}
// ****** end of function CreateSidebox_FolderMenu



  	$menu_title = $GLOBALS['phpgw_info']['apps'][$appname]['title'] . ' '. lang('Folders');
	display_sidebox($appname,$menu_title,CreateSidebox_FolderMenu($sidebox_mailacct));





}
?>
