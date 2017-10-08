<?php

/* //////////////////////////////////////////////////////////////////////////////// */
/* ///////////////////////////////////////////////////////////////////// GOOGLE /// */
/* //////////////////////////////////////////////////////////////////////////////// */

/* --------------------------------------------------------------------- CLIENT --- */
function google_client() {
    $client = new Google_Client([
        'client_id' => GOOGLE_ID,
        'client_secret' => GOOGLE_SECRET
    ]);
    $client->setRedirectUri(BASE_URL . '/src/include/data.php?f=google_callback');
    $client->setScopes('email');

    return $client;
}

/* ----------------------------------------------------------------------- AUTH --- */
function google_auth() {
    $client = google_client();
    $_SESSION['redirect'] = $_GET['redirect'];

    $login_url = $client->createAuthUrl();

    header('Location:' . $login_url);
    exit;
}

/* ------------------------------------------------------------------- CALLBACK --- */
function google_callback() {
    global $mysqli;

    $client = google_client();

    $code = $_GET['code'];

    $client->authenticate($code);
    $data = $client->getAccessToken();
    $access_token = $data['access_token'];

    $client->setAccessToken($access_token);

    $service = new Google_Service_Oauth2($client);

    $data = (array) $service->userinfo->get();

    $user = user_get($mysqli, $data['email'], 'email');

    if (!isset($user)) {
        $user_id = user_insert($mysqli, [
            'name_first' => $data['givenName'],
            'name_last' => $data['familyName'],
            'email' => $data['email'],
            'image' => $data['picture']
        ]);

        $user = user_get($mysqli, $user_id);
    }

    $_SESSION['user'] = $user;

    redirect('/league.php');
}
