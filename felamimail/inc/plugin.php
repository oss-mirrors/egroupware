<?php

/**
 ** plugin.php
 **
 ** This file provides the framework for a plugin architecture.
 **
 ** Plugins will eventually be a way to provide added functionality
 ** without having to patch the SquirrelMail source code. Have some
 ** patience, though, as the these funtions might change in the near
 ** future.
 **
 ** Documentation on how to write plugins might show up some time.
 **
 ** $Id$
 **/


   $plugin_php = true;

   // This function adds a plugin
   function use_plugin ($name) {
      $f_setup = PHPGW_APP_ROOT . '/plugins/'.$name.'/setup.php';
      if (file_exists($f_setup)) {
         include ($f_setup);
         $function = 'felamimail_plugin_init_'.$name;
         if (function_exists($function)) {
            $function();
	 }	
      }
   }

   // This function executes a hook
   function do_hook ($name) {
      global $felamimail_plugin_hooks;
      $Data = func_get_args();
      if (isset($felamimail_plugin_hooks[$name]) && 
          is_array($felamimail_plugin_hooks[$name])) {
         foreach ($felamimail_plugin_hooks[$name] as $id => $function) {
            // Add something to set correct gettext domain for plugin
            if (function_exists($function)) {
                $function($Data);
            }
         }
      }
      
      // Variable-length argument lists have a slight problem when
      // passing values by reference.  Pity.  This is a workaround.
      return $Data;
  }

   // On startup, register all plugins configured for use
   if (isset($plugins) && is_array($plugins))
      foreach ($plugins as $id => $name)
         use_plugin($name);

?>
