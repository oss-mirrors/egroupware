<?php
   /**************************************************************************\
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for phpGroupWare
   Copyright (C)2002, 2004 Pim Snel <pim@lingewoud.nl>

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
   \**************************************************************************/

   /*!
   @class plgins
   @abstract JiNN object events plugin class
   */
   class plugins_object_events
   {
	  var $local_bo;
	  var $test;
	  
	  /*!
	  @function plugins
	  @abstract standard contructure that includes all plugins
	  */
	  function plugins_object_events()
	  {
		 $this->include_plugins();
	  }


  	  function call_event_action($post, $config)
	  {
		return call_user_func('event_action_'.$config[name], $post, $config);
	  }
	  
	  

	  /**
	  @function include_plugins
	  @abstract include ALL plugins
	  */
	  function include_plugins()
	  {
		 global $local_bo;
		 $local_bo=$this;
		 if ($handle = opendir(PHPGW_SERVER_ROOT.'/jinn/plugins/object_events_plugins')) {

			/* This is the correct way to loop over the directory. */

			while (false !== ($file = readdir($handle))) 
			{ 
			   if (substr($file,0,7)=='plugin.')
			   {

				  include_once(PHPGW_SERVER_ROOT.'/jinn/plugins/object_events_plugins/'.$file);
			   }
			}
			closedir($handle); 
		 }
	  }

	  
	  /**
	  @function plugin_hooks
	  @abstract get plugins that hook with the given event type
	  @return array with plugins
	  @param string $eventtype
	  */
	  function plugin_hooks($eventtype)
	  {
		 if (count($this->object_events_plugins>0))
		 {	
			foreach($this->object_events_plugins as $plugin)
			{
			   foreach($plugin['event_hooks'] as $hook)
			   {
				  if ($hook==$eventtype) 
				  {
					 $plugin_hooks[]=array(
						'value'=>$plugin['name'],
						'name'=>$plugin['title']
					 );
				  }
			   }
			}
			return $plugin_hooks;
		 }
	  }


  }

?>
