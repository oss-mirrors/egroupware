<?php
# ---------------------------------------------------------------
# bookmarker
# A WWW based bookmark management, retrieval, and search tool.
# Copyright (C) 1998  Padraic Renaghan
# Licensed under terms of GNU General Public License
# (see http://www.renaghan.com/bookmarker/source/LICENSE)
# ---------------------------------------------------------------
# $Id$
# ---------------------------------------------------------------
/*class bk_db extends DB_Sql {
  var $Host     = "localhost";
  var $Database = "bookmarks";
  var $User     = "test";
  var $Password = "test";

  var $Auto_Free     = 1;     ## Set to 1 for automatic mysql_free_result()
  var $Debug         = 0;     ## Set to 1 for debugging messages.
  var $Halt_On_Error = "report";  
                              ## "yes" (halt with message),
                              ## "no" (ignore errors quietly),
                              ## "report" (ignore errror, but spit a warning)

  ## override the haltmsg function of the database class to match our
  ## error handling standard. Here we store the error msg in the global
  ## erorr_msg variable for later use by the page.
  function haltmsg($msg) {
    global $error_msg;

    $error_msg .= sprintf(
      "<br><b>Database Error</b>
       <br>DB Err Number: %s 
       <br>DB Err Message: %s 
       <br>Application Message: %s", 
       $this->Errno, $this->Error, $msg);
  }

  # override construtor to instantiate our db callout class
  function bk_db($query = "") {
    global $bk_db_callout;

    if (!isset($bk_db_callout)) {
      if ($this->type == "mysql") {
        $db_callout = "bk_mysql.inc";
      } else if ($this->type == "odbc") {
        $db_callout = "bk_odbc.inc";
      } else {
        $db_callout = "bk_postgresql.inc";
      }
      include(DBDIR     . $db_callout);

      # instantiate the bookmarker database callout class
      # where we have db specific code
      $bk_db_callout = new bk_db_callout_class ();
    }

    if (!empty($query))
      $this->query($query);
  }   
} */

/*class bk_ct_sql extends CT_Sql {
  var $database_class = "bk_db";           ## which database to connect
  var $database_table = "active_sessions"; ## find session data in this tbl
}*/

/*class bk_sess extends Session {
  var $classname      = "bk_sess";
  var $cookiename     = "";                ## defaults to classname
  var $magic          = "8675309pad";      ## ID seed
  var $mode           = "cookie";          ## We propagate session IDs with cookies
  var $fallback_mode  = "get";
  var $lifetime       = 0;                 ## 0 = do session cookies, else minutes
  var $database_class = "bk_db";           ## Which database to connect...
  var $database_table = "active_sessions"; ## and find our session data in this table.

  var $gc_time  = 1440;                    ## Purge all session data older than 1440 minutes.
  var $gc_probability = 1;                 ## Garbage collect probability in percent


  var $allowcache     = "no";              ## if you want to allow caching
  var $allowcache_expire = 30;             ## cached pages expire in this many
                                           ## minutes

  var $that_class = "bk_ct_sql";           ## Name of data storage container
  var $auto_init = "auto_init.inc";        ## this file is included at session
                                           ## startup by PHPLIB.

}

class bk_sess_cache extends bk_sess {
#
# use this version of the bookmarker session object when
# we want to allow a page to be cached on the client
# side. for performance reasons in the bookmarker app,
# we want to cache at least the tree view page.
# when HTTP/1.1 is more prevalent, we will switch this
# to private caching.
#
  var $allowcache        = "public";
}

class bk_user extends User {
  var $classname  = "bk_user";
  var $magic      = "007padraic45";  ## ID seed
  var $that_class = "bk_ct_sql";     ## Name of data storage container
}

class bk_cr_auth extends Auth {
  var $classname      = "bk_cr_auth";
  var $lifetime       =  120;
  var $mode           = "log";         ## "log" for login only systems,
                                       ## "reg" for user self registration
  var $nobody         = true;          ## If true, a default auth is created...
  var $magic          = "773pad9235";  ## Challenge seed
  var $database_class = "bk_db";       ## Name of database class
  var $database_table = "auth_user";

  function is_nobody() {
    if ($this->auth["uid"] == "nobody") {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  function login() {
# if not already logged in, then call std login_if function
    if ($this->is_authenticated()
    &&  ! $this->is_nobody() ) {
      return TRUE;
    } else {
      return $this->login_if(TRUE);
    }
  }

  function set_perm_auth_cookie($perm_auth_cookie) {
    global $sess, $save_auth, $bookmarker;
    ## if the user checked the box on the login form,
    ## then set the permanent auth cookie. the perm
    ## auth cookie is good for 100 days.
    if ($save_auth == "on") {
      SetCookie("bookmarker_perm_auth", $perm_auth_cookie
       , (time()+$bookmarker->perm_cookie_lifetime)
       , "/", $sess->$cookie_domain);
    }
  }

  function auth_preauth() {
    global $bookmarker_perm_auth;
    ## did the user's browser send back the pauth,
    ## permanent auth, cookie. If so, see if it is
    ## still valid.
    if (!empty($bookmarker_perm_auth)) {    
      list($username, $cookie_val) = 
        explode (" ", base64_decode($bookmarker_perm_auth));
      $this->db->query(sprintf("select * from %s where username = '%s' and perm_auth_cookie = '%s'", $this->database_table, $username, $cookie_val));

      while($this->db->next_record()) {
        $uid = $this->db->f("user_id");
        $this->auth["uname"] = $this->db->f("username");
        $this->auth["perm"]  = $this->db->f("perms");
        $this->auth["name"]  = $this->db->f("name");
        $this->auth["email"] = $this->db->f("email");
        $this->auth["default_public"]  = $this->db->f("default_public");
        $this->auth["include_public"]  = $this->db->f("include_public");
        return $uid;
      }
    }
    return false;    
  }

  function auth_loginform() {
    global $sess, $challenge;

    $challenge = md5(uniqid($this->magic));
    $sess->register("challenge");
    include(LIBDIR . "crlogin.inc");
  }

  function auth_validatelogin() {
    global $sess, $username, $password, $challenge, $response;

    $this->auth["uname"] = $username;

    $this->db->query(sprintf("select * from %s where username = '%s'"
                            , $this->database_table, $username));

    while($this->db->next_record()) {
      $uid   = $this->db->f("user_id");
      $perm  = $this->db->f("perms");
      $pass  = $this->db->f("password");
      $name  = $this->db->f("name");
      $email = $this->db->f("email");
      $default_public    = $this->db->f("default_public");
      $include_public    = $this->db->f("include_public");
      $perm_auth_cookie  = 
        base64_encode(
          sprintf("%s %s", $username, $this->db->f("perm_auth_cookie")));
    }
    $expected_response = md5("$username:$pass:$challenge");

    ## True when JS is disabled
    if ($response == "") {
      if (md5($password) != $pass) {
        return false;
      } else {
        $this->auth["perm"] = $perm;
        $this->auth["name"] = $name;
        $this->auth["email"] = $email;
        $this->auth["default_public"] = $default_public;
        $this->auth["include_public"] = $include_public;
        $this->set_perm_auth_cookie($perm_auth_cookie);
        return $uid;
      }
    }

    ## Response is set, JS is enabled
    if ($expected_response != $response) {
      if (! empty($password)) {
        echo "<br><small><font color=red><strong>WARNING:</strong><br>Your browser has a buggy implementation of JavaScript that is<br> causing the plaintext password to be sent over the network!</font></small>";
      }

      return false;
    } else {
      $this->auth["perm"] = $perm;
      $this->auth["name"] = $name;
      $this->auth["email"] = $email;
      $this->auth["default_public"] = $default_public;
      $this->auth["include_public"] = $include_public;
      $this->set_perm_auth_cookie($perm_auth_cookie);

      ## reset the auto_init internal session variable
      ## to force the re-execution of the auto_init file.
      ## since this is not necessarily a new session, a new
      ## login does not necessarily mean a new session, we
      ## need to force this so that are session vars and
      ## esp user_total_bookmarks get recalced.
      $sess->in = '';

      return $uid;
    }
  }
} */

/*class bk_perm extends Perm {
  var $classname = "bk_perm";
  var $permissions = array(
                            "guest"     => 1,
                            "editor"    => 3,
                            "admin"     => 7
                          );

  function perm_invalid($does_have, $must_have) {
  }
}

class bk_Sql_Query extends Sql_Query {
  var $classname = "bk_Sql_Query";
  var $persistent_slots = array(
    "conditions", "input_size", "input_max", "method", "lang", "translate", "container", "variable", "query"
  );
  var $query = "1=0";       ## last WHERE clause used
  var $conditions = 1;      ## Allow for that many Query Conditions
  var $input_size = 35;     ## Used in text input field creation
  var $input_max  = 80;

  var $method     = "post"; ## Generate get or post form...
  var $lang       = "en";   ## HTML Widget language

  var $translate = "on";    ## If set, translate column names
  var $container = "";      ## If set, create a container table
  var $variable  = "on";    ## if set, create variable size buttons
} */

class bktemplate extends Template {
  var $classname = "bktemplate";
  
  /* if set, echo assignments */
  /* 1 = debug set, 2 = debug get, 4 = debug internals */
  var $debug     = false;
  
  /* "yes" => halt, "report" => report error, continue, 
   * "no" => ignore error quietly 
  */
  var $halt_on_error  = "yes";
# 
# override the finish function to better handle with javascript.
# we don't have whitespace in our var names, so no need to be
# so all encompassing with the remove.

  function finish($str) {
    switch ($this->unknowns) {
      case "keep":
      break;
      
      case "remove":
        $str = preg_replace("/\{[-_a-zA-Z0-9]+\}/", "", $str);
      break; 
      
      case "comment":
        $str = preg_replace("/\{([-_a-zA-Z0-9]+)\}/", "<!-- Template $handle: Variable \\1 undefined -->", $str);
      break; 
    } 
    return $str;
  } 
}
?>
