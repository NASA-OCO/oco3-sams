<?php
include('../private/authenticate.php');

session_start();

if (!$_SESSION['user'] || !$_SESSION['password']) {
	header('Location: ../authentication/index.php?breadcrumb=/requests/index.php');
	die();
} else {
	if (($result = authenticate($_SESSION['user'], $_SESSION['password'])) == NULL) {
		header('Location: ../authentication/index.php?breadcrumb=/requests/index.php');
		die();
	}
}
?>

<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<title>Request a SAM | SAMs</title>

	<?php
	include '../private/config.php';
	if (!($conn = @mysqli_connect($server, $webuser, $webpass, $db)))
		die("Could not Connect to the database");
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
	<script type="text/javascript" charset="utf-8">
		$(document).ready(function() {
			$('#requests').DataTable();
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
			if (frm.nickname.value == "") {
				alert("Nickname is a required field.");
				frm.nickname.focus();
				return reject_form();
			}
			if (frm.spatial.value == "") {
				alert("Please make sure to select a region from the map.");
				frm.spatial.focus();
				return reject_form();
			}
			if (frm.startdate.value == "") {
				alert("Start Date is a required field.");
				frm.startdate.focus();
				return reject_form();
			}
			if (frm.enddate.value == "") {
				alert("End Date is a required field.");
				frm.enddate.focus();
				return reject_form();
			}
			if (frm.justification.value == "") {
				alert("Justification is a required field.");
				frm.justification.focus();
				return reject_form();
			}
			if (frm.obsType.value == "SAM" && frm.drawType.value != "rectangle") {
				alert("SAM observations require you to draw a rectangle (not a marker) on the map.  Please update and try again.");
				return reject_form();
			}
			if (frm.obsType.value == "target" && frm.drawType.value != "marker") {
				alert("Target observations require you to drop a marker (not a rectangle) on the map.  Please update and try again.");
				return reject_form();
			}
			return accept_form();
		}
	</script>
</head>

<body>

	<?php
		include '../includes/files/header.php';
		date_default_timezone_set('UTC');
	?>

	<div class="container">

		<div class="pagetitle">
			<h1>Request a SAM</h1>
		</div>

		<div class="pagecontent">
			<p>Please take a look at the <a href="/index.php" target="_blank">map on the SAMs homepage</a> to ensure that your request is not already covered by an existing SAM. Also review <a href="requesthistory.php" target="_blank">all previously requested SAMs</a> to make sure you or somebody on your team did not already request the SAM you are interested in. After you have checked exising SAMs and existing requests, you may draw a box or drop a pin on the map for the area you would like to request. Once you've done that, you can then edit your selection if you need to (coordinates are in longitutde latitude format). Note that you may only draw one box or drop one pin at a time.</p>
		</div>
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

			map.addLayer(drawnItems);

			var drawControlFull = new L.Control.Draw({
				draw: {
					polygon: false,
					marker: true,
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

			map.once(L.Draw.Event.CREATED, function(event) {
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
						document.getElementById('drawType').value = "rectangle";
				});
				}
				if (type === 'marker') {
					var lat = String(layer._latlng.lat);
					var lon = String(layer._latlng.lng);
					document.getElementById('spatial').value = lon + " " + lat;
					document.getElementById('drawType').value = "marker";
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
		</script>
		<br /><br />
		<div id="info">
			<form method="POST" action="requestsubmit.php" name="submit request" onsubmit="return val_form(this);">
				<div class="form-group">
					<label for="nickname">Request Nickname (must use a name you have not used before)</label>
					<input type="text" class="form-control" id="nickname" name="nickname" tabindex="2" />
				</div>
				<div class="form-group">
					<label for="spatial">Spatial Region (you must use the map above to select a point or area; after that you can edit it here (lon lat) format)</label>
					<input type="text" class="form-control" id="spatial" name="spatial" placeholder="use map to select an area or point" tabindex="3" />
					<input type="hidden" class="form-control" id="drawType" name="drawType" value="None" />
				</div>
				<div class="form-row">
					<div class="form-group col-md-6">
						<label for="startdate">Start Date*</label>
						<input type="date" class="form-control" id="startdate" name="startdate" tabindex="4" min="<?php echo date('Y-m-d', strtotime('+6 weeks')); ?>" max="<?php echo date('Y-m-d', strtotime('+1 year')); ?>" />
					</div>
					<div class="form-group col-md-6">
						<label for="enddate">End Date*</label>
						<input type="date" class="form-control" id="enddate" name="enddate" tabindex="5" min="<?php echo date('Y-m-d', strtotime('+6 weeks')); ?>" max="<?php echo date('Y-m-d', strtotime('+1 year')); ?>" />
					</div>
				</div>
				<div class="form-group">
					<label for="obsType">Observation Type</label>
					<select name="obsType" id="obsType" tabindex="6">
						<option value="SAM" SELECTED>Snapshot Area Map (SAM)</option>
						<option value="target">Target</option>
					</select>
				</div>
				<div class="form-group">
					<label for="justification">Science Justification</label>
					<input type="textarea" col="20" row="200" class="form-control" id="justification" name="justification" tabindex="7" />
				</div>
				<input type="hidden" name="username" <?php echo "value='" . $_SESSION['user'] . "'" ?>>
				<button type="submit" class="btn btn-primary" tabindex="8">SUBMIT</button>
			</form>
			<p><i><small>*Dates must be at least 6 weeks from "today", and no further out than 1 year from "today"</small></i></p>
		</div>
		<br />
		<hr />
		<br />

		<h2>Your Requests</h2>
		<div class="pagecontent">
			<p>You can also see <a href="/sams/requests/requesthistory.php">all past SAMs requests</a>.</p>
		</div>
		<br />
		<table id="requests" class="display" style="width:100%;">
			<thead>
				<tr>
					<th>Request ID</th>
					<th>Submit Date</th>
					<th>Nickname</th>
					<th>Observation Type</th>
					<th>SAM Start Date</th>
					<th>SAM End Date</th>
					<th>Region</th>
					<th>Approved</th>
					<th>Scheduled Date</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$email = $_SESSION['user'];
				$sql = "SELECT rID, nickname, startDate, endDate, scheduledDate, obsType, approved, submitted "
						." FROM requests WHERE uID=(SELECT uID FROM users WHERE email=?) ORDER BY startDate DESC";
			
				$stmt = mysqli_prepare($conn, $sql);
				mysqli_stmt_bind_param(
					$stmt,
					"s",
					$email
				);
				mysqli_stmt_execute($stmt);

				$result = mysqli_stmt_get_result($stmt);
				while ($row = mysqli_fetch_assoc($result)) {
					echo "<tr>\n";
					echo "<td>" . $row['rID'] . "</td>\n";
					echo "<td>" . $row['submitted'] . "</td>\n";
					echo "<td>" . $row['nickname'] . "</td>\n";
					echo "<td>" . $row['obsType'] . "</td>\n";
					echo "<td>" . $row['startDate'] . "</td>\n";
					echo "<td>" . $row['endDate'] . "</td>\n";
					echo "<td><a href='/requests/requestview.php?rID=" . $row['rID'] . "'>View</a></td>\n";
					echo "<td>" . $row['approved'] . "</td>\n";
					echo "<td>" . $row['scheduledDate'] . "</td>\n";
				}
				echo "</tr>\n";
				mysqli_stmt_close($stmt);
				?>
			</tbody>
		</table>
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
