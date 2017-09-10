<div id="league_join" class="modal_overlay">
    <div class="modal_box">
        <h2>Join League</h2>

        <form action="<?php echo BASE_URL . '/src/include/data.php?f=league_join'; ?>" method="post">

            <div class="modal_scroll">

                <div class="item required">
                    <label for="code">Code</label>
                    <input type="text" id="code" name="code" required autofocus>
                </div>

            </div>

            <div class="modal_footer">
                <button type="submit" class="button primary">Join</button>
                <a class="button secondary" onclick="modal_close(false);">Cancel</a>
            </div>

        </form>

    </div>
</div>
