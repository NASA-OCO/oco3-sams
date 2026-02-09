<?php
include('../../private/authenticate.php');

session_start();

if (!$_SESSION['user'] || !$_SESSION['password']) {
	header('Location: ../../authentication/index.php');
	die();
} else {
	if (($result = authenticate($_SESSION['user'], $_SESSION['password'])) == NULL) {
		header('Location: ../../authentication/index.php');
		die();
	}
}

$testAuth = authorize($_SESSION['user']);
if ($testAuth == NULL) {
	die('You are not authorized to view this page.');
}

?>

<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<title>SAM Request Disposition | SAMs</title>

	<?php
	include '../../private/config.php';
	if (!($conn = @mysqli_connect($server, $webuser, $webpass, $db)))
		die("Could not Connect to the database");
	?>

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
	
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</head>

<body>
	<?php
	include '../../includes/files/header.php';
	date_default_timezone_set('UTC');

	if (isset($_POST['rID']) && $_POST['rID'] != '') {
		$rID = $_POST['rID'];
	} else {
		$rID = 'undef';
	}
	if (isset($_POST['startDate']) && $_POST['startDate'] != '') {
		$startDate = $_POST['startDate'];
	} else {
		$startDate = 'undef';
	}
	if (isset($_POST['endDate']) && $_POST['endDate'] != '') {
		$endDate = $_POST['endDate'];
	} else {
		$endDate = 'undef';
	}
	if (isset($_POST['justification']) && $_POST['justification'] != '') {
		$justification = $_POST['justification'];
	} else {
		$justification = 'undef';
	}
	if (isset($_POST['obsType']) && $_POST['obsType'] != '') {
		$obsType = $_POST['obsType'];
	} else {
		$obsType = 'undef';
	}
	if (isset($_POST['scheduledDate']) && $_POST['scheduledDate'] != '') {
		$scheduledDate = $_POST['scheduledDate'];
	} else {
		$scheduledDate = 'undef';
	}
	if (isset($_POST['approved']) && $_POST['approved'] != '') {
		$approved = $_POST['approved'];
	} else {
		$approved = 'undef';
	}

	#Injection protection
	$scheduledDate = mysqli_real_escape_string($conn, $scheduledDate);
	$approved = mysqli_real_escape_string($conn, $approved);
	$justification = mysqli_real_escape_string($conn, $justification);
	$obsType = mysqli_real_escape_string($conn, $obsType);
	$startdate = mysqli_real_escape_string($conn, $startdate);
	$enddate = mysqli_real_escape_string($conn, $enddate);
	$rID = mysqli_real_escape_string($conn, $rID);

	#XSS
	$scheduledDate = strip_tags($scheduledDate);
	$justification = strip_tags($justification);
	$obsType = strip_tags($obsType);
	$approved = strip_tags($approved);
	$startdate = strip_tags($startdate);
	$enddate = strip_tags($enddate);
	$rID = mysqli_real_escape_string($conn, $rID);
	?>

	<div class="container">

		<div class="pagetitle">
			<h1>SAM Request Disposition</h1>
		</div>

		<?php
		if ($justification != "undef" && $startDate != "undef" && $endDate != "undef" && $approved != "undef" && $rID != "undef" && $obsType != "undef") {
			$startCheck = strtotime($startDate);
			$endCheck = strtotime($endDate);
			if ($startCheck == '') {
				echo "<div class='pagecontent'>\n";
				echo "<p>The start date you entered is not in 'MM/DD/YYYY' format or is invalid.  Please go back and correct the start date.</p>\n";
				echo "</div>\n";
			} elseif ($endCheck == '') {
				echo "<div class='pagecontent'>\n";
				echo "<p>The end date you entered is not in 'MM/DD/YYYY' format or is invalid.  Please go back and correct the end date.</p>\n";
				echo "</div>\n";
			} elseif ($scheduledDate != "undef" && $approved == 'No') {
				echo "<div class='pagecontent'>\n";
				echo "<p>You have scheduled a date for the SAM but not marked the SAM as approved.  Please go back and correct this.</p>\n";
				echo "</div>\n";
			} elseif ($scheduledDate != "undef" && $approved == 'Pending') {
				echo "<div class='pagecontent'>\n";
				echo "<p>You have scheduled a date for the SAM but not marked the SAM as approved.  Please go back and correct this.</p>\n";
				echo "</div>\n";
			} elseif ($scheduledDate == "undef" && $approved == 'Yes') {
				echo "<div class='pagecontent'>\n";
				echo "<p>You have approved the SAM but not entered the scheduled date.  Please go back and correct this.</p>\n";
				echo "</div>\n";
			} elseif ($endDate <= $startDate) {
				echo "<div class='pagecontent'>\n";
				echo "<p>The end date you selected occurs before the start date.  Please go back and update your requested end date.</p>\n";
				echo "</div>\n";
			} else {
				if ($approved == 'No') {
					$disposition = ' has been rejected';
				}
				if ($approved == 'Yes') {
					$disposition = ' has been approved';
				}
				if ($approved == 'Pending') {
					$disposition = ' is still under review';
				}

				echo '<div class="pagecontent">';
				echo '<p>SAM Request ' . $rID . $disposition . '.</p>';
				echo '</div>';

				if ($scheduledDate == 'undef') { $scheduledDate = null; }
				$sql = "UPDATE requests SET justification = ?, startDate = ?, approved = ?, endDate = ?, obsType = ?, scheduledDate=?  WHERE rID=?";
				$types = 'ssssssi';
				$params = [$justification, $startDate, $approved, $endDate, $obsType, $scheduledDate, $rID];

				$stmt = mysqli_prepare($conn, $sql);
				if ($types !== '') {
					mysqli_stmt_bind_param($stmt, $types, ...$params);
				}
				mysqli_stmt_execute($stmt);
				mysqli_stmt_close($stmt);

				$sql = "SELECT r.nickname, u.email FROM users u, requests r WHERE r.uID=u.uID AND r.rID?";
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
				while ($row = mysqli_fetch_assoc($result)) {
					$nickname = $row['nickname'];
					$email = $row['email'];
				}
				mysqli_stmt_close($stmt);

				$headers = "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
				$headers .= 'From: noreply@yoursite.com' . "\r\n";
				$to = $email;
				$subject = "SAM Request Disposition";
				$body = "Your recent SAM request '" . $nickname . $disposition . " by the team.\n\n" .
					"You can fiew the full details of your request at https://yoursite.com/requests/requestview.php?rID=" . $rID;
				if (mail($to, $subject, $body, $headers)) {
					echo ("");
				} else {
					echo ("<p>Message delivery failed...</p>");
				}
			}
		}
		?>
		<br />
		<br />
		<br />
		<br />

	</div>

	<div class="fixed-bottom">
		<button type="button" style="float: right;" class="btn pmd-btn-fab btn-danger pmd-ripple-effect pmd-btn-raised"><a href="../../authentication/logout.php" style="color: #FFFFFF; font-weight: bold;">LOGOUT</a></button>
	</div>

	<?php
	mysqli_close($conn);
	include '../../includes/files/footer.php';
	?>

</body>

</html>
