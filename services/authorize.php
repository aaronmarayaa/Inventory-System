<?php
require_once 'session.php';

function authorize($roles) {
    if (!in_array($_SESSION['role'], $roles)) {
        header("Location: ../exceptions/forbidden.php");
        exit();
    }
}
?>