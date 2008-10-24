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


include_once(EGW_INCLUDE_ROOT.'/etemplate/inc/class.so_sql.inc.php');

/**
 * General storage object for resources
 *
 * @author Cornelius Weiss <egw@von-und-zu-weiss.de>
 * @package resources
 */
class so_resources extends so_sql
{
	function so_resources()
	{
		$this->so_sql('resources','egw_resources');
		
		$custom =& CreateObject('admin.customfields','resources');
		$this->customfields = $custom->get_customfields();
		$this->soextra =& CreateObject('etemplate.so_sql');
		$this->soextra->so_sql('resources','egw_resources_extra');
	}

	/**
	 * gets the value of $key from resource of $res_id
	 *
	 * @param string $key key of value to get
	 * @param int $res_id resource id
	 * @return mixed value of key and resource, false if key or id not found.
	 */
	function get_value($key,$res_id)
	{
		if($this->db->select($this->table_name,$key,array('res_id' => $res_id),__LINE__,__FILE__))
		{
			$value = $this->db->row(row);
			return $value[$key];
		}
		return false;
	}
	
	/**
	 * reads resource including custom fields
	 *
	 * @param interger $res_id res_id
	 * @return array/boolean data if row could be retrived else False
	 */
	function read($res_id)
	{
		// read main data
		$resource = parent::read($res_id);
		
		// read customfields
		$keys = array(
			'extra_id' => $res_id,
			'extra_owner' => -1,
		);
		$customfields = $this->soextra->search($keys,false);
		foreach ((array)$customfields as $field)
		{
			$resource['#'.$field['extra_name']] = $field['extra_value'];
		}
		return $resource;
	}

	/**
	 * saves a resource including extra fields
	 *
	 * @param array $resource key => value 
	 * @return mixed id of resource if all right, false if fale
	 */
	function save($resource)
	{
		$this->data = $resource;
		if(parent::save() != 0) return false;
		$res_id = $this->data['res_id'];
		
		// save customfields
		foreach ($this->customfields as $field => $options)
		{
			$value = $resource['#'.$field];
			$data = array(
				'extra_id' => $res_id,
				'extra_name' => $field,
				'extra_owner' => -1,
				'extra_value' => $value,
			);
			$this->soextra->data = $data;
			$error_nr = $this->soextra->save();
			if($error_nr) return false;
		}
		return $res_id;
	}
	
}
