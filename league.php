<?php require_once __DIR__ . '/src/include/data.php'; ?>

<?php
$_view = $_GET['view'];
?>

<?php require_once __DIR__ . '/src/include/top.php'; ?>
<?php require_once __DIR__ . '/src/include/header.php'; ?>

<?php 
/* --------------------------------------------------------------------- DETAIL --- */
if ($_view == 'detail') : 
?>

<?php
$league_id = $_GET['league_id'];
$league = league_get($mysqli, $league_id);
if (!isset($league)) page_error(404);

// $user_position = league_user_position($mysqli, $league->id);
?>

<?php require_once __DIR__ . '/src/include/template/modal/game_insert.php'; ?>
<?php if ($_SESSION['user']->id == $league->owner->id) : ?>
    <?php require_once __DIR__ . '/src/include/template/modal/league_setting.php'; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/src/include/template/snippet/league_detail.php'; ?>

<?php 
/* ----------------------------------------------------------------------- LIST --- */
else :
?>

<?php
$user_id = $_SESSION['user']->id;

$league_array = user_league_select($mysqli, $user_id);
$user_array = user_select($mysqli);
?>

<?php require_once __DIR__ . '/src/include/template/modal/league_insert.php'; ?>
<?php require_once __DIR__ . '/src/include/template/modal/league_join.php'; ?>

<?php require_once __DIR__ . '/src/include/template/snippet/league_list.php'; ?>

</div>
</main>

<?php 
/* ------------------------------------------------------------------------ END --- */
endif;
?>

<?php require_once __DIR__ . '/src/include/footer.php'; ?>
<?php require_once __DIR__ . '/src/include/bottom.php'; ?>
