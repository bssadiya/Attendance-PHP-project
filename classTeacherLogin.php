<?php
include 'Includes/dbcon.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="img/logo/attnlg.jpg" rel="icon">
  <title>AMS - Class Teacher Login</title>

  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
</head>

<body class="bg-gradient-login">

<div class="container-login">
  <div class="row justify-content-center">
    <div class="col-xl-10 col-lg-12 col-md-9">
      <div class="card shadow-sm my-5">
        <div class="card-body p-0">
          <div class="row">
            <div class="col-lg-12">
              <div class="login-form">

                <div class="text-center">
                  <img src="img/logo/attnlg.jpg" style="width:100px;height:100px">
                  <br><br>
                  <h1 class="h4 text-gray-900 mb-4">Class Teacher Login</h1>
                </div>

                <form method="POST">
                  <div class="form-group">
                    <input type="email" class="form-control" required
                           name="username" placeholder="Email Address">
                  </div>

                  <div class="form-group">
                    <input type="password" class="form-control" required
                           name="password" placeholder="Password">
                  </div>

                  <div class="form-group">
                    <button type="submit" name="login"
                            class="btn btn-primary btn-block">
                      Login
                    </button>
                  </div>
                </form>

<?php
if (isset($_POST['login'])) {

  $email = trim($_POST['username']);
  $password = $_POST['password'];

  $stmt = $conn->prepare(
    "SELECT Id, firstName, lastName, emailAddress, password,
            classId, classArmId,
            status, failed_attempts
     FROM tblclassteacher
     WHERE emailAddress = ?"
  );
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows !== 1) {
    echo "<div class='alert alert-danger'>Invalid email or password.</div>";
    exit();
  }

  $user = $result->fetch_assoc();

  /* 🔒 ACCOUNT BLOCKED */
  if ($user['status'] === 'blocked') {
    echo "<div class='alert alert-danger'>
            Account locked due to multiple failed attempts.<br>
            Please check your email to unlock.
          </div>";
    exit();
  }

  /* ✅ PASSWORD CORRECT */
  if (password_verify($password, $user['password'])) {

    // reset failed attempts
    $reset = $conn->prepare(
      "UPDATE tblclassteacher
       SET failed_attempts = 0
       WHERE Id = ?"
    );
    $reset->bind_param("i", $user['Id']);
    $reset->execute();

    session_regenerate_id(true);

    $_SESSION['userId']     = $user['Id'];
    $_SESSION['firstName']  = $user['firstName'];
    $_SESSION['lastName']   = $user['lastName'];
    $_SESSION['emailAddress'] = $user['emailAddress'];
    $_SESSION['classId']    = $user['classId'];
    $_SESSION['classArmId'] = $user['classArmId'];

    echo "<script>window.location='ClassTeacher/index.php'</script>";
    exit();
  }

  /* ❌ PASSWORD WRONG */
  $attempts = $user['failed_attempts'] + 1;

  /* 🚨 LOCK ACCOUNT */
  if ($attempts >= 5) {

    $token  = bin2hex(random_bytes(32));
    $expiry = date("Y-m-d H:i:s", strtotime("+30 minutes"));

    $lock = $conn->prepare(
      "UPDATE tblclassteacher
       SET failed_attempts = ?,
           status = 'blocked',
           unlock_token = ?,
           token_expiry = ?
       WHERE Id = ?"
    );
    $lock->bind_param("issi", $attempts, $token, $expiry, $user['Id']);
    $lock->execute();

    /* 📧 SEND UNLOCK EMAIL */
    $unlockLink = "http://localhost/attendance-php/unlockByEmail.php?token=$token";

    $subject = "Account Locked - Unlock Required";
    $message = "Your account has been locked after multiple failed login attempts.\n\n";
    $message .= "Click the link below to unlock your account:\n";
    $message .= $unlockLink . "\n\n";
    $message .= "This link will expire in 30 minutes.";

    $headers = "From: no-reply@attendance-system.com";

    mail($user['emailAddress'], $subject, $message, $headers);

    echo "<div class='alert alert-danger'>
            Account locked after 5 failed attempts.<br>
            Unlock link sent to your email.
          </div>";
    exit();
  }

  /* ⏳ STILL ALLOWED */
  $upd = $conn->prepare(
    "UPDATE tblclassteacher
     SET failed_attempts = ?
     WHERE Id = ?"
  );
  $upd->bind_param("ii", $attempts, $user['Id']);
  $upd->execute();

  echo "<div class='alert alert-danger'>
          Invalid password. Attempt $attempts of 5.
        </div>";
}
?>

                <hr>
                <div class="text-center">
                  <a class="font-weight-bold small" href="forgotPassword.php">
                    Forgot Password?
                  </a>
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="js/ruang-admin.min.js"></script>
</body>
</html>
