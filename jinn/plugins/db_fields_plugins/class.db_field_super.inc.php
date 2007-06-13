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
   }
?>
