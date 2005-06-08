<?php
include_once(GALAXIA_LIBRARY.SEP.'src'.SEP.'ProcessManager'.SEP.'BaseManager.php');
//!! ProcessManager
//! A class to maniplate processes.
/*!
  This class is used to add,remove,modify and list
  processes.
  Most of the methods acts directly in database level, bypassing Project object methods.
*/
class ProcessManager extends BaseManager {
  var $parser;
  var $tree;
  var $current;
  var $buffer;
  var $Process;
  
  /*!
    Constructor takes a PEAR::Db object to be used
    to manipulate roles in the database.
  */
  function ProcessManager(&$db) 
  {
    if(!$db) {
      die("Invalid db object passed to ProcessManager constructor");  
    }
    $this->db =& $db;  
  }
 
  /*!
    Sets a process as active
  */
  function activate_process($pId)
  {
    $query = "update ".GALAXIA_TABLE_PREFIX."processes set wf_is_active='y' where wf_p_id=$pId";
    $this->query($query);  
    $msg = sprintf(tra('Process %d has been activated'),$pId);
    $this->notify_all(3,$msg);
  }
  
  /*!
    De-activates a process
  */
  function deactivate_process($pId)
  {
    $query = "update ".GALAXIA_TABLE_PREFIX."processes set wf_is_active='n' where wf_p_id=$pId";
    $this->query($query);  
    $msg = sprintf(tra('Process %d has been deactivated'),$pId);
    $this->notify_all(3,$msg);
  }
  
  /*!
    Creates an XML representation of a process.
  */
  function serialize_process($pId)
  {
    // <process>
    $out = '<process>'."\n";
    $proc_info = $this->get_process($pId);
    $wf_procname = $proc_info['wf_normalized_name'];
    $out.= '  <name>'.htmlspecialchars($proc_info['wf_name']).'</name>'."\n";
    $out.= '  <isValid>'.htmlspecialchars($proc_info['wf_is_valid']).'</isValid>'."\n";
    $out.= '  <version>'.htmlspecialchars($proc_info['wf_version']).'</version>'."\n";
    $out.= '  <isActive>'.htmlspecialchars($proc_info['wf_is_active']).'</isActive>'."\n";
    $out.='   <description>'.htmlspecialchars($proc_info['wf_description']).'</description>'."\n";
    $out.= '  <lastModif>'.date("d/m/Y [h:i:s]",$proc_info['wf_last_modif']).'</lastModif>'."\n";

    //Shared code
    $out.= '  <sharedCode><![CDATA[';
    $fp=fopen(GALAXIA_PROCESSES.SEP."$wf_procname".SEP."code".SEP."shared.php","r");
    while(!feof($fp)) {
      $line=fread($fp,8192);
      $out.=$line;
    }
    fclose($fp);
    $out.= '  ]]></sharedCode>'."\n";

    //Loop on config values
    $query = "select * from ".GALAXIA_TABLE_PREFIX."process_config where wf_p_id=$pId";
    $result = $this->query($query);
    $out.='  <configs>'."\n";
    while($res = $result->fetchRow()) {      
      $name = $res['wf_config_name'];
      $value_int = $res['wf_config_value_int'];
      $value = $res['wf_config_value'];
      $out.='    <config>'."\n";
      $out.='      <name>'.htmlspecialchars($name).'</name>'."\n";
      $out.='      <value>'.htmlspecialchars($value).'</value>'."\n";
      $out.='      <value_int>'.htmlspecialchars($value_int).'</value_int>'."\n";
      $out.='    </config>'."\n";
    }
    $out.='  </configs>'."\n";

    // Now loop over activities
    $query = "select * from ".GALAXIA_TABLE_PREFIX."activities where wf_p_id=$pId";
    $result = $this->query($query);
    $out.='  <activities>'."\n";
    $am = new ActivityManager($this->db);
    while($res = $result->fetchRow()) {      
      $name = $res['wf_normalized_name'];
      $out.='    <activity>'."\n";
      $out.='      <name>'.htmlspecialchars($res['wf_name']).'</name>'."\n";
      $out.='      <type>'.htmlspecialchars($res['wf_type']).'</type>'."\n";
      $out.='      <description>'.htmlspecialchars($res['wf_description']).'</description>'."\n";
      $out.='      <lastModif>'.date("d/m/Y [h:i:s]",$res['wf_last_modif']).'</lastModif>'."\n";
      $out.='      <isInteractive>'.$res['wf_is_interactive'].'</isInteractive>'."\n";
      $out.='      <isAutoRouted>'.$res['wf_is_autorouted'].'</isAutoRouted>'."\n";
      $out.='      <roles>'."\n";

      $roles = $am->get_activity_roles($res['wf_activity_id']);
      foreach($roles as $role) {
        $out.='        <role>'.htmlspecialchars($role['wf_name']).'</role>'."\n";
      }  
      $out.='      </roles>'."\n";
      $out.='      <code><![CDATA[';
      $fp=fopen(GALAXIA_PROCESSES.SEP."$wf_procname".SEP."code".SEP."activities".SEP."$name.php","r");
      while(!feof($fp)) {
        $line=fread($fp,8192);
        $out.=$line;
      }
      fclose($fp);
      $out.='      ]]></code>';
      if($res['wf_is_interactive']=='y') {
        $out.='      <template><![CDATA[';
        $fp=fopen(GALAXIA_PROCESSES.SEP."$wf_procname".SEP."code".SEP."templates".SEP."$name.tpl","r");
        while(!feof($fp)) {
          $line=fread($fp,8192);
          $out.=$line;
        }
        fclose($fp);
        $out.='      ]]></template>';
      }
      $out.='    </activity>'."\n";    
    }
    $out.='  </activities>'."\n";
    $out.='  <transitions>'."\n";
    $transitions = $am->get_process_transitions($pId);
    foreach($transitions as $tran) {
      $out.='     <transition>'."\n";
      $out.='       <from>'.htmlspecialchars($tran['wf_act_from_name']).'</from>'."\n";
      $out.='       <to>'.htmlspecialchars($tran['wf_act_to_name']).'</to>'."\n";
      $out.='     </transition>'."\n";
    }     
    $out.='  </transitions>'."\n";
    $out.= '</process>'."\n";
    //$fp = fopen(GALAXIA_PROCESSES."/$wf_procname/$wf_procname.xml","w");
    //fwrite($fp,$out);
    //fclose($fp);
    return $out;
  }
  
  /*!
    Creates  a process PHP data structure from its XML 
    representation
  */
  function unserialize_process($xml) 
  {
    // Create SAX parser assign this object as base for handlers
    // handlers are private methods defined below.
    // keep contexts and parse
    $this->parser = xml_parser_create(); 
    xml_parser_set_option($this->parser,XML_OPTION_CASE_FOLDING,0);
    xml_set_object($this->parser, $this);
    xml_set_element_handler($this->parser, "_start_element_handler", "_end_element_handler");
    xml_set_character_data_handler($this->parser, "_data_handler"); 
    $aux=Array(
      'name'=>'root',
      'children'=>Array(),
      'parent' => 0,
      'data'=>''
    );
    $this->tree[0]=$aux;
    $this->current=0;
    if (!xml_parse($this->parser, $xml, true)) {
       $error = sprintf("XML error: %s at line %d",
                    xml_error_string(xml_get_error_code($this->parser)),
                    xml_get_current_line_number($this->parser));
       trigger_error($error,E_USER_WARNING);
    }
    xml_parser_free($this->parser);   
    // Now that we have the tree we can do interesting things
    //print_r($this->tree);
    $process=Array();
    $activities=Array();
    $transitions=Array();
    for($i=0;$i<count($this->tree[1]['children']);$i++) {
      // Process attributes
      $z=$this->tree[1]['children'][$i];
      $name = trim($this->tree[$z]['name']);
      
      //config values
      if ($name=='configs') {
        for($j=0;$j<count($this->tree[$z]['children']);$j++) {
          $z2 = $this->tree[$z]['children'][$j];
          // this is a config $name = $this->tree[$z2]['name'];
          if($this->tree[$z2]['name']=='config') {
            for($k=0;$k<count($this->tree[$z2]['children']);$k++) {
              $z3 = $this->tree[$z2]['children'][$k];
              $name = trim($this->tree[$z3]['name']);
              $value= trim($this->tree[$z3]['data']);
              $aux[$name]=$value;
            }
            $configs[]=$aux;
          }
        }      
      }
      //activities
      elseif($name=='activities') {
        for($j=0;$j<count($this->tree[$z]['children']);$j++) {
          $z2 = $this->tree[$z]['children'][$j];
          // this is an activity $name = $this->tree[$z2]['name'];
          if($this->tree[$z2]['name']=='activity') {
            for($k=0;$k<count($this->tree[$z2]['children']);$k++) {
              $z3 = $this->tree[$z2]['children'][$k];
              $name = trim($this->tree[$z3]['name']);
              $value= trim($this->tree[$z3]['data']);
              if($name=='roles') {
                $roles=Array();
                for($l=0;$l<count($this->tree[$z3]['children']);$l++) {
                  $z4 = $this->tree[$z3]['children'][$l];
                  $name = trim($this->tree[$z4]['name']);
                  $data = trim($this->tree[$z4]['data']);
                  $roles[]=$data;
                }                
              } else {
                $aux[$name]=$value;
                //print("$name:$value<br/>");
              }
            }
            $aux['roles']=$roles;
            $activities[]=$aux;
          }
        }
      } elseif($name=='transitions') {
        for($j=0;$j<count($this->tree[$z]['children']);$j++) {
          $z2 = $this->tree[$z]['children'][$j];
          // this is an activity $name = $this->tree[$z2]['name'];
          if($this->tree[$z2]['name']=='transition') {
            for($k=0;$k<count($this->tree[$z2]['children']);$k++) {
              $z3 = $this->tree[$z2]['children'][$k];
              $name = trim($this->tree[$z3]['name']);
              $value= trim($this->tree[$z3]['data']);
              if($name == 'from' || $name == 'to') {
                $aux[$name]=$value;
              }
            }
          }
          $transitions[] = $aux;
        }
      } else {
        $value = trim($this->tree[$z]['data']);
        //print("$name is $value<br/>");
        $process[$name]=$value;
      }
    }
    $process['configs']=$configs;
    $process['activities']=$activities;
    $process['transitions']=$transitions;
    return $process;
  }

  /*!
   Creates a process from the process data structure, if you want to 
   convert an XML to a process then use first unserialize_process
   and then this method.
  */
  function import_process($data)
  {
    //Now the show begins
    $am = new ActivityManager($this->db);
    $rm = new RoleManager($this->db);
    // First create the process
    $vars = Array(
      'name' => $data['name'],
      'version' => $data['version'],
      'description' => $data['description'],
      'lastModif' => $data['lastModif'],
      'isActive' => $data['isActive'],
      'isValid' => $data['isValid']
    );
    $pid = $this->replace_process(0,$vars,false);
    //Put the shared code 
    $proc_info = $this->get_process($pid);
    $wf_procname = $proc_info['wf_normalized_name'];
    $fp = fopen(GALAXIA_PROCESSES.SEP."$wf_procname".SEP."code".SEP."shared.php","w");
    fwrite($fp,$data['sharedCode']);
    fclose($fp);
    $actids = Array();
    // Store config values
    // rebuild a config_array with type of config and value
    $config_array = array();
    foreach($data['configs'] as $config) {
      if (isset($config['value_int']))
      {
        $config_array[$config['name']] = array('int' => $config['value_int']);
      }
      else
      {
        $config_array[$config['name']] = array('text' => $config['value']);
      }
    }
    $this->setConfigValues($pid, $config_array);
    
    // Foreach activity create activities
    foreach($data['activities'] as $activity) {
      $vars = Array(
        'name' => $activity['wf_name'],
        'description' => $activity['wf_description'],
        'type' => $activity['wf_type'],
        'lastModif' => $activity['wf_lastModif'],
        'isInteractive' => $activity['wf_is_interactive'],
        'isAutoRouted' => $activity['wf_is_autorouted']
      );    
      $actname=$am->_normalize_name($activity['wf_name']);
      
      $actid = $am->replace_activity($pid,0,$vars);
      $fp = fopen(GALAXIA_PROCESSES.SEP."$wf_procname".SEP."code".SEP."activities".SEP."$actname".'.php',"w");
      fwrite($fp,$activity['code']);
      fclose($fp);
      if($activity['isInteractive']=='y') {
        $fp = fopen(GALAXIA_PROCESSES.SEP."$wf_procname".SEP."code".SEP."templates".SEP."$actname".'.tpl',"w");
        fwrite($fp,$activity['template']);
        fclose($fp);
      }
      $actids[$activity['name']] = $am->_get_activity_id_by_name($pid,$activity['wf_name']);
      $actname = $am->_normalize_name($activity['wf_name']);
      $now = date("U");

      foreach($activity['wf_roles'] as $role) {
        $vars = Array(
          'name' => $role,
          'description' => $role,
          'lastModif' => $now,
        );
        if(!$rm->role_name_exists($pid,$role)) {
          $rid=$rm->replace_role($pid,0,$vars);
        } else {
          $rid = $rm->get_role_id($pid,$role);
        }
        if($actid && $rid) {
          $am->add_activity_role($actid,$rid);
        }
      }
    }
    foreach($data['transitions'] as $tran) {
      $am->add_transition($pid,$actids[$tran['from']],$actids[$tran['to']]);  
    }
    // FIXME: recompile activities seems to be needed here
    foreach ($actids as $name => $actid) {
      $am->compile_activity($pid,$actid);
    }
    // create a graph for the new process
    $am->build_process_graph($pid);
    unset($am);
    unset($rm);
    $msg = sprintf(tra('Process %s %s imported'),$proc_info['wf_name'],$proc_info['wf_version']);
    $this->notify_all(2,$msg);
  }

  /*!
   Creates a new process based on an existing process
   changing the process version. By default the process
   is created as an unactive process and the version is
   by default a minor version of the process.
   */
  ///\todo copy process activities and so     
  function new_process_version($pId, $minor=true)
  {
    $oldpid = $pId;
    $proc_info = $this->get_process($pId);
    $name = $proc_info['wf_name'];
    if(!$proc_info) return false;

    // Now update the version
    $version = $this->_new_version($proc_info['wf_version'],$minor);
    while($this->getOne("select count(*) from ".GALAXIA_TABLE_PREFIX."processes where wf_name='$name' and wf_version='$version'")) {
      $version = $this->_new_version($version,$minor);
    }
    // Make new versions unactive
    $proc_info['wf_version'] = $version;
    $proc_info['wf_is_active'] = 'n';
    // create a new process, but don't create start/end activities
    $pid = $this->replace_process(0, $proc_info, false);

    //Since we are copying a process we should copy
    //the old directory structure to the new directory
    $oldname = $proc_info['wf_normalized_name'];
    $newname = $this->_get_normalized_name($pid);
    $this->_rec_copy(GALAXIA_PROCESSES.SEP."$oldname".SEP.'code',GALAXIA_PROCESSES.SEP."$newname".SEP.'code');

    // And here copy all the activities & so
    $am = new ActivityManager($this->db);
    $query = "select * from ".GALAXIA_TABLE_PREFIX."activities where wf_p_id=$oldpid";
    $result = $this->query($query);
    $newaid = array();
    while($res = $result->fetchRow()) {    
      $oldaid = $res['wf_activity_id'];
      $newaid[$oldaid] = $am->replace_activity($pid,0,$res, false);
    }
    // create transitions
    $query = "select * from ".GALAXIA_TABLE_PREFIX."transitions where wf_p_id=$oldpid";
    $result = $this->query($query);

	while($res = $result->fetchRow()) { 
      if (empty($newaid[$res['wf_act_from_id']]) || empty($newaid[$res['wf_act_to_id']])) {
        continue;
      }
      $am->add_transition($pid,$newaid[$res['wf_act_from_id']],$newaid[$res['wf_act_to_id']]);
    }
    // create roles
    $rm = new RoleManager($this->db);
    $query = "select * from ".GALAXIA_TABLE_PREFIX."roles where wf_p_id=$oldpid";
    $result = $this->query($query);
    $newrid = array();
    while($res = $result->fetchRow()) {
      if(!$rm->role_name_exists($pid,$res['wf_name'])) {
        $rid=$rm->replace_role($pid,0,$res);
      } else {
        $rid = $rm->get_role_id($pid,$res['wf_name']);
      }
      $newrid[$res['wf_role_id']] = $rid;
    }
    // map users to roles
    if (count($newrid) > 0) {
      $query = "select * from ".GALAXIA_TABLE_PREFIX."user_roles where wf_p_id=$oldpid";
      $result = $this->query($query);
      while($res = $result->fetchRow()) {
        if (empty($newrid[$res['wf_role_id']])) {
          continue;
        }
        $rm->map_user_to_role($pid,$res['wf_user'],$newrid[$res['wf_role_id']], $res['wf_account_type']);
      }
    }
    // add roles to activities
    if (count($newaid) > 0 && count($newrid ) > 0) {
      $query = "select * from ".GALAXIA_TABLE_PREFIX."activity_roles where wf_activity_id in (" . join(', ',array_keys($newaid)) . ")";
      $result = $this->query($query);
      while($res = $result->fetchRow()) {
        if (empty($newaid[$res['wf_activity_id']]) || empty($newrid[$res['wf_role_id']])) {
          continue;
        }
        $am->add_activity_role($newaid[$res['wf_activity_id']],$newrid[$res['wf_role_id']]);
      }
    }

    // create a graph for the new process
    $am->build_process_graph($pid);
    return $pid;
  }
  
  /*!
   This function can be used to check if a process name exists, note that
   this is NOT used by replace_process since that function can be used to
   create new versions of an existing process. The application must use this
   method to ensure that processes have unique names.
  */
  function process_name_exists($name,$version)
  {
    $name = addslashes($this->_normalize_name($name,$version));
    return $this->getOne("select count(*) from ".GALAXIA_TABLE_PREFIX."processes where wf_normalized_name='$name'");
  }
  
  
  /*!
    Gets a process by pId. Fields are returned as an asociative array
  */
  function get_process($pId)
  {
    $query = "select * from ".GALAXIA_TABLE_PREFIX."processes where wf_p_id=$pId";
    $result = $this->query($query);
    if(!$result->numRows()) return false;
    $res = $result->fetchRow();
    return $res;
  }
  
  /*!
   Lists processes (all processes)
  */
  function list_processes($offset,$maxRecords,$sort_mode,$find,$where='')
  {
    $sort_mode = $this->convert_sortmode($sort_mode);
    if($find) {
      $findesc = '%'.$find.'%';
      $mid=" where ((wf_name like ?) or (wf_description like ?))";
      $bindvars = array($findesc,$findesc);
    } else {
      $mid="";
      $bindvars = array();
    }
    if($where) {
      if($mid) {
        $mid.= " and ($where) ";
      } else {
        $mid.= " where ($where) ";
      }
    }
    $query = "select * from ".GALAXIA_TABLE_PREFIX."processes $mid order by $sort_mode";
    $query_cant = "select count(*) from ".GALAXIA_TABLE_PREFIX."processes $mid";
    $result = $this->query($query,$bindvars,$maxRecords,$offset);
    $cant = $this->getOne($query_cant,$bindvars);
    $ret = Array();
    while($res = $result->fetchRow()) {
      $ret[] = $res;
    }
    $retval = Array();
    $retval["data"] = $ret;
    $retval["cant"] = $cant;
    return $retval;
  }
  
  /*!
   Marks a process as an invalid process
  */
  function invalidate_process($pid)
  {
    $query = "update ".GALAXIA_TABLE_PREFIX."processes set wf_is_valid='n' where wf_p_id=$pid";
    $this->query($query);
  }
  
  /*! 
    Removes a process by pId
  */
  function remove_process($pId)
  {
    $this->deactivate_process($pId);
    $name = $this->_get_normalized_name($pId);
    $aM = new ActivityManager($this->db);
    // Remove process activities
    $query = "select wf_activity_id from ".GALAXIA_TABLE_PREFIX."activities where wf_p_id=$pId";
    $result = $this->query($query);
    while($res = $result->fetchRow()) {
      $aM->remove_activity($pId,$res['wf_activity_id']);
    }

    // Remove process roles
    $query = "delete from ".GALAXIA_TABLE_PREFIX."roles where wf_p_id=$pId";
    $this->query($query);
    $query = "delete from ".GALAXIA_TABLE_PREFIX."user_roles where wf_p_id=$pId";
    $this->query($query);

	// Remove process instances
	$query = "delete from ".GALAXIA_TABLE_PREFIX."instances where wf_p_id=$pId";
    $this->query($query);
    
    // Remove the directory structure
    if (!empty($name) && is_dir(GALAXIA_PROCESSES.SEP."$name")) {
      $this->_remove_directory(GALAXIA_PROCESSES.SEP."$name",true);
    }
    if (GALAXIA_TEMPLATES && !empty($name) && is_dir(GALAXIA_TEMPLATES.SEP."$name")) {
      $this->_remove_directory(GALAXIA_TEMPLATES.SEP."$name",true);
    }
    // And finally remove the proc
    $query = "delete from ".GALAXIA_TABLE_PREFIX."processes where wf_p_id=$pId";
    $this->query($query);
    $msg = sprintf(tra('Process %s removed'),$name);
    $this->notify_all(5,$msg);
    
    return true;
  }
  
  /*!
    Updates or inserts a new process in the database, $vars is an asociative
    array containing the fields to update or to insert as needed.
    $pId is the processId
  */
  function replace_process($pId, $vars, $create = true)
  {
    $TABLE_NAME = GALAXIA_TABLE_PREFIX."processes";
    $now = date("U");
    $vars['wf_last_modif']=$now;
    $vars['wf_normalized_name'] = $this->_normalize_name($vars['wf_name'],$vars['wf_version']);        
    foreach($vars as $key=>$value)
    {
      $vars[$key]=addslashes($value);
    }
  
    if($pId) {
      // update mode
      $old_proc = $this->get_process($pId);
      $first = true;
      $query ="update $TABLE_NAME set";
      foreach($vars as $key=>$value) {
        if(!$first) $query.= ',';
        if(!is_numeric($value)||strstr($value,'.')) $value="'".$value."'";
        $query.= " $key=$value ";
        $first = false;
      }
      $query .= " where wf_p_id=$pId ";
      $this->query($query);
      // Note that if the name is being changed then
      // the directory has to be renamed!
      $oldname = $old_proc['wf_normalized_name'];
      $newname = $vars['wf_normalized_name'];
      if ($newname != $oldname) {
          rename(GALAXIA_PROCESSES.SEP."$oldname",GALAXIA_PROCESSES.SEP."$newname");
      }
      $msg = sprintf(tra('Process %s has been updated'),$vars['wf_name']);     
      $this->notify_all(3,$msg);
    } else {
      unset($vars['wf_p_id']);
      // insert mode
      $name = $this->_normalize_name($vars['wf_name'],$vars['wf_version']);
      $this->_create_directory_structure($name);
      $first = true;
      $query = "insert into $TABLE_NAME(";
      foreach(array_keys($vars) as $key) {
        if(!$first) $query.= ','; 
        $query.= "$key";
        $first = false;
      } 
      $query .=") values(";
      $first = true;
      foreach(array_values($vars) as $value) {
        if(!$first) $query.= ','; 
        if(!is_numeric($value)||strstr($value,'.')) $value="'".$value."'";
        $query.= "$value";
        $first = false;
      } 
      $query .=")";
      $this->query($query);
      $pId = $this->getOne("select max(wf_p_id) from $TABLE_NAME where wf_last_modif=$now"); 
      // Now automatically add a start and end activity 
      // unless importing ($create = false)
      if($create) {
        $aM= new ActivityManager($this->db);
        $vars1 = Array(
          'wf_name' => 'start',
          'wf_description' => 'default start activity',
          'wf_type' => 'start',
          'wf_is_interactive' => 'y',
          'wf_is_autorouted' => 'y'
        );
        $vars2 = Array(
          'wf_name' => 'end',
          'wf_description' => 'default end activity',
          'wf_type' => 'end',
          'wf_is_interactive' => 'n',
          'wf_is_autorouted' => 'y'
        );
  
        $aM->replace_activity($pId,0,$vars1);
        $aM->replace_activity($pId,0,$vars2);
      }
    $msg = sprintf(tra('Process %s has been created'),$vars['wf_name']);     
    $this->notify_all(4,$msg);
    }
    // Get the id
    return $pId;
  }
   
  /*!
   \private
   Gets the normalized name of a process by pid
  */
  function _get_normalized_name($pId)
  {
    $info = $this->get_process($pId);
    return $info['wf_normalized_name'];
  }
   
  /*!
   \private
   Normalizes a process name
  */
  function _normalize_name($name, $version)
  {
    $name = $name.'_'.$version;
    $name = str_replace(" ","_",$name);
    $name = preg_replace("/[^0-9A-Za-z\_]/",'',$name);
    return $name;
  }
   
  /*!
   \private
   Generates a new minor version number
  */
  function _new_version($version,$minor=true)
  {
    $parts = explode('.',$version);
    if($minor) {
      $parts[count($parts)-1]++;
    } else {
      $parts[0]++;
      for ($i = 1; $i < count($parts); $i++) {
        $parts[$i] = 0;
      }
    }
    return implode('.',$parts);
  }
   
  /*!
   \private
   Creates directory structure for process
  */
  function _create_directory_structure($name)
  {
    // Create in processes a directory with this name
    mkdir(GALAXIA_PROCESSES.SEP."$name",0770);
    mkdir(GALAXIA_PROCESSES.SEP."$name".SEP."graph",0770);
    mkdir(GALAXIA_PROCESSES.SEP."$name".SEP."code",0770);
    mkdir(GALAXIA_PROCESSES.SEP."$name".SEP."compiled",0770);
    mkdir(GALAXIA_PROCESSES.SEP."$name".SEP."code".SEP."activities",0770);
    mkdir(GALAXIA_PROCESSES.SEP."$name".SEP."code".SEP."templates",0770);
    if (GALAXIA_TEMPLATES) {
      mkdir(GALAXIA_TEMPLATES.SEP."$name",0770);
    }
    // Create shared file
    $fp = fopen(GALAXIA_PROCESSES.SEP."$name".SEP."code".SEP."shared.php","w");
    fwrite($fp,'<'.'?'.'php'."\n".'?'.'>');
    fclose($fp);
  }
   
  /*!
   \private
   Removes a directory recursively
  */
  function _remove_directory($dir,$rec=false)
  {
    // Prevent a disaster
    if(trim($dir) == SEP || trim($dir)=='.' || trim($dir)=='templates' || trim($dir)=='templates'.SEP) return false;
    $h = opendir($dir);
    while(($file = readdir($h)) != false) {
      if(is_file($dir.SEP.$file)) {
        @unlink($dir.SEP.$file);
      } else {
        if($rec && $file != '.' && $file != '..') {
          $this->_remove_directory($dir.SEP.$file, true);
        }
      }
    }
    closedir($h);   
    @rmdir($dir);
    @unlink($dir);
  }

  function _rec_copy($dir1,$dir2)
  {
    @mkdir($dir2,0777);
    $h = opendir($dir1);
    while(($file = readdir($h)) !== false) {
      if(is_file($dir1.SEP.$file)) {
        copy($dir1.SEP.$file,$dir2.SEP.$file);
      } else {
        if($file != '.' && $file != '..') {
          $this->_rec_copy($dir1.SEP.$file, $dir2.SEP.$file);
        }
      }
    }
    closedir($h);   
  }

  function _start_element_handler($parser,$element,$attribs)
  {
    $aux=Array('name'=>$element,
               'data'=>'',
               'parent' => $this->current,
               'children'=>Array());
    $i = count($this->tree);           
    $this->tree[$i] = $aux;

    $this->tree[$this->current]['children'][]=$i;
    $this->current=$i;
  }


  function _end_element_handler($parser,$element)
  {
    //when a tag ends put text
    $this->tree[$this->current]['data']=$this->buffer;           
    $this->buffer='';
    $this->current=$this->tree[$this->current]['parent'];
  }


  function _data_handler($parser,$data)
  {
    $this->buffer.=$data;
  }

  //! return an associative array with all config items for the given processId
  /*!
  This getConfigValues differs from the Process->getConfigValues because the parameter here
  id just the processId. All config items are returned as a function result. This function
  get the items defined in process_config table for this process. In fact this admin function bypass
  the process behaviour and is just showing you the basic content of the table.
  */
  function getConfigValues($pId)
  {
    $query = "select * from ".GALAXIA_TABLE_PREFIX."process_config where wf_p_id=?";
    $result = $this->query($query, array($pId));
    $result_array=array();
    while($res = $result->fetchRow())
    {
      if ($res['wf_config_value_int']==null)
      {
        $result_array[$res['wf_config_name']] = $res['wf_config_value'];
      }
      else
      {
        $result_array[$res['wf_config_name']] = $res['wf_config_value_int'];
      }
    }
    return $result_array;
  }
  
  //! call a process object to save his new config values
  /*!
  This setConfigValues takes a process Id as first argument and simply call this process's setConfigValues
  function. We let the process define the better way to store the data given as second arg.
  */
  function setConfigValues($pId, &$config_array)
  {
    //Warning: this means you have to include the Process.php from the API
    $this->Process =& new Process($this->db);
    $this->Process->getProcess($pId);
    $this->Process->setConfigValues($config_array);
    unset ($this->Process);
  }

}


?>
