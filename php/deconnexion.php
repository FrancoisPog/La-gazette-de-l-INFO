<?php

session_start();
ob_start();
require_once("bibli_generale.php");

// if the user came in this page without be logged -> go index.php
fp_is_logged('../index.php');

$page = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER']:'../index.php' ;
fp_session_exit($page);

