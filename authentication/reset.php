<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<title>Reset Password | SAMs</title>

	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.6.0/dist/leaflet.css" integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ==" crossorigin="" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/0.4.2/leaflet.draw.css" />
	<link href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css" rel="stylesheet">
	<link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
	
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
	<script src="https://unpkg.com/leaflet@1.6.0/dist/leaflet.js" integrity="sha512-gZwIG9x3wUXg2hdXF6+rVkLF/0Vi9U8D2Ntg4Ga5I5BZpVkVxlJWbSQtXPSiUTtC0TjtGOmxa1AJPuV0CPthew==" crossorigin=""></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/0.4.2/leaflet.draw.js"></script>
	<script src="https://code.jquery.com/jquery-3.4.1.js" integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU=" crossorigin="anonymous"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
	<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js" type="text/javascript"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>

	<script type='text/javascript'>
		function reject_form() {
			return false;
		}

		function accept_form() {
			return true;
		}

		function val_form(frm) {
			if (frm.email.value == "") {
				alert("E-Mail is a required field.");
				frm.firstName.focus();
				return reject_form();
			}
			return accept_form();
		}
	</script>


	<?php
	require_once('../private/config.php');
	if (!($conn = @mysqli_connect($server, $webuser, $webpass, $db)))
		die("Could not Connect to the database");
	?>

</head>

<body>

	<?php include '../includes/files/header.php'; ?>

	<div class="container">

		<div class="pagetitle">
			<h1>Reset Password</h1>
		</div>

		<div id="pagecontent">
			<?php

			date_default_timezone_set('UTC');

			if (isset($_POST['email']) && $_POST['email'] != '') {
				$email = $_POST['email'];
			} else {
				$email = 'undef';
			}

			// Initial stab at blocking out SQL injections
			$email = mysqli_real_escape_string($conn, $email);

			// Now look at XSS
			$email = strip_tags($email);

			$email = htmlentities($email);

			if ($email == "undef") {
				$footer = "standard";
				echo "<div class='row'>\n";

				echo "<div class='col-xs-6' style='width: 500px; border: 2px solid black; border-radius: 5px; margin-right: 25px; margin-left: 20px; padding: 15px;'>\n";
				echo "<p>Enter the e-mail address for your user account.  If the e-mail address is correct, you will be sent instructions on how to reset your password.</p>\n";
				echo "<hr /><br />\n";
				echo "<form method='POST' action='reset.php' name='reset' onsubmit='return val_form(this);'>\n";
				echo "<div class='form-row'>\n";
				echo "<div class='form-group col-md-6'>\n";
				echo "<label for='email'>E-Mail Address</label>\n";
				echo "<input type='text' class='form-control' id='email' name='email' tabindex='1'/>\n";
				echo "</div>\n";
				echo "</div>\n";
				echo "<div class='form-row'>\n";
				echo "<br />\n";
				echo "<button type='submit' class='btn btn-primary' tabindex='2' />SUBMIT</button>\n";
				echo "</div>\n";
				echo "</form>\n";
				echo "</div>\n";
				echo "</div>\n";
			} else {
				echo "Your submission was successful, please check your e-mail for further instruction.\n";
				$footer = "bottom";
				$sql = "SELECT uID FROM users WHERE email=?";
				$stmt = mysqli_prepare($conn, $sql);
				mysqli_stmt_bind_param(
					$stmt,
					"s",
					$email
				);
				mysqli_stmt_execute($stmt);

				$result = mysqli_stmt_get_result($stmt);
				$emailCheck = mysqli_num_rows($result);
				mysqli_stmt_close($stmt);
				if ($emailCheck == 1) {
					// Exaple of how to generate a random key.
					// Use your method of choice.
					$key = hash('ripemd160', $email . random_int(0,1000000000));
					$sql = "UPDATE users SET resetKey=? WHERE email=?";
					$stmt = mysqli_prepare($conn, $sql);
					mysqli_stmt_bind_param(
						$stmt,
						"ss",
						$key,
						$email
					);
					mysqli_stmt_execute($stmt);
					mysqli_stmt_close($stmt);

					$message = "You recently requested to reset your password on the SAMs website.  Please follow this link to complete the reset:\n\n
							https://yourwebsite.com/authentication/resetpassword.php?resetkey=" . $key . "\n\n";
					$headers = "From: SAMs Website <webmaster@yourwebsite.com>\r\n";
					$headers .= "Reply-To:noreply@yourwebsite.com\r\n";
					$headers .= "X-Mailer: PHP/" . phpversion();
					mail($email, 'SAMs Password Reset', $message, $headers);
				}
			}
			?>
		</div>
	</div>

	<div style="height: 350px;"></div>

	<?php include '../includes/files/footer.php'; ?>

</body>

</html>
