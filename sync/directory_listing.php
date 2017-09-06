<?

//require_once('../main/inc/global.inc.php');
//require_once('../main/inc/lib/main_api.lib.php');
?>
<body>
<?

//$path = "./kaasenwijn/"; 
//echo "Folder $path = ".filesize_r($path)/1000 ." kilobytes"; 
if ( !function_exists('mime_content_type')) {
   

	 function mime_content_type($bestand) {
		$ext = end(explode('.',$bestand));
		switch($ext) {
		case "jpg":
		case "jpeg":
		return 'image/jpeg';
		break;
		case "png":
		return 'image/png';
		break;
		default:
		return 'text/plain';
		break;
		
		}

 	}
 }
 
 
function filesize_r($path){ 
  if(!file_exists($path)) return 0; 
  if(is_file($path)) return filesize($path); 
  $ret = 0; 
  foreach(glob($path."/*") as $fn) 
    $ret += filesize_r($fn); 
  return trim($ret); 
} 


function ByteSize($bytes) 
    {
    $size = $bytes / 1024;
    if($size < 1024)
        {
        $size = number_format($size, 2);
        $size .= ' KB';
        } 
    else 
        {
        if($size / 1024 < 1024) 
            {
            $size = number_format($size / 1024, 2);
            $size .= ' MB';
            } 
        else if ($size / 1024 / 1024 < 1024)  
            {
            $size = number_format($size / 1024 / 1024, 2);
            $size .= ' GB';
            } 
        }
    return $size;
    }

// Returns '19.28mb'
//print ByteSize('20211982'); 


  function getFileList($dir, $recurse=false, $depth=false)
  {
    # array to hold return value
    $retval = array();

    # add trailing slash if missing
    if(substr($dir, -1) != "/") $dir .= "/";

    # open pointer to directory and read list of files
    $d = @dir($dir) or die("getFileList: Failed opening directory $dir for reading");
    while(false !== ($entry = $d->read())) {
      # skip hidden files
      if($entry[0] == ".") continue;
      if(is_dir("$dir$entry")) {
        $retval[] = array(
          "name" => "$dir$entry/",
          "type" => filetype("$dir$entry"),
          "size" => filesize_r("$dir$entry"),// "size" => 0,
          "lastmod" => filemtime("$dir$entry")
        );
        if($recurse && is_readable("$dir$entry/")) {
          if($depth === false) {
            $retval = array_merge($retval, getFileList("$dir$entry/", true));
          } elseif($depth > 0) {
            $retval = array_merge($retval, getFileList("$dir$entry/", true, $depth-1));
          }
        }
      } elseif(is_readable("$dir$entry")) {
       
		 
		  	 $retval[] = array(
          "name" => "$dir$entry",
          "type" => mime_content_type("$dir$entry"),
          "size" => filesize("$dir$entry"),
          "lastmod" => filemtime("$dir$entry")
		
		   ); 
        
      }
    }
    $d->close();

    return $retval;
  }
  # single directory
   $dirlist = getFileList("../courses/");
 // $dirlist = getFileList("../courses");
  
  # include all subdirectories recursively
 // $dirlist = getFileList("./", true);

  # include just one or two levels of subdirectories
  //$dirlist = getFileList("./", true, 1);
  //$dirlist = getFileList("./", true, 2);
  
 
  echo "<table>\n";
  echo "<tr><th>Name </th><th>Type </th><th>Size </th><th>Last Mod. </th></tr>\n";
  foreach($dirlist as $file) {
    echo "<tr>\n";
    echo "<td>{$file['name']}</td>\n";
    echo "<td>{$file['type']}</td>\n";
	
	//$file['size']=ByteSize($file['size']);
//$formatted_filesize = ByteSize($file['size']); 
	//echo "<td>{ByteSize({$file['size']})}, </td>\n";
   echo "<td>{$file['size']}</td>\n";
    echo "<td>" . date("r", $file['lastmod']) ."</td>\n";
    echo "</tr>\n";
  }
  echo "</table>\n\n";



?>

