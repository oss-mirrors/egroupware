<?php
   /*
   JiNN - Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare
   Authors: Pim Snel <pim@lingewoud.nl>, 
   Copyright (C)2002, 2003, 2004, 2005 Pim Snel <pim@lingewoud.nl>

   eGroupWare - http://www.egroupware.org

   This file is part of JiNN

   JiNN is free software; you can redistribute it and/or modify it under
   the terms of the GNU General Public License as published by the Free
   Software Foundation; either version 2 of the License, or (at your 
   option) any later version.

   JiNN is distributed in the hope that it will be useful,but WITHOUT ANY
   WARRANTY; without even the implied warranty of MERCHANTABILITY or 
   FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
   for more details.

   You should have received a copy of the GNU General Public License 
   along with JiNN; if not, write to the Free Software Foundation, Inc.,
   59 Temple Place, Suite 330, Boston, MA 02111-1307  USA
   */

   include_once(PHPGW_INCLUDE_ROOT.'/jinn/inc/class.bojinn.inc.php');

   /**
   * boacl 
   * 
   * @uses bojinn
   * @package 
   * @version $Id$
   * @copyright Pim Snel - Lingewoud B.V.
   * @author Pim Snel <pim-AT-lingewoud-DOT-nl> 
   * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
   */
   class boacl extends bojinn
   {

	  var $public_functions = Array(
		 'save_access_rights_object'=> True,
		 'save_access_rights_site'=> True
	  );

	  /**
	  * boacl: constructor
	  * 
	  * @access public
	  * @return void
	  */
	  function boacl()
	  {
		 parent::bojinn();

		 /* this is for the sidebox */
		 //	 global $local_bo;
		 $local_bo=$this;
	  }

	  /**
	  * save_access_rights_site 
	  * 
	  * @access public
	  * @return void
	  */
	  function save_access_rights_site()
	  {
		 reset ($_POST);
		 $site_id=$_POST['site_id'];

		 while (list ($key, $val) = each ($_POST)) 
		 {
			if (substr($key,0,6)=='editor')	$editors[]=$val;
		 }

		 if (is_array($editors)) $editors=array_unique($editors);

		 $status=$this->so->update_site_access_rights($editors,$site_id);

		 if($status[error])
		 {
			$this->addError(lang('Access rights for Site NOT succesfully saved'));
		 }
		 else
		 {
			$this->addInfo(lang('Access rights for Site succesfully saved'));
		 }

		 $this->addDebug(__LINE__,__FILE__,$status[sql]);

		 $this->exit_and_open_screen('jinn.uiacl.main_screen');
	  }


	  /**
	  * save_access_rights_object 
	  * 
	  * @access public
	  * @return void
	  */
	  function save_access_rights_object()
	  {
		 reset ($_POST);
		 $site_id=$_POST['site_id'];
		 $object_id=$_POST['object_id'];

		 while (list ($key, $val) = each ($_POST)) 
		 {
			if (substr($key,0,6)=='editor')	$editors[]=$val;
		 }

		 if (is_array($editors)) $editors=array_unique($editors);

		 $status=$this->so->update_object_access_rights($editors,$_POST['object_id']);

		 if ($status[error])
		 {
			$this->addError(lang('Access rights for site-object NOT succesfully saved'));
		 }
		 else
		 {
			$this->addInfo(lang('Access rights for site-object succesfully saved'));
		 }
		 $this->addDebug(__LINE__,__FILE__,$status[sql]);

		 $this->exit_and_open_screen('jinn.uiacl.main_screen');
	  }

	  /**
	  * get_sites_to_admin: get sites which the user can administrate
	  * 
	  * @param mixed $uid 
	  * @access public
	  * @return void
	  */
	  function get_sites_to_admin($uid)
	  {
		 $groups=$GLOBALS['phpgw']->accounts->membership();

		 if (is_array ($groups))
		 {
			foreach ( $groups as $groupfields )
			{
			   $group[]=$groupfields[account_id];
			}
		 }

		 $user_sites=$this->so->get_sites_to_admin($uid,$group);
		 return $user_sites;
	  }

	  /**
	  * user_is_site_admin: checks is user is admin a site 
	  * 
	  * @param mixed $site_id 
	  * @param mixed $uid if empty current user is used 
	  * @access public
	  * @return boolean true if user is admin else returns false
	  */
	  function user_is_site_admin($site_id,$uid=false)
	  {
		 if(!$uid)
		 {
			$uid=$GLOBALS['phpgw_info']['user']['account_id'];
		 }

		 $sites=$this->get_sites_to_admin($uid);

		 if(in_array($site_id,$sites))
		 {
			return true;
		 }
		 else
		 {
			return false;
		 }
	  }

	  /**
	  * has_object_access 
	  * 
	  * @param mixed $object_id 
	  * @param mixed $uid 
	  * @access public
	  * @return void
	  */
	  function has_object_access($object_id,$uid=false)
	  {
		 if($GLOBALS['egw_info']['flags']['currentapp'] != 'jinn')
		 {
			return true;
		 }
		 if(!$uid)
		 {
			$uid=$GLOBALS['phpgw_info']['user']['account_id'];
		 }

		 $objects=$this->get_all_objects_allowed($uid);

		 if(in_array($object_id,$objects))
		 {
			return true;
		 }
		 else
		 {
			return false;
		 }
	  }

	  /**
	  * get_all_objects_allowed: get objects to which user has access too
	  * 
	  * @param mixed $uid 
	  * @access public
	  * @return void
	  */
	  function get_all_objects_allowed($uid)
	  {
		 $groups=$GLOBALS['phpgw']->accounts->membership();

		 if (is_array ($groups))
		 {
			foreach ( $groups as $groupfields )
			{
			   $group[]=$groupfields[account_id];
			}
		 }

		 $objects=$this->so->get_all_objects($uid,$group);

		 $sites=$this->get_sites_to_admin($uid);

		 foreach($sites as $site_id)
		 {
			$objects_from_sites=$this->get_objects_allowed_in_site($site_id,$uid);
			$objects=array_merge($objects,$objects_from_sites);


		 }

		 return $objects;
	  }

	  /**
	  * get_objects_allowed_in_site: get objects to which user has access too 
	  * 
	  * @param mixed $site_id 
	  * @param mixed $uid 
	  * @access public
	  * @return void
	  */
	  function get_objects_allowed_in_site($site_id,$uid)
	  {
		 $groups=$GLOBALS['phpgw']->accounts->membership();

		 if (is_array($groups))
		 {
			foreach ( $groups as $groupfields )
			{
			   $group[]=$groupfields[account_id];
			}
		 }

		 $objects=$this->so->get_objects($site_id,$uid,$group);
		 return $objects;
	  }


   }
