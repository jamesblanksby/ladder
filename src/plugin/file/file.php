<?php

$action = $_GET['action'];
$function = $action;

$function();

/* -------------------------------------------------------------- POST MAX SIZE --- */
function post_max_size() {
    $post_max_size = ini_get('upload_max_filesize');

    echo $post_max_size;
}

/* --------------------------------------------------------------------- UPLOAD --- */
function upload() {
    $name_tmp = $_GET['name'];
    $uri = $_GET['uri'];
    $file_path = $uri;

    if (!file_exists($file_path)) {
        echo json_encode(['code'  => 2]);

        return;
    }

    if (!is_writable($file_path)) {
        echo json_encode(['code'  => 3]);

        return;
    }

    $input = fopen('php://input', 'rb');
    $file = fopen($file_path . '/' . $name_tmp, 'ab');

    while (!feof($input)) {
        fwrite($file, fread($input, 102400));
    }

    fclose($input);
    fclose($file);

    if (file_exists($file_path . '/' . $name_tmp)) {
        echo json_encode(true);

        return;
    }

    echo json_encode(['code' => null]);

    return;
}

/* --------------------------------------------------------------------- DELETE --- */
function delete() {
    $name_tmp = $_GET['name'];
    $uri = $_GET['uri'];
    $file_path = $uri;

    $file_path .= '/' . $name_tmp;

    @unlink($file_path);
}
