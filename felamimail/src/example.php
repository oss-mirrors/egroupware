<?PHP

// This is just an example how you could use the mime_email_class
// This includes the MIME class of course and an pop3 abstraction
// class, i dont know if the pop3 section is correct, but thats not
// the point in this example

include("../include/class.pop3.inc");
include("../include/mime_email.class.php");

// create new pop3 object
$myPOP3 = new pop3();

// login to pop3server
$myPOP3->login("username", "pw");

// get an email (msg_array) with some ID, dont ask me where you get it :))
// this email is in an array (each line in the email has an array index)
$msg_array = $myPOP3->get($id);

// create a new MIME EMAIL object
$md = new mime_email();

// submit the raw email (the message array)
$md->set_emaildata($msg_array);

// decode the message and you will get back an object of Type mime_email_class
$myEmail = $md->go_decode();

// if you want to dump the data in order to see if the attributes were filled corectly
// use $md->dump() or $md->dump(true) if you want to see also the raw email in front of
// it all
$md->dump(true);


// now you must work with the object, later on i will submit also an object description
// but for now you must just read out the attributes at mime_email.class.php 
// this file has two classes, look out for them!
  
# close the POP session
$myPOP3->quit();

?>