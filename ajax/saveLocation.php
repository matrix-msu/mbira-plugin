<?php
require_once('../../pluginsConfig.php');
require_once(basePathPlugin.'includes/includes.php');

$con=mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

// Check connection
if (mysqli_connect_errno()) {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

//Delete location
function deleteRow($con){
	$id = $_POST['id'];
	
	mysqli_query($con,"DELETE FROM mbira_locations WHERE id='$id'");
}

//Update location
function updateRow($con){
	$projectId = $_POST['projectId'];
	$name = $_POST['name'];
	$desc = $_POST['description'];
	$dig_deeper = $_POST['dig_deeper'];
	$lat = $_POST['latitude'];
	$lon = $_POST['longitude'];
	$toggle_dig_deeper = $_POST['toggle_dig_deeper'];
	$toggle_media = $_POST['toggle_media'];
	$toggle_comments = $_POST['toggle_comments'];
	
	mysqli_query($con,"UPDATE mbira_locations SET name='$name', description='$desc', dig_deeper='$dig_deeper', toggle_comments='$toggle_comments', toggle_dig_deeper='$toggle_dig_deeper', 
		latitude='$lat', longitude='$lon', toggle_media='$toggle_media' WHERE project_id='$projectId'");
}

//Add new location
function createRow($con) {
	$projectId = $_POST['projectId'];
	$pid = $_POST['pid'];
	$name = $_POST['name'];
	$desc = $_POST['description'];
	$lat = $_POST['lat'];
	$lon = $_POST['lon'];
	
	//Save image
	$uploaddir = '../images/';
	
	//Use default image if no file provided
	if(isset($_FILES['file']['name'])){
		$uploadfile = $uploaddir . basename($_FILES['file']['name']);	
		move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile);
		$path = $_FILES['file']['name'];
	}else{
		$path = 'Default.png';
	}
	
	//Create row in mbira_locations
	mysqli_query($con,"INSERT INTO mbira_locations (project_id, pid, name, description, latitude, longitude, file_path) VALUES ('$projectId', '$pid', '$name', '$desc', '$lat', '$lon', '$path')");
	
	
	//Create and ingest location record to kora
	$result = mysqli_query($con, "SELECT * FROM scheme WHERE schemeName = 'Location' AND pid = " . $pid);
	
	while($row = mysqli_fetch_array($result)) {
		$sid = $row['schemeid'];
	}
	
	$xml = Array();
	$xml["Name"] = $name;
	$xml["Description"] = $desc;
	$xml["Longitude"] = $lon;
	$xml["Latitude"] = $lat;
	
	// "<Record>
	// <id>'$rid'</id> 
	// <Name>'$name'</Name> 
	// <Description>'$desc'</Description> 
	// <Longitude>'$lat'</Longitude> 
	// <Latitude>'$lon'</Latitude> 
	// </Record>"

	$record = new Record($pid, $sid);
	$record -> ingest($xml);
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