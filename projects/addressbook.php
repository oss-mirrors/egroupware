<?php
/**************************************************************************\
* phpGroupWare - Projects addressbook                                     *
* http://www.phpgroupware.org                                              *
* Written by Bettina Gille [ceb@phpgroupware.org]                          *
* -----------------------------------------------                          *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/
/* $Id$ */

    $phpgw_info["flags"] = array("noheader" => True,
				 "nonavbar" => True,
			       "currentapp" => "projects",
		  "enable_nextmatchs_class" => True);

    include("../header.inc.php");

    $t = CreateObject('phpgwapi.Template',$phpgw->common->get_tpl_dir("projects"));
    $t->set_file(array('addressbook_list_t' => 'addressbook.tpl',
		        'addressbook_list' => 'addressbook.tpl'));
    $t->set_block('addressbook_list_t','addressbook_list','list');
        
    $d = CreateObject("phpgwapi.contacts");

    $t->set_var('title',$phpgw_info["site_title"]);                                                                                                                                          
    $t->set_var('bg_color',$phpgw_info["theme"]["bg_color"]);                                                                                                                                
    $t->set_var('lang_addressbook_action',lang('Address book'));
    $charset = $phpgw->translation->translate("charset");                                                                                                                                  
    $t->set_var('charset',$charset);                                                                                                                                                       
    $t->set_var('font',$phpgw_info["theme"]["font"]);	
    $t->set_var('lang_search',lang('Search'));                                                                                                                                             
    $t->set_var('searchurl',$phpgw->link("/projects/addressbook.php"));

    if (! $start) { $start = 0; }                                                                                                                                                                                    
                                                                                                                                                                                         
    if ($filter == "none") { $filter = ""; }                                                                                                                                         
    if ($filter != "" ) { $filter = "access=$filter"; }

    if($phpgw_info["user"]["preferences"]["common"]["maxmatchs"] && $phpgw_info["user"]["preferences"]["common"]["maxmatchs"] > 0) {
    $offset = $phpgw_info["user"]["preferences"]["common"]["maxmatchs"];
    }
    else { $offset = 15; }

    $account_id = $phpgw_info['user']['account_id'];

    $cols = array('n_given' => 'n_given',
                'n_family' => 'n_family',
                'org_name' => 'org_name');

    $entries = $d->read($start,$offset,$cols,$query,$filter,$sort,$order,$account_id);

//--------------------------------- nextmatch --------------------------------------------    

    $left = $phpgw->nextmatchs->left('addressbook.php',$start,$d->total_records,"&order=$order&filter=$filter&sort=$sort&query=$query");
    $right = $phpgw->nextmatchs->right('addressbook.php',$start,$d->total_records,"&order=$order&filter=$filter&sort=$sort&query=$query");
    $t->set_var('left',$left);
    $t->set_var('right',$right);
                                                                                                                                                                                         
    if ($d->total_records > $phpgw_info["user"]["preferences"]["common"]["maxmatchs"]) {
    $lang_showing=lang('showing x - x of x',($start + 1),($start + $phpgw_info["user"]["preferences"]["common"]["maxmatchs"]),$d->total_records);
    }
    else { $lang_showing=lang('showing x',$d->total_records); }
    $t->set_var('lang_showing',$lang_showing);
                                                                                                                                                                                         
// ------------------------------ end nextmatch ------------------------------------------

// -------------- list header variable template-declaration ------------------------

    $t->set_var('th_bg',$phpgw_info["theme"]["th_bg"]);                                                                                                                                     
    $t->set_var('sort_company',$phpgw->nextmatchs->show_sort_order($sort,'org_name',$order,'addressbook.php',lang('Company')));                                                           
    $t->set_var('sort_firstname',$phpgw->nextmatchs->show_sort_order($sort,'n_given',$order,'addressbook.php',lang('Firstname')));                                                     
    $t->set_var('sort_lastname',$phpgw->nextmatchs->show_sort_order($sort,'n_family',$order,'addressbook.php',lang('Lastname')));                                                        
    $t->set_var('lang_select',lang('Select'));

// ------------------------- end header declaration --------------------------------

    for ($i=0;$i<count($entries);$i++) {
    $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);                                                                                                                      
    $t->set_var(tr_color,$tr_color);
    $firstname = $entries[$i]['n_given'];
    if (!$firstname) { $firstname = "&nbsp;"; }
    $lastname = $entries[$i]['n_family'];
    if (!$lastname) { $lastname = "&nbsp;"; }
    $company = $entries[$i]['org_name'];
    if (!$company) { $company = "&nbsp;"; }
    $id      = $entries[$i]['id'];

// ---------------- template declaration for list records -------------------------- 

    $t->set_var(array("company" => $company,                                                                                                                                               
                    "firstname" => $firstname,                                                                                                                                           
                     "lastname" => $lastname));                                                                                                                                           
                                                                                                                                                                                         
    $t->set_var('id',$id);                                                                                                                                                                 

    $t->parse('list','addressbook_list',True);
    }
 
    $t->set_var('lang_done',lang('Done'));                                                                                                                                                    
    $t->parse('out','addressbook_list_t',True);                                                                                                                                            
    $t->p('out');
    
    $phpgw->common->phpgw_exit();                                                                                                                                                           
?>

