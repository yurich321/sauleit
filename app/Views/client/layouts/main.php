<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Client zone</title>
    <meta name="csrf-token" content="<?= csrf()::token() ?>">
    <link rel="stylesheet" href="<?=view()->asset('css/reset.css')?>">
    <link rel="stylesheet" href="<?=view()->asset('css/dashboard.css').'?v='.time();?>">
   <!-- <script defer src="/js/common.js"></script>>-->
    <script defer src="<?=view()->asset('js/dashboard.js').'?v='.time();?>"></script
</head>
<body>
<div class="toast-container" id="toastContainer"></div>
    <main>
        <?= $content ?? '' ?>
    </main>
    <div class="container">
        <footer>
            <small>© <?= date('Y') ?>Test App</small>
        </footer>
    </div>

</body>
</html>
