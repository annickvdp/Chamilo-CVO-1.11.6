<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>create dokeos reservation periods</title>
</head>

<body>

<?
 
 
$incpath='C:/Inetpub/wwwroot/Dokeos/main/inc/';
//--------------------------------------------------------------------------------------------------
//include files
//--------------------------------------------------------------------------------------------------
require_once($incpath.'global.inc.php');
//--------------------------------------------------------------------------------------------------
 
$mysql=mysqli_connect($_configuration['db_host'], $_configuration['db_user'], $_configuration['db_password'], $_configuration['main_database']) or die;
 
//--------------------------------------------------------------------------------------------------
 
//--------------------------------------------------------------------------------------------------
function getDatesInRange($start_date,$end_date,$date_format='Y-m-d'){
	if ($start_date != NULL && $end_date != NULL && $start_date <= $end_date){
		 $i = 0;
		while ($current_date <> $end_date) {
			 $temp_time_string = $start_date." + ".$i." days";
			 $time_stamp_temp = strtotime($temp_time_string);
			 
			 $i++;
			 $current_date= date($date_format,$time_stamp_temp);
			 $output[] = $current_date;
		}
	return $output;
	} else { echo 'ERROR: date range error<br/><br/>'; exit;}
	
}


function getVacationDays(){
	
	$vacation_days=array();
	$sql='select * from dokeos_main.mssql_vakantiedagen ;';
	//$sql='INSERT INTO mssql_category (name, code) VALUES ("'.$name.'", "'.$code.'");';
	$res=mysql_query($sql) or print(mysql_error());
	if (mysql_num_rows($res)==0){ echo 'ERROR: no vacation days found<br/><br/>'; exit;}
	 
		while($row=mysql_fetch_array($res))	{
			//print_r($row);
			$days = getDatesInRange($row['start'],$row['end']);
			$vacation_days = array_merge($vacation_days,$days);
			/*
			foreach($days as $row_days){
			
				$vacation_days[]=$row_days;
			}
			*/
		 
		}
		return $vacation_days;
		 
		
}
 
$vacation_days = getVacationDays();
 
print  '<br/><br/>Vacation Days:<br/>';
print_r ($vacation_days);

 



/** * Get Mondays and Sundays * * Get monday, sunday, last monday & last sunday * Example usage: * 
// to retreive the dates using today as starting point * $mondaysAndSundays = getMondaysAndSundays(); * 
// to retreive the dates using a custom date as starting point * $mondaysAndSundays = getMondaysAndSundays('1987-04-14'); * 
* @param date $offset Provide a date from where to calculate from in strtotime() translatable format. If none is given, today's date will be used. 
* * @return array * 
*/

function getMondaysAndSundays($offset=false){   
	if(!$offset) $offset = strtotime(date('Y-m-d')); 
	else $offset = strtotime($offset);   
	// this week 
	if(date('w',$offset) == 1) { 
		$mas['monday'] = date('Y-m-d',$offset);
	}else{ 
		$mas['monday'] = date('Y-m-d',strtotime("last Monday",$offset)); 
	}  
	 
	if(date('w',$offset) == 6){ 
		$mas['sunday'] = date('Y-m-d',$offset);
	}else{ 
		$mas['sunday'] = date('Y-m-d',strtotime("next Sunday",$offset)); 
	}   
	 
	// last week 
	if(date('w',$offset) == 1){ 
		$mas['lastmonday'] =  date('Y-m-d',strtotime('-1 week',$offset)); 
	}else{
		$mas['lastmonday'] = date('Y-m-d',strtotime('-1 week', strtotime(date('Y-m-d',strtotime("last Monday",$offset))))); 
	}   
	if(date('w') == 6) { 
		$mas['lastsunday'] = date('Y-m-d',strtotime('-1 week',$offset)); 
	}else{ 
		$mas['lastsunday'] = date('Y-m-d',strtotime("last Sunday",$offset));
	}  
	 
	return $mas;
  
  
} 
 
 // table veriables
 //DATABASE: dokeos_Reservation
 //TABLE: Reservation
 
// docenten foto - CLASS_ID=454
// studio id = 21

 
 
 
 
 // 0 for sunday through 6 for saturday //$period_input[weekdaynumber][poperty]
$periods_input[0]['allow'] = $allow_sundays = false;
$periods_input[0]['start_td'] = $sunday_start_time = '9:00';
$periods_input[0]['end_td'] = $sunday_end_time = '22:00';

$periods_input[1]['allow'] = $allow_mondays = true;
$periods_input[1]['start_td'] = $monday_start_time = '9:00';
$periods_input[1]['end_td'] = $monday_end_time = '22:00';

$periods_input[2]['allow'] = $allow_tuesdays = false;
$periods_input[2]['start_td'] = $tuesday_start_time = '9:00';
$periods_input[2]['end_td'] = $tuesday_end_time = '22:00';

$periods_input[3]['allow'] = $allow_wednesdays = true;
$periods_input[3]['start_td'] = $wednesday_start_time = '9:00';
$periods_input[3]['end_td'] = $wednesday_end_time = '12:00';

$periods_input[4]['allow'] = $allow_thursdays = true;
$periods_input[4]['start_td'] = $thursday_start_time = '9:00';
$periods_input[4]['end_td'] = $thursday_end_time = '22:00';

$periods_input[5]['allow'] = $allow_fridays = true;
$periods_input[5]['start_td'] = $friday_start_time = '9:00';
$periods_input[5]['end_td'] = $friday_end_time = '18:00';

$periods_input[6]['allow'] = $allow_saturdays = true;
$periods_input[6]['start_td'] = $saturday_start_time = '9:00';
$periods_input[6]['end_td'] = $saturday_end_time = '12:00';

$allow_holidays = false;


//set default table values
$id = NULL; //INT - autoincrement
$subid = NULL; // ??? // MUST BE INCREMENTED
$item_id = 21;  // unique item id - dokeos_reservation.item.id
$auto_accept = 1;  // always 1
$max_users = 1;  //always 1
$start_at = NULL;  // START DATE AND TIME
$end_at = NULL;  // END DATE AND TIME
$subscribe_from = 0 ; // always 0
$subscribe_until = 0 ; // always 0
$subscribers = 0 ; // always 0
$notes = NULL ; //
$timepicker = 1 ; // always 1
$timepicker_min = 0 ; // always 0
$timepicker_max = 0 ; // should be set for 4 hours

 

// set current item test values
$id = NULL; //INT - autoincrement
$item_id = 21;  // unique item id - dokeos_reservation.item.id
$subid = 1+(($item_id-2)*122); // ??? // MUST BE INCREMENTED

$auto_accept = 1;  // always 1
$max_users = 1;  //always 1
$start_at = NULL;  // START DATE AND TIME
$end_at = NULL;  // END DATE AND TIME
$subscribe_from = 0 ; // always 0
$subscribe_until = 0 ; // always 0
$subscribers = 0 ; // always 0
$notes = NULL ; //
$timepicker = 1 ; // always 1
$timepicker_min = 0 ; // always 0
$timepicker_max = 4*60 ; // max reservation time in minutes




//$startDate = date(&quot;Y-m-d&quot;, mktime(0, 0, 0, 2, 23, 2008));
//$endDate = date(&quot;Y-m-d&quot;, mktime(0, 0, 0, 2, 23+20, 2008));

$start_date_string = "Sept 1, 2010";
$end_date_string = "June 30, 2011";

 //format dates
 $startDate = date("Y-m-d", strtotime($start_date_string));
 $endDate = date("Y-m-d",  strtotime($end_date_string));
 
 $dates_array = getDatesInRange($startDate,$endDate);
 print '<br/><br/>Dates including Vacation Days:<br/>';
 print_r ($dates_array);
 
 if ($allow_holidays == false){
	 $vacation_days = getVacationDays();
	 //$dates_array = array_diff($dates_array,$vacation_days);
	 
	 print '<br/><br/>Dates exclucing Vacation Days:<br/>';
 	// print_r ($vacation_days);
	 
	 
	 
	 $result = array_values(array_diff($dates_array, $vacation_days));
     print_r ($result);

  }
  

//show table headers
echo "<br/><br/><strong>"."id;subid;item_id;auto_accept;max_users;start_at;end_at;subscribe_from;subscribe_until;subscribers;notes;timepicker;timepicker_min;timepicker_max;"."</strong><br/>";


 
foreach ($dates_array as $current_date){
  // 0 for sunday through 6 for saturday
 $current_weekday_num = intval(date("w", $current_date));
 
 if ($periods_input[$current_weekday_num]['allow'] == true){
	 
	 
	 //datums in formaat jjjj-mm-dd uu:mm:ss
	 //$current_date = date("Y-m-d", $row);
	 
	 $start_at = $current_date." ".$periods_input[$current_weekday_num]['start_td'];
	 $end_at = $current_date." ".$periods_input[$current_weekday_num]['end_td'] ; 
	 
	// echo "<strong>"."subid;item_id;auto_accept;max_users;start_at;end_at;subscribe_from;subscribe_until;subscribers;notes;timepicker;timepicker_min;timepicker_max;"."</strong><br/>";
       echo "$id;$subid;$item_id;$auto_accept;$max_users;$start_at;$end_at;$subscribe_from;$subscribe_until;$subscribers;$notes;$timepicker;$timepicker_min;$timepicker_max;"."<br/>";

	 
 }
 
 
} 



?>

</body>
</html>