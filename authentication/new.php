<?php
  error_reporting(0);
  if (isset($_GET['breadcrumb'])){
    $breadcrumb = $_GET['breadcrumb'];
    $location = 'Location: ..' . str_replace('--', '?', $breadcrumb);
  } elseif (isset($_POST['breadcrumb'])) {
    $breadcrumb = $_POST['breadcrumb'];
    $location = 'Location: ..' . str_replace('--', '?', $breadcrumb);
} else {
  $location = 'Location: ../index.php';
}
include('../private/authenticate.php');

session_start();
if (isset($_SESSION['user']) && $_SESSION['password']) {
  header($location);
  die();
} elseif (isset($_POST['user']) && isset($_POST['password'])) {
  if ($result = authenticate($_POST['user'], $_POST['password']) == NULL) {
    $fail = '<h2>Authorization failed, please try logging in again.</h2>';
  } else {
    $_SESSION['user'] = $_POST['user'];
    $_SESSION['password'] = $_POST['password'];
    header($location);
  }
}

$breadcrumb = htmlspecialchars($breadcrumb);
$breadcrumb = strip_tags($breadcrumb);
$breadcrumb = addslashes($breadcrumb);

$breadcrumb = htmlspecialchars($breadcrumb);
$breadcrumb = strip_tags($breadcrumb);
$breadcrumb = addslashes($breadcrumb);

?>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Register for a New Account | SAMs</title>

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
  <script type='text/javascript'>
    function reject_form() {
      return false;
    }

    function accept_form() {
      return true;
    }

    function val_form(frm) {
      if (frm.firstName.value == "") {
        alert("First Name is a required field.");
        frm.firstName.focus();
        return reject_form();
      }
      if (frm.lastName.value == "") {
        alert("Last Name is a required field.");
        frm.lastName.focus();
        return reject_form();
      }
      if (frm.email.value == "") {
        alert("E-Mail Address is a required field.");
        frm.email.focus();
        return reject_form();
      }
      if (frm.affiliation.value == "") {
        alert("Affiliation is a required field.");
        frm.affiliation.focus();
        return reject_form();
      }
      if (frm.origpassword.value == "") {
        alert("Password is a required field.");
        frm.origpassword.focus();
        return reject_form();
      }
      if (frm.repassword.value == "") {
        alert("Please re-enter your password.");
        frm.repassword.focus();
        return reject_form();
      }

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
</head>


<body>
  <?php
  include '../includes/files/header.php';
  date_default_timezone_set('UTC');
  ?>

  <div class="container">

    <div class="pagetitle">
      <h1>Register for a New Account</h1>
    </div>

    <div class="pagecontent">
      <br /><br />
      <?php echo $fail; ?>

      <div class="row">

        <div class="col-xs-6" style="width: 500px; border: 2px solid black; border-radius: 5px; padding: 15px;">
          <h2>Sign-Up</h2>
          <hr /><br />
          <form method="POST" action="registration.php" enctype="multipart/form-data" name="registration" onsubmit="return val_form(this);">
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="firstName">First Name</label>
                <input type="text" class="form-control" id="firstName" name="firstName" tabindex="4" />
              </div>
            </div>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="lastName">Last Name</label>
                <input type="text" class="form-control" id="lastName" name="lastName" tabindex="5" />
              </div>
            </div>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="email">E-Mail Address</label>
                <input type="text" class="form-control" id="email" name="email" tabindex="6" />
              </div>
            </div>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="affiliation">Affiliation</label>
                <input type="text" class="form-control" id="affiliation" name="affiliation" tabindex="7" />
              </div>
            </div>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="origpassword">Password</label>
                <input type="password" class="form-control" id="origpassword" name="origpassword" tabindex="8" />
              </div>
            </div>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="repassword">Re-Enter Password</label>
                <input type="password" class="form-control" id="repassword" name="repassword" tabindex="9" /><br />
              </div>
            </div>
            <div class="form-row">
              <button type="submit" class="btn btn-primary" tabindex="10" />SUBMIT</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <?php
  include '../includes/files/footer.php';
  ?>

</body>

</html>
