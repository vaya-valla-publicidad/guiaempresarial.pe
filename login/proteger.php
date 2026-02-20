<?php
session_start();

if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit;
}