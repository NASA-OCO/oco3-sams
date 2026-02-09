<?php
include('../private/authenticate.php');

session_start();

if (!$_SESSION['user'] || !$_SESSION['password']) {
	header('Location: ../authentication/index.php?breadcrumb=/requests/requesthistory.php');
	die();
} else {
	if (($result = authenticate($_SESSION['user'], $_SESSION['password'])) == NULL) {
		header('Location: ../authentication/index.php?breadcrumb=/requests/requesthistory.php');
		die();
	}
}
?>

<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<title>SAM Request History | SAMs</title>


	<?php
	include '../private/config.php';
	if (!($conn = @mysqli_connect($server, $webuser, $webpass, $db)))
		die("Could not Connect to the database");
	?>

	<link href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css" rel="stylesheet">
	<link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
	
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
	<script src="https://code.jquery.com/jquery-3.4.1.js" integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU=" crossorigin="anonymous"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
	<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js" type="text/javascript"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
	<script type="text/javascript" charset="utf-8">
		$(document).ready(function() {
			$('#requests').DataTable({
				pageLength: 50
			});
		});
	</script>
</head>

<body>
	<?php
	include '../includes/files/header.php';
	date_default_timezone_set('UTC');
	?>

	<div class="container">

		<div class="pagetitle">
			<h1>SAM Request History</h1>
		</div>

		<div class="pagecontent">
			<p>Below is a record of SAM requests.</p>
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
					<th>User</th>
					<th>Approval Status</th>
					<th>Scheduled Date</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$sql = "SELECT r.rID, r.startDate, r.endDate, CONCAT(u.lastName, ', ', u.firstName) AS name, u.email, r.approved, r.scheduledDate, r.submitted, r.nickname, r.obsType FROM requests r, users u WHERE r.uID=u.uID ORDER BY r.rID DESC";
				$resultID = @mysqli_query($conn, $sql);
				while ($row = mysqli_fetch_array($resultID)) {
					echo "<tr>\n";
					echo "<td><a href='/requests/requestview.php?rID=" . $row['rID'] . "'>" . $row['rID'] . "</a></td>\n";
					echo "<td>" . $row['submitted']  . "</td>\n";
					echo "<td>" . $row['nickname'] . "</td>\n";
					echo "<td>" . $row['obsType'] . "</td>\n";
					echo "<td>" . $row['startDate'] . "</td>\n";
					echo "<td>" . $row['endDate'] . "</td>\n";
					echo "<td><a href='mailto:" . $row['email'] . "'>" . $row['name'] . "</a></td>\n";
					echo "<td>" . $row['approved'] . "</td>\n";
					if ($row['scheduledDate'] == '') {
						echo "<td>None</td>\n";
					} else {
						echo "<td>" . $row['scheduledDate'] . "</td>\n";
					}
				}
				echo "</tr>\n";
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
