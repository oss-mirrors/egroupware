<?php
/**
 * ProjectManager - Constraints storage object
 *
 * @link http://www.egroupware.org
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @package projectmanager
 * @copyright (c) 2005-8 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * Constraints storage object of the projectmanager
 *
 * Tables: egw_pm_constraints
 */
class projectmanager_constraints_so extends so_sql
{
	// Gantt chart supports 4 constraint types.  The most common is 0,
	// <start> Ends before <end>.
	static $constraint_types = array(
		'Ends before',
		'Starts before',
		'Ends after',
		'Starts after',
	);
	/**
	 * Constructor, calls the constructor of the extended class
	 *
	 * @param int $pm_id pm_id of the project to use, default null
	 */
	function __construct($pm_id=null)
	{
		parent::__construct('projectmanager','egw_pm_constraints');

		if ((int) $pm_id)
		{
			$this->pm_id = (int) $pm_id;
		}
	}

	/**
	 * searches db for rows matching searchcriteria, reimplemented to automatic add $this->pm_id
	 *
	 * '*' and '?' are replaced with sql-wildcards '%' and '_'
	 *
	 * @param array/string $criteria array of key and data cols, OR a SQL query (content for WHERE), fully quoted (!)
	 * @param boolean $only_keys=true True returns only keys, False returns all cols
	 * @param string $order_by='' fieldnames + {ASC|DESC} separated by colons ',', can also contain a GROUP BY (if it contains ORDER BY)
	 * @param string/array $extra_cols='' string or array of strings to be added to the SELECT, eg. "count(*) as num"
	 * @param string $wildcard='' appended befor and after each criteria
	 * @param boolean $empty=false False=empty criteria are ignored in query, True=empty have to be empty in row
	 * @param string $op='AND' defaults to 'AND', can be set to 'OR' too, then criteria's are OR'ed together
	 * @param mixed $start=false if != false, return only maxmatch rows begining with start, or array($start,$num)
	 * @param array $filter=null if set (!=null) col-data pairs, to be and-ed (!) into the query without wildcards
	 * @param string $join='' sql to do a join, added as is after the table-name, eg. ", table2 WHERE x=y" or
	 *	"LEFT JOIN table2 ON (x=y)", Note: there's no quoting done on $join!
	 * @return array of matching rows (the row is an array of the cols) or False
	 */
	function &search($criteria,$only_keys=True,$order_by='',$extra_cols='',$wildcard='',$empty=False,$op='AND',$start=false,$filter=null,$join='')
	{
		if ($this->pm_id && !isset($criteria['pm_id']) && !isset($filter['pm_id']))
		{
			$filter['pm_id'] = $this->pm_id;
		}
		if (isset($criteria['pe_id']) && $criteria['pe_id'])
		{
			$pe_id = is_numeric($criteria['pe_id']) ? (int) $criteria['pe_id'] : array_map('intval',$criteria['pe_id']);
			unset($criteria['pe_id']);
		}
		if (isset($filter['pe_id']) && $filter['pe_id'])
		{
			$pe_id = is_numeric($filter['pe_id']) ? (int) $filter['pe_id'] : array_map('intval',$filter['pe_id']);
			unset($filter['pe_id']);
		}
		if ($pe_id)
		{
			$filter[] = '('.$this->db->column_data_implode(' OR ',array('pe_id_start' => $pe_id, 'pe_id_end' => $pe_id)) .')';

			if ($extra_cols && !is_array($extra_cols)) $extra_cols = explode(',',$extra_cols);
			if (!$order_by) $order_by = 'pe_id_start';
		}
		return parent::search($criteria,$only_keys,$order_by,$extra_cols,$wildcard,$empty,$op,$start,$filter,$join);
	}

	/**
	 * reads all constraints of a milestone (ms_id given), an element (pe_id given) or a project (pm_id given)
	 *
	 * It calls allways search to retrive the data. The form of the data returned depends on the given keys!
	 *
	 * @param array $keys array with keys in form internalName => value, may be a scalar value if only one key
	 * @param string/array $extra_cols string or array of strings to be added to the SELECT, eg. "count(*) as num"
	 * @param string $join='' sql to do a join, added as is after the table-name, eg. ", table2 WHERE x=y" or
	 * @return array/boolean milestones: array with pe_id's, element: array with subarrays for start, end, milestone,
	 *	or same as search($keys) would return
	*/
	function read($keys,$extra_cols='',$join='')
	{
		if (!$search =& $this->search($criteria,$only_keys=False,$order_by='',$extra_cols='',$wildcard='',$empty=False,$op='AND',$start=false,$keys))
		{
			return false;
		}
		$ret = array();

		if ((int) $keys['ms_id'])
		{
			foreach($search as $row)
			{
				$ret[] = $row['pe_id_end'];
			}
		}
		elseif ((int) $keys['pe_id'])
		{
			$ret =& $search;

			// Add in a generated ID for UI to use
			foreach($search as &$row)
			{
				$pe_id_start = $row['pe_id_start'] ? $row['pe_id_start']: 'milestone:'.$row['ms_id'];
				$pe_id_end = $row['pe_id_end'] ? $row['pe_id_end']: 'milestone:'.$row['ms_id'];
				$row['id'] = $row['pm_id'] . ':'.$pe_id_start.':'.$pe_id_end;
			}
		}
		else
		{
			$ret =& $search;
		}
		if ($this->debug)
		{
			echo "<p>soconstraints::read(".print_r($keys,true).",'$extra_cols','$join')</p>\n";
			_debug_array($ret);
		}
		return $ret;
	}

	/**
	 * saves the given data to the db
	 *
	 * @param array $data with either data for one row or null, or
	 *	for the constraints of an elements the keys pe_id, start, end, milestone, or
	 *	for the constraints of a milestone the keys ms_id, pe_id (pm_id can be given or is taken from $this->pm_id)
	 * @return int 0 on success and errno != 0 else
	 */
	function save($data=null)
	{
		if ($this->debug) { echo "<p>soconstraints::save:"; _debug_array($data); }

		// constraints of an element?
		if ($data['pe_id'])
		{
			$pm_id = $data['pm_id'] ? $data['pm_id'] : $this->pm_id;
			unset($data['pm_id']);
			$pe_id = $data['pe_id'];
			unset($data['pe_id']);

			$this->delete(array(
				'pm_id' => $pm_id,
				'pe_id' => $pe_id,
			));
			foreach($data as $row)
			{
				$row['pm_id'] = $pm_id;

				if (($err = parent::save($row)))
				{
					return $err;
				}
			}
			return 0;
		}
		// constraints of a milestone
		if ($data['ms_id'] && is_array($data['pe_id']))
		{
			$keys = array(
				'pm_id'       => $data['pm_id'] ? $data['pm_id'] : $this->pm_id,
				'pe_id_start' => 0,
				'ms_id'       => $data['ms_id'],
			);
			$this->delete($keys);

			foreach($data['pe_id'] as $pe_id);
			{
				$keys['pe_id_end'] = $pe_id;

				if (($err = parent::save($keys)))
				{
					return $err;
				}
			}
			return 0;
		}
		return parent::save($data);
	}

	/**
	 * reimplented to delete all constraints from a project-element if a pe_id is given
	 *
	 * @param array/int $keys if given array with col => value pairs to characterise the rows to delete or pe_id
	 * @return int affected rows, should be 1 if ok, 0 if an error
	 */
	function delete($keys=null)
	{
		if ($this->debug) echo "<p>soconstraints::delete(".print_r($keys,true).")</p>\n";

		if (is_numeric($keys) || is_array($keys) && (int) $keys['pe_id'])
		{
			if (is_array($keys))
			{
				$pe_id = (int) $keys['pe_id'];
				unset($keys['pe_id']);
			}
			else
			{
				$pe_id = (int) $keys;
				$keys = array();
			}
			$keys[] = "(pe_id_end=$pe_id OR pe_id_start=$pe_id)";
			return $this->db->delete($this->table_name,$keys,__LINE__,__FILE__);
		}
		return parent::delete($keys);
	}

	/**
	 * Copy the constrains from an other project
	 *
	 * @param int $source pm_id of the project to copy
	 * @param array $elements array with old => new pe_id's
	 * @param array $milestones array with old => new ms_id's
	 * @param int $pm_id=null pm_id to use, default null to use the current pm_id
	 * @return true if all contrains copied successful to the new project, false otherwise
	 */
	function copy($source,$elements,$milestones,$pm_id=null)
	{
		if (is_null($pm_id)) $pm_id = $this->pm_id;

		$copied = 0;
		if (($constrains = $this->search(array('pm_id' => $source),false)))
		{
			foreach($constrains as $n => $constrain)
			{
				if ($constrain['pe_id_start'])
				{
					if (!isset($elements[$constrain['pe_id_start']])) continue;
					$constrain['pe_id_start'] = $elements[$constrain['pe_id_start']];
				}
				if ($constrain['pe_id_end'])
				{
					if (!isset($elements[$constrain['pe_id_end']])) continue;
					$constrain['pe_id_end'] = $elements[$constrain['pe_id_end']];
				}
				if ($constrain['ms_id'])
				{
					if (!isset($milestones[$constrain['ms_id']])) continue;
					$constrain['ms_id'] = $milestones[$constrain['ms_id']];
				}
				$constrain['pm_id'] = $pm_id;
				$this->init($constrain);
				$this->save();

				$copied++;
			}
		}
		return $copied == count($constrains);

	}
}