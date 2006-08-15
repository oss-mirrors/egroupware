<script>
   function change_selection(total_options,val,id,dest_fld)
   {
		 var prefix = dest_fld + '_img_';
		 for(i=1;i<=total_options;i++)
		 {
			   document.getElementById(prefix+i).style.border="2px solid white";
			   document.getElementById(prefix+i).style.padding="1px";
		 }
		 document.getElementById(prefix+id).style.border="2px dashed red";
		 document.getElementById(prefix+id).style.padding="1px";
		 document.getElementById(dest_fld).value=val;
   }

</script>
<div style="margin-top:5px;border:solid 1px black ;display:table; background-color:white;width:auto;padding:5px;">
   <input type="hidden" name="<?=$this->field_name?>" value="<?=$this->value?>" id ="<?=$this->field_name?>"/>
   <?php $i=1;?>
   <?php if(is_array($this->config)):?>
   <?php foreach($this->config as $config):?>
   <?if($this->value == $config['option_value']):?>
   <img style="padding:1px;margin:10px;border:2px dashed red" src="<?php echo($this->upload_url);?>/visual_selection/<?php echo($config['imgfile']);?>" onclick="change_selection(<?php echo(count($this->config));?>,'<?php echo($config['option_value']);?>',<?php echo($i);?>,'<?=$this->field_name?>');" id="<?=$this->field_name?>_img_<?php echo($i);?>">

   <?else:?>
   <img style="padding:1px;margin:10px;border:2px dashed white" src="<?php echo($this->upload_url);?>/visual_selection/<?php echo($config['imgfile']);?>" onclick="change_selection(<?php echo(count($this->config));?>,'<?php echo($config['option_value']);?>',<?php echo($i);?>,'<?=$this->field_name?>');" id="<?=$this->field_name?>_img_<?php echo($i);?>">
   <?endif?>
   <?php $i++;?>
   <?php endforeach?>
   <?endif?>
</div>
