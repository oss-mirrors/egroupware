<?php
  /**************************************************************************\
  * phpGroupWare - Administration                                            *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/
 
  /* $Id$ */

	class uimenuclass
	{
		function uimenuclass()
		{
			$this->t = CreateObject('phpgwapi.Template',$phpgw->common->get_tpl_dir('admin'));

			$this->t->set_file(array('menurow' => 'menurow.tpl'));
			$this->t->set_block('menurow','menu_links','menu_links');
			$this->t->set_block('menurow','link_row','link_row');

			$this->rowColor[0] = $phpgw_info["theme"]["row_on"];
			$this->rowColor[1] = $phpgw_info["theme"]["row_off"];
		}

		function section_item($pref_link='',$pref_text='', $bgcolor)
		{
			global $t;

			$this->t->set_var('row_link',$pref_link);
			$this->t->set_var('row_text',$pref_text);
			$this->t->set_var('tr_color',$bgcolor);
			$this->t->parse('all_rows','link_row',True);
		}

		// $file must be in the following format:
		// $file = array(
		//  'Login History' => array('/index.php','menuaction=admin.uiaccess_history.list')
		// );
		// This allows extra data to be sent along
		function display_section($_menuData)
		{
			global $account_id;

			$i=0;

			while(list($key,$value) = each($_menuData))
			{
				if (!empty($value['extradata']))
				{
					$link = $GLOBALS['phpgw']->link($value['url'],'account_id=' . $account_id . '&' . $value['extradata']);
				}
				else
				{
					$link = $GLOBALS['phpgw']->link($value['url'],'account_id=' . $account_id);
				}
				$this->section_item($link,lang($value['description']),$this->rowColor[$i%2]);
				$i++;
			}

			$this->t->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg']);

			$this->t->set_var('link_done',$GLOBALS['phpgw']->link('/index.php','menuaction=admin.uiaccounts.list_users'));
			$this->t->set_var('lang_done',lang('Back'));

			$this->t->set_var('row_on',$this->rowColor[0]);

			$this->t->parse('out','menu_links');
			
			return $this->t->get('out','menu_links');
		}

		// create the html code for the menu
		function createHTMLCode($_hookname)
		{
			switch ($_hookname)
			{
				case 'edit_user':
					$GLOBALS['menuData'][] = array(
						'description' => 'User Data',
						'url'         => '/index.php',
						'extradata'   => 'menuaction=admin.uiaccounts.edit_user'
					);
					break;
				case 'view_user':
					$GLOBALS['menuData'][] = array(
						'description' => 'User Data',
						'url'         => '/index.php',
						'extradata'   => 'menuaction=admin.uiaccounts.view_user'
					);
					break;
			}

			$GLOBALS['phpgw']->common->hook($_hookname);

			if (count($GLOBALS['menuData']) > 1) 
			{
				$result = $this->display_section($GLOBALS['menuData']);
				//clear $menuData
				$GLOBALS['menuData'] = '';
				return $result;
			}
			else
			{
				// clear $menuData
				$GLOBALS['menuData'] = '';
				return '';
			}
		}
	}
?>
