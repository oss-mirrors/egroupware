<?php
  /**************************************************************************\
  * phpGroupWare API - Applications manager functions                        *
  * This file written by Mark Peters <skeeter@phpgroupware.org>              *
  * Copyright (C) 2001 Mark Peters                                           *
  * -------------------------------------------------------------------------*
  * This library is part of the phpGroupWare API                             *
  * http://www.phpgroupware.org/api                                          * 
  * ------------------------------------------------------------------------ *
  * This library is free software; you can redistribute it and/or modify it  *
  * under the terms of the GNU Lesser General Public License as published by *
  * the Free Software Foundation; either version 2.1 of the License,         *
  * or any later version.                                                    *
  * This library is distributed in the hope that it will be useful, but      *
  * WITHOUT ANY WARRANTY; without even the implied warranty of               *
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     *
  * See the GNU Lesser General Public License for more details.              *
  * You should have received a copy of the GNU Lesser General Public License *
  * along with this library; if not, write to the Free Software Foundation,  *
  * Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA            *
  \**************************************************************************/

  /* $Id$ */
  
  class applications
  {
    var $account_id;
    var $data = Array();
    var $db;

    /**************************************************************************\
    * Standard constructor for setting $this->account_id                       *
    \**************************************************************************/
    function applications($account_id = "")
    {
      global $phpgw, $phpgw_info;
      $this->db = $phpgw->db;
      if ($account_id == ""){ $account_id = $phpgw_info["user"]["account_id"]; }
      elseif (gettype($account_id) == "string") { $account_id = $phpgw->accounts->name2id($account_id); }
      $this->account_id = $account_id;
    }

    /**************************************************************************\
    * These are the standard $this->account_id specific functions              *
    \**************************************************************************/

    function read_repository()
    {
      global $phpgw, $phpgw_info;
      if (gettype($phpgw_info["apps"]) != "array") {
        $this->read_installed_apps();
      }
      $this->data = Array();
      reset($phpgw_info["apps"]);
      while ($app = each($phpgw_info["apps"])) {
        $check = $phpgw->acl->check("run",1,$app[0]);
        if ($check) {
          $this->data[$app[0]] = array("title" => $phpgw_info["apps"][$app[0]]["title"], "name" => $app[0], "enabled" => True, "status" => $phpgw_info["apps"][$app[0]]["status"]);
        } 
      }
      reset($this->data);
      return $this->data;
    }

    function read() {
      if (count($this->data) == 0){ $this->read_repository(); }
      reset($this->data);
      return $this->data;
    }

    function add($apps) {
      global $phpgw_info;
      if(gettype($apps) == "array") {
        while($app = each($apps)) {
          $this->data[$app[1]] = array("title" => $phpgw_info["apps"][$app[1]]["title"], "name" => $app[1], "enabled" => True, "status" => $phpgw_info["apps"][$app[1]]["status"]);
        }
      } elseif(gettype($apps) == "string") {
          $this->data[$apps] = array("title" => $phpgw_info["apps"][$apps]["title"], "name" => $apps, "enabled" => True, "status" => $phpgw_info["apps"][$apps]["status"]);
      }
      reset($this->data);
      return $this->data;
    }

    function delete($appname) {
      if($this->data[$appname]) {
        unset($this->data[$appname]);
      }
      reset($this->data);
      return $this->data;
    }

    function update_data($data) {
      reset($data);
      $this->data = Array();
      $this->data = $data;
      reset($this->data);
      return $this->data;
    }
   
    function save_repository(){
      global $phpgw;
      $num_rows = $phpgw->acl->delete("%%", "run", $this->account_id);
      reset($this->data);
      while($app = each($this->data)) {
        if(!$this->is_system_enabled($app[0])) { continue; }
        $phpgw->acl->add($app[0],'run',$this->account_id,1);
      }
      reset($this->data);
      return $this->data;
    }

    /**************************************************************************\
    * These are the non-standard $this->account_id specific functions          *
    \**************************************************************************/

    function app_perms()
    {
      global $phpgw, $phpgw_info;
      if (count($this->data) == 0) {
        $this->read_repository();
      }
      @reset($this->data);
      while (list ($key) = each ($this->data)) {
          $app[] = $this->data[$key]["name"];
      }
      return $app;
    }

    function read_account_specific() {
      global $phpgw, $phpgw_info;
      if (gettype($phpgw_info["apps"]) != "array") {
        $this->read_installed_apps();
      }
      @reset($phpgw_info["apps"]);
      while ($app = each($phpgw_info["apps"])) {
        if ($phpgw->acl->check_specific("run",1,$app[0], $this->account_id) && $this->is_system_enabled($app[0])) {
          $this->data[$app[0]] = array("title" => $phpgw_info["apps"][$app[0]]["title"], "name" => $app[0], "enabled" => True, "status" => $phpgw_info["apps"][$app[0]]["status"]);
        } 
      }
      reset($this->data);
      return $this->data;
    }

    /**************************************************************************\
    * These are the generic functions. Not specific to $this->account_id       *
    \**************************************************************************/

    function read_installed_apps(){
      global $phpgw_info;
      $this->db->query("select * from applications where app_enabled != '0' order by app_order asc",__LINE__,__FILE__);
      if($this->db->num_rows()) {
        while ($this->db->next_record()) {
          $name = $this->db->f("app_name");
          $title  = $this->db->f("app_title");
          $status = $this->db->f("app_enabled");
          $phpgw_info["apps"]["$name"] = array("title" => $title, "name" => $name, "enabled" => True, "status" => $status);
        }
      }
    }

    function is_system_enabled($appname){
      global $phpgw_info;
      if(gettype($phpgw_info["apps"]) != "array") {
        $this->read_installed_apps();
      }
      if ($phpgw_info["apps"][$appname]["enabled"]) {
        return True;
      }else{
        return False;
      }
    }
  }
?>