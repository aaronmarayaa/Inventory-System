<?php
    require '../services/authorize.php';
    authorize(['ADMIN']);

    require "../lib/connection.php";

    $firstName = $_SESSION["first_name"];
?>