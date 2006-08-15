<div>
   <input type="hidden" value="" id="<?=$this->prefix.'_IMG_EDIT_'.$this->stripped_name.$this->i?>" name="<?=$this->prefix.'_IMG_EDIT_'.$this->stripped_name.$this->i?>">
   <fieldset class="fmfieldset" class="overflow:hidden;">
	  <legend><?=lang('File %1',$this->i)?></legend>
	  <div style="padding:3px;padding-top:0px;overflow:hidden;">
		 <?=$this->showfile?>
	  </div>
	  <div style="padding-top:5px;">
		 <input type="button"  class="egwbutton" value="<?=lang('delete')?>" onClick="onDelete('<?=$this->prefix?>', '<?=$this->stripped_name?>', <?=$this->i?>);" src="jinn/plugins/db_fields_plugins/__filemanager/popups/ImageManager/edit_trash.gif">
		 <input onClick="onBrowseServer('<?=$this->prefix?>', '<?=$this->stripped_name?>', <?=$this->i?>,'<?=$this->curr_obj_id?>');" type="button"  class="egwbutton" value="<?=lang('browse')?>" name="<?=$this->prefix.'_IMG_EDIT_BUTTON_'.$this->stripped_name.$this->i?>">
	  </div>
   </fieldset>
</div>
