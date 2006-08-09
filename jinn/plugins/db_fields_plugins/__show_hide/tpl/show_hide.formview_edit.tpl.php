<select  name="<?php echo($this->fieldname);?>" 
   onchange="
   jinnShowHideFields(<?php echo($this->fieldname);?>,'Hide',<?php echo($this->hide_sel);?>);
   jinnShowHideFields(<?php echo($this->fieldname);?>,'Show',<?php echo($this->show_sel);?>);
   ">
   <?php foreach($this->options as $option):?>
   <option value="<?php echo($option[Value]);?>" <?if($this->value == $option[Value]){echo("selected=selected");}?>><?php echo($option[Label]);?></option>
   <?php endforeach?>
</select>
<script>
   window.onload=function(){jinnShowHideFields(document.frm.<?php echo($this->fieldname);?>,'Show',<?=($this->show_sel?$this->show_sel:"''");?>);jinnShowHideFields(document.frm.<?php echo($this->fieldname);?>,'Hide',<?=($this->hide_sel?$this->hide_sel:"''");?>)};
</script>
