<?php

//print 'hello world';
 exit;

require_once('../main/inc/global.inc.php');
require_once('func.course.php');
 require_once('func.class.php'); print '- I mean functions imported';
 
 
// note courses are activated and users are synced, but teachers don't have access until after regular automated sync occurs 

$dbs=mysqli_connect($_configuration['db_host'], $_configuration['db_user'], $_configuration['db_password'], $_configuration['main_database']);

if(!$dbs){print('<br/>no db connection <br>'); exit;}
else{print('<br/>connected <br>');}
 
//maximize available resources since this can be time and memory consuming
if(function_exists('ini_set'))
{
	ini_set('memory_limit','256M');
	ini_set('max_execution_time',1800);
	print('resources extended <br>');
}


// this query shows all results
/*
$sql = "SELECT t1.*, t2.`code`, t3.`code` as check FROM chamilo2015.activate_list as t1 LEFT JOIN mssql_course as t2 on (t1.title = t2.title) LEFT JOIN chamilo2015.course as t3 on (t2.code = t3.code) where 1 "; 
*/

// this is only those needing activation
$sql = "SELECT t1.*, t2.`code`, t3.`code` as `check` FROM chamilo2015.activate_list as t1 LEFT JOIN mssql_course as t2 on (t1.title = t2.title) LEFT JOIN chamilo2015.course as t3 on (t2.code = t3.code) where t3.code IS NULL;";
//if both code and check are null it could be that the course title changed - or that the info is not being passed from mssql
 
$activate_list_result = mysql_query($sql) or die('<br/><br/>'.mysql_error());

$activate_list = array();
while($row = mysql_fetch_array($activate_list_result)){
	 array_push($activate_list,$row);
}

//requires array with values 'title','code' - these courses are then activated, classes are created, users are added

 

course_list_activate($activate_list);

/*
//compare mysql tables chamilo2015.mssql_course with chamilo2015.course if not found then activate

$sql = "SELECT `title` FROM `mssql_course` WHERE `category_code` = '{$current_category}' 

AND `code` NOT LIKE '%VG%'
AND `code` NOT LIKE '%D%'
AND `code` NOT LIKE '%R%'
 AND `code` NOT LIKE '%S%'
"; 


$result = mysql_query($sql);

while($row = mysql_fetch_array($result)){
		
		
}
*/

?>