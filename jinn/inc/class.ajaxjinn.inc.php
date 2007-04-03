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


   }
