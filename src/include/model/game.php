<?php

/* //////////////////////////////////////////////////////////////////////////////// */
/* /////////////////////////////////////////////////////////////////////// GAME /// */
/* //////////////////////////////////////////////////////////////////////////////// */

/* ------------------------------------------------------------------------ GET --- */
function game_get($mysqli, $data, $column = 'id') {
    if (is_string($data)) $data = '"' . $data . '"';

    $game_sql = "
        SELECT game.*
        FROM game
        WHERE game.$column = $data";

    $game_result = $mysqli->query($game_sql);

    if ($game_result->num_rows > 0) {
        $game = $game_result->fetch_object();

        $game->player_1 = user_get($mysqli, $game->player_1);
        $game->player_2 = user_get($mysqli, $game->player_2);

        return $game;
    }

    return null;
}

/* --------------------------------------------------------------------- INSERT --- */
function game_insert($mysqli) {
    $res = (object) [];

    extract(data('post'));

    if ($player_1 == $player_2) {
        $res->type = 'negative';
        $res->text = 'Player cannot play themselves';

        return response($res);
    }

    $league = league_get($mysqli, $league_id);

    if ($score_1 > $score_2) {
        $winner = 1;
    } else if ($score_1 < $score_2) {
        $winner = 2;
    }

    $game_sql = "
        INSERT INTO game (
            league,
            player_1,
            player_2,
            score_1,
            score_2,
            winner
        ) VALUES (?, ?, ?, ?, ?, ?)";

    $game_stmt = $mysqli->prepare($game_sql);
    $game_stmt->bind_param('iiiiii',
        $league_id,
        $player_1,
        $player_2,
        $score_1,
        $score_2,
        $winner
    );
    $game_result = $game_stmt->execute();

    $game_id = $mysqli->insert_id;

    // rating insert
    league_user_rating_insert($mysqli, $league_id, $game_id);

    //rating decay
    league_user_rating_decay($mysqli, $league_id, $player_1, $player_2);

    if ($game_result) {
        $res->type = 'positive';
        $res->text = 'Game successfully added';
        $res->redirect = BASE_URL . '/league.php?view=detail&league_uri=' . $league->uri . '&league_id=' . $league->id;
    } else {
        $res->type = 'negative';
        $res->text = 'Something went wrong';
    }

    return response($res);
}
