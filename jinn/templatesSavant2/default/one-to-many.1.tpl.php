<!--<select name="<?=$this->input_name?>" onchange="document.frm.O2MXXX<?=$this->fprops[name]?>" value="document.frm.<?=$this->input_name?>.options[document.frm.'<?=$this->input_name?>.selectedIndex].text">-->

   <?php if($this->readonly):?>
   <span style="font-style:italic"><?=$this->related_value?></span>
   <?php else:?>
   <select name="<?=$this->input_name?>" onchange="document.frm.O2MXXX<?=$this->fprops[name]?>">
	  <option value=""></option>
	  <?php foreach($this->related_fields as $rfields):?>
	  <?php 
		 if(!$rfields[name])
		 {
			$rfields[name]=$rfields[value];
		 }

		 if($rfields[value]==$this->value)
		 {
			$selected='selected="selected"'; 
		 }
		 else
		 {
			$selected=''; 
		 }
	  ?>
	  <option value="<?=$rfields[value]?>" <?=$selected?> ><?=$rfields[name]?></option>
	  <?php endforeach?>

   </select> <!--(<?=lang('real value')?>: <?=$this->value?>)-->
   <!-- TODO <a href=""><?=lang('Add');?></a>-->
   <?php endif?>

   <!--<input type="hidden" name="O2MXXX<?=$this->fprops[name]?>" value="<?=$this->related_fields_keyed[$this->value]?>" />-->

