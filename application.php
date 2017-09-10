<?php

/* //////////////////////////////////////////////////////////////////////////////// */
/* //////////////////////////////////////////////////////////////// APPLICATION /// */
/* //////////////////////////////////////////////////////////////////////////////// */

/* ------------------------------------------------------------------ REPORTING --- */
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

/* ---------------------------------------------------------------- ENVIRONMENT --- */
$host = $_SERVER['HTTP_HOST'];
$mode = $host == 'ladder.blanks.by' ? 'live' : 'local';

define('MODE', $mode);

/* --------------------------------------------------------------------- CONFIG --- */
require_once __DIR__ . '/config.php';

$env = $config[MODE];

/* ------------------------------------------------------------------------- DB --- */
$mysqli = new \mysqli($env['db_host'], $env['db_user'], $env['db_pass'], $env['db_name']);
$mysqli->set_charset('utf8');

/* ----------------------------------------------------------------------- TIME --- */
date_default_timezone_set('Europe/London');

/* ----------------------------------------------------------------------- BASE --- */
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . $env['dir_root']);
define('BASE_PATH', dirname(__FILE__));

/* ------------------------------------------------------------------ DIRECTORY --- */
define('DIR_TMP', '/tmp');
define('DIR_LIB', '/lib');

/* -------------------------------------------------------------------- SESSION --- */
session_cache_limiter(false);
session_save_path(BASE_PATH . DIR_TMP . '/session');
session_name('ladder');
session_start();

/* --------------------------------------------------------------- FACEBOOK API --- */
define('FACEBOOK_ID', $env['facebook']['id']);
define('FACEBOOK_SECRET', $env['facebook']['secret']);
