<div id="league_setting" class="modal_overlay">
    <div class="modal_box">
        <h2><?php echo $league->name; ?> Settings</h2>

        <div class="control">
            <?php $delete_path = BASE_URL . '/src/include/data.php?f=league_delete&league_id=' . $league->id; ?>
            <a href="<?php echo $delete_path; ?>" data-action="delete"></a>
        </div>

        <form action="<?php echo BASE_URL . '/src/include/data.php?f=league_update'; ?>" method="post">

            <div class="modal_scroll">

                <!-- data -->
                <div class="data">
                    <input type="hidden" id="league_id" name="league_id" value="<?php echo $league->id; ?>">
                </div>

                <!-- poster -->
                <div class="item">
                    <label for="poster">Poster</label>
                    <input type="file" id="poster" name="poster" accept="image/jpeg, image/png">
                    <input type="hidden" id="poster_tmp" name="poster_tmp">

                    <?php $style = isset($league->poster) ? 'style="background-image: url( ' . $league->poster . ')"' : ''; ?>
                    <div class="file_area" <?php echo $style; ?>></div>
                </div>
                
                <!-- name -->
                <div class="item required">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="<?php echo $league->name; ?>" required>
                </div>

                <!-- code -->
                <div class="item required">
                    <label for="code">Join Code</label>
                    <input type="text" id="code" name="code" value="<?php echo $league->code; ?>" onclick="this.select();" readonly required>

                    <a data-action="code_refresh"></a>
                </div>

                <!-- user -->
                <div class="player_list">
                    <label>League Players</label>
                    <?php foreach ($league->user as $user) : ?>
                        <?php $owner = $league->owner->id == $user->id ? 'owner' : ''; ?>
                        <div class="item <?php echo $owner; ?>">
                            <div class="avatar" style="background-image: url(<?php echo $user->image; ?>);"></div>
                            <div class="name"><?php echo user_name($user); ?></div>    
                            <div class="toggle">
                                <input type="checkbox" id="<?php echo 'user_' . $user->id; ?>" name="user[]" value="<?php echo $user->id; ?>">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>

            <div class="modal_footer">
                <button type="submit" class="button primary">Save</button>
                <a class="button secondary" onclick="modal_close(false);">Cancel</a>
            </div>

        </form>
        
    </div>
</div>
