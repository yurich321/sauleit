<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="<?= csrf()::token() ?>">
    <link rel="stylesheet" href="<?=view()->asset('css/reset.css')?>">
    <link rel="stylesheet" href="<?=view()->asset('css/login.css').'?v='.time();?>">
    <title>Client zone</title>
    <!--<link rel="stylesheet" href="/css/auth.css">-->
</head>
<body class="auth-page">
<main class="auth-container">
    <?= $content ?? '' ?>
</main>
</body>
</html>
