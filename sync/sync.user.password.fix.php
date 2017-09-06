<?
//--------------------------------------------------------------------------------------------------
//include files
//--------------------------------------------------------------------------------------------------
require_once('inc.sync.php');
require_once('func.user.php');
//--------------------------------------------------------------------------------------------------
//main program
//--------------------------------------------------------------------------------------------------
 print 'hello, here we go ';
// user_sync();  

// user_deactivate(); 
//user_add();

//-------------------------------------------------------------------------------------
// -----START reset PASSWORDS
//--------------------------------------------------------------------------------------------------
print '<br/><br/>securing personeelspagnia checkup';

print $sql='select * from chamilo2015.mssql_user where status = 5 and password > 0; ';  //some passwords are blank or negative integers -- must fix
print '<br/><br/>';	   
	   
	   
 $res=mysqli_query($mysql, $sql) or die (mysqli_error($mysql));
  
 if($res){
		
		
		
	    while ($row =mysqli_fetch_array($res)){
			 
			print  $sql = "update user set password = '".md5($row['password'])."' 
					where username = '".$row['username']. "'   ;";
			 print '<br/>';
					
			//$res2=mysql_query($sql,$mysql_conn);
			$res2=mysqli_query($mysql, $sql) or die (mysqli_error($mysql));
			 
						
		 }
		 
	}


//-------------------------------------------------------------------------------------
// -----END reset PASSWORDS
//--------------------------------------------------------------------------------------------------


//--------------------------------------------------------------------------------------------------
echo('<br>User proces terminated');
//--------------------------------------------------------------------------------------------------

//redirect to next sync step
//header("Location: http://86.39.161.68/sync/sync.class.php");



?>
