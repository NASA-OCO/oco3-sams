<?php
include('../../private/authenticate.php');

session_start();

$rID = $_GET['rID'];

if (!$_SESSION['user'] || !$_SESSION['password']) {
	header('Location: ../../authentication/index.php?breadcrumb=/requests/disposition/requestdisposition.php--rID=' . $rID);
	die();
} else {
	if (($result = authenticate($_SESSION['user'], $_SESSION['password'])) == NULL) {
		header('Location: ../../authentication/index.php?breadcrumb=/requests/disposition/requestdisposition.php--rID=' . $rID);
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

	<link href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css" rel="stylesheet">
	<link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
	
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
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
			if (frm.startDate.value == "") {
				alert("Start Date is a required field.");
				frm.startDate.focus();
				return reject_form();
			}
			if (frm.endDate.value == "") {
				alert("End Date is a required field.");
				frm.endDate.focus();
				return reject_form();
			}
			if (frm.justification.value == "") {
				alert("Justification is a required field.");
				frm.justification.focus();
				return reject_form();
			}
			return accept_form();
		}
	</script>

</head>

<body>

	<?php
	include '../../includes/files/header.php';
	date_default_timezone_set('UTC');
	$rID = $_GET["rID"];
	$rID = mysqli_real_escape_string($conn, $rID);
	$rID = strip_tags($rID);
	$rID = intval($rID);
	?>

	<div class="container">

		<div class="pagetitle">
			<h1>Request Disposition</h1>
		</div>

		<div class="pagecontent">
			<p>
				Please use the below form to approve or reject a SAM request.<br />
				You can <a href="/requests/requestview.php?rID=<?php echo htmlspecialchars($rID, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">view the full details of the request</a> and make any updates below.<br />
You can also view <a href="https://yoursite.com/requests/requesthistory.php" target="_blank">the full request history</a>.</p>
		</div>
		<br />
		<div>
			<form method="POST" action="requestdispositionsubmit.php" name="submit request" onsubmit="return val_form(this);">
				<?php
				$sql = 'SELECT r.startDate, r.endDate, r.justification, r.approved, r.scheduledDate, r.obsType, CONCAT(u.lastName, ", ", u.firstName) AS name, u.email FROM requests r, users u WHERE r.rID=? AND r.uID=u.uID';
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
					$obsType = $row['obsType'];
					$justification = $row['justification'];
					$approved = $row['approved'];
					$scheduledDate = $row['scheduledDate'];
					$name = $row['name'];
					$email = $row['email'];
				}
				mysqli_stmt_close($stmt);
				?>

				<div class="form-row">
					<div class="form-group col-md-6">
						<label for="name">Submitted By</label>
						<input type="text" class="form-control" id="name" name="name" tabindex="1" value="<?php echo htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" disabled />
					</div>
					<div class="form-group col-md-6">
						<label for="email">Submitter E-Mail</label>
						<input type="text" class="form-control" id="email" name="email" tabindex="2" value="<?php echo htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" disabled />
					</div>
				</div>
				<div class="form-row">
					<div class="form-group col-md-6">
						<label for="startDate">SAM Start Date*</label>
						<input type="date" class="form-control" id="startDate" name="startDate" tabindex="3" value="<?php echo htmlspecialchars($startDate, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" />
					</div>
					<div class="form-group col-md-6">
						<label for="endDate">SAM End Date*</label>
						<input type="date" class="form-control" id="endDate" name="endDate" tabindex="4" value="<?php echo htmlspecialchars($endDate, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" />
					</div>
				</div>
				<div class="form-group">
					<label for="justification">Science Justification</label>
					<input type="textarea" col="20" row="200" class="form-control" id="justification" name="justification" tabindex="5" value="<?php echo htmlspecialchars($justification, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" />
				</div>
				<div class="form-row">
					<div class="form-group col-md-6">
						<label for="scheduledDate">Scheduled Date</label>
						<input type="text" class="form-control" id="scheduledDate" name="scheduledDate" placeholder="e.g., June 20024; leave blank if rejecting" tabindex="6" <?php if (htmlspecialchars($scheduledDate, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') != '') {
																																													echo 'value="<?php echo htmlspecialchars($scheduledDate, ENT_QUOTES | ENT_SUBSTITUTE, \'UTF-8\'); ?>"';
																																												} else {
																																													echo 'value=""';
																																												} ?> />
					</div>
					<div class="form-group col-md-6">
						<label for="obsType">Observation Type</label>
						<select name="obsType" id="obsType" tabindex="6">
							<option value="SAM" <?php if ($obsType == "SAM") {
													echo 'SELECTED';
												} ?>>Snapshot Area Map (SAM)</option>
							<option value="target" <?php if ($obsType == "target") {
														echo 'SELECTED';
													} ?>>Target</option>
						</select>
					</div>
				</div>
				<div class="form-group">
					<div class="form-group col-md-6">
						<label for="approved">Approved</label></br>
						<select name="approved" id="approved" tabindex="7">
							<?php
							if ($approved == 'Pending') {
								echo '<option value="Pending" selected>Pending</option>';
							} else {
								echo '<option value="Pending">Pending</option>';
							}
							if ($approved == 'No') {
								echo '<option value="No" selected>No</option>';
							} else {
								echo '<option value="No">No</option>';
							}
							if ($approved == 'Yes') {
								echo '<option value="Yes" selected>Yes</option>';
							} else {
								echo '<option value="Yes">Yes</option>';
							}
							?>
						</select>
					</div>
				</div>
				<input type="hidden" name="rID" value="<?php echo htmlspecialchars($rID, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" />
				<button type="submit" class="btn btn-primary" tabindex="8">SUBMIT</button>
			</form>
			<p><i><small>*Dates must be +2 weeks from "today", and no further out than 2 years from "today"</small></i></p>
		</div>
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
