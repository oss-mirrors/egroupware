<?php
/**
 * eGroupWare - resources
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package resources
 * @link http://www.egroupware.org
 * @author Cornelius Weiss <egw@von-und-zu-weiss.de>
 * @author Lukas Weiss <wnz_gh05t@users.sourceforge.net>
 * @version $Id$
 */


/**
 * General business object for resources
 *
 * @package resources
 */
class bo_resources
{
	const PICTURE_NAME = '.picture.jpg';
	var $resource_icons = '/resources/templates/default/images/resource_icons/';
	var $debug = 0;
	/**
	 * Instance of resources so object
	 *
	 * @var so_resources
	 */
	var $so;

	function bo_resources()
	{
		$this->so =& CreateObject('resources.so_resources');
		$this->acl =& CreateObject('resources.bo_acl');
		$this->cats = $this->acl->egw_cats;

		$this->cal_right_transform = array(
			EGW_ACL_CALREAD 	=> EGW_ACL_READ,
			EGW_ACL_DIRECT_BOOKING 	=> EGW_ACL_READ | EGW_ACL_ADD | EGW_ACL_EDIT | EGW_ACL_DELETE,
			EGW_ACL_CAT_ADMIN 	=> EGW_ACL_READ | EGW_ACL_ADD | EGW_ACL_EDIT | EGW_ACL_DELETE,
		);
	}

	/**
	 * get rows for resources list
	 *
	 * Cornelius Weiss <egw@von-und-zu-weiss.de>
	 */
	function get_rows($query,&$rows,&$readonlys)
	{
		if ($this->debug) _debug_array($query);
		$query['search'] = $query['search'] ? $query['search'] : '*';
		$criteria = array('name' => $query['search'], 'short_description' => $query['search'], 'inventory_number' => $query['search']);
		$read_onlys = 'res_id,name,short_description,quantity,useable,bookable,buyable,cat_id,location,storage_info';

		$accessory_of = $query['view_accs_of'] ? $query['view_accs_of'] : -1;
 		$filter = array('accessory_of' => $accessory_of);
		if ($query['filter'])
		{
			$filter = $filter + array('cat_id' => $query['filter']);
		}
		else
		{
			$readcats = array_flip((array)$this->acl->get_cats(EGW_ACL_READ));
			if($readcats) $filter = $filter + array('cat_id' => $readcats);
		}
		if($query['show_bookable']) $filter = $filter + array('bookable' => true);
		$order_by = $query['order'] ? $query['order'].' '. $query['sort'] : '';
		$start = (int)$query['start'];

		$rows = $this->so->search($criteria,$read_onlys,$order_by,'','%',$empty=False,$op='OR',$start,$filter,$join='',$need_full_no_count=false);
		$nr = $this->so->total;

		// we are called to serve bookable resources (e.g. calendar-dialog)
		if($query['show_bookable'])
		{
			// This is somehow ugly, i know...
			foreach((array)$rows as $num => $resource)
			{
				$rows[$num]['default_qty'] = 1;
			}
			// we don't need all the following testing
			return $nr;
		}

		foreach((array)$rows as $num => $resource)
		{
			if (!$this->acl->is_permitted($resource['cat_id'],EGW_ACL_EDIT))
			{
				$readonlys["edit[$resource[res_id]]"] = true;
			}
			if (!$this->acl->is_permitted($resource['cat_id'],EGW_ACL_DELETE))
			{
				$readonlys["delete[$resource[res_id]]"] = true;
			}
			if ((!$this->acl->is_permitted($resource['cat_id'],EGW_ACL_ADD)) || $accessory_of != -1)
			{
				$readonlys["new_acc[$resource[res_id]]"] = true;
			}
			if (!$resource['bookable'])
			{
				$readonlys["bookable[$resource[res_id]]"] = true;
				$readonlys["calendar[$resource[res_id]]"] = true;
			}
			if(!$this->acl->is_permitted($resource['cat_id'],EGW_ACL_CALREAD))
			{
				$readonlys["calendar[$resource[res_id]]"] = true;
			}
			if (!$resource['buyable'])
			{
				$readonlys["buyable[$resource[res_id]]"] = true;
			}
			$readonlys["view_acc[$resource[res_id]]"] = true;
			$links = egw_link::get_links('resources',$resource['res_id']);
			if(count($links) != 0 && $accessory_of == -1)
			{
				foreach ($links as $link_num => $link)
				{
					if($link['app'] == 'resources')
					{
						if($this->so->get_value('accessory_of',$link['res_id']) != -1)
						{
							$readonlys["view_acc[$resource[res_id]]"] = false;
						}
					}
				}
			}
			$rows[$num]['picture_thumb'] = $this->get_picture($resource['res_id']);
			$rows[$num]['admin'] = $this->acl->get_cat_admin($resource['cat_id']);
		}
		return $nr;
	}

	/**
	 * reads a resource exept binary datas
	 *
	 * Cornelius Weiss <egw@von-und-zu-weiss.de>
	 * @param int $res_id resource id
	 * @return array with key => value or false if not found or allowed
	 */
	function read($res_id)
	{
		if(!$this->acl->is_permitted($this->so->get_value('cat_id',$res_id),EGW_ACL_READ))
		{
			echo lang('You are not permitted to get information about this resource!') . '<br>';
			echo lang('Notify your administrator to correct this situation') . '<br>';
			return false;
		}
		return $this->so->read(array('res_id' => $res_id));
	}

	/**
	 * saves a resource. pictures are saved in vfs
	 *
	 * Cornelius Weiss <egw@von-und-zu-weiss.de>
	 * @param array $resource array with key => value of all needed datas
	 * @return string msg if somthing went wrong; nothing if all right
	 */
	function save($resource)
	{
		if(!$this->acl->is_permitted($resource['cat_id'],EGW_ACL_EDIT))
		{
			return lang('You are not permitted to edit this reource!');
		}

		// we need an id to save pictures and make links...
		if(!$resource['res_id'])
		{
			$resource['res_id'] = $this->so->save($resource);
		}

		switch ($resource['picture_src'])
		{
			case 'own_src':
				if($resource['own_file']['size'] > 0)
				{
					$msg = $this->save_picture($resource['own_file'],$resource['res_id']);
					break;
				}
				elseif(@egw_vfs::stat('/apps/resources/'.$resource['res_id'].'/'.self::PICTURE_NAME))
				{
					break;
				}
				$resource['picture_src'] = 'cat_src';
			case 'cat_src':
				break;
			case 'gen_src':
				$resource['picture_src'] = $resource['gen_src_list'];
				break;
			default:
				if($resource['own_file']['size'] > 0)
				{
					$resource['picture_src'] = 'own_src';
					$msg = $this->save_picture($resource['own_file'],$resource['res_id']);
				}
				else
				{
					$resource['picture_src'] = 'cat_src';
				}
		}
		// somthing went wrong on saving own picture
		if($msg)
		{
			return $msg;
		}

		// delete old pictures
		if($resource['picture_src'] != 'own_src')
		{
			$this->remove_picture($resource['res_id']);
		}

		// save links
		if(is_array($resource['link_to']['to_id']))
		{
			egw_link::link('resources',$resource['res_id'],$resource['link_to']['to_id']);
		}
		if($resource['accessory_of'] != -1)
		{
			egw_link::link('resources',$resource['res_id'],'resources',$resource['accessory_of']);
		}

		if(!empty($resource['res_id']) && $this->so->get_value("cat_id",$resource['res_id']) != $resource['cat_id'] && $resource['accessory_of'] == -1)
		{
			$accessories = $this->get_acc_list($resource['res_id']);
			foreach($accessories as $accessory => $name)
			{
				$acc = $this->so->read($accessory);
				$acc['cat_id'] = $resource['cat_id'];
				$this->so->data = $acc;
				$this->so->save();
			}
		}

		return $this->so->save($resource) ? false : lang('Something went wrong by saving resource');
	}

	/**
	 * deletes resource including pictures and links
	 *
	 * @author Lukas Weiss <wnz_gh05t@users.sourceforge.net>
	 * @param int $res_id id of resource
	 */
	function delete($res_id)
	{
		if(!$this->acl->is_permitted($this->so->get_value('cat_id',$res_id),EGW_ACL_DELETE))
		{
			return lang('You are not permitted to delete this reource!');
		}

		if ($this->so->delete(array('res_id'=>$res_id)))
		{
			$this->remove_picture($res_id);
	 		egw_link::unlink(0,'resources',$res_id);
	 		// delete the resource from the calendar
	 		ExecMethod('calendar.calendar_so.deleteaccount','r'.$res_id);
	 		return false;
		}
		return lang('Something went wrong by deleting resource');
	}

	/**
	 * gets list of accessories for resource
	 *
	 * Cornelius Weiss <egw@von-und-zu-weiss.de>
	 * @param int $res_id id of resource
	 * @return array
	 */
	function get_acc_list($res_id)
	{
		if($res_id < 1){return;}
		$data = $this->so->search('','res_id,name','','','','','',$start,array('accessory_of' => $res_id),'',$need_full_no_count=true);
		foreach($data as $num => $resource)
		{
			$acc_list[$resource['res_id']] = $resource['name'];
		}
		return $acc_list;
	}

	/**
	 * returns info about resource for calender
	 * @author Cornelius Weiss<egw@von-und-zu-weiss.de>
	 * @param int/array $res_id single id or array $num => $res_id
	 * @return array
	 */
	function get_calendar_info($res_id)
	{
		//echo "<p>bo_resources::get_calendar_info(".print_r($res_id,true).")</p>\n";
		if(!is_array($res_id) && $res_id < 1) return;

		$data = $this->so->search(array('res_id' => $res_id),'res_id,cat_id,name,useable');

		foreach($data as $num => $resource)
		{
			$data[$num]['rights'] = false;
			foreach($this->cal_right_transform as $res_right => $cal_right)
			{
				if($this->acl->is_permitted($resource['cat_id'],$res_right))
				{
					$data[$num]['rights'] = $cal_right;
				}
			}
			$data[$num]['responsible'] = $this->acl->get_cat_admin($resource['cat_id']);
		}
		return $data;
	}

	/**
	 * returns status for a new calendar entry depending on resources ACL
	 * @author Cornelius Weiss <egw@von-und-zu-weiss.de>
	 * @param int $res_id single id
	 * @return array
	 */
	function get_calendar_new_status($res_id)
	{
		$data = $this->so->search(array('res_id' => $res_id),'res_id,cat_id,bookable');
		if($data[0]['bookable'] == 0) return 'x';
		return $this->acl->is_permitted($data[0]['cat_id'],EGW_ACL_DIRECT_BOOKING) ? A : U;
	}

	/**
	 * @author Cornelius Weiss <egw@von-und-zu-weiss.de>
	 * query infolog for entries matching $pattern
	 * @param string|array $pattern if it's a string it is the string we will search for as a criteria, if it's an array we
	 * 	will seach for 'search' key in this array to get the string criteria. others keys handled are actually used
	 *	for calendar disponibility.
	 *
	 */
	function link_query( $pattern )
	{
		if (is_array($pattern))
		{
			$criteria =array('name' => $pattern['search']
					,'short_description' => $pattern['search']);
		}
		else
		{
			$criteria = array('name' => $pattern
				, 'short_description' => $pattern);
		}
		$only_keys = 'res_id,name,short_description,bookable,useable';
		$filter = array(
			'cat_id' => array_flip((array)$this->acl->get_cats(EGW_ACL_READ)),
			//'accessory_of' => '-1'
		);
		$data = $this->so->search($criteria,$only_keys,$order_by='',$extra_cols='',$wildcard='%',$empty,$op='OR',false,$filter);
		// maybe we need to check disponibility of the searched resources in the calendar if $pattern ['exec'] contains some extra args
		$show_conflict=False;
		if (is_array($pattern) && isset($pattern['exec']) )
		{
			// we'll use a cache for resources info taken from database
			static $res_info_cache = array();
			$cal_info=$pattern['exec'];
			if ( isset($cal_info['start']) && isset($cal_info['duration']))
			{
				//get a calendar objet for reservations
				if ( (!isset($this->bocal)) || !(is_object($this->bocal)))
				{
					require_once(EGW_INCLUDE_ROOT.'/calendar/inc/class.calendar_bo.inc.php');
					$this->bocal =& CreateObject('calendar.calendar_bo');
				}

				//get the real timestamps from infos we have on the event
				//use etemplate date widget to handle date values
				require_once(EGW_INCLUDE_ROOT.'/phpgwapi/inc/class.jscalendar.inc.php');
				$jscal=& CreateObject('phpgwapi.jscalendar');
				$startarr= $jscal->input2date($cal_info['start']['str'],$raw='raw',$day='day',$month='month',$year='year');
				if (isset($cal_info['whole_day'])) {
					$startarr['hour'] = $startarr['minute'] = 0;
					unset($startarr['raw']);
					$start = $this->bocal->date2ts($startarr);
					$end = $start + 86399;
				} else {
					$startarr['hour'] = $cal_info['start']['H'];
					$startarr['minute'] = $cal_info['start']['i'];
					unset($startarr['raw']);
					$start = $this->bocal->date2ts($startarr);
					$end = $start + ($cal_info['duration']);
				}

				// search events matching our timestamps
 				$resource_list=array();
				foreach($data as $num => $resource)
				{
					// we only need resources id for the search, but with a 'r' prefix
					// now we take this loop to store a new resource array indexed with resource id
					// and as we work for calendar we use only bookable resources
					if ((isset($resource['bookable'])) && ($resource['bookable'])){
						$res_info_cache[$resource['res_id']]=$resource;
						$resource_list[]='r'.$resource['res_id'];
					}
				}
				$overlapping_events =& $this->bocal->search(array(
					'start' => $start,
					'end'   => $end,
					'users' => $resource_list,
					'ignore_acl' => true,   // otherwise we get only events readable by the user
					'enum_groups' => false,  // otherwise group-events would not block time
				));

				// parse theses overlapping events
				foreach($overlapping_events as $event)
				{
					if ($event['non_blocking']) continue; // ignore non_blocking events
					if (isset($cal_info['event_id']) && $event['id']==$cal_info['event_id']) {
						continue; //ignore this event, it's the current edited event, no conflict by def
					}
					// now we are interested only on resources booked by theses events
					if (isset($event['participants']) && is_array($event['participants'])){
						foreach($event['participants'] as $part_key => $part_detail){
							if ($part_key{0}=='r')
							{ //now we gatta resource here
								//need to check the quantity of this resource
								$resource_id=substr($part_key,1);
								// if we do not find this resource in our indexed array it's certainly
								// because it was unset, non bookable maybe
								if (!isset($res_info_cache[$resource_id])) continue;
								// to detect ressources with default to 1 quantity
								if (!isset($res_info_cache[$resource_id]['useable'])) {
									$res_info_cache[$resource_id]['useable'] = 1;
								}
								// now decrement this quantity usable
								// TODO : decrement with real event quantity, not 1
								// but this quantity is not given by calendar search, we should re-use a cal object
								// to load specific cal infos, like quantity... lot of requests
								$res_info_cache[$resource_id]['useable']--;
							}
						}
					}
				}
			}
		}
		if (isset($res_info_cache)) {
			$show_conflict= (isset($pattern['exec']['show_conflict'])&& ($pattern['exec']['show_conflict']=='0'))? False:True;
			// if we have this array indexed on resource id it means non-bookable resource are removed and we are working for calendar
			// so we'll loop on this one and not $data
			foreach($res_info_cache as $id => $resource) {
				//maybe this resource is reserved
				if ( ($resource['useable'] < 1) )
				{
					if($show_conflict) {
						$list[$id] = ' ('.lang('conflict').') '.$resource['name']. ($resource['short_description'] ? ', ['.$resource['short_description'].']':'');
					}
				} else {
				        $list[$id] = $resource['name']. ($resource['short_description'] ? ', ['.$resource['short_description'].']':'');
				}
			}
		} else {
			// we are not working for the calendar, we loop on the initial $data
			foreach($data as $num => $resource)
			{
				$id=$resource['res_id'];
				$list[$id] = $resource['name']. ($resource['short_description'] ? ', ['.$resource['short_description'].']':'');
			}
		}
		return $list;
	}
	/**
	 * @author Cornelius Weiss <egw@von-und-zu-weiss.de>
	 * get title for an infolog entry identified by $res_id
	 *
	 * @return string/boolean string with title, null if resource does not exist or false if no perms to view it
	 */
	function link_title( $resource )
	{
		if (!is_array($resource))
		{
			if (!($resource  = $this->so->read(array('res_id' => $resource)))) return null;
		}
		if(!$this->acl->is_permitted($resource['cat_id'],EGW_ACL_READ)) return false;

		return $resource['name']. ($resource['short_description'] ? ', ['.$resource['short_description'].']':'');
	}

	/**
	 * resizes and saves an pictures in vfs
	 *
	 * Cornelius Weiss <egw@von-und-zu-weiss.de>
	 * @param array $file array with key => value
	 * @param int $resource_id
	 * @return mixed string with msg if somthing went wrong; nothing if all right
	 */
	function save_picture($file,$resouce_id)
	{
		switch($file['type'])
		{
			case 'image/gif':
				$src_img = imagecreatefromgif($file['tmp_name']);
				break;
			case 'image/jpeg':
			case 'image/pjpeg':
				$src_img = imagecreatefromjpeg($file['tmp_name']);
				break;
			case 'image/png':
			case 'image/x-png':
				$src_img = imagecreatefrompng($file['tmp_name']);
				break;
			default:
				return lang('Picture type is not supported, sorry!');
		}

		$src_img_size = getimagesize($file['tmp_name']);
		$dst_img_size = array( 0 => 320, 1 => 240);

		$tmp_name = tempnam($GLOBALS['egw_info']['server']['temp_dir'],'resources-picture');
		if($src_img_size[0] > $dst_img_size[0] || $src_img_size[1] > $dst_img_size[1])
		{
			$f = $dst_img_size[0] / $src_img_size[0];
			$f = $dst_img_size[1] / $src_img_size[1] < $f ? $dst_img_size[1] / $src_img_size[1] : $f;
			$dst_img = imagecreatetruecolor($src_img_size[0] * $f, $src_img_size[1] * $f);
			imagecopyresized($dst_img,$src_img,0,0,0,0,$src_img_size[0] * $f,$src_img_size[1] * $f,$src_img_size[0],$src_img_size[1]);
			imagejpeg($dst_img,$tmp_name);
			imagedestroy($dst_img);
		}
		else
		{
			imagejpeg($src_img,$tmp_name);
		}
		imagedestroy($src_img);

		egw_link::attach_file('resources',$resouce_id,array(
			'tmp_name' => $tmp_name,
			'name'     => self::PICTURE_NAME,
			'type'     => 'image/jpeg',
		));
	}

	/**
	 * get resource picture either from vfs or from symlink
	 * Cornelius Weiss <egw@von-und-zu-weiss.de>
	 * @param int $res_id id of resource
	 * @param bool $fullsize false = thumb, true = full pic
	 * @return string url of picture
	 */
	function get_picture($res_id=0,$fullsize=false)
	{
		if ($res_id > 0)
		{
			$src = $this->so->get_value('picture_src',$res_id);
		}
#echo $scr."<br>". $this->pictures_dir."<br>";
		switch($src)
		{
			case 'own_src':
				$picture = egw_link::vfs_path('resources',$res_id,self::PICTURE_NAME,true);	// vfs path
				if ($fullsize)
				{
					$picture = egw::link(egw_vfs::download_url($picture));
				}
				else
				{
					$picture = egw::link('/etemplate/thumbnail.php',array('path' => $picture));
				}
				//$picture=$GLOBALS['egw_info']['server'].$picture;
#echo $picture."<br>";
				break;
			case 'cat_src':
				list($picture) = $this->cats->return_single($this->so->get_value('cat_id',$res_id));
				$picture = unserialize($picture['data']);
				if($picture['icon'])
				{
					$picture = $GLOBALS['egw_info']['server']['webserver_url'].'/phpgwapi/images/'.$picture['icon'];
					break;
				}
			case 'gen_src':
			default :
				$picture = $GLOBALS['egw_info']['server']['webserver_url'].$this->resource_icons;
				$picture .= strpos($src,'.') !== false ? $src : 'generic.png';
		}
		return $picture;
	}

	/**
	 * remove_picture
	 * removes picture from vfs
	 *
	 * Cornelius Weiss <egw@von-und-zu-weiss.de>
	 * @param int $res_id id of resource
	 * @return bool succsess or not
	 */
	function remove_picture($res_id)
	{
		if (($arr = egw_link::delete_attached('resources',$res_id,self::PICTURE_NAME)))
		{
			return array_shift($arr);	// $arr = array($path => (bool)$ok);
		}
		return false;
	}

	/**
	 * get_genpicturelist
	 * gets all pictures from 'generic picutres dir' in selectbox style for eTemplate
	 *
	 * Cornelius Weiss <egw@von-und-zu-weiss.de>
	 * @return array directory contens in eTemplates selectbox style
	 */
	function get_genpicturelist()
	{
		$icons['generic.png'] = lang('gernal resource');
		$dir = dir(EGW_SERVER_ROOT.$this->resource_icons);
		while($file = $dir->read())
		{
			if (preg_match('/\\.(png|gif|jpe?g)$/i',$file) && $file != 'generic.png')
			{
				$icons[$file] = substr($file,0,strpos($file,'.'));
			}
		}
		$dir->close();
		return $icons;
	}
}
