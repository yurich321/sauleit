<?php

$emails = [];
$phones = [];
foreach (($client['contacts'] ?? []) as $c) {
    if (($c['type'] ?? '') === 'email') { $emails[] = $c['value']; }
    if (($c['type'] ?? '') === 'phone') { $phones[] = $c['value']; }
}


$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

$fmtMoney = function(int $minor, string $cur): string {
    return number_format($minor / 100, 2, '.', ' ') . ' ' . currencySymbol($cur);
};
?>


<div class="header sticky">
    <div class="container">
        <nav>
            <div class="left-menu">
                <a href="/panel/clients">CLIENTS</a>
            </div>
            <div class="right-menu">
                <form action="/panel/logout" method="post">
                    <?= csrf()::field();?>
                    <button type="submit">Logout</button>
                </form>
            </div>
        </nav>
    </div>
</div>

<div class="wrap">
    <div class="title">Client info</div>
    <div class="card card-client">
        <div class="kv">
            <div>ID</div>
            <div>#<?= (int)$client['id'] ?></div>
            <div>Name</div>
            <div><?= $e($client['name'] ?? '—') ?></div>
            <div>Email(s)</div>
            <div>
                <?php if ($emails): ?>
                    <?php foreach ($emails as $i => $mail): ?>
                        <?php if ($i) echo ', '; ?>
                        <?= $e($mail) ?>
                    <?php endforeach; ?>
                <?php else: ?>—<?php endif; ?>
            </div>
            <div>Phone(s)</div>
            <div>
                <?php if ($phones): ?>
                    <?php foreach ($phones as $i => $ph): ?>
                        <?php if ($i) echo ', '; ?>
                        <a href="tel:<?= $e(preg_replace('/\s+/', '', $ph)) ?>"><?= $e($ph) ?></a>
                    <?php endforeach; ?>
                <?php else: ?>—<?php endif; ?>
            </div>
            <div>Gender</div>
            <div><?= $e(ucfirst($client['gender'] ?? '—')) ?></div>
            <div>Birth date</div>
            <div><?= $e($client['birth_date'] ?? '—') ?></div>
            <div>Status</div>
            <div><?= !empty($client['is_active']) ? 'Active' : 'Disabled' ?></div>
            <div>Created</div>
            <div><?= $e($client['created_at'] ?? '—') ?></div>
        </div>
    </div>
    <div class="card card-wallets">
        <div class="head-slim">Wallets</div>
        <div class="wallets-grid">
            <?php foreach (($client['wallets'] ?? []) as $cur => $minor): ?>
                <div class="wallet">
                    <div class="cur"><?= $e($cur) ?></div>
                    <div class="amt" data-wallet="<?= $e($cur) ?>"><?= $fmtMoney((int)$minor, (string)$cur) ?></div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($client['wallets'])): ?>
                <div class="wallet empty">No wallets</div>
            <?php endif; ?>
        </div>
    </div>
</div>


<div class="wrap">
    <div class="title">Bet history</div>

    <div class="card">
        <div class="head head-bets">
            <div>ID</div>
            <div>Date</div>
            <div>Stake</div>
            <div>Outcome</div>
            <div>Odds</div>
            <div>Status</div>
            <div>Actions</div>
        </div>

        <?php foreach ($client_stakes as $b):
            $isSettled = !empty($b['result']);
            $badgeText = $isSettled ? strtoupper($b['result']) : 'PENDING';
            $odds = $b['odds'] ?? null;
            ?>
            <div class="row row-bets">
                <div><?= (int)$b['id'] ?></div>
                <div><?= htmlspecialchars($b['created_at']) ?></div>
                <div><?= number_format($b['amount_minor']/100, 2, '.', ' ') ?> <?= currencySymbol($b['currency']) ?></div>
                <div><?= htmlspecialchars((string)($b['outcome'] ?? '')) ?> (<?= htmlspecialchars((string)($b['outcome_key'] ?? '')) ?>)</div>
                <div><?= $odds !== null ? rtrim(rtrim(number_format($odds, 2, '.', ''), '0'), '.') : '—' ?></div>
                <div><?= $badgeText ?></div>

                <div>
                    <?php if (!$isSettled): ?>
                        <form class="settle-form" method="post" action="/panel/bets/settle">
                            <?= csrf()->field(); ?>
                            <input type="hidden" name="stake_id" value="<?= (int)$b['id'] ?>">

                            <label><input type="radio" name="result" value="win" required> Win</label>
                            <label><input type="radio" name="result" value="lose" required> Lose</label>

                            <button type="button" class="js-settle">Set</button>
                        </form>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>



