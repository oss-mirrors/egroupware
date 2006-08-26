<?php
/**
 * API - Interapplicaton links BO layer
 *
 * Links have two ends each pointing to an entry, each entry is a double:
 * 	 - app   app-name or directory-name of an egw application, eg. 'infolog'
 * 	 - id    this is the id, eg. an integer or a tupple like '0:INBOX:1234'
 *
 * @link http://www.egroupware.org
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package api
 * @subpackage link
 * @version $Id$
 */

include_once(EGW_API_INC . '/class.solink.inc.php');
	
/**
 * generalized linking between entries of eGroupware apps - BO layer
 *
 * The BO-layer implementes some extra features on top of the so-layer:
 * 1) It handles links to not already existing entries. This is used by the eTemplate link-widget, which allows to
 *    setup links even for new / not already existing entries, before they get saved.
 * 	  In that case you have to set the first id to 0 for the link-function and pass the array returned in that id 
 * 	  (not the return-value) after saveing your new entry again to the link function.
 * 2) Attaching files: they are saved in the vfs and not the link-table (!).
 *    Attached files are stored under $vfs_basedir='/infolog' in the vfs!
 * 3) It manages the link-registry, in which apps can register themselfs by implementing some hooks
 * 4) It notifies apps, who registered for that service, about changes in the links their entries
 */
class bolink extends solink
{
	/**
	 * other apps can participate in the linking by implementing a 'search_link' hook, which
	 * has to return an array in the format of an app_register entry below
	 * @var array $app_register
	 */
	var $app_register = array(
		'projects' => array(
			'query' => 'projects_query',
			'title' => 'projects_title',
			'view' => array (
				'menuaction' => 'projects.uiprojects.view_project'
			),
			'view_id' => 'project_id'
		),
/*
		'email' => array(
			'view' => array(
				'menuaction' => 'email.uimessage.message'
			),
			'view_id' => 'msgball[acctnum:folder:msgnum]'	// id is a tupple/array, fields separated by ':'
		),
*/
	);
	var $public_functions = array(	// functions callable via menuaction
		'get_file' => True
	);
	var $vfs;
	var $vfs_basedir='/infolog';
	var $vfs_appname='file';		// pseudo-appname for own file-attachments in vfs, this is NOT the vfs-app
	var $valid_pathes = array();
	var $send_file_ips = array();

	/**
	 * constructor
	 */
	function bolink( )
	{
		$this->solink( );					// call constructor of derived class

		$this->vfs =& CreateObject('phpgwapi.vfs');

		$this->link_pathes   = $GLOBALS['egw_info']['server']['link_pathes'];
		$this->send_file_ips = $GLOBALS['egw_info']['server']['send_file_ips'];
		
		// other apps can participate in the linking by implementing a search_link hook, which
		// has to return an array in the format of an app_register entry
		// for performance reasons, we do it only once / cache it in the session
		if (!($search_link_hooks = $GLOBALS['egw']->session->appsession('search_link_hooks','phpgwapi')))
		{
			$search_link_hooks = $GLOBALS['egw']->hooks->process('search_link');
			$GLOBALS['egw']->session->appsession('search_link_hooks','phpgwapi',$search_link_hooks);
		}
		if (is_array($search_link_hooks))
		{
			foreach($search_link_hooks as $app => $data)
			{
				if (is_array($data))
				{
					$this->app_register[$app] = $data;
				}
			}
		}
	}
	
	/**
	 * creats a link between $app1,$id1 and $app2,$id2 - $id1 does NOT need to exist yet
	 *
	 * Does NOT check if link already exists.
	 * File-attachments return a negative link-id !!!
	 *
	 * @param string $app1 app of $id1
	 * @param string/array &$id1 id of item to linkto or 0 if item not yet created or array with links 
	 * 	of not created item or $file-array if $app1 == $this->vfs_appname (see below).
	 * 	If $id==0 it will be set on return to an array with the links for the new item.
	 * @param string/array $app2 app of 2.linkend or array with links ($id2 not used)
	 * @param string $id2='' id of 2. item of $file-array if $app2 == $this->vfs_appname (see below)<br>
	 * 	$file array with informations about the file in format of the etemplate file-type<br>
	 * 	$file['name'] name of the file (no directory)<br>
	 * 	$file['type'] mine-type of the file<br>
	 * 	$file['tmp_name'] name of the uploaded file (incl. directory)<br>
	 * 	$file['path'] path of the file on the client computer<br>
	 * 	$file['ip'] of the client (path and ip in $file are only needed if u want a symlink (if possible))
	 * @param string $remark='' Remark to be saved with the link (defaults to '')
	 * @param int $owner=0 Owner of the link (defaults to user)
	 * @param int $lastmod=0 timestamp of last modification (defaults to now=time())
	 * @param int $no_notify=0 &1 dont notify $app1, &2 dont notify $app2
	 * @return int/boolean False (for db or param-error) or on success link_id (Please not the return-value of $id1)
	 */
	function link( $app1,&$id1,$app2,$id2='',$remark='',$owner=0,$lastmod=0,$no_notify=0 )
	{
		if ($this->debug)
		{
			echo "<p>bolink.link('$app1',$id1,'".print_r($app2,true)."',".print_r($id2,true).",'$remark',$owner,$lastmod)</p>\n";
		}
		if (!$app1 || !$app2 || $app1 == $app2 && $id1 == $id2)
		{
			return False;
		}
		if (is_array($id1) || !$id1)		// create link only in $id1 array
		{
			if (!is_array($id1))
			{
				$id1 = array( );
			}
			$link_id = $this->temp_link_id($app2,$id2);

			$id1[$link_id] = array(
				'app' => $app2,
				'id'  => $id2,
				'remark' => $remark,
				'owner'  => $owner,
				'link_id' => $link_id,
				'lastmod' => time()
			);
			if ($this->debug)
			{
				_debug_array($id1);
			}
			return $link_id;
		}
		if (is_array($app2) && !$id2)
		{
			reset($app2);
			$link_id = True;
			while ($link_id && list(,$link) = each($app2))
			{
				if (!is_array($link))	// check for unlink-marker
				{
					//echo "<b>link='$link' is no array</b><br>\n";
					continue;
				}
				if ($link['app'] == $this->vfs_appname)
				{
					$link_id = $this->attach_file($app1,$id1,$link['id'],$link['remark']);
				}
				else
				{
					$link_id = solink::link($app1,$id1,$link['app'],$link['id'],
						$link['remark'],$link['owner'],$link['lastmod']);
					
					// notify both sides
					if (!($no_notify&2)) $this->notify('link',$link['app'],$link['id'],$app1,$id1,$link_id);
					if (!($no_notify&1)) $this->notify('link',$app1,$id1,$link['app'],$link['id'],$link_id);
				}
			}
			return $link_id;
		}
		if ($app1 == $this->vfs_appname)
		{
			return $this->attach_file($app2,$id2,$id1,$remark);
		}
		elseif ($app2 == $this->vfs_appname)
		{
			return $this->attach_file($app1,$id1,$id2,$remark);
		}
		$link_id = solink::link($app1,$id1,$app2,$id2,$remark,$owner);

		if (!($no_notify&2)) $this->notify('link',$app2,$id2,$app1,$id1,$link_id);
		if (!($no_notify&1)) $this->notify('link',$app1,$id1,$app2,$id2,$link_id);
		
		return $link_id;
	}

	/**
	 * generate temporary link_id used as array-key
	 *
	 * @param string $app app-name
	 * @param mixed $id
	 * @return string
	 */
	function temp_link_id($app,$id)
	{
		return $app.':'.($app != $this->vfs_appname ? $id : $id['name']);
	}

	/**
	 * returns array of links to $app,$id (reimplemented to deal with not yet created items)
	 *
	 * @param string $app appname
	 * @param string/array $id id of entry in $app or array of links if entry not yet created
	 * @param string $only_app if set return only links from $only_app (eg. only addressbook-entries) or NOT from if $only_app[0]=='!'
	 * @param string $order='link_lastmod DESC' defaults to newest links first
	 * @return array of links or empty array if no matching links found
	 */
	function get_links( $app,$id,$only_app='',$order='link_lastmod DESC' )
	{
		//echo "<p>bolink::get_links(app='$app',id='$id',only_app='$only_app',order='$order')</p>\n";

		if (is_array($id) || !$id)
		{
			$ids = array();
			if (is_array($id))
			{
				if ($not_only = $only_app[0])
				{
					$only_app = substr(1,$only_app);
				}
				end($id);
				while ($link = current($id))
				{
					if (!is_array($link) ||		// check for unlink-marker
					    $only_app && $not_only == ($link['app'] == $only_app))
					{
						continue;
					}
					$ids[$link['link_id']] = $link;
					prev($id);
				}
			}
			return $ids;
		}
		$ids = solink::get_links($app,$id,$only_app,$order);

		if (empty($only_app) || $only_app == $this->vfs_appname ||
		    ($only_app[0] == '!' && $only_app != '!'.$this->vfs_appname))
		{
			if ($vfs_ids = $this->list_attached($app,$id))
			{
				$ids += $vfs_ids;
			}
		}
		//echo "ids=<pre>"; print_r($ids); echo "</pre>\n";

		return $ids;
	}

	/**
	 * Read one link specified by it's link_id or by the two end-points
	 *
	 * If $id is an array (links not yet created) only link_ids are allowed.
	 *
	 * @param int/string $app_link_id > 0 link_id of link or app-name of link
	 * @param string/array $id='' id if $app_link_id is an appname or array with links, if 1. entry not yet created
	 * @param string $app2='' second app
	 * @param string $id2='' id in $app2
	 * @return array with link-data or False
	 */ 
	function get_link($app_link_id,$id='',$app2='',$id2='')
	{
		if (is_array($id))
		{
			if (!strstr($app_link_id,':')) $app_link_id = $this->temp_link_id($app2,$id2);	// create link_id of temporary link, if not given
			
			if (isset($id[$app_link_id]) && is_array($id[$app_link_id]))	// check for unlinked-marker
			{
				return $id[$app_link_id];
			}
			return False;
		}
		if (intval($app_link_id) < 0 || $app_link_id == $this->vfs_appname || $app2 == $this->vfs_appname)
		{
			if (intval($app_link_id) < 0)	// vfs link_id ?
			{
				return $this->fileinfo2link(-$app_link_id);
			}
			if ($app_link_id == $this->vfs_appname)
			{
				return $this->info_attached($app2,$id2,$id);
			}
			return $this->info_attached($app_link_id,$id,$id2);
		}
		return solink::get_link($app_link_id,$id,$app2,$id2);
	}

	/**
	 * Remove link with $link_id or all links matching given $app,$id
	 *
	 * Note: if $link_id != '' and $id is an array: unlink removes links from that array only
	 * 	unlink has to be called with &$id to see the result (depricated) or unlink2 has to be used !!!
	 *
	 * @param $link_id link-id to remove if > 0
	 * @param string $app='' appname of first endpoint
	 * @param string/array $id='' id in $app or array with links, if 1. entry not yet created
	 * @param string $app2='' app of second endpoint
	 * @param string $id2='' id in $app2
	 * @return the number of links deleted
	 */
	function unlink($link_id,$app='',$id='',$owner='',$app2='',$id2='')
	{
		return $this->unlink2($link_id,$app,$id,$owner,$app2,$id2);
	}

	/**
	 * Remove link with $link_id or all links matching given $app,$id
	 *
	 * @param $link_id link-id to remove if > 0
	 * @param string $app='' appname of first endpoint
	 * @param string/array &$id='' id in $app or array with links, if 1. entry not yet created
	 * @param string $app2='' app of second endpoint
	 * @param string $id2='' id in $app2
	 * @return the number of links deleted
	 */
	function unlink2($link_id,$app,&$id,$owner='',$app2='',$id2='')
	{
		if ($this->debug)
		{
			echo "<p>bolink::unlink('$link_id','$app','$id','$owner','$app2','$id2')</p>\n";
		}
		if ($link_id < 0)	// vfs-link?
		{
			return $this->delete_attached(-$link_id);
		}
		elseif ($app == $this->vfs_appname)
		{
			return $this->delete_attached($app2,$id2,$id);
		}
		elseif ($app2 == $this->vfs_appname)
		{
			return $this->delete_attached($app,$id,$id2);
		}
		if (!is_array($id))
		{
			if (!$link_id && !$app2 && !$id2)
			{
				$this->delete_attached($app,$id);	// deleting all attachments
			}
			$deleted =& solink::unlink($link_id,$app,$id,$owner,$app2,$id2);
			
			// only notify on real links, not the one cached for writing or fileattachments
			$this->notify_unlink($deleted);

			return count($deleted);
		}
		if (!$link_id) $link_id = $this->temp_link_id($app2,$id2);	// create link_id of temporary link, if not given

		if (isset($id[$link_id]))
		{
			$id[$link_id] = False;	// set the unlink marker

			if ($this->debug)
			{
				_debug_array($id);
			}
			return True;
		}
		return False;
	}

	/**
	 * get list/array of link-aware apps the user has rights to use
	 *
	 * @param string $must_support capability the apps need to support, eg. 'add', default ''=list all apps
	 * @return array with app => title pairs
	 */
	function app_list($must_support='')
	{
		$apps = array();
		foreach($this->app_register as $app => $reg)
		{
			if ($must_support && !isset($reg[$must_support])) continue;

			if ($GLOBALS['egw_info']['user']['apps'][$app])
			{
				$apps[$app] = $GLOBALS['egw_info']['apps'][$app]['title'];
			}
		}
		return $apps;
	}

	/**
	 * Searches for a $pattern in the entries of $app
	 *
	 * @param string $app app to search
	 * @param string $pattern pattern to search
	 * @return array with $id => $title pairs of matching entries of app
	 */
	function query($app,$pattern)
	{
		if ($app == '' || !is_array($reg = $this->app_register[$app]) || !isset($reg['query']))
		{
			return array();
		}
		$method = $reg['query'];

		if ($this->debug)
		{
			echo "<p>bolink.query('$app','$pattern') => '$method'</p>\n";
		}
		return strchr($method,'.') ? ExecMethod($method,$pattern) : $this->$method($pattern);
	}

	/**
	 * returns the title (short description) of entry $id and $app
	 *
	 * @param string $app appname
	 * @param string $id id in $app 
	 * @param array $link=null link-data for file-attachments
	 * @return string/boolean string with title, null if $id does not exist in $app or false if no perms to view it
	 */
	function title($app,$id,$link=null)
	{
		if ($this->debug)
		{
			echo "<p>bolink::title('$app','$id')</p>\n";
		}
		if (!$id) return '';

		if ($app == $this->vfs_appname)
		{
			if (is_array($id) && $link)
			{
				$link = $id;
				$id = $link['name'];
			}
			if (is_array($link))
			{
				$size = $link['size'];
				if ($size_k = intval($size / 1024))
				{
					if (intval($size_k / 1024))
					{
						$size = sprintf('%3.1dM',doubleval($size_k)/1024.0);
					}
					else
					{
						$size = $size_k.'k';
					}
				}
				$extra = ': '.$link['type'] . ' '.$size;
			}
			return $id.$extra;
		}
		if ($app == '' || !is_array($reg = $this->app_register[$app]) || !isset($reg['title']))
		{
			return array();
		}
		$method = $reg['title'];

		$title = strchr($method,'.') ? ExecMethod($method,$id) : $this->$method($id);

		if ($id && is_null($title))	// $app,$id has been deleted ==> unlink all links to it
		{
			$this->unlink(0,$app,$id);
			return False;
		}
		return $title;
	}

	/**
	 * Add new entry to $app, evtl. already linked to $to_app, $to_id
	 *
	 * @param string $app appname of entry to create
	 * @param string $to_app appname to link the new entry to
	 * @param string $to_id id in $to_app 
	 * @return array/boolean with name-value pairs for link to add-methode of $app or false if add not supported
	 */
	function add($app,$to_app='',$to_id='')
	{
		//echo "<p>bolink::add('$app','$to_app','$to_id') app_register[$app] ="; _debug_array($app_register[$app]);
		if ($app == '' || !is_array($reg = $this->app_register[$app]) || !isset($reg['add']))
		{
			return false;
		}
		$params = $reg['add'];
		
		if ($reg['add_app'] && $to_app && $reg['add_id'] && $to_id)
		{
			$params[$reg['add_app']] = $to_app;
			$params[$reg['add_id']] = $to_id;
		}
		return $params;
	}

	/**
	 * view entry $id of $app
	 *
	 * @param string $app appname
	 * @param string $id id in $app 
	 * @param array $link=null link-data for file-attachments
	 * @return array with name-value pairs for link to view-methode of $app to view $id
	 */
	function view($app,$id,$link=null)
	{
		if ($app == $this->vfs_appname && !empty($id) && is_array($link))
		{
			return $this->get_file($link);
		}
		if ($app == '' || !is_array($reg = $this->app_register[$app]) || !isset($reg['view']) || !isset($reg['view_id']))
		{
			return array();
		}
		$view = $reg['view'];

		$names = explode(':',$reg['view_id']);
		if (count($names) > 1)
		{
			$id = explode(':',$id);
			while (list($n,$name) = each($names))
			{
				$view[$name] = $id[$n];
			}
		}
		else
		{
			$view[$reg['view_id']] = $id;
		}
		return $view;
	}

	/**
	 * Check if $app uses a popup for $action
	 *
	 * @param string $app app-name
	 * @param string $action='view' name of the action, atm. 'view' or 'add'
	 * @return boolean/string false if no popup is used or $app is not registered, otherwise string with the prefered popup size (eg. '640x400)
	 */
	function is_popup($app,$action='view')
	{
		if (!($reg = $this->app_register[$app]) || !$reg[$action.'_popup'])
		{
			return false;
		}
		return $reg[$action.'_popup'];
	}	

	function get_file($link='')
	{
		if (is_array($link))
		{
			return array(
				'menuaction' => 'phpgwapi.bolink.get_file',
				'app' => $link['app2'],
				'id'  => $link['id2'],
				'filename' => $link['id']
			);
		}
		$app = get_var('app','GET');
		$id  = get_var('id','GET');
		$filename = get_var('filename','GET');

		if (empty($app) || empty($id) || empty($filename) || !$this->title($app,$id))
		{
			$GLOBALS['egw_info']['flags']['nonavbar'] = false;
			$GLOBALS['egw']->common->egw_header();
			echo '<h1 style="text-align: center; color: red;">'.lang('Access not permitted')." !!!</h1>\n";
			$GLOBALS['egw']->common->egw_footer();
			$GLOBALS['egw']->common->egw_exit();
		}
		$browser =& CreateObject('phpgwapi.browser');

		$local = $this->attached_local($app,$id,$filename,$_SERVER['REMOTE_ADDR'],$browser->is_windows());

		if ($local)
		{
			Header('Location: ' . $local);
		}
		else
		{
			$info = $this->info_attached($app,$id,$filename);
			$browser->content_header($filename,$info['type']);
			echo $this->read_attached($app,$id,$filename);
		}
		$GLOBALS['egw']->common->egw_exit();
	}

	/**
	 * path to the attached files of $app/$ip or the directory for $app if no $id,$file given
	 *
	 * All link-files are based in the vfs-subdir '/infolog'. For other apps
	 * separate subdirs with name app are created.
	 *
	 * @param string $app appname
	 * @param string $id='' id in $app 
	 * @param string $file='' filename
	 * @param boolean/array $relatives=False return path as array with path in string incl. relatives
	 * @return string/array path or array with path and relatives, depending on $relatives
	 */
	function vfs_path($app,$id='',$file='',$relatives=False)
	{
		$path = $this->vfs_basedir . ($app == '' || $app == 'infolog' ? '' : '/'.$app) .
			($id != '' ? '/' . $id : '') . ($file != '' ? '/' . $file : '');
		
		if ($this->debug)
		{
			echo "<p>bolink::vfs_path('$app','$id','$file') = '$path'</p>\n";
		}
		return $relatives ? array(
			'string' => $path,
			'relatives' => is_array($relatives) ? $relatives : array($relatives)
		) : $path;
	}

	/**
	 * Put a file to the corrosponding place in the VFS and set the attributes
	 *
	 * @param string $app appname to linke the file to
	 * @param string $id id in $app 
	 * @param array $file informations about the file in format of the etemplate file-type
	 * 	$file['name'] name of the file (no directory)
	 * 	$file['type'] mine-type of the file
	 * 	$file['tmp_name'] name of the uploaded file (incl. directory)
	 * 	$file['path'] path of the file on the client computer
	 * 	$file['ip'] of the client (path and ip are only needed if u want a symlink (if possible))
	 * @param string $comment='' comment to add to the link
	 * @return int negative id of phpgw_vfs table as negative link-id's are for vfs attachments
	 */
	function attach_file($app,$id,$file,$comment='')
	{
		if ($this->debug)
		{
			echo "<p>attach_file: app='$app', id='$id', tmp_name='$file[tmp_name]', name='$file[name]', size='$file[size]', type='$file[type]', path='$file[path]', ip='$file[ip]', comment='$comment'</p>\n";
		}
		// create the root for attached files in infolog, if it does not exists
		$vfs_data = array('string'=>$this->vfs_basedir,'relatives'=>array(RELATIVE_ROOT));
		if (!($this->vfs->file_exists($vfs_data)))
		{
			$this->vfs->override_acl = 1;
			$this->vfs->mkdir($vfs_data);
			$this->vfs->override_acl = 0;
		}

		$vfs_data = $this->vfs_path($app,False,False,RELATIVE_ROOT);
		if (!($this->vfs->file_exists($vfs_data)))
		{
			$this->vfs->override_acl = 1;
			$this->vfs->mkdir($vfs_data);
			$this->vfs->override_acl = 0;
		}
		$vfs_data = $this->vfs_path($app,$id,False,RELATIVE_ROOT);
		if (!($this->vfs->file_exists($vfs_data)))
		{
			$this->vfs->override_acl = 1;
			$this->vfs->mkdir($vfs_data);
			$this->vfs->override_acl = 0;
		}
		$fname = $this->vfs_path($app,$id,$file['name']);
		$tfname = '';
		if (!empty($file['path']) && is_array($this->link_pathes) && count($this->link_pathes))
		{
			$file['path'] = str_replace('\\\\','/',$file['path']);	// vfs uses only '/'
			@reset($this->link_pathes);
			while ((list($valid,$trans) = @each($this->link_pathes)) && !$tfname)
			{  // check case-insensitive for WIN etc.
				$check = $valid[0] == '\\' || strstr(':',$valid) ? 'eregi' : 'ereg';
				$valid2 = str_replace('\\','/',$valid);
				//echo "<p>attach_file: ereg('".$this->send_file_ips[$valid]."', '$file[ip]')=".ereg($this->send_file_ips[$valid],$file['ip'])."</p>\n";
				if ($check('^('.$valid2.')(.*)$',$file['path'],$parts) &&
				    ereg($this->send_file_ips[$valid],$file['ip']) &&     // right IP
				    $this->vfs->file_exists(array('string'=>$trans.$parts[2],'relatives'=>array(RELATIVE_NONE|VFS_REAL))))
				{
					$tfname = $trans.$parts[2];
				}
				//echo "<p>attach_file: full_fname='$file[path]', valid2='$valid2', trans='$trans', check=$check, tfname='$tfname', parts=(x,'${parts[1]}','${parts[2]}')</p>\n";
			}
			if ($tfname && !$this->vfs->securitycheck(array('string'=>$tfname)))
			{
				return False; //lang('Invalid filename').': '.$tfname;
			}
		}
		$this->vfs->override_acl = 1;
		$this->vfs->cp(array(
			'symlink' => !!$tfname,		// try a symlink
			'from' => $tfname ? $tfname : $file['tmp_name'],
			'to'   => $fname,
			'relatives' => array(RELATIVE_NONE|VFS_REAL,RELATIVE_ROOT),
		));
		$this->vfs->set_attributes(array(
			'string' => $fname,
			'relatives' => array (RELATIVE_ROOT),
			'attributes' => array (
				'mime_type' => $file['type'],
				'comment' => stripslashes ($comment),
				'app' => $app
		)));
		$this->vfs->override_acl = 0;

		$link = $this->info_attached($app,$id,$file['name']);

		return is_array($link) ? $link['link_id'] : False;
	}

	/**
	 * deletes an attached file
	 *
	 * @param int/string $app > 0: file_id of an attchemnt or $app/$id entry which linked to
	 * @param string $id='' id in app
	 * @param string $fname filename
	 */
	function delete_attached($app,$id='',$fname = '')
	{
		if (intval($app) > 0)	// is file_id
		{
			$link  = $this->fileinfo2link($file_id=$app);
			$app   = $link['app2'];
			$id    = $link['id2'];
			$fname = $link['id'];
		}
		if ($this->debug)
		{
			echo "<p>bolink::delete_attached('$app','$id','$fname') file_id=$file_id</p>\n";
		}
		if (empty($app) || empty($id))
		{
			return False;	// dont delete more than all attachments of an entry
		}
		$vfs_data = $this->vfs_path($app,$id,$fname,RELATIVE_ROOT);
		
		$Ok = false;
		if ($this->vfs->file_exists($vfs_data))
		{
			$this->vfs->override_acl = 1;
			$Ok = $this->vfs->delete($vfs_data);
			$this->vfs->override_acl = 0;
		}
		// if filename given (and now deleted) check if dir is empty and remove it in that case
		if ($fname && !count($this->vfs->ls($vfs_data=$this->vfs_path($app,$id,'',RELATIVE_ROOT))))
		{
			$this->vfs->override_acl = 1;
			$this->vfs->delete($vfs_data);
			$this->vfs->override_acl = 0;
		}
		return $Ok;
	}

	/**
	 * converts the infos vfs has about a file into a link
	 *
	 * @param string $app appname
	 * @param string $id id in app
	 * @param string $filename filename
	 * @return array 'kind' of link-array
	 */
	function info_attached($app,$id,$filename)
	{
		$this->vfs->override_acl = 1;
		$attachments = $this->vfs->ls($this->vfs_path($app,$id,$filename,RELATIVE_NONE));
		$this->vfs->override_acl = 0;

		if (!count($attachments) || !$attachments[0]['name'])
		{
			return False;
		}
		return $this->fileinfo2link($attachments[0]);
	}

	/**
	 * converts a fileinfo (row in the vfs-db-table) in a link
	 *
	 * @param array/int $fileinfo a row from the vfs-db-table (eg. returned by the vfs ls function) or a file_id of that table
	 * @return array a 'kind' of link-array
	 */
	function fileinfo2link($fileinfo)
	{
		if (!is_array($fileinfo))
		{
			$fileinfo = $this->vfs->ls(array('file_id' => $fileinfo));
			list(,$fileinfo) = each($fileinfo);

			if (!is_array($fileinfo))
			{
				return False;
			}
		}
		$lastmod = $fileinfo[!empty($fileinfo['modified']) ? 'modified' : 'created'];
		list($y,$m,$d) = explode('-',$lastmod);
		$lastmod = mktime(0,0,0,$m,$d,$y);

		$dir_parts = array_reverse(explode('/',$fileinfo['directory']));

		return array(
			'app'       => $this->vfs_appname,
			'id'        => $fileinfo['name'],
			'app2'      => $dir_parts[1],
			'id2'       => $dir_parts[0],
			'remark'    => $fileinfo['comment'],
			'owner'     => $fileinfo['owner_id'],
			'link_id'   => -$fileinfo['file_id'],
			'lastmod'   => $lastmod,
			'size'      => $fileinfo['size'],
			'type'      => $fileinfo['mime_type']
		);
	}

	/**
	 * lists all attachments to $app/$id
	 *
	 * @param string $app appname
	 * @param string $id id in app
	 * @return array with link_id => 'kind' of link-array pairs
	 */
	function list_attached($app,$id)
	{
		$this->vfs->override_acl = 1;
		$attachments = $this->vfs->ls($this->vfs_path($app,$id,False,RELATIVE_ROOT));
		$this->vfs->override_acl = 0;

		if (!count($attachments) || !$attachments[0]['name'])
		{
			return False;
		}
		foreach($attachments as $fileinfo)
		{
			$link = $this->fileinfo2link($fileinfo);
			$attached[$link['link_id']] = $link;
		}
		return $attached;
	}

	/**
	 * checks if path starts with a '\\' or has a ':' in it
	 *
	 * @param string $path path to check
	 * @return boolean true if windows path, false otherwise
	 */
	function is_win_path($path)
	{
		return $path[0] == '\\' || strstr($path,':');
	}

	/**
	 * reads the attached file and returns the content
	 *
	 * @param string $app appname
	 * @param string $id id in app
	 * @param string $filename filename
	 * @return string/boolean content of the attached file, null if $id not found, false if no view perms
	 */
	function read_attached($app,$id,$filename)
	{
		$ret = null;
		if (empty($app) || !$id || empty($filename) || !($ret = $this->title($app,$id)))
		{
			return $ret;
		}
		$this->vfs->override_acl = 1;
		$data = $this->vfs->read($this->vfs_path($app,$id,$filename,RELATIVE_ROOT));
		$this->vfs->override_acl = 0;
		return $data;
	}

	/**
	 * Checks if filename should be local availible and if so returns
	 *
	 * @param string $app appname
	 * @param string $id id in app
	 * @param string $filename filename
	 * @param string $id ip-address of user
	 * @param boolean $win_user true if user is on windows, otherwise false
	 * @return string 'file:/path' for HTTP-redirect else return False
	 */
	function attached_local($app,$id,$filename,$ip,$win_user)
	{
		//echo "<p>attached_local(app=$app, id='$id', filename='$filename', ip='$ip', win_user='$win_user', count(send_file_ips)=".count($this->send_file_ips).")</p>\n";

		if (!$id || !$filename || /* !$this->check_access($info_id,EGW_ACL_READ) || */
		    !count($this->send_file_ips))
		{
			return False;
		}
		$link = $this->vfs->ls($this->vfs_path($app,$id,$filename,RELATIVE_ROOT)+array('readlink'=>True));
		$link = @$link[0]['symlink'];

		if ($link && is_array($this->link_pathes))
		{
			reset($this->link_pathes); $fname = '';
			while ((list($valid,$trans) = each($this->link_pathes)) && !$fname)
			{
				if (!$this->is_win_path($valid) == !$win_user && // valid for this OS
				    $win_user &&                                 // only for IE/windows atm
				    eregi('^'.$trans.'(.*)$',$link,$parts)  &&   // right path
				    ereg($this->send_file_ips[$valid],$ip))      // right IP
				{
					$fname = $valid . $parts[1];
					$fname = !$win_user ? str_replace('\\','/',$fname) : str_replace('/','\\',$fname);
					return 'file:'.($win_user ? '//' : '' ).$fname;
				}
				//echo "<p>attached_local: link=$link, valid=$valid, trans='$trans', fname='$fname', parts=(x,'${parts[1]}','${parts[2]}')</p>\n";
			}
		}
		return False;
	}

	/**
	 * reverse function of htmlspecialchars()
	 *
	 * @param string $str string to decode
	 * @return string decoded string
	 */
	function decode_htmlspecialchars($str)
	{
		return str_replace(array('&amp;','&quot;','&lt;','&gt;'),array('&','"','<','>'),$str);
	}

	/**
	 * get title for a project, should be moved to boprojects.link_title
	 *
	 * @param int/array $event project-id or already read project
	 * @return string/boolean the title (number: title), null if project is not found or false if no perms to view it
	 */
	function projects_title( $proj )
	{
		if (!is_object($this->boprojects))
		{
			if (!file_exists(EGW_SERVER_ROOT.'/projects'))	// check if projects installed
			{
				return false;
			}
			$this->boprojects = createobject('projects.boprojects');
		}
		if (!is_array($proj))
		{
			$proj = $this->boprojects->read_single_project( $proj );
		}
		return is_array($proj) ? $proj['number'].': '.$proj['title'] : False;
	}

	/**
	 * query for projects matching $pattern, should be moved to boprojects.link_query
	 *
	 * @param string $pattern pattern to search
	 * @return array with id => title pairs of matching projects
	 */
	function projects_query( $pattern )
	{
		if (!is_object($this->boprojects))
		{
			if (!file_exists(EGW_SERVER_ROOT.'/projects'))	// check if projects installed
				return array();
			$this->boprojects = createobject('projects.boprojects');
		}
		$projs = $this->boprojects->list_projects( array('action'=>'all','query'=>$pattern,'limit'=>FALSE) );
		$content = array();
		while ($projs && list( $key,$proj ) = each( $projs ))
		{
			$content[$proj['project_id']] = $this->projects_title($proj);
		}
		return $content;
	}

	/**
	 * notify other apps about changed content in $app,$id
	 *
	 * @param string $app name of app in which the updated happend
	 * @param string $id id in $app of the updated entry
	 * @param array $data=null updated data of changed entry, as the read-method of the BO-layer would supply it
	 */
	function notify_update($app,$id,$data=null)
	{
		foreach($this->get_links($app,$id,'!'.$this->vfs_appname) as $link_id => $link)
		{
			$this->notify('update',$link['app'],$link['id'],$app,$id,$link_id,$data);
		}
	}

	/**
	 * notify an application about a new or deleted links to own entries or updates in the content of the linked entry
	 *
	 * Please note: not all apps supply update notifications
	 *
	 * @internal 
	 * @param string $type 'link' for new links, 'unlink' for unlinked entries, 'update' of content in linked entries
	 * @param string $notify_app app to notify
	 * @param string $notify_id id in $notify_app
	 * @param string $target_app name of app whos entry changed, linked or deleted
	 * @param string $target_id id in $target_app
	 * @param array $data=null data of entry in app2 (optional)
	 */
	function notify($type,$notify_app,$notify_id,$target_app,$target_id,$link_id,$data=null)
	{
		if ($link_id && isset($this->app_register[$notify_app]) && isset($this->app_register[$notify_app]['notify']))
		{
			ExecMethod($this->app_register[$notify_app]['notify'],array(
				'type'       => $type,
				'id'         => $notify_id,
				'target_app' => $target_app,
				'target_id'  => $target_id,
				'link_id'    => $link_id,
				'data'       => $data,
			));
		}
	}

	/**
	 * notifies about unlinked links
	 *
	 * @internal 
	 * @param array &$links unlinked links from the database
	 */
	function notify_unlink(&$links)
	{
		foreach($links as $link)
		{
			// we notify both sides of the link, as the unlink command NOT clearly knows which side initiated the unlink
			$this->notify('unlink',$link['link_app1'],$link['link_id1'],$link['link_app2'],$link['link_id2'],$link['link_id']);
			$this->notify('unlink',$link['link_app2'],$link['link_id2'],$link['link_app1'],$link['link_id1'],$link['link_id']);
		}	
	}
}