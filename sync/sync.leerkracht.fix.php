<?php

 exit;
$total_coarses_without_users = 0;
$total_coarses_missing_teachers = 0;
$total_coarses_checked = 0;
$total_teachers_found = 0;

//connection for cvo dokeos site
$host='localhost'; $user='root'; $password='cvo382';


//connect
$mysql_connection=mysql_connect($host, $user, $password) or die('Database Server Connection Failed_');

if($mysql_connection){print('connected to host<br>');};
 mysql_select_db("dokeos_main", $mysql_connection) or die('db selection failed_');

if(mysql_select_db){print('connected to database<br>');};

// complicate mysql version - replaces previous code but doesn't give much reporting

//function add_prof2course_if_not_exists{
	
	//global $mysql_connection;
	//add teachers to course user table for active courses where current head teacher has not yet been added
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
	$result=mysql_query($query) or die('query1 failed_<br>');
	
		
	print('<br/>teachers added: '.mysql_affected_rows()); 
	print('<br>');print('<br>');
//}

//add_prof2course_if_not_exists();


/*

$query1="SELECT * FROM dokeos_main.mssql_course WHERE code NOT LIKE '%V%' AND code NOT LIKE '%D%' AND code NOT LIKE '%R%'  AND code NOT LIKE '%S%' ";

$result=mysql_query($query1) or die('query1 failed_<br>');
//if($result){print('query1 succeeded_<br>');};

$num=mysql_numrows($result);
print('$num: '.$num.'<br><br>');

$i=0;
while ($i < $num) {
	
	$current_course_code=mysql_result($result,$i,"code");
	print($current_course_code); print(' ');
	//check if current course db exists - that means it's activated 
	$current_course_db = 'dokeos_course'.$current_course_code;
	mysql_select_db($current_course_db, $mysql_connection);
	if(mysql_select_db){print('course directory found <br>');
		
		$current_course_professor_name=mysql_result($result,$i,"username_professor");
		print($current_course_professor_name); print(' ');
		
		$query2="SELECT * FROM dokeos_main.user WHERE username = '{$current_course_professor_name}';";
		$result2=mysql_query($query2);
		
		$current_professor_id = ' ';
		while($row = mysql_fetch_array($result2)){
			$current_professor_id = $row['user_id'];
		}
		print('Current Prof ID: '.$current_professor_id); print(' ');
		
			
		mysql_select_db("dokeos_main", $mysql_connection) or die('db selection failed_');
		$query3="SELECT * FROM course_rel_user WHERE course_code='{$current_course_code}' AND user_id='{$current_professor_id}';";
		$num_rows=0;
		$num_rows = mysql_numrows(mysql_query($query3));
		if ($num_rows > 0){// prof exists
			print('Professor Exists '.$current_professor_id.' '.$current_course_code); print('<br>'); 
			$total_teachers_found++;
			}
		else {//prof doesn't exist
			print('PROFFESSOR MISSING '); print('<br>');
			
			// -- REMOVED FOR TKO FIX --------------------------------------------------------------------------
			// THIS CHECKED TO SEE IF THERE WERE STUDENTS IN THE CLASS
			//----------------------------------------------------------------------
			//mysql_select_db("dokeos_main", $mysql_connection) or die('db selection failed_');
			//$query4="SELECT * FROM course_rel_user WHERE course_code='{$current_course_code}';"; 
			//check if anyone exists as user in course
			//$num_rows2=0;
			//$num_rows2 = mysql_numrows(mysql_query($query4));
			//if ($num_rows2 > 0){// Users Exist
				//print('Users Exist '); print('<br>'); 
				// PROFFESSOR SHOULD BE ADDED TO USERLIST
				
				mysql_query("INSERT INTO `dokeos_main`.`course_rel_user` (`course_code`, `user_id`, `status`, `role`, `group_id`, `tutor_id`, `sort`, 		`user_course_cat`) VALUES ('{$current_course_code}', '{$current_professor_id}', '1', 'Professor', '0', '0', '0', '0');");
				$total_coarses_missing_teachers++;	
				
			//}
			//else{print('NO USERS '); print('<br>');
			//	$total_coarses_without_users++;
				
			//}
		}
	}
	else{print('COURSE DIRECTORY NOT FOUND <br>');}
	
	print('<br>');
	
$total_coarses_checked++;
$i++;
}

print('<br>');print('<br>');
print('total_coarses_checked= '.$total_coarses_checked); print('<br>');
print('total_coarses_without_users (dead coarses? or not activated?)= '.$total_coarses_without_users); print('<br>');
print('total_coarses_missing_teachers (should be fixed)= '.$total_coarses_missing_teachers); print('<br>');
print('total_teachers_found (ok)= '.$total_teachers_found); print('<br>');
*/

?> 