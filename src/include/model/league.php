<?php

/* //////////////////////////////////////////////////////////////////////////////// */
/* ///////////////////////////////////////////////////////////////////// LEAGUE /// */
/* //////////////////////////////////////////////////////////////////////////////// */

/* ------------------------------------------------------------------------ GET --- */
function league_get($mysqli, $data, $column = 'id') {
    if (is_string($data)) $data = '"' . $data . '"';

    $league_sql = "
        SELECT league.*
        FROM league
        WHERE league.$column = $data";

    $league_result = $mysqli->query($league_sql);

    if ($league_result->num_rows > 0) {
        $league = $league_result->fetch_object();

        $league->owner = user_get($mysqli, $league->owner);
        $league->user = league_user_select($mysqli, $league->id);
        $league->table = league_table($mysqli, $league->id);
        $league->game = league_game_select($mysqli, $league->id);

        $image_path = DIR_LIB . '/league/' . $league->id . '.' . 'jpg';
        if (file_exists(BASE_PATH . $image_path) && !is_dir(BASE_PATH . $image_path)) {
            $league->poster = BASE_URL . $image_path;
        }

        $league->result_last = league_game_last($mysqli, $league->id);

        return $league;
    }

    return null;
}

/* --------------------------------------------------------------------- SELECT --- */
function league_select($mysqli) {
    $league_sql = "
        SELECT league.*
        FROM league
	ORDER BY league.time DESC";

    $league_result = $mysqli->query($league_sql);

    if ($league_result->num_rows > 0) {
        $league_array = [];
        while ($league = $league_result->fetch_object()) {
            $league = league_get($mysqli, $league->id);

            $league_array[] = $league;
        }

        return $league_array;
    }

    return null;
}

/* --------------------------------------------------------------------- INSERT --- */
function league_insert($mysqli) {
    $res = (object) [];
    
    extract(data('post'));

    $uri = slugify($name);
    $owner = $_SESSION['user']->id;
    $code = league_code();

    $league_sql = "
        INSERT INTO league (
            uri,
            name,
            owner,
            code
        ) VALUES (?, ?, ?, ?)";

    $league_stmt = $mysqli->prepare($league_sql);
    $league_stmt->bind_param('ssis',
        $uri,
        $name,
        $owner,
        $code
    );
    $league_result = $league_stmt->execute();

    $league_id = $mysqli->insert_id;

    // user
    league_user_insert($mysqli, $league_id);

    if ($league_result) {
        $res->type = 'positive';
        $res->text = 'League successfully created';
        $res->redirect = BASE_URL . '/league.php?view=detail&league_uri=' . $uri . '&league_id=' . $league_id;
    } else {
        $res->type = 'negative';
        $res->text = 'Something went wrong';
    }

    return response($res);
}

/* --------------------------------------------------------------------- UPDATE --- */
function league_update($mysqli) {
    $res = (object) [];
    
    extract(data('post'));

    $league = league_get($mysqli, $league_id);

    if (!isset($league)) die;
    if ($league->owner->id != $_SESSION['user']->id) {
        $res->type = 'negative';
        $res->text = 'You do not have permission to modify this';

        return response($res);
    }

    $uri = slugify($name);

    $league_sql = "
        UPDATE league
        SET name = ?,
            code = ?
        WHERE league.id = ?";

    $league_stmt = $mysqli->prepare($league_sql);
    $league_stmt->bind_param('ssi',
        $name,
        $code,
        $league_id
    );
    $league_result = $league_stmt->execute();

    // poster
    if (!is_empty($poster_tmp)) {
        $source = str_replace(BASE_URL, BASE_PATH, $poster_tmp);
        $target = BASE_PATH . DIR_LIB . '/league/' . $league_id . '.' . 'jpg';

        $image = imagecreatefromstring(file_get_contents($source));

        imagejpeg($image, $target);

        imagedestroy($image);
        @unlink($source);
    }

    // user
    if (isset($user)) {
        foreach ($user as $user_id) {
            // user
            $user_sql = "
                DELETE FROM league_user
                WHERE league_user.league = ?
                    AND league_user.user = ?";

            $user_stmt = $mysqli->prepare($user_sql);
            $user_stmt->bind_param('ii',
                $league_id,
                $user_id
            );
            $user_result = $user_stmt->execute();

            // game
            $game_sql = "
                DELETE FROM game
                WHERE game.player_1 = ?
                    OR game.player_2 = ?";

            $game_stmt = $mysqli->prepare($game_sql);
            $game_stmt->bind_param('ii',
                $user_id,
                $user_id
            );
            $game_result = $game_stmt->execute();
        }
    }

    if ($league_result) {
        $res->type = 'positive';
        $res->text = 'League successfully updated';
        $res->redirect = BASE_URL . '/league.php?view=detail&league_uri=' . $league->uri . '&league_id=' . $league->id;
    } else {
        $res->type = 'negative';
        $res->text = 'Something went wrong';
    }

    return response($res);
}

/* ----------------------------------------------------------------------- JOIN --- */
function league_join($mysqli) {
    $res = (object) [];
    
    extract(data('post'));

    $user_id = $_SESSION['user']->id;

    $league = league_get($mysqli, $code, 'code');

    if (!isset($league)) {
        $res->type = 'negative';
        $res->text = 'League join code invalid';

        return response($res);
    }

    $user = league_user_get($mysqli, $league->id, $user_id);

    if (isset($user)) {
        $res->type = 'negative';
        $res->text = 'You already belong to ' . $league->name;

        return response($res);
    }

    $user_sql = "
        INSERT INTO league_user (
            league,
            user
        ) VALUES (?, ?)";

    $user_stmt = $mysqli->prepare($user_sql);
    $user_stmt->bind_param('ii',
        $league->id,
        $user_id
    );
    $user_result = $user_stmt->execute();

    if ($user_result) {
        $res->type = 'positive';
        $res->text = 'Successfully joined league';
        $res->redirect = BASE_URL . '/league.php?view=detail&league_uri=' . $league->uri . '&league_id=' . $league->id;
    } else {
        $res->type = 'negative';
        $res->text = 'Something went wrong';
    }

    return response($res);
}

/* --------------------------------------------------------------------- DELETE --- */
function league_delete($mysqli) {
    $res = (object) [];

    extract(data('get'));

    $league = league_get($mysqli, $league_id);

    if (!isset($league)) die;
    if ($league->owner->id != $_SESSION['user']->id) {
        $res->type = 'negative';
        $res->text = 'You do not have permission to delete this';
        $res->redirect = '/league.php?view=detail&league_uri=' . $league->uri . '&league_id=' . $league->id;

        response($res);

        redirect($res->redirect);

        return;
    }

    // user
    $user_sql = "
        DELETE FROM league_user
        WHERE league_user.league = ?";

    $user_stmt = $mysqli->prepare($user_sql);
    $user_stmt->bind_param('i',
        $league_id
    );
    $user_result = $user_stmt->execute();

    // game
    $game_sql = "
        DELETE FROM game
        WHERE game.league = ?";

    $game_stmt = $mysqli->prepare($game_sql);
    $game_stmt->bind_param('i',
        $league_id
    );
    $game_result = $game_stmt->execute();

    // poster
    @unlink(BASE_URL . DIR_LIB . '/league/' . $league->id . '.' . 'jpg');

    // league
    $league_sql = "
        DELETE FROM league
        WHERE league.id = ?";

    $league_stmt = $mysqli->prepare($league_sql);
    $league_stmt->bind_param('i',
        $league_id
    );
    $league_result = $league_stmt->execute();

    if ($league_result) {
        $res->type = 'positive';
        $res->text = 'League successfully deleted';
        $res->redirect = '/league.php';
    } else {
        $res->type = 'negative';
        $res->text = 'Something went wrong';
        $res->redirect = '/league.php?view=detail&league_uri=' . $league->uri . '&league_id=' . $league->id;
    }

    response($res);

    redirect($res->redirect);
}

/* ----------------------------------------------------------------------- CODE --- */
function league_code($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $character_length = strlen($characters);

    $string = '';
    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[rand(0, $character_length - 1)];
    }

    return $string;
}

/* ------------------------------------------------------------------ CODE AJAX --- */
function league_code_ajax() {
    $code = league_code();

    echo $code;
}

/* --------------------------------------------------------------------- TABLE  --- */
function league_table($mysqli, $league_id) {
    $user_array = league_user_select($mysqli, $league_id);

    $table_array = [];
    if (isset($user_array)) {
        foreach ($user_array as $user) {

            $time = time();
            for ($i = 0; $i < 10; $i++) {
                if ($i === 0) {
                    $rating = $user->rating;
                } else {
                    $time = strtotime('-' . $i . ' ' . 'day');
                    $rating = user_rating_get($mysqli, $league_id, $user->id, $time);
                    if (is_null($rating)) $rating = RATING_DEFAULT;
                }

                $user_graph_array[date('d/m', $time)] = $rating;
            }

            $item = (object) [
                'empty' => true,
                'user' => $user,
                'played' => 0,
                'won' => 0,
                'lost' => 0,
                'for' => 0,
                'against' => 0,
                'goal_difference' => 0,
                'rating' => $user->rating,
                'graph' => array_reverse($user_graph_array)
            ];

            $game_array = user_game_select($mysqli, $user->id, $league_id, [strtotime(date('Y-m-01')), time()]);

            if (isset($game_array)) {
                $item->empty = false;

                foreach ($game_array as $game) {
                    $item->played++;

                    if ($game->player_1->id == $user->id) {
                        $player = 1;
                    } else if ($game->player_2->id == $user->id) {
                        $player = 2;
                    }

                    if ($game->winner == $player) {
                        $item->won++;
                    } else {
                        $item->lost++;
                    }

                    $item->for += $game->{'score_' . $player};

                    if ($player == 1) {
                        $item->against += $game->score_2;
                    } else {
                        $item->against += $game->score_1;
                    }

                }
            }

            $item->goal_difference = ($item->for - $item->against);

            $item->stat = (object) [
                'result_last' => user_game_last($mysqli, $user->id, $league_id)[0],
                'most_played' => user_game_opponent($mysqli, $user->id, $league_id),
                'goal_average' => user_game_goal($mysqli, $user->id, $league_id),
                'last_10' => user_game_last($mysqli, $user->id, $league_id, [strtotime(date('Y-m-01')), time()], 10)
            ];

            $table_array[] = $item;
        }
    }


    usort($table_array, function ($a, $b) {
        $c = $b->rating - $a->rating;
        if ($c != 0) return $c;

        $c = $b->goal_difference - $a->goal_difference;
        if ($c != 0) return $c;

        $c = $b->for - $a->for;
        if ($c != 0) return $c;
        
        return strcmp($a->user->name_last, $b->user->name_last);
    });

    return $table_array;
}

/* ------------------------------------------------------------------- USER GET --- */
function league_user_get($mysqli, $league_id, $user_id) {
    $user_sql = "
        SELECT user.*,
            (
                SELECT rating.rating 
                FROM rating 
                WHERE rating.league = $league_id
                    AND rating.user = user.id 
                ORDER BY rating.time 
                DESC LIMIT 1
            ) AS rating
        FROM user
        JOIN league_user ON user.id = league_user.user 
        JOIN league ON league_user.league = league.id
        WHERE league.id = $league_id
            AND user.id = $user_id
        GROUP BY user.id";

    $user_result = $mysqli->query($user_sql);

    if ($user_result->num_rows > 0) {
        $user = $user_result->fetch_object();
        $rating = !empty($user->rating) ? $user->rating : RATING_DEFAULT;

        $user = user_get($mysqli, $user->id);
        $user->rating = $rating;

        return $user;
    }

    return null;
}

/* ---------------------------------------------------------------- USER SELECT --- */
function league_user_select($mysqli, $league_id) {
    $user_sql = "
        SELECT user.*,
            (
                SELECT rating.rating 
                FROM rating 
                WHERE rating.league = $league_id
                    AND rating.user = user.id 
                ORDER BY rating.time 
                DESC LIMIT 1
            ) AS rating
        FROM user
        JOIN league_user ON user.id = league_user.user 
        JOIN league ON league_user.league = league.id
        WHERE league.id = $league_id
        GROUP BY user.id";

    $user_result = $mysqli->query($user_sql);

    if ($user_result->num_rows > 0) {
        $user_array = [];
        while ($user = $user_result->fetch_object()) {
            $rating = !empty($user->rating) ? $user->rating : RATING_DEFAULT;

            $user = user_get($mysqli, $user->id);
            $user->rating = $rating;

            $user_array[] = $user;
        }

        return $user_array;
    }

    return null;
}

/* ---------------------------------------------------------------- USER INSERT --- */
function league_user_insert($mysqli, $id) {
    extract(data('post'));

    $league_id = $id;

    $user_array[] = $_SESSION['user']->id;

    foreach ($user_array as $user_id) {
        $user_sql = "
            INSERT INTO league_user (
                league,
                user
            ) VALUES (?, ?)";

        $user_stmt = $mysqli->prepare($user_sql);
        $user_stmt->bind_param('ii',
            $league_id,
            $user_id
        );
        $user_result = $user_stmt->execute();
    }
}

/* --------------------------------------------------------- USER RATING INSERT --- */
function league_user_rating_insert($mysqli, $league_id, $game_id) {
    extract(data('post'));

    $user_array = league_user_select($mysqli, $league_id);

    $rating_gift = max(0, (count($user_array) - 2));
    if ($rating_gift > 0) $rating_gift = (($rating_gift * RATING_DECAY) / 2);

    $player = league_user_get($mysqli, $league_id, $player_1);
    $rating_1 = (int) $player->rating;

    $player = league_user_get($mysqli, $league_id, $player_2);
    $rating_2 = (int) $player->rating;

    $result = $score_1 > $score_2 ? 1 : 0;

    $elo = new Elo;
    $delta = $elo->new_rating($rating_1, $rating_2, $result);

    $rating_1 = (($rating_1 + $delta) + $rating_gift);
    $rating_2 = (($rating_2 - $delta) + $rating_gift);

    $user_sql = "
        INSERT INTO rating (
            league,
            user,
            rating
        ) VALUES (?, ?, ?)";

    $user_stmt = $mysqli->prepare($user_sql);
    $user_stmt->bind_param('iii',
        $league_id,
        $player_1,
        $rating_1
    );
    $user_result = $user_stmt->execute();

    $user_sql = "
        INSERT INTO rating (
            league,
            user,
            rating
        ) VALUES (?, ?, ?)";

    $user_stmt = $mysqli->prepare($user_sql);
    $user_stmt->bind_param('iii',
        $league_id,
        $player_2,
        $rating_2
    );
    $user_result = $user_stmt->execute();

    $game_sql = "
        UPDATE game 
        SET rating_1 = ?,
            rating_2 = ?
        WHERE game.id = ?";

    $game_stmt = $mysqli->prepare($game_sql);
    $game_stmt->bind_param('iii',
        $rating_1,
        $rating_2,
        $game_id
    );
    $game_result = $game_stmt->execute();
}

/* ---------------------------------------------------------- USER RATING DECAY --- */
function league_user_rating_decay($mysqli, $league_id, $player_1, $player_2) {
    $user_array = league_user_select($mysqli, $league_id);

    if (isset($user_array)) {
        foreach ($user_array as $user) {
            if (in_array($user->id, [$player_1, $player_2])) continue;

            $user_id = $user->id;
            $rating = ($user->rating - RATING_DECAY);

            $user_sql = "
                INSERT INTO rating (
                    league,
                    user,
                    rating
                ) VALUES (?, ?, ?)";

            $user_stmt = $mysqli->prepare($user_sql);
            $user_stmt->bind_param('iii',
                $league_id,
                $user_id,
                $rating
            );
            $user_result = $user_stmt->execute();
        }
    }
}

/* ---------------------------------------------------------------- GAME SELECT --- */
function league_game_select($mysqli, $league_id, $time = null) {
    $game_sql = "
        SELECT game.*
        FROM game
        WHERE game.league = $league_id";

    if (isset($time)) {
        $start = date('Y-m-d H:i:s', 0);
        $finish = date('Y-m-d H:i:s', strtotime('+1 week', $time));

        $game_sql .= "
            AND game.time BETWEEN '$start' AND '$finish'";
    }

    $game_sql .= "
        ORDER BY game.time ASC";

    $game_result = $mysqli->query($game_sql);

    if ($game_result->num_rows > 0) {
        $game_array = [];
        while ($game = $game_result->fetch_object()) {
            $game = game_get($mysqli, $game->id);

            $game_array[] = $game;
        }

        return $game_array;
    }

    return null;
}

/* ------------------------------------------------------------------ GAME LAST --- */
function league_game_last($mysqli, $league_id) {
    $game_sql = "
        SELECT game.*
        FROM game
        WHERE game.league = $league_id
        ORDER BY game.time DESC
        LIMIT 1";

    $game_result = $mysqli->query($game_sql);

    if ($game_result->num_rows > 0) {
        $game = $game_result->fetch_object();
        
        $game = game_get($mysqli, $game->id);

        return $game;
    }

    return null;
}
