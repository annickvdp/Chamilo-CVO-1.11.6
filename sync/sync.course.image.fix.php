<?php 

exit;

//must be re-written for linix

$dir_to_create[] = '/document/images';
$dir_to_create[] = '/document/images/gallery';
$dir_to_create[] = '/document/audio';
$dir_to_create[] = '/document/flash';
$dir_to_create[] = '/document/video';




$image_dir = $dir.'/document/images/gallery';

	foreach(glob('../courses/*', GLOB_ONLYDIR) as $dir) 
	{ 
		 $folder_name = basename($dir); 
		
		 //$dirname = $_POST["DirectoryName"];     
    		 //$filename = "/folder/{$dirname}/"; 
 		
		 //$image_dir = $dir.'/document/images/gallery';
		  
		 foreach ($dir_to_create as $current_dir_to_create){
		  
		  	 $full_directory = $dir.$current_dir_to_create;	
				
			 if (file_exists($full_directory)) {     
				 echo "The directory {$full_directory} exists <br>";     
			 } else {     
				 mkdir("{$full_directory}", 0777);     
				  echo "The directory {$full_directory} was successfully created<br>";     
			 }   
			 
		 }
		
		 //echo '<option value="', $dir, '">', $dir, '</option>'; 
		 
	 }     
      
 	
?> 