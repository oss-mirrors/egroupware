<?
/*****FORUM CONFIG***********

This file controls all the forum settings. As well provide the SQL
to create the tables that this forum uses.

*/
class forumConfig {

//Edit the lines below to configure your forum....
var $DBhost="localhost";  //your internet address of the mySQL database your going to use.
var $DBusername="root";
var $DBpassword="";

var $DBname="vegasforum"; //name of database
var $headerpath="/home/httpd/html/portal/vegas/pageheaderforum.php";  // this is the path I used.  It inserts this HTML file at the top of your forum
var $footerpath="/home/httpd/html/portal/vegas/pagefooter.php";       // this is the path I used.  It inserts this HTML file at the Bottom of your forum
var $forummainpath="/home/httpd/html/portal/vegas/forummain.php";  //example path

var $forumfile="index.php";  // leave this, unless your going to name your forum something else besides forum.php
var $forumadminfile="forumadmin.php";  // leave this, unless your going to name your forum admin page something else besides forumadmin.php
var $fontsizemain="-1";
var $fontfacemain="Arial";
var $forumwidth=500;
var $forumname="Johnny_Vegas Discussion Forum";  //Name of your Forum
var $forumnum=1;  //this must be a different number for each forum you have.

//color of the background in these table data cells.  You have to change the text color in your body tag to change font color
var $authorcolor="#333333";  
var $subjectcolor="#333333";
var $datecolor="#333333";
var $messagecolor="#666666";
var $messageborder=0;
var $messagepadding=5;

//images used for the next and back buttons.  Either use mine... or get your own!  hehe
var $nextonimg="http://nerd.teraflops.com/portal/vegas/next.gif";
var $nextoffimg="http://nerd.teraflops.com/portal/vegas/next_na.gif";
var $backonimg="http://nerd.teraflops.com/portal/vegas/back.gif";
var $backoffimg="http://nerd.teraflops.com/portal/vegas/back_na.gif";
var $adminicon="http://nerd.teraflops.com/portal/vegas/admin_icon.gif";

//Error messages.  Define your own for fun and profit.
var $authorError="What are you trying to pull?  You left the author field empty.  Sheeeeit.";
var $subjectError="If you would be so kind as to fill out the subject field, we'd all appreciate it.";

//Instatiate adminuser array, which is defined in your forum admin script.. define these below at bottom of page
var $adminuser;

//Customization params..
var $htmlallowed=1;  //1 for true, 0 for false.  Currently this doesn't do anything. Sorry!
var $maxthreads=20; //max number of threads per page


/***************************************************************************
 Here are the mySQL commands you can run from the command line to create the 
tables that are needed by this forum.....YOU MUST DO THIS YOURSELF!!

Also you only need to create these tables once, no matter how many forums you have.


CREATE TABLE forumbodies (
  id int(10) DEFAULT '0' NOT NULL,
  body blob NOT NULL,
  PRIMARY KEY (id),
  KEY id (id)
);
CREATE TABLE forumthreads (
  id int(10) DEFAULT '0' NOT NULL auto_increment,
  date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
  mainthread int(10) DEFAULT '0' NOT NULL,
  parent int(10) DEFAULT '0' NOT NULL,
  author char(50) DEFAULT '' NOT NULL,
  subject char(50) DEFAULT '' NOT NULL,
  host char(50),
  forum int(4) DEFAULT '0' NOT NULL,
  PRIMARY KEY (id),
  KEY id (id),
  KEY date (date),
  KEY mainthread (mainthread),
  KEY parent (parent),
  KEY author (author),
  KEY subject (subject),
  KEY forum (forum)
);

END OF SQL TABLE CREATION COMMANDS
****************************************************************************/

}

$config=new forumConfig();

mysql_connect($config->DBhost,$config->DBusername,$config->DBpassword); //leave this line alone!


//Set your Admin users here, I added an example user called "superuser".  Use the same format to add more users...
//sure it isn't pretty, but I didn't want to use the database for this part...
$config->adminuser['superuser']="sfa2222";

?>