<?php
	/**************************************************************************\
	* phpGroupWare - Bookmarks                                                 *
	* http://www.phpgroupware.org                                              *
	* Based on Bookmarker Copyright (C) 1998  Padraic Renaghan                 *
	*                     http://www.renaghan.com/bookmarker                   *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	class bktemplate extends Template
	{
		var $classname = "bktemplate";
  
		/* if set, echo assignments */
		/* 1 = debug set, 2 = debug get, 4 = debug internals */
		var $debug     = false;
     
		/* "yes" => halt, "report" => report error, continue, 
		** "no" => ignore error quietly 
		*/
		var $halt_on_error  = "yes";

		// override the finish function to better handle with javascript.
		// we don't have whitespace in our var names, so no need to be
		// so all encompassing with the remove.

     function finish($str)
     {
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

	// This will drop all form values into appsessions for later use
	function grab_form_values($returnto, $need_done_button = False)
	{
		global $phpgw, $bookmark;

		$location_info = array(
			'returnto'             => $returnto,
			'need_done_button'     => $need_done_button,
			'bookmark_url'         => $bookmark['url'],
			'bookmark_name'        => $bookmark['name'],
			'bookmark_desc'        => $bookmark['desc'],
			'bookmark_keywords'    => $bookmark['keywords'],
			'bookmark_category'    => $bookmark['category'],
			'bookmark_subcategory' => $bookmark['subcategory'],
			'bookmark_rating'      => $bookmark['rating']
		);
		$phpgw->bookmarks->save_session_data($location_info);
	}

	function date_information(&$tpl, $raw_string)
	{
		global $phpgw;

		$ts = explode(',',$raw_string);

		$tpl->set_var('added_value',$phpgw->common->show_date($ts[0]));
		$tpl->set_var('visited_value',($ts[1]?$phpgw->common->show_date($ts[1]):lang('Never')));
		$tpl->set_var('updated_value',($ts[2]?$phpgw->common->show_date($ts[2]):lang('Never')));
	}

  function  set_standard($title, &$p_tpl) 
  {
     global $bookmarker, $SERVER_NAME, $phpgw;

     $p_tpl->set_var(array(
       TITLE            => $title,
       START_URL        => $phpgw->link("index.php"),
       TREE_URL         => $phpgw->link("tree.php"),
//       LIST_URL         => $phpgw->link("list.php"),
//       CREATE_URL       => $phpgw->link("create.php"),
       MAINTAIN_URL     => $phpgw->link("maintain.php"),
       MAILLINK_URL     => $phpgw->link("maillink.php"),
//       SEARCH_URL       => $phpgw->link("search.php"),
       FAQ_URL          => $phpgw->link("faq.php"),
//       CATEGORY_URL     => $phpgw->link("codes.php","codetable=bookmarks_category"),
//       SUBCATEGORY_URL  => $phpgw->link("codes.php","codetable=bookmarks_subcategory"),
       USER_URL         => $phpgw->link("useropt.php"),
       USER_SETTINGS_URL=> $phpgw->link("user.php"),
       IMPORT_URL       => $phpgw->link("import.php"),
       DOWNLOAD_URL     => $phpgw->link("download.php"),
       BUGS_URL         => $phpgw->link("bugs.php"),
       MAILLIST_URL     => $phpgw->link("maillist.php"),
       VERSION          => $bookmarker->version,
       IMAGE_URL_PREFIX => $bookmarker->image_url_prefix,
       IMAGE_EXT        => $bookmarker->image_ext,
       NAME_HTML        => $name_html,
       SERVER_NAME      => $SERVER_NAME
     ));
     $p_tpl->set_var('img_root',PHPGW_IMAGES);
     $p_tpl->set_var('search_link',$phpgw->link('/bookmarks/search.php'));
     $p_tpl->set_var('create_link',$phpgw->link('/bookmarks/create.php'));
     $p_tpl->set_var('list_link',$phpgw->link('/bookmarks/list.php'));
     $p_tpl->set_var('tree_link',$phpgw->link('/bookmarks/tree.php'));
  }

  // function to load a drop down list box from one
  // of the standard id-name formatted tables. this
  // routine will insert the <option> tags, it does
  // not insert the <select> tags.
  function load_ddlb($table, $selected = "")
  {
     global $phpgw, $phpgw_info;
     $db = $phpgw->db;

/*     $query = sprintf("select id, name from %s where username='%s' order by name", $table,
                      $phpgw_info["user"]["account_id"]);
     $db->query($query,__LINE__,__FILE__);
     while ($db->next_record()) {
        $s .= '<option value="' . $db->f("id") . '"';
        if ($selected == $db->f("id")) {
           $s .= " selected";
        }        
        $s .= '>' . $phpgw->strip_html($db->f("name")) . '</option>';
        $s .= "\n";
     } */
     return $s;
  }

	// function to determine what type of browser the user has.
	// code idea from http://www.php.net/
	function check_browser()
	{
		global $HTTP_USER_AGENT;
  
		$browser= 'UNKNOWN';
  
		if (ereg('MSIE',$HTTP_USER_AGENT))
		{
			$browser = 'MSIE';
		}
		elseif (ereg('Mozilla',$HTTP_USER_AGENT))
		{
			$browser = 'NETSCAPE';
		}
		else
		{
			$browser = 'UNKNOWN';
		}

		return $browser;
	}


	class bookmarks
	{
		var $db;
		var $grants;

		function bookmarks()
		{
			global $phpgw;

			$this->db     = $phpgw->db;
			$this->grants = $phpgw->acl->get_grants('bookmarks');
		}

		function check_perms($id, $required)
		{
			global $phpgw_info;

			$this->db->query("select bm_owner from phpgw_bookmarks where bm_id='$id'",__LINE__,__FILE__);
			$this->db->next_record();

			//echo "<br>id: $id required: $required grants: " . $this->grants[$this->db->f('bm_owner')] . " owner: " . $this->db->f('bm_owner') . " user: " . $phpgw_info['user']['account_id'];

			if (($this->grants[$this->db->f('bm_owner')] & $required) || ($this->db->f('bm_owner') == $phpgw_info['user']['account_id']))
			{
				return True;
			}
			else
			{
				return False;
			}
		}

		function categories_list($selected)
		{
			global $phpgw;

			$mains[] = array(
				'id'      => 0,
				'parent'  => 0,
				'name'    => '--'
			);

			$mains = $phpgw->categories->return_array('mains',0,True,'','cat_name','',True);

			$s = '<option value="0|0">-- :: --</option>';

			while (is_array($mains) && $main = each($mains))
			{
				$phpgw->db->query("select * from phpgw_categories where cat_parent='" . $main[1]['id'] . "' and (cat_appname='bookmarks' or cat_appname='phpgw') order by cat_name",__LINE__,__FILE__);
				while ($phpgw->db->next_record())
				{
					$id = $main[1]['id'] . '|' . $phpgw->db->f('cat_id');
					$s .= '<option value="' . $id . '"';
					if ($id == $selected)
					{
						$s .= ' selected';
					}
					$s .= '>' . $main[1]['name'] . ' :: ' . $phpgw->db->f('cat_name') . '</option>';
				}

				if ($main[1]['parent'] == 0 && $phpgw->db->num_rows() == 0)
				{
					$s .= '<option value="' . $main[1]['id'] . '|0"';
					if ($main[1]['id'] == $selected)
					{
						$s .= ' selected';
					}
					$s .= '>' . $main[1]['name'] . ' :: --</option>';
				}
			}
			return '<select name="bookmark[category]" size="5">' . $s . '</select>';
		}

		function add(&$id,$values)
		{
			global $phpgw_info, $error_msg, $msg, $phpgw;

			$db = $phpgw->db;

			if (! $this->validate($values))
			{
				return False;
			}

			// Does the bookmark already exist?
			$query = sprintf("select count(*) from phpgw_bookmarks where bm_url='%s' and bm_owner='%s'",$values['url'], $phpgw_info['user']['account_id']);
			$db->query($query,__LINE__,__FILE__);

			if ($db->f(0))
			{
				$error_msg .= sprintf('<br>URL <B>%s</B> already exists!', $values['url']);
				return False;
			}

			if (! $values['access'])
			{
				$values['access'] = 'public';
			}

			if (! $values['timestamps'])
			{
				$values['timestamps'] = time() . ',0,0';
			}

			list($category,$subcategory) = explode('|',$values['category']);

			$query = sprintf("insert into phpgw_bookmarks (bm_url, bm_name, bm_desc, bm_keywords, bm_category,"
                       . "bm_subcategory, bm_rating, bm_owner, bm_access, bm_info, bm_visits) "
                       . "values ('%s','%s','%s','%s',%s,%s,%s,'%s','%s','%s',0)", 
                          $values['url'], addslashes($values['name']), addslashes($values['desc']), addslashes($values['keywords']),
                          $category, '0', $values['rating'], $phpgw_info['user']['account_id'], $values['access'],
                          $values['timestamps']);
    
			$db->query($query,__LINE__,__FILE__);

			$msg .= 'Bookmark created sucessfully.';

			return true;
		}

		function update($id, $values)
		{
			global $error_msg, $msg, $validate, $phpgw_info, $phpgw;

/*       if (!$this->validate(&$url, &$name, &$ldesc, &$keywords, &$category, &$subcategory,
                        &$rating, &$public, &$public_db)) {
          return False;
       } */

			if (! $values['access'])
			{
				$values['access'] = 'public';
			}

			$phpgw->db->query("select bm_info from phpgw_bookmarks where bm_id='$id'",__LINE__,__FILE__);
			$phpgw->db->next_record();
			$ts = explode(',',$phpgw->db->f('bm_info'));
	
			$timestamps = sprintf('%s,%s,%s',$ts[0],$ts[1],time());

			list($category,$subcategory) = explode('|',$values['category']);

			// Update bookmark information.
			$query = sprintf("update phpgw_bookmarks set bm_url='%s', bm_name='%s', bm_desc='%s', "
	                      . "bm_keywords='%s', bm_category='%s', bm_subcategory='%s', bm_rating='%s',"
	                      . "bm_info='%s', bm_access='%s' where bm_id='%s'", 
	                         $values['url'], addslashes($values['name']), addslashes($values['desc']), addslashes($values['keywords']), 
	                         $category, '0', $values['rating'], $timestamps, $values['access'], $id);

			$phpgw->db->query($query,__LINE__,__FILE__);

			$msg .= lang('Bookmark changed sucessfully');
	
			return true;
		}

    function delete($id)
    {
       global $error_msg, $msg, $phpgw, $phpgw_info;
   
       $db = $phpgw->db;
       
       // Delete that bookmark.
       $query = sprintf("delete from phpgw_bookmarks where bm_id='%s' and bm_owner='%s'", $id, $phpgw_info["user"]["account_id"]);
       $db->query($query,__LINE__,__FILE__);
       if ($db->Errno != 0) {
          return False;
       }
       
       $msg .= "Bookmark deleted sucessfully.";
   
       // Update the PHPLIB user variable that keeps track of how
       // many bookmarks this user has.
       // NOTE: This needs to be moved into appsessions
       $this->update_user_total_bookmarks($phpgw_info["user"]["account_id"]);
   
       return true;
    }

		function validate ($values)
		{
			global $error_msg, $msg, $validate;

			// trim the form fields
			// $url = $validate->strip_space($url);
//			$name = trim($name);
//			$desc = trim($ldesc);
//			$keyw = trim($keywords);
       
			// Do we have all necessary data?
			if (! $values['url'] || $values['url'] == 'http://')
			{
				$error_msg .= '<br>URL is required.';
			}

			if (! $values['name'])
			{
				$error_msg .= '<br>Name is required.';
			}   

			// does the admin want us to check URL format
			if ($phpgw->bookmarks->url_format_check > 0)
			{
				// Is the URL format valid
				if ($values['url'] == 'http://')
				{
					$error_msg .= '<br>You must enter a URL';
				}
				else
				{
					if (! $validate->is_url($values['url']))
					{
						$format_msg = '<br>URL invalid. Format must be <strong>http://</strong> or 
	                            <strong>ftp://</strong> followed by a valid hostname and 
	                            URL!<br><small>' .  $validate->ERROR . '</small>';
	  
						// does the admin want this formatted as a warning or an error?
						if ($phpgw->bookmarks->url_format_check == 2)
						{
							$error_msg .= $format_msg;
						}
						else
						{
							$msg .= $format_msg;
						}
					}
				}
			}    

			if ($error_msg)
			{
				return False;
			}
			else
			{
				return True;
			}
		}

   function update_user_total_bookmarks($uname)
   {
      global $user_total_bookmarks, $phpgw, $phpgw_info;

      $db = $phpgw->db;

/*
      $db->query("select count(*) as total_bookmarks from bookmarks where username = '"
               . $phpgw_info["user"]["account_id"] . "' or bookmarks.public_f='Y'",__LINE__,__FILE__);
      $db->next_record();
      $phpgw->common->appsession($db->f("total_bookmarks"));
 
         // need to find out how many public bookmarks exist from
         // this user so other users can correctly calculate pages
         // on the list page.

         $total_public = 0;
         $query = sprintf("select count(id) as total_public from bookmarks where username = '%s' and public_f='Y'",$phpgw_info["user"]["account_id"]);
         $db->query($query,__LINE__,__FILE__);
         if ($db->Errno == 0) {
            if ($db->next_record()) {
//               $total_public = $db->f("total_public");
               echo "TEST: " . $db->f("total_public");
               $phpgw->common->appsession($db->f("total_public"));
            } else {
               echo "TEST: False";
               return False;
            } */
 
//            $phpgw->common->appsession($total_public);
/*
            $query = sprintf("update auth_user set total_public_bookmarks=%s where username = '%s'",$total_public, $uname);
            $db->query($query,__LINE__,__FILE__);
            if ($db->Errno != 0) {
               return False;
            }
            return true;*/
       //}
   }

	function get_totalbookmarks()
	{
		global $phpgw, $phpgw_info;

		$filtermethod = '( bm_owner=' . $phpgw_info['user']['account_id'];
		if (is_array($phpgw->bookmarks->grants))
		{
			$grants = $phpgw->bookmarks->grants;
			reset($grants);
			while (list($user) = each($grants))
			{
				$public_user_list[] = $user;
			}
			reset($public_user_list);
			$filtermethod .= " OR (bm_access='public' AND bm_owner in(" . implode(',',$public_user_list) . ')))';
		}
		else
		{
			$filtermethod .= ' )';
		}

		$phpgw->db->query("select count(*) from phpgw_bookmarks where $filtermethod",__LINE__,__FILE__);
		$phpgw->db->next_record();

		return $phpgw->db->f(0);
	} 

	function save_session_data($data)
	{
		global $phpgw;

		$phpgw->session->appsession('session_data','bookmarks',$data);
	}

	function read_session_data()
	{
		global $phpgw;

		return $phpgw->session->appsession('session_data','bookmarks');
	}

}



# the following class sets various configuration variables
# used throughout the application.
class bookmarker_class  {
  var $version        = "2.8.0";

# directory where templates are located on this server
  var $template_dir   = "./lib/templates";

# image URL - string added to the begining of an image file
# (for example, I set this to "./images/" which makes bookmarker
# build image URLs like <img src="./images/mailto.png"...)
  var $image_url_prefix;

# URL format checking. bookmarker can check the format of
# URLs entered on the create/maintain pages. This option
# lets you control this checking. Possible values:
#  0 = no checking of URL format
#  1 = URL format is checked, problems reported as warnings
#  2 = URL format is checked, problems reported as errors
  var $url_format_check = 2;

# URL response checking. bookmarker can check that the URL
# responds to a request and show a warning if it does not
# respond.
  var $url_responds_check = False;

# how many characters after the scheme(http://) and hostname
# (www.mydomain.com) to match when checking for possible
# duplicates on the create page.
# Zero means to just match on scheme and hostname - this is
# what I prefer.
  var $possible_dup_chars = 0;

# level of access required for user to use the mail-this-link
# page. The default is to only allow registered users to send
# email using bookmarker - anything else is asking for abuse!
# if you allow guest, you may want to bcc yourself by using the
# site header variable below.
  var $mail_this_link_permission_required = "editor";

# this var controls if the bookmarker links (start, create, search...)
# are displayed in the tree view. NOTE: these links are only displayed
# if 'group by category/subcategory' is also selected.
  var $show_bk_in_tree = 1; # set to 0 for 'off' 1 for 'on'


  function bookmarker_class()
  {
    global $SERVER_NAME, $SERVER_ADMIN, $REMOTE_ADDR, $PHP_SELF, $phpgw_info;

    $this->image_url_prefix = $phpgw_info["server"]["app_images"] . "/";

    $where_am_i = sprintf("http://%s%s/", $SERVER_NAME, dirname($PHP_SELF));

# used for quik-mark bookmark
    $this->create_url   = $where_am_i . "create.php";

# used for mail-this-link bookmark
    $this->maillink_url = $where_am_i . "maillink.php";

# the following wording is automatically added to all outgoing
# mail-this-link email messages
    $this->site_footer  = sprintf("--\nThis message sent from the bookmarker bookmark manager\nat %s\nPlease contact the server administrator at\n%s to report abuse of this service.", $where_am_i, $SERVER_ADMIN);

# this var controls the headers that are added to the mail-this-link
# email message. You may choose to bcc: yourself, record the senders IP...
# the headers should be separated by a newline ("\n")
    $this->site_headers = sprintf("X-Sender: bookmarker at %s\nX-Sender-IP: $REMOTE_ADDR", $SERVER_NAME);
  }
     
}

# instantiate the bookmarker class so we can access
# the variables.
$bookmarker = new bookmarker_class ();

# if the user's browser is a 5.0 or later version, then
# use PNG images. otherwise use GIF images.
$bookmarker->image_ext="gif";

if (ereg( "MSIE ([0-9]+)",$HTTP_USER_AGENT,$version)) {
  $ver=(int)$version[1];
  if ($ver>=5) $bookmarker->image_ext="png";

} elseif (ereg( "Opera/([0-9]+)",$HTTP_USER_AGENT,$version)) {
    # $opera=true;

} elseif (ereg( "Mozilla/([0-9]+)",$HTTP_USER_AGENT,$version)) {
  $ver=(int)$version[1];
  if ($ver>=5) $bookmarker->image_ext="png";
}

$phpgw->template->set_unknowns("remove");

$validate = createobject('phpgwapi.validator');


?>
