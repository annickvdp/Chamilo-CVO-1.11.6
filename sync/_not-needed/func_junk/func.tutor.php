<?
//--------------------------------------------------------------------------------------------------
//define include path (must be absolute for scheduled tasks)
//--------------------------------------------------------------------------------------------------
//$incpath=dirname(realpath('.')).'/main/inc/';
$incpath='C:/Inetpub/wwwroot/Dokeos/main/inc/';
//--------------------------------------------------------------------------------------------------
//include files
//--------------------------------------------------------------------------------------------------
require_once($incpath.'global.inc.php');
require_once($incpath.'lib/main_api.lib.php');
require_once($incpath.'lib/course.lib.php');
//--------------------------------------------------------------------------------------------------
//define objects
//--------------------------------------------------------------------------------------------------
$dbs=mysqli_connect($_configuration['db_host'], $_configuration['db_user'], $_configuration['db_password'], $_configuration['main_database']);
//--------------------------------------------------------------------------------------------------
function synclog_add_tutor($type, $action, $descr)
{global $dbs;
 $sql='INSERT INTO mssql_synclog (`type`, `script`, `action`, `description`, `datetime`) VALUES ("'.strtoupper($type).'", "CLASS","'.strtoupper($action).'" ,"'.htmlentities($descr).'", NOW());';
 $result=mysqli_query($dbs, $sql);
 return $result;
}
//--------------------------------------------------------------------------------------------------
//add tutor to course
function tutor2course_add()
{global $dbs;
 $courseobj=new CourseManager();
 $msg_arr=array();
 $sql='SELECT t2.user_id, t3.code AS course FROM mssql_class_tutor AS t1 INNER JOIN user AS t2 ON t1.username = t2.username INNER JOIN course AS t3 ON t1.classname = t3.code WHERE t2.user_id NOT IN (SELECT user_id FROM course_rel_user WHERE t2.status=1 AND course_code=t3.code) ORDER BY t3.code;'; //filter with status=1 only users that are teachers
 $rcs=mysqli_query($dbs, $sql);
 $counter=0;
 while($row = mysqli_fetch_array($rcs))
 {
  $user_id=(int)$row['user_id'];
  $course_id=(string)($row['course']);
  $addres=$courseobj->subscribe_user($user_id, $course_id, 1); //1=coursemanager, 5=student
  if(!addres)
  {synclog_add_tutor('ERROR', 'TUTOR SUBSCRIBE', 'Tutor with user_id "'.$user_id.'" failed to subscribe in course "'.$course_id.'"');
  }
  else
  {$msg_arr[$course_id]=$msg_arr[$course_id]+1;
  }
 }
 foreach($msg_arr as $course=>$counter)
 {synclog_add_tutor('INFO', 'TUTOR SUBSCRIBE', $counter.' tutors subscribed in course "'.$course.'"');
 }
}
//--------------------------------------------------------------------------------------------------
//delete tutor to course
function tutor2course_del()
{global $dbs;
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
  {synclog_add_tutor('ERROR', 'TUTOR UNSUBSCRIBE', 'Tutor with user_id "'.$user_id.'" failed to unsubscribe in course "'.$course_id.'"');
  }
  else
  {$msg_arr[$course_id]=$msg_arr[$course_id]+1;
  }
 }
 foreach($msg_arr as $course=>$counter)
 {synclog_add_tutor('INFO', 'TUTOR UNSUBSCRIBE', $counter.' tutors unsubscribed in course "'.$course.'"');
 }
}
//--------------------------------------------------------------------------------------------------
?>

