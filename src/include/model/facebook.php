<?php

/* //////////////////////////////////////////////////////////////////////////////// */
/* /////////////////////////////////////////////////////////////////// FACEBOOK /// */
/* //////////////////////////////////////////////////////////////////////////////// */

/* --------------------------------------------------------------------- CLIENT --- */
function facebook_client() {
    $client = new Facebook\Facebook([
        'app_id' => FACEBOOK_ID,
        'app_secret' => FACEBOOK_SECRET,
        'default_graph_version' => 'v2.8',
    ]);

    return $client;
}

/* ----------------------------------------------------------------------- AUTH --- */
function facebook_auth() {
    $client = facebook_client();

    $helper = $client->getRedirectLoginHelper();

    $login_url = $helper->getLoginUrl(BASE_URL . '/src/include/data.php?f=facebook_callback', ['email']);

    header('Location:' . $login_url);
    exit;
}

/* ------------------------------------------------------------------- CALLBACK --- */
function facebook_callback() {
    global $mysqli;

    $res = (object) [];

    $client = facebook_client();

    $helper = $client->getRedirectLoginHelper();

    $access_token = $helper->getAccessToken();

    if (isset($access_token)) {
        $response = $client->get('/me?fields=id,first_name,last_name,email', $access_token);

        $data = $response->getGraphUser();

        if (empty($data['email'])) {
            $res->type = 'negative';
            $res->text = 'Primary Facebook contact must be an email address';
            $res->redirect = '/';

            redirect($res->redirect);
        }

        $user = user_get($mysqli, $data['email'], 'email');

        if (!isset($user)) {
            $data['image'] = 'https://graph.facebook.com/' . $data['id'] . '/picture';

            $user_id = user_insert($mysqli, [
                'name_first' => $data['first_name'],
                'name_last' => $data['last_name'],
                'email' => $data['email'],
                'image' => $data['image']
            ]);

            $user = user_get($mysqli, $user_id);
        }

        $_SESSION['user'] = $user;

       	redirect('/league.php');
    }
}
