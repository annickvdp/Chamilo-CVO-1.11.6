<?php


//define key veriables
$current_enquete_array[0]['enqhbostudietijd1']; 
$current_enquete_array[0]['ENQ_HBOSTUDIETIJD_1A'];

$current_enquete_array[0]['enquete_cursus_code']; 
$current_enquete_array[0]['enquete_code'];

$current_enquete_array[0]['enquete_cursus_code']; 
$current_enquete_array[0]['enquete_code'];
 

print('CONTINUOUS SURVEY RESET');print('<br>');print('<br>');

//localhost connection for testing
//$host='localhost'; $user='root'; $password='greed';

//connection for cvo dokeos site
$host='localhost'; $user='root'; $password='cvo382';
//connect
$mysql_connection=mysql_connect($host, $user, $password) or die('Database Server Connection Failed');

mysql_select_db("dokeos_main", $mysql_connection) or die('Database Selection Failed');

foreach($enquete[] as $current_enquete_array){

//define key veriables
 $current_enquete =$current_enquete_array['enquete_cursus_code']; 
 $Enquete_Code = $current_enquete_array['enquete_code'];


//query for participant directories
$participants = array( ); // initialize as empty array;
$participant_course_codes = array( ); // initialize as empty array;
$participant_course_names = array( ); // initialize as empty array;

$query1="SELECT * FROM $current_enquete.survey_participants;";

$result=mysql_query($query1);
$num=mysql_numrows($result);

$i=0;
while ($i < $num) {
	
	$course_name=mysql_result($result,$i,"course_name");
	//print($course_name);print('<br>');print('<br>');
	
	$participant_course_names[] = $course_name;
		
	$i++;
}




// DEBUG if(!$participant_course_names){print('NO PARTICIPANT COURSE NAMES');print('<br>');}


foreach ($participant_course_names as $n){
	//print($n);print('<br>');print($n);print('<br>');
	$query="SELECT * FROM dokeos_main.course WHERE title = '{$n}';";
	
	$result=mysql_query($query) or die(mysql_error());
	//DEBUG echo($result);print('<br>');
	$num=mysql_numrows($result);
	//print($num);print('_');
	//DEBUG echo($num);print('<br>');
	$i=0;
	while ($i < $num) {
		$db_name=mysql_result($result,$i,"db_name");
		$participants[] = $db_name;
		
		//print('db_name: ');print($db_name);print('<br>');
		
		$course_code=mysql_result($result,$i,"code");
		$participant_course_codes[] = $course_code;
		//DEBUG print('course_code: ');print($course_code);print('<br>');
		$i++;
	}
$n++;
}



foreach ($participant_course_codes as $c){	
		
		// - live mode
		$db='dokeos_course_'.$c;	
		
		// - test mode
		//$db=''.$c;	
		
		 
		
		//reset invitations for current enquete
		$query="SELECT * FROM dokeos_main.course_rel_user WHERE course_code = '{$c}';";
		$result=mysql_query($query) or die('course_code query failed');
		
		$reset_query="UPDATE $db.survey_invitation SET answered = '0' WHERE survey_code = '{$Enquete_Code}';";
		$reset_result = mysql_query($reset_query) or die('<br><br>invitation reset failed - '.mysql_error());
			 
		
}

} // end enquete loop
Mysql_close(); 



?> 