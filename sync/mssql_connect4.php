<?

 // simple connection - with website username
 $mssql=mssql_connect('databank.cvoleuven.be,1433', 'website2', 'ngnageot!etisbew') or die('Database connection failed');
 mssql_select_db('test3', $mssql)or die('Database selection failed');

print ('I think we are good');



//---------------------------------------------------------------------------------------------------------
$sql='SELECT * FROM OVERZICHThuidigeBOEKENvoorWEBSITE';
$res=mssql_query($sql, $mssql);
while($row=mssql_fetch_array($res))
{print_r($row); print_r('<br>');

/*
 $code                =(string)strtoupper(trim($row['code']));
 $title               =(string)trim($row['title']);
 $category_code       =(string)trim($row['category_code']);
 $course_language     ='dutch';
 $username_professor  =(string)trim($row['username_professor']);
 $db_prefix           ='dokeos_course_';
 $expiration_dt       =strtotime($row['expiration_dt']);
 $disk_quota          =50000000;
 $visibility          =1;
 $subscribe           =0;
 $unsubscribe         =0;
 $insql='INSERT INTO mssql_course (code, title, category_code, course_language, username_professor, db_prefix, expiration_dt, disk_quota, visibility, subscribe, unsubscribe) VALUES ("'.$code.'", "'.$title.'", "'.$category_code.'", "'.$course_language.'", "'.$username_professor.'", "'.$db_prefix.'", FROM_UNIXTIME('.$expiration_dt.'), '.$disk_quota.', '.$visibility.', '.$subscribe.', '.$unsubscribe.');';
 $inres=mysqli_query($mysql, $insql);
 if(!$inres){echo('ERROR: '.$insql.'<hr>');}
 */
}


//---------------------------------------------------------------------------------------------------------


 
?>