<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<title>Enter New Password | SAMs</title>

	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.6.0/dist/leaflet.css" integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ==" crossorigin="" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/0.4.2/leaflet.draw.css" />
	<link href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css" rel="stylesheet">
	<link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
	
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>	<script src="https://unpkg.com/leaflet@1.6.0/dist/leaflet.js" integrity="sha512-gZwIG9x3wUXg2hdXF6+rVkLF/0Vi9U8D2Ntg4Ga5I5BZpVkVxlJWbSQtXPSiUTtC0TjtGOmxa1AJPuV0CPthew==" crossorigin=""></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/0.4.2/leaflet.draw.js"></script>
	<script src="https://code.jquery.com/jquery-3.4.1.js" integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU=" crossorigin="anonymous"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
	<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js" type="text/javascript"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>

	<?php
	require_once('../private/config.php');
	if (!($conn = @mysqli_connect($server, $webuser, $webpass, $db)))
		die("Could not Connect to the database");
	?>

</head>

<body>

	<?php include '../includes/files/header.php';	?>

	<div class="container">

		<div class="pagetitle">
			<h1>Reset Password</h1>
		</div>

		<div id="pagecontent">
			<?php

			date_default_timezone_set('UTC');

			if (isset($_POST['origpassword']) && $_POST['origpassword'] != '') {
				$origpassword = $_POST['origpassword'];
			} else {
				$origpassword = 'undef';
			}
			if (isset($_POST['resetkey']) && $_POST['resetkey'] != '') {
				$resetkey = $_POST['resetkey'];
			} else {
				$resetkey = 'undef';
			}

			// Now look at XSS
			$origpassword = strip_tags($origpassword);
			$resetkey = strip_tags($resetkey);

			// Exammple of how to check for a strong password.  Use your methods of choice.
			$uppercase = preg_match('@[A-Z]@', $origpassword);
			$lowercase = preg_match('@[a-z]@', $origpassword);
			$number    = preg_match('@[0-9]@', $origpassword);
			$specialChars = preg_match('@[^\w]@', $origpassword);
			if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($origpassword) < 8 || strlen($origpassword) > 30) {
					echo 'Password should be 8 - 30 characters in length and should include at least one upper case letter, one number, and one special character.';
			}

			// Initial stab at blocking out SQL injections
			$origpassword = mysqli_real_escape_string($conn, $origpassword);
			$resetkey = mysqli_real_escape_string($conn, $resetkey);

			$sql = "SELECT password, email FROM users WHERE resetKey=?";
			$stmt = mysqli_prepare($conn, $sql);
			mysqli_stmt_bind_param(
				$stmt,
				"s",
				$resetkey
			);
			mysqli_stmt_execute($stmt);

			$result = mysqli_stmt_get_result($stmt);
			$row = mysqli_fetch_assoc($result);
			mysqli_stmt_close($stmt);
			$oldPassword = $row['password'];
			$email = $row['email'];

			if (password_verify($origpassword, $oldPassword)) {
				die('You have previously used this password.  Please go back and use a different password.');
			}
			if ($email == $origpassword) {
					die("Sorry, you may not use your email address as your password.  Please go back and choose a different password.");
			}
			
			echo "Your submission was successful, please <a href='index.php'>login</a> with your new password.\n";
			// This is an example of how to hash and store a password,
			// you can use your method or algorithm of choice.
			$passHash = password_hash($origpassword, PASSWORD_ARGON2I);
			$sql = "UPDATE users SET password=?, resetKey = NULL WHERE resetKey=?";
				$stmt = mysqli_prepare($conn, $sql);
				mysqli_stmt_bind_param(
					$stmt,
					"ss",
					$passHash,
					$resetkey
				);
				mysqli_stmt_execute($stmt);
				mysqli_stmt_close($stmt);
			?>
		</div>
	</div>

	<div style="height: 350px;"></div>

	<?php include '../includes/files/footer.php'; ?>

</body>

</html>
