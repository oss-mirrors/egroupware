<?php
   class db_field_super
   {
	  var $plug_root = '';
	  var $local_bo;

	  function initPluginObject()
	  {
		 $this->tplsav2 = CreateObject('phpgwapi.tplsavant2');
		 $this->tplsav2->addPath('template',$this->plug_root.'/tpl');
	  }

	  function getCurrentObjectArray()
	  {
		 return $this->local_bo->site_object; 
	  }

	  function getCurrentObjectId()
	  {
		 return $this->local_bo->site_object['object_id']; 
	  }

	  function getWhereStringEnc()
	  {
		 return $this->local_bo->where_string_encoded;
	  }
	  function getWhereString()
	  {
		 return $this->local_bo->where_string;
	  }

	  function getFieldPrefix($field_name)
	  {
		 return substr($field_name,0,6);
		 }

	  function makeSerializedRecordFieldInfo($field_name,$config)
	  {
		 return base64_encode(serialize( array(
			'field_name'=> $field_name,
			'where_string'=> $this->getWhereString(),
			'object_id' => $this->getCurrentObjectId(),
			'field_config'=> $config,
			'prefix'=> $this->getFieldPrefix($field_name)
		 )));
	  }


   }
?>
