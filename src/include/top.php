<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ladder</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <link rel="stylesheet" href="./src/css/style.css">
</head>
<?php $_page = basename($_SERVER["SCRIPT_FILENAME"], '.php'); ?>
<body class="<?php echo $_page; ?>">

<script>
var BASE_URL = '<?php echo BASE_URL; ?>';
var BASE_PATH = '<?php echo BASE_PATH; ?>';

var DIR_TMP = '<?php echo DIR_TMP; ?>';
var DIR_LIB = '<?php echo DIR_LIB; ?>';
</script>

<?php if (isset($_SESSION['response'])) : ?>
    <?php $res = $_SESSION['response']; ?>
    <div class="message <?php echo $res->type; ?>">
        <div class="text"><?php echo $res->text; ?><a></a></div>
    </div>
    <?php unset($_SESSION['response']); ?>
<?php endif; ?>
