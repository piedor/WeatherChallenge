<?php
    ini_set('session.cookie_lifetime', 60 * 60 * 24 * 30); // 30 giorni
    ini_set('session.gc_maxlifetime',  60 * 60 * 24 * 30); // 30 giorni
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_domain', '');
    session_start();

    header('Cache-Control: no-store, no-cache, must-revalidate, private');
    header('Pragma: no-cache');
?>