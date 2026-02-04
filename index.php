<?php
include '../Includes/dbcon.php';
include '../Includes/session.php';

/* ===============================
   1️⃣ SESSION CHECK
================================ */
if (!isset($_SESSION['userId'])) {
  header("Location: ../classTeacherLogin.php");
  exit();
}

/* ===============================
   2️⃣ CHECK ACCOUNT STATUS
================================ */
$statusStmt = $conn->prepare(
  "SELECT status FROM tblclassteacher WHERE Id = ?"
);
$statusStmt->bind_param("i", $_SESSION['userId']);
$statusStmt->execute();
$statusResult = $statusStmt->get_result()->fetch_assoc();
$statusStmt->close();

if (!$statusResult || $statusResult['status'] === 'blocked') {
  session_destroy();
  header("Location: ../classTeacherLogin.php");
  exit();
}

/* ===============================
   3️⃣ FETCH CLASS DETAILS
================================ */
$classStmt = $conn->prepare(
  "SELECT tblclass.className, tblclassarms.classArmName
   FROM tblclassteacher
   INNER JOIN tblclass ON tblclass.Id = tblclassteacher.classId
   INNER JOIN tblclassarms ON tblclassarms.Id = tblclassteacher.classArmId
   WHERE tblclassteacher.Id = ?"
);
$classStmt->bind_param("i", $_SESSION['userId']);
$classStmt->execute();
$classData = $classStmt->get_result()->fetch_assoc();
$classStmt->close();

/* SAFETY FALLBACK */
$className = $classData['className'] ?? 'N/A';
$classArm  = $classData['classArmName'] ?? 'N/A';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Class Teacher Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="img/logo/attnlg.jpg" rel="icon">
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
</head>

<body id="page-top">

<div id="wrapper">

  <?php include "Includes/sidebar.php"; ?>

  <div id="content-wrapper" class="d-flex flex-column">
    <div id="content">

      <?php include "Includes/topbar.php"; ?>

      <div class="container-fluid" id="container-wrapper">

        <div class="d-sm-flex align-items-center justify-content-between mb-4">
          <h1 class="h3 mb-0 text-gray-800">
            Class Teacher Dashboard
            (<?php echo htmlspecialchars("$className - $classArm"); ?>)
          </h1>
        </div>

        <div class="row mb-3">

          <!-- STUDENTS COUNT -->
          <?php
          $stmt = $conn->prepare(
            "SELECT id FROM tblstudents WHERE classId = ? AND classArmId = ?"
          );
          $stmt->bind_param("ii", $_SESSION['classId'], $_SESSION['classArmId']);
          $stmt->execute();
          $students = $stmt->get_result()->num_rows;
          $stmt->close();
          ?>
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col">
                    <div class="text-xs font-weight-bold text-uppercase mb-1">Students</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $students; ?></div>
                  </div>
                  <div class="col-auto">
                    <i class="fas fa-users fa-2x text-info"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- CLASSES -->
          <?php
          $stmt = $conn->prepare("SELECT id FROM tblclass");
          $stmt->execute();
          $classes = $stmt->get_result()->num_rows;
          $stmt->close();
          ?>
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col">
                    <div class="text-xs font-weight-bold text-uppercase mb-1">Classes</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $classes; ?></div>
                  </div>
                  <div class="col-auto">
                    <i class="fas fa-chalkboard fa-2x text-primary"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- COURSE CODES -->
          <?php
          $stmt = $conn->prepare("SELECT id FROM tblclassarms");
          $stmt->execute();
          $classArms = $stmt->get_result()->num_rows;
          $stmt->close();
          ?>
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col">
                    <div class="text-xs font-weight-bold text-uppercase mb-1">Course Codes</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $classArms; ?></div>
                  </div>
                  <div class="col-auto">
                    <i class="fas fa-code-branch fa-2x text-success"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- ATTENDANCE -->
          <?php
          $stmt = $conn->prepare(
            "SELECT id FROM tblattendance WHERE classId = ? AND classArmId = ?"
          );
          $stmt->bind_param("ii", $_SESSION['classId'], $_SESSION['classArmId']);
          $stmt->execute();
          $attendance = $stmt->get_result()->num_rows;
          $stmt->close();
          ?>
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col">
                    <div class="text-xs font-weight-bold text-uppercase mb-1">Attendance Records</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $attendance; ?></div>
                  </div>
                  <div class="col-auto">
                    <i class="fas fa-calendar fa-2x text-warning"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

    </div>

    <?php include 'Includes/footer.php'; ?>
  </div>
</div>

<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="js/ruang-admin.min.js"></script>
</body>
</html>
