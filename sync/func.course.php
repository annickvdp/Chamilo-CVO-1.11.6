<?
//--------------------------------------------------------------------------------------------------
//define include path (must be absolute for scheduled tasks)
//--------------------------------------------------------------------------------------------------
//$incpath=dirname(realpath('.')).'/main/inc/';
//$incpath='C:/Inetpub/wwwroot/Dokeos/main/inc/';
//--------------------------------------------------------------------------------------------------
//include files
//--------------------------------------------------------------------------------------------------
//include_once($incpath.'global.inc.php');
require_once('inc.sync.php');
require_once($incpath.'conf/add_course.conf.php');
require_once($incpath.'lib/main_api.lib.php');
require_once($incpath.'lib/add_course.lib.inc.php');
require_once($incpath.'lib/course.lib.php');
require_once($incpath.'lib/classmanager.lib.php');
 require_once('func.class.php');
//--------------------------------------------------------------------------------------------------
//define objects
//--------------------------------------------------------------------------------------------------
// $dbs=mysqli_connect($_configuration['db_host'], $_configuration['db_user'], $_configuration['db_password'], $_configuration['main_database']);
//--------------------------------------------------------------------------------------------------
/*
function synclog_add($type, $action, $descr)
{global $dbs;
 $sql='INSERT INTO mssql_synclog (`type`, `script`, `action`, `description`, `datetime`) VALUES ("'.strtoupper($type).'", "COURSE", "'.strtoupper($action).'" ,"'.htmlentities($descr).'", NOW());';
 $result=mysqli_query($dbs, $sql);
 return $result;
}
*/
$script='COURSE';
//--------------------------------------------------------------------------------------------------
//add course
function course_add($code_course='')
{global $mysql;
global $script; 
 $course=new CourseManager();
 $list_dbs=array();
 // SHAUN MOD - first activate course if exists
  $sql='UPDATE course set visibility = 1 where course.code = "'.$code_course.'" ;';
  $res =mysqli_query($mysql, $sql);
 if(mysqli_affected_rows($mysql)>0){
	 synclog_add('INFO', 'COURSE ADD', 'Course with code "'.$code_course.'" made visible', $script);
	 return true;}
 //this won't work -- everything is in one db now
 //$sql ='SHOW DATABASES LIKE "dokeos_course_%";';
 $sql ='SELECT code FROM course;';
 $res =mysqli_query($mysql, $sql);
 while($row = mysqli_fetch_array($res))
 {array_push($list_dbs, $row[0]);
 }
 $sql='SELECT t1.*, t1.db_prefix, t3.user_id AS professor_id, CONCAT(t3.lastname, " ", t3.firstname) AS profname FROM mssql_course AS t1 '
     .'LEFT JOIN course AS t2 ON t1.code=t2.code INNER JOIN user as t3 ON t1.username_professor=t3.username '
	 .'WHERE t2.code is null';
 if($code_course!=''){$sql.=' AND t1.code="'.$code_course.'";';}
 else{$sql.=';';}
 
 $rcs=mysqli_query($mysql, $sql);
 while($row = mysqli_fetch_array($rcs))
 {
  $class_code=strtoupper($row['code']);
  $new_prof =$row['profname'];
  $expir_date=strtotime('+1 day', strtotime($row['expiration_dt'])); //add 1 day to the expiration date
  
  // courses created need to be visible
  $row['visibility']= 1;
  
  $check=true;
  //if(array_search($row['db_prefix'].$class_code, $list_dbs)){$check=false;}
  if(array_search($class_code, $list_dbs)){$check=false;}
  $path=$_configuration['root_sys'].$_configuration['course_folder'];
  if(is_dir($path.$class_code)){$check=false;}  
  
  if($check==false){synclog_add('ERROR', 'COURSE ADD', 'Course directory or course database with code "'.$class_code.'" already exists', $script); break;}
    
  //$course_info = CourseManager::create_course($course);
  
  // use new method??
  /*
  $wanted_code        = $course_values['wanted_code'];
    //$tutor_name         = $course_values['tutor_name'];
    $category_code      = $course_values['category_code'];
    $title              = $course_values['title'];
    $course_language    = $course_values['course_language'];
    $exemplary_content  = !empty($course_values['exemplary_content']);

    if ($course_validation_feature) {
        $description     = $course_values['description'];
        $objetives       = $course_values['objetives'];
        $target_audience = $course_values['target_audience'];
    }

    if ($wanted_code == '') {
        $wanted_code = generate_course_code(api_substr($title, 0, CourseManager::MAX_COURSE_LENGTH_CODE));
    }

    // Check whether the requested course code has already been occupied.
    if (!$course_validation_feature) {
        $course_code_ok = !CourseManager::course_code_exists($wanted_code);
    } else {
        $course_code_ok = !CourseRequestManager::course_code_exists($wanted_code);
    }

    if ($course_code_ok) {
        if (!$course_validation_feature) {

            $params = array();
            $params['title']                = $title;
            $params['exemplary_content']    = $exemplary_content;
            $params['wanted_code']          = $wanted_code;
            $params['course_category']        = $category_code;
            $params['course_language']      = $course_language;
            $params['gradebook_model_id']   = isset($course_values['gradebook_model_id']) ? $course_values['gradebook_model_id'] : null;

            $course_info = CourseManager::create_course($params);

  */

//  create_course($class_code, $row['title'], $new_prof, $row['category_code'], $row['course_language'], $row['professor_id'], $row['db_prefix'], $expir_date, $row['disk_quota'], $row['visibility'], $row['subscribe'], $row['unsubscribe']);
     
	$params['disk_quota'] = 300*1024*1024; //300mb
    $params['exemplary_content']    = empty($example_content) ? false : true; //empty == no example content
    $params['teachers']             = $new_prof;
    $params['user_id']              = $row['professor_id'];
    $params['title']                = $row['title'];
    $params['exemplary_content']    = $exemplary_content;
    $params['wanted_code']          = $class_code;
    $params['course_category']        = $row['category_code'];
    $params['course_language']      = $row['course_language'];
	
	 $params['visibility']      = $row['visibility'];
	 $params['subscribe']      = $row['subscribe'];
	 $params['unsubscribe']      = $row['unsubscribe'];
	 $params['expiration_date']      = $expir_date;
	 
	// $params['tutor_name'] 	 		= $course_tutor ;
   $course_info = CourseManager::create_course($params);

 $sql='SELECT id FROM chamilo2015.course WHERE code="'.$class_code.'";';
 $courseres=mysqli_query($mysql, $sql);
 
  if(!$courseres)
  {synclog_add('ERROR', 'COURSE ADD', 'Course with code "'.$class_code.'" creation failed', $script);
  }
  else
  {synclog_add('INFO', 'COURSE ADD', 'Course with code "'.$class_code.'" created', $script);
   return true;
   /*
   $classres=mysqli_query($mysql, 'SELECT COUNT(id) AS num FROM class WHERE name="'.$class_code.'";');
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
	$query = "REPLACE INTO chamilo2015.course_rel_user (course_code,user_id,status,role,group_id,tutor_id,sort,user_course_cat)SELECT t1.code AS course_code,t2.user_id, 1 AS
	status , 'professor' AS role, 0 AS group_id, 0 AS tutor_id, 0 AS sort, 0 AS user_course_cat
	FROM chamilo2015.mssql_course AS t1
	LEFT JOIN chamilo2015.user AS t2 ON ( t1.username_professor = t2.username )
	LEFT JOIN chamilo2015.course AS t3 ON ( t1.code = t3.code )
	LEFT JOIN chamilo2015.course_rel_user AS t4 ON ( t1.code = t4.course_code AND t2.user_id = t4.user_id)
	WHERE
	t1.code NOT LIKE '%V%'
	AND t1.code NOT LIKE '%D%'
	AND t1.code NOT LIKE '%R%'
	AND t1.code NOT LIKE '%S%'
	 
	AND t3.code IS NOT NULL     AND (t4.course_code IS NULL or t4.status !=1); ";
	 
	$res=mysqli_query($mysql, $query);
	 }
	return true;
   } */
    
   
  }
 }
 return false;
}
//--------------------------------------------------------------------------------------------------


//create class // ----------------------------- NOT NEEDED FOR CHAMILO
function class_add($class_code='')
{global $mysql;
global $script; 
 $class=new ClassManager();

 $sql='SELECT id FROM chamilo2015.class WHERE name="'.$class_code.'";';
 $rcs=mysqli_query($mysql, $sql);
 if(mysqli_num_rows($rcs1)==0 && $class_code!='')
 {$classres=$class->create_class($class_code);
  if(!$classres)
  {synclog_add('ERROR', 'CLASS ADD', 'Class with code "'.$class_code.'" creation failed', $script);
   return false;
  }
  else
  {synclog_add('INFO', 'CLASS ADD', 'Class with code "'.$class_code.'" created', $script);
   return true;
  }
 } 
 else
 {synclog_add('ERROR', 'CLASS ADD', 'Class with code "'.$class_code.'" already exist', $script);
  return false;
 }
}
//--------------------------------------------------------------------------------------------------
//subscribe class to course
function class_subscribe($class_code='', $course_code='')
{global $mysql;
global $script; 
 //$class=new ClassManager();
 //$sql1="SELECT id FROM dokeos_main.class WHERE name='".$class_code."';";
 //$rcs1=mysqli_query($mysql, $sql1)  ;
  //$sql2="SELECT code FROM dokeos_main.course WHERE code='".$course_code."';";
 
 //$sql2 = "insert ignore into dokeos_main.course_rel_class SELECT t1.code as course_code, t2.id as class_id FROM dokeos_main.course as t1 inner join dokeos_main.class as t2 on (t1.code = t2.name) ; ";
 
 //for now subscribing all classes to courses
 $sql2 = "insert ignore into chamilo2015.course_rel_class select name as course_code, id as class_id from chamilo2015.class ; ";
 
 
 $rcs2=mysqli_query($mysql, $sql2) ;
 
 // synclog_add('INFO', 'CLASS SUBSCRIBE', $sql1.' - '.mysqli_num_rows($rcs1), $script);
 // synclog_add('INFO', 'CLASS SUBSCRIBE', $sql2.' - '.mysqli_num_rows($rcs2), $script);
  
 
 if($mysqli_affected_rows>0)
 {//$row=mysqli_fetch_array($rcs2);
  
  
 // print '<br/>class subscribe to course'.$row['id'].','.$course_code.'<br/>';
 // $class->subscribe_to_course($row['class_id'], $course_code);
  synclog_add('INFO', 'CLASS SUBSCRIBE', 'Class with code "'.$class_code.'" subscribed to course "'.$course_code.'"', $script);
  return true;
 }
 synclog_add('ERROR', 'CLASS SUBSCRIBE', 'Class with code "'.$class_code.'" failed to subscribe to course "'.$course_code.'"', $script);
 return false;
}
//--------------------------------------------------------------------------------------------------
//notify theachers that their course is expired more than ... ($time)
function course_del_notify($time='1 month') // or 11 months
{global $mysql;
global $script; 
 $course=new CourseManager();
 $class=new ClassManager();
 $sql='SELECT t1.code, t1.title, t1.expiration_date, t2.firstname, t2.lastname, t2.email FROM course AS t1 INNER JOIN user AS t2 ON CONCAT(t2.lastname, \' \', t2.firstname)=t1.tutor_name WHERE DATE(t1.expiration_date)="'.date("Y-m-d", strtotime('-'.$time)).'" ORDER BY t2.email;';
 $rcs=mysqli_query($mysql, $sql);
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
{global $mysql;
global $script; 
 $course=new CourseManager();
 $class=new ClassManager();
 $sql='SELECT code FROM course WHERE code="'.$course_code.'";';
 $rcs=mysqli_query($mysql, $sql);
 if(mysqli_num_rows($rcs)>0)
 {
  $courseres=$course->delete_course($course_code);
  synclog_add('INFO', 'COURSE DELETE', 'Course with code "'.$course_code.'" deleted', $script);
  $sql2='SELECT id FROM class WHERE name="'.$course_code.'";';
  $rcs2=mysqli_query($mysql, $sql2);
  $row2=mysqli_fetch_array($rcs2);
  $class->delete_class($row2['id']);
  synclog_add('INFO', 'CLASS DELETE', 'Class with name "'.$course_code.'" and id "'.$row2['id'].'" deleted', $script);
  return true;




 }
 else
 {return false;
 }
}
//--------------------------------------------------------------------------------------------------
//deactivate array of courses
function course_deactivate($course_code='')
{global $mysql;
global $script; 
 $course=new CourseManager();
 $class=new ClassManager();
 $sql='SELECT code FROM course WHERE code="'.$course_code.'";';
 $rcs=mysqli_query($mysql, $sql);
 if(mysqli_num_rows($rcs)>0)
 {
  $sql='UPDATE course set visibility = "4" where code="'.$course_code.'";';
  $result=mysqli_query($mysql, $sql);
  
  synclog_add('INFO', 'COURSE DEACTIVATE', 'Course with code "'.$course_code.'" deactivated', $script);
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
{global $mysql;
 $course=new CourseManager();
 $class=new ClassManager();
 //delete courses that not exist in mssql_course and are more than 1 year expired
 $sql='SELECT t2.code FROM mssql_course AS t1 RIGHT JOIN course AS t2 ON t1.code=t2.code WHERE t1.code is null AND t2.expiration_date < "'.date("Y-m-d H:m:s", strtotime('-'.$time)).'";';
 
 $rcs=mysqli_query($mysql, $sql);
 while($row=mysqli_fetch_array($rcs))
 {
  $class_code=strtoupper($row['code']);
  $courseres=$course->delete_course($class_code);
  synclog_add('INFO', 'COURSE DELETE', 'Course with code "'.$class_code.'" deleted', $script);
  $sql2='SELECT id FROM class WHERE name="'.$class_code.'";';
  $rcs2=mysqli_query($mysql, $sql2);
  $row2=mysqli_fetch_array($rcs2);
  $class->delete_class($row2['id']);
  synclog_add('INFO', 'CLASS DELETE', 'Class with name "'.$class_code.'" and id "'.$row2['id'].'" deleted', $script);
 }
}

//deactivate regular courses (numeric code) where the expiration has passed //they should then be deleted 1 year? after this
function deactivate_old_courses()  
{global $mysql;
 //$course=new CourseManager();
// $class=new ClassManager();
 //delete courses that not exist in mssql_course and are more than 1 year expired
 //$sql="SELECT * FROM `course` t1 left join mssql_course t2 on (t1.code = t2.code) where t2.code is NULL and  concat('',t1.code * 1) = t1.code;";
   $sql='SELECT * FROM mssql_course AS t1 RIGHT JOIN course AS t2 ON t1.code=t2.code WHERE t1.code is null AND t2.expiration_date < "'.date("Y-m-d H:m:s", time()).'"  and ( concat("",t2.code * 1) = t2.code) and t2.visibility <> 4 ;';
   
 $rcs=mysqli_query($mysql, $sql);
 while($row=mysqli_fetch_array($rcs))
 {
   $class_code=strtoupper($row['code']);
  $res=course_deactivate($class_code);
   
  }
}


//--------------------------------------------------------------------------------------------------
//sync only existing courses with the mssql database
function course_sync()
{global $mysql;
global $script; 
 $course=new CourseManager();
 //$sql='SELECT t1.code, t1.expiration_dt, t2.expiration_date, t1.title, t1.category_code, t2.tutor_name, t3.user_id, CONCAT(t3.lastname, \' \', t3.firstname) AS profname, t3.user_id FROM mssql_course AS t1 INNER JOIN course AS t2 ON t1.code=t2.code INNER JOIN user AS t3 ON t1.username_professor=t3.username WHERE t1.title<>t2.title OR t3.username<>(SELECT username FROM user AS t4 WHERE CONCAT(t4.lastname, \' \', t4.firstname)=t2.tutor_name AND t4.status=1 ORDER BY `t1`.`title`  DESC LIMIT 1) OR t1.category_code <>t2.category_code OR t1.expiration_dt <>t2.expiration_date;';
 $sql='SELECT t1.code, t1.expiration_dt, t2.expiration_date, t1.title, t1.category_code 
 FROM mssql_course AS t1 
 INNER JOIN course AS t2 ON t1.code=t2.code 
 WHERE t1.title<>t2.title OR t1.category_code <>t2.category_code OR t1.expiration_dt <>t2.expiration_date;';
 
 $rcs=mysqli_query($mysql, $sql);
 while($row=mysqli_fetch_array($rcs))
 { 
   
   $sql2='UPDATE course SET title="'.$row['title'].'", category_code="'.$row['category_code'].'",  expiration_date="'.$row['expiration_dt'].'" WHERE code="'.$row['code'].'";';
   
   $res=mysqli_query($mysql, $sql2);
   if(!$res){synclog_add('ERROR', 'COURSE UPDATE', 'Course with code "'.$row['code'].'" failed to update', $script);}
   else{synclog_add('INFO', 'COURSE UPDATE', 'Course with code "'.$row['code'].'" updated', $script);}
 }
  // make sure course administrators are on the course tutor list
  /*
  $sql3a='INSERT IGNORE INTO mssql_class_tutor
		SELECT username_professor AS username, code AS classname
		FROM `mssql_course` 
		WHERE mssql_course.code LIKE "%d%"
		OR mssql_course.code LIKE "%v%"
		OR mssql_course.code LIKE "%s%" 
	; ';
 
    $res=mysqli_query($mysql, $sql3a);
  */
 
  
  // Remove teachers from docenten cursussen if not in mssql list
  /* $sql3='DELETE b
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
	*/
	
	/*$sql3='DELETE b 
	 
	FROM
	  dokeos_main.course_rel_user AS b
	  LEFT JOIN dokeos_main.user AS c ON (b.user_id = c.user_id)
	  LEFT JOIN dokeos_main.course AS a ON (b.course_code = a.code)
	  LEFT JOIN dokeos_main.mssql_class AS d ON (c.username = d.username AND b.course_code = d.classname)
	  LEFT JOIN dokeos_main.mssql_class_tutor AS e ON (c.username = e.username AND b.course_code = e.classname)
	
	WHERE
	  (b.course_code LIKE "%vg%" OR b.course_code LIKE "%dg%" OR b.course_code LIKE "%d%")
	  AND (
	
	
	  b.course_code NOT LIKE "%backup%"
	  AND b.course_code NOT LIKE "%temp%"
	  AND b.course_code NOT LIKE "%shaun%"
	  AND b.course_code NOT LIKE "%enquete%"
	  AND b.course_code NOT LIKE "%survey%"
	  AND b.course_code NOT LIKE "%enq%"
	  )
	
	  AND (e.username IS NULL and d.username IS NULL)
		
	 
	; ';
	
	
    $res=mysqli_query($mysql, $sql3);
    if(!$res){synclog_add('ERROR', 'COURSE USER DELETE', 'Failed to remove unauthorised teachers from teacher courses', $script);}
    else{
		if(mysqli_affected_rows($mysql)>0){
		 synclog_add('INFO', 'COURSE USER DELETE', 'Removed '.mysqli_affected_rows($mysql).' unauthorised teachers from teacher courses', $script);
		}
	}*/
 
}
//--------------------------------------------------------------------------------------------------
//sync only existing classes with the mssql database
//NO LONGER NEEDED FOR CHAMILO -- UNLESS WE START GROUPING COURSES TOGETHER
function class_sync()
{global $mysql;
global $script; 
 $class=new ClassManager();
 $createres=mysqli_query($mysql, 'SELECT t1.code, t2.name FROM course AS t1 LEFT JOIN class AS t2 ON t1.code=t2.name WHERE t2.name IS NULL;');
 while($createrow=mysqli_fetch_array($createres))
 {$class_code=strtoupper($createrow['code']);
  $classres=$class->create_class($class_code);
  synclog_add('INFO', 'CLASS ADD', 'Class with code "'.$class_code.'" created', $script);
 }
 $classsql='SELECT t1.code, t2.course_code FROM course AS t1 LEFT JOIN course_rel_class AS t2 ON t1.code=t2.course_code WHERE t2.course_code IS NULL;';
 $classres=mysqli_query($mysql, $classsql);
 while($classrow=mysqli_fetch_array($classres))
 {$linkres=mysqli_query($mysql, 'SELECT id FROM class WHERE name="'.$classrow['code'].'" LIMIT 0,1;');
  if(mysqli_num_rows($linkres)==1)
  {$class_id=mysqli_fetch_array($linkres);
   $class->subscribe_to_course($class_id['id'], $classrow['code']);
   synclog_add('INFO', 'CLASS SUBSCRIBE', 'Class with id "'.$classrow['id'].'" subscribed to course "'.$classrow['code'].'"',$script);
  }
 }
}


function course_list_activate($current_activate_list){//v 2.0 for chamilo
	//global $dbs;
	
	foreach ($current_activate_list as $row){
			 
				$value = course_add($row['code']);
				
				if($value){ // ACTIVATED -----------------
					//print(',succesvol aangemaakt'); 
					print ("<br>activated,".$row['title'].",".$row['code']);
					// SYNC STUDENTS HERE?? OR AS SEPERATE ACTION??
				} else{ // ERROR - - COURSE FAILED TO ACTIVATE
					print ("<br>ACTIVATION ERROR,".$row['title'].",".$row['code']);
					 
				}
				
			 
	}
	 
	user2course_add();
}
//--------------------------------------------------------------------------------------------------
//echo('<br>Course proces terminated');
//--------------------------------------------------------------------------------------------------
?>
