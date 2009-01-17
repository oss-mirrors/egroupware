<?php
/**
 * eGroupWare generalized SQL Storage Object with build in custom field support
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package etemplate
 * @link http://www.egroupware.org
 * @author Ralf Becker <RalfBecker@outdoor-training.de>
 * @copyright 2009 by RalfBecker@outdoor-training.de
 * @version $Id$
 */

/**
 * Generalized SQL Storage Object with build in custom field support
 * 
 * This class allows to display, search, order and filter by custom fields simply by replacing so_sql
 * by it and adding custom field widgets to the eTemplates of an applications. 
 * It's inspired by the code from Klaus Leithoff, which does the same thing limited to addressbook.
 *
 * The schema of the custom fields table should be like (the lenght of the cf name is nowhere enfored and
 * varies throughout eGW from 40-255, the value column from varchar(255) to longtext!):
 * 
 * 'egw_app_extra' => array(
 * 	'fd' => array(
 * 		'prefix_id' => array('type' => 'int','precision' => '4','nullable' => False),
 * 		'prefix_name' => array('type' => 'string','precision' => '64','nullable' => False),
 * 		'prefix_value' => array('type' => 'text'),
 * 	),
 *  'pk' => array('prefix_id','prefix_name'),
 *	'fk' => array(),
 *	'ix' => array(),
 *	'uc' => array()
 * )
 *
 * @package etemplate
 * @subpackage api
 * @author RalfBecker-AT-outdoor-training.de
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 */
class so_sql_cf extends so_sql
{
	const CF_PREFIX = '#';

	/**
	 * name of customefields table
	 *
	 * @var string
	 */
	var $extra_table;

	/**
	 * name of id column, defaults to the regular tables auto id
	 * 
	 * @var string
	 */
	var $extra_id = '_id';

	/**
	 * Name of key (cf name) column or just a postfix added to the table prefix
	 * 
	 * @var string
	 */
	var $extra_key = '_name';

	/**
	 * Name of value column or just a postfix added to the table prefix
	 * 
	 * @var string
	 */
	var $extra_value = '_value';

	var $extra_join;
	var $extra_join_order;
	var $extra_join_filter;
	
	/**
	 * Custom fields of $app, read by the constructor
	 * 
	 * @var array
	 */
	var $customfields;

	/**
	 * constructor of the class
	 * 
	 * Please note the different params compared to so_sql!
	 *
	 * @param string $app application name to load table schemas
	 * @param string $table name of the table to use
	 * @param string $extra_table name of the custom field table
	 * @param string $colum_prefix='' column prefix to automatic remove from the column-name, if the column name starts with it
	 * @param string $extra_key='_name' column name for cf name column (will be prefixed with colum prefix, if starting with _)
	 * @param string $extra_value='_value' column name for cf value column (will be prefixed with colum prefix, if starting with _)
	 * @param string $extra_id='_id' column name for cf id column (will be prefixed with colum prefix, if starting with _)
	 * @param egw_db $db=null database object, if not the one in $GLOBALS['egw']->db should be used, eg. for an other database
	 * @param boolean $no_clone=true can we avoid to clone the db-object, default yes (different from so_sql!)
	 * 	new code using appnames and foreach(select(...,$app) can set it to avoid an extra instance of the db object
	 */
	function __construct($app,$table,$extra_table,$column_prefix='',
		$extra_key='_name',$extra_value='_value',$extra_id='_id',
		$db=null,$no_clone=true)
	{
		// calling the so_sql constructor
		parent::__construct($app,$table,$db,$column_prefix,$no_clone);
		
		$this->extra_table = $extra_table;
		if (!$this->extra_id) $this->extra_id = $this->autoinc_id;	// default to auto id of regular table
		
		// if names from columns of extra table are only postfixes (starting with _), prepend column prefix
		if (!($prefix=$column_prefix))
		{
			list($prefix) = explode('_',$this->autoinc_id);
		}
		elseif(substr($prefix,-1) == '_')
		{
			$prefix = substr($prefix,0,-1);	// remove trailing underscore from column prefix parameter
		}
		foreach(array('extra_id','extra_key','extra_value') as $col)
		{
			$this->$col = $col_name = $$col;
			if ($col_name[0] == '_') $this->$col = $prefix . $$col;
		}
		// some sanity checks, maybe they should be active only for development
		if (!($extra_defs = $this->db->get_table_definitions($app,$extra_table)))
		{
			throw new egw_exception_wrong_parameter("extra table $extra_table is NOT defined!");
		}
		foreach(array('extra_id','extra_key','extra_value') as $col)
		{
			if (!$this->$col || !isset($extra_defs['fd'][$this->$col]))
			{
				throw new egw_exception_wrong_parameter("$col column $extra_table.{$this->$col} is NOT defined!");
			}
		}
		// setting up our extra joins, now we know table and column names
		$this->extra_join = " LEFT JOIN $extra_table ON $table.$this->autoinc_id=$extra_table.$this->extra_id";
		$this->extra_join_order = " LEFT JOIN $extra_table extra_order ON $table.$this->autoinc_id=extra_order.$this->extra_id";
		$this->extra_join_filter = " JOIN $extra_table extra_filter ON $table.$this->autoinc_id=extra_filter.$this->extra_id";
		
		$this->customfields = config::get_customfields($app);
	}

	/**
	 * Read all customfields of the given id's
	 *
	 * @param int|array $ids one ore more id's
	 * @param array $field_names=null custom fields to read, default all
	 * @return array id => self::CF_PREFIX.name => value
	 */
	function read_customfields($ids,$field_names=null)
	{
		if (is_null($field_names)) $field_names = array_keys($this->customfields);

		foreach((array)$ids as $key => $id)
		{
			if (!(int)$id && is_array($ids)) unset($ids[$key]);
		}
		if (!$ids || !$field_names) return array();	// nothing to do

		$fields = array();
		foreach($this->db->select($this->extra_table,'*',array(
			$this->extra_id => $ids,
			$this->extra_key => $field_names,
		),__LINE__,__FILE__,false,'',$this->app) as $row)
		{
			$fields[$row[$this->extra_id]][self::CF_PREFIX.$row[$this->extra_key]] = $row[$this->extra_value];
		}
		return $fields;
	}

	/**
	* saves custom field data
	*
	* @param array $data data to save (cf's have to be prefixed with self::CF_PREFIX = #)
	* @return bool false on success, errornumber on failure
	*/
	function save_customfields($data)
	{
		foreach ((array)$this->customfields as $field => $options)
		{
			if (!isset($data[self::CF_PREFIX.$field])) continue;

			$where = array(
				$this->extra_id    => $data[$this->autoinc_id],
				$this->extra_key   => $field,
			);
			
			if((string) $data[self::CF_PREFIX.$field] === '')	// dont write empty values
			{
				$this->db->delete($this->extra_table,$where,__LINE__,__FILE__,$this->app);	// just delete them, in case they were previously set
				continue;
			}
			if (!$this->db->insert($this->extra_table,array($this->extra_value => $data[self::CF_PREFIX.$field]),$where,__LINE__,__FILE__,$this->app))
			{
				return $this->db->Errno;
			}
		}
		return false;	// no error
	}
	
	/**
	 * merges in new values from the given new data-array
	 *
	 * reimplemented to also merge the customfields
	 *
	 * @param $new array in form col => new_value with values to set
	 */
	function data_merge($new)
	{
		parent::data_merge($new);

		if ($this->customfields)
		{
			foreach($this->customfields as $name => $data)
			{
				if (isset($new[self::CF_PREFIX.$name]))
				{
					$this->data[self::CF_PREFIX.$name] = $new[self::CF_PREFIX.$name];
				}
			}
		}
	}

	/**
	 * reads row matched by key and puts all cols in the data array
	 * 
	 * reimplented to also read the custom fields
	 *
	 * @param array $keys array with keys in form internalName => value, may be a scalar value if only one key
	 * @param string|array $extra_cols string or array of strings to be added to the SELECT, eg. "count(*) as num"
	 * @param string $join sql to do a join, added as is after the table-name, eg. ", table2 WHERE x=y" or
	 * @return array|boolean data if row could be retrived else False
	 */
	function read($keys,$extra_cols='',$join='')
	{
		if (!parent::read($keys,$extra_cols,$join))
		{
			return false;
		}
		if (($id = (int)$this->data[$this->autoinc_id]) && $this->customfields &&
			($cfs = $this->read_customfields($id)))
		{
			$this->data = array_merge($this->data,$cfs[$id]);
		}
		return $this->data;
	}

	/**
	 * saves the content of data to the db
	 *
	 * reimplented to also save the custom fields
	 *
	 * @param array $keys if given $keys are copied to data before saveing => allows a save as
	 * @param string|array $extra_where=null extra where clause, eg. to check an etag, returns true if no affected rows!
	 * @return int|boolean 0 on success, or errno != 0 on error, or true if $extra_where is given and no rows affected
	 */
	function save($keys=null,$extra_where=null)
	{
		if (is_array($keys) && count($keys)) $this->data_merge($keys);

		$ret = parent::save(null,$extra_where);
		
		if ($ret == 0 && $this->customfields)
		{
			$this->save_customfields($this->data);
		}
		return $ret;
	}

	/**
	 * deletes row representing keys in internal data or the supplied $keys if != null
	 *
	 * reimplented to also delete the custom fields
	 *
	 * @param array $keys if given array with col => value pairs to characterise the rows to delete
	 * @return int affected rows, should be 1 if ok, 0 if an error
	 */
	function delete($keys=null)
	{
		if ($this->customfields)
		{
			$query = parent::delete($keys,true);
			// check if query contains more then the id's
			if (!isset($query[$this->autoinc_id]) || count($query) != 1)
			{
				foreach($this->db->select($this->table_name,$this->autoinc_id,$query,__LINE__,__FILE__,false,'',$this->app) as $row)
				{
					$ids[] = $row[$this->autoinc_id];
				}
				if (!$ids) return 0;	// no rows affected
			}
			else
			{
				$ids = $query[$this->autoinc_id];
			}
			$this->db->delete($this->extra_table,array($this->extra_id => $ids),__LINE__,__FILE__);
		}
		return parent::delete($keys);
	}

	/**
	 * query rows for the nextmatch widget
	 * 
	 * Reimplemented to also read the custom fields (if enabled via $query['selectcols']).
	 * 
	 * Please note: the name of the nextmatch-customfields has to be 'customfields'!
	 *
	 * @param array $query with keys 'start', 'search', 'order', 'sort', 'col_filter'
	 *	For other keys like 'filter', 'cat_id' you have to reimplement this method in a derived class.
	 * @param array &$rows returned rows/competitions
	 * @param array &$readonlys eg. to disable buttons based on acl, not use here, maybe in a derived class
	 * @param string $join='' sql to do a join, added as is after the table-name, eg. ", table2 WHERE x=y" or
	 *	"LEFT JOIN table2 ON (x=y)", Note: there's no quoting done on $join!
	 * @param boolean $need_full_no_count=false If true an unlimited query is run to determine the total number of rows, default false
	 * @param mixed $only_keys=false, see search
	 * @return int total number of rows
	 */
	function get_rows($query,&$rows,&$readonlys,$join='',$need_full_no_count=false,$only_keys=false)
	{
		$criteria = array();
		if ($query['search'])
		{
			foreach($this->db_cols as $col)	// we search all cols
			{
				$criteria[$col] = $query['search'];
			}
			$criteria[$this->extra_value] = $query['search'];
		}
		$rows = $this->search($criteria,$only_keys,$query['order']?$query['order'].' '.$query['sort']:'',
			'','%',false,'OR',$query['num_rows']?array((int)$query['start'],$query['num_rows']):(int)$query['start'],
			$query['col_filter'],$join,$need_full_no_count);

		if (!$rows) $rows = array();	// otherwise false returned from search would be returned as array(false)
		
		$selectcols = $query['selectcols'] ? explode(',',$query['selectcols']) : array();

		if ($rows && $this->customfields && (!$selectcols || in_array('customfields',$selectcols)))
		{
			$id2keys = array();
			foreach($rows as $key => $row)
			{
				$id2keys[$row[$this->autoinc_id]] = $key;
			}
			// check if only certain cf's to show
			foreach($selectcols as $col)
			{
				if ($col[0] == self::CF_PREFIX) $fields[] = substr($col,1);
			}
			if (($cfs = $this->read_customfields(array_keys($id2keys),$fields)))
			{
				foreach($cfs as $id => $data)
				{
					$rows[$id2keys[$id]] = array_merge($rows[$id2keys[$id]],$data);
				}
			}
		}
		return $this->total;
	}

	/**
	 * searches db for rows matching searchcriteria
	 * 
	 * Reimplemented to search, order and filter by custom fields
	 *
	 * @param array/string $criteria array of key and data cols, OR a SQL query (content for WHERE), fully quoted (!)
	 * @param boolean/string/array $only_keys=true True returns only keys, False returns all cols. or
	 *	comma seperated list or array of columns to return
	 * @param string $order_by='' fieldnames + {ASC|DESC} separated by colons ',', can also contain a GROUP BY (if it contains ORDER BY)
	 * @param string/array $extra_cols='' string or array of strings to be added to the SELECT, eg. "count(*) as num"
	 * @param string $wildcard='' appended befor and after each criteria
	 * @param boolean $empty=false False=empty criteria are ignored in query, True=empty have to be empty in row
	 * @param string $op='AND' defaults to 'AND', can be set to 'OR' too, then criteria's are OR'ed together
	 * @param mixed $start=false if != false, return only maxmatch rows begining with start, or array($start,$num), or 'UNION' for a part of a union query
	 * @param array $filter=null if set (!=null) col-data pairs, to be and-ed (!) into the query without wildcards
	 * @param string $join='' sql to do a join, added as is after the table-name, eg. "JOIN table2 ON x=y" or
	 *	"LEFT JOIN table2 ON (x=y AND z=o)", Note: there's no quoting done on $join, you are responsible for it!!!
	 * @param boolean $need_full_no_count=false If true an unlimited query is run to determine the total number of rows, default false
	 * @return boolean/array of matching rows (the row is an array of the cols) or False
	 */
	function &search($criteria,$only_keys=True,$order_by='',$extra_cols='',$wildcard='',$empty=False,$op='AND',$start=false,$filter=null,$join='',$need_full_no_count=false)
	{
		// check if we search in the custom fields
		if ($criteria && is_array($criteria) && isset($criteria[$this->extra_value]))
		{
			$criteria[] = $this->extra_table.'.'.$this->extra_value . ' ' .
				$this->db->capabilities[egw_db::CAPABILITY_CASE_INSENSITIV_LIKE]. ' ' . 
				$this->db->quote('%'.$criteria[$this->extra_value].'%');
			unset($criteria[$this->extra_value]);
			$join .= $this->extra_join;
		}
		// check if we order by a custom field --> join cf table for given cf and order by it's value
		if (strpos($order_by,self::CF_PREFIX) !== false && 
			preg_match('/'.self::CF_PREFIX.'([^ ]+) (asc|desc)/i',$order_by,$matches))
		{
			$order_by = str_replace($matches[0],'extra_order.'.$this->extra_value.' IS NULL,extra_order.'.$this->extra_value.' '.$matches[2],$order_by);
			$join .= $this->extra_join_order.' AND extra_order.'.$this->extra_key.'='.$this->db->quote($matches[1]);
		}
		
		// check if we filter by a custom field
		foreach($filter as $name => $val)
		{
			if (is_string($name) && $name[0] == self::CF_PREFIX)
			{
				if (!empty($val))	// empty -> dont filter
				{
					$join .= str_replace('extra_filter','extra_filter'.$extra_filter,$this->extra_join_filter.
						' AND extra_filter.'.$this->extra_key.'='.$this->db->quote(substr($name,1)).
						' AND extra_filter.'.$this->extra_value.'='.$this->db->quote($val));
					++$extra_filter;
				}
				unset($filter[$name]);
			}
			elseif(is_int($name) && $val[0] == self::CF_PREFIX)	// lettersearch: #cfname LIKE 's%'
			{
				list($cf) = explode(' ',$val);
				$join .= str_replace('extra_filter','extra_filter'.$extra_filter,$this->extra_join_filter.
					' AND extra_filter.'.$this->extra_key.'='.$this->db->quote(substr($cf,1)).
					' AND '.str_replace($cf,'extra_filter.'.$this->extra_value,$val));
				++$extra_filter;
				unset($filter[$name]);
			}
		}
		
		return parent::search($criteria,$only_keys,$order_by,$extra_cols,$wildcard,$empty,$op,$start,$filter,$join,$need_full_no_count);
	}
	
	/**
	 * Prevent someone calling the old php4 so_sql constructor
	 */
	function so_sql()
	{
		throw new egw_exception_assertion_failed('use __construct()!');
	}
}
