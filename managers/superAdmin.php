<?php
    require '../services/authorize.php';
    authorize(['SUPER_ADMIN']);

    require "../lib/connection.php";

    $firstName = $_SESSION["first_name"];
?>