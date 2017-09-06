<?php

 exit;

//connection for cvo dokeos site
$host='localhost'; $user='root'; $password='cvo382';

//connect
$mysql_connection=mysql_connect($host, $user, $password) or die('Database Server Connection Failed_');

if($mysql_connection){print('connected to host<br><br>');};


mysql_select_db("dokeos_main", $mysql_connection) or die('db selection failed_');

if(mysql_select_db){print('connected to database<br><br>');};

mysql_query("UPDATE dokeos_main.user SET email = '' 
			WHERE  email LIKE 'geen'
			OR email LIKE 'geen@geen.be'
			OR email LIKE 'geen.mail@cvoleuven.be'
			OR email LIKE 'niemand@cvoleuven.be'
			")or die(mysql_error());
	

$result = mysql_query("SELECT * FROM dokeos_main.user WHERE email LIKE '' ; ")or die(mysql_error());
			
			
 
print  "<strong>Users without dokeos email: <br><br>Student Code, Username, Current Dokeos Email, New Dokeos Email, Status</strong><BR><BR>";
	 
while($row = mysql_fetch_array( $result )) {
	// Print out the contents of each row into a table
	echo $row['code'];
	 echo ","; 
	echo $row['username'];
	 echo ",";
	echo $row['email'];
	echo ",";
	
	$current_user_code = $row['code'];
	 
	print $current_email_for_update ; 	
	$result2 = mysql_query("SELECT * FROM dokeos_main.mssql_user WHERE   code   ='".$current_user_code."' LIMIT 1  ;")or die(mysql_error());
			 
	while($row2 = mysql_fetch_array( $result2 )) {
		// Print out the contents of each row into a table
		
		echo $row2['email'];
		echo ",";
		
		if($row2['email']!==''){
		
				$result3 = mysql_query("UPDATE dokeos_main.user SET email = '".$row2['email']."' 
								WHERE code='".$current_user_code."' LIMIT 1  ;")or die(mysql_error());
				if($result3){echo UPDATED;}
		}
		
		
		
	} echo "<br>"; 
	
} 


?> 