<?php
   /*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare
   Copyright (C)2002 - 2006 Pim Snel <pim@lingewoud.nl>

   eGroupWare - http://www.eGroupware.org

   This file is part of JiNN

   JiNN is free software; you can redistribute it and/or modify it under
   the terms of the GNU General Public License as published by the Free
   Software Foundation; Version 2 of the License.

   JiNN is distributed in the hope that it will be useful,but WITHOUT ANY
   WARRANTY; without even the implied warranty of MERCHANTABILITY or 
   FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
   for more details.

   You should have received a copy of the GNU General Public License 
   along with JiNN; if not, write to the Free Software Foundation, Inc.,
   59 Temple Place, Suite 330, Boston, MA 02111-1307  USA
   */

   include_once(PHPGW_INCLUDE_ROOT.'/jinn/inc/class.uijinn.inc.php');

   /**
    * listsites 
    * 
    * @uses uijinn
    * @package 
    * @version $Id$
    * @copyright Lingewoud B.V.
    * @author Pim Snel <pim-AT-lingewoud-DOT-nl> 
    * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
    */
	class ui_listsites extends uijinn
   {
	  var $public_functions = Array(
		 'browse_egw_jinn_sites' => True,
	  );

	  /**
	  * uiadmin 
	  * 
	  * @access public
	  * @return void
	  */
	  function ui_listsites()
	  {
		 $this->bo = CreateObject('jinn.boadmin');
		 parent::uijinn();

		 $this->app_title = lang('Administrator Mode');

		 $this->permissionCheck();
	  }

	  /**
	  * browse_egw_jinn_sites: list all sites 
	  *
	  * @todo rename to list sites
	  */
	  function browse_egw_jinn_sites()
	  {
		 if($_POST['submitted']=='true')
		 {
			if($_POST['action']=='delete_mult_sites')
			{
			   $status=$this->bo->del_mult_egw_jinn_sites();	
			}
		 }

		 $this->header(lang('List Sites'));

		 $this->msg_box();

		 $this->tplsav2->helplink=$GLOBALS['phpgw']->link('/manual/index.php');
		 $this->tplsav2->link_add_site=$GLOBALS['phpgw']->link("/index.php","menuaction=jinn.uiadmin.add_edit_site");
		 $this->tplsav2->link_import_site=$GLOBALS['phpgw']->link("/index.php","menuaction=jinn.ui_importsite.import_egw_jinn_site");

		 $this->tplsav2->icon_del=$GLOBALS['phpgw']->common->image('phpgwapi','delete');
		 $this->tplsav2->icon_edit=$GLOBALS['phpgw']->common->image('phpgwapi','edit');
		 $this->tplsav2->icon_export=$GLOBALS['phpgw']->common->image('phpgwapi','filesave');

		 $records=$this->bo->get_phpgw_records('egw_jinn_sites','','','','','name');
		 if (count($records)>0)
		 {
			foreach($records as $recordvalues)
			{
			   $objects=$this->bo->get_phpgw_records('egw_jinn_objects','parent_site_id',$recordvalues['site_id'],'','','name');
			   $recordvalues['num_objects']= @count($objects);

			   $recordvalues['link_edit'] = $GLOBALS['phpgw']->link("/index.php","menuaction=jinn.uiadmin.add_edit_site&site_id=".$recordvalues['site_id']."&where_value=".$recordvalues['site_id']."&where_key=site_id");
			   $recordvalues['link_del']=$GLOBALS['phpgw']->link("/index.php","menuaction=jinn.boadmin.del_egw_jinn_site&where_key=site_id&where_value=".$recordvalues['site_id']);
			   $recordvalues['link_export']=$GLOBALS['phpgw']->link("/index.php","menuaction=jinn.exportsite.save_site_to_file&where_key=site_id&where_value=".$recordvalues['site_id']);

			   $this->tplsav2->site_records[]=$recordvalues;
			}
		 }
		 $this->tplsav2->display('list_sites.tpl.php');
	  }
   }
?>
