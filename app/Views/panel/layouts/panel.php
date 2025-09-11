<div class="header sticky">
    <div class="container">
        <nav>
            <div class="left-menu">
                <a href="#">CLIENTS</a>
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
    <div class="title">Clients</div>

    <div class="card">
        <div class="head">
            <div>ID</div>
            <div>Name</div>
            <div>Gender</div>
            <div>Birthday</div>
            <div>Phone</div>
            <div>Email</div>
            <div>Actions</div>
        </div>
        <?php if(!empty($clientsList)): ?>
            <?php foreach ($clientsList as $client):
                $gender = $client['gender'] ?? null;
                $genderLbl = $gender === 'male' ? 'Male' : ($gender === 'female' ? 'Female' : '—');
                $phone = trim(($client['phone'] ?? ''));
                $email = trim(($client['contact_email'] ?? $client['email'] ?? ''));
            ?>
        <div class="row">

            <div><?= (int)$client['id']; ?></div>
            <div class="name"><?=$client['name'] ?? ''; ?></div>
            <div><?= $genderLbl ?></div>
            <div><?= $client['birth_date'] ?? '—'; ?></div>
            <div><?= $phone; ?></div>
            <div><?= $email; ?></div>
            <div>
                <a class="btn" href="/panel/clients/<?= (int)$client['id'] ?>">Details</a>
            </div>

        </div>
            <?php endforeach;?>
        <?php endif;?>
    </div>
</div>




