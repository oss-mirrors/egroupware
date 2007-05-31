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

		 if($value==1)
		 {
			$enabled='<a href="javascript:void(0);" onclick="toggleFieldEnabled(\''.$field.'\',\'disable\',\''.$object_id.'\')"><img src="'.$GLOBALS['phpgw']->common->image('jinn','fld_enabled').'" alt="" /></a>';
		 }
		 else
		 {
			$enabled='<a href="javascript:void(0);" onclick="toggleFieldEnabled(\''.$field.'\',\'enable\',\''.$object_id.'\')"><img src="'.$GLOBALS['phpgw']->common->image('jinn','fld_disabled').'" alt="" /></a>';
		 }
		 $response->addAssign('panel_fenabled', "innerHTML", $enabled);

		 $where_string="field_parent_object='$object_id' AND  field_name='$field_name'";
		 $status = $this->bo->so->update_phpgw_data('egw_jinn_obj_fields',$data,'','',$where_string,true);
		 $this->bo->set_site_version_info($this->bo->site['site_id']);
		 //	 return $response->addAlert($where_string);
		 return $response->getXML();
	  }

	  /**
	  * getPanelFieldProps recieve all field properties and assign to design panel 
	  * 
	  * @param string $field 
	  * @param string $object_id 
	  * @access public
	  * @return void
	  */
	  function getPanelFieldProps($field,$object_id)
	  {
		 $response = new xajaxResponse();

		 $props=$this->bo->so->get_field_values($object_id,$field);
		 $props_db=$this->bo->so->object_field_metadata($object_id,$field);

		 if($props['field_enabled']==1)
		 {
			$enabled='<a href="javascript:void(0);" onclick="toggleFieldEnabled(\''.$field.'\',\'disable\',\''.$object_id.'\')"><img src="'.$GLOBALS['phpgw']->common->image('jinn','fld_enabled').'" alt="" /></a>';
		 }
		 else
		 {
			$enabled='<a href="javascript:void(0);" onclick="toggleFieldEnabled(\''.$field.'\',\'enable\',\''.$object_id.'\')"><img src="'.$GLOBALS['phpgw']->common->image('jinn','fld_disabled').'" alt="" /></a>';
		 }
		 $response->addAssign('panel_fenabled', "innerHTML", $enabled);

		 if($props['form_visibility']==1)
		 {
			$form_visibility='<a href="javascript:void(0);" onclick="toggleFieldVisProp(\''.$field.'\',\'hide\',\''.$object_id.'\',\'form_visibility\')"><img src="'.$GLOBALS['phpgw']->common->image('jinn','eyevisible').'" alt="" /></a>';
		 }
		 else
		 {
			$form_visibility='<a href="javascript:void(0);" onclick="toggleFieldVisProp(\''.$field.'\',\'visible\',\''.$object_id.'\',\'form_visibility\')"><img src="'.$GLOBALS['phpgw']->common->image('jinn','eyehidden').'" alt="" /></a>';
		 }

		 if($props['list_visibility']==1)
		 {
			$list_visibility='<a href="javascript:void(0);" onclick="toggleFieldVisProp(\''.$field.'\',\'hide\',\''.$object_id.'\',\'list_visibility\')"><img src="'.$GLOBALS['phpgw']->common->image('jinn','eyevisible').'" alt="" /></a>';
		 }
		 else
		 {
			$list_visibility='<a href="javascript:void(0);" onclick="toggleFieldVisProp(\''.$field.'\',\'visible\',\''.$object_id.'\',\'list_visibility\')"><img src="'.$GLOBALS['phpgw']->common->image('jinn','eyehidden').'" alt="" /></a>';
		 }


		 if($props['label_visibility']==1)
		 {
			$label_visibility='<a href="javascript:void(0);" onclick="toggleFieldVisProp(\''.$field.'\',\'hide\',\''.$object_id.'\',\'label_visibility\')"><img src="'.$GLOBALS['phpgw']->common->image('jinn','eyevisible').'" alt="" /></a>';
		 }
		 else
		 {
			$label_visibility='<a href="javascript:void(0);" onclick="toggleFieldVisProp(\''.$field.'\',\'visible\',\''.$object_id.'\',\'label_visibility\')"><img src="'.$GLOBALS['phpgw']->common->image('jinn','eyehidden').'" alt="" /></a>';
		 }
		 
		 if(!empty($props['data_source']))
		 {
			$link_delete_element=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_edit_record.delete_element&object_id='.$object_id);
			$del='<a href="'.$link_delete_element.'&field_name='.$field.'" onclick="return window.confirm(\''.lang('Are you sure you want to delete this element?').'\')"><img src="'.$GLOBALS['egw']->common->image('phpgwapi','close').'" alt="" /></a>';
		 }

		 $response->addAssign('panel_fname', "innerHTML", $props['field_name']);
		 $response->addAssign('panel_jftype', "innerHTML", $props['element_type']);

		 $response->addAssign('panel_flabel', "innerHTML", $props['element_label']);
		 $response->addAssign('panel_fhelpinfo', "innerHTML", $props['field_help_info']);
		 $response->addAssign('panel_forder', "innerHTML", $props['form_listing_order']);

		 $response->addAssign('panel_flabel_visibility', "innerHTML", $label_visibility);
		 $response->addAssign('panel_fform_visibility', "innerHTML", $form_visibility);
		 $response->addAssign('panel_flist_visibility', "innerHTML", $list_visibility);
		 $response->addAssign('panel_fsingle_col', "innerHTML", $props['single_col']);
		 $response->addAssign('panel_ffe_readonly', "innerHTML", $props['fe_readonly']);
		 $response->addAssign('panel_fdata_source', "innerHTML", $props['data_source']);
		 $response->addAssign('panel_del', "innerHTML", $del);

		 $response->addAssign('panel_dbtype', "innerHTML", $props_db['type']);
		 $response->addAssign('panel_dbsize', "innerHTML", $props_db['len']);
		 $response->addAssign('panel_dbflags', "innerHTML", $props_db['flags']);
		 $response->addAssign('panel_dbhasdef', "innerHTML", $props_db['has_default']);
		 $response->addAssign('panel_dbdefval', "innerHTML", $props_db['default']);
		 $response->addAssign('panel_dbbin', "innerHTML", $props_db['binary']);
		 $response->addAssign('panel_dbnotnull', "innerHTML", $props_db['not_null']);

		 //$debug=_debug_array($props_db,false); 
		 //$response->addAssign('panel_debug', "innerHTML", $debug);


		 return $response->getXML();

	  }

	  function toggleFieldVisProp($field,$toggleTo,$object_id,$prop)
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
			'name' => $prop, 
			'value' => $value
		 );

		 if($value==1)
		 {
			$newlink='<a href="javascript:void(0);" onclick="toggleFieldVisProp(\''.$field.'\',\'hide\',\''.$object_id.'\',\''.$prop.'\')"><img src="'.$GLOBALS['phpgw']->common->image('jinn','eyevisible').'" alt="" /></a>';
		 }
		 else
		 {
			$newlink='<a href="javascript:void(0);" onclick="toggleFieldVisProp(\''.$field.'\',\'visible\',\''.$object_id.'\',\''.$prop.'\')"><img src="'.$GLOBALS['phpgw']->common->image('jinn','eyehidden').'" alt="" /></a>';
		 }

		 $response->addAssign('panel_f'.$prop, "innerHTML", $newlink);
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

		 $field_conf_arr=$this->bo->so->get_field_values($object_id,$field);

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

	  function saveReadSingleField($value,$wherestring_enc,$field,$object_id,$cell)
	  {
		 $this->switchBoUser();
		 $this->db_ftypes = CreateObject('jinn.dbfieldtypes');

		 $response = new xajaxResponse();
		 $wherestring=base64_decode($wherestring_enc);

		 $status=$this->bo->single_recordfield_update($wherestring,$field,$value,$this->bo->site_object);

		 $field_conf_arr=$this->bo->so->get_field_values($object_id,$field);
		 $recordvalue=$this->bo->plug->call_plugin_bv('FLDXXX'.$cell, $value, $wherestring, $field_conf_arr, $ftype);

		 $response->addAssign($cell, "innerHTML", $recordvalue);

		 return $response->getXML();
	  }

	  /*function readSingleField($cell,$wherestring_enc,$field,$object_id)
	  {
		 $this->switchBoUser();
		 $this->db_ftypes = CreateObject('jinn.dbfieldtypes');
		 $response = new xajaxResponse();
		 $wherestring=base64_decode($wherestring_enc);
		 
		 $field_conf_arr=$this->bo->so->get_field_values($object_id,$field);

		 $values=$this->bo->so->get_record_values($this->bo->session['site_id'],$this->bo->site_object['table_name'],'','','','','name','',$field,$wherestring);
		 $recordvalue=$this->bo->plug->call_plugin_bv('FLDXXX'.$cell, $values[0][$field], $wherestring, $field_conf_arr, $ftype);

		 $response->addAssign($cell, "innerHTML", $recordvalue);
		 return $response->getXML();
	  }*/
   }
