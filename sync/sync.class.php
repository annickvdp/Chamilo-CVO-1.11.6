<?
//--------------------------------------------------------------------------------------------------
//include files
//--------------------------------------------------------------------------------------------------
require_once('inc.sync.php');
require_once('func.class.php');
//--------------------------------------------------------------------------------------------------
//main program
//--------------------------------------------------------------------------------------------------
 
//  print '<br/><br/>...activate...'; user_activate();

 print '<br/><br/>...del...<br/>';  user2course_del(); 
 
 print '<br/><br/>...add...'; user2course_add();
 
//user_deactivate(); 

 
echo('<br><br>User2class proces terminated');
//--------------------------------------------------------------------------------------------------
?>
