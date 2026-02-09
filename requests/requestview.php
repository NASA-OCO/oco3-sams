<?php
include('../private/authenticate.php');

session_start();

$rID = $_GET['rID'];

if (!$_SESSION['user'] || !$_SESSION['password']) {
	header('Location: ../authentication/index.php?breadcrumb=/requests/requestview.php--rID=' . $rID);
	die();
} else {
	if (($result = authenticate($_SESSION['user'], $_SESSION['password'])) == NULL) {
		header('Location: ../authentication/index.php?breadcrumb=/requests/requestview.php--rID=' . $rID);
		die();
	}
}
?>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<title>View SAM Request | SAMs</title>

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
</head>

<body>

	<?php
	include '../includes/files/header.php';
	date_default_timezone_set('UTC');
	$rID = $_GET["rID"];
	$rID = mysqli_real_escape_string($conn, $rID);
	$rID = strip_tags($rID);
	$rID = intval($rID);

	$sql = 'SELECT r.startDate, r.endDate, r.scheduledDate, r.approved, ST_ASTEXT(r.requestRegion) AS requestRegion, '
			.' CONCAT(u.lastName, ", ", u.firstName) AS name, r.nickname, r.obsType, r.submitted FROM requests r, users u WHERE r.uID=u.uID AND rID=?';
	$stmt = mysqli_prepare($conn, $sql);
	mysqli_stmt_bind_param(
		$stmt,
		"i",
		$rID
	);
	mysqli_stmt_execute($stmt);

	$result = mysqli_stmt_get_result($stmt);		
	while ($row = mysqli_fetch_assoc($result)) {
		$startDate = $row['startDate'];
		$endDate = $row['endDate'];
		$name = $row['name'];
		$obsType = $row['obsType'];
		$coords = $row['requestRegion'];
		$scheduledDate = $row['scheduledDate'];
		$approved = $row['approved'];
		$submitted = $row['submitted'];
		$nickname = $row['nickname'];
	}
	mysqli_stmt_close($stmt);
	?>

	<div class="container">

		<div class="pagetitle">
			<h1>View SAM Request</h1>
		</div>

		<div class="pagecontent">
			<p>
				Below is information on SAM Request ID #<?php echo $rID; ?><br />
				<br />
				<b>Requestor:</b> <?php echo $name; ?><br />
				<b>Nickname:</b> <?php echo $nickname; ?><br />
				<b>Observation Type:</b> <?php echo $obsType; ?><br />
				<b>Submitted Date:</b> <?php echo $submitted; ?><br />
				<b>SAM Start Date:</b> <?php echo $startDate; ?><br />
				<b>SAM End Date:</b> <?php echo $endDate; ?><br />
				<b><a href="https://libgeos.org/specifications/wkt/">WKT</a> Coordinates:</b> <?php echo $coords; ?><br />
				<b>Approved:</b> <?php echo $approved; ?><br />
				<b>Scheduled Date:</b> <?php if ($scheduledDate == "") { echo "Not yet scheduled"; } else { echo $scheduledDate; } ?><br />
			</p>
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

			<?php
			if ($obsType == 'SAM') {
				$coords = str_replace('POLYGON((', '', $coords);
				$coords = str_replace('))', '', $coords);
				$coords = str_replace(',', '|', $coords);
				$coords = str_replace(' ', ',', $coords);
				$coords = explode('|', $coords);
				echo "var polygon = L.polygon([";
				foreach ($coords as $point) {
					$pointArr = explode(',', $point);
					$point = $pointArr[1] . ',' . $pointArr[0];
					echo "[" . $point . "],";
				}
				echo "]).addTo(map);\n";
				echo "map.fitBounds(polygon.getBounds());\n";
			} elseif ($obsType == 'target') {
				$coords = str_replace('POINT(', '', $coords);
				$coords = str_replace(')', '', $coords);
				$coords = explode(' ', $coords);
				$lat = $coords[1];
				$lon = $coords[0];
				echo "var marker = L.marker([" . $lat . "," . $lon . "]).addTo(map);\n";
				echo "map.flyTo(marker.getLatLng(), 5);\n";
			}
			?>
		</script>
		<br /><br />
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
