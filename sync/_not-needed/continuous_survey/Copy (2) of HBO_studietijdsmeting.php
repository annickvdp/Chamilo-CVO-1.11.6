<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<? 
//default data -----------------------------------------
$page_title = 'Studietijdmeting HBO v2' ;
$exclude_teachers = false;
//SURVEY list
$enquete[][] = array();
$enquete[0]['enquete_cursus_code'] = 'enqhbostudietijd1'; 
$enquete[0]['enquete_code'] = 'ENQ_HBOSTUDIETIJD_1A';

$enquete[1]['enquete_cursus_code'] = 'enqhbostudie2a'; 
$enquete[1]['enquete_code'] = 'ENQ_HBOSTUDIETIJD_2A';

$enquete[2]['enquete_cursus_code'] = 'enqhbostudie2b'; 
$enquete[2]['enquete_code'] = 'ENQ_HBOSTUDIETIJD_2B';
 

//ESTIMATED HOURS PER MODULE
//10-11 Sem1
$geraamde_uren['10RPoPersonenenfamilierechtDoAvKristelL'] = 50 ;
$geraamde_uren['10RPnStrafrechtDiAvJohanL'] = 33 ;
$geraamde_uren['10RPdToegepasteinformaticaDoAvAnnKL'] = 50 ;

$geraamde_uren['10BHsBasisBoekhoudenDoVMLudwigKL'] =  NULL ;
$geraamde_uren['10BHsKostprijsberekeningDoAvAnnemieL'] =  NULL ;

//10-11 Sem2
$geraamde_uren['11RPjVerbintenissenDoAvKristelL'] = 33 ;
$geraamde_uren['11RPjadministratiefrechtWoAvMattiasL'] = 66 ; 
$geraamde_uren['11RPjseminarieZaVmKristelL'] = 33 ;  // enqhbostudietijd 2B

$geraamde_uren['11BHfKostenbel_BudgetAnnemieDoAvL'] =  NULL ;
$geraamde_uren['11BHfAanvullendBoekhoudenDoAvLudwigL'] =  NULL ;
$geraamde_uren['11BHfAanvullendBoekhoudenDoVmLudwigKL'] = NULL ; 




?>
<title><? echo $page_title; ?></title>

<style>
* { margin:0; padding:0; }
p { margin:5px 0 10px 0; }
div { margin:10px 0px; }
body{margin:0;font-family:Arial, Helvetica, sans-serif;font-size:12px;background:#333;}
#studietijdmeting_menu{margin:0px 0px;font-weight:700;width:330px;float:left;}
#studietijdmeting_menu ul{font-size:13px;list-style:none;display:block;background: #ccc;padding:4px 0px 0px 0px;}
#studietijdmeting_menu ul li{margin:4px;}
#studietijdmeting_menu ul ul a{color: #B30000;padding:4px 4px 4px 4px;font-size:11px;}
#studietijdmeting_menu ul ul a:visited{color: #B30700;}


#studietijdmeting_menu ul ul{background: #999}
#container{margin:0;width:100%;background:#EFEFEF;} 
#header {margin:0;background:#333;color:#CCC;height:60px;width:100%;}
#header h1{margin:0px 20px;diplay:inline;width:330px;height:60px;float:left;}

#content{margin:0px 15px;width:100%;}
#studietijdmeting_results{float:left;margin:15px 30px 15px 25px;font-weight:700;font-size:14px;}
#footer{height:15px;width:100%;clear:both;margin-top:25px;}
#pie_chart{margin:20px 20px;}
.estimated_hours{color:#999;}

</style>
</head>
<body>
<?php





//connect
$host='localhost'; $user='root'; $password='cvo382';
$mysql_connection=mysql_connect($host, $user, $password) or die('Database Server Connection Failed');
mysql_select_db("dokeos_main", $mysql_connection) or die('Database Selection Failed');


//$survey_list = array( ) ;
//$survey_list = 'enqhbostudietijd1';
//@todo = = next step ----------------------------------------- !!!!!!!!!!!!!!!!!!!!!!!!!!!
/*

for each participant - fetch survey code - gather participant list

*/

$output_menu = '';


foreach($enquete as $current_enquete_array){

//define key veriables
//print('<br>');print('<br>');print('<br>');
//print '<h2>';
//print
$current_enquete =$current_enquete_array['enquete_cursus_code'];
//print('<br>');
//print '</h2>';
$Enquete_Code = $current_enquete_array['enquete_code'];



// ----------------------------------------------------------------------------------------


//define key veriables
// @todo THESE VALUES WILL NEED TO BE DYNAMIC FOR THE NEXT VERSION if there are different version of survey
//$current_enquete ='enqhbostudietijd1'; //course code where enquete is
//$current_survey_Code = 'ENQ_HBOSTUDIETIJD_1A'; //enquete code within course

//$test_mode = 1;
$anonymous = 1; // 1=yes, 0=no

// DON'T FORGET TO SPECIFY WHICH QUESTIONS ARE OPEN ONES BELOW --------------------------------
// open questions are handled a bit differently  // doesn't apply at the moment for this survey

// @todo  - don't need to call this info each time -  - should pass array with $_POST

//GET INFO OF COURSE
$query1="select * from   $current_enquete.survey_participants left join dokeos_main.course on ($current_enquete.survey_participants.course_name = dokeos_main.course.title) order by dokeos_main.course.category_code, dokeos_main.course.title;";
$previous_category_code = NULL ;

 

$rowcount = 0;
$result=mysql_query($query1) or die(mysql_error());
while($row = mysql_fetch_array($result))
  {
  if ($previous_category_code != $row['category_code']){  
  			if($rowcount>0){$output_menu .= '</UL></UL>';}
  			
  			$output_menu .= '<UL><LI>'.$row['category_code'].' ['.$Enquete_Code.']'.'</LI><UL class="studietijdmeting_submenu">'  ; 
  	}
    $enquete_cursus_code[$row['code']] = $current_enquete ;
	$enquete_code[$row['code']] = $Enquete_Code ;
	 
	$output_menu .= '<LI><a href="'.htmlentities($_SERVER['PHP_SELF']).'?'.'course_code='.$row['code'].'&'.'course_title='.$row['title'].'&'.'course_teacher='.$row['tutor_name'].'">'.$row['title'].'_'.$row['code'].'</a></LI>'; 

  $previous_category_code = $row['category_code'];
  $rowcount ++;
  }


$output_menu .= '</UL></UL>';

}
//set default veriables before using



if ($_GET){
	
	$selected_course_code = htmlentities($_GET['course_code']);
	$selected_course_title = htmlentities($_GET['course_title']);
	$selected_course_teacher = htmlentities($_GET['course_teacher']);
	
	$selected_survey_code =  $enquete_cursus_code[$selected_course_code] ;
	$selected_survey = $enquete_code[$selected_course_code];
	
	 
	$display_results = true;
}
else {
	
	$display_results = false;
	
	}


//$TODO results only need to be queried if course changes (but this will be most of the time)

if($display_results){
	
	
	//GET DATASET FOR CURRENT CLASS
	
	//TOTAL COURSE STUDENTS
	$query2="select * from dokeos_main.course
inner join dokeos_main.course_rel_user on (course.code = course_rel_user.course_code) where   ";
if($exclude_teachers){$query2.="course_rel_user.status <> 1 and";} 
$query2.=" code = '$selected_course_code' ; ";
    $result2 = mysql_query($query2) or die('query2 failed'.mysql_error().'<br><br>'.$query2);
	//print $query2;
	
	$selected_course_total_students = mysql_num_rows($result2)   ; 
	
	//TOTAL STUDENTS WHO PARTICIPATED    
 	$query3="SELECT dokeos_course_$selected_course_code.survey_invitation.invitation_code, dokeos_course_$selected_course_code.survey_answer.* FROM  dokeos_course_$selected_course_code.survey_invitation left join dokeos_course_$selected_course_code.survey_answer on  (dokeos_course_$selected_course_code.survey_answer.user = dokeos_course_$selected_course_code.survey_invitation.invitation_code)
	left join dokeos_course_$selected_course_code.survey on  (dokeos_course_$selected_course_code.survey_answer.survey_id = dokeos_course_$selected_course_code.survey.survey_id) 
 
  INNER JOIN dokeos_main.user ON (survey_invitation.user = user.user_id) 
 
 WHERE "; 
 if($exclude_teachers){$query3.=" course_rel_user.status <> 1 and ";} 
$query3.="
     dokeos_course_$selected_course_code.survey_answer.answer_id IS NOT NULL
   AND dokeos_course_$selected_course_code.survey.code = '".$selected_survey."' 
 
GROUP BY dokeos_course_$selected_course_code.survey_invitation.invitation_code
ORDER BY survey_invitation.invitation_code DESC ; ";
    $result3 = mysql_query($query3) or die('query3 failed'.mysql_error());
	
	$selected_course_total_participants = mysql_num_rows($result3); 
	
	
	//TOTAL NUMBER OF ANWSERS
	$query4="SELECT * FROM  dokeos_course_$selected_course_code.survey_answer ; ";
    $result4 = mysql_query($query4) or die('query4 failed'.mysql_error().$query4);
	
	$total_answers = mysql_num_rows($result4); 
	//print $query4;
	
	
 
	//print '<br>'; 
	//print_r($question_list);
	
	// Get array with total values per question  - no teachers - no 'Week van' - current survey only
	
		$query5="  SELECT
  survey_question.survey_question,  SUM(CAST(survey_question_option.option_text AS UNSIGNED)) as total_hours, survey_answer.answer_id, survey_answer.survey_id, survey_answer.question_id, survey_question.question_id, survey_question_option.option_text, survey_answer.option_id, survey_answer.user, survey_invitation.user, user.status, user.username, survey.code
FROM
  dokeos_course_$selected_course_code.survey_question_option
  INNER JOIN dokeos_course_$selected_course_code.survey_question ON survey_question_option.question_id = survey_question.question_id
  INNER JOIN dokeos_course_$selected_course_code.survey_answer ON survey_answer.option_id = survey_question_option.question_option_id
  INNER JOIN dokeos_course_$selected_course_code.survey_invitation ON survey_answer.user = survey_invitation.invitation_code
  INNER JOIN dokeos_main.user ON survey_invitation.user = user.user_id
  INNER JOIN dokeos_course_$selected_course_code.survey ON survey_answer.survey_id = survey.survey_id
WHERE ";
  if($exclude_teachers){$query5.=" course_rel_user.status <> 1 and ";} 
$query5.=" survey_question.survey_question NOT LIKE 'Week Van' AND  survey_question.survey_question NOT LIKE 'Maand' AND  dokeos_course_$selected_course_code.survey.code = '".$selected_survey."' 
GROUP BY
  survey_question.survey_question  ";
	
	 
	/*	
		SELECT * FROM dokeos_course_2146414202.survey_answer left join dokeos_course_2146414202.survey on(survey_answer.survey_id = survey.survey_id) left join dokeos_course_2146414202.survey_question on (survey_answer.question_id = survey_question.question_id ) 
left join dokeos_course_2146414202.survey_question_option on (survey_question_option.question_option_id = survey_answer.option_id) 
*/
		
    $result5 = mysql_query($query5) or die('query5 failed'.mysql_error().$query5);
	
	$number_of_questions = mysql_num_rows($result5); 
	// print $query5;
	
	//print $number_of_questions ;
	
	
		// CALCULATE TOTAL HOURS
		
		$grand_total_hours = 0;
		while($row = mysql_fetch_array($result5)){
		 $grand_total_hours = $grand_total_hours + $row['total_hours'];			 
		}
          
		 $result5 = mysql_query($query5) or die('query5 failed'.mysql_error().$query5);
		// GET TOTALS PER QUESTION, CALCULATE AVERAGES, BUILD DATA ARRAY FOR CHART
		$counter=0;
		$grand_total_hours_avg = 0;

		while($row = mysql_fetch_array($result5)){
			
			//echo "Totaal ". $row['survey_question']. " = ". $row['total_hours'];
			$data_percentages[$counter] = round(($row['total_hours']/$grand_total_hours)*100) ;
			$data_totals[$counter] = round($row['total_hours']);
			$data_averages[$counter] =  round($row['total_hours']/$selected_course_total_participants);
			// echo $row['survey_question']. " = ". $data_averages[$counter]." (".$data_percentages[$counter]."%)<br />";
			$data_question_output_array[$counter] = $row['survey_question']. " = ". $data_averages[$counter]." (".$data_percentages[$counter]."%)<br />";
			
			$pie_chart_values .=  $data_percentages[$counter].',';
			$pie_chart_labels .=  $row['survey_question'].',';
			
			$grand_total_hours_avg = $grand_total_hours_avg + $data_averages[$counter];
			
			$counter++;
		}
        // strip last comma from $pie_chart_values
		$pie_chart_values = substr($pie_chart_values,0,-1);
		$pie_chart_labels = substr($pie_chart_labels,0,-1);
		
		
	 /*
	 
	while($row = mysql_fetch_array($result5))
  {
    //$totals_per_question[$row['survey_question']] = $totals_per_question[$row['survey_question']] + intval($row['option_text']) ;
    //$totals_per_question[$count] = $totals_per_question[$count] + intval($row['option_text']) ;
	 $totals_0 = $totals_0 + intval($row['option_text']) ;
	 //print $totals_0; print '<br>';
	
  }
		 $totals_0 = $totals_0/2; // @todo -------------------------------- EACH QUESTION OPTION WAS THERE 2X - -- MAKE SURE FOR NEXT TIME THAT THERE ARE unique enquetes, questions, and question options
	 $grand_total_hours = $grand_total_hours + $totals_0 ;
	
	print '<br><br>'.$question.': '.$totals_0  ; //$totals_per_question[$row['survey_question']] ;
	
	$avg_totals_0 = $totals_0/$selected_course_total_participants;
	print '<br><strong>Gemiddled '.$question.': '.$avg_totals_0.'</strong>' ; //$totals_per_question[$row['survey_question']] ;		
	$count++;
	
	
}
	*/
}
?>
	  <div id="container">
	  		<div id="header" style=''><h1><? echo $page_title; ?></h1>
            
            <div id="current_course_info"  >
            
            	 <h3><?
			 print $selected_course_title.'<br>'; 
			  
			print $selected_course_code.' - '; 	print $selected_course_teacher.'<br>'; 
			?></h3> 
            <h3 style="float:left;"><? echo date("d/m/Y"); ?></h3>
            
              
            </div> 
   

            </div>
            
	
	<div id='content'><? 
	
	?> <div id='studietijdmeting_menu'><?
print $output_menu;


?></div><?
 
	?><div id='studietijdmeting_results'><?
	
	
	foreach($data_question_output_array as $row){
	   print $row;	
		}			// echo $row['survey_question']. " = ". $data_averages[$counter]." (".$data_percentages[$counter]."%)<br />";

	
	//print '<br><br>Totaal Cursisten: '. ;
	print '<br>Totaal deelnemers: '.$selected_course_total_participants.' / '.$selected_course_total_students ;
	//print '<br>Totaal Vragen: '.$number_of_questions  ;
	//print '<br>Totaal Antwoorden: '.$total_answers  ;
	
		print '<br><br><strong>Gemiddelde uren cursist: '.$grand_total_hours_avg.'</strong>' ;
	print '<br><strong><span class="estimated_hours">Gemiddlede uren geraamde: '.$geraamde_uren[$selected_course_title].'</span></strong><br><br>' ;
	
	
	
	
	
   ?></div> 
   
   <div id='studietijdmeting_pie_chart'><div id="pie_chart"  ></div><br /><br />
   <script type="text/javascript" src="../../openflashchart/js/swfobject.js"></script> 	
<script type="text/javascript"> 
var so = new SWFObject("../../openflashchart/actionscript/open-flash-chart.swf", "ofc", "400", "300", "9", "#FFFFFF");
<? //so.addVariable("data", "../data-files/data-20.txt"); ?>
 
so.addVariable("variables","true");
 
so.addVariable("title","Uren per gedeelte (gemiddeld),{font-size:20px; color:#000000}");
so.addVariable("pie","60,#999999,font-size: 12px; #000000,bg_color:#FF3;");
so.addVariable("background","#ff3");
 
<? echo'so.addVariable("values","'.$pie_chart_values.'");' ?>
<? //so.addVariable("values","1,2,3,4"); ?>
so.addVariable("colours","#70cd1e,#67abf1,#f7ce3e,#f78732,#cb81f0");
so.addVariable("links","javascript:alert('15%25'),javascript:alert('25%25'),javascript:alert('60%25')");
<? if($grand_total_hours_avg>o){?> so.addVariable("pie_labels","contact,examen,oefeningen,studeren");<? }
   else{?> so.addVariable("pie_labels"," , , , ");<? }
?>
so.addVariable("tool_tip","#x_label#<br>Value: #val#%25");
 
so.addParam("allowScriptAccess", "always" );//"sameDomain");
so.write("pie_chart");
</script>
   </div>
   
  
   <?



?> </div>
</div>
<div id='footer'>
</div>
</body>
</html>
