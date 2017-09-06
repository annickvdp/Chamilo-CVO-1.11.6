

<?php

exit;

//this activates all tko courses

//includes
include_once('../main/inc/global.inc.php');
include_once('../sync/func.course.php');



// Make a MySQL Connection
$dbs=mysqli_connect($_configuration['db_host'], $_configuration['db_user'], $_configuration['db_password'], $_configuration['main_database']);
if(!$dbs){exit;}
//else{print('connected <br>');}



//maximize available resources since this can be time and memory consuming
if(function_exists('ini_set'))
{
	ini_set('memory_limit','256M');
	ini_set('max_execution_time',1800);
	print('resources extended <br>');
}



// DEFINE CATEGORIES TO ACTIVATE
$categories = array('ASO', 'AV', 'AV2', 'AV3', 'BB', 'GRAF', 'HT2', 'JGZ', 'KANT', 'KIND', 'INF');
//$categories = array('TEST1', 'TEST2');


foreach($categories as $current_category){
print($current_category);
print('<br>');print('<br>');
//compare mysql tables dokeos_main.mssql_course with dokeos_main.course if not found then activate

$sql = "SELECT `code` FROM `mssql_course` WHERE `category_code` = '{$current_category}' 

AND `code` NOT LIKE '%VG%'
AND `code` NOT LIKE '%D%'
AND `code` NOT LIKE '%R%'
 AND `code` NOT LIKE '%S%'
"; 


$result = mysql_query($sql);

while($row = mysql_fetch_array($result)){
		
		$current_course=$row['code'];
		print($current_course);
		
		$sql2 = "SELECT * FROM `course` WHERE `code` = '{$current_course}'"; 
		
		
		$result2 = mysql_query($sql2);
		$test = mysql_fetch_array($result2);
		print(' - ');print($test['code']);
		
		 if($test['code'] == $current_course){ print(' - active'); }
		else{ print(' - NOT ACTIVEATED YET');
		
		//ACTIVATE COURSE
			$current_course = addslashes(trim($current_course));
			$value = course_add($current_course);
   		 	if($value){print(' - Cursus met cursuscode '.$current_course.' succesvol aangemaakt');}
		
			
		}
		
		 
		print('<br>');
	 
}
	
print('<br>');print('<br>');

}

?>


