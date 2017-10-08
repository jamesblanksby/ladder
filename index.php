<?php require_once __DIR__ . '/src/include/data.php'; ?>

<?php require_once __DIR__ . '/src/include/top.php'; ?>

<div class="center">

    <a class="logo"></a>

    <div class="login">

        <div class="social">
            <a class="button google" href="<?php echo BASE_URL . '/src/include/data.php?f=google_auth'; ?>">Login with Google</a>
            <!-- <a class="button twitter">Login with Twitter</a> -->
            <a class="button facebook" href="<?php echo BASE_URL . '/src/include/data.php?f=facebook_auth'; ?>">Login with Facebook</a>
        </div>

    </div>

</div>

<?php require_once __DIR__ . '/src/include/bottom.php'; ?>
