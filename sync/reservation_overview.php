<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Overzicht Reserveringen</title>


<?





$site = 1;

$current_time_stamp = date("Y-m-d H:i:s");
	$current_date = date("Y-m-d");
 	$current_time = date("H:i:s");
	$standard_time = date("H:i");
	$xls_time_stamp = date("Y-m-d")."T".date("H:i:s")."Z"; 
 
  $output='';
 
  

?>
<link href="site.css" rel="stylesheet" type="text/css" />

<style type="text/css">

 #content{
	 width:750px;
	 margin-left:auto;
 	 margin-right:auto;
	 padding:20px;
	 }
  td {
	  padding:3px;
	  
	  }	
  .column_titles td{
	  
	  font-weight:700
	  
	  }	 
	.item_name{
		color:#006;
		font-weight:700;
		}
	.start_reservation{
		color: #090;
		font-weight:700;
		}
	.end_reservation{
		color: #900;
		font-weight:700;
		}
	  
	    

</style>


<script type="text/javascript">
<!--  http://www.htmlforums.com/client-side-scripting/t-using-javascript-confirm-box-with-php-80585.html
function confirmation(ID,Description) {
	var answer = confirm("Delete entry "+Description+" ?")
	if (answer){
		alert("Entry Deleted")
		window.location = "reservation_overview.php?act=reservationdelete&id="+ID;
	}
	else{
		alert("No action taken")
	}
}
// -->
</script>


</head>


<?
//define include path (must be absolute for scheduled tasks)
//--------------------------------------------------------------------------------------------------
//$incpath=dirname(realpath('.')).'\\main\\inc\\';
$incpath='C:/Inetpub/wwwroot/Dokeos/main/inc/';
//--------------------------------------------------------------------------------------------------
//include files
//--------------------------------------------------------------------------------------------------
require_once($incpath.'global.inc.php');
//--------------------------------------------------------------------------------------------------
//connect to the MSSQL database
//$mysql=mysqli_connect('127.0.0.1', 'cvoleuven', 'cvoleuven', 'dokeos_main') or die;
//$mysql=mysql_connect($_configuration['db_host'], $_configuration['db_user'], $_configuration['db_password'], $_configuration['main_database']) or die;
mysql_connect($_configuration['db_host'], $_configuration['db_user'], $_configuration['db_password']);
  //--------------------------------------------------------------------------------------------------
$dbs= "dokeos_reservation";
mysql_select_db($dbs,$mysql_conn);
//-----------------------------------------------------------------------------------------


$sql = "SELECT dokeos_reservation.category.name as category_name, dokeos_reservation.item.name as item_name, dokeos_reservation.subscription.start_at as start_reservation, dokeos_reservation.subscription.end_at as end_reservation, dokeos_main.user.lastname, dokeos_main.user.firstname, dokeos_main.user.email, dokeos_reservation.subscription.dummy FROM dokeos_reservation.subscription left join dokeos_reservation.reservation on dokeos_reservation.subscription.reservation_id = dokeos_reservation.reservation.id left join dokeos_reservation.item on dokeos_reservation.reservation.item_id = item.id
left join dokeos_main.user on dokeos_reservation.subscription.user_id = dokeos_main.user.user_id left join dokeos_reservation.category on dokeos_reservation.item.category_id = dokeos_reservation.category.id
order by category_name, item_name, start_reservation";

 
 $result=mysql_query($sql);
if (!$result){print ("sql query failed".mysql_error()); exit;}

?>

<body>
<div id="content">
<h2>Overzicht Reserveringen - <? print $current_date ?></h2>
<?
  
  
	
  
	
	 
 
 	 
  
   
   $count = 0;
   $current_category_name = '';
   $current_item_name = '';
   $cell_bgcolor1='#ffffff';
   $cell_bgcolor2='#f1f1f1';
   
   
    $output = '<table>';
   
   while($row=mysql_fetch_array($result)) 
 {
		
		 
		// print_r($row);
		// $temp_counter++;
	 
	  
		
	 
	  
	  if ($row['category_name']!=$previous_category_name){$cell_bgcolor=$cell_bgcolor1;  
	  		$output.='<tr bgcolor='.$cell_bgcolor.'><td valign="top" colspan=5><br><h1>'.$row['category_name'].'</h1></td></tr><tr class="column_titles" > <td >Item</td><td>Begin reservation</td><td>End reservation</td><td>Naam</td><td>Voornaam</td><td>Email</td><td>&nbsp;</td></tr><tr>' ;}
      else{ $output.='<tr bgcolor='.$cell_bgcolor.'>';}
	  
	   if ($row['item_name']!=$previous_item_name){  
	  		$output.='<td valign="top" class="item_name" ><strong>'.$row['item_name'].'</strong></td>' ;}
      else{
		  $output.='<td>&nbsp;</td>'  ;
	  }
	 	$output.='
         
     	<td  valign="top" class="start_reservation">'.$row['start_reservation'].'</td>
    	 <td  valign="top" class="end_reservation">'.$row['end_reservation'].'</td>
		 <td  valign="top">'.$row['lastname'].'</td>
		  <td  valign="top">'.$row['firstname'].'</td>
		  <td  valign="top"><a href="mailto:'.$row['email'].'">'.$row['email'].'</td>
		  <td><a href="javascript:confirmation('.$row['dummy'].','.$row['item_name'].' - '.$row['start_reservation'].')">delete</a>
		   <td><a href="javascript:confirmation(214,Studio - 2011-03-17 09:00:00)">delete</a> 

</td>
		  
     </tr>';
	   
	  
	  $previous_category_name=$row['category_name'];
	  $previous_item_name=$row['item_name'];
	  
	  if($cell_bgcolor==$cell_bgcolor1){$cell_bgcolor=$cell_bgcolor2;}
	  else{$cell_bgcolor=$cell_bgcolor1;}
  } 
  
  $output.='<tr bgcolor='.$cell_bgcolor1.'><td>&nbsp;</td></tr>';
  
  $output.='</table>
 
 <br /><br /><br />
 ';


/*

$File = "boekenlijst.txt";
$fh = fopen($File, 'w');// or die("can't open file");
fwrite($fh, $output );
fclose($fh);

unlink("boekenlijst.xls");
rename("boekenlijst.txt","boekenlijst.xls" );
*/	   
print $output;



?>
</div>
</body>
</html>