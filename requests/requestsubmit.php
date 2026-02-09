<?php
include('../private/authenticate.php');

session_start();

if (!$_SESSION['user'] || !$_SESSION['password']) {
	header('Location: ../authentication/index.php');
	die();
} else {
	if (($result = authenticate($_SESSION['user'], $_SESSION['password'])) == NULL) {
		header('Location: ../authentication/index.php');
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

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
	
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</head>

<body>

	<?php
	include '../includes/files/header.php';
	date_default_timezone_set('UTC');
	if (isset($_POST['nickname']) && $_POST['nickname'] != '') {
		$nickname = $_POST['nickname'];
	} else {
		$nickname = 'undef';
	}
	if (isset($_POST['spatial']) && $_POST['spatial'] != '') {
		$spatial = $_POST['spatial'];
	} else {
		$spatial = 'undef';
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
	if (isset($_POST['username']) && $_POST['username'] != '') {
		$username = $_POST['username'];
	} else {
		$username = 'undef';
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

	#Injection protection
	$nickname = mysqli_real_escape_string($conn, $nickname);
	$spatial = mysqli_real_escape_string($conn, $spatial);
	$obsType = mysqli_real_escape_string($conn, $obsType);
	$justification = mysqli_real_escape_string($conn, $justification);
	$username = mysqli_real_escape_string($conn, $username);
	$startdate = mysqli_real_escape_string($conn, $startdate);
	$enddate = mysqli_real_escape_string($conn, $enddate);

	#XSS
	$nickname = strip_tags($nickname);
	$obsType = strip_tags($obsType);
	$justification = strip_tags($justification);
	$spatial = strip_tags($spatial);
	$username = strip_tags($username);
	$startdate = strip_tags($startdate);
	$enddate = strip_tags($enddate);
	?>

	<div class="container">

		<div class="pagetitle">
			<h1>Request a SAM</h1>
		</div>

		<?php
		if ($nickname != "undef" && $spatial != "undef" && $justification != "undef" && $username != "undef" && $startdate != "undef" && $enddate != "undef" && $obstype != "undef") {
			$startCheck = strtotime($startdate);
			$endCheck = strtotime($enddate);
			if ($startCheck == '') {
				echo "<div class='pagecontent'>\n";
				echo "<p>The start date you entered is not in 'DD/MM/YYYY' format or is invalid.  Please go back and correct the start date.</p>\n";
				echo "</div>\n";
			} elseif ($endCheck == '') {
				echo "<div class='pagecontent'>\n";
				echo "<p>The end date you entered is not in 'DD/MM/YYYY' format or is invalid.  Please go back and correct the end date.</p>\n";
				echo "</div>\n";
			} else {
				$sql = "SELECT COUNT(*) AS count FROM requests WHERE nickname=? AND uID=(SELECT uID FROM users WHERE email=?)";
		        $stmt = mysqli_prepare($conn, $sql);
				mysqli_stmt_bind_param(
							$stmt,
							"ss",
							$nickname,
							$username
				);				
				mysqli_stmt_execute($stmt);

				$result = mysqli_stmt_get_result($stmt);	
				while ($row = mysqli_fetch_assoc($result)) {
					$nicknameCheck = $row['count'];
					if ($nicknameCheck > 0) {
						echo "<div class='pagecontent'>\n";
						echo "<p>You have already submitted a request with with this nickname (" . $nickname . ").  Please go back and change your request's nickname.</p>\n";
						echo "</div>\n";
					} elseif ($enddate <= $startdate) {
						echo "<div class='pagecontent'>\n";
						echo "<p>The end date you selected occurs before the start date.  Please go back and update your requested end date.</p>\n";
						echo "</div>\n";
					} else {
						echo "<div class='pagecontent'>\n";
						echo "<p>Your request has been submitted to the Team. They will review your request and you will recieve a notice about the status of your request.</p>\n";
						echo "</div>\n";

						if ($obsType == 'SAM') {
							$polygon = "POLYGON((";
							$polygon .= $spatial;
							$p = explode(",", $spatial);
							$polygon .= "," . $p[0];
							$polygon .= "))";
						} elseif ($obsType == 'target') {
							$polygon = "POINT(" . $spatial . ")";
						}

						$sql = "INSERT INTO requests SET uID=(SELECT uID FROM users WHERE email=?), justification=?, nickname=?, requestRegion=ST_GEOMFROMTEXT(?), startDate=?, endDate=?, obsType=?";
						$stmt = mysqli_prepare($conn, $sql);
						mysqli_stmt_bind_param(
							$stmt,
							"sssssss",
							$username,
							$justification,
							$nickname,
							$polygon,
							$startdate,
							$enddate,
							$obsType
						);
						mysqli_stmt_execute($stmt);
						mysqli_stmt_close($stmt);

						$sql = "SELECT r.rID, CONCAT(u.firstName, ' ', u.lastName) AS name FROM users u, requests r  WHERE u.uID=r.uID AND r.nickname=? AND u.email=?";
						$stmt = mysqli_prepare($conn, $sql);
						mysqli_stmt_bind_param(
							$stmt,
							"ss",
							$nickname,
							$username
						);
						mysqli_stmt_execute($stmt);

						$result = mysqli_stmt_get_result($stmt);
						while ($row = mysqli_fetch_assoc($result)) {
							$name = $row['name'];
							$rID = $row['rID'];
						}
						mysqli_stmt_close($stmt);

						$polytrim = ltrim($polygon, $polygon[0]);
						$polytrim = rtrim($polytrim, "'");

						$headers = "MIME-Version: 1.0" . "\r\n";
						$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
						$headers .= 'From: noreply@yoursite.com' . "\r\n";
						$to = $username;
						$subject = "New SAM Request";
						$body = "A new SAMs request (Request ID #" . $rID . ") has been submitted by " . $name . ".<br /><br />" .
							"You can view the full details of the request at <a href='https://yourwebsite.com/requests/requestview.php?rID=" . $rID . "'>https://yourwebsite.com/requests/requestview.php?rID=" . $rID . "</a><br /><br />" .
							"The user has requested this SAM for the following reason: '" . $justification . "'<br /><br />" .
							"To approve or reject this request, <a href='https://yourwebsite.com/requests/disposition/requestdisposition.php?rID=" . $rID . "'>click here</a>.<br /><br />";
						if (mail($to, $subject, $body, $headers)) {
							echo ("");
						} else {
							echo ("<p>Message delivery failed...</p>");
						}
					}
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
		<button type="button" style="float: right;" class="btn pmd-btn-fab btn-danger pmd-ripple-effect pmd-btn-raised"><a href="../authentication/logout.php" style="color: #FFFFFF; font-weight: bold;">LOGOUT</a></button>
	</div>

	<?php
	mysqli_close($conn);
	include '../includes/files/footer.php';
	?>

</body>

</html>
