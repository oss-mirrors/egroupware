<?php
  /**************************************************************************\
  * phpGroupWare - Stock Quotes                                              *
  * http://www.phpgroupware.org                                              *
  *  This file is based on PStocks v.0.1                                     *
  *  http://www.dansteinman.com/php/pstocks/                                 *
  *  Copyright (C) 1999 Dan Steinman (dan@dansteinman.com)                   *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  // return content of a url as a string array
  

  function http_fetch($url,$post,$port,$proxy)
  {
     global $phpgw;
     
     return $phpgw->network->gethttpsocketfile($url);
  }
  
  // Rename this is something better
  function return_html($quotes)
  {
     $return_html = '<table cellspacing="1" cellpadding="0" border="0" bgcolor="black"><tr><td>'
                  . '<table cellspacing="1" cellpadding="2" border="0" bgcolor="white">'
                  . '<tr><td><b>Name</b></td><td><b>Symbol</b></td><td><b>Price</b></td><td>'
                  . '<b>$&nbsp;Change</b></td><td><b>%&nbsp;Change</b></td><td><b>Date</b></td><td>'
                  . '<b>Time</b></td></tr>';
    
    for ($i=0;$i<count($quotes);$i++) {
        $q      = $quotes[$i];
        $symbol = $q['symbol'];
        $name   = $q['name'];
        $price0 = $q['price0']; // today's price
        $price1 = $q['price1'];
        $price2 = $q['price2'];
        $dollarchange  = $q['dchange'];
        $percentchange = $q['pchange'];
        $date   = $q['date'];
        $time   = $q['time'];
        $volume = $q['volume'];
        
        if ($dollarchange < 0) {
           $color = "red";
         } else {
            $color = "green";
        }
        
        $return_html .= "<tr><td>$name</td><td>$symbol</td><td>$price0</td><td><font color=\""
                      . "$color\">$dollarchange</font></td><td><font color=\"$color\">$percentchange"
                      . "</font></td><td>$date</td><td>$time</td></tr>";
    }
    $return_html .= "</table></td></tr></table></table>";

    return $return_html;
  }

  function get_quotes($stocklist)
  {
     if (! $stocklist) {
        return array();
     }
     while (list($symbol,$name) = each($stocklist)) {
        $symbollist[] = $symbol;
        $symbol = rawurldecode($symbol);
        $symbolstr .= $symbol;
        if ($i++<count($stocklist)-1) {
           $symbolstr .= "+";
        }
     }

     $regexp_stocks = "/(" . implode("|",$symbollist) . ")/";

     $url = "http://finance.yahoo.com/d/quotes.csv?f=sl1d1t1c1ohgv&e=.csv&s=$symbolstr";
     $lines = http_fetch($url,false,80,'');

     $quotes = array();
     $i = 0;
     while ($line = each($lines)) {
         $line = $lines[$i];
         if (preg_match($regexp_stocks,$line)) {
            $line = ereg_replace('"','',$line);
            list($symbol,$price0,$date,$time,$dchange,$price1,$price2) = split(',',$line);
               
            if ($price1>0 && $dchange!=0) {
               $pchange = round(10000*($dchange)/$price1)/100;
            } else {
               $pchange = 0;
            }
   
            if ($pchange>0) {
               $pchange = "+" . $pchange;
            }
   
            $name = $stocklist[$symbol];
            if (! $name) {
               $name = $symbol;
            }
                               
            $quotes[] = array("symbol"  => $symbol,
                              "price0"  => $price0,
                              "date"    => $date,
                              "time"    => $time,
                              "dchange" => $dchange,
                              "price1"  => $price1,
                              "price2"  => $price2,
                              "pchange" => $pchange,
                              "name"    => $name
                             );
            }
         $i++;
     }
     return $quotes;
  }

  function get_savedstocks()
  {
     global $phpgw_info, $phpgw;

     // If they don't have any stocks in there, give them something to look at
     if (! count($phpgw_info["user"]["preferences"]["stocks"])) {
        $phpgw_info["user"]["preferences"]["stocks"]["LNUX"] = "VA%20Linux";
        $phpgw_info["user"]["preferences"]["stocks"]["RHAT"] = "RedHat";
     }
     
     while ($stock = each($phpgw_info["user"]["preferences"]["stocks"])) {
        if (rawurldecode($stock[0]) != "enabled") {
           $symbol = rawurldecode($stock[0]);
           $name   = rawurldecode($stock[1]);
           if ($symbol) {
              if (! $name) {
                 $name = $symbol;
              }
              $stocklist[$symbol] = $name;
           }
        }
     }
     return $stocklist;
  }

  function return_quotes()
  {
     $stocklist = get_savedstocks();
     $quotes    = get_quotes($stocklist);
     return return_html($quotes);
  }
?>
