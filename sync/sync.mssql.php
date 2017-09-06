<?
 //disabled --------------------
 
// exit;
//--------------------------------------------------------------------------------------------------
//define include path (must be absolute for scheduled tasks)
//--------------------------------------------------------------------------------------------------
//$incpath=dirname(realpath('.')).'\\main\\inc\\';
//$incpath='/home/cvo/var/public_html/main/inc/';
//--------------------------------------------------------------------------------------------------
//include files
//--------------------------------------------------------------------------------------------------
//require_once($incpath.'global.inc.php');
require_once('inc.sync.php');
//--------------------------------------------------------------------------------------------------
//connect to the MSSQL database
//$mysql=mysqli_connect('127.0.0.1', 'cvoleuven', 'cvoleuven', 'chamilo2015') or die;
//$mysql=mysqli_connect($_configuration['db_host'], $_configuration['db_user'], $_configuration['db_password'], $_configuration['main_database']) or die('mysql connect failed<br/>');
//$mssql=mssql_connect('nt6server2', 'dokeos', 'netneduts!mroftalp') or die; 
//$mssql=mssql_connect('192.168.0.2', 'dokeos', 'netneduts!mroftalp') or die; 
//$mssql=mssql_connect('databank.cvoleuven.be:1433', 'dokeos', 'netneduts!mroftalp') or die('mssql_connect failed<br/>');
//mssql_select_db('test3', $mssql);
//mssql_select_db('test3tijdelijk2', $mssql);
//--------------------------------------------------------------------------------------------------
//function synclog_add_mssql($type, $action, $descr)
//{global $mysql;
// $sql='INSERT INTO mssql_synclog (`type`, `script`, `action`, `description`, `datetime`) VALUES ("'.strtoupper($type).'", "MSSQL","'.strtoupper($action).'" ,"'.htmlentities($descr).'", NOW());';
// $result=mysqli_query($mysql, $sql);
// return $result;
//}
//--------------------------------------------------------------------------------------------------

 define("DBASE","81.82.196.44/3050:/database/data/CVO_LL_OPERATIONEEL.FB"); 
	  define("DBUSER","SYSDBA"); 
	  define("DBPASS","t1G3rF0c3"); 

/*
    define("DBASE","81.83.9.170/3050:C:\\WISA\\CSWisAdmin\\Server\\Data\\CVO_LL_OPERATIONEEL.FB"); 
	define("DBUSER","SYSDBA"); 
	define("DBPASS","t1G3rF0c3"); 
*/
/*
 	define("DBASE","webmail.cvoleuven.be/3050:C:\\WISA\\CSWisAdmin\\Server\\Data\\CVO_LL_OPERATIONEEL.FB"); 
	define("DBUSER","GEBRUIKERWEBSITE"); 
	define("DBPASS","cvo123"); 
  */
    /*
    define("DBASE","webmail.cvoleuven.be/3050:C:\\WISA\\CSWisAdmin\\Server\\Data\\CVO_LL_OPERATIONEEL.FB"); 
	define("DBUSER","dokeos"); 
	define("DBPASS","Leerplatform2013"); 
    */
   
    // DB connection
    $mssql_conn = ibase_connect(DBASE,DBUSER,DBPASS);
   // $mssql_conn = ibase_connect(DBASE,DBUSER,DBPASS)or die("no connection with mssql<br/>"); 
    if ($mssql_conn == FALSE) {
    echo 'could not connect to DB :-(<BR>'.ibase_errmsg(); exit;
    } else {
    echo 'success to connect to DB :-)<BR>';
    // DB dis connection
    
	}
	
	
	
	
function ibase_numberofrows($res){
		
		while ($row = ibase_fetch_object($res)) {
		//print $row->email . "\n";
		$record_number++;
		
		}
		return $record_number;
		
		
}
 
 

if ($mysql && $mssql_conn){
   // we're ok to continue
   print 'holy connection Batman!';
   
} else {
	if(!$mysql){
		synclog_add('ERROR', 'MSSQL SYNC', 'No connection with MYSQL database','MYSQL');
	}
	if(!$mssql_conn){
		synclog_add('ERROR', 'MSSQL SYNC', 'No connection with MSSQL database','MSSQL');
	}
	
	exit;
}


 


// IMPROVED DOKEOS SYNC SAFETY CHECK ----------------------

function numberDifference($number1, $number2){
	//returns difference between two numerical values
	
	if ( is_numeric($number1) && is_numeric($number2) ) {
	$absolute_difference = abs( $number1 - $number2 );
	}else{$absolute_difference=NULL;}
	return $absolute_difference;
	
	}

// CHECKS DIFFERENCE IN TABLE ROWS BEFORE PERFORMING TRUNCATE OR INSERT
function dokeosSyncSafetyCheck($mssqlResultArray, $mysqlTable, $differenceLimit = 100){
	 global $mysql;
	 
	 $query='select * from '.$mysqlTable.' ;';
	 $res=mysqli_query($mysql, $query);
	 $numrows_mysql =  mysqli_num_rows($res);
	  
	 //$numrows_mssql =  mssql_num_rows($mssqlResultArray) ;
	 $numrows_mssql = ibase_numberofrows($mssqlResultArray) ;
	 
	 $difference = numberDifference($numrows_mssql, $numrows_mysql);
	 
	 print '<br/><br/>';
	 print 'mysql table:'.$mysqlTable.'<br/>';
	 print 'difference:'.$difference.'<br/>';
	 print 'numrows_mysql:'.$numrows_mysql.'<br/>';
	 print 'numrows_mssql:'.$numrows_mssql.'<br/>';
	  print '<br/><br/>';
	  
	 if((($difference <= $differenceLimit) && ($numrows_mysql>0) &&($numrows_mssql>0) && (!is_null($difference))) OR ($_GET['key']=='eagle-eye')){
		 // control passed  // or is overridden
		 return true;
	 } else {
		 //control failed !!!!!!!!!!!!! // or is being tested
		 $errorMessage = 'CHAMILO SYNC ERROR - '.$mysqlTable.' Difference: '.$difference.' Limit: '.$differenceLimit;
		 
		 //SEND EMAIL NOTIFICATION TO WEBMASTER ---------------------
		 mail('webmaster@cvoleuven.be', $errorMessage, $errorMessage);		//------------------------------------------------------------
		 
		synclog_add('ERROR', 'MSSQL SYNC', $errorMessage,'MSSQL'); 
		 		 
		 exit; // stop scipt dead!!!!!!
	 }
	
}


//course teacher fix
//add teachers to course user table for active courses where current head teacher has not yet been added
	// this is also the code for course_leerkracht fix
	$query95494 = "REPLACE INTO chamilo2015.course_rel_user (course_code,user_id,status,role,group_id,tutor_id,sort,user_course_cat)SELECT t1.code AS course_code,t2.user_id, 1 AS
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
	 
	AND t3.code IS NOT NULL     AND (t4.course_code IS NULL or t4.status !=1)   ; ";
	 
	$res95494=mysqli_query($mysql, $query95494);


// this is also the code for course_leerkracht fix
	$query95444 = " 
	 replace into chamilo2015.course_rel_user_history select * from chamilo2015.course_rel_user where 1;
	 ";
	 
	$res95444=mysqli_query($mysql, $query95444);
//--------------------------------------------------------------------------------------------------

//@todo this wan't working properly after the data merge queries, but it really should happen after the data merge
// otherwise it takes 2 syncs for newly registered students to access their courses

// make sure all classes are subscribed to courses
//$q="insert ignore into chamilo2015.course_rel_class select name as course_code, id as class_id from chamilo2015.class";
//mysqli_query($mysql, $q);


/* todo - reactivate this for chamilo ????
// make sure all class students are added to courses
$q="INSERT IGNORE INTO chamilo2015.course_rel_user SELECT t1.course_code, t2.user_id, 5 as status, NULL as role, 0 as group_id, 0 as tutor_id, NULL as sort, 0 as user_course_cat FROM chamilo2015.course_rel_class as t1 inner join chamilo2015.class_user as t2 on (t1.class_id = t2.class_id)  ;  
";
mysqli_query($mysql, $q);

*/

$perform_cat_sync = false; // not yet working
$perform_user_sync = true;
$perform_course_sync = true;
$perform_class_sync = true;
$perform_class_tutor_sync = true;

function getIbaseTableInfo($table){
	$sql='SELECT * FROM '.$table;
		$rs=ibase_query($mssql_conn, $sql) or print('ibase $table select failed!'.$sql);
	$coln = ibase_num_fields($rs);
	$line_return = "\n";
	$line_return = "<br/>";
	for ($i = 0; $i < $coln; $i++) {
		$col_info = ibase_field_info($rs, $i);
		echo "name: ". $col_info['name']. $line_return;
		echo "alias: ". $col_info['alias']. $line_return;
		echo "relation: ". $col_info['relation']. $line_return;
		echo "length: ". $col_info['length']. $line_return;
		echo "type: ". $col_info['type']. $line_return;
		echo $line_return;
	}
}



 
//--------------------------------------------------------------------------------------------------
if ($perform_cat_sync){
	//$sql='SELECT * FROM dokeos_category'; 
	//$res=mssql_query($sql, $mssql);
	$sql='SELECT * FROM VW_DOKEOS_CATEGORY'; // THIS QUERY DOESN'T EXIST YET
	//$res=sqlsrv_query($mssql_conn, $sql);
	$res=ibase_query($mssql_conn, $sql) ;//or die('ibase category select failed!');
	$res2=ibase_query($mssql_conn, $sql) ;// or die('ibase category select failed!');
	
	if (!$res or !$res2){ synclog_add('ERROR', 'MSSQL SYNC', 'ibase category select failed!','MSSQL'); exit;} 
		
	$check = dokeosSyncSafetyCheck($res2, 'mssql_category', 5);
	mysqli_query($mysql, 'TRUNCATE mssql_category');
	//mysqli_query($mysql, 'TRUNCATE mssql_category'); //doing this later
	
	while($row = ibase_fetch_object($res))
	{$name             =(string)clean(trim($row['name']));
	 $code             =(string)clean(strtoupper(trim($row['code'])));
	 //$parent_code      =(string)$row['parent_code'];
	 //$tree_pos         =(int)$row['tree_pos'];
	 //$children_count   =(int)$row['children_count'];
	 //$auth_course_child=$row['auth_course_child'];
	
	 $insql='INSERT INTO mssql_category (name, code) VALUES ("'.$name.'", "'.$code.'");';
	 $inres=mysqli_query($mysql, $insql);
	}
	unset($res,$res2);
}
//--------------------------------------------------------------------------------------------------
if ($perform_user_sync){
	//$sql='SELECT * FROM dokeos_user';
	//$res=mssql_query($sql, $mssql);
	$sql='SELECT * FROM VW_DOKEOS_USER';
	//$res=sqlsrv_query($mssql_conn, $sql);
	$res=ibase_query($mssql_conn, $sql) or print('ibase user select failed!');
	$res2=ibase_query($mssql_conn, $sql) or print('ibase user select failed!');
	//$res = odbc_exec($mssql_conn, $sql);
	
	if (!$res or !$res2){ synclog_add('ERROR', 'MSSQL SYNC', 'ibase user select failed!','MSSQL'); exit;} 
	
	
	  $check = dokeosSyncSafetyCheck($res2, 'mssql_user', 250);
	mysqli_query($mysql, 'TRUNCATE chamilo2015.mssql_user')or die(mysqli_error($mysql)) ;
	//mysqli_query($mysql, 'TRUNCATE mssql_user') ;
	
	while($row = ibase_fetch_object($res))
	{$code     = (string)clean(strtoupper(trim($row->CODE))); //(string)strtoupper(trim($row['code']));
	 $lastname =(string)str_replace("\'","`",clean(trim($row->LASTNAME)));
	 $firstname=(string)clean(trim($row->FIRSTNAME));
	 $username =(string)clean(strtolower(trim($row->USERNAME)));
	 //$password =(string)strtolower(trim($row['username']));
	 $password =(string)clean(strtolower(trim($row->PASSWORD)));
	 $email    =(string)clean(strtolower(trim($row->EMAIL)));
	 $status   =(int)trim($row->STATUS);		//1=lesgever, 5=student
	 $status   =($status==5 OR $status==1)?$status:5;
	 $language ='dutch_(cvo_volt)';
	 $active   =1;							//1=active, 0=passive
	 $insql='INSERT INTO mssql_user (code, lastname, firstname, username, password, email, status, language, registration_date, active) VALUES ("'.$code.'", "'.$lastname.'", "'.$firstname.'", "'.$username.'", "'.$password.'", "'.$email.'", '.$status.', "'.$language.'", NOW(), '.$active.');';
	 $inres=mysqli_query($mysql, $insql);
	 if(!$inres){echo('<br/><br/>ERROR: '.$insql.'<br/>'.mysqli_error($mysql));
	 	$failedInserts++;}
	}
	print '<br/><br/>user sync complete - failed inserts: '.$failedInserts.'<br/></br>';
	print '<br/>//--------------------------------------------------------------------------------------------------<br/></br>';
unset($res,$res2,$failedInserts);
	
}

 
//--------------------------------------------------------------------------------------------------
if ($perform_course_sync){
	//$sql='SELECT * FROM VW_DOKEOS_COURSE where title NOT LIKE "%Zelfstudie%"';
	$sql='SELECT * FROM VW_DOKEOS_COURSE  ';
	$res=ibase_query($mssql_conn, $sql) or print('ibase course select failed!');
	$res2=ibase_query($mssql_conn, $sql) or print('ibase course select failed!');
	//$res = odbc_exec($mssql_conn, $sql);
	if (!$res or !$res2){ synclog_add('ERROR', 'MSSQL SYNC', 'ibase course select failed!','MSSQL'); exit;} 
	
	
	  $check = dokeosSyncSafetyCheck($res2, 'mssql_course', 300);
	mysqli_query($mysql, 'TRUNCATE mssql_course');
    //mysqli_query($mysql, 'TRUNCATE mssql_course');
	
	while($row = ibase_fetch_object($res))
	{//print_r($row);
	 
	 $code                =(string)strtolower(trim($row->VWK_ID));
	
	 $title               =(string)clean(trim($row->TITLE));
	 $category_code       =(string)clean(trim($row->CATEGORY_CODE));
	 $course_language     ='dutch_(cvo_volt)';
	 $username_professor  =(string)clean(trim($row->USERNAME_PROFESSOR));
	  $db_prefix           =''; //'dokeos_course_';
	 $expiration_dt       =strtotime($row->EXPIRATION_DT);
	
	
	$disk_quota          =250000000;
	 $visibility          =1;
	 $subscribe           =0;
	 $unsubscribe         =0;
	 $insql='INSERT INTO mssql_course (code, title, category_code, course_language, username_professor, db_prefix, expiration_dt, disk_quota, visibility, subscribe, unsubscribe) VALUES ("'.$code.'", "'.$title.'", "'.$category_code.'", "'.$course_language.'", "'.$username_professor.'", "'.$db_prefix.'", FROM_UNIXTIME('.$expiration_dt.'), '.$disk_quota.', '.$visibility.', '.$subscribe.', '.$unsubscribe.');';
	 $inres=mysqli_query($mysql, $insql);
	 if(!$inres){echo('<br/><br/>ERROR: '.$insql.'<br/>'.mysqli_error($mysql).'<hr>');}
	}
	print '<br/>course sync complete</br>';
	unset($res,$res2);
}

//--------------------------------------------------------------------------------------------------

if($perform_class_sync){
	//$sql='SELECT * FROM VW_DOKEOS_CLASS where username NOT LIKE "2007"'; //PROBABLY DOCENTENCURSUSSEN WITH BLANK ENTRIES
	$sql='SELECT * FROM VW_DOKEOS_CLASS '; //PROBABLY DOCENTENCURSUSSEN WITH BLANK ENTRIES
	$res=ibase_query($mssql_conn, $sql) or print('ibase class select failed!');
	$res2=ibase_query($mssql_conn, $sql) or print('ibase class select failed!');
	//$res = odbc_exec($mssql_conn, $sql);
	if (!$res or !$res2){ synclog_add('ERROR', 'MSSQL SYNC', 'ibase class select failed!','MSSQL'); exit;} 
	
	  $check = dokeosSyncSafetyCheck($res2, 'mssql_class', 900);
	mysqli_query($mysql, 'TRUNCATE mssql_class');
	//mysqli_query($mysql, 'TRUNCATE mssql_class');
	
	while($row = ibase_fetch_object($res))
	{$username =(string)clean(trim($row->USERNAME));
	 $classname=(string)clean(strtolower(trim($row->CODE)));
	 $insql='INSERT INTO mssql_class (username, classname) VALUES ("'.$username.'", "'.$classname.'");';
	 $inres=mysqli_query($mysql, $insql);
	 if(!$inres){echo('ERROR: '.$insql.'<hr>');}
	}
	print '<br/>user class complete</br>';
	unset($res,$res2);
	
	
	//exception for ve --------------------------------------- portfolio modules 2015-2016
	mysqli_query($mysql,  "insert ignore into mssql_class SELECT username, '9129' as classname FROM mssql_class WHERE classname like '9198';");
	
}
//--------------------------------------------------------------------------------------------------

 

if ($perform_class_tutor_sync){
	//$sql='SELECT * FROM dokeos_class_tutor';
	//$res=mssql_query($sql, $mssql);
	$sql='SELECT * FROM VW_DOKEOS_CLASS_TUTOR ';  
	$res=ibase_query($mssql_conn, $sql) or print('ibase class tutor select failed!');
	$res2=ibase_query($mssql_conn, $sql) or print('ibase class tutor select failed!');
	if (!$res or !$res2){ synclog_add('ERROR', 'MSSQL SYNC', 'ibase class tutor select failed!','MSSQL'); exit;} 
	
	$check = dokeosSyncSafetyCheck($res2, 'mssql_class_tutor', 10000000);
	mysqli_query($mysql, 'TRUNCATE mssql_class_tutor');
	//mysqli_query($mysql, 'TRUNCATE mssql_class_tutor');
	
	while($row = ibase_fetch_object($res))
	{$username =(string)clean(trim($row->DOKEOSLOGIN));
	 $classname=(string)clean(strtolower(trim($row->CODE)));
	 $insql='INSERT INTO mssql_class_tutor (username, classname) VALUES ("'.$username.'", "'.$classname.'");';
	 $inres=mysqli_query($mysql, $insql);
	 if(!$inres){echo('ERROR: '.$insql.'<hr>');}
	}
	print '<br/>tutor sync complete</br>';
	unset($res,$res2);
}
//--------------------------------------------------------------------------------------------------
//START sync IKZ module survey table
//--------------------------------------------------------------------------------------------------

 /* CREATE TABLE IF NOT EXISTS `mssql_enquete_modules` (
  `code` int(250) NOT NULL,													 
  `vestigingspl` tinyint(2) NOT NULL,
  `vakgebied_id` tinyint(3) NOT NULL,
  `vakgebied` varchar(250) NOT NULL,
  `title` varchar(250) NOT NULL,
  `aantal_keer_per_week` tinyint(1) NOT NULL,
  `leerkracht_id` varchar(40) NOT NULL,
  `begindatum` datetime NOT NULL,
  `einddatum` datetime NOT NULL,
  `deelklas` varchar(1) NOT NULL,
  
  PRIMARY KEY  (`code`)
) TYPE=MyISAM;
 */

 /*
  
 
 // ----------------------------------------------------------------------------------  TURNED OFF SO WE CAN MANUALLY SET COURSES FOR SURVEY AND SPECIFY WHICH ONES HAVE MORE THAN ONE TEACHER
$sql='SELECT * FROM IKZenqueteModules'; 
$res=mssql_query($sql, $mssql)or die('<br/> select IKZenqueteModules table FAILED') ;

//$check = dokeosSyncSafetyCheck($res, 'mssql_enquete_modules', 5);
//mysqli_query($mysql, 'TRUNCATE mssql_enquete_modules');

while($row = ibase_fetch_object($res))
{print_r($row);
 $vestigingspl         =(int)trim($row['vestigingspl']); //tinyint 2
 $vakgebied_id         =(int)trim($row['VakgebiedID']);  //tinyint 3
 $vakgebied           =(string)trim($row['Vakgebied']); //varchar 250
 $code   			  =(int)trim($row['INrichtingsID']); // int 40
 $title               =(string)trim($row['E_naam']); //varchar 250
 $aantal_keer_per_week    =(int)trim($row['AantalKeerPerWeek']);  // tinyint 1
 $leerkracht_id     	  =(int)trim($row['LeraarID']);  //varchar 40
 $begindatum      	 =strtotime($row['begindatum']); //datetime
 $einddatum      		 =strtotime($row['einddatum']);  //datetime
 $deelklas           =(string)strtoupper(trim($row['Deelklas'])); //varchar 1
 $send_dt      	     =strtotime($row['VoorgesteldeDatumEnquete']); //datetime

 
print $insql="
INSERT IGNORE INTO `mssql_enquete_modules` 
(`code`, `vestigingspl`, `vakgebied_id`, `vakgebied`, `title`, `aantal_keer_per_week`, `leerkracht_id`, `begindatum`, `einddatum`, `deelklas`, `send_dt`) VALUES
($code, $vestigingspl, $vakgebied_id, '$vakgebied', '$title', $aantal_keer_per_week,  $leerkracht_id, FROM_UNIXTIME($begindatum), FROM_UNIXTIME($einddatum), '$deelklas', FROM_UNIXTIME($send_dt));";
 $inres=mysqli_query($mysql, $insql);
 if(!$inres){echo('ERROR: '.$insql.'<hr>');}
 
}
 
//--------------------------------------------------------------------------------------------------
//END sync IKZ module survey table
//--------------------------------------------------------------------------------------------------

*/






 


//merge data from old mssql database ----------------------------------------------------
// first add data from old database, then add new data 
// at the moment new data does not overwrite the old (keeping old user codes)
// we need to see how we're going to switch to the new user id's 
 
 
 //---------  THIS IS NO LONGER NECESSARY --------------------
 /* 
$q="
use chamilo2015;

truncate mssql_category;
insert ignore into mssql_category select * from mssql1_category  ;
insert ignore into mssql_category select * from mssql0_category  ;

 truncate mssql_class_tutor; 
 insert ignore into mssql_class_tutor SELECT * FROM mssql1_class_tutor where classname NOT LIKE '%vg%' ;
 insert ignore into mssql_class_tutor SELECT * FROM mssql0_class_tutor where classname NOT LIKE '%vg%' ;
 
 truncate mssql_course;
 insert ignore into mssql_course select * from mssql1_course;
  insert ignore into mssql_course select * from mssql0_course;
  
truncate mssql_user;
  
 insert ignore into mssql_user select * from mssql0_user;
 
truncate mssql_class;
  
 insert ignore into mssql_class select * from mssql0_class;

update chamilo2015.mssql_user set username = 'tineke.tailleur', lastname = 'TAILLEUR', firstname = 'TINEKE' where username = 'hubert.bergs' LIMIT 1;

update chamilo2015.mssql_class set username = 'tineke.tailleur' where username = 'hubert.bergs'; 

update chamilo2015.mssql_class set classname = '882' where  username = 'hassani' and classname = '890';



INSERT IGNORE INTO chamilo.user SELECT t2 . * , '' AS openid, '' AS theme, '' AS hr_dept_id FROM chamilo2015.`user` AS t2
   ;
 
INSERT ignore into chamilo.course_rel_user select t2.*, '' as relation_type, '' as legal_agreement from chamilo2015.course_rel_user as t2;
   UPDATE chamilo.user as t1 set t1.active = 1 where user_id in (SELECT user_id from chamilo.course_rel_user );  
 
 
 ";
 
 */
 
 //this multi query function is working, but after this multi_query fails - result not being freed properly?? driver problem??
//mysqli_query($mysql, $q);
 
 //$res = mysqli_multi_query($mysql, $q) or print ('<br/> data merge failed !!!!!!');
/*  
 if($res){print '<br/>data seems to be merged ok';}
 else{
	 //report data merge failed
	 synclog_add('ERROR', 'MSSQL SYNC', 'DATA MERGE FAILED!','MSSQL');
	 exit;
	 } 
 
unset($res);
*/

//make sure chamilo users are sync'd
//$q = "INSERT ignore into chamilo.course_rel_user select t2.*, '' as relation_type, '' as legal_agreement from chamilo2015.course_rel_user as t2 ;";
 
 //$res = mysqli_query($mysql, $q)  or print ('<br/> data chamilo user sync failed !!!!!!'.mysqli_error($mysql).''.$q);

//-------------- @todo verwijder dit als Hubert geen cursist meer is -----------
 print '<br/><br/>'.'so far so good';
	
//$q='INSERT INTO chamilo2015.mssql_user (code, lastname, firstname, username, password, email, status, language, registration_date, active) VALUES ("-C2145766235", "TAILLEUR", "TINEKE", "tineke.tailleur", "tineke.tailleur", "hubert.berghs.2011@gmail.com", "5", "dutch", "2011-12-22 16:25:32", "1"); ';
//mysqli_query($mysql, $q) or print($q.' '.mysqli_error($mysql));
/*
$q="update chamilo2015.mssql_user set username = 'tineke.tailleur', lastname = 'TAILLEUR', firstname = 'TINEKE' where username = 'hubert.bergs' LIMIT 1;

update chamilo2015.mssql_class set username = 'tineke.tailleur' where username = 'hubert.bergs'; ";
 
$res = mysqli_multi_query($mysql, $q) or print('<br/><br/>'.$q.' '.mysqli_error($mysql).'<br/><br/>' );
unset($res);
//--------------------------------------------------------------------------------------------------
 */
 /*
//not sure why but $mysql->multi_query($q) wasn't working
$q="truncate mssql_category;"; 
mysqli_query($mysql, $q);
$q="insert ignore into mssql_category select * from mssql1_category;"; 
mysqli_query($mysql, $q);
$q="insert ignore into mssql_category select * from mssql0_category  ;"; 
mysqli_query($mysql, $q);

$q=" truncate mssql_class_tutor; "; 
mysqli_query($mysql, $q);
$q="insert ignore into mssql_class_tutor select * from mssql1_class_tutor;"; 
mysqli_query($mysql, $q);
$q="insert ignore into mssql_class_tutor select * from mssql0_class_tutor;"; 
mysqli_query($mysql, $q);

$q=" truncate mssql_course;"; 
mysqli_query($mysql, $q);
$q=" insert ignore into mssql_course select * from mssql1_course; "; 
mysqli_query($mysql, $q);
$q="insert ignore into mssql_course select * from mssql0_course; "; 
mysqli_query($mysql, $q);

$q="truncate mssql_user;"; 
mysqli_query($mysql, $q);
$q=" insert ignore into mssql_user select * from mssql1_user;
 "; 
mysqli_query($mysql, $q);
$q="insert ignore into mssql_user select * from mssql0_user;
 "; 
mysqli_query($mysql, $q);

$q="truncate mssql_class;
"; 
mysqli_query($mysql, $q);
$q=" insert ignore into mssql_class select * from mssql1_class;
 "; 
mysqli_query($mysql, $q);
$q=" insert ignore into mssql_class select * from mssql0_class;
"; 
mysqli_query($mysql, $q);
 */

//------------------------------------------------------------------------
//--------- add exceptions -----------------
// -------------------------------------------------------------------------
   
    //add all active teachers to educative tools course
    $q='insert ignore into mssql_class select username, "EDUC" as classname from chamilo2015.mssql_user where status = 1';
	 $res=mysqli_query($mysql, $q);
	 if(!$res){echo('ERROR: 651346548 '.$q.'<hr>');}
	 
	 
	 
    //generate test courses
    $q="insert ignore into mssql_course SELECT UPPER(REPLACE( CONCAT( 'Test', `firstname` , `lastname` ) , ' ', '' )) AS code, REPLACE( CONCAT( 'Test', `firstname` , `lastname` ) , ' ', '' ) AS title, '' AS category_code, 'dutch_(cvo_volt)' AS course_language, username AS username_professor, '' AS db_prefix, '2030-01-01' AS expiration_dt, 250000000 AS disk_quota, 1 AS visibility, 0 AS subscribe, 0 AS unsubscribe FROM chamilo2015.mssql_user WHERE STATUS =1;";
	$res=mysqli_query($mysql, $q);
	if(!$res){echo('ERROR: 9984654631'.$q.'<hr>');}
	 

    //add junk student for testing
	$q="INSERT INTO `chamilo2015`.`mssql_user` (`code`, `lastname`, `firstname`, `username`, `password`, `email`, `status`, `language`, `registration_date`, `active`) VALUES ('Djunkcursist', 'mr', 'karel', 'mr.karel.for.prez', '1047', 'kareltheunis@telenet.be', '5', 'dutch_(cvo_volt)', '2016-02-03 13:02:55', '1');";
	$res=mysqli_query($mysql, $q);
	if(!$res){echo('ERROR: 65165132 '.$q.'<hr>');}


 //make sure all teachers are added to class tutor list
	$q=  "insert ignore into mssql_class_tutor select t1.username_professor, t1.code as classname 			from mssql_course  as t1 ;";
	$res=mysqli_query($mysql, $q);
	if(!$res){echo('ERROR: 66165135 '.$q.'<hr>');}


 //make sure onderwijscommissie has toegang naar account of filip neesen
	//$q=  "update user set password = '".md5('peperkoek')."' where username like 'filip.neesen' limit 1 ;";
	//$res=mysqli_query($mysql, $q);
	//if(!$res){echo('ERROR: 66165135 '.$q.'<hr>');}




//merge german class 4 & 5 for school year 2016-17 -- they are physically one class
	$q=  "update `mssql_class` set  `classname` =  '11686' 	where `classname` LIKE '11514';";
	$res=mysqli_query($mysql, $q);
	if(!$res){echo('ERROR: 665464615 '.$q.'<hr>');}

//merge graphicshe portfolio classes 
	$q=  "update `mssql_class` set  `classname` =  '10809' 	where `classname` LIKE '10810';";
	$res=mysqli_query($mysql, $q);
	if(!$res){echo('ERROR: 665464615 '.$q.'<hr>');}


//merge filip neesen automatisering tko en hbo classes 2017-6-17 sem 2
	$q=  "update `mssql_class` set  `classname` =  '11169' 	where `classname` LIKE '12587';";
	$res=mysqli_query($mysql, $q);
	if(!$res){echo('ERROR: 6665464615 '.$q.'<hr>');}



 print '<br/><br/>'.'still going ...';






//----------------------------------------------------------------------------------------------------
// START sync PERSONEEL PHOTOBOOK TABLES  ---------------------------------------------------------
//----------------------------------------------------------------------------------------------------


	//--------------------------------------------------------------------------------------------------
	 
	$sql='SELECT * FROM VW_WEB_FOTOBOEK_PERSOON';
	//$res=sqlsrv_query($mssql_conn, $sql);
	$res=ibase_query($mssql_conn, $sql) or die('ibase FOTOBOEK persoon select failed! ');
	//$res = odbc_exec($mssql_conn, $sql);
	 
	 
	
	if($res){
		$query1="truncate mssql_personeel_persoon;"; 
		$result=mysql_query($query1) ; 
	}
	else{
	  print 'personeel_fotoboek_persoon select failed<br>'; 
	}
	
	
	while($row=ibase_fetch_object($res))
	{$leerkrachtID     			=(int)trim($row->LEERKRACHTID); //(int)trim($row['leerkrachtID']);
	 $Naam 						=(string)clean(ucfirst(strtolower(trim($row->NAAM))));
	  $Voornaam					=(string)clean(ucfirst(strtolower(trim($row->VOORNAAM)))); 
	 $Email 					=(string)clean(strtolower(trim($row->EMAIL)));
	 $DokeosLogin 				=(string)clean(strtolower(trim($row->DOKEOSLOGIN)));
	 $HoofdVestigingsplaats   	=(int)trim($row->HOOFDVESTIGINGSPLAATS);
	 $Hoofdvakgroep   			=(int)trim($row->HOOFDVAKGROEP);	 
	 $InDienst   				=(string)trim($row->INDIENST);	
	 $Vakgebied 				=(string)trim($row->VAKGEBIED);
	 $Vestigingsnaam2   		=(string)clean(trim($row->VESTIGINGSNAAM));							 
	 
	 $Adres   					=(string)clean(ucfirst(strtolower(trim($row->ADRES))));  	
	 $Postnr   					=(int)trim($row->POSTNR);	
	 $gemeente   				=(string)clean(ucfirst(strtolower(trim($row->GEMEENTE)))); 	
	 $Tel   					=(string)trim($row->TEL);	
	 $GSM  						=(string)trim($row->GSM);	
	 $geboortedatum   			=date('Y-m-d', strtotime($row->GEBOORTEDATUM)); 
	 $Geslacht   				=(string)trim($row->GESLACHT);	
	 $EmailPrive   				=(string)clean(strtolower(trim($row->EMAILPRIVE)));	
	  
	 //NEW FIELDS ADDED WITH WISA
	 $Roepnaam   				=(string)clean(ucfirst(strtolower(trim($row->PS_ROEPNAAM))));
	 $Photo   				 	=(string)clean(strtolower(trim($row->PS_PHOTO)));
	 $Flags   					=(string)clean(trim($row->PS_FLAGS));
	 $Van   					=(string)trim($row->PS_VAN);
	 $Tot  						=(string)trim($row->PS_TOT);
	 $TYPEADRES_FKP 			=(string)trim($row->PS_TYPEADRES_FKP);
	 
	 
	 
	 $insql='INSERT INTO mssql_personeel_persoon (leerkrachtID, Naam, Voornaam, Email, DokeosLogin, HoofdVestigingsplaats, Hoofdvakgroep, InDienst, Vakgebied, Vestigingsnaam2, Adres, Postnr, gemeente, Tel, GSM, geboortedatum, Geslacht, EmailPrive) VALUES ('.$leerkrachtID .', "'.$Naam .'", "'.$Voornaam.'", "'.$Email.'", "'.$DokeosLogin.'", '.$HoofdVestigingsplaats.', "'.$Hoofdvakgroep.'", "'.$InDienst.'", "'.$Vakgebied.'", "'. $Vestigingsnaam2.'", "'. $Adres.'", "'. $Postnr.'", "'. $gemeente.'", "'. $Tel.'", "'. $GSM.'", "'. $geboortedatum.'", "'. $Geslacht.'", "'. $EmailPrive.'");';
	 //$inres=mysqli_query($mysql, $insql);
	  $inres=mysql_query($insql);
	 if(!$inres){echo('<br/>Personeel insert:'.mysql_error());}
}

	// set default Vakgebied  - Administratie
	$query='UPDATE mssql_personeel_persoon set Vakgebied = "Administratie" WHERE Vakgebied = "" ; ';
	 //$inres=mysqli_query($mysql, $insql);
	  $updateres=mysql_query($query);

	//mssql_close();
 
	
	//------------------------------------------------------------------------------------------------------------------
	
	
	//-------------------------------------------------------------------------------------	
	//MYSQL_CONNECTION 
	//$mysql_conn = mysql_connect("localhost", "cvoleuven", "cvo382");
	//mysql_select_db("cvoleuven",$mysql_conn) or die("no connection with mysql<br/>"); 
	//-------------------------------------------------------------------------------------
	 //mysql_query("SET SESSION sql_mode = 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION,TRADITIONAL,STRICT_ALL_TABLES' ");
	
	//-------------------------------------------------------------------------------------	
/*	//Microsoft SQL CONNECTION 
	$mssql_conn=mssql_connect('databank.cvoleuven.be,1433', 'website2', 'ngnageot!etisbew') or  print("no connection with mssql<br/>");//die("no connection with mssql<br/>"); // windows system 
	mssql_select_db('test3', $mssql_conn);
	
*/	//-------------------------------------------------------------------------------------
	
	 
	//--------------------------------------------------------------------------------------------------
	  $sql='Select * FROM VW_WEB_FOTOBOEK_VAKGEBIEDEN ;';
	$res=ibase_query($mssql_conn, $sql) or die('ibase FOTOBOEK VAKGEBIEDEN select failed! ');
	
	if($res){
		$query1="truncate mssql_personeel_vakken;"; 
		$result=mysql_query($query1) ;
		
		 
		$mssql_result=mssql_query($sql) ;
	}
	else{
	  print 'personeel_fotoboek_vakken select failed<br>'; 
	}
	
	
	
	while($row=ibase_fetch_object($res))
	{$leerkrachtID     			=(int)trim($row->LEERKRACHTID);
	 $naam 						=(string)clean(trim($row->NAAM));
	 $voornaam					=(string)clean(trim($row->VOORNAAM));
	 $actief 					=(int)trim($row->DV_ACTIEF);
	 $van   					=date('Y-m-d', strtotime($row->DV_VAN));	
	 $tot   					=date('Y-m-d', strtotime($row->DV_TOT));
	 $vestgingsplaats   		=(int)clean(trim($row->VWK_VESTIGINGSPLAATS_FK));	
	 $vakgroepID   				=(int)trim($row->VAKGROEPID);
	 $vakgroep 					=(string)clean(trim($row->VAKGEBIEDGROEP));
	 
	 
	 $insql='INSERT INTO mssql_personeel_vakken (`leerkrachtID`, `naam`, `voornaam`, `actief`, `van`, `tot`, `vestigingsplaats`, `vakgroepID`, `vakgroep`) VALUES ('.$leerkrachtID .', "'.$naam .'", "'.$voornaam.'", "'.$actief.'", "'.$van.'", '. $tot .', '.$vestgingsplaats  .', "'.$vakgroepID.'", "'.$vakgroep.'");';
	 //$inres=mysqli_query($mysql, $insql);
	  $inres=mysql_query($insql);
	 if(!$inres){echo('<br/>ERROR: '.$insql);}
}


	//mssql_close();
 


//Define directie members
 $sql='UPDATE mssql_personeel_persoon as t1, user2title as t2
 		SET t1.Hoofdvakgroep = 0, t1.Vakgebied = "Directie" 
 		WHERE t1.DokeosLogin=t2.username
		AND t2.title LIKE "%Directeur%"  ' ;
	 
		//SELECT title, FROM user2title WHERE code = "D'.$id.'" ';
	 //$inres=mysqli_query($mysql, $insql);
	  $res=mysql_query($sql);
	 if(!$res){echo('<br/>ERROR: '.$sql);}
 
	
	
	/* UPDATE Authors AS a, AuthorArticle AS ab, Articles AS b
SET AuthorLastName='Wats'
WHERE a.AuthID=ab.AuthID AND ab.ArticleID=b.ArticleID
   AND ArticleTitle='AI';

*/
	 
//----------------------------------------------------------------------------------------------------
// start sync MODULE VARIANTEN  ---------------------------------------------------------
//----------------------------------------------------------------------------------------------------

 //--------------------------------------------------------------------------------------------------
	 
	$sql='SELECT * FROM VW_WEBSITE_IMV';
	//$res=sqlsrv_query($mssql_conn, $sql);
	$res=ibase_query($mssql_conn, $sql) or die('ibase IMV select failed! ');
	//$res = odbc_exec($mssql_conn, $sql);
	 
	 
	
	if($res){
		$query1="truncate mssql_sync_module_variant;"; 
		$result=mysql_query($query1) ; 
	}
	else{
	  print 'module_variant select failed<br>'; 
	}
	
	
	while($row=ibase_fetch_object($res))
	{
		 
		$inrichtingsID     		=(int)trim($row->INRICHTINGSID);  
	    $courseTitle 		=(string)trim(clean($row->VWK_OMSCHRIJVING));
	    $moduleVariantID					=(int)trim(clean($row->IMV_ID)); 
	    $moduleVariantTitle 		=(string)trim(clean($row->IMV_OMSCHRIJVING));
	  
	 
	 
	 $insql='INSERT INTO mssql_sync_module_variant (inrichtingsID, courseTitle, moduleVariantID, moduleVariantTitle) VALUES
   ('.$inrichtingsID .', "'.$courseTitle .'",  '.$moduleVariantID.' , "'.$moduleVariantTitle.'" );';
    
	 //$inres=mysqli_query($mysql, $insql);
	  $inres=mysql_query($insql);
	 if(!$inres){echo('<br/>IMV insert:'.mysql_error());}
}

 




	 

//----------------------------------------------------------------------------------------------------
// END sync PERSONEEL PHOTOBOOK TABLES  ---------------------------------------------------------
//----------------------------------------------------------------------------------------------------









//@todo - this seems to be failing
synclog_add('INFO', 'MSSQL SYNC', 'MSSQL database synchronized with chamilo database','MSSQL') or print('<br/><br/>'.mysqli_error($mysql));







mssql_close();
echo('<br>MSSQL proces terminated');
//--------------------------------------------------------------------------------------------------

//redirect to next sync step
//header("Location: http://86.39.161.68/sync/sync.course.php");
 


?>
