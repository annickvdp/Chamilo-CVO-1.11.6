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




$script='CLASS'; 
//--------------------------------------------------------------------------------------------------
//add tutor to courses -- standard teacher should already be added, extra teachers (from wisa contactpersonen) should also be added through class user sync, but this will ensure that they have teacher status.

function user2course_add()
{global $mysql;
 $courseobj=new CourseManager();
 $msg_arr=array();
 // $sql='SELECT t2.user_id, t3.code AS course FROM mssql_class_tutor AS t1 INNER JOIN user AS t2 ON t1.username = t2.username INNER JOIN course AS t3 ON t1.classname = t3.code WHERE t2.user_id NOT IN (SELECT user_id FROM course_rel_user WHERE t2.status=1 AND course_code=t3.code) ORDER BY t3.code;'; //filter with status=1 only users that are teachers
  $sql = 'SELECT t2.user_id, t1.classname as course
	FROM chamilo2015.mssql_class AS t1
	inner JOIN chamilo2015.user AS t2 ON ( t1.username = t2.username )
	Left JOIN chamilo2015.course_rel_user AS t3 ON ( t2.user_id = t3.user_id
	AND t1.classname = t3.course_code ) 
	INNER JOIN course AS t4 ON (t1.classname = t4.code) 
	
	where (t3.status is NULL)
	ORDER BY t3.course_code
	';

  
 $rcs=mysqli_query($mysql, $sql)or die(mysqli_error()) ;
 $counter=0;
  
 
 while($row = mysqli_fetch_array($rcs))
 {
  //print_r($row);
   	 
  $user_id=(int)$row['user_id'];
  $course_id=(string)($row['course']);
  $addres=$courseobj->subscribe_user($user_id, $course_id, 5); //1=coursemanager, 5=student
  /*  public static function subscribe_user(
        $user_id,
        $course_code,
        $status = STUDENT,
        $session_id = 0,
        $userCourseCategoryId = 0
    )*/
  
  if(!addres)
  {synclog_add('ERROR', 'STUDENT SUBSCRIBE', 'STUDENT with user_id "'.$user_id.'" failed to subscribe in course "'.$course_id.'"',$script);
  }
  else
  {$msg_arr[$course_id]=$msg_arr[$course_id]+1;
   //make sure course role is set so we know user was added from sync script
     $sql = 'update chamilo2015.course_rel_user set role = "Cursist" where course_code = "'. $course_id .'" AND user_id = '. $user_id .' ';
    $res=mysqli_query($mysql, $sql)or die(mysqli_error()) ;
  }
 }
 foreach($msg_arr as $course=>$counter)
 {synclog_add('INFO', 'STUDENT SUBSCRIBE', $counter.' students subscribed in course "'.$course.'"',$script);
 }
}
//--------------------------------------------------------------------------------------------------
//delete students from course //teachers added as students by teachers can stay in class
function user2course_del()
{global $mysql;
 $courseobj=new CourseManager();
 $msg_arr=array();
  //$sql='SELECT t1.user_id, t1.course_code AS course, t2.username FROM course_rel_user AS t1 LEFT JOIN user AS t2 ON t1.user_id=t2.user_id WHERE t1.status=5 AND CONCAT(t2.lastname,\' \', t2.firstname) NOT IN (SELECT tutor_name FROM course WHERE code=t1.course_code) AND t2.username NOT IN (SELECT t3.username FROM mssql_class_tutor AS t3 INNER JOIN course AS t4 ON t3.classname=t4.code WHERE t3.classname=t1.course_code);';
   
 
 $sql=" SELECT t1.user_id, t1.course_code AS course, t2.username FROM course_rel_user AS t1 INNER JOIN user AS t2 ON t1.user_id=t2.user_id LEFT JOIN  mssql_class AS t3  ON (t3.classname=t1.course_code and t3.username = t2.username)  where  t1.status=5 and  t3.username is NULL and t1.course_code NOT LIKE '' AND t1.course_code NOT LIKE '%test%' AND t1.course_code NOT LIKE '%EDU%' AND t2.username not like '%cursist%'  AND t2.username not like '%leerkracht%' AND t2.status<>1  ";
 

 $rcs=mysqli_query($mysql, $sql);
 $counter=0;
 while($row = mysqli_fetch_array($rcs))
 {
  $user_id=(int)$row['user_id'];
  $course_id=(string)($row['course']);
  $delres=$courseobj->unsubscribe_user($user_id, $course_id);
  if(!addres)
  {synclog_add('ERROR', 'STUDENT UNSUBSCRIBE', 'Student with user_id "'.$user_id.'" failed to unsubscribe in course "'.$course_id.'"',$script);
  }
  else
  {$msg_arr[$course_id]=$msg_arr[$course_id]+1;
  }
 }
 foreach($msg_arr as $course=>$counter)
 {synclog_add('INFO', 'STUDENT UNSUBSCRIBE', $counter.' students unsubscribed in course "'.$course.'"',$script);
 }
}
//--------------------------------------------------------------------------------------------------
?>

