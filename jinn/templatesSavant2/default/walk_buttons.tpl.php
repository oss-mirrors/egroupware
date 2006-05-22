<div style="margin:5px 0px 5px 0px;">
   <?php if(is_array($this->walkbuttons_arr)):?>
   <?php foreach($this->walkbuttons_arr as $walkbutton):?>
   <input type="button" name="<?=$walkbutton['name']?>" value="<?=$walkbutton['name']?>" onclick="parent.window.open('<?=$this->walkevent_link?>&selvalues=' +returnSelectedCheckbox() , 'genobjoptions', 'width=580,height=500,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no')" />
   <?php endforeach?>
   <?php endif?>
</div>
