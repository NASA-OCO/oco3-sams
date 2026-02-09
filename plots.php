<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<title>Plots | SAMs</title>

	<?php
	require_once('private/config.php');
	if (!($conn = @mysqli_connect($server, $webuser, $webpass, $db)))
		die("Could not Connect to the database");
	?>

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
	
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

</head>

<body>
	<?php
	include 'includes/files/header.php';
	date_default_timezone_set('UTC');

	$thisID = $_GET['sID'];
	if (!is_numeric($thisID)) {
		$thisID = mysqli_real_escape_string($conn, $thisID);
	}
	$thisID = strip_tags($thisID);
	$thisID = intval($thisID);

	echo "<div class='container'>\n";
	echo "<div class='pagetitle'>\n";
	$sql = "SELECT s.name, t.targetTimeStart FROM sites s, selectedTargets t WHERE t.selectionID=? AND t.targetID=s.targetID";
	$stmt = mysqli_prepare($conn, $sql);
	mysqli_stmt_bind_param($stmt, "i", $thisID);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);
	$siteName = mysqli_fetch_row($result);
	mysqli_stmt_close($stmt);
	echo "<h1>Plots for " . $siteName[0] . " beginning at " . $siteName[1] . " GMT</h1>\n";
	echo "</div>\n";
	echo "<div class='pagecontent'>\n";
	echo "<br />\n";

	$sql = "SELECT plotType FROM plotFiles WHERE selectionID=? ORDER BY plotType DESC";
	$stmt = mysqli_prepare($conn, $sql);
	mysqli_stmt_bind_param($stmt, "i", $thisID);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);
	$plotCount = mysqli_num_rows($result);
	$plotArray = array();
	if ($plotCount > 0) {
		echo "<!--small>Click on a plot for a larger view or <a href=''>download data for this SAM</a>.</small-->\n";
		echo "<br /><br /><br />\n";
		echo "<div class='row'>\n";

		$sqli = "SELECT filename FROM plotFiles WHERE selectionID=? AND plotType='xco2' ORDER BY version DESC LIMIT 1";
		$stmti = mysqli_prepare($conn, $sqli);
		mysqli_stmt_bind_param($stmti, "i", $thisID);
		mysqli_stmt_execute($stmti);
		$resulti = mysqli_stmt_get_result($stmti);
		$xco2Count = mysqli_num_rows($resulti);
		if ($xco2Count > 0) {
			while ($row = mysqli_fetch_array($resulti)) {
				echo "<div class='col-xs-6' style='width: 500px; border: 2px solid black; border-radius: 5px; margin-right: 25px; margin-left: 20px; padding: 15px;'>\n";
				echo "<h2>xco2</h2>\n";
				echo "<hr /><br />\n";
				echo "<figure class='col-lg-12'><a href='plots/xco2/" . $row['filename'] . "' target='_blank'><img src='plots/xco2/" . $row['filename'] . "'></a></figure>\n";
				echo "</div>\n";
			}
		}
		mysqli_stmt_close($stmti);
		$sqli = "SELECT filename FROM plotFiles WHERE selectionID=? AND plotType='xco2_bc_qf' ORDER BY version DESC LIMIT 1";
		$stmti = mysqli_prepare($conn, $sqli);
		mysqli_stmt_bind_param($stmti, "i", $thisID);
		mysqli_stmt_execute($stmti);
		$resulti = mysqli_stmt_get_result($stmti);
		$bcCount = mysqli_num_rows($resulti);
		if ($bcCount > 0) {
			while ($row = mysqli_fetch_array($resulti)) {
				echo "<div class='col-xs-6' style='width: 500px; border: 2px solid black; border-radius: 5px; margin-right: 25px; margin-left: 20px; padding: 15px;'>\n";
				echo "<h2>xco2_bc_qf</h2>\n";
				echo "<hr /><br />\n";
				echo "<figure class='col-lg-12'><a href='plots/xco2_bc_qf/" . $row['filename'] . "' target='_blank'><img src='plots/xco2_bc_qf/" . $row['filename'] . "'></a></figure>\n";
				echo "</div>\n";
			}
		}
		mysqli_stmt_close($stmti);
		echo "</div>\n";
		echo "<br />\n";
		echo "<br />\n";
		echo "<div class='row'>\n";
		$sql = "SELECT filename FROM plotFiles WHERE selectionID=? AND plotType='geostationary_imagery' ORDER BY version DESC LIMIT 1";
		$stmti = mysqli_prepare($conn, $sql);
		mysqli_stmt_bind_param($stmti, "i", $thisID);
		mysqli_stmt_execute($stmti);
		$resulti = mysqli_stmt_get_result($stmti);		
		$geostationaryImageryCount = mysqli_num_rows($resulti);
		if ($geostationaryImageryCount > 0) {
			while ($row = mysqli_fetch_array($resulti)) {
				echo "<div class='col-xs-6' style='width: 500px; border: 2px solid black; border-radius: 5px; margin-right: 25px; margin-left: 20px; padding: 15px;'>\n";
				echo "<h2>geostationary_imagery</h2>\n";
				echo "<hr /><br />\n";
				echo "<figure class='col-lg-12'><a href='plots/none/" . $row['filename'] . "' target='_blank'><img src='plots/none/" . $row['filename'] . "'></a></figure>\n";
				echo "</div>\n";
			}
		}
		mysqli_stmt_close($stmti);
		$sql = "SELECT filename FROM plotFiles WHERE selectionID=? AND plotType='xco2_bc_qf_+_geostationary_imagery' ORDER BY version DESC LIMIT 1";
		$stmti = mysqli_prepare($conn, $sql);
		mysqli_stmt_bind_param($stmti, "i", $thisID);
		mysqli_stmt_execute($stmti);
		$resulti = mysqli_stmt_get_result($stmti);
		$bcgiCount = mysqli_num_rows($resulti);
		if ($bcgiCount > 0) {
			while ($row = mysqli_fetch_array($resulti)) {
				echo "<div class='col-xs-6' style='width: 500px; border: 2px solid black; border-radius: 5px; margin-right: 25px; margin-left: 20px; padding: 15px;'>\n";
				echo "<h2>xco2_bc_qf_+_geostationary_imagery</h2>\n";
				echo "<hr /><br />\n";
				echo "<figure class='col-lg-12'><a href='plots/xco2_bc_qf_goes/" . $row['filename'] . "' target='_blank'><img src='plots/xco2_bc_qf_goes/" . $row['filename'] . "'></a></figure>\n";
				echo "</div>\n";
			}
		}
		mysqli_stmt_close($stmti);
		echo "</div>\n";
		echo "<br />\n";
		echo "<br />\n";
		echo "<div class='row'>\n";
		$sql = "SELECT filename FROM plotFiles WHERE selectionID=? AND plotType='no2' ORDER BY version DESC LIMIT 1";
		$stmti = mysqli_prepare($conn, $sql);
		mysqli_stmt_bind_param($stmti, "i", $thisID);
		mysqli_stmt_execute($stmti);
		$resulti = mysqli_stmt_get_result($stmti);		
		$no2Count = mysqli_num_rows($resulti);
		if ($no2Count > 0) {
			while ($row = mysqli_fetch_array($resulti)) {
				echo "<div class='col-xs-6' style='width: 500px; border: 2px solid black; border-radius: 5px; margin-right: 25px; margin-left: 20px; padding: 15px;'>\n";
				echo "<h2>no2</h2>\n";
				echo "<hr /><br />\n";
				echo "<figure class='col-lg-12'><a href='plots/no2/" . $row['filename'] . "' target='_blank'><img src='plots/no2/" . $row['filename'] . "'></a></figure>\n";
				echo "</div>\n";
			}
		}
		mysqli_stmt_close($stmti);
		$sql = "SELECT filename FROM plotFiles WHERE selectionID=? AND plotType='co' ORDER BY version DESC LIMIT 1";
		$stmti = mysqli_prepare($conn, $sqli);
		mysqli_stmt_bind_param($stmti, "i", $thisID);
		mysqli_stmt_execute($stmti);
		$resulti = mysqli_stmt_get_result($stmti);		
		$coCount = mysqli_num_rows($resulti);
		if ($coCount > 0) {
			while ($row = mysqli_fetch_array($resulti)) {
				echo "<div class='col-xs-6' style='width: 500px; border: 2px solid black; border-radius: 5px; margin-right: 25px; margin-left: 20px; padding: 15px;'>\n";
				echo "<h2>co</h2>\n";
				echo "<hr /><br />\n";
				echo "<figure class='col-lg-12'><a href='plots/co/" . $row['filename'] . "' target='_blank'><img src='plots/co/" . $row['filename'] . "'></a></figure>\n";
				echo "</div>\n";
			}
		}
		mysqli_stmt_close($stmti);
		echo "</div>\n";
		echo "<br />\n";
		echo "<br />\n";
		echo "<div class='row'>\n";
		$sql = "SELECT filename FROM plotFiles WHERE selectionID=? AND plotType='o2radiance' ORDER BY version DESC LIMIT 1";
		$stmti = mysqli_prepare($conn, $sql);
		mysqli_stmt_bind_param($stmti, "i", $thisID);
		mysqli_stmt_execute($stmti);
		$resulti = mysqli_stmt_get_result($stmti);		
		$o2Count = mysqli_num_rows($resulti);
		if ($o2Count > 0) {
			while ($row = mysqli_fetch_array($resulti)) {
				echo "<div class='col-xs-6' style='width: 500px; border: 2px solid black; border-radius: 5px; margin-right: 25px; margin-left: 20px; padding: 15px;'>\n";
				echo "<h2>o2_radiance</h2>\n";
				echo "<hr /><br />\n";
				echo "<figure class='col-lg-12'><a href='plots/o2_radiance/" . $row['filename'] . "' target='_blank'><img src='plots/o2_radiance/" . $row['filename'] . "'></a></figure>\n";
				echo "</div>\n";
			}
		}
		mysqli_stmt_close($stmti);
		$sql = "SELECT filename FROM plotFiles WHERE selectionID=? AND plotType='sif_757nm' ORDER BY version DESC LIMIT 1";
		$stmti = mysqli_prepare($conn, $sql);
		mysqli_stmt_bind_param($stmti, "i", $thisID);
		mysqli_stmt_execute($stmti);
		$resulti = mysqli_stmt_get_result($stmti);		
		$dpCount = mysqli_num_rows($resulti);
		if ($dpCount > 0) {
			while ($row = mysqli_fetch_array($resulti)) {
				echo "<div class='col-xs-6' style='width: 500px; border: 2px solid black; border-radius: 5px; margin-right: 25px; margin-left: 20px; padding: 15px;'>\n";
				echo "<h2>sif_757nm</h2>\n";
				echo "<hr /><br />\n";
				echo "<figure class='col-lg-12'><a href='plots/sif_757nm/" . $row['filename'] . "' target='_blank'><img src='plots/sif_757nm/" . $row['filename'] . "'></a></figure>\n";
				echo "</div>\n";
			}
		}
		mysqli_stmt_close($stmti);
		echo "</div>\n";
		echo "<br />\n";
		echo "<br />\n";
		echo "<div class='row'>\n";
		$sql = "SELECT filename FROM plotFiles WHERE selectionID=? AND plotType='dp' ORDER BY version DESC LIMIT 1";
		$stmti = mysqli_prepare($conn, $sqli);
		mysqli_stmt_bind_param($stmti, "i", $thisID);
		mysqli_stmt_execute($stmti);
		$resulti = mysqli_stmt_get_result($stmti);		
		$dpCount = mysqli_num_rows($resulti);
		if ($dpCount > 0) {
			while ($row = mysqli_fetch_array($resulti)) {
				echo "<div class='col-xs-6' style='width: 500px; border: 2px solid black; border-radius: 5px; margin-right: 25px; margin-left: 20px; padding: 15px;'>\n";
				echo "<h2>dp</h2>\n";
				echo "<hr /><br />\n";
				echo "<figure class='col-lg-12'><a href='plots/dp/" . $row['filename'] . "' target='_blank'><img src='plots/dp/" . $row['filename'] . "'></a></figure>\n";
				echo "</div>\n";
			}
		}
		mysqli_stmt_close($stmti);
		echo "<div></div>\n";
		echo "</div>\n";
	}
	if ($plotCount < 1) {
		echo "No plots or images found for this SAM";
	}
	mysqli_stmt_close($stmt);
	?>




	</div>
	</div>
	</div>

	<?php
	mysqli_close($conn);
	include 'includes/files/footer.php';
	?>

</body>

</html>
