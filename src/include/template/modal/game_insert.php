<div id="game_insert" class="modal_overlay">
    <div class="modal_box">
        <h2>New Match Result</h2>

        <form action="<?php echo BASE_URL . '/src/include/data.php?f=game_insert'; ?>" method="post">

            <div class="modal_scroll">

                <!-- data -->
                <div class="data">
                    <input type="hidden" id="league_id" name="league_id" value="<?php echo $league->id; ?>">
                </div>
                    
                <!-- player_1 -->
                <div class="item chosen required">
                    <label for="player_1">Player 1</label>
                    <select name="player_1" id="player_1" data-placeholder="Choose a player..." required>
                        <option value></option>
                        <?php foreach ($league->user as $user) : ?>
                            <option value="<?php echo $user->id; ?>"><?php echo user_name($user); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- score_1 -->
                <div class="item required">
                    <label for="score_1">Score</label>
                    <input type="text" id="score_1" name="score_1" min="0" max="10" required>
                </div>
                
                <!-- player_2 -->
                <div class="item chosen required">
                    <label for="player_2">Player 2</label>
                    <select name="player_2" id="player_2" data-placeholder="Choose a player..." required>
                        <option value></option>
                        <?php foreach ($league->user as $user) : ?>
                            <option value="<?php echo $user->id; ?>"><?php echo user_name($user); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- score_2 -->
                <div class="item required">
                    <label for="score_1">Score</label>
                    <input type="text" id="score_2" name="score_2" required>
                </div>

            </div>

            <div class="modal_footer">
                <button type="submit" class="button primary">Create Result</button>
                <a class="button secondary" onclick="modal_close(false);">Cancel</a>
            </div>

        </form>

    </div>
</div>
