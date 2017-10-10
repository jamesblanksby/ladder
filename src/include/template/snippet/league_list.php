<main>
<div class="container">

<h1>Leagues</h1>

<div class="control">
    <a class="button primary" data-modal="league_insert">New League</a>
    <a class="button primary" data-modal="league_join">Join League</a>
</div>

<div class="league_list">

    <?php if (isset($league_array)) : ?>
        <?php foreach ($league_array as $league) : ?>
        <?php $league_path = BASE_URL . '/league.php?view=detail&league_uri=' . $league->uri . '&league_id=' . $league->id; ?>
        <a class="item" href="<?php echo $league_path; ?>">
            <?php $style = isset($league->poster) ? 'style="background-image: url( ' . $league->poster . ')"' : ''; ?>
            <div class="poster" <?php echo $style; ?>>
                <div class="name"><?php echo $league->name; ?></div>
            </div>
            <div class="detail">
                <div class="owner">
                    <div class="avatar" style="background-image: url(<?php echo $league->owner->image; ?>);"></div>
                    <div class="name"><?php echo user_name($league->owner); ?></div>
                    <div class="role">Owner</div>
                </div>
                <div class="player_list">
                    <?php foreach($league->user as $index => $user) : ?>
                        <?php if (count($league->user) > 9 && $index == 8) : ?>
                            <div class="truncate"><?php echo ((count($league->user) - 1) - $index); ?></div>
                            <?php break; ?>
                        <?php else : ?>
                            <div class="avatar" style="background-image: url(<?php echo $user->image; ?>);"></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <div class="leader">
                    <?php if (isset($league->table[0])) : ?>
                        <?php echo user_name($league->table[0]->user); ?>
                    <?php else : ?>
                        --
                    <?php endif; ?>
                </div>
                <div class="result_last">
                    <?php if (isset($league->result_last)) : ?>
                        <?php $result_last = $league->result_last; ?>
                        <div class="player_1" data-value="<?php echo $result_last->score_1; ?>"><?php echo user_name($result_last->player_1); ?></div>
                        <div class="player_2" data-value="<?php echo $result_last->score_2; ?>"><?php echo user_name($result_last->player_2); ?></div>
                    <?php else : ?>
                        --
                    <?php endif; ?>
                </div>
            </div>
            <div class="stats">
                <div class="item" data-label="Players">
                    <div class="value"><?php echo count($league->user); ?></div>
                </div>
                <div class="item" data-label="Position">
                    <?php 
                    $user_id = $_SESSION['user']->id;
                    $position = user_league_position($mysqli, $user_id, $league->id);
                    ?>
                    <div class="value" data-suffix="<?php echo ordinal_suffix($position); ?>"><?php echo $position; ?></div>
                </div>
                <div class="item" data-label="Games">
                    <div class="value"><?php echo count($league->game); ?></div>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    <?php else : ?>
        <div class="empty">No leagues...</div>
    <?php endif; ?>

</div>

</div>
</main>
