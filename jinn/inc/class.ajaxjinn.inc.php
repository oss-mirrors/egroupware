<?php
   include_once(PHPGW_INCLUDE_ROOT.'/jinn/inc/class.uijinn.inc.php');

   /**
   * xajaxjinn 
   * 
   * @uses uijinn
   * @package 
   * @version $Id$
   * @copyright Lingewoud B.V.
   * @author Pim Snel <pim-AT-lingewoud-DOT-nl> 
   * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
   */
   class ajaxjinn extends uijinn
   {
	  function ajaxjinn()
	  {
		 //fixme now we're getting problems because we also need bouser
		 $this->bo = CreateObject('jinn.boadmin');
		 parent::uijinn();
	  }

	  function toggleFieldEnabled($field,$toggleTo,$object_id)
	  {
		 $response = new xajaxResponse();

		 if($toggleTo=='enable')
		 {
			$value=1;				
		 }
		 elseif($toggleTo=='disable')
		 {
			$value=0;				

		 }
		 else
		 {
			return $response->getXML();
		 }

		 $data[]=array(
			'name'=>'field_name',
			'value'=>$field
		 );

		 $data[]=array(
			'name'=>'field_parent_object',
			'value'=>$object_id
		 );

		 $data[] = array(
			'name' => 'field_enabled', 
			'value' => $value
		 );

		 $where_string="field_parent_object='$object_id' AND  field_name='$field_name'";
		 $status = $this->bo->so->update_phpgw_data('egw_jinn_obj_fields',$data,'','',$where_string,true);
		 $this->bo->set_site_version_info($this->bo->site['site_id']);
		 //	 return $response->addAlert($where_string);
		 return $response->getXML();
	  }
	  function toggleFieldFormVisible($field,$toggleTo,$object_id)
	  {
		 $response = new xajaxResponse();

		 if($toggleTo=='visible')
		 {
			$value=1;				
		 }
		 elseif($toggleTo=='hide')
		 {
			$value=0;				

		 }
		 else
		 {
			return $response->getXML();
		 }

		 $data[]=array(
			'name'=>'field_name',
			'value'=>$field
		 );

		 $data[]=array(
			'name'=>'field_parent_object',
			'value'=>$object_id
		 );

		 $data[] = array(
			'name' => 'form_visibility', 
			'value' => $value
		 );
		 $where_string="field_parent_object='$object_id' AND  field_name='$field'";
		 $status = $this->bo->so->update_phpgw_data('egw_jinn_obj_fields',$data,'','',$where_string,true);
		 $this->bo->set_site_version_info($this->bo->site['site_id']);

		 return $response->getXML();
	  }




	  function toggleFieldListVisible($field,$toggleTo,$object_id)
	  {
		 $response = new xajaxResponse();

		 if($toggleTo=='visible')
		 {
			$value=1;				
		 }
		 elseif($toggleTo=='hide')
		 {
			$value=0;				
		 }
		 else
		 {
			return $response->getXML();
		 }

		 $data[]=array(
			'name'=>'field_name',
			'value'=>$field
		 );

		 $data[]=array(
			'name'=>'field_parent_object',
			'value'=>$object_id
		 );

		 $data[] = array(
			'name' => 'list_visibility', 
			'value' => $value
		 );

		 $where_string="field_parent_object='$object_id' AND  field_name='$field'";
		 $status = $this->bo->so->update_phpgw_data('egw_jinn_obj_fields',$data,'','',$where_string,true);
		 $this->bo->set_site_version_info($this->bo->site['site_id']);

		 return $response->getXML();

	  }

	  //fixme temp workarround: merge admin and user
	  function switchBoUser()
	  {
		 $this->bo = CreateObject('jinn.bouser');
		 parent::uijinn();
	  }

	  function editSingleField($cell,$wherestring_enc,$field,$object_id)
	  {
		 $this->switchBoUser();
		 $this->db_ftypes = CreateObject('jinn.dbfieldtypes');
		 $response = new xajaxResponse();
		 $wherestring=base64_decode($wherestring_enc);
		 
		 $all_fields_conf_arr = $this->bo->so->mk_field_conf_arr_for_obj($object_id);
		 $fields_meta_data= $this->bo->so->site_table_metadata($this->bo->session['site_id'],$this->bo->site_object['table_name']);
		 foreach ($fields_meta_data as $fprops)
		 {	
			if($fprops['name']==$field)
			{
			   $ftype=$this->db_ftypes->complete_resolve($fprops);
			   $field_conf_arr=$all_fields_conf_arr[$fprops['name']];
			   break;
			}
		 }

		 $values=$this->bo->so->get_record_values($this->bo->session['site_id'],$this->bo->site_object['table_name'],'','','','','name','',$field,$wherestring);
		 $plug_arr = $this->bo->plug->call_plugin_fi('FLDXXX'.$cell, $values[0][$field], $ftype, $field_conf_arr, $attr_arr,false);

		 $miniform=$plug_arr['html'].'<!--<br/><input type="button" onclick="callLiveFieldSave()" value="'.lang('save').'" />-->';
		 
		 $response->addAssign($cell, "innerHTML", $miniform);
		 
		 return $response->getXML();
	  }

	  function saveSingleField($value,$wherestring_enc,$field,$object_id)
	  {
		 $this->switchBoUser();
		 $this->db_ftypes = CreateObject('jinn.dbfieldtypes');

		 $response = new xajaxResponse();
		 $wherestring=base64_decode($wherestring_enc);
		 
		 $status=$this->bo->single_recordfield_update($wherestring,$field,$value,$this->bo->site_object);
		 
		 return $response->getXML();
	  }

	  function readSingleField($cell,$wherestring_enc,$field,$object_id)
	  {
		 $this->switchBoUser();
		 $this->db_ftypes = CreateObject('jinn.dbfieldtypes');
		 $response = new xajaxResponse();
		 $wherestring=base64_decode($wherestring_enc);

		 $all_fields_conf_arr = $this->bo->so->mk_field_conf_arr_for_obj($object_id);
		 $fields_meta_data= $this->bo->so->site_table_metadata($this->bo->session['site_id'],$this->bo->site_object['table_name']);
		 foreach ($fields_meta_data as $fprops)
		 {	
			if($fprops['name']==$field)
			{
			   $ftype=$this->db_ftypes->complete_resolve($fprops);
			   $field_conf_arr=$all_fields_conf_arr[$fprops['name']];
			   break;
			}
		 }

		 $values=$this->bo->so->get_record_values($this->bo->session['site_id'],$this->bo->site_object['table_name'],'','','','','name','',$field,$wherestring);
		 $recordvalue=$this->bo->plug->call_plugin_bv('FLDXXX'.$cell, $values[0][$field], $wherestring, $field_conf_arr, $ftype);

		 $response->addAssign($cell, "innerHTML", $recordvalue);
		 return $response->getXML();
	  }
   }
