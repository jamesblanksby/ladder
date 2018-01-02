<?php

/* //////////////////////////////////////////////////////////////////////////////// */
/* /////////////////////////////////////////////////////////////////////// USER /// */
/* //////////////////////////////////////////////////////////////////////////////// */

/* ----------------------------------------------------------------------- AUTH --- */
function user_auth() {
    if (user_check($mysqli) && strpos($_SERVER['SCRIPT_NAME'], '/index.php') !== false) {
        redirect('/league.php');
    }

    if (!user_check($mysqli) && strpos($_SERVER['SCRIPT_NAME'], '/index.php') === false) {
        if (strpos($_GET['f'], '_auth') === false 
        	&& strpos($_GET['f'], '_callback') === false) {
            redirect('/');
        }
    }
}

/* ---------------------------------------------------------------------- CHECK --- */
function user_check($mysqli) {
    if (isset($_SESSION['user']->id) == true) {
        return true;
    }
    return false;
}

/* ------------------------------------------------------------------------ GET --- */
function user_get($mysqli, $data, $column = 'id') {
    if (is_string($data)) $data = '"' . $data . '"';

    $user_sql = "
        SELECT user.*
        FROM user
        WHERE user.$column = $data";

    $user_result = $mysqli->query($user_sql);

    if ($user_result->num_rows > 0) {
        $user = $user_result->fetch_object();

        return $user;
    }

    return null;
}

/* --------------------------------------------------------------------- SELECT --- */
function user_select($mysqli) {
    $user_sql = "
        SELECT user.*
        FROM user";

    $user_result = $mysqli->query($user_sql);

    if ($user_result->num_rows > 0) {
        $user_array = [];
        while ($user = $user_result->fetch_object()) {
            $user = user_get($mysqli, $user->id);

            $user_array[] = $user;
        }

        return $user_array;
    }

    return null;
}

/* --------------------------------------------------------------------- INSERT --- */
function user_insert($mysqli, $data) {
    extract($data);

    $user_sql = "
        INSERT INTO user (
            name_first,
            name_last,
            email,
            image
        ) VALUES (?, ?, ?, ?)";

    $user_stmt = $mysqli->prepare($user_sql);
    $user_stmt->bind_param('ssss',
        $name_first,
        $name_last,
        $email,
        $image
    );
    $user_result = $user_stmt->execute();

    $user_id = $mysqli->insert_id;

    return $user_id;
}

/* --------------------------------------------------------------------- LOGOUT --- */
function user_logout($mysqli) {
    unset($_SESSION['user']);

    redirect('/');
}

/* ----------------------------------------------------------------------- NAME --- */
function user_name($user) {
    return $user->name_first . ' ' . $user->name_last;
}

/* -------------------------------------------------------------- LEAGUE SELECT --- */
function user_league_select($mysqli, $user_id) {
    $league_sql = "
        SELECT league.*
        FROM`user`
        JOIN league_user ON `user`.id = league_user.`user` 
        JOIN league ON league_user.league = league.id
        WHERE `user`.id = $user_id
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

/* ------------------------------------------------------------ LEAGUE POSITION --- */
function user_league_position($mysqli, $user_id, $league_id) {
    $table_array = league_table($mysqli, $league_id);

    foreach ($table_array as $position => $item) {
        if ($item->user->id == $user_id) {
            return ($position + 1);
        }
    }
}

/* --------------------------------------------------------------- LEAGUE NAKED --- */
function user_league_naked($mysqli, $user_id, $league_id) {
    $count = 0;

    $game_sql = "
        SELECT game.*
        FROM game
        WHERE (game.player_1 = $user_id
            OR game.player_2 = $user_id)
            AND game.league = $league_id";

    $game_result = $mysqli->query($game_sql);

    if ($game_result->num_rows > 0) {
        while ($game = $game_result->fetch_object()) {
            if ($game->player_1 == $user_id) {
                $player = 1;
            } else if ($game->player_2 == $user_id) {
                $player = 2;
            }

            if ($game->{'score_' . $player} == 0) {
                $count++;
            }
        }
    }

    return min(3, $count);
}

/* ---------------------------------------------------------------- GAME SELECT --- */
function user_game_select($mysqli, $user_id, $league_id = null, $time = null, $limit = null) {
    $game_sql = "
        SELECT game.*
        FROM game
        WHERE (game.player_1 = $user_id
            OR game.player_2 = $user_id)";

    if (isset($league_id)) {
        $game_sql .= "
            AND game.league = $league_id";
    }

    if (isset($time)) {
        if (is_array($time)) {
            $start = date('Y-m-d H:i:s', $time[0]);
            $finish = date('Y-m-d H:i:s', $time[1]);
        } else {
            $start = date('Y-m-d H:i:s', 0);
            $finish = date('Y-m-d H:i:s', $time);
        }

        $game_sql .= "
            AND game.time BETWEEN '$start' AND '$finish'";

        $game_sql .= "
            ORDER BY game.time DESC";
    }

    if (isset($limit)) {
        $game_sql .= "
            LIMIT $limit";
    }

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
function user_game_last($mysqli, $user_id, $league_id = null, $time = null, $limit = 1) {
    $game_sql = "
        SELECT game.*
        FROM game
        WHERE (game.player_1 = $user_id
            OR game.player_2 = $user_id)";

    if (isset($league_id)) {
        $game_sql .= "
            AND game.league = $league_id";
    }

    if (isset($time)) {
        if (is_array($time)) {
            $start = date('Y-m-d H:i:s', $time[0]);
            $finish = date('Y-m-d H:i:s', $time[1]);
        } else {
            $start = date('Y-m-d H:i:s', 0);
            $finish = date('Y-m-d H:i:s', $time);
        }

        $game_sql .= "
            AND game.time BETWEEN '$start' AND '$finish'";
    }

    $game_sql .= "
        ORDER BY game.time DESC
        LIMIT $limit";

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

/* -------------------------------------------------------------- GAME OPPONENT --- */
function user_game_opponent($mysqli, $user_id, $league_id = null) {
    $user_sql = "
        SELECT COUNT(game.id) AS count,
            CASE WHEN game.player_1 = $user_id THEN game.player_2 ELSE game.player_1 END AS user_id
        FROM game
        WHERE (game.player_1 = $user_id
            OR game.player_2 = $user_id)";

    if (isset($league_id)) {
        $user_sql .= "
            AND game.league = $league_id";
    }

    $user_sql .= "
        GROUP BY user_id
        ORDER BY count DESC
        LIMIT 1";

    $user_result = $mysqli->query($user_sql);

    if ($user_result->num_rows > 0) {
        $user = $user_result->fetch_object();

        $user = user_get($mysqli, $user->user_id);

        return $user;
    }

    return null;
}

/* ------------------------------------------------------------------ GAME GOAL --- */
function user_game_goal($mysqli, $user_id, $league_id = null) {
    $game_sql = "
        SELECT game.*
        FROM game
        WHERE (game.player_1 = $user_id
            OR game.player_2 = $user_id)";

    if (isset($league_id)) {
        $game_sql .= "
            AND game.league = $league_id";
    }

    $game_result = $mysqli->query($game_sql);

    if ($game_result->num_rows > 0) {
        $goal_count = 0;
        while ($game = $game_result->fetch_object()) {
            $goal_count += ($game->score_1 + $game->score_2);
        }

        return round(($goal_count / $game_result->num_rows), 1);
    }

    return 0;
}

/* ----------------------------------------------------------------- RATING GET --- */
function user_rating_get($mysqli, $league_id, $user_id, $time = null) {
    $rating_sql = "
        SELECT rating.*
        FROM rating
        WHERE rating.league = $league_id
            AND rating.user = $user_id";

    if (isset($time)) {
        $start = date('Y-m-d H:i:s', 0);
        $finish = date('Y-m-d H:i:s', $time);

        $rating_sql .= "
            AND rating.time BETWEEN '$start' AND '$finish'";
    }

    $rating_sql .= "
        ORDER BY rating.time DESC
        LIMIT 1";

    $rating_result = $mysqli->query($rating_sql);

    if ($rating_result->num_rows > 0) {
        $rating = $rating_result->fetch_object();

        return $rating->rating;
    }

    return null;
}
