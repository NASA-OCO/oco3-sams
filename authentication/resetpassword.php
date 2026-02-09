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

			var onepass = document.getElementById('origpassword').value;
			var twopass = document.getElementById('repassword').value;

			if (onepass != twopass) {
				alert("Your password does not match.  Please reenter your password.");
				frm.origpassword.focus();
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

			$resetkey = $_GET['resetkey'];
			$resetkey = mysqli_real_escape_string($conn, $resetkey);
			$resetkey = strip_tags($resetkey);

			$sql = "SELECT uID FROM users WHERE resetKey=?";
			$stmt = mysqli_prepare($conn, $sql);
			mysqli_stmt_bind_param(
				$stmt,
				"s",
				trim($resetkey)
			);
			mysqli_stmt_execute($stmt);

			$result = mysqli_stmt_get_result($stmt);
			$keyCheck = mysqli_num_rows($result);
			mysqli_stmt_close($stmt);

			if ($resetkey != "undef" && $keyCheck === 1) {
				$footer = "standard";
				echo "<div class='row'>\n";

				echo "<div class='col-xs-6' style='width: 500px; border: 2px solid black; border-radius: 5px; margin-right: 25px; margin-left: 20px; padding: 15px;'>\n";
				echo "<p>Please enter your new password.</p>\n";
				echo "<hr /><br />\n";
				echo "<form method='POST' action='resetpasswordconfirm.php' name='reset' onsubmit='return val_form(this);'>\n";
				echo "<div class='form-row'>\n";
				echo "<div class='form-group col-md-6'>\n";
				echo "<label for='origpassword'>New Password</label>\n";
				echo "<input type='password' class='form-control' id='origpassword' name='origpassword' tabindex='1'/>\n";
				echo "</div>\n";
				echo "</div>\n";
				echo "<div class='form-row'>\n";
				echo "<div class='form-group col-md-6'>\n";
				echo "<label for='repassword'>Re-Enter Password</label>\n";
				echo "<input type='password' class='form-control' id='repassword' name='repassword' tabindex='2'/>\n";
				echo "</div>\n";
				echo "</div>\n";
				echo "<div class='form-row'>\n";
				echo "<br />\n";
				echo "<input type='hidden' name='resetkey' value='" . $resetkey . "'>\n";
				echo "<button type='submit' class='btn btn-primary' tabindex='3' />SUBMIT</button>\n";
				echo "</div>\n";
				echo "</form>\n";
				echo "</div>\n";
				echo "</div>\n";
			} else {
				$footer = "bottom";
				echo "We're sorry, you cannot reset your password at this time.  Please go back to the <a href='index.php'>login page</a> or contact the webmaster.";
			}
			?>
		</div>
	</div>

	<div style="height: 350px;"></div>

	<?php include '../includes/files/footer.php'; ?>

</body>

</html>
