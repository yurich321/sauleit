<?php
$client = authClient()->user();
?>

<div class="header sticky">
    <div class="container">
        <nav>

            <button class="toggle-nav" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <div class="left-menu">
                <a href="#">CASINO</a>
                <a href="#">SPORTS</a>
            </div>
            <div class="right-menu">
                <form id="walletForm" class="wallet">
                    <label for="walletCur">Wallet:</label>
                    <select id="walletCur" name="currency">
                        <?php foreach (['EUR','USD','RUB'] as $c): ?>
                            <option value="<?= $c ?>" <?= $c === wallet()->currentCurrency() ? 'selected' : '' ?>>
                                <?= $c ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="wallet">
                    Balance:
                        <span id="walletBalance"><?= wallet()->formattedBalance($client->id) ?></span>
                    </span>
                    <?= csrf()::field();?>
                </form>


                <!--<form action="/logout" method="post">
                    <?php /*= csrf()::field();*/?>
                    <button type="submit">Logout</button>
                </form>-->
                <div class="user-dropdown">
                    <button type="button" class="user-btn">
                        Hi, <?= htmlspecialchars($client->name ?? $client->email) ?> ▼
                    </button>
                    <div class="dropdown-menu">
                        <div>Settings</div>
                        <form action="/logout" method="post">
                            <?= csrf()::field(); ?>
                            <button type="submit">Logout</button>
                        </form>
                    </div>
                </div>

            </div>

        </nav>
        <div class="nav-backdrop" hidden></div>
    </div>
</div>

<div class="main">
    <div class="container">
        <div class="events-container">
            <div class="events">
                <?php if(!empty(events())):?>
                    <?php foreach(events() as $eventId => $event):?>
                        <div class="event-card">
                            <div class="event-time has-divider">
                                <div class="event-time-text">
                                    <div class="date"><?=$event['date'] ?? ''?></div>
                                    <div class="time"><?=$event['time'] ?? ''?></div>
                                </div>
                            </div>
                            <div class="event-title">
                                <?=$event['title'] ?? ''?>
                            </div>
                            <div class="outcomes">
                                <?php if(!empty($event['outcomes'])):?>
                                    <?php foreach($event['outcomes'] as $key => $outcome):?>
                                        <div class="outcome"
                                             data-event-id="<?= htmlspecialchars($eventId ?? '', ENT_QUOTES) ?>"
                                             data-event-title="<?= htmlspecialchars($event['title'] ?? '', ENT_QUOTES) ?>"
                                             data-date="<?= htmlspecialchars($event['date'] ?? '', ENT_QUOTES) ?>"
                                             data-time="<?= htmlspecialchars($event['time'] ?? '', ENT_QUOTES) ?>"
                                             data-outcome-label="<?= htmlspecialchars($outcome['label'] ?? '', ENT_QUOTES) ?>"
                                             data-outcome-key="<?= htmlspecialchars($key ?? '', ENT_QUOTES) ?>"
                                             data-odds="<?= htmlspecialchars($outcome['odds'] ?? '', ENT_QUOTES) ?>">
                                            <?=$outcome['odds'] ?? ''?>
                                        </div>
                                    <?php endforeach;?>
                                <?php else:?>
                                    <div>No outcomes</div>
                                <?php endif;?>
                            </div>

                        </div>

                    <?php endforeach;?>
                <?php else:?>
                    <div> There no evens!</div>

                <?php endif;?>
            </div>

            <div class="betslip">
                <div class="betslip-header">
                    Your betslip
                </div>
                <div class="betslip-body">
                    <div class="empty-betslip">
                        Your betslip is empty
                    </div>
                </div>
                <div class="betslip-footer">
                    <button disabled>BET NOW</button>
                </div>

            </div>

        </div>


    </div>
</div>
