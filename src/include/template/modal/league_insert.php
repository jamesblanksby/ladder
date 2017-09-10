<div id="league_insert" class="modal_overlay">
    <div class="modal_box">
        <h2>Create New League</h2>

        <form action="<?php echo BASE_URL . '/src/include/data.php?f=league_insert'; ?>" method="post">
            
            <div class="modal_scroll">
                
                <div class="item required">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required autofocus>
                </div>

                <div class="item chosen">
                    <label for="user_array">Players</label>
                    <select id="user_array" name="user_array[]" data-placeholder="Select players..." multiple>
                    <?php if (isset($user_array)) : ?>
                        <?php foreach ($user_array as $user) : ?>
                            <?php if ($_SESSION['user']->id == $user->id) continue; ?>
                            <option value="<?php echo $user->id; ?>"><?php echo user_name($user); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </select>
                </div>

            </div>

            <div class="modal_footer">
                <button type="submit" class="button primary">Create League</button>
                <a class="button secondary" onclick="modal_close(false);">Cancel</a>
            </div>
        
        </form>
        
    </div>
</div>
