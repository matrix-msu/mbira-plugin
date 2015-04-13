//View all locations in the menu
mbira.controller("viewLocationsCtrl", function ($scope, $http, makeArray){

	//Get all locations
	$http({
		method: 'GET',
		url: "ajax/getLocations.php",
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).success(function(data){
		$scope.data = data;
		
		//Get all projects
		$http({
			method: 'GET',
			url: "ajax/getProjects.php",
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		}).success(function(data2){
			for(i=0;i<data2.length;i++){
			  	LocArray = makeArray.make(data2[i].id, $scope);
			  	data2[i].locations = LocArray;
			}
			$scope.projects = data2;
		})
	})
});	

//creating a new location
mbira.controller("newLocationCtrl", function ($scope, $http, $upload, $stateParams, setMap, $state, exhibits){
	$scope.marker = false;
	$scope.ID = $stateParams.project;
	$scope.PID = $stateParams.pid;
	$scope.param = $stateParams.project;
	
	//new location model
	$scope.newLocation = {
		name: "",
		descrition: "",
		dig_deeper: "",
		file: "",
		latitude: '',
		longitude: '',
		toggle_comments: true,
		toggle_media: true,
		toggle_dig_deeper: false
	}

	$scope.exhibits = [];
	temp=[];
	 $http({
		method: 'POST',
		url: "ajax/getExhibits.php",
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).success(function(data){
		if (!data.length) {
			$scope.exhibits.push({name:'No Exhibits'})
		}else {
			for(i=0;i<data.length;i++){
				temp.push({name:data[i].name,id:data[i].id, ticked:false});
				
			}
			$scope.exhibits=temp;
			console.log($scope.exhibits)
		}
	})  

	
	//Get file to be uploaded
	$scope.onFileSelect = function($files) {
		if($files.length > 1) {
			alert("Only upload one image for the thumbnail.");
		}else{
			$scope.file = $files[0];

			$scope.uploadFile = $upload.upload({
				url:'ajax/tempImg.php',
				method:"POST",
				headers: {'Content-Type': 'application/x-www-form-urlencoded'},
				file: $scope.file
			}).success(function(data) {	
				$('.dropzone img').attr('src', 'images/temp.jpg?' + (new Date).getTime()) // forces img refresh	
			});
		}
	};




	//submit new location
	$scope.submit = function() {	
		//submit location
		$scope.upload = $upload.upload({				
			url: 'ajax/saveLocation.php',
			method: 'POST',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: { 
					task: 'create',
					projectId: $scope.ID,
					pid: $stateParams.pid,
					name: $scope.newLocation.name,
					description: $scope.newLocation.description,
					dig_deeper: $scope.newLocation.dig_deeper,
					lat: $scope.newLocation.latitude,
					lon: $scope.newLocation.longitude,
					toggle_media: $scope.newLocation.toggle_media,
					toggle_dig_deeper: $scope.newLocation.toggle_dig_deeper,
					toggle_comments: $scope.newLocation.toggle_comments
				},
			file: $scope.file
		}).success(function(data) {
			exhibits.add(data,$scope.outputExhibits, 'loc')
			//return to project
			location.href = "#/viewProject/?project="+$scope.ID+'&pid='+$scope.PID;
		});
	};
	
	//<MAP_STUFF>
	//initialize map
	var map = setMap.setToCurrent();

	//initialize search bar
	$scope.search = new L.Control.GeoSearch({
		provider: new L.GeoSearch.Provider.OpenStreetMap(),
		position: 'topcenter',
		showMarker: true,
		scope: $scope,
		location: $scope.newLocation,
		map: map
	}).addTo(map);

	//Click to set marker and save location to scope
	map.on('click', function(e) {
		if($scope.marker != false){
			map.removeLayer($scope.marker);
			$scope.marker = false;
		}
		if($scope.search._positionMarker){
			map.removeLayer($scope.search._positionMarker);
		}
		$scope.newLocation.latitude = e.latlng.lat;
		$scope.newLocation.longitude = e.latlng.lng;
		
		$scope.marker = L.marker(e.latlng, {draggable:true}).addTo(map);
		$scope.$apply();
		$('#done').fadeIn('slow');
	});
	
	//Click to set marker and save location to scope
	$("#done").on('click', function(e) {
		e.stopPropagation()
		$('#done').fadeOut('slow', function() {
			$('#done').remove();
			$('.loc_info').fadeIn('slow', function(){
				$('html,body').animate({scrollTop: $('.loc_info').offset().top}, 1500);
			});
		});
	});
	//</MAP_STUFF>
});


//viewing a specific location
mbira.controller("singleLocationCtrl", function ($scope, $http, $state, $upload, $stateParams, setMap, timeStamp, exhibits){
	var map;
	$scope.newMedia = false;
	$scope.project = $stateParams.project;
	$scope.pid = $stateParams.pid;
	$scope.previous = $stateParams.previous
	$scope.media;
	$scope.comments = []
	$scope.userData = []
	
	$scope.exhibits = [];
	temp=[];

	$http({
		method: 'POST',
		url: "ajax/inExhibit.php",
		data: $.param({'id': $stateParams.location, 'task': 'loc'}),
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).success(function(data){
		ids = []
		for (a=0;a<data.length;a++){
			ids.push(data[a].id);
		}

		 $http({
			method: 'POST',
			url: "ajax/getExhibits.php",
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		}).success(function(data2){
			//adds checkmark if the location is in an exhibit.
			for(i=0;i<data2.length;i++){
				if ($.inArray(data2[i].id, ids) > -1) {
					temp.push({name:data2[i].name,id:data2[i].id, ticked:true});
				} else {
					temp.push({name:data2[i].name,id:data2[i].id, ticked:false});
				}
			}
			$scope.exhibits=temp;
		})  
	}) 
	
	
	
    function getMedia(){
		$http({
			method: 'POST',
			url: "ajax/getMedia.php",
			data: $.param({'id': $stateParams.location, 'type': 'loc'}),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		}).success(function(data){
			$scope.media = data;
		})
    }
	
	function checkIfHasReply(objToPushTo, idToCheck,data){
		for(j=0;j<data.length;j++){
			if (idToCheck == data[j].replyTo) {
				name = idToUser(data[j]);
				date = timeStamp.toDate(data[j].timeStamp) + " | " + timeStamp.toTime(data[j].timeStamp)
				tempObj = {comment_id:data[j].id, user:name, date:date,comment:data[j].comment, replies:[]};
				objToPushTo.replies.push(tempObj);
			}
		}
		for(h=0;h<objToPushTo.replies.length;h++) {
			checkIfHasReply(objToPushTo.replies[h],objToPushTo.replies[h].comment_id,data);
		}
	}
	
	function idToUser(commentData){
		for(q=0;q<$scope.userData.length;q++){					//match user_id of comment or reply to name
			if ($scope.userData[q].id === commentData.user_id){
				return $scope.userData[q].firstName + " " + $scope.userData[q].lastName;
			}
		}
	}
	
	function loadComments(userData) {//load comments
		$http({
			method: 'POST',
			url: "ajax/getComments.php",
			data: $.param({
					id: $stateParams.location,
					type: 'location'
				}),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		}).success(function(data){
			data.sort(function(x, y){
				if ( x.replyTo - y.replyTo === 0){
					return y.timeStamp - x.timeStamp
				} else {
					return y.replyTo - x.replyTo;
				}
			})
			for(i=0;i<data.length;i++){
				if (data[i].replyTo == 0){
					name = idToUser(data[i]);
					date = timeStamp.toDate(data[i].timeStamp) + " | " + timeStamp.toTime(data[i].timeStamp)
					tempObj = {comment_id:data[i].id, user:name, date:date,comment:data[i].comment, replies:[]}
					$scope.comments.push(tempObj)
				}
			}
			for(i=0;i<$scope.comments.length;i++){
				checkIfHasReply($scope.comments[i], $scope.comments[i].comment_id, data)
			}
			
		})
	}
	
	
	
	$http({
		method: 'POST',
		url: "ajax/getUsers.php",
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).success(function(data){
		$scope.userData = data;
		loadComments(data);
	})	
	
	//load location info
	$http({
		method: 'POST',
		url: "ajax/getLocationInfo.php",
		data: $.param({
				id: $stateParams.location
			}),
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).success(function(data){
		//Put location in scope
		$scope.location = data;
		
		//Set up map
		map = setMap.set(data.latitude, data.longitude);
		$scope.marker = L.marker([data.latitude, data.longitude],{draggable:'true'}).addTo(map);
		$scope.marker.on('dragend', function(event){
				var marker = event.target;
				var position = marker.getLatLng();
				$scope.location.latitude = position.lat;
				$scope.location.longitude = position.lng;
				marker.setLatLng([position.lat,position.lng],{draggable:'true'}).update();
		});
	
		//Set switches
		if($scope.location.toggle_comments == 'true'){
			$scope.location.toggle_comments = true;
		}else{
			$scope.location.toggle_comments = false;
		}
		if($scope.location.toggle_media == 'true'){
			$scope.location.toggle_media = true;
		}else{
			$scope.location.toggle_media = false;
		}
		if($scope.location.toggle_dig_deeper == 'true'){
			$scope.location.toggle_dig_deeper = true;
		}else{
			$scope.location.toggle_dig_deeper = false;
		}
		
		getMedia();
	})
	
	//Submit Media
	$scope.onFileSelect = function($files) {
		if($files.length > 1) {
			alert("Only upload one image at a time.");
		}else{
		  	$scope.file = $files[0];
			$scope.upload = $upload.upload({
					url: 'ajax/saveMedia.php',
					method: 'POST',
					headers: {'Content-Type': 'application/x-www-form-urlencoded'},
					data: {'id': $stateParams.location, 'type': 'loc'},
					file: $scope.file
			}).success(function(data, status, headers, config) {
				getMedia();
			});  
		}
	};
	
	//Handle "save and close"
	$scope.submit = function(){
		//Save
		$http({
			method: 'POST',
			url: "ajax/saveLocation.php",
			data: $.param({
						task: 'update',
						lid: $stateParams.location,
						name: $scope.location.name,
						description: $scope.location.description,
						dig_deeper: $scope.location.dig_deeper,
						latitude: $scope.location.latitude,
						longitude: $scope.location.longitude,
						toggle_media: $scope.location.toggle_media,
						toggle_dig_deeper: $scope.location.toggle_dig_deeper,
						toggle_comments: $scope.location.toggle_comments
					}),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		}).success(function(data){
			exhibits.add($stateParams.location,$scope.outputExhibits, 'loc')
			//Close (return to project)
			location.href = "javascript:history.back()";
		})
	}
	
	//Delete location
	$scope.delete = function(){
		$http({
		method: 'POST',
		url: "ajax/saveLocation.php",
		data: $.param({
				task: "delete",
				id: $stateParams.location
			}),
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		}).success(function(data){
			//return to projecct
			location.href = "javascript:history.back()";
		})
	}
});
