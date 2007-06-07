<?php
/**
 * eGroupWare - importexport
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package importexport
 * @link http://www.egroupware.org
 * @author Cornelius Weiss <nelius@cwtech.de>
 * @copyright Cornelius Weiss <nelius@cwtech.de>
 * @version $Id$
 */

require_once(EGW_INCLUDE_ROOT. '/importexport/inc/class.definition.inc.php');
require_once(EGW_INCLUDE_ROOT. '/importexport/inc/class.arrayxml.inc.php');
require_once(EGW_INCLUDE_ROOT.'/etemplate/inc/class.so_sql.inc.php');

/** bo to define {im|ex}ports
 *
 * @todo make this class an egw_record_pool!
 */
class bodefinitions {

	const _appname = 'importexport';
	const _defintion_talbe = 'egw_importexport_definitions';
	
	/**
	 * holds so_sql
	 *
	 * @var so_sql
	 */
	private $so_sql;
	private $definitions;
	
	public function __construct($_query=false)
	{
		$this->so_sql = new so_sql(self::_appname, self::_defintion_talbe );
		if ($_query) {
			$definitions = $this->so_sql->search($_query, true);
			foreach ((array)$definitions as $definition) {
				$this->definitions[] = $definition['definition_id'];
			}
		}
	}
	
	/**
	 * gets definitions as raw data. 
	 * well, we need a god idea for egw_record pools...
	 * its not a god idea to make a definition object of each 
	 * at the moment, as each defintion holds an so_sql instance.
	 *
	 * @return array
	 */
	public function get_definitions() {
		return $this->definitions;
	}
	
	/**
	 * reads a definition from database
	 *
	 * @deprecated see class.definition.inc.php
	 * @param mixed &$definition
	 * @return bool success or not
	 */
	public function read(&$definition) {
		if(is_int($definition)) $definition = array('definition_id' => $definition);
		elseif(is_string($definition)) $definition = array('name' => $definition);
		if(!$definition = $this->so_sql->read($definition)) return false;
		$definition += (array)unserialize($definition['plugin_options']);
		unset($definition['plugin_options']);
		return true;
	}
	
	/**
	 * deletes a defintion
	 *
	 * @param array $keys
	 */
	public function delete($keys) {
		$this->so_sql->delete(array('definition_id' => $keys));
		// clear private cache
		foreach ($keys as $key) {
			unset($this->definitions[array_search($key,$this->definitions)]);
		}
	}
	
	/**
	 * checkes if user if permitted to access given definition
	 *
	 * @param array $_definition
	 * @return bool
	 */
	static public function is_permitted($_definition) {
		$allowed_user = explode(',',$_definition['allowed_users']);
		$this_user_id = $GLOBALS['egw_info']['user']['userid'];
		$this_membership = $GLOBALS['egw']->accounts->membership($this_user_id);
		$this_membership[] = array('account_id' => $this_user_id);
		//echo $this_user_id;
		//echo ' '.$this_membership;
		foreach ((array)$this_membership as $account)
		{
			$this_membership_array[] =  $account['account_id'];
		}
		$alluser = array_intersect($allowed_user,$this_membership_array);
		return in_array($this_user_id,$alluser) ? true : false;
	}
	
	/**
	 * exports definitions
	 *
	 * @param array $keys to export
	 */
	public function export($keys)
	{
		$export_data = array('metainfo' => array(
			'type' => 'importexport definitions',
			'charset' => $GLOBALS['egw']->translation->charset(),
			'entries' => count($keys),
		));
		
		$export_data['definitions'] = array();
		foreach ($keys as $definition_id) {
			$definition = new definition( $definition_id );
			$export_data['definitions'][$definition->name] = $definition->get_record_array();
			unset($export_data['definitions'][$definition->name]['definition_id']);
			unset($definition);
		}
		
		
		$xml = new arrayxml();
		return $xml->array2xml($export_data, 'importExportDefinitions');
	}
	
	/**
	 * imports definitions from file
	 *
	 * @param string $import_file
	 * @throws Exeption
	 * @return void
	 */
	public static function import( $_import_file )
	{
		if ( !is_file( $_import_file ) ) {
			throw new Exception("'$_import_file' is not a valid file" );
		}
		
		$data = arrayxml::xml2array( file_get_contents( $_import_file ) );
		
		$metainfo = $data['importExportDefinitions']['metainfo'];
		$definitions = $data['importExportDefinitions']['definitions'];
		unset ( $data );
		
		// convert charset into internal used charset
		$definitions = $GLOBALS['egw']->translation->convert( 
			$definitions,
			$metainfo['charset'],
			$GLOBALS['egw']->translation->charset()
		);
		
		// save definition(s) into internal table
		foreach ( $definitions as $name => $definition_data )
		{
			$definition = new definition( $definition_data['name'] );
			$definition_id = $definition->get_identifier() ? $definition->get_identifier() : NULL;
			
			$definition->set_record( $definition_data );
			$definition->save( $definition_id );
		}
	}
	
}

