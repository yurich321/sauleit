<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin zone</title>
    <meta name="csrf-token" content="<?= csrf()::token() ?>">
    <link rel="stylesheet" href="<?=view()->asset('css/reset.css')?>">
    <link rel="stylesheet" href="<?=view()->asset('css/styles.css').'?v='.time();?>">
    <script defer src="<?=view()->asset('js/panel.js').'?v='.time();?>"></script
</head>
<body>
<div class="toast-container" id="toastContainer"></div>
<main>
    <?= $content ?? '' ?>
</main>

</body>
</html>
