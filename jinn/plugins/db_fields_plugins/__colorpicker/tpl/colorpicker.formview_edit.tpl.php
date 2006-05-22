<?=$this->javascript;?>
<style>
   #colbox<?=$this->fieldname?>
   {
		 border:solid 1px black;
		 width:20px;
		 height:20px;
		 background-color:<?=$this->value?>

   }
   #colbox<?=$this->fieldname?>:hover
   {
		 border:dashed 1px black;

   }
   #colbox<?=$this->fieldname?>:active
   {
		 border:solid 1px red;

   }

</style>
<div style="white-space: nowrap;"><div id="colbox<?=$this->fieldname?>" onclick="openColorPicker('<?=$this->fieldname?>','<?=str_replace('#','',$this->value)?>','<?=$this->stripped_name?>');" style="float:left;"></div><input name="<?=$this->fieldname?>" id="<?=$this->fieldname?>" type="hidden" value="<?=$this->value?>" />&nbsp;&nbsp;<?=lang('click to select a new color')?></div>
<div style="clear:both;"></div>
