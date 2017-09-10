<header>
    <div class="container">
        <a class="logo" href="<?php echo BASE_URL; ?>"></a>
        <nav>
            <a class="active" href="./league.php">Leagues</a>
        </nav>
        <a class="user">
            <div class="avatar" style="background-image: url(<?php echo $_SESSION['user']->image; ?>);"></div>
            <div class="name"><?php echo user_name($_SESSION['user']); ?></div>
        </a>
        <div class="user">
            <!--
            <a class="disabled">Profile</a>
            <span></span>
            -->
            <a href="<?php echo BASE_URL . '/src/include/data.php?f=user_logout'; ?>">Logout</a>
        </div>
    </div>
</header>
