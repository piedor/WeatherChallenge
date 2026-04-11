<?php
    require __DIR__ . '/settings.php';
?>

<!-- ══════════════════════════════════════
     PULSANTE MENU DESKTOP (visibile solo su desktop)
══════════════════════════════════════ -->
<button class="btn btn-outline-primary btn-menu d-none d-md-block"
        type="button" data-bs-toggle="offcanvas" data-bs-target="#menuSidebarDesktop">
    ☰ <span class="menu-label">Menu</span>
</button>

<!-- ══════════════════════════════════════
     SIDEBAR DESKTOP — da sinistra
══════════════════════════════════════ -->
<div class="offcanvas offcanvas-start d-none d-md-flex"
     tabindex="-1" id="menuSidebarDesktop" aria-labelledby="menuSidebarDesktopLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="menuSidebarDesktopLabel">Menù</h5>
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
            <li class="list-group-item"><a href="<?= $baseUrl ?>/weather_sources_forecasts.php" class="text-decoration-none">📈 Statistiche Siti Meteo Ufficiali</a></li>
        </ul>
    </div>
</div>

<!-- ══════════════════════════════════════
     SIDEBAR MOBILE — bottom sheet
══════════════════════════════════════ -->
<div class="offcanvas offcanvas-bottom d-md-none"
     tabindex="-1" id="menuSidebarMobile" aria-labelledby="menuSidebarMobileLabel"
     style="height: auto; border-radius: 20px 20px 0 0;">
    <div class="offcanvas-header pb-0 justify-content-center">
        <!-- Handle bar — niente X, si chiude swipando o toccando fuori -->
        <div style="width:40px; height:4px; background:#dee2e6; border-radius:2px;"></div>
    </div>
    <div class="offcanvas-body d-flex flex-column">

        <!-- Voci menu — crescono verso il basso, padding grande per il pollice -->
        <ul class="list-unstyled mb-0">
            <?php if (isset($role) && ($role === 'admin')): ?>
                <li>
                    <a href="<?= $baseUrl ?>/admin/manage_plagiarism.php" class="mobile-menu-item">
                        <span class="mmi-icon">👨‍💻</span>
                        <span class="mmi-label">Gestione plagi</span>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (isset($role) && ($role === 'professor' || $role === 'admin')): ?>
                <li>
                    <a href="<?= $baseUrl ?>/students_forecasts.php" class="mobile-menu-item">
                        <span class="mmi-icon">📋</span>
                        <span class="mmi-label">I miei studenti</span>
                    </a>
                </li>
            <?php endif; ?>
            <li>
                <a href="<?= $baseUrl ?>/weather_sources_forecasts.php" class="mobile-menu-item">
                    <span class="mmi-icon">📈</span>
                    <span class="mmi-label">Statistiche Siti Meteo Ufficiali</span>
                </a>
            </li>
            <li>
                <a href="<?= $baseUrl ?>/export_pdf.php" class="mobile-menu-item">
                    <span class="mmi-icon">📄</span>
                    <span class="mmi-label">Esporta Previsioni in PDF</span>
                </a>
            </li>
            <li>
                <a href="<?= $baseUrl ?>/forecast_resources.php" class="mobile-menu-item">
                    <span class="mmi-icon">🛠️</span>
                    <span class="mmi-label">Strumenti Meteo</span>
                </a>
            </li>
            <li>
                <a href="<?= $baseUrl ?>/global_ranking.php" class="mobile-menu-item">
                    <span class="mmi-icon">🏆</span>
                    <span class="mmi-label">Classifica</span>
                </a>
            </li>
            <li>
                <a href="<?= $baseUrl ?>/data_video.php" class="mobile-menu-item">
                    <span class="mmi-icon">📺</span>
                    <span class="mmi-label">Previsioni pubbliche</span>
                </a>
            </li>
            <li>
                <a href="<?= $baseUrl ?>/history_forecast.php" class="mobile-menu-item">
                    <span class="mmi-icon">📊</span>
                    <span class="mmi-label">Storico previsioni</span>
                </a>
            </li>
            <li>
                <a href="<?= $baseUrl ?>/insert_forecast.php" class="mobile-menu-item">
                    <span class="mmi-icon">📌</span>
                    <span class="mmi-label">Inserisci una previsione</span>
                </a>
            </li>
            <li>
                <a href="<?= $baseUrl ?>/index.php" class="mobile-menu-item mobile-menu-item--primary">
                    <span class="mmi-icon">🏠</span>
                    <span class="mmi-label">Dashboard</span>
                </a>
            </li>
        </ul>
    </div>
</div>

<style>
    /* Voci menu mobile */
    .mobile-menu-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 16px;
        border-radius: 12px;
        text-decoration: none;
        color: #1e293b;
        font-size: 1rem;
        font-weight: 600;
        transition: background .15s;
        border-bottom: 1px solid #f1f5f9;
    }
    .mobile-menu-item:last-child { border-bottom: none; }
    .mobile-menu-item:active { background: #f0f4f8; }

    /* Voci principali (Dashboard e Inserisci) leggermente evidenziate */
    .mobile-menu-item--primary {
        background: #f0f9ff;
        color: #0369a1;
    }
    .mobile-menu-item--primary:active { background: #e0f2fe; }

    .mmi-icon  { font-size: 1.3rem; flex-shrink: 0; }
    .mmi-label { flex: 1; }

    /* Voce attiva — pagina corrente */
    .mobile-menu-item--active {
        background: #dbeafe;
        color: #1d4ed8;
        font-weight: 800;
    }
    /* Inverti la direzione dello scroll: parte dal basso */
    #menuSidebarMobile .offcanvas-body {
        transform: scaleY(-1);
        overflow-y: auto;
        padding-bottom: 10px;        /* rimuovi il padding-bottom originale */
        padding-top: 70px;        /* mettilo qui invece */
    }

    #menuSidebarMobile .offcanvas-body > ul {
        transform: scaleY(-1);
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const sidebar = document.getElementById("menuSidebarMobile");
        if (!sidebar) return;

        const urlParams  = new URLSearchParams(window.location.search);
        const fromParam  = urlParams.get('from');

        /* Evidenzia voce attiva */
        const pageMap = {
            'details_forecast.php': fromParam === 'students'
                                                    ? 'students_forecasts.php'
                                                    : 'history_forecast.php',
            "modify_forecast.php":                  "history_forecast.php",
            "details_weather_source_forecasts.php": "weather_sources_forecasts.php",
            "weather_source_forecast_details.php":  "weather_sources_forecasts.php",
            "student_forecast_details.php":         "students_forecasts.php",
        };

        const currentPath = window.location.pathname;
        const currentFile = currentPath.split("/").pop();
        const targetFile  = pageMap[currentFile] ?? currentFile;

        sidebar.querySelectorAll(".mobile-menu-item").forEach(function(link) {
            const href = link.getAttribute("href");
            if (href && href.split("/").pop() === targetFile) {
                link.classList.add("mobile-menu-item--active");
                link.classList.remove("mobile-menu-item--primary");
            }
        });
    });
</script>