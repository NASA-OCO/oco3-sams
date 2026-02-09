<?php

error_reporting(0);
date_default_timezone_set('UTC');


# All the functions below are examples of how to check and handle
# user credentials.  Please use your methods of choice and
# harden as needed.

function authenticate($user,$password) {
  # This function is used to authenticate a user
  # based on their username and password, which is
  # checked against your database.  It is used for
  # the 'favorites' and 'requests' areas of the site.
  $PHP_AUTH_USER=$user;
  $PHP_AUTH_PW=$password;

  require_once('config.php');
  if(!($conn = @ mysqli_connect($server,$webuser,$webpass,$db)))
  die("Could not connect to the database");

  if ($PHP_AUTH_USER != "" && $PHP_AUTH_PW != "") {
    $stmt = $conn->prepare('SELECT password, uID FROM users WHERE email= ?');
    $stmt->bind_param('s', $PHP_AUTH_USER);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
      $getPassword = $row['password'];
      $uID = $row['uID'];
    }
    if (password_verify($PHP_AUTH_PW, $getPassword)) {
      mysqli_close($conn);
      return $uID;
    }
  }
  mysqli_close($conn);
  return NULL;
}

function authorize($user) {
  # This function is used to authorize an authenticated user
  # access to the 'requests/disposition' area.  These are usually
  # members of your team who will approve requests.  You must
  # enter their email address below and it should match their 
  # email address in the database (which should be their username).
  $lowerUser = strtolower($user);
  $authUsers = ['user1@mail.com', 'user2@mail.com'];

  if (in_array($lowerUser, $authUsers)) {
        return 'yes';
  } else {
        return NULL;
  }

}

function lockStatus($conn, $user) {
  # This function locks a user's account if they have too many
  # login or password reset tries.
  $stmt = $conn->prepare('SELECT locked FROM users WHERE email=?');
  $stmt->bind_param('s', $user);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_assoc()) {
    $locked = $row['locked'];
  }
  if ($locked == 1) {
    return 1;
  } else {
    return 0;
  }
}

function incrementAttempt($conn, $user) {
  # This function increments attempts to reset a password.  After
  # x number of attempts, their account is locked.
  $stmt = $conn->prepare('SELECT attempt, attemptTime FROM users WHERE email=?');
  $stmt->bind_param('s', $user);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_assoc()) {
    $attempt = $row['attempt'];
  }
  if ($attempt < 2) {
    $stmt = $conn->prepare('UPDATE users SET attempt = attempt+1, attemptTime=UTC_TIMESTAMP() WHERE email=?');
    $stmt->bind_param('s', $user);
    $stmt->execute();
  } elseif ($attempt == 2) {
    $stmt = $conn->prepare('UPDATE users SET attempt = attempt+1, attemptTime=UTC_TIMESTAMP(), locked=1 WHERE email=?');
    $stmt->bind_param('s', $user);
    $stmt->execute();
  } else {
    $nothing = 0;
  }

  return;
}

function unlock($conn, $user) {
  # Thus function unlocks a users's account.
  $now = new DateTime(date("Y-m-d H:i:s"));
  $stmt = $conn->prepare('SELECT attemptTime, locked FROM users WHERE email=?');
  $stmt->bind_param('s', $user);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_assoc()) {
    $attemptTime = $row['attemptTime'];
    $locked = $row['locked'];
  }
  $lastAttempt = new DateTime($attemptTime);
  if ($locked == 1) {
    $interval = $lastAttempt->diff($now);
    $minutes = $interval->days * 24 * 60;
    $minutes += $interval->h * 60;
    $minutes += $interval->i;
    if ($minutes > 2) {
      $stmt = $conn->prepare('UPDATE users SET locked=0, attempt=0, attemptTime="1901-01-01 00:00:00" WHERE  email=?');
      $stmt->bind_param('s', $user);
      $stmt->execute();
    }
  }

  return;
}
?>