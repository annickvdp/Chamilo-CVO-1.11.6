<?
//--------------------------------------------------------------------------------------------------
//define include path (must be absolute for scheduled tasks)
//--------------------------------------------------------------------------------------------------
//$incpath=dirname(realpath('.')).'/main/inc/';
$incpath='C:/Inetpub/wwwroot/Dokeos/main/inc/';
//--------------------------------------------------------------------------------------------------
//include files
//--------------------------------------------------------------------------------------------------
include_once($incpath.'global.inc.php');
include_once($incpath.'conf/add_course.conf.php');
include_once($incpath.'lib/main_api.lib.php');
include_once($incpath.'lib/add_course.lib.inc.php');
include_once($incpath.'lib/course.lib.php');
include_once($incpath.'lib/classmanager.lib.php');
//--------------------------------------------------------------------------------------------------
//define objects
//--------------------------------------------------------------------------------------------------
$dbs=mysqli_connect($_configuration['db_host'], $_configuration['db_user'], $_configuration['db_password'], $_configuration['main_database']);
//--------------------------------------------------------------------------------------------------
function synclog_add($type, $action, $descr)
{global $dbs;
 $sql='INSERT INTO mssql_synclog (`type`, `script`, `action`, `description`, `datetime`) VALUES ("'.strtoupper($type).'", "COURSE", "'.strtoupper($action).'" ,"'.htmlentities($descr).'", NOW());';
 $result=mysqli_query($dbs, $sql);
 return $result;
}
//--------------------------------------------------------------------------------------------------
//add course
function course_add($code_course='')
{global $dbs;
 $course=new CourseManager();
 $list_dbs=array();
 // SHAUN MOD - first activate course if exists
  $sql='UPDATE course set visibility = 1 where course.code = "'.$code_course.'" ;';
  $res =mysqli_query($dbs, $sql);
 if(mysqli_affected_rows($dbs)>0){return true;}
 
 $sql ='SHOW DATABASES LIKE "dokeos_course_%";';
 $res =mysqli_query($dbs, $sql);
 while($row = mysqli_fetch_array($res))
 {array_push($list_dbs, $row[0]);
 }
 $sql='SELECT t1.*, t1.db_prefix, t3.user_id AS professor_id, CONCAT(t3.lastname, \' \', t3.firstname) AS profname FROM mssql_course AS t1 '
     .'LEFT JOIN course AS t2 ON t1.code=t2.code INNER JOIN user as t3 ON t1.username_professor=t3.username '
	 .'WHERE t2.code is null';
 if($code_course!=''){$sql.=' AND t1.code="'.$code_course.'";';}
 else{$sql.=';';}
 
 $rcs=mysqli_query($dbs, $sql);
 while($row = mysqli_fetch_array($rcs))
 {
  $class_code=strtoupper($row['code']);
  $new_prof =$row['profname'];
  $expir_date=strtotime('+1 day', strtotime($row['expiration_dt'])); //add 1 day to the expiration date
  
  // courses created need to be visible
  $row['visibility']= 1;
  
  $check=true;
  if(array_search($row['db_prefix'].$class_code, $list_dbs)){$check=false;}
  $path=$_configuration['root_sys'].$_configuration['course_folder'];
  if(is_dir($path.$class_code)){$check=false;}  
  
  if($check==false){synclog_add('ERROR', 'COURSE ADD', 'Course directory or course database with code "'.$class_code.'" already exists'); break;}
   
  create_course($class_code, $row['title'], $new_prof, $row['category_code'], $row['course_language'], $row['professor_id'], $row['db_prefix'], $expir_date, $row['disk_quota'], $row['visibility'], $row['subscribe'], $row['unsubscribe']);

  $sqlparam='UPDATE course SET disk_quota='.$row['disk_quota'].', visibility='.$row['visibility'].', subscribe='.$row['subscribe'].', unsubscribe='.$row['unsubscribe'].' WHERE code="'.$class_code.'";';
  $courseres=mysqli_query($dbs, $sqlparam);

  if(!$courseres)
  {synclog_add('ERROR', 'COURSE ADD', 'Course with code "'.$class_code.'" creation failed');
  }
  else
  {synclog_add('INFO', 'COURSE ADD', 'Course with code "'.$class_code.'" created');

   $classres=mysqli_query($dbs, 'SELECT COUNT(id) AS num FROM class WHERE name="'.$class_code.'";');
   $classrow=mysqli_fetch_array($classres);
   if($classrow['num']>0)
   {
    class_subscribe($class_code, $class_code);
    return true;
   }
   else
   {
    if(class_add($class_code)==true){class_subscribe($class_code, $class_code);
	
	//add teachers to course user table for active courses where current head teacher has not yet been added
	// this is also the code for course_leerkracht fix
	$query = "INSERT IGNORE INTO dokeos_main.course_rel_user (course_code,user_id,status,role,group_id,tutor_id,sort,user_course_cat)SELECT t1.code AS course_code,t2.user_id, 1 AS
	status , 'professor' AS role, 0 AS group_id, 0 AS tutor_id, 0 AS sort, 0 AS user_course_cat
	FROM dokeos_main.mssql_course AS t1
	LEFT JOIN dokeos_main.user AS t2 ON ( t1.username_professor = t2.username )
	LEFT JOIN dokeos_main.course AS t3 ON ( t1.code = t3.code )
	LEFT JOIN dokeos_main.course_rel_user AS t4 ON ( t1.code = t4.course_code AND t2.user_id = t4.user_id)
	WHERE
	t1.code NOT LIKE '%V%'
	AND t1.code NOT LIKE '%D%'
	AND t1.code NOT LIKE '%R%'
	AND t1.code NOT LIKE '%S%'
	 
	AND t3.code IS NOT NULL   AND t4.course_code IS NULL ; ";
	 
	$res=mysqli_query($dbs, $query);
	}
	return true;
   }
  }
 }
 return false;
}
//--------------------------------------------------------------------------------------------------
//create class
function class_add($class_code='')
{global $dbs;
 $class=new ClassManager();

 $sql='SELECT id FROM dokeos_main.class WHERE name="'.$class_code.'";';
 $rcs=mysqli_query($dbs, $sql);
 if(mysqli_num_rows($rcs1)==0 && $class_code!='')
 {$classres=$class->create_class($class_code);
  if(!$classres)
  {synclog_add('ERROR', 'CLASS ADD', 'Class with code "'.$class_code.'" creation failed');
   return false;
  }
  else
  {synclog_add('INFO', 'CLASS ADD', 'Class with code "'.$class_code.'" created');
   return true;
  }
 } 
 else
 {synclog_add('ERROR', 'CLASS ADD', 'Class with code "'.$class_code.'" already exist');
  return false;
 }
}
//--------------------------------------------------------------------------------------------------
//subscribe class to course
function class_subscribe($class_code='', $course_code='')
{global $dbs;
 $class=new ClassManager();
 $sql1='SELECT id FROM dokeos_main.class WHERE name="'.$class_code.'";';
 $rcs1=mysqli_query($dbs, $sql1);
 $sql2='SELECT code FROM dokeos_main.course WHERE code="'.$course_code.'";';
 $rcs2=mysqli_query($dbs, $sql2);
 if(mysqli_num_rows($rcs1)>0 && mysqli_num_rows($rcs2)>0)
 {$row=mysqli_fetch_array($rcs1);
  $class->subscribe_to_course($row['id'], $course_code);
  synclog_add('INFO', 'CLASS SUBSCRIBE', 'Class with code "'.$class_code.'" subscribed to course "'.$course_code.'"');
  return true;
 }
 synclog_add('ERROR', 'CLASS SUBSCRIBE', 'Class with code "'.$class_code.'" failed to subscribe to course "'.$course_code.'"');
 return false;
}
//--------------------------------------------------------------------------------------------------
//notify theachers that their course is expired more than ... ($time)
function course_del_notify($time='1 month') // or 11 months
{global $dbs;
 $course=new CourseManager();
 $class=new ClassManager();
 $sql='SELECT t1.code, t1.title, t1.expiration_date, t2.firstname, t2.lastname, t2.email FROM course AS t1 INNER JOIN user AS t2 ON CONCAT(t2.lastname, \' \', t2.firstname)=t1.tutor_name WHERE DATE(t1.expiration_date)="'.date("Y-m-d", strtotime('-'.$time)).'" ORDER BY t2.email;';
 $rcs=mysqli_query($dbs, $sql);
 $emailold='';
 $to      ='';
 $message ='';
 $subject = 'Automatische verwijdering cursus(sen)';
 $headers = 'From: '.api_get_setting('siteName').' <'.api_get_setting('emailAdministrator').'>' . "\r\n".
            'Reply-To: '.api_get_setting('emailAdministrator') . "\r\n";
 while($row=mysqli_fetch_array($rcs))
 {$to=$row['email'];
  if($emailold<>'' && $emailold<>$to)
  {api_send_mail($to, $subject, $message, $headers);
   $emailold=$to;
   $message='';

  }
  else
  {$message.='Uw cursus met cursuscode "'.$row['code'].'" en cursustitel "'.$row['title'].'" is al 1 maand vervallen.'.
             'Indien U uw cursus wilt bijhouden, neem dan een backup. Na 1 jaar wordt de cursus automatisch verwijderd !'."\n".
  	 	     'Datum van geplande verwijdering: '.date("Y-m-d", strtotime('+1 year', strtotime($row['expiration_date'])))."\n\n";
  }
 }
 if($to<>''){api_send_mail($to, $subject, $message, $headers);}
}
//--------------------------------------------------------------------------------------------------
//delete course
function course_del($course_code='')
{global $dbs;
 $course=new CourseManager();
 $class=new ClassManager();
 $sql='SELECT code FROM course WHERE code="'.$course_code.'";';
 $rcs=mysqli_query($dbs, $sql);
 if(mysqli_num_rows($rcs)>0)
 {
  $courseres=$course->delete_course($course_code);
  synclog_add('INFO', 'COURSE DELETE', 'Course with code "'.$course_code.'" deleted');
  $sql2='SELECT id FROM class WHERE name="'.$course_code.'";';
  $rcs2=mysqli_query($dbs, $sql2);
  $row2=mysqli_fetch_array($rcs2);
  $class->delete_class($row2['id']);
  synclog_add('INFO', 'CLASS DELETE', 'Class with name "'.$course_code.'" and id "'.$row2['id'].'" deleted');
  return true;




 }
 else
 {return false;
 }
}
//--------------------------------------------------------------------------------------------------
//deactivate array of courses
function course_deactivate($course_code='')
{global $dbs;
 $course=new CourseManager();
 $class=new ClassManager();
 $sql='SELECT code FROM course WHERE code="'.$course_code.'";';
 $rcs=mysqli_query($dbs, $sql);
 if(mysqli_num_rows($rcs)>0)
 {
  $sql='UPDATE course set visibility = "0" where code="'.$course_code.'";';
  $result=mysqli_query($dbs, $sql);
  
  synclog_add('INFO', 'COURSE DEACTIVATE', 'Course with code "'.$course_code.'" deactivated');
  //$sql2='SELECT id FROM class WHERE name="'.$course_code.'";';
  //$rcs2=mysqli_query($dbs, $sql2);
  //$row2=mysqli_fetch_array($rcs2);
  //$class->delete_class($row2['id']);
  //synclog_add('INFO', 'CLASS DELETE', 'Class with name "'.$course_code.'" and id "'.$row2['id'].'" deleted');
  return true;
  



 }
 else
 {return false;
 }
}
//--------------------------------------------------------------------------------------------------
//delete courses that not exist in the mssql database and are expired more than ... ($time)
function course_del_time($time='1 year') // or 2 years
{global $dbs;
 $course=new CourseManager();
 $class=new ClassManager();
 //delete courses that not exist in mssql_course and are more than 1 year expired
 $sql='SELECT t2.code FROM mssql_course AS t1 RIGHT JOIN course AS t2 ON t1.code=t2.code WHERE t1.code is null AND t2.expiration_date < "'.date("Y-m-d H:m:s", strtotime('-'.$time)).'";';
 
 $rcs=mysqli_query($dbs, $sql);
 while($row=mysqli_fetch_array($rcs))
 {
  $class_code=strtoupper($row['code']);
  $courseres=$course->delete_course($class_code);
  synclog_add('INFO', 'COURSE DELETE', 'Course with code "'.$class_code.'" deleted');
  $sql2='SELECT id FROM class WHERE name="'.$class_code.'";';
  $rcs2=mysqli_query($dbs, $sql2);
  $row2=mysqli_fetch_array($rcs2);
  $class->delete_class($row2['id']);
  synclog_add('INFO', 'CLASS DELETE', 'Class with name "'.$class_code.'" and id "'.$row2['id'].'" deleted');
 }
}
//--------------------------------------------------------------------------------------------------
//sync only existing courses with the mssql database
function course_sync()
{global $dbs;
 $course=new CourseManager();
 $sql='SELECT t1.code, t1.expiration_dt, t2.expiration_date, t1.title, t1.category_code, t2.tutor_name, t3.user_id, CONCAT(t3.lastname, \' \', t3.firstname) AS profname, t3.user_id FROM mssql_course AS t1 INNER JOIN course AS t2 ON t1.code=t2.code INNER JOIN user AS t3 ON t1.username_professor=t3.username WHERE t1.title<>t2.title OR t3.username<>(SELECT username FROM user AS t4 WHERE CONCAT(t4.lastname, \' \', t4.firstname)=t2.tutor_name AND t4.status=1 ORDER BY `t1`.`title`  DESC LIMIT 1) OR t1.category_code <>t2.category_code OR t1.expiration_dt <>t2.expiration_date;';
 
 $rcs=mysqli_query($dbs, $sql);
 while($row=mysqli_fetch_array($rcs))
 {$new_prof=$row['profname'];
  $old_prof =strtoupper($row['tutor_name']);
  $update_or_insert='insert';
  $sqlteachers='SELECT t1.user_id, CONCAT( t2.lastname, \' \', t2.firstname ) AS profname FROM course_rel_user AS t1 INNER JOIN user AS t2 ON t1.user_id = t2.user_id WHERE t1.course_code="'.$row['code'].'" AND t1.status=1;';
  $resteachers=mysqli_query($dbs, $sqlteachers);
  while($rowteachers=mysqli_fetch_array($resteachers))
  {if($rowteachers['profname']==$old_prof){mysqli_query($dbs, 'DELETE FROM course_rel_user WHERE user_id='.$rowteachers['user_id'].' AND course_code="'.$row['code'].'" AND status=1;');}
   if($rowteachers['profname']==$new_prof){$teacherid=$rowteachers['user_id']; $update_or_insert='update';}
  }
  if($update_or_insert=='update')
  {
  $sql3='UPDATE course_rel_user SET status=1, role="professor" WHERE user_id='.$teacherid.' AND course_code="'.$row['code'].'";';
  }	
  else
  {
  $sql3='INSERT INTO course_rel_user (user_id, course_code, status, role, sort) VALUES ('.$row['user_id'].', "'.$row['code'].'", 1, "Professor", 0);';
  }
  $res=mysqli_query($dbs, $sql3);
  if(!$res){synclog_add('ERROR', 'COURSE RENAME', 'Course with code "'.$row['code'].'" failed to change tutor name with user_id "'.$row['user_id'].'"');}
  else
  {synclog_add('INFO', 'COURSE RENAME', 'Course with code "'.$row['code'].'" changed tutor name to user with user_id "'.$row['user_id'].'"');}

   $sql2='UPDATE course SET title="'.$row['title'].'", category_code="'.$row['category_code'].'", tutor_name="'.$new_prof.'", expiration_date="'.$row['expiration_dt'].'" WHERE code="'.$row['code'].'";';
   
   $res=mysqli_query($dbs, $sql2);
   if(!$res){synclog_add('ERROR', 'COURSE RENAME', 'Course with code "'.$row['code'].'" failed to update');}
   else{synclog_add('INFO', 'COURSE RENAME', 'Course with code "'.$row['code'].'" updated');}
 }
  // make sure course administrators are on the course tutor list
  $sql3a='INSERT IGNORE INTO mssql_class_tutor
		SELECT username_professor AS username, code AS classname
		FROM `mssql_course` 
		WHERE mssql_course.code LIKE "%d%"
		OR mssql_course.code LIKE "%v%"
		OR mssql_course.code LIKE "%s%" 
	; ';
 
    $res=mysqli_query($dbs, $sql3a);
  
  
  // Remove teachers from docenten cursussen if not in mssql list
   $sql3='DELETE b
    FROM course_rel_user AS b
    INNER JOIN user AS c ON ( b.user_id = c.user_id ) 
    INNER JOIN course AS a ON ( b.course_code = a.code ) 
    LEFT OUTER JOIN mssql_class_tutor AS d ON ( c.username = d.username
    AND b.course_code = d.classname ) 
    WHERE b.course_code LIKE "%D%"
    AND ( 
    b.course_code NOT LIKE "%dg%"
    AND b.course_code NOT LIKE "%s%"
    AND b.course_code NOT LIKE "%vg%"
    AND b.course_code NOT LIKE "%temp%"
    AND b.course_code NOT LIKE "%shaun%"
    AND b.course_code NOT LIKE "%enquete%"
    AND b.course_code NOT LIKE "%survey%"
    AND b.course_code NOT LIKE "%enq%"
    )
    AND classname IS NULL 
    ; ';
	
    $res=mysqli_query($dbs, $sql3);
    if(!$res){synclog_add('ERROR', 'COURSE USER DELETE', 'Failed to remove unauthorised teachers from teacher courses');}
    else{
		if(mysqli_affected_rows($dbs)>0){
		 synclog_add('INFO', 'COURSE USER DELETE', 'Removed '.mysqli_affected_rows($dbs).' unauthorised teachers from teacher courses');
		}
	}
 
}
//--------------------------------------------------------------------------------------------------
//sync only existing classes with the mssql database
function class_sync()
{global $dbs;
 $class=new ClassManager();
 $createres=mysqli_query($dbs, 'SELECT t1.code, t2.name FROM course AS t1 LEFT JOIN class AS t2 ON t1.code=t2.name WHERE t2.name IS NULL;');
 while($createrow=mysqli_fetch_array($createres))
 {$class_code=strtoupper($createrow['code']);
  $classres=$class->create_class($class_code);
  synclog_add('INFO', 'CLASS ADD', 'Class with code "'.$class_code.'" created');
 }
 $classsql='SELECT t1.code, t2.course_code FROM course AS t1 LEFT JOIN course_rel_class AS t2 ON t1.code=t2.course_code WHERE t2.course_code IS NULL;';
 $classres=mysqli_query($dbs, $classsql);
 while($classrow=mysqli_fetch_array($classres))
 {$linkres=mysqli_query($dbs, 'SELECT id FROM class WHERE name="'.$classrow['code'].'" LIMIT 0,1;');
  if(mysqli_num_rows($linkres)==1)
  {$class_id=mysqli_fetch_array($linkres);
   $class->subscribe_to_course($class_id['id'], $classrow['code']);
   synclog_add('INFO', 'CLASS SUBSCRIBE', 'Class with id "'.$classrow['id'].'" subscribed to course "'.$classrow['code'].'"');
  }
 }
}
//--------------------------------------------------------------------------------------------------
//echo('<br>Course proces terminated');
//--------------------------------------------------------------------------------------------------
?>