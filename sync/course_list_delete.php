

<?php

 exit;

//includes
include_once('../main/inc/global.inc.php');
include_once('../sync/func.course.php');



$dbs=mysqli_connect($_configuration['db_host'], $_configuration['db_user'], $_configuration['db_password'], $_configuration['main_database']);

if(!$dbs){exit;}


//maximize available resources since this can be time and memory consuming
if(function_exists('ini_set'))
{
	ini_set('memory_limit','256M');
	ini_set('max_execution_time',1800);
	print('resources extended <br>');
}


$sql = "SELECT * FROM dokeos_main.delete_list "; 


$result = mysql_query($sql) or die(mysql_error());

while($row = mysql_fetch_array($result)){
		$course = $row['code'];
		$course = addslashes(trim($course));
		$value = course_del($course);
		if($value){$message[] = $course.', Course deleted<br />';}
		else{$message[] = '<font style="color:red">'.$course.', course not found<br /></font>';
		}
			 
	 
}

?>
<strong>Resultaat van de cursusactivatie/-deactivatie:<br /><br /></strong>
 
<? foreach($message as $msg){echo( $msg  );} ?>
 
 

