<?php
include('../private/authenticate.php');

session_start();

if (!$_SESSION['user'] || !$_SESSION['password']) {
	header('Location: ../authentication/index.php?breadcrumb=/favorites/index.php');
	die();
} else {
	if (($result = authenticate($_SESSION['user'], $_SESSION['password'])) == NULL) {
		header('Location: ../authentication/index.php?breadcrumb=/favorites/index.php');
		die();
	}
}
?>

<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<title>Favorite Sites | SAMs</title>

	<?php
	include '../private/config.php';
	if (!($conn = @mysqli_connect($server, $webuser, $webpass, $db)))
		die("Could not Connect to the database");
	$email = $_SESSION['user'];
	$sql = "SELECT s.name FROM sites s, users_sites x, users u WHERE s.display=1 AND x.uID=u.uID AND u.email=? AND s.targetID=x.targetID "
			." UNION DISTINCT SELECT s.targetID FROM sites s, users_sites x, users u WHERE s.display=1 AND x.uID=u.uID AND u.email=? AND s.targetID=x.targetID ORDER BY name ASC";
	$stmt = mysqli_prepare($conn, $sql);
	mysqli_stmt_bind_param(
		$stmt,
		"ss",
		$email,
		$email
	);
	mysqli_stmt_execute($stmt);

	$result = mysqli_stmt_get_result($stmt);
	?>

	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.8.0/dist/leaflet.css" integrity="sha512-hoalWLoI8r4UszCkZ5kL8vayOGVae1oxXe/2A4AO6J9+580uKHDO3JdHb7NzwwzK5xr/Fs0W40kiNHxM9vyTtQ==" crossorigin="" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/0.4.2/leaflet.draw.css" />
	<link href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css" rel="stylesheet">
	<link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
	
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
	<script src="https://unpkg.com/leaflet@1.8.0/dist/leaflet.js" integrity="sha512-BB3hKbKWOc9Ez/TAwyWxNXeoV9c1v6FIeYiBieIWkpLjauysF18NzgR1MBNBXf8/KABdlkX68nAhlwcDFLGPCQ==" crossorigin=""></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/0.4.2/leaflet.draw.js"></script>
	<script src="https://code.jquery.com/jquery-3.4.1.js" integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU=" crossorigin="anonymous"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
	<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js" type="text/javascript"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script type="text/javascript" src="../includes/js/markers.js"></script>
	<script type="text/javascript" charset="utf-8">
		$(document).ready(function() {
			$('#sitelist').DataTable();
		});
	</script>
	<script>
		$(function() {
			var siteNames = [
				<?php
				while ($row = mysqli_fetch_array($result)) {
					echo '"' . $row['name'] . '",';
				}
				?>
			];
			$("#sites").autocomplete({
				source: siteNames
			});
		});
	</script>
	<script type='text/javascript'>
		function reject_form() {
			return false;
		}

		function accept_form() {
			return true;
		}

		function val_form(frm) {
			if (frm.sza.value != "" && frm.szarange.value == "") {
				alert("Must specify a degree value when using SZA.");
				frm.szarange.focus();
				return reject_form();
			}
			if (frm.sza.value == "" && frm.szarange.value != "") {
				alert("Must specify an SZA value when setting an SZA degree value.");
				frm.sza.focus();
				return reject_form();
			}
			return accept_form();
		}
	</script>
	<script type='text/javascript'>
		function filterMarkers(type) {
  			allMarkers.forEach(function(marker) {
    			if (type === "all" || marker.type === type) {
      				if (!map.hasLayer(marker)) {
        				marker.addTo(map);
      				}
    			} else {
      				if (map.hasLayer(marker)) {
        				map.removeLayer(marker);
      				}
    			}
  			});

			allPolygons.forEach(function(polygon) {
    			if (type === "all" || polygon.type === type) {
      				if (!map.hasLayer(polygon)) {
        				polygon.addTo(map);
      				}
    			} else {
      				if (map.hasLayer(polygon)) {
        				map.removeLayer(polygon);
      				}
    			}
  			});
		}
	</script>
	<?php mysqli_stmt_close($stmt); ?>
</head>

<body>

	<?php
	include '../includes/files/header.php';
	date_default_timezone_set('UTC');
	if (isset($_POST['sites']) && $_POST['sites'] != '') {
		$sites = $_POST['sites'];
	} else {
		$sites = 'undef';
	}
	if (isset($_POST['startdate']) && $_POST['startdate'] != '') {
		$startdate = $_POST['startdate'];
	} else {
		$startdate = 'undef';
	}
	if (isset($_POST['enddate']) && $_POST['enddate'] != '') {
		$enddate = $_POST['enddate'];
	} else {
		$enddate = 'undef';
	}
	if (isset($_POST['sza']) && $_POST['sza'] != '') {
		$sza = $_POST['sza'];
	} else {
		$sza = 'undef';
	}
	if (isset($_POST['szarange']) && $_POST['szarange'] != '') {
		$szarange = $_POST['szarange'];
	} else {
		$szarange = 'undef';
	}
	if (isset($_POST['nsoundings']) && $_POST['nsoundings'] != '') {
		$nsoundings = $_POST['nsoundings'];
	} else {
		$nsoundings = 'undef';
	}
	if (isset($_POST['spatial']) && $_POST['spatial'] != '') {
		$spatial = $_POST['spatial'];
	} else {
		$spatial = 'undef';
	}
	if (isset($_POST['type']) && $_POST['type'] != '') {
		$type = $_POST['type'];
	} else {
		$type = 'undef';
	}
	if (isset($_POST['adding']) && $_POST['adding'] != '') {
		$adding = $_POST['adding'];
	} else {
		$adding = 'undef';
	}
	if (isset($_POST['removing']) && $_POST['removing'] != '') {
		$removing = $_POST['removing'];
	} else {
		$removing = 'undef';
	}
	if (isset($_POST['fullsitelist']) && $_POST['fullsitelist'] != '') {
		$fullsitelist = $_POST['fullsitelist'];
	} else {
		$fullsitelist = 'undef';
	}
	if (isset($_POST['yoursitelist']) && $_POST['yoursitelist'] != '') {
		$yoursitelist = $_POST['yoursitelist'];
	} else {
		$yoursitelist = 'undef';
	}

	#Injection protection
	$sites = mysqli_real_escape_string($conn, $sites);
	$startdate = mysqli_real_escape_string($conn, $startdate);
	$enddate = mysqli_real_escape_string($conn, $enddate);
	$sza = mysqli_real_escape_string($conn, $sza);
	$szarange = mysqli_real_escape_string($conn, $szarange);
	$nsoundings = mysqli_real_escape_string($conn, $nsoundings);
	$spatial = mysqli_real_escape_string($conn, $spatial);
	$type = mysqli_real_escape_string($conn, $type);
	$adding = mysqli_real_escape_string($conn, $adding);
	$removing = mysqli_real_escape_string($conn, $removing);
	if ($fullsitelist != "undef") {
		for ($i = 0; $i < count($fullsitelist); $i += 1) {
			$fullsitelist[$i] = mysqli_real_escape_string($conn, $fullsitelist[$i]);
		}
	}
	if ($yoursitelist != "undef") {
		for ($i = 0; $i < count($yoursitelist); $i += 1) {
			$yoursitelist[$i] = mysqli_real_escape_string($conn, $yoursitelist[$i]);
		}
	}

	#XSS
	$sites = strip_tags($sites);
	$startdate = strip_tags($startdate);
	$enddate = strip_tags($enddate);
	$sza = strip_tags($sza);
	$szarange = strip_tags($szarange);
	$nsoundings = strip_tags($nsoundings);
	$spatial = strip_tags($spatial);
	$type = strip_tags($type);
	$adding = strip_tags($adding);
	$removing = strip_tags($removing);
	if ($fullsitelist != "undef") {
		for ($i = 0; $i < count($fullsitelist); $i += 1) {
			$fullsitelist[$i] = strip_tags($fullsitelist[$i]);
		}
	}
	if ($yoursitelist != "undef") {
		for ($i = 0; $i < count($yoursitelist); $i += 1) {
			$yoursitelist[$i] = strip_tags($yoursitelist[$i]);
		}
	}

	#HTML Entities
	$sites = htmlentities($sites);
	$startdate = htmlentities($startdate);
	$enddate = htmlentities($enddate);
	$sza = htmlentities($sza);
	$szarange = htmlentities($szarange);
	$nsoundings = htmlentities($nsoundings);
	$spatial = htmlentities($spatial);
	$type = htmlentities($type);
	?>

	<div class="container">

		<div class="pagetitle">
			<h1>Favorite Sites</h1>
		</div>

		<div class="pagecontent">
			<p>This page lists your "favorite" target sites. Add or remove sites to the "Your Sites" list and they will be reflected in the searches below (you may select multiple sites at once). You will also receive a weekly digest of times when your sites have been observed and what data is availalbe.</p>
		</div>

		<?php
		$email = $_SESSION['user'];
		if ($adding == "1" && $fullsitelist != "undef") {
			foreach ($fullsitelist as $thisSite) {
				$sql = "INSERT INTO users_sites SET uID=(SELECT uID FROM users WHERE email=?), targetID=(SELECT targetID FROM sites WHERE name=?)";
				$stmt = mysqli_prepare($conn, $sql);
				mysqli_stmt_bind_param(
					$stmt,
					"ss",
					$email,
					$thisSite
				);
				mysqli_stmt_execute($stmt);
				mysqli_stmt_close($stmt);			
			}
		}
		if ($removing == "1" && $yoursitelist != "undef") {
			foreach ($yoursitelist as $thisSite) {
				$sql = "DELETE FROM users_sites WHERE uID=(SELECT uID FROM users WHERE email=?) AND targetID=(SELECT targetID FROM sites WHERE name=?)";
				$stmt = mysqli_prepare($conn, $sql);
				mysqli_stmt_bind_param(
					$stmt,
					"ss",
					$email,
					$thisSite
				);
				mysqli_stmt_execute($stmt);
				mysqli_stmt_close($stmt);			
			}
		}
		?>

		<div class="form-row">
			<div class="form-group col-md-6">
				<form method="POST" action="index.php" name="favorites list add">
					<label for="fullsitelist">Full Site List</label>
					<select multiple class="form-control" id="fullsitelist" name="fullsitelist[]" style="height: 250px;" tabindex="1">
						<?php
						$sql = "SELECT x.targetID FROM users_sites x, users u WHERE x.uID=u.uID and u.email=?";
						$stmt = mysqli_prepare($conn, $sql);
						mysqli_stmt_bind_param(
							$stmt,
							"s",
							$email
						);
						mysqli_stmt_execute($stmt);

						$result = mysqli_stmt_get_result($stmt);
						$userSitesExist = mysqli_num_rows($result);
						mysqli_stmt_close($stmt);

						if ($userSitesExist > 0) {
							$sql = "SELECT name FROM sites WHERE display=1 AND targetID NOT IN (SELECT x.targetID FROM users_sites x, users u WHERE x.uID=u.uID  AND u.email=?) ORDER BY name ASC";
							$stmt = mysqli_prepare($conn, $sql);
							mysqli_stmt_bind_param(
								$stmt,
								"s",
								$email
							);
							mysqli_stmt_execute($stmt);
						} else {
							$sql = "SELECT name FROM sites WHERE display=1 ORDER BY name ASC";
							$stmt = mysqli_prepare($conn, $sql);
							mysqli_stmt_execute($stmt);
						}
						$result = mysqli_stmt_get_result($stmt);
						while ($row = mysqli_fetch_assoc($result)) {
							echo "<option>" . $row['name'] . "</option>";
						}
						mysqli_stmt_close($stmt);
						?>
					</select>
					<br />
					<input type="hidden" name="adding" value="1">
					<button type="submit" class="btn btn-primary" tabindex="2">ADD</button>
				</form>
			</div>
			<div class="form-group col-md-6">
				<form method="POST" action="index.php" name="favorites list remove">
					<label for="yoursitelist">Your Sites</label>
					<select multiple class="form-control" id="yoursitelist" name="yoursitelist[]" style="height: 250px;" tabindex="3">
						<?php
						$sql = "SELECT s.name FROM sites s, users u, users_sites x WHERE s.display=1 AND s.targetID=x.targetID AND x.uID=u.uID AND u.email=? ORDER BY s.name ASC";
						$stmt = mysqli_prepare($conn, $sql);
						mysqli_stmt_bind_param(
							$stmt,
							"s",
							$email
						);
						mysqli_stmt_execute($stmt);

						$result = mysqli_stmt_get_result($stmt);
						while ($row = mysqli_fetch_assoc($result)) {
							echo "<option>" . $row['name'] . "</option>";
						}
						mysqli_stmt_close($stmt);
						?>
					</select>
					<br />
					<input type="hidden" name="removing" value="1">
					<button type="submit" class="btn btn-primary" style="float: right;" tabindex="2">REMOVE</button>
				</form>
			</div>
		</div>
		</form>

		<br />
		<hr />
		<br />

		<h2>Search Your Sites</h2>
		Marker colors denote the type of site: <span style="color: orange;">validation</span>, <span style="color: grey;">calibration</span>, <span style="color: green;">desert</span>, <span style="color: gold;">SIF_Low</span>, <span style="color: blue;">fossil</span>, <span style="color: #742E98;">SIF_high</span>, and <span style="color: red;">volcano</span>.</p>
		Filter the map below by type: 
		<select id="typeFilter" onchange="filterMarkers(this.value)">
			<option value='all' selected>All</option>
			<?php
				$sql = "SELECT DISTINCT siteType FROM sites WHERE display=1 ORDER BY siteType DESC";
				$resultID = @mysqli_query($conn, $sql);
				while ($row = mysqli_fetch_array($resultID)) {
					echo "<option value='". $row['siteType'] . "' >" . $row['siteType'] . "</option>";
				} 
			?>
		</select>
		<br />
		<br />
		<div id="mapid" style="height: 500; width: 100%; z-index: 0;"></div>

		<script>
			var map = L.map('mapid', {
				minZoom: 2,
				maxZoom: 11
			}).setView([0, 0], 2);

			L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
				maxZoom: 18,
				attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
			}).addTo(map);

			var drawnItems = new L.FeatureGroup();

			function onClick(e) {
				document.getElementById('sites').value = this.options.title;
			}

			map.addLayer(drawnItems);

			var drawControlFull = new L.Control.Draw({
				draw: {
					polygon: false,
					marker: false,
					circle: false,
					polyline: false
				},
				edit: {
					featureGroup: drawnItems,
					remove: true
				}
			});

			var drawControlEditOnly = new L.Control.Draw({
				edit: {
					featureGroup: drawnItems
				},
				draw: false
			});

			map.addControl(drawControlFull);

			map.on(L.Draw.Event.CREATED, function(event) {
				var layer = event.layer;
				drawnItems.addLayer(layer);
			});

			map.on('draw:created', function(e) {
				var type = e.layerType,
					layer = e.layer;

				if (type === 'rectangle') {
					layer.on('mouseover', function() {
						var coords = String(layer.getLatLngs());
						var coords = coords.replace(/LatLng\(/g, '');
						var coords = coords.substring(0, coords.length - 1);
						var coords = coords.split(")");
						var newCoords = '';
						for (i = 0; i < coords.length; i++) {
							var thisCoord = coords[i].replace(/,/g, '');
							var individualCoords = thisCoord.split(' ');
							newCoords += individualCoords[1] + ' ' + individualCoords[0] + ',';
						}
						document.getElementById('spatial').value = newCoords.substring(0, newCoords.length - 1);
					});
				}

				drawnItems.addLayer(layer);
				drawControlFull.remove(map);
				drawControlEditOnly.addTo(map)
			});

			map.on(L.Draw.Event.DELETED, function(e) {
				if (drawnItems.getLayers().length === 0) {
					drawControlEditOnly.remove(map);
					drawControlFull.addTo(map);
				};
			});

			var allMarkers = [];
			var allPolygons = [];

			<?php
			$sql = "SELECT s.targetID, s.name, ST_X(s.targetGeo) AS lon, ST_Y(s.targetGeo) AS lat, ST_ASTEXT(s.targetShape) as shape, s.targetAlt, s.siteType "
					."FROM sites s, users u, users_sites x WHERE s.display=1 AND s.targetID=x.targetID AND x.uID=u.uID AND u.email=?";
			$stmt = mysqli_prepare($conn, $sql);
			mysqli_stmt_bind_param(
				$stmt,
				"s",
				$email
			);
			mysqli_stmt_execute($stmt);

			$result = mysqli_stmt_get_result($stmt);
			$siteCount = mysqli_num_rows($result);
			$i = 1;
			while ($row = mysqli_fetch_assoc($result)) {
				if ($row['siteType'] == 'validation') {
					$icon = 'orangeIcon';
					$polyColor = 'orange';
				}
				if ($row['siteType'] == 'calibration') {
					$icon = 'greyIcon';
					$polyColor = 'grey';
				}
				if ($row['siteType'] == 'desert') {
					$icon = 'greenIcon';
					$polyColor = 'green';
				}
				if ($row['siteType'] == 'SIF_Low') {
					$icon = 'goldIcon';
					$polyColor = 'gold';
				}
				if ($row['siteType'] == 'fossil') {
					$icon = 'blueIcon';
					$polyColor = 'blue';
				}
				if ($row['siteType'] == 'SIF_High') {
					$icon = 'violetIcon';
					$polyColor = 'violet';
				}
				if ($row['siteType'] == 'volcano') {
					$icon = 'redIcon';
					$polyColor = 'red';
				}
				echo "var marker" . $i . " = L.marker([" . $row['lat'] . "," . $row['lon'] . "], {icon: " . $icon . ", title: '" . $row['name'] . "'}).addTo(map).on('click', onClick);\n";
				echo "marker" . $i . ".type = '" . $row['siteType'] . "'\n";
				echo "marker" . $i . ".bindPopup('<b>" . $row['name'] . "</b><br>Target ID: " . $row['targetID'] . "<br>Type: " . $row['siteType'] . "');\n";
				echo "allMarkers.push(marker" . $i . ");\n";
				$coords = $row['shape'];
				$coords = str_replace('POLYGON((', '', $coords);
				$coords = str_replace('))', '', $coords);
				$coords = str_replace(',', '|', $coords);
				$coords = str_replace(' ', ',', $coords);
				$coords = explode('|', $coords);
				echo "var polygon" . $i . " = L.polygon([";
				foreach ($coords as $point) {
					$pointArr = explode(',', $point);
					$point = $pointArr[1] . ',' . $pointArr[0];
					echo "[" . $point . "],";
				}
				echo "], {color: '"  . $polyColor . "'}).addTo(map);\n";
				echo "polygon" . $i . ".type = '" . $row['siteType'] . "'\n";
				echo "allPolygons.push(polygon" . $i . ");\n";
				$i++;
			}
			mysqli_stmt_close($stmt);
			?>
		</script>
		<br /><br />
		<div id="info">
			<p>Please either use the map above to select a region that contains SAM sites or click a single SAM site to have it populate the form below. Fill out the rest of the form fields as appropriate.<br /><br />
			<form method="POST" action="index.php#results" name="search sites" onsubmit="return val_form(this);">
				<div class="form-row">
					<div class="form-group col-md-6">
						<label for="sites">Site Name or Target ID</label>
						<input type="text" class="form-control" id="sites" name="sites" placeholder="start typing target name, if you did not select a region on the map" <?php if ($sites != "undef") {
																																												echo "value='" . $sites . "'";
																																											} else {
																																												echo "value=''";
																																											} ?> tabindex="1" />
					</div>
					<div class="form-group col-md-6">
						<label for="type">Site Type</label>
						<select id="type" class="form-control" name="type" tabindex="2">
							<option></option>
							<?php
							$sql = "SELECT DISTINCT siteType FROM sites WHERE display=1 ORDER BY siteType DESC";
							$resultID = @mysqli_query($conn, $sql);
							while ($row = mysqli_fetch_array($resultID)) {
								if ($type == $row['siteType']) {
									echo "<option SELECTED>" . $row['siteType'] . "</option>";
								} else {
									echo "<option>" . $row['siteType'] . "</option>";
								}
							}
							?>
						</select>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group col-md-6">
						<label for="startdate">Start Date</label>
						<input type="date" class="form-control" id="startdate" name="startdate" <?php if ($startdate != "undef") {
																																echo "value='" . $startdate . "'";
																															} else {
																																echo "value=''";
																															} ?> tabindex="3" />
					</div>
					<div class="form-group col-md-6">
						<label for="enddate">End Date</label>
						<input type="date" class="form-control" id="enddate" name="enddate" <?php if ($enddate != "undef") {
																															echo "value='" . $enddate . "'";
																														} else {
																															echo "value=''";
																														} ?> tabindex="4" />
					</div>
				</div>
				<div class="form-row">
					<div class="form-group col-md-6">
						<label for="sza">SZA</label>
						<input type="text" class="form-control" id="sza" name="sza" <?php if ($sza != "undef") {
																						echo "value='" . $sza . "'";
																					} else {
																						echo "value=''";
																					} ?> tabindex="5" />
					</div>
					<div class="form-group col-md-6">
						<label for="szarange">+/- degrees</label>
						<input type="text" class="form-control" id="szarange" name="szarange" <?php if ($szarange != "undef") {
																									echo "value='" . $szarange . "'";
																								} else {
																									echo "value=''";
																								} ?> tabindex="6" />
					</div>
					<div class="form-group col-md-6">
						<label for="nsoundings"># of Soundings</label>
						<input type="text" class="form-control" id="nsoundings" name="nsoundings" <?php if ($nsoundings != "undef") {
																										echo "value='" . $soundings . "'";
																									} else {
																										echo "value=''";
																									} ?> tabindex="7" />
					</div>
				</div>
				<div class="form-group">
					<label for="spatial">Spatial Region</label>
					<input type="text" class="form-control" id="spatial" name="spatial" placeholder="use map to select an area, if you did not fill out the 'Site Name' field" <?php if ($spatial != "undef") {
																																													echo "value='" . $spatial . "'";
																																												} else {
																																													echo "value=''";
																																												} ?> tabindex="8" />
				</div>
				<button type="submit" class="btn btn-primary" tabindex="9">SEARCH</button>
			</form>
		</div>

		<br />
		<hr />
		<br />

		<div id="results">
		<h2>Search Results</h2>
		<?php
		if ($sites == "undef" && $startdate == "undef" && $enddate == "undef" && $sza == "undef" && $spatial == "undef" && $type == "undef" && $nsoundings == "undef") {
			echo "<p>Showing all available SAMs.  Use the form above to narrow down this list.</p>";
		}
		?>
		<br />
		<table id="sitelist" class="display" style="width:100%;">
			<thead>
				<tr>
					<th>TargetID</th>
					<th>Name</th>
					<th>Soundings</th>
					<th>Start Date/Time</th>
					<th>End Date/Time</th>
					<th>Plots</th>
					<th>Subset XCO2</th>
					<!--th>Subset SIF</th-->
				</tr>
			</thead>
			<tbody>
				<?php
				if ($spatial != "undef") {
					$sql = "SELECT s.targetID, ST_ASTEXT(s.targetGeo) AS targetGeo FROM sites s, users u, users_sites x WHERE s.display=1 AND s.targetID=x.targetID AND x.uID=u.uID AND u.email=?";
					$stmt = mysqli_prepare($conn, $sql);
					mysqli_stmt_bind_param(
						$stmt,
						"s",
						$email
					);
					mysqli_stmt_execute($stmt);
					$result = mysqli_stmt_get_result($stmt);									
					$info = array();
					while ($row = mysqli_fetch_assoc($result)) {
						array_push($info, array($row['targetID'], $row['targetGeo']));
					}
					mysqli_stmt_close($stmt);
					$polygon = "POLYGON((";
					$polygon .= $spatial;
					$p = explode(",", $spatial);
					$polygon .= "," . $p[0];
					$polygon .= "))";

					$goodTargets = array();
					foreach ($info as $thisInfo) {
						$sql = "SELECT targetID FROM sites WHERE ST_CONTAINS(ST_GEOMFROMTEXT(?), ST_GEOMFROMTEXT(?)) AND targetID=?";
						$stmt = mysqli_prepare($conn, $sql);
						mysqli_stmt_bind_param(
							$stmt,
							"sss",
							$polygon, 
							$thisInfo[1],
							$thisInfo[0]
						);
						mysqli_stmt_execute($stmt);
						
						$result = mysqli_stmt_get_result($stmt);									
						$row = mysqli_num_rows($result);
						if ($row == 1) {
							array_push($goodTargets, "'" . $thisInfo[0] ."'");
						}
					}
					$email = $_SESSION['user'];
					$sql = "SELECT s.targetID, s.name, t.soundings, t.targetTimeStart, t.targetTimeEnd, t.selectionID, t.sza "
							."FROM sites s, selectedtargets t, users u, users_sites x WHERE s.display=1 AND t.display=1 "
							."AND s.targetID=t.targetID AND s.targetID=x.targetID AND x.uID=u.uID ";

					$types = '';
					$params = [];
					
					$sql .= " AND u.email = ? ";
					$types .= 's';
					$params[] = $email;

					if ($startdate != "undef") {
						$sql .= " AND DATE(t.targetTimeStart) >= ? ";
						$types .= 's';
						$params[] = $startdate;
					}
					if ($enddate != "undef") {
						$sql .= " AND DATE(t.targetTimeEnd) <= ? ";
						$types .= 's';
						$params[] = $enddate;
					}
					if ($sza != "undef" && $szarange != "undef") {
						$szaMin = $sza - $szarange;
						$sql .= " AND t.sza >= ? ";
						$types .= 'd';
						$params[] = $szaMin;
						$szaMax = $sza + $szarange;
						$sql .= " AND t.sza <= ? ";
						$types .= 'd';
						$params[] = $szaMax;
					}
					if ($type != "undef") {
						$sql .= " AND s.siteType=? ";
						$type .= 's';
						$params[] = $type;
					}
					if ($nsoundings != "undef") {
						$sql .= " AND t.soundings >= ? ";
						$type .= 'i';
						$params[] = $nsoundings;
					}
					if (!empty($goodTargets)) {
 				        $placeholders = implode(',', array_fill(0, count($goodTargets), '?'));
				        $sql .= " AND s.targetID IN ($placeholders) ";
						$types .= str_repeat('s', count($goodTargets));
		
						foreach ($goodTargets as $target) {
				        	$params[] = trim($target, "'");
    					}
					} 

					$sql .= "ORDER BY s.targetID ASC";

					$stmt = mysqli_prepare($conn, $sql);
					if ($types !== '') {
    					mysqli_stmt_bind_param($stmt, $types, ...$params);
					}
					mysqli_stmt_execute($stmt);
				} else {
					$email = $_SESSION['user'];
					$sql = "SELECT s.targetID, s.name, t.soundings, t.targetTimeStart, t.targetTimeEnd, t.selectionID, t.sza FROM sites s, selectedtargets t, users u, users_sites x "
							."WHERE s.display=1 AND t.display=1 AND s.targetID=t.targetID AND s.targetID=x.targetID AND x.uID=u.uID ";
					
					$types = '';
					$params = [];
					
					$sql .= " AND u.email = ? ";
					$types .= 's';
					$params[] = $email;

					if ($sites != "undef") {
						$checkTargetSQL = "SELECT targetID FROM sites WHERE targetID=?";
						$stmt = mysqli_prepare($conn, $sql);
						mysqli_stmt_bind_param(
							$stmt,
							"s",
							$sites
						);
						mysqli_stmt_execute($stmt);

						$checkResult = mysqli_stmt_get_result($stmt);						
						$targetCount = mysqli_num_rows($checkResult);
						if ($targetCount > 0) {
							$sql .= " AND s.targetID=? ";
							$types .= 's';
							$params[] = $sites;
						} else {
							$sql .= " AND s.name=? ";
							$types .= 's';
							$params[] = $sites;
						}
					}
					if ($startdate != "undef") {
						$sql .= " AND DATE(t.targetTimeStart) >= ? ";
						$types .= 's';
						$params[] = $startdate;
					}
					if ($enddate != "undef") {
						$sql .= " AND DATE(t.targetTimeEnd) <= ? ";
						$types .= 's';
						$params[] = $enddate;
					}
					if ($sza != "undef" && $szarange != "undef") {
						$szaMin = $sza - $szarange;
						$sql .= " AND t.sza >= ? ";
						$types .= 'd';
						$params[] = $szaMin;
						$szaMax = $sza + $szarange;
						$sql .= " AND t.sza <= ? ";
						$types .= 'd';
						$params[] = $szaMax;
					}
					if ($sza != "undef" && $szarange == "undef") {
						$sql .= " AND t.sza=? ";
						$types .= 'd';
						$params[] = $sza;
					}
					if ($type != "undef") {
						$sql .= " AND s.siteType=? ";
						$type .= 's';
						$params[] = $type;
					}
					if ($nsoundings != "undef") {
						$sql .= " AND t.soundings >= ? ";
						$types .= 'i';
						$params[] = $nsoundings;
					}
					$sql .= "ORDER BY s.targetID ASC";

					$stmt = mysqli_prepare($conn, $sql);
					if ($types !== '') {
    					mysqli_stmt_bind_param($stmt, $types, ...$params);
					}
					mysqli_stmt_execute($stmt);
				}

				$result = mysqli_stmt_get_result($stmt);
				while ($row = mysqli_fetch_assoc($result)) {
					echo "<tr>\n";
					echo "<td>" . $row['targetID'] . "</td>\n";
					echo "<td>" . $row['name'] . "</td>\n";
					echo "<td>" . $row['soundings'] . "</td>\n";
					echo "<td>" . $row['targetTimeStart'] . "</td>\n";
					echo "<td>" . $row['targetTimeEnd'] . "</td>\n";
					$cSQL = "SELECT filename FROM plotfiles WHERE selectionID=?";
					$Cstmt = mysqli_prepare($conn, $cSQL);
					mysqli_stmt_bind_param($Cstmt, 'i', $row['selectionID']);
					mysqli_stmt_execute($Cstmt);
					$cResult = mysqli_stmt_get_result($Cstmt);
					$plotCount = mysqli_num_rows($cResult);
					if ($plotCount > 0) {
						echo "<td align='center' valign='top'><button type='submit' class='btn btn-secondary'><a target='_blank' href='../plots.php?sID=" . $row['selectionID'] . "' style='color:white;'>Plots</a></button></td>\n";
					} else {
						echo "<td align='center' valign='top'>no plots</td>\n";
					}
					mysqli_stmt_close($Cstmt);
					#changing to an 8 week delay
					$sixWeeksAgo = date('Y-m-d', strtotime('-8 weeks'));
					if (date('Y-m-d', strtotime($row['targetTimeStart'])) < $sixWeeksAgo) {
						echo "<td align='center' valign='top'><form action='submit-subset.php' method='POST' name='submit subset job'><input type='hidden' name='targetID' value='" . $row['targetID'] . "'><input type='hidden' name='startDateTime' value='" . $row['targetTimeStart'] . "'><input type='hidden' name='endDateTime' value='" . $row['targetTimeEnd'] . "'><input type='hidden' name='product' value='xco2'><button type='submit' class='btn btn-secondary'>XCO2</button></form></td>";
					} else {
						echo "<td align='center' valign='top'>no data</td>";
					}
					echo "</tr>\n";
				}
				mysqli_stmt_close($stmt);
				?>
			</tbody>
		</table>
		</div>
	</div>

	<div class="fixed-bottom">
		<button type="button" style="float: right;" class="btn pmd-btn-fab btn-danger pmd-ripple-effect pmd-btn-raised"><a href="../authentication/logout.php" style="color: #FFFFFF; font-weight: bold;">LOGOUT</a></button>
	</div>

	<?php
	mysqli_close($conn);
	include '../includes/files/footer.php';
	?>

</body>

</html>
