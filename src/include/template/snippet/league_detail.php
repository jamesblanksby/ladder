<main>
<div class="container">

<h1><?php echo $league->name; ?></h1>

<div class="control">
    <a class="button primary" data-modal="game_insert">New Result</a>
    <?php if ($_SESSION['user']->id == $league->owner->id) : ?>
        <?php $league_path = BASE_URL . '/league.php?view=setting&league_uri=' . $league->uri . '&league_id=' . $league->id; ?>
        <a class="button secondary" data-modal="league_setting">Settings</a>
    <?php endif; ?>
</div>

<table class="league_table">
    <thead>
        <tr>
            <td width="30" class="stats"></td>
            <td width="70" class="position">Position</td>
            <td class="player">Player</td>
            <td width="57" class="played">P</td>
            <td width="39" class="won">W</td>
            <td width="39" class="lost">L</td>
            <td width="39" class="for">F</td>
            <td width="39" class="against">A</td>
            <td width="57" class="goal_difference">GD</td>
            <td width="65" class="rating">Rating</td>
            <td width="180" class="last_10">Last 10 Games</td>
            <td width="65" class="previous">Prev</td>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($league->table as $position => $item) : ?>
        <tr class="<?php echo $position % 2 === 0 ? 'even' : 'odd'; ?>">
            <td class="stats"><a class="toggle"></a></td>
            <td class="position"><?php echo ($position + 1); ?></td>
            <td class="player"><?php echo user_name($item->user); ?></td>
            <td class="played"><?php echo $item->played; ?></td>
            <td class="won"><?php echo $item->won; ?></td>
            <td class="lost"><?php echo $item->lost; ?></td>
            <td class="for"><?php echo $item->for; ?></td>
            <td class="against"><?php echo $item->against; ?></td>
            <td class="goal_difference"><?php echo $item->goal_difference; ?></td>
            <td class="rating"><?php echo $item->rating->current; ?></td>
            <td class="last_10">
                <?php $count = (10 - count($item->stat->last_10)); ?>
                <?php for ($i = 1; $i <= $count; $i++) : ?>
                    <div class="game"></div>
                <?php endfor; ?>
                <?php if (isset($item->stat->last_10)) : ?>
                    <?php $item->stat->last_10 = array_reverse($item->stat->last_10); ?>
                    <?php foreach ($item->stat->last_10 as $game) : ?>
                        <?php
                        if ($game->player_1->id == $item->user->id) {
                            $player = 1;
                        } else if ($game->player_2->id == $item->user->id) {
                            $player = 2;
                        }
                        $outcome = $game->winner == $player ? 'won' : 'lost';
                        ?>
                        <div class="game <?php echo $outcome; ?>">
                            <div class="detail">
                                <?php echo $game->score_1 . ' - ' . $game->score_2; ?>
                                <span class="opponent"><?php echo $player == 1 ? user_name($game->player_2) : user_name($game->player_1); ?></span>
                                <div class="date"><?php echo date('jS F Y', strtotime($game->time)); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </td>
            <td class="previous"><?php echo !empty($item->rating->previous) ? $item->rating->previous : '--'; ?></td>
        </tr>
        <tr class="stats">
            <td colspan="13">
                <div class="content">
                    <?php $graph_data = htmlentities(json_encode(array_values($item->graph))); ?>
                    <?php $graph_label = htmlentities(json_encode(array_keys($item->graph))); ?>
                    <div class="graph" data-graph="<?php echo $graph_data; ?>" data-graph-label="<?php echo $graph_label; ?>"></div>
                    <div class="last">
                        <?php
                        $result_last = $item->stat->result_last;
                        if (isset($result_last)) {
                            for ($i = 1; $i <= 2; $i++) {
                                if ($result_last->{'player_' . $i}->id == $item->user->id) {
                                    $player_index = $i;
                                } else {
                                    $opponent = $result_last->{'player_' . $i};
                                    $opponent_index = $i;
                                }
                            }
                        }
                        ?>
                        <?php if (isset($result_last)) : ?>
                        <span>
                            <?php echo $result_last->{'score_' . $player_index}; ?> - <?php echo $result_last->{'score_' . $opponent_index}; ?>
                            vs <?php echo user_name($opponent); ?>
                        </span>
                        <?php else : ?>
                            <span>--</span>
                        <?php endif; ?>
                    </div>
                    <div class="average">
                        <div class="goals"><span><?php echo $item->stat->goal_average > 0 ? $item->stat->goal_average : '--'; ?></span></div>
                        <div class="games"><span><?php echo isset($item->stat->most_played) ? user_name($item->stat->most_played) : '--'; ?></span></div>
                    </div>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</div>
</main>
