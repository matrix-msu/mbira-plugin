<?php
	require_once('../../model/manager.php');
	require_once('../pluginsConfig.php');
	
	Manager::Init();
	
	if (!Manager::IsLoggedIn())
	{
		header( 'Location: ../../accountLogin.php' ) ;
	}	
	
	require_once("../pluginsInclude.php");
	include_once(basePathPlugin.'plugins/pluginsHeader.php');
?>

<html>
<head>
	
	<meta http-equiv="content-type" content="text/html; charset=utf-8" >
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">  	
	
	<script src="https://cdn.jsdelivr.net/tipped/4.0.10/js/tipped/tipped.js"></script>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/tipped/4.0.10/css/tipped/tipped.css">

	<link rel="stylesheet" href="vendor/css/hint.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.2/css/font-awesome.min.css">
	<link rel="stylesheet" href="vendor/css/cropper.min.css">
 
	<script src="vendor/js/jquery-1.11.1.min.js"></script>
	<script src="vendor/js/hideShowPassword.min.js"></script>
	<script src="vendor/js/clipboard.min.js"></script>
	<script src="vendor/js/cropper.min.js"></script>
 
	<script src="vendor/js/angular/angular.min.js"></script>
	<script src="vendor/js/angular/angular-animate.min.js"></script>
	<script src="vendor/js/angular/angular-ui-router.min.js"></script>
	<script src="vendor/js/angular/fileUpload/angular-file-upload-shim.min.js"></script> 
	<script src="vendor/js/angular/fileUpload/angular-file-upload.min.js"></script> 
	<script src="vendor/js/angular/angular-sortable-view.js"></script>
	<script src="vendor/js/angular/isteven-multi-select.js"></script>

	<script src="controllers/configController.js"></script> 
	<script src="js/userController.js"></script> 
	<script src="js/projectController.js"></script> 
	<script src="controllers/locations/newLocationController.js"></script> 
	<script src="controllers/locations/singleLocationController.js"></script> 
	<script src="controllers/locations/viewLocationsController.js"></script> 
	<script src="controllers/users/newUserController.js"></script> 
	<script src="controllers/users/editUserController.js"></script> 
	<script src="controllers/users/usersToProjectController.js"></script> 
	<script src="controllers/users/viewUsersController.js"></script> 
	<script src="js/areaController.js"></script> 
	<script src="js/explorationController.js"></script> 
	<script src="js/exhibitController.js"></script> 
	<script src="js/mediaController.js"></script>
	<script src="js/notificationController.js"></script>
	<script src="js/settingsController.js"></script>
	
</head>
<body ng-app="mbira">
	<div id="wrap" class="container wrap">	
		
		<div class="content" ui-view=""></div>	

	</div>
	<?php
		require_once(basePathPlugin."plugins/pluginsFooter.php");
	?>
</body>
</html>