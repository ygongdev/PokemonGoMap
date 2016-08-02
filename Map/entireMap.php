<!DOCTYPE html>
<html>
  <head>
    <title>Archive</title>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>

    <link rel="stylesheet" type="text/css" href="../css/styles.css" media="screen" />
    <style>
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
      #scanning {
        top:0;
        bottom: 0;
        left: 0;
        right: 0;
        margin: auto;
      }
      #map {
        height: 100%;
      position:absolute;
      height:auto;
      bottom:0;
      top:0;
      left:0;
      right:0;
      margin-top:50px; /* adjust top margin to your header height */
      }
      .controls {
        margin-top: 10px;
        border: 1px solid transparent;
        border-radius: 2px 0 0 2px;
        box-sizing: border-box;
        -moz-box-sizing: border-box;
        height: 32px;
        outline: none;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
      }

      #pac-input {
        background-color: #fff;
        font-family: Roboto;
        font-size: 15px;
        font-weight: 300;
        margin-left: 12px;
        padding: 0 11px 0 13px;
        text-overflow: ellipsis;
        width: 250px;
      }

      #pac-input:focus {
        border-color: #4d90fe;
      }

      .pac-container {
        font-family: Roboto;
      }

      #type-selector {
        color: #fff;
        background-color: #4d90fe;
        padding: 5px 11px 0px 11px;
      }

      #type-selector label {
        font-family: Roboto;
        font-size: 13px;
        font-weight: 300;
      }
      #target {
        width: 345px;
      }
      #heatmap{
        margin-bottom: 20px;
        margin-right: 40px;
      }
      #relocate{
        margin-bottom: 20px;
      }
      #filter{
        margin-left: -5px;
      }
    </style>
  </head>
  <body>
  <nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container-fluid">
      <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
        <a class="navbar-brand" href="../index.html">WheresMyPokemon</a>
      </div>
      <div class="collapse navbar-collapse " id="myNavbar">
      <ul class="nav navbar-nav">
        <li><a href="../index.html"><span class="glyphicon glyphicon-home" aria-hidden="true"></span>Home</a></li>
        <li><a href="spawnMap.php"><span class="glyphicon glyphicon-map-marker" aria-hidden="true"></span>Map</a></li>
        <li class="active"><a href="entireMap.php"></span>Archive</a></li>
      </ul>
    </di
    v>
  </nav>
    <input id="pac-input" class="controls" type="text" placeholder="Search Location">
    <div id="map"></div>
    <button id ="heatmap" type="button" class="btn btn-success" onclick="toggleHeatmap()">Heatmap ON</button>
    <button id ="relocate" type="button" class="btn btn-primary" onclick="getCurrentLocation()"><span class="glyphicon glyphicon-screenshot" aria-hidden="true"></span> Find Me</button>

     <div class="form-group row" id="filter">
      <div class="col-sm-10">
        <select id = "filterInfo" class="form-control" name="pokemonName" style="width:80%;">
        <option value="" selected disabled>Filter</option>
        <option value="all">All</option>
          <?php 
          require_once '../database/connection.php';
            $sqlNames = "CALL sp_selectAllPokemonName";
            $names = $con->query($sqlNames);
            while ($name = $names->fetch_assoc()){
              echo "<option value= '".$name['pokemonName']."'>".$name['pokemonName']."</option>";
            }
            $con->next_result();
	    $con->close();
          ?>
        </select>
      </div>
    </div>


      
    <script>
      // Note: This example requires that you consent to location sharing when
      // prompted by your browser. If you see the error "The Geolocation service
      // failed.", it means you probably did not give permission for the browser to
      // locate you.
      var infowindows = [];
      var placeMarkers = [];
      //var currentInfoWindow = null;
      var currentMarker = null;
      var markers = [];
      var durations = [];
	var locations = [];
	console.log("testing");
	console.log(locations);
	
      var map = null;
      var heatmap = null;
      var mapOn = false;
      var heatmapOn = false;
      var globalPosition = {
          lat: 41.8781,
          lng: -87.6298
      }
	console.log(globalPosition);

      function initMap() {
          map = new google.maps.Map(document.getElementById('map'), {
          center: globalPosition,
          zoom: 17
        });
          mapOn = false;

        heatmap = new google.maps.visualization.HeatmapLayer({
          dissipating: true,
          maxIntensity: 10,
          radius: 5,
          opacity: 1,
          data: getPoints(locations),
          map: map
        });
          heatmapOn = true;
          //heatmap.setMap(null);

        // Create the search box and link it to the UI element.
        var input = document.getElementById('pac-input');
        var searchBox = new google.maps.places.SearchBox(input);
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

        var heat = document.getElementById('heatmap'); 
        heat.index = 1;   
        map.controls[google.maps.ControlPosition.BOTTOM_CENTER].push(heat); 

        var filter = document.getElementById('filter'); 
        filter.index = 1;   
        map.controls[google.maps.ControlPosition.LEFT_TOP].push(filter);  

        var relocate = document.getElementById('relocate'); 
        relocate.index = 1;   
        map.controls[google.maps.ControlPosition.BOTTOM_CENTER].push(relocate);  

        // Bias the SearchBox results towards current map's viewport.
        map.addListener('bounds_changed', function() {
          searchBox.setBounds(map.getBounds());
        });

        
        // Listen for the event fired when the user selects a prediction and retrieve
        // more details for that place.
        searchBox.addListener('places_changed', function() {
          var places = searchBox.getPlaces();
          

          if (places.length == 0) {
            return;
          }

          if (currentMarker){
            currentMarker.setMap(null);
          }
          globalPosition = places[0].geometry.location;
          setCurrentMarker(map);

        });


        getCurrentLocation();


        setInfo(map, locations); 
      }

      function handleLocationError(browserHasGeolocation, infoWindow, pos) {

        setCurrentMarker(map);
        infoWindow.setPosition(pos);
        infoWindow.setContent(browserHasGeolocation ?
                              'Error: The Geolocation service failed.' :
                              'Error: Your browser doesn\'t support geolocation.');
      }

      function createMarkerIcon(name) {
        var name = name.toLowerCase();
        switch (name){        
          case ("farfetch'd"):
            name = "farfetchd";
            break;
          case ("mr. mime"):
            name = "mr-mime";
            break;
          case ("nidoran f"):
            name = "nidoran-f";
            break;
          case ("nidoran m"):
            name = "nidoran-m";
            break;
          default:
             break;
        }
        return {
          'url' : 'https://img.pokemondb.net/sprites/black-white/normal/' + name + '.png',
          'scaledSize' : new google.maps.Size(70, 70)
        };
      }

      function closeAllInfoWindow(){
        for (var i=0; i < infowindows.length; i++) {
          infowindows[i].close();
        }
      }

      //filter change
      $('#filterInfo').on('change', function() {
        if (this.value == "all"){
          $.post("../database/retrieveAllPokemonInfo.php", {pokemonName: this.value}, function(data){
            locations = eval(data);
            if (heatmapOn == true){
              heatmap.setMap(null);
              heatmap.setData(getPoints(locations));
              heatmap.setMap(map);
            }
            else
            {
              setInfo(map, locations);
            }  
          });
        }
        else
        {
          $.post("../database/retrievePokemonInfoByName.php", {pokemonName: this.value}, function(data){
            locations = eval(data);
            if (heatmapOn == true){
              heatmap.setMap(null);
              heatmap.setData(getPoints(locations));
              heatmap.setMap(map);
            }
            else
            {
              setInfo(map, locations);
            }  
          });
        }
      });

      
      function getCurrentLocation(){
        // Try HTML5 geolocation.
        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(function(position) {
            var pos = {
              lat: position.coords.latitude,
              lng: position.coords.longitude
            };

            globalPosition = pos;
            map.setCenter(pos);
            console.log(globalPosition);
            console.log(pos);
            if (currentMarker){
              currentMarker.setMap(null);
            }
            
            setCurrentMarker(map);

          }, function() {
            var infoWindow = new google.maps.InfoWindow({map: map});
            handleLocationError(true, infoWindow, globalPosition);
          });
        } else {
          // Browser doesn't support Geolocation
          var infoWindow = new google.maps.InfoWindow({map: map});
          handleLocationError(false, infoWindow, globalPosition);

        }
      }
      function setCurrentMarker(map){
        if (currentMarker){
          currentMarker.setMap(null);
        }
        currentMarker = new google.maps.Marker({
          map: map,
          icon: {
            url: "https://upload.wikimedia.org/wikipedia/en/3/39/Pokeball.PNG",
            size: new google.maps.Size(71, 71),
            origin: new google.maps.Point(0, 0),
            anchor: new google.maps.Point(10, 20),
            scaledSize: new google.maps.Size(25, 25)
          },
          title: "Current Location",
          position: globalPosition
        });


        
        google.maps.event.addListener(map,'click', function(event) {          
        if (currentMarker != null){
          currentMarker.setPosition(event.latLng);
          globalPosition = event.latLng;
          }
      });
      }

      function removeEverything(map, locations){
        for (var i = 0 ; i < markers.length; i++){
          markers[i].setMap(null);
          infowindows[i].setMap(null);
        }
      }
      function setInfo(map, locations){
          removeEverything(map, locations);
          for (var i = 0; i < locations.length; i++) {
          var updatedTime = new Date(locations[i]['updatedDate'].replace(/-/g,"/"));
          var duration = parseInt(locations[i]['spawnDuration']);

          durations[i] = Math.floor((updatedTime.getTime() - Date.now())/1000) + duration;
          var durationMinutes = Math.floor(durations[i] / 60);
          var durationSeconds = durations[i] - durationMinutes * 60;

          var hour = locations[i]['hour'];
          var minute = (locations[i]['minute'] < 10 ? '0' + locations[i]['minute'] : locations[i]['minute']);
          var AMPM = (hour>= 12 ? 'PM' : 'AM');
          if (hour > 12){
            hour -= 12;
          }

          markers[i] = new google.maps.Marker({
            position: new google.maps.LatLng(locations[i]['latitude'], locations[i]['longitude']),
            map: map,
            title: locations[i]['pokemonName'],
            icon: createMarkerIcon(locations[i]['pokemonName']),
            anchorPoint: new google.maps.Point(0, -50)
          });

          infowindows[i] = new google.maps.InfoWindow({
            maxHeight: 100,
            disableAutoPan: true,
            content: 'Pokemon Name: ' + '<b>' + locations[i]['pokemonName'] + '</b>' + '</br>' + 
            'Spawned On: ' + '<b>' + locations[i]['createdDate'] + '</b>' + '</br>'
            + '<a href=' + '"https://www.google.com/maps/dir/current+location/' + locations[i]['latitude'] + ',' + locations[i]['longitude'] + '"' + ' target=' + '"_blank"' + 'style="text-decoration:none;">' +  'Get Directions</a>'
          });

          google.maps.event.addListener(markers[i], 'mouseover', (function(marker, i) {
            return function() {
              infowindows[i].open(map, markers[i]);
            google.maps.event.addListener(markers[i], 'mouseout', (function(i) {    
            return function() {        
              infowindows[i].close();
            }      
          })(i)); 
            }
          })(markers[i], i));   

          google.maps.event.addListener(markers[i], 'click', (function(marker, i) {
            return function() {
              infowindows[i].open(map, markers[i]);
              google.maps.event.clearListeners(markers[i], 'mouseout');

            }
          })(markers[i], i));  
     
        }
      }

      function toggleHeatmap() {
        // turn off
        if (heatmapOn == true){
          heatmapOn = false;
          document.getElementById('heatmap').innerHTML ="Heatmap OFF";
          document.getElementById('heatmap').className = "btn btn-danger";
          setInfo(map, locations);
        }else{
          removeEverything(map, locations);
          heatmapOn = true;
          document.getElementById('heatmap').innerHTML ="Heatmap ON";
          document.getElementById('heatmap').className = "btn btn-success";
        }
        heatmap.setMap(heatmap.getMap() ? null : map);
      }

      function getPoints(locations){
        var retval = [];
        for (var i = 0; i < locations.length; i++){
          retval.push(
            new google.maps.LatLng(locations[i]['latitude'], locations[i]['longitude']));
        }
        return retval;
      }

    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=[Your API Key Here]&libraries=places,visualization&callback=initMap">
    </script>
</html>
