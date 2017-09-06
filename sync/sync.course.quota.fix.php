<?php


 exit;
//connection for cvo dokeos site
$host='localhost'; $user='root'; $password='cvo382';

$current_course_code="";
$total_errors="";
$fixed_errors="";

//connect
$mysql_connection=mysql_connect($host, $user, $password) or die('Database Server Connection Failed_');

if($mysql_connection){print('connected to host<br><br>');};


mysql_select_db("dokeos_main", $mysql_connection) or die('db selection failed_');

if(mysql_select_db){print('connected to database<br>');};

$query1="SELECT * FROM dokeos_main.course WHERE disk_quota<50000000";

$result=mysql_query($query1) or die('query1 failed_<br>');
//if($result){print('query1 succeeded_<br>');};

while($row = mysql_fetch_array($result)){
			$current_course_code = $row['code'];print('<br>');
			$total_errors++;
			print($current_course_code);print(' ');print($row['disk_quota']);
			mysql_query("UPDATE dokeos_main.course SET disk_quota = '50000000'
WHERE code = {$current_course_code}");
						
}
print('<br>');print('<br>');		
print('Total erros found and fixed: ');print($total_errors);		

?> 