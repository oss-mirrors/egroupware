<?php
/**
 * Addressbook - SQL backend
 *
 * @link http://www.egroupware.org
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @package addressbook
 * @copyright (c) 2006-8 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

include_once(EGW_INCLUDE_ROOT.'/etemplate/inc/class.so_sql.inc.php');

/**
 * SQL storage object of the adressbook
 */
class addressbook_sql extends so_sql
{
	/**
	 * name of customefields table
	 *
	 * @var string
	 */
	var $extra_table = 'egw_addressbook_extra';
	var $extra_join = ' LEFT JOIN egw_addressbook_extra ON egw_addressbook.contact_id=egw_addressbook_extra.contact_id';
	var $extra_join_order = ' LEFT JOIN egw_addressbook_extra extra_order ON egw_addressbook.contact_id=extra_order.contact_id';
	var $extra_join_filter = ' JOIN egw_addressbook_extra extra_filter ON egw_addressbook.contact_id=extra_filter.contact_id';
	var $account_repository = 'sql';
	var $contact_repository = 'sql';
	var $grants;

	/**
	 * join to show only active account (and not already expired ones)
	 */
	const ACOUNT_ACTIVE_JOIN = ' LEFT JOIN egw_accounts ON egw_addressbook.account_id=egw_accounts.account_id';
	/**
	 * filter to show only active account (and not already expired ones)
	 * UNIX_TIMESTAMP(NOW()) gets replaced with value of time() in the code!
	 */
	const ACOUNT_ACTIVE_FILTER = '(account_expires IS NULL OR account_expires = -1 OR account_expires > UNIX_TIMESTAMP(NOW()))';

	/**
	 * internal name of the id, gets mapped to uid
	 *
	 * @var string
	 */
	var $contacts_id='id';

	/**
	 * Name of the table for distribution lists
	 *
	 * @var string
	 */
	var $lists_table = 'egw_addressbook_lists';
	/**
	 * Name of the table with the members (contacts) of the distribution lists
	 *
	 * @var string
	 */
	var $ab2list_table = 'egw_addressbook2list';

	function __construct()
	{
		$this->so_sql('phpgwapi','egw_addressbook',null,'contact_',true);	// true = using the global db object, no clone!

		if ($GLOBALS['egw_info']['server']['account_repository'])
		{
			$this->account_repository = $GLOBALS['egw_info']['server']['account_repository'];
		}
		elseif ($GLOBALS['egw_info']['server']['auth_type'])
		{
			$this->account_repository = $GLOBALS['egw_info']['server']['auth_type'];
		}
		if ($GLOBALS['egw_info']['server']['contact_repository'])
		{
			$this->contact_repository = $GLOBALS['egw_info']['server']['contact_repository'];
		}
	}

	/**
	 * Query organisations by given parameters
	 *
	 * @var array $param
	 * @var string $param[org_view] 'org_name', 'org_name,adr_one_location', 'org_name,org_unit' how to group
	 * @var int $param[owner] addressbook to search
	 * @var string $param[search] search pattern for org_name
	 * @var string $param[searchletter] letter the org_name need to start with
	 * @var array $param[col_filter] filter
	 * @var string $param[search] or'ed search pattern
	 * @var int $param[start]
	 * @var int $param[num_rows]
	 * @var string $param[sort] ASC or DESC
	 * @return array or arrays with keys org_name,count and evtl. adr_one_location or org_unit
	 */
	function organisations($param)
	{
		$filter = is_array($param['col_filter']) ? $param['col_filter'] : array();

		// fix cat_id filter to search in comma-separated multiple cats and return subcats
		if ((int)$filter['cat_id'])
		{
			$filter[] = $this->_cat_filter($filter['cat_id']);
			unset($filter['cat_id']);
		}
		// add filter for read ACL in sql, if user is NOT the owner of the addressbook
		if ($param['owner'] && $param['owner'] == $GLOBALS['egw_info']['user']['account_id'])
		{
			$filter['owner'] = $param['owner'];
		}
		else
		{
			// we have no private grants in addressbook at the moment, they have then to be added here too
			if ($param['owner'])
			{
				if (!$this->grants[(int) $filter['owner']]) return false;	// we have no access to that addressbook

				$filter['owner'] = $param['owner'];
				$filter['private'] = 0;
			}
			else	// search all addressbooks, incl. accounts
			{
				if ($this->account_repository != 'sql' && $this->contact_repository != 'sql-ldap')
				{
					$filter[] = $this->table_name.'.contact_owner != 0';	// in case there have been accounts in sql previously
				}
				$filter[] = "(contact_owner=".(int)$GLOBALS['egw_info']['user']['account_id'].
					" OR contact_private=0 AND contact_owner IN (".
					implode(',',array_keys($this->grants))."))";
			}
		}
		if ($param['searchletter'])
		{
			$filter[] = 'org_name LIKE '.$this->db->quote($param['searchletter'].'%');
		}
		else
		{
			$filter[] = "org_name != ''";// AND org_name IS NOT NULL";
		}
		$sort = $param['sort'] == 'DESC' ? 'DESC' : 'ASC';

		list(,$by) = explode(',',$param['org_view']);
		if (!$by)
		{
			$extra = array(
				'COUNT(org_name) AS org_count',
				"COUNT(DISTINCT CASE WHEN org_unit IS NULL THEN '' ELSE org_unit END) AS org_unit_count",
				"COUNT(DISTINCT CASE WHEN adr_one_locality IS NULL THEN '' ELSE adr_one_locality END) AS adr_one_locality_count",
			);
			$append = "GROUP BY org_name ORDER BY org_name $sort";
		}
		else	// by adr_one_location or org_unit
		{
			// org total for more then one $by
			$by_expr = $by == 'org_unit_count' ? "COUNT(DISTINCT CASE WHEN org_unit IS NULL THEN '' ELSE org_unit END)" :
				"COUNT(DISTINCT CASE WHEN adr_one_locality IS NULL THEN '' ELSE adr_one_locality END)";
			$append = "GROUP BY org_name HAVING $by_expr > 1 ORDER BY org_name $sort";
			parent::search($param['search'],array('org_name'),$append,array(
				"NULL AS $by",
				'1 AS is_main',
				'COUNT(org_name) AS org_count',
				"COUNT(DISTINCT CASE WHEN org_unit IS NULL THEN '' ELSE org_unit END) AS org_unit_count",
				"COUNT(DISTINCT CASE WHEN adr_one_locality IS NULL THEN '' ELSE adr_one_locality END) AS adr_one_locality_count",
			),'%',false,'OR','UNION',$filter);
			// org by location
			$append = "GROUP BY org_name,$by ORDER BY org_name $sort,$by $sort";
			parent::search($param['search'],array('org_name'),$append,array(
				"CASE WHEN $by IS NULL THEN '' ELSE $by END AS $by",
				'0 AS is_main',
				'COUNT(org_name) AS org_count',
				"COUNT(DISTINCT CASE WHEN org_unit IS NULL THEN '' ELSE org_unit END) AS org_unit_count",
				"COUNT(DISTINCT CASE WHEN adr_one_locality IS NULL THEN '' ELSE adr_one_locality END) AS adr_one_locality_count",
			),'%',false,'OR','UNION',$filter);
			$append = "ORDER BY org_name $sort,is_main DESC,$by $sort";
		}
		$rows = parent::search($param['search'],array('org_name'),$append,$extra,'%',false,'OR',
			array($param['start'],$param['num_rows']),$filter);

		if (!$rows) return false;

		// query the values for *_count == 1, to display them instead
		$filter['org_name'] = $orgs = array();
		foreach($rows as $n => $row)
		{
			if ($row['org_unit_count'] == 1 || $row['adr_one_locality_count'] == 1)
			{
				$filter['org_name'][$row['org_name']] = $row['org_name'];	// use as key too to have every org only once
			}
			$org_key = $row['org_name'].($by ? '|||'.($row[$by] || $row[$by.'_count']==1 ? $row[$by] : '|||') : '');
			$orgs[$org_key] = $row;
		}
		unset($rows);

		if (count($filter['org_name']))
		{
			foreach((array) parent::search($criteria,array('org_name','org_unit','adr_one_locality'),'GROUP BY org_name,org_unit,adr_one_locality',
				'','%',false,'AND',false,$filter) as $row)
			{
				$org_key = $row['org_name'].($by ? '|||'.$row[$by] : '');
				if ($orgs[$org_key]['org_unit_count'] == 1)
				{
					$orgs[$org_key]['org_unit'] = $row['org_unit'];
				}
				if ($orgs[$org_key]['adr_one_locality_count'] == 1)
				{
					$orgs[$org_key]['adr_one_locality'] = $row['adr_one_locality'];
				}
				if ($by && isset($orgs[$org_key = $row['org_name'].'||||||']))
				{
					if ($orgs[$org_key]['org_unit_count'] == 1)
					{
						$orgs[$org_key]['org_unit'] = $row['org_unit'];
					}
					if ($orgs[$org_key]['adr_one_locality_count'] == 1)
					{
						$orgs[$org_key]['adr_one_locality'] = $row['adr_one_locality'];
					}
				}
			}
		}
		return array_values($orgs);
	}

	/**
	 * searches db for rows matching searchcriteria
	 *
	 * '*' and '?' are replaced with sql-wildcards '%' and '_'
	 *
	 * For a union-query you call search for each query with $start=='UNION' and one more with only $order_by and $start set to run the union-query.
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
	 * @param string $join='' sql to do a join, added as is after the table-name, eg. ", table2 WHERE x=y" or
	 *	"LEFT JOIN table2 ON (x=y)", Note: there's no quoting done on $join!
	 * @param boolean $need_full_no_count=false If true an unlimited query is run to determine the total number of rows, default false
	 * @return boolean/array of matching rows (the row is an array of the cols) or False
	 */
	function &search($criteria,$only_keys=True,$order_by='',$extra_cols='',$wildcard='',$empty=False,$op='AND',$start=false,$filter=null,$join='',$need_full_no_count=false)
	{
		if ((int) $this->debug >= 4) echo "<p>socontacts_sql::search(".print_r($criteria,true).",".print_r($only_keys,true).",'$order_by','$extra_cols','$wildcard','$empty','$op','$start',".print_r($filter,true).",'$join')</p>\n";

		$owner = isset($filter['owner']) ? $filter['owner'] : (isset($criteria['owner']) ? $criteria['owner'] : null);

		// fix cat_id filter to search in comma-separated multiple cats and return subcats
		if (($cats = $filter['cat_id']))
		{
			if ($filter['cat_id'][0] == '!')
			{
				$filter['cat_id'] = substr($filter['cat_id'],1);
				$not = 'NOT';
			}
			$filter[] = $this->_cat_filter((int)$filter['cat_id'],$not);
			unset($filter['cat_id']);
		}

		// add filter for read ACL in sql, if user is NOT the owner of the addressbook
		if (isset($this->grants) && !(isset($filter['owner']) && $filter['owner'] == $GLOBALS['egw_info']['user']['account_id']))
		{
			// we have no private grants in addressbook at the moment, they have then to be added here too
			if (isset($filter['owner']))
			{
				if (!$this->grants[(int) $filter['owner']]) return false;	// we have no access to that addressbook

				$filter['private'] = 0;
			}
			else	// search all addressbooks, incl. accounts
			{
				if ($this->account_repository != 'sql' && $this->contact_repository != 'sql-ldap')
				{
					$filter[] = $this->table_name.'.contact_owner != 0';	// in case there have been accounts in sql previously
				}
				$filter[] = "($this->table_name.contact_owner=".(int)$GLOBALS['egw_info']['user']['account_id'].
					" OR contact_private=0 AND $this->table_name.contact_owner IN (".
					implode(',',array_keys($this->grants)).") OR $this->table_name.contact_owner IS NULL)";
			}
		}
		$search_customfields = isset($criteria['contact_value']) && !empty($criteria['contact_value']);
		if (is_array($criteria))
		{
			foreach($criteria as $col => $val)
			{
				if ($col[0] === '#')	// search for a value in a certain custom field
				{
					$valarray=array();
					# val may be a list of values, constructed by multiple select fields, to be able to do the contains feature of adv-search
					# we split the value and search for each part individually
					if ($wildcard !='') {
						$valarray=explode(',',$val);
					} else {
						$valarray[]=$val;
					}
					$negate = false;      //negate the search funktion
					if ($criteria[$col][0] == '!') $negate = True;
					unset($criteria[$col]);
					foreach ($valarray as $vkey => $part)
					{
						$criteria[] =$this->table_name.'.contact_id'.($negate ? ' not '  :'').' in (select '.$this->extra_table.'.contact_id from '.$this->extra_table.' where '.
							"(".$this->extra_table.".contact_name='".substr($col,1)."' AND ".$this->extra_table.".contact_value".(!$wildcard?' = ':' LIKE ')."'".$wildcard.($negate?substr($part,1):$part).$wildcard."'"."))";

					}
					$search_customfields = true;
				}
				elseif($col === 'cat_id')	// search in comma-sep. cat-column
				{
					$criteria = array_merge($criteria,$this->_cat_search($val));
					unset($criteria[$col]);
				}
				elseif($col === 'contact_value')
				{
					if ($order_by[0] == '#')
					{
						$criteria =array_merge($criteria,array('extra_order.contact_value'=>$val));
						unset($criteria[$col]);
					}
				}
			}
		}
		if ($search_customfields)	// search the custom-fields
		{
			$join .= $this->extra_join;
		}
		// do we order by a cf?
		if ($order_by[0] == '#')
		{
			list($val) = explode("<>''",$order_by);
			$order_by = str_replace($val,'extra_order.contact_value',$order_by);
			$join .= $this->extra_join_order.' AND extra_order.contact_name='.$this->db->quote(substr($val,1));
		}
		// do we filter by a cf?
		$extra_filter = '';
		foreach($filter as $name => $val)
		{
			if ($name[0] == '#')
			{
				if (!empty($val))	// empty -> dont filter
				{
					$join .= str_replace('extra_filter','extra_filter'.$extra_filter,$this->extra_join_filter.' AND extra_filter.contact_name='.$this->db->quote(substr($name,1)).
						' AND extra_filter.contact_value='.$this->db->quote($val));
					++$extra_filter;
				}
				unset($filter[$name]);
			}
			elseif($val[0] == '#')	// lettersearch: #cfname like 's%'
			{
				list($cf) = explode(' ',$val);
				$join .= str_replace('extra_filter','extra_filter'.$extra_filter,$this->extra_join_filter.' AND extra_filter.contact_name='.$this->db->quote(substr($cf,1)).
					' AND '.str_replace($cf,'extra_filter.contact_value',$val));
				++$extra_filter;
				unset($filter[$name]);
			}
		}
		if (isset($filter['list']))
		{
			$join .= " JOIN $this->ab2list_table ON $this->table_name.contact_id=$this->ab2list_table.contact_id AND list_id=".(int)$filter['list'];
			unset($filter['list']);
		}
		if ($join)
		{
			switch(gettype($only_keys))
			{
				case 'boolean':
					// only return the egw_addressbook columns, to not generate dublicates by the left join
					// and to not return the NULL for contact_{id|owner} of not found custom fields!
					$only_keys = (strpos($join,$this->extra_table)!==false?'DISTINCT ':'').$this->table_name.'.'.($only_keys ? 'contact_id AS contact_id' : '*');
					break;
				case 'string':
					$only_keys = explode(',',$only_keys);
					// fall through
				case 'array':
					foreach($only_keys as $key => $val)
					{
						switch($key)
						{
							case 'id': case 'contact_id':
								$only_keys[$key] = $this->table_name.'.contact_id';
								break;
							case 'owner': case 'contact_owner':
								$only_keys[$key] = $this->table_name.'.contact_owner';
								break;
						}
					}
					break;
			}
			if (isset($filter['owner']))
			{
				$filter[] = $this->table_name.'.contact_owner='.(int)$filter['owner'];
				unset($filter['owner']);
			}
			if (isset($criteria['owner']))
			{
				$criteria[] = $this->table_name.'.contact_owner='.(int)$criteria['owner'];
				unset($criteria['owner']);
			}
			// postgres requires that expressions in order by appear in the columns of a distinct select
			if ($this->db->Type != 'mysql' && preg_match("/([a-zA-Z_.]+)<>''/",$order_by,$matches))
			{
				if (!is_array($extra_cols))	$extra_cols = $extra_cols ? explode(',',$extra_cols) : array();
				$extra_cols[] = $matches[1];
				$extra_cols[] = $matches[1]."<>''";
			}
		}
		// add join to show only active accounts (only if accounts are shown and in sql and we not already join the accounts table, eg. used by admin)
		if (!$owner && substr($this->account_repository,0,3) == 'sql' &&
			strpos($join,$GLOBALS['egw']->accounts->backend->table) === false && !array_key_exists('account_id',$filter))
		{
			$join .= self::ACOUNT_ACTIVE_JOIN;
			$filter[] = str_replace('UNIX_TIMESTAMP(NOW())',time(),self::ACOUNT_ACTIVE_FILTER);
		}
		$rows =& parent::search($criteria,$only_keys,$order_by,$extra_cols,$wildcard,$empty,$op,$start,$filter,$join,$need_full_no_count);

		if ($start === false) $this->total = is_array($rows) ? count($rows) : 0;	// so_sql sets total only for $start !== false!

		return $rows;
	}

	/**
	 * fix cat_id filter to search in comma-separated multiple cats and return subcats
	 *
	 * @internal
	 * @param int $cat_id
	 * @return string sql to filter by given cat
	 */
	function _cat_filter($cat_id, $not='')
	{
		if (!is_object($GLOBALS['egw']->categories))
		{
			$GLOBALS['egw']->categories = CreateObject('phpgwapi.categories');
		}
		foreach($GLOBALS['egw']->categories->return_all_children((int)$cat_id) as $cat)
		{
			$cat_filter[] = $this->db->concat("','",cat_id,"','")." $not LIKE '%,$cat,%'";
		}
		$cfilter = '('.implode(' OR ',$cat_filter).')';
		if(!empty($not))
		{
			$cfilter = "( $cfilter OR cat_id IS NULL )";
		}
		return $cfilter;
	}

	/**
	 * fix cat_id criteria to search in comma-separated multiple cats
	 *
	 * @internal
	 * @param int/array $cats
	 * @return array of sql-strings to be OR'ed or AND'ed together
	 */
	function _cat_search($cats)
	{
		$cat_filter = array();
		foreach(is_array($cats) ? $cats : array($cats) as $cat)
		{
			if (is_numeric($cat)) $cat_filter[] = $this->db->concat("','",cat_id,"','")." LIKE '%,$cat,%'";
		}
		return $cat_filter;
	}

	/**
	 * Change the ownership of contacts owned by a given account
	 *
	 * @param int $account_id account-id of the old owner
	 * @param int $new_owner account-id of the new owner
	 */
	function change_owner($account_id,$new_owner)
	{
		if (!$new_owner)	// otherwise we would create an account (contact_owner==0)
		{
			die("socontacts_sql::change_owner($account_id,$new_owner) new owner must not be 0");
		}
		$this->db->update($this->table_name,array(
			'contact_owner' => $new_owner,
		),array(
			'contact_owner' => $account_id,
		),__LINE__,__FILE__);
	}

	/**
	 * Get the availible distribution lists for givens users and groups
	 *
	 * @param array $uids user or group id's
	 * @return array with list_id => array(list_id,list_name,list_owner,...) pairs
	 */
	function get_lists($uids)
	{
		$user = $GLOBALS['egw_info']['user']['account_id'];
		$lists = array();
		foreach($this->db->select($this->lists_table,'*',array('list_owner'=>$uids),__LINE__,__FILE__,
			false,'ORDER BY list_owner<>'.(int)$GLOBALS['egw_info']['user']['account_id'].',list_name') as $row)
		{
			$lists[$row['list_id']] = $row;
		}
		//echo "<p>socontacts_sql::get_lists(".print_r($uids,true).")</p>\n"; _debug_array($lists);
		return $lists;
	}

	/**
	 * Adds a distribution list
	 *
	 * @param string $name list-name
	 * @param int $owner user- or group-id
	 * @param array $contacts=array() contacts to add
	 * @return int/boolean integer list_id, true if the list already exists or false on error
	 */
	function add_list($name,$owner,$contacts=array())
	{
		if (!$name || !(int)$owner) return false;

		if ($this->db->select($this->lists_table,'list_id',array(
			'list_name' => $name,
			'list_owner' => $owner,
		),__LINE__,__FILE__)->fetchSingle())
		{
			return true;	// return existing list-id
		}
		if (!$this->db->insert($this->lists_table,array(
			'list_name' => $name,
			'list_owner' => $owner,
			'list_created' => time(),
			'list_creator' => $GLOBALS['egw_info']['user']['account_id'],
		),array(),__LINE__,__FILE__)) return false;

		if ((int)($list_id = $this->db->get_last_insert_id($this->lists_table,'list_id')) && $contacts)
		{
			foreach($contacts as $contact)
			{
				$this->add2list($list_id,$contact);
			}
		}
		return $list_id;
	}

	/**
	 * Adds one contact to a distribution list
	 *
	 * @param int $contact contact_id
	 * @param int $list list-id
	 * @return false on error
	 */
	function add2list($contact,$list)
	{
		if (!(int)$list || !(int)$contact) return false;

		if ($this->db->select($this->ab2list_table,'list_id',array(
			'contact_id' => $contact,
			'list_id' => $list,
		),__LINE__,__FILE__)->fetchSingle())
		{
			return true;	// no need to insert it, would give sql error
		}
		return $this->db->insert($this->ab2list_table,array(
			'contact_id' => $contact,
			'list_id' => $list,
			'list_added' => time(),
			'list_added_by' => $GLOBALS['egw_info']['user']['account_id'],
		),array(),__LINE__,__FILE__);
	}

	/**
	 * Removes one contact from distribution list(s)
	 *
	 * @param int $contact contact_id
	 * @param int $list=null list-id or null to remove from all lists
	 * @return false on error
	 */
	function remove_from_list($contact,$list=null)
	{
		if (!(int)$list && !is_null($list) || !(int)$contact) return false;

		$where = array(
			'contact_id' => $contact,
		);
		if (!is_null($list)) $where['list_id'] = $list;

		return $this->db->delete($this->ab2list_table,$where,__LINE__,__FILE__);
	}

	/**
	 * Deletes a distribution list (incl. it's members)
	 *
	 * @param int/array $list list_id(s)
	 * @return number of members deleted or false if list does not exist
	 */
	function delete_list($list)
	{
		if (!$this->db->delete($this->lists_table,array('list_id' => $list),__LINE__,__FILE__)) return false;

		$this->db->delete($this->ab2list_table,array('list_id' => $list),__LINE__,__FILE__);

		return $this->db->affected_rows();
	}

	/**
	 * Reads a contact, reimplemented to use the uid, if a non-numeric key is given
	 *
	 * @param int|string|array $keys
	 * @param string|array $extra_cols
	 * @param string $join
	 * @return array|boolean
	 */
	function read($keys,$extra_cols='',$join='')
	{
		if (!is_array($keys) && !is_numeric($keys))
		{
			$keys = array('contact_uid' => $keys);
		}
		return parent::read($keys,$extra_cols,$join);
	}

	/**
	 * Saves a contact, reimplemented to check a given etag and set a uid
	 *
	 * @param array $keys if given $keys are copied to data before saveing => allows a save as
	 * @param string|array $extra_where=null extra where clause, eg. to check the etag, returns 'nothing_affected' if not affected rows
	 * @return int 0 on success and errno != 0 else
	 */
	function save($keys=null)
	{
		if (is_array($keys) && count($keys)) $this->data_merge($keys);

		$new_entry = !$this->data['id'];

		if (isset($this->data['etag']))		// do we have an etag in the data to write
		{
			$etag = $this->data['etag'];
			unset($this->data['etag']);
			if (!($err = parent::save(array('contact_etag=contact_etag+1'),array('contact_etag' => $etag))))
			{
				$this->data['etag'] = $etag+1;
			}
			else
			{
				$this->data['etag'] = $etag;
			}
		}
		else
		{
			unset($this->data['etag']);
			if (!($err = parent::save(array('contact_etag=contact_etag+1'))) && $new_entry)
			{
				$this->data['etag'] = 0;
			}
		}
		// enforce a minium uid strength
		if (!$err && ($new_entry || isset($this->data['uid'])) && (strlen($this->data['uid']) < 20 || is_numeric($this->data['uid'])))
		{
			parent::update(array('uid' => common::generate_uid('addressbook',$this->data['id'])));
			//echo "<p>set uid={$this->data['uid']}, etag={$this->data['etag']}</p>";
		}
		return $err;
	}


	/**
	 * Read data of a distribution list
	 *
	 * @param int $list list_id
	 * @return array of data or false if list does not exist
	 */
	function read_list($list)
	{
		if (!$list) return false;

		return $this->db->select($this->lists_table,'*',array('list_id'=>$list),__LINE__,__FILE__)->fetch();
	}
}
