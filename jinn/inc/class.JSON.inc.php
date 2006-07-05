<?php
   require_once(PHPGW_SERVER_ROOT.'/jinn/inc/JSON.php');

   class JSON extends Services_JSON 
   {
      function JSON($use = 0)
      {
         $this->use = $use;
      }
   }
