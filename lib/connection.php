<?php
   mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

   try {
      $conn = new mysqli("localhost", "root", "123456", "chocodb");

   } catch (mysqli_sql_exception $e) {
      error_log("Database connection failed: " . $e->getMessage());
      header("Location: ../exceptions/internalServerError.php");
      exit();
   }
?>