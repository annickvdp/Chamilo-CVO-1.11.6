<?
//--------------------------------------------------------------------------------------------------
//define include path (must be absolute for scheduled tasks)
//--------------------------------------------------------------------------------------------------
//$incpath=dirname(realpath('.')).'/main/inc/';
//$incpath='C:/Inetpub/wwwroot/Dokeos/main/inc/';
//--------------------------------------------------------------------------------------------------
//include files
//--------------------------------------------------------------------------------------------------
//require_once($incpath.'global.inc.php');
require_once('inc.sync.php');
require_once($incpath.'lib/main_api.lib.php');
require_once($incpath.'lib/course.lib.php');
//--------------------------------------------------------------------------------------------------
//define objects
//--------------------------------------------------------------------------------------------------
//$dbs=mysqli_connect($_configuration['db_host'], $_configuration['db_user'], $_configuration['db_password'], $_configuration['main_database']);
//--------------------------------------------------------------------------------------------------
/*function synclog_add_tutor($type, $action, $descr)
{global $dbs;
 $sql='INSERT INTO mssql_synclog (`type`, `script`, `action`, `description`, `datetime`) VALUES ("'.strtoupper($type).'", "CLASS","'.strtoupper($action).'" ,"'.htmlentities($descr).'", NOW());';
 $result=mysqli_query($dbs, $sql);
 return $result;
}
*/




$script='TUTOR'; 
//--------------------------------------------------------------------------------------------------
//add tutor to courses -- standard teacher should already be added, extra teachers (from wisa contactpersonen) should also be added through class user sync, but this will ensure that they have teacher status.

function tutor2course_add()
{global $mysql;
 global $script; 
 $courseobj=new CourseManager();
 $msg_arr=array();
 // $sql='SELECT t2.user_id, t3.code AS course FROM mssql_class_tutor AS t1 INNER JOIN user AS t2 ON t1.username = t2.username INNER JOIN course AS t3 ON t1.classname = t3.code WHERE t2.user_id NOT IN (SELECT user_id FROM course_rel_user WHERE t2.status=1 AND course_code=t3.code) ORDER BY t3.code;'; //filter with status=1 only users that are teachers
  $sql = 'SELECT t2.user_id, t1.classname as course
	FROM chamilo2015.mssql_class_tutor AS t1
	inner JOIN chamilo2015.user AS t2 ON ( t1.username = t2.username )
	Left JOIN chamilo2015.course_rel_user AS t3 ON ( t2.user_id = t3.user_id
	AND t1.classname = t3.course_code ) 
	INNER JOIN course AS t4 ON (t1.classname = t4.code) 
	
	where t2.status = 1 and (t3.status <> 1 or t3.status is NULL)
	ORDER BY t3.course_code
	';

  
 $rcs=mysqli_query($mysql, $sql)or die(mysqli_error()) ;
 $counter=0;
  
 
 while($row = mysqli_fetch_array($rcs))
 {
  //print_r($row);
   	 
  $user_id=(int)$row['user_id'];
  $course_id=(string)($row['course']);
  $addres=$courseobj->subscribe_user($user_id, $course_id, 1); //1=coursemanager, 5=student
  if(!addres)
  {synclog_add('ERROR', 'TUTOR SUBSCRIBE', 'Tutor with user_id "'.$user_id.'" failed to subscribe in course "'.$course_id.'"',$script);
  }
  else
  {$msg_arr[$course_id]=$msg_arr[$course_id]+1;
  }
 }
 foreach($msg_arr as $course=>$counter)
 {synclog_add('INFO', 'TUTOR SUBSCRIBE', $counter.' tutors subscribed in course "'.$course.'"',$script);
 }
}
//--------------------------------------------------------------------------------------------------
//delete tutor to course
function tutor2course_del()
{global $dbs;
global $script; 
 $courseobj=new CourseManager();
 $msg_arr=array();
 $sql='SELECT t1.user_id, t1.course_code AS course, t2.username FROM course_rel_user AS t1 LEFT JOIN user AS t2 ON t1.user_id=t2.user_id WHERE t1.status=1 AND CONCAT(t2.lastname,\' \', t2.firstname) NOT IN (SELECT tutor_name FROM course WHERE code=t1.course_code) AND t2.username NOT IN (SELECT t3.username FROM mssql_class_tutor AS t3 INNER JOIN course AS t4 ON t3.classname=t4.code WHERE t3.classname=t1.course_code);';
 $rcs=mysqli_query($dbs, $sql);
 $counter=0;
 while($row = mysqli_fetch_array($rcs))
 {
  $user_id=(int)$row['user_id'];
  $course_id=(string)($row['course']);
  $delres=$courseobj->unsubscribe_user($user_id, $course_id);
  if(!addres)
  {synclog_add('ERROR', 'TUTOR UNSUBSCRIBE', 'Tutor with user_id "'.$user_id.'" failed to unsubscribe in course "'.$course_id.'"',$script);
  }
  else
  {$msg_arr[$course_id]=$msg_arr[$course_id]+1;
  }
 }
 foreach($msg_arr as $course=>$counter)
 {synclog_add('INFO', 'TUTOR UNSUBSCRIBE', $counter.' tutors unsubscribed in course "'.$course.'"',$script);
 }
}
//--------------------------------------------------------------------------------------------------





function prof_checkup()
{global $mysql;
global $script; 
  
 // $sql='SELECT t2.user_id, t3.code AS course FROM mssql_class_tutor AS t1 INNER JOIN user AS t2 ON t1.username = t2.username INNER JOIN course AS t3 ON t1.classname = t3.code WHERE t2.user_id NOT IN (SELECT user_id FROM course_rel_user WHERE t2.status=1 AND course_code=t3.code) ORDER BY t3.code;'; //filter with status=1 only users that are teachers
  $sql = 'SELECT t1.username_professor as prof1, t2.username as prof2, t1.code as course_code, t2.user_id as user_id, t3.role
	FROM chamilo2015.mssql_course AS t1
	inner JOIN chamilo2015.user AS t2 ON ( t1.username_professor = t2.username )
	Left JOIN chamilo2015.course_rel_user AS t3 ON ( t2.user_id = t3.user_id AND t1.code = t3.course_code ) 
	 
	where t3.role<>"Professor"
	ORDER BY t3.course_code
	';

   
 $rcs=mysqli_query($mysql, $sql)or die(mysqli_error()) ;
 $counter=0;
  
 
 
 while($row = mysqli_fetch_array($rcs))
 {
	 
	// var_dump($row);
 //exit;
  //print_r($row);
  //there's a change in prof!!!!!!
  //remove all profs roles from course
  $sql = 'update course_rel_user set role = "" where course_code = "'. $row['course_code'] .'" and status = 1';
  $rcs=mysqli_query($mysql, $sql)or die(mysqli_error()) ; 
  
   //set current prof role to prof (they should already be added)
  $sql = 'update course_rel_user set role = "Professor" where course_code = "'. $row['course_code'] .'" AND user_id = '. $row['user_id'] .' ';
  $rcs=mysqli_query($mysql, $sql)or die(mysqli_error()) ;  
  
  if(!$rcs){
	  synclog_add('ERROR', 'PROF CHECKUP', 'Prof with user_id '.$row['user_id'].' failed to update role in course "'.$row['course_code'].'"',$script);
  }
  else
  {
	$counter++;  
  }
 }
 if($counter>0){
	 synclog_add('INFO', 'PROF CHECKUP', $counter.' Prof status updates ',$script);
 }
}










?>

