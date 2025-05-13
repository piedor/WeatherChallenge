<div class="header mb-3">
    <h1 id="weather-title" style="font-family: 'Raleway', sans-serif; 
                            text-align: center; 
                            font-weight: bold; 
                            color: #008CBA;">
    </h1>
    <?php
        require __DIR__ . '/settings.php';
        require ('menu.php'); 
    ?>

    <script>
        const text = "WeatherChallenge ⛅";
        let i = 0;
        function typeEffect() {
            if (i < text.length) {
                document.getElementById("weather-title").innerHTML += text.charAt(i);
                i++;
                setTimeout(typeEffect, 50);
            }
        }
        window.onload = typeEffect;
    </script>
    <!-- Profilo utente con tendina -->
    <div class="dropdown">
        <button class="btn btn-light dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="<?= htmlspecialchars($_SESSION['user']['picture']) ?>" alt="Foto profilo" class="rounded-circle" width="40" height="40">
            <span class="ms-2"><?= htmlspecialchars($user['full_name']) ?></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <!-- Info Utente -->
            <li class="user-info">
                <img src="<?= htmlspecialchars($_SESSION['user']['picture']) ?>" alt="Foto profilo">
                <div>
                    <strong><?= htmlspecialchars($user['full_name']) ?></strong>
                    <div class="user-email"><?= htmlspecialchars($user['email']) ?></div>
                </div>
            </li>
            <li><hr class="dropdown-divider"></li>

            <!-- Accuratezza -->
            <li>
                <strong>Livello di Accuratezza</strong>
                <div class="accuracy-bar">
                    <div class="accuracy-fill" style="width: <?= htmlspecialchars($user['total_accuracy']) ?>%;"></div>
                </div>
                <div class="text-center mt-1"><?= htmlspecialchars($user['total_accuracy']) ?>%</div>
            </li>

            <?php if ($user['consistency_bonus'] > 0): ?>
                <span class="badge bg-success">
                    +<?= $user['consistency_bonus'] ?>% costanza
                </span>
            <?php endif; ?>
            <?php if ($user['positive_series_bonus'] > 0): ?>
                <span class="badge bg-success">
                    +<?= $user['positive_series_bonus'] ?>% serie accurate
                </span>
            <?php endif; ?>
            <?php if ($user['positive_series_bonus'] > 0): ?>
                <span class="badge bg-success">
                    +<?= $user['early_forecasts_bonus'] ?>% anticipo
                </span>
            <?php endif; ?>
            <br>

            <!-- Accuratezza -->
            <li>
                <strong>Punteggio</strong>
                <div class="accuracy-bar">
                    <div class="accuracy-fill" style="width: <?= htmlspecialchars($user['score']) ?>%;"></div>
                </div>
                <div class="text-center mt-1"><?= htmlspecialchars($user['score']) ?>/100</div>
            </li>

            <li><hr class="dropdown-divider"></li>

            <!-- Impostazioni -->
            <li>
                <a class="dropdown-item text-center" href="<?= $baseUrl ?>/user_settings.php">⚙️ Impostazioni</a>
            </li>

            <li><hr class="dropdown-divider"></li>

            <!-- Logout -->
            <li><a class="dropdown-item text-danger text-center fw-bold" href="<?= $baseUrl ?>/logout.php">Esci</a></li>
        </ul>
    </div>
</div>