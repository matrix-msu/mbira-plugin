<?php
require_once('../../pluginsConfig.php');
require_once(basePathPlugin.'includes/includes.php');

$con=mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

// Check connection
if (mysqli_connect_errno()) {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

//Delete area
function deleteRow($con){
	$id = $_POST['id'];
	
	//Get media to delete
	$mediaResult = mysqli_query($con, "SELECT file_path FROM mbira_area_media WHERE area_id = '$id'");
	
	while($mediaRow = mysqli_fetch_array($mediaResult)) {
		unlink('../images/' .$mediaRow['file_path']);
	}
	//mysqli_query($con,"DELETE FROM mbira_map_locexpl WHERE locationid='$id'");   //once exploration area adding works
	mysqli_query($con,"DELETE FROM mbira_area_media WHERE area_id='$id'");
	mysqli_query($con,"DELETE FROM mbira_areas WHERE id='$id'");
}

//Update area
function updateRow($con){
	$projectId = $_POST['projectId'];
	$id = $_POST['id'];
	$name = mysqli_real_escape_string($con, $_POST['name']);
	$desc = mysqli_real_escape_string($con, $_POST['description']);
	$dig_deeper = mysqli_real_escape_string($con, $_POST['dig_deeper']);
	$coords = $_POST['coordinates'];
	$json = $_POST['json'];
	$radius = $_POST['radius'];
	$shape = $_POST['shape'];
	$toggle_dig_deeper = $_POST['toggle_dig_deeper'];
	$toggle_media = $_POST['toggle_media'];
	$toggle_comments = $_POST['toggle_comments'];
	$geoPath = $projectId."_".$id.".geojson";
	
	file_put_contents("../JSON/".$geoPath ,$json);
	
	mysqli_query($con,"UPDATE mbira_areas SET name='$name', description='$desc', dig_deeper='$dig_deeper', toggle_comments='$toggle_comments', toggle_dig_deeper='$toggle_dig_deeper', 
		coordinates='$coords', radius='$radius', shape='$shape', toggle_media='$toggle_media', geoJSON_path='$geoPath' WHERE id='$id'");
	
	if (isset($_FILES['file'])) {
		//Save image
		$uploaddir = '../images/';
		
		//Use default image if no file provided
		if(isset($_FILES['file']['name'])){
			$filename = explode('.', basename($_FILES['file']['name']));

			$uploadfile = $uploaddir . $filename[0].time().'.'.$filename[count($filename)-1];
			move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile);
			$path = $filename[0].time().'.'.$filename[count($filename)-1];
			
			mysqli_query($con, "UPDATE mbira_area_media SET isThumb='no' WHERE area_id='$id' AND isThumb='yes'");
			mysqli_query($con, "UPDATE mbira_areas SET thumb_path='$path' WHERE id='$id'");
			mysqli_query($con,"INSERT INTO mbira_area_media (area_id, file_path, isThumb) VALUES ('$id', '$path', 'yes')");
			mysqli_query($con,"DELETE FROM mbira_area_media WHERE area_id='$id' AND file_path='Default.png'");
		}
	}
}

//Add new area
function createRow($con) {

	$projectId = $_POST['projectId'];
	$name = mysqli_real_escape_string($con, $_POST['name']);
	$desc = mysqli_real_escape_string($con, $_POST['description']);
	$dig_deeper = mysqli_real_escape_string($con, $_POST['dig_deeper']);
	$coords = $_POST['coordinates'];
	$radius = $_POST['radius'];
	$shape = $_POST['shape'];
	$toggle_dig_deeper = $_POST['toggle_dig_deeper'];
	$toggle_media = $_POST['toggle_media'];
	$toggle_comments = $_POST['toggle_comments'];
	$json = $_POST['json'];
	
	
	
	//Save image
	$uploaddir = '../images/';
	
	//Use default image if no file provided
	if(isset($_FILES['file']['name'])){
		$filename = explode('.', basename($_FILES['file']['name']));

		$uploadfile = $uploaddir . $filename[0].time().'.'.$filename[count($filename)-1];
		move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile);
		$path = $filename[0].time().'.'.$filename[count($filename)-1];
	}else{
		$path = 'Default.png';
	}
	
	//Create row in mbira_areas
	mysqli_query($con,"INSERT INTO mbira_areas (project_id, name, description, dig_deeper, coordinates, radius, shape, thumb_path, toggle_comments, toggle_media, toggle_dig_deeper) VALUES ('$projectId', '$name', '$desc', '$dig_deeper', '$coords', '$radius', '$shape', '$path', '$toggle_comments', '$toggle_media', '$toggle_dig_deeper')");

	$idQuery = mysqli_query($con, "SELECT id FROM mbira_areas WHERE coordinates = '".$coords."'");
	$IDrow = mysqli_fetch_array($idQuery);
	$aid = $IDrow['id'];
	$geoPath = $projectId."_".$aid.".geojson";
	echo $aid;
	
	file_put_contents("../JSON/".$geoPath ,$json);
	
	//add geoJSON_path
	mysqli_query($con,"UPDATE mbira_areas SET geoJSON_path='$geoPath' WHERE id='$aid'");
	
	//Create row in mbira_exp_media
	mysqli_query($con,"INSERT INTO mbira_area_media (area_id, file_path, isThumb) VALUES ('$aid', '$path', 'yes')");		
	
	
	
}


if($_POST['task'] == 'create'){
	createRow($con);
}else if($_POST['task'] == 'update'){
	updateRow($con);
}else if($_POST['task'] == 'delete'){
	deleteRow($con);
}

mysqli_close($con);
?>