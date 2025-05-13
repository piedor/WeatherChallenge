<?php
    require __DIR__ . '/settings.php';
?>

<!-- Pulsante Menu (hamburger) -->
<button class="btn btn-outline-primary btn-menu" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuSidebar">
    ☰ Menu
</button>

<!-- Sidebar con il menu -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="menuSidebar" aria-labelledby="menuSidebarLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="menuSidebarLabel">Menù</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="list-group">
            <li class="list-group-item"><a href="<?= $baseUrl ?>/index.php" class="text-decoration-none">🏠 Dashboard</a></li>
            <?php if (isset($role) && ($role === 'professor' || $role === 'admin')): ?>
                <li class="list-group-item">
                    <a href="<?= $baseUrl ?>/students_forecasts.php" class="text-decoration-none">📋 I miei studenti</a>
                </li>
            <?php endif; ?>
            <?php if (isset($role) && ($role === 'admin')): ?>
                <li class="list-group-item">
                    <a href="<?= $baseUrl ?>/admin/manage_plagiarism.php" class="text-decoration-none">👨‍💻 Gestione plagi</a>
                </li>
            <?php endif; ?>
            <li class="list-group-item"><a href="<?= $baseUrl ?>/insert_forecast.php" class="text-decoration-none">📌 Inserisci una previsione</a></li>
            <li class="list-group-item"><a href="<?= $baseUrl ?>/history_forecast.php" class="text-decoration-none">📊 Storico previsioni</a></li>
            <li class="list-group-item"><a href="<?= $baseUrl ?>/export_pdf.php" class="text-decoration-none">📄 Esporta Previsioni in PDF</a></li>
            <li class="list-group-item"><a href="<?= $baseUrl ?>/forecast_resources.php" class="text-decoration-none">🛠️ Strumenti Meteo</a></li>
            <li class="list-group-item"><a href="<?= $baseUrl ?>/data_video.php" class="text-decoration-none">📺 Previsioni pubbliche</a></li>
            <li class="list-group-item"><a href="<?= $baseUrl ?>/global_ranking.php" class="text-decoration-none">🏆 Classifica</a></li>
            <!--<li class="list-group-item"><a href="" class="text-decoration-none">🌦️ Dati Meteo Attuali</a></li>
            <li class="list-group-item"><a href="" class="text-decoration-none">📈 Statistiche</a></li>-->
        </ul>
    </div>
</div>