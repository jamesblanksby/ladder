<?php

/* //////////////////////////////////////////////////////////////////////////////// */
/* //////////////////////////////////////////////////////////////// APPLICATION /// */
/* //////////////////////////////////////////////////////////////////////////////// */

/* ---------------------------------------------------------------------- MODEL --- */
foreach (glob(__DIR__ . '/model/*.php') as $file) {
    require_once $file;
}

/* -------------------------------------------------------------------- REQUIRE --- */
require_once __DIR__ . '/../../application.php';
require_once __DIR__ . '/../vendor/autoload.php';


/* //////////////////////////////////////////////////////////////////////////////// */
/* //////////////////////////////////////////////////////////////////// DEFAULT /// */
/* //////////////////////////////////////////////////////////////////////////////// */

/* ----------------------------------------------------------------------- AUTH --- */
user_auth();

/* ---------------------------------------------------------------------- ROUTE --- */
if (isset($_GET['f'])) {
    $function = $_GET['f'];

    $function($mysqli);
}


/* //////////////////////////////////////////////////////////////////////////////// */
/* /////////////////////////////////////////////////////////////////////// PAGE /// */
/* //////////////////////////////////////////////////////////////////////////////// */

/* ---------------------------------------------------------------------- ERROR --- */
function page_error($code) {
    switch ($code) {
        case 404 :
            @header('HTTP/1.0 404 Not Found');
            
            include(BASE_PATH . '/src/include/template/snippet/404.php');
            exit;
        break;
    }
}


/* //////////////////////////////////////////////////////////////////////////////// */
/* ///////////////////////////////////////////////////////////////////// HELPER /// */
/* //////////////////////////////////////////////////////////////////////////////// */

/* ----------------------------------------------------------------------- DATA --- */
function data($verb = 'get') {
    $data = [];

    switch($verb) {
        case 'get' :
            foreach ($_GET as $key => $value) {
                $data[$key] = $value;
            }
        break;
        case 'post' :
            foreach ($_POST as $key => $value) {
                $data[$key] = $value;
            }
        break;
        case 'files' :
            foreach ($_FILES as $key => $value) {
                $data[$key] = $value;
            }
        break;
    }

    return $data;
}

/* --------------------------------------------------------------------- PRETTY --- */
function pretty($data) {
    if (is_array($data) || is_object($data)) {
        echo '<pre>' . print_r($data, 1) . '</pre>';
    } else {
        var_dump($data);
    }
}

/* -------------------------------------------------------------------------- P --- */
function p($data) {
    pretty($data);
}

/* ------------------------------------------------------------------- REDIRECT --- */
function redirect($uri) {
    @header('Location: ' . BASE_URL . $uri);
}

/* ---------------------------------------------------------------------- EMPTY --- */
function is_empty($value) {
    return $value == '' ? true : false;
}

/* --------------------------------------------------------------- EMPTY 2 NULL --- */
function empty2null($value) {
    return is_empty($value) ? null : $value;
}


/* -------------------------------------------------------------------- MESSAGE --- */
function response($data) {
    $_SESSION['response'] = $data;
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode($data);
    } else {
        return $data;
    }
}

/* -------------------------------------------------------------------- SLUGIFY --- */
function slugify($string) {
    $table = [
        'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
        'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
        'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
        'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
        'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
        'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
        'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r',
    ];

    $string = strtr($string, $table);
    $string = preg_replace('~[^\\pL\d]+~u', '-', $string);
    $string = trim($string, '-');
    $string = iconv('utf-8', 'us-ascii//TRANSLIT', $string);
    $string = strtolower($string);
    $string = preg_replace('~[^-\w]+~', '', $string);

    return $string;
}

/* ------------------------------------------------------------- ORDINAL SUFFIX --- */
function ordinal_suffix($v) {
    $v = $v % 100;
    if ($v < 11 || $v > 13) {
         switch ($v % 10) {
            case 1: return 'st';
            case 2: return 'nd';
            case 3: return 'rd';
        }
    }
    return 'th';
}