<?php
include('../private/authenticate.php');

session_start();

if (!$_SESSION['user'] || !$_SESSION['password']) {
  header('Location: ../authenticate/index.php');
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

  <title>Submit Subset Request | SAMs</title>

  <?php
  include '../private/config.php';
  if (!($conn = @mysqli_connect($server, $webuser, $webpass, $db)))
    die("Could not Connect to the database");
  ?>

  <link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
	
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
  <script src="https://code.jquery.com/jquery-3.4.1.js" integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU=" crossorigin="anonymous"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
</head>

<body>

  <?php
  include '../includes/files/header.php';
  date_default_timezone_set('UTC');
  if (isset($_POST['targetID']) && $_POST['targetID'] != '') {
    $targetID = $_POST['targetID'];
  } else {
    $targetID = 'undef';
  }
  if (isset($_POST['startDateTime']) && $_POST['startDateTime'] != '') {
    $startDateTime = $_POST['startDateTime'];
  } else {
    $startDateTime = 'undef';
  }
  if (isset($_POST['endDateTime']) && $_POST['endDateTime'] != '') {
    $endDateTime = $_POST['endDateTime'];
  } else {
    $endDateTime = 'undef';
  }
  if (isset($_POST['product']) && $_POST['product'] != '') {
    $product = $_POST['product'];
  } else {
    $product = 'undef';
  }

  #Injection protection
  $targetID = mysqli_real_escape_string($conn, $targetID);
  $startDateTime = mysqli_real_escape_string($conn, $startDateTime);
  $endDateTime = mysqli_real_escape_string($conn, $endDateTime);
  $product = mysqli_real_escape_string($conn, $product);

  #XSS
  $targetID = strip_tags($targetID);
  $startDateTime = strip_tags($startDateTime);
  $endDateTime = strip_tags($endDateTime);
  $product = strip_tags($product);

  #HTML entities
  $targetID = htmlentities($targetID);
  $startDateTime = htmlentities($startDateTime);
  $endDateTime = htmlentities($endDateTime);
  $product = htmlentities($product);
  ?>

  <div class="container">

    <div class="pagetitle">
      <h1>Subset Request Submitted</h1>
    </div>

    <div class="pagecontent">
      <?php
      if ($startDateTime != "undef" && $endDateTime != "undef" && $targetID != "undef") {
        $startDateTime = date('Y-m-d\TH:i:s.000\Z', strtotime($startDateTime));
        $endDateTime = date('Y-m-d\TH:i:s.000\Z', strtotime($endDateTime));

        #Must have an Earthdata account to download
        if ($product == 'xco2') {
          $parameters = "{\"methodname\":\"subset\",\"args\":{\"box\":[-180,-53,180,53],\"crop\":true,\"agent\":\"SUBSET_LEVEL2\",\"role\":\"subset\",\"start\":\"" . $startDateTime . "\", \"end\":\"" . $endDateTime . "\",\"data\":[{\"datasetId\":\"OCO3_L2_Lite_FP_11r\"}]}}";
        } else {
          die('No valid product found.  Please contact the SAMs webmaster with the details of the subset job you are tyring to process.');
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://disc.gsfc.nasa.gov/service/subset/jsonwsp');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        $headers = array();
        $headers[] = 'Accept: application/json, text/plain, */*';
        $headers[] = 'Content-Type: application/json;charset=utf-8';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
          echo 'Error:' . curl_error($ch);
        } else {
          echo 'Your job  has been submitted.  You will receive an e-mail when it is ready.';
        }
        curl_close($ch);
        $resultInfo = json_decode($result, TRUE);
        $email = $_SESSION['user'];
        $sql = "INSERT INTO jobs SET status=?, uID=(SELECT uID FROM users WHERE email=?), targetID=?, jobID=?, sessionID=?, parameters=?";
        $stmt = mysqli_prepare($conn, $sql);
				mysqli_stmt_bind_param(
            $stmt, 
            'ssssss', 
            $resultInfo['result']['Status'],
            $email,
            $targetID,
            $resultInfo['result']['jobId'],
            $resultInfo['result']['sessionId'],
            $parameters
        );
				mysqli_stmt_execute($stmt);
				mysqli_stmt_close($stmt);
      }
      ?>
    </div>


  </div>

  <div class="fixed-bottom">
    <button type="button" style="float: right;" class="btn pmd-btn-fab btn-danger pmd-ripple-effect pmd-btn-raised"><a href="logout.php" style="color: #FFFFFF; font-weight: bold;">LOGOUT</a></button>
  </div>

  <?php
  mysqli_close($conn);
  include '../includes/files/footer.php';
  ?>

</body>

</html>
