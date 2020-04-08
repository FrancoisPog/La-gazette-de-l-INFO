<?php

session_start();
ob_start();
require_once("bibli_gazette.php");

// if the user came in this page without be logged -> go index.php
cp_is_logged('../index.php');

$page = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER']:'../index.php' ;
cp_session_exit($page);

