<?php
require_once('../../../pluginsConfig.php');
require_once(basePathPlugin.'includes/includes.php');
require_once('../../utils/removeFolders.php');

$con=mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

// Check connection
if (mysqli_connect_errno()) {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

//Delete exploration
function deleteRow($con){
	$id = $_POST['id'];
	$project_id = $_POST['project'];
	
	Delete('../../media/images/project'.$project_id.'/exhibits/'.$id.'/');

	mysqli_query($con,"DELETE FROM mbira_locations_has_mbira_exhibits WHERE mbira_exhibits_id='$id'");
	mysqli_query($con,"DELETE FROM mbira_areas_has_mbira_exhibits WHERE mbira_exhibits_id='$id'");
	mysqli_query($con,"DELETE FROM mbira_exhibits WHERE id='$id'");
}

//Update exploration
function updateRow($con){
	$id = $_POST['id'];
	$project_id = $_POST['project'];
	$name = mysqli_real_escape_string($con, $_POST['name']);
	$desc = mysqli_real_escape_string($con, $_POST['description']);
	$path = $_POST['path'];
	
	//Save image
	$uploaddir = '../../media/images/project'.$project_id.'/exhibits/'.$id.'/';

	//Use default image if no file provided
	if(isset($_FILES['file']['name'])){
		$mediaResult = mysqli_query($con, "SELECT thumb_path FROM mbira_exhibits WHERE id = '$id'");
		
		while($mediaRow = mysqli_fetch_array($mediaResult)) {
			unlink('../../' .$mediaRow['thumb_path']);
		}
		$filename = explode('.', basename($_FILES['file']['name']));

		$uniqid = uniqid();
		$uploadfile = $uploaddir .$uniqid.'.'.$filename[count($filename)-1];
		move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile);
		$path = 'media/images/project'.$project_id.'/exhibits/'.$id.'/'.$uniqid.'.'.$filename[count($filename)-1];
	}
	
	mysqli_query($con,"UPDATE mbira_exhibits SET name='$name', description='$desc', thumb_path='$path' WHERE id='$id'");
}

//Add new exploration
function createRow($con) {
	$projectId = $_POST['projectId'];
	$pid = $_POST['pid'];
	$name = mysqli_real_escape_string($con, $_POST['name']);
	$desc = mysqli_real_escape_string($con, $_POST['description']);
		
	//Create row in mbira_explorations
	mysqli_query($con,"INSERT INTO mbira_exhibits (project_id, pid, name, description) VALUES ('$projectId', '$pid', '$name', '$desc')");
	$id =  mysqli_insert_id($con);

	//Save image
	$uploaddir = '../../media/images/project'.$projectId.'/exhibits/'.$id.'/';

	if (!file_exists($uploaddir)) {
	    mkdir($uploaddir, 0775, true);
	}

	//Use default image if no file provided
	if(isset($_FILES['file']['name'])){
		$filename = explode('.', basename($_FILES['file']['name']));

		$uniqid = uniqid();
		$uploadfile = $uploaddir .$uniqid.'.'.$filename[count($filename)-1];
		move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile);
		$path = 'media/images/project'.$projectId.'/exhibits/'.$id.'/'.$uniqid.'.'.$filename[count($filename)-1];
	}else{
		$path = 'app/assets/images/Default.png';
	}

	//Add path to exhibit's row
	$sql = "UPDATE mbira_exhibits SET thumb_path='".$path."' WHERE id=".$id;
	mysqli_query($con, $sql);
	
	//Create and ingest exploration record to kora
	// $result = mysqli_query($con, "SELECT * FROM scheme WHERE schemeName = 'Exploration' AND pid = " . $pid);
	
	// while($row = mysqli_fetch_array($result)) {
		// $sid = $row['schemeid'];
	// }
	
	
	// $xml = Array();
	// $xml["Name"] = $name;
	// $xml["Description"] = $desc;
	// $xml["Direction"] = $dir;
	
	// "<Record>
	// <id>'$rid'</id> 
	// <Name>'$name'</Name> 
	// <Description>'$desc'</Description> 
	// <Longitude>'$lat'</Longitude> 
	// <Latitude>'$lon'</Latitude> 
	// </Record>"

	// $record = new Record($pid, $sid);
	// $record -> ingest($xml);
	echo $id;
}

function uploadHeader($con) {
	$id = $_POST['id'];
	$project_id = $_POST['project'];
	$uploaddir = '../../media/images/project'.$project_id.'/exhibits/'.$id.'/';

	
	//Use default image if no file provided
	if(isset($_FILES['file']['name'])){
		$mediaResult = mysqli_query($con, "SELECT header_image_path FROM mbira_exhibits WHERE id = '$id'");
		
		while($mediaRow = mysqli_fetch_array($mediaResult)) {
			unlink('../../' .$mediaRow['header_image_path']);
		}

		$filename = explode('.', basename($_FILES['file']['name']));

		$uniqid = uniqid();
		$uploadfile = $uploaddir .$uniqid.'.'.$filename[count($filename)-1];
		move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile);
		$path = 'media/images/project'.$project_id.'/exhibits/'.$id.'/'.$uniqid.'.'.$filename[count($filename)-1];
	}else{
		$path = 'app/assets/images/Default-Header.png';
	}
	mysqli_query($con,"UPDATE mbira_exhibits SET header_image_path='$path' WHERE id='$id'");
}

function getAll($con) {		
	if ($_POST['proj'] != null){
		$proj = $_POST['proj'];
		$results = mysqli_query($con, "SELECT * FROM mbira_exhibits WHERE project_id='$proj'");
	} else {
		$results = mysqli_query($con, "SELECT * FROM mbira_exhibits");
	}
	$resultsArray = Array();
	while($row = mysqli_fetch_array($results)) {
		array_push($resultsArray, $row);
	}
	echo json_encode($resultsArray);
}

function getInfo($con) {
	$id = $_POST['id'];	
	$results = mysqli_query($con, "SELECT * FROM mbira_exhibits WHERE id = ".$id);
	$exhibitArray = Array();
	while($row = mysqli_fetch_array($results)) {
		array_push($exhibitArray, $row);
	}
	echo json_encode($exhibitArray[0]);
}

function cleanDb($con, $id) {
	$locResult = mysqli_query($con,"SELECT file_path, thumb_path FROM mbira_exhibits WHERE id='$id'");
	while($locRow = mysqli_fetch_array($locResult)) {
		unlink("../../".$locRow['file_path']); //remove the file
		unlink("../../".$locRow['thumb_path']); //remove the file
	}
}

function saveThumbnail($con) {
	$id = $_POST['eid'];
	$project_id = $_POST['project'];

	$thumbCanvasData = $_POST['thumbCanvasData'];
	$thumbCropData = $_POST['thumbCropData'];
	$uploaddir = '../../media/images/project'.$project_id.'/exhibits/'.$id.'/';

	if ($_POST['type'] == 'new') {
		cleanDb($con, $id);
		$name = explode('.', basename($_FILES['file']['name']));

		$uniqid = uniqid();
		$uploadfile = $uploaddir .$uniqid.'.'.$name[count($name)-1];
		move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile);
		$file_path = 'media/images/project'.$project_id.'/exhibits/'.$id.'/'.$uniqid.'.'.$name[count($name)-1];

		$thumb_file_ext = explode('/', $_FILES['croppedThumbnail']['type'])[1];
		$thumb_uploadfile = $uploaddir .$uniqid.'_thumb.'.$thumb_file_ext;
		move_uploaded_file($_FILES['croppedThumbnail']['tmp_name'], $thumb_uploadfile);
		$thumb_file_path = 'media/images/project'.$project_id.'/exhibits/'.$id.'/'.$uniqid.'_thumb.'.$thumb_file_ext;	

		$sql = "UPDATE mbira_exhibits SET file_path='$file_path', thumbCanvasData='$thumbCanvasData', thumbCropData='$thumbCropData', thumb_path='$thumb_file_path' WHERE id=".$id;
		mysqli_query($con, $sql);

	} else {

		$locResult = mysqli_query($con,"SELECT file_path, thumb_path FROM mbira_exhibits WHERE id='$id'");
		while($locRow = mysqli_fetch_array($locResult)) {
			$file_path = $locRow['file_path'];
			unlink("../../".$locRow['thumb_path']); //remove the file
		}

		$f_path = explode('/', $file_path);
		$last_element = end($f_path);
		$temp_array = explode('.', $last_element );
		$uniqid = $temp_array[0];

		$thumb_file_ext = explode('/', $_FILES['croppedThumbnail']['type'])[1];
		$thumb_uploadfile = $uploaddir .$uniqid.'_thumb.'.$thumb_file_ext;
		move_uploaded_file($_FILES['croppedThumbnail']['tmp_name'], $thumb_uploadfile);

		mysqli_query($con, "UPDATE mbira_exhibits SET thumbCanvasData='$thumbCanvasData', thumbCropData='$thumbCropData' WHERE id='$id'");

	}

}

function addTo($con) {
	$piece = $_POST['piece'];
	$type = $_POST['type'];

	if ($type == 'loc') {
		mysqli_query($con,"DELETE FROM mbira_locations_has_mbira_exhibits WHERE mbira_locations_id='$piece'");
	} else {
		mysqli_query($con,"DELETE FROM mbira_areas_has_mbira_exhibits WHERE mbira_areas_id='$piece'");
	}

	
	$exhibits = json_decode($_POST['exhibits']);
		
	//Link areas and locations to exhibit
	foreach ($exhibits as $exhibit) {
		if ($type == 'loc') {
			mysqli_query($con,"INSERT INTO mbira_locations_has_mbira_exhibits (mbira_locations_id, mbira_exhibits_id) VALUES ('$piece', '".$exhibit->id."')");
		} else {
			mysqli_query($con,"INSERT INTO mbira_areas_has_mbira_exhibits (mbira_areas_id, mbira_exhibits_id) VALUES ('$piece', '".$exhibit->id."')");
		}
	}
}


if ($_POST['task'] == 'create'){
	createRow($con);
} else if ($_POST['task'] == 'update'){
	updateRow($con);
} else if ($_POST['task'] == 'delete'){
	deleteRow($con);
} else if ($_POST['task'] == 'uploadHeader'){
	uploadHeader($con);
} else if ($_POST['task'] == 'all'){
	getAll($con);
} else if ($_POST['task'] == 'info'){
	getInfo($con);
} else if ($_POST['task'] == 'thumbnail'){
	saveThumbnail($con);
} else if ($_POST['task'] == 'addTo'){
	addTo($con);
}

mysqli_close($con);
?>