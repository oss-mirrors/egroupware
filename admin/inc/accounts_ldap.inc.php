<?php
  /**************************************************************************\
  * phpGroupWare - administration                                            *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/
  
  /* $Id$ */
  
  // Sections of code where taking from slapda http://www.jeremias.net/projects/sldapa  by
  // Jason Jeremias <jason@jeremias.net>
  
  
  $ldap = ldap_connect($phpgw_info["server"]["ldap_host"]);

  if (! @ldap_bind($ldap, $phpgw_info["server"]["ldap_root_dn"], $phpgw_info["server"]["ldap_root_pw"])) {
     echo "<p><b>Error binding to LDAP server.  Check your config</b>";
     exit;
  }

  function getSearchLine($searchstring)
  {
     if (($searchstring=="*") || ($searchstring=="")) {
        $searchline = "cn=*";
     } else {
        $searchline = sprintf("cn=*%s*",$searchstring);
     }
     return $searchline;
  }
  
  // Not the best method, but it works for now.
  function account_total()
  {
    global $phpgw_info, $ldap;

    $filter = "(|(uid=*))";
    $sr = ldap_search($ldap,$phpgw_info["server"]["ldap_context"],$filter,array("uid"));
    $info = ldap_get_entries($ldap, $sr);

    $total = -1;
    for ($i=0;$i<count($info);$i++) {
       if (! $phpgw_info["server"]["global_denied_users"][$info[$i]["uid"][0]]) {
          $total++;
       }
    }

    if ($total == 1) {
       $total = 2;
    }

    return $total;
  }
  

  // i think we don't need this anymore, replaced by $phpgw->accounts->read_userData(); 
  function account_view($loginid)
  {
    global $phpgw_info, $ldap;

    $filter = "(|(uid=$loginid))";
    $sr = ldap_search($ldap,$phpgw_info["server"]["ldap_context"],$filter,array("sn","givenname","uid","uidnumber"));
    $aci = ldap_get_entries($ldap, $sr);
    
    $account_info["account_id"]        = $aci[0]["uid"][0];
    $account_info["account_lid"]       = $aci[0]["uidnumber"][0];
    $account_info["account_lastname"]  = $aci[0]["sn"][0];
    $account_info["account_firstname"] = $aci[0]["givenname"][0];

    return $account_info;
  }

  function account_read($method,$start = 0,$sort,$order)
  {
    global $phpgw_info, $ldap;
    
/*    echo "sort: $sort";
    if ($sort == "account_lastname") {
       $sort = 3;
    } else if ($sort == "account_firstname") {
       $sort = 2;
    } else {
       $sort = 1;
    }
    echo " - sort: $sort";
*/

    $filter = "(|(uid=*))";
    $sr = ldap_search($ldap,$phpgw_info["server"]["ldap_context"],$filter,array("sn","givenname","uid","uidnumber"));
    $info = ldap_get_entries($ldap, $sr);

    $nummsg = $start;
    
    if ($nummsg < $phpgw_info["user"]["preferences"]["common"]["maxmatchs"]) {
       $totaltodisplay = $nummsg;
    } else if (($nummsg - $start) > $phpgw_info["user"]["preferences"]["common"]["maxmatchs"]) {
       $totaltodisplay = $start + $phpgw_info["user"]["preferences"]["common"]["maxmatchs"];
    } else {
       $totaltodisplay = $nummsg;
    }
  
/*
    for ($i=$start, $j=0; $i<$totaltodisplay; $i++,$j++) {
       if (! $phpgw_info["server"]["global_denied_users"][$info[$j]["uid"][0]]) {
          $account_info[$j]["account_id"]        = rawurlencode($info[$j]["dn"]);
          $account_info[$j]["account_lid"]       = $info[$j]["uid"][0];
          $account_info[$j]["account_lastname"]  = $info[$j]["givenname"][0];
          $account_info[$j]["account_firstname"] = $info[$j]["sn"][0];
       }
    }
*/

    for ($i=0, $j=0; $i<count($info); $i++,$j++) {
       if (! $phpgw_info["server"]["global_denied_users"][$info[$i]["uid"][0]]) {
          $account_info[$i]["account_id"]        = rawurlencode($info[$i]["dn"]);
          $account_info[$i]["account_lid"]       = $info[$i]["uid"][0];
          $account_info[$i]["account_lastname"]  = $info[$i]["givenname"][0];
          $account_info[$i]["account_firstname"] = $info[$i]["sn"][0];
       }
    }

    
//    echo " - order: $order";
/*    if ($order == "ASC") {
       sort($account_info[$sort]);
    } else {
       rsort($account_info[$sort]);
    } */

    return $account_info;
  }
  
  function account_add($account_info)
  {
     global $phpgw_info, $phpgw, $ldap;
   
     $account_info["passwd"] = $phpgw->common->encrypt_password($account_info["passwd"]);

     // This method is only temp.  We need to figure out the best way to assign uidnumbers and
     // guidnumbers.
     
     $phpgw->db->query("select (max(account_id)+1) from accounts");
     $phpgw->db->next_record();
     
     $account_info["account_id"] = $phpgw->db->f(0);

     // Much of this is going to be guess work for now, until we get things planned out.
     $entry["uid"]              = $account_info["loginid"];
     $entry["uidNumber"]        = $account_info["account_id"];
     $entry["gidNumber"]                = $account_info["account_id"];
     $entry["userpassword"]      = $account_info["passwd"];
     $entry["loginShell"]          = "/bin/bash";
     $entry["homeDirectory"]    = "/home/" . $account_info["loginid"];
     $entry["cn"]                          = sprintf("%s %s", $account_info["firstname"], $account_info["lastname"]);
     $entry["sn"]                          = $account_info["lastname"];
     $entry["givenname"]                = $account_info["firstname"];
     //$entry["company"]                  = $company;
     //$entry["title"]             = $title;
     $entry["mail"]                      = $account_info["loginid"] . "@" . $phpgw_info["server"]["mail_suffix"];
     //$entry["telephonenumber"]  = $telephonenumber;
     //$entry["homephone"]              = $homephone;
     //$entry["pagerphone"]        = $pagerphone;
     //$entry["cellphone"]              = $cellphone;
     //$entry["streetaddress"]  = $streetaddress;
     //$entry["locality"]                = $locality;
     //$entry["st"]                       = $st;
     //$entry["postalcode"]        = $postalcode;
     //$entry["countryname"]     = $countryname;
     //$entry["homeurl"]                  = $homeurl;
     //$entry["description"]      = $description;
     $entry["objectclass"][0]   = "account";
     $entry["objectclass"][1]   = "posixAccount";
     $entry["objectclass"][2]   = "shadowAccount";
     $entry["objectclass"][3]   = "inetOrgperson";
     $entry["objectclass"][4]   = "person";
     $entry["objectclass"][5]   = "top";
     /* $dn=sprintf("cn=%s %s, %s", $givenname, $sn, $BASEDN);*/
     $dn=sprintf("uid=%s, %s", $account_info["loginid"], $phpgw_info["server"]["ldap_context"]);
      
     // add the entries
     if (ldap_add($ldap, $dn, $entry)) {
        $cd = 28;
     } else {
        $cd = 99;               // Come out with a code for this
     }
  
     @ldap_close($ldap);
     
     add_default_preferences($account_info["account_id"]);
     $phpgw->db->lock(array("accounts","preferences"));

     while ($permission = each($account_info["permissions"])) {
       if ($phpgw_info["apps"][$permission[0]]["enabled"]) {
          $phpgw->accounts->add_app($permission[0]);
       }
     }

     $sql = "insert into accounts (account_id,account_lid,account_pwd,account_firstname,"
          . "account_lastname,account_permissions,account_groups,account_status,"
          . "account_lastpwd_change) values ('" . $account_info["account_id"] . "','"
          . $account_info["loginid"] . "','x','". addslashes($account_info["firstname"]) . "','"
          . addslashes($account_info["lastname"]) . "','" . $phpgw->accounts->add_app("",True)
          . "','" . $account_info["groups"] . "','A',0)";

     $phpgw->db->query($sql);
     $phpgw->db->unlock();

     $sep = $phpgw->common->filesystem_separator();

     $basedir = $phpgw_info["server"]["files_dir"] . $sep . "users" . $sep;

     if (! @mkdir($basedir . $n_loginid, 0707)) {
        $cd = 36;
     } else {
        $cd = 28;
     }

     return $cd;
  }
    
  function account_edit($account_info)
  {
     global $phpgw, $phpgw_info, $ldap;

     
     // This is just until the API fully handles reading the LDAP account info.
     $lid = $account_info["loginid"];
     
     if ($account_info["old_loginid"] != $account_info["loginid"]) {
//        $account_info["loginid"] = $account_info["c_loginid"];

        $entry["uid"]            = $account_info["loginid"];
        $entry["homeDirectory"]  = "/home/" . $account_info["loginid"];
        $entry["mail"]		 = $account_info["loginid"] . "@" . $phpgw_info["server"]["mail_suffix"];

        $phpgw->db->query("update accounts set account_lid='" . $account_info["loginid"] . "' "
                        . "where account_lid='" . $account_info["old_loginid"] . "'");
     }
     
     if ($account_info["passwd"]) {
        $entry["userpassword"] = $phpgw->common->encrypt_password($n_passwd);

        // Update the sessions table. (The user might be logged in)
        $phpgw->db->query("update sessions set session_pwd='" . $phpgw->common->encrypt($n_passwd) . "' "
        		        . "where session_lid='$lid'");
     }
     
     while ($permission = each($account_info["permissions"])) {
        if ($phpgw_info["apps"][$permission[0]]["enabled"]) {
           $phpgw->accounts->add_app($permission[0]);
        }
     }

     if (! $account_info["account_status"]) {
        $account_info["account_status"] = "L";
     }

     $entry["cn"]	 	= sprintf("%s %s", $account_info["firstname"], $account_info["lastname"]);
     $entry["sn"]	 	= $account_info["lastname"];
     $entry["givenname"] 	= $account_info["firstname"];
     $entry["phpgw_status"] 	= $account_info["account_status"];
     
     $i=0;
     reset ($account_info["permissions"]);
     while (list($key,$value) = each($account_info["permissions"]))
     {
     	$entry["phpgw_account_perms"][$i] = $key;
     	$i++;
     }

     $dn = $account_info["account_id"];
     @ldap_modify($ldap, $dn, $entry);

     $cd = 27;
     if ($account_info["old_loginid"] != $account_info["loginid"]) {
        $sep = $phpgw->common->filesystem_separator();
	
        $basedir = $phpgw_info["server"]["files_dir"] . $sep . "users" . $sep;

        if (! @rename($basedir . $account_info["old_loginid"], $basedir . $account_info["loginid"])) {
           $cd = 35;
        }
     }     
     return $cd;     
  }
  
  function account_delete($account_id)
  {
    global $phpgw_info, $phpgw, $ldap;

    #ldap_delete($ldap,"ou=phpgwpreferences,".$account_id);
    
    //delete sub dn's
    $sri = ldap_search($ldap, $phpgw_info["server"]["ldap_context"], "objectclass=phpgw_*");
    $allValues = ldap_get_entries($ldap, $sri);
    
    for ($i=0; $i < $allValues["count"]; $i++)
    {
        #print "<br> delete".$allValues[$i]["dn"];
    	ldap_delete($ldap,$allValues[$i]["dn"]);
    	#print ldap_error($ldap);
    } 
    ldap_delete($ldap,$account_id);
  }

  function account_exsists($loginid)
  {
    global $phpgw_info, $ldap;

    $filter = "(|(uid=$loginid))";

    $sr = ldap_search($ldap,$phpgw_info["server"]["ldap_context"],$filter,array("uid"));
    $total = ldap_get_entries($ldap, $sr);
    
    // Odd, but it works
    if (count($total) == 2) {
       return True;
    } else {
       return False;
    }
  }
  
  function account_close()
  {
     @ldap_close($ldap);  
  }
