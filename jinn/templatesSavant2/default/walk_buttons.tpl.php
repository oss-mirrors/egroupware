<!--<div style="margin:5px 0px 5px 0px;">-->
   <?php if(is_array($this->walkbuttons_arr)):?>
   <?php foreach($this->walkbuttons_arr as $walkbutton):?>
   <input style="background-color:#abd3ab" type="button" name="<?=($walkbutton['eventlabel']?$walkbutton['eventlabel']:$walkbutton['name'])?>" value="<?=($walkbutton['eventlabel']?$walkbutton['eventlabel']:$walkbutton['name'])?>" onclick="parent.window.open('<?=$walkbutton['walklistevent_link']?>&selvalues=' +returnSelectedCheckbox() , 'genobjoptions', 'width=580,height=500,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no')" />
   <?php endforeach?>
   <?php endif?>
   <!--</div>-->
