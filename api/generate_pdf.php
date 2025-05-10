<?php
    setlocale(LC_TIME, 'ita', 'it_IT');

    // Include il file per il controllo della sessione
    include '../utils/check_session.php';
    require('../lib/TCPDF/tcpdf.php');

    $weatherIcons = [
        "Soleggiato" => "./../assets/img/sole.svg",
        "Nuvoloso" => "./../assets/img/nuvoloso.svg",
        "Pioggia" => "./../assets/img/pioggia.svg",
        "Temporale" => "./../assets/img/temporale.svg",
        "Neve" => "./../assets/img/neve.svg",
        "Parzialmente Nuvoloso" => "./../assets/img/parz_nuvoloso.svg",
        "Grandine" => "./../assets/img/grandine.svg",
        
    ];    

    // Ottieni le date dal GET
    $start_date = $_GET['start_date'] ?? date('Y-m-01');
    $end_date = $_GET['end_date'] ?? date('Y-m-t');
    $sintesi = $_GET['sintesi'] ?? ''; // Recupera la sintesi

    // Recupera le previsioni dal database dell'utente
    $query = "SELECT f.date, f.morning_desc, f.afternoon_desc, f.temp_max, f.temp_min, f.note, u.forecast_name FROM forecasts f JOIN users u ON f.user_id = u.id WHERE f.date BETWEEN ? AND ? AND f.user_id = ? ORDER BY f.date ASC";
    $stmt = $__con->prepare($query);
    $stmt->bind_param("ssi", $start_date, $end_date, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Creazione PDF con TCPDF
    $pdf = new TCPDF();
    $pdf->SetAutoPageBreak(TRUE, 10);
    $pdf->AddPage();
    $pdf->Ln(5);

    // Definizione larghezza colonne
    $columnWidths = [40, 50, 50, 40];
    $lMargin = ($pdf->GetPageWidth() - array_sum($columnWidths)) / 2;
    $pdf->SetMargins($lMargin, 15, 10);

    // Titolo del documento
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(255, 0, 0); // Rosso
    $pdf->Cell(0, 10, !empty($row["forecast_name"]) ? $row["forecast_name"] : 'LE PREVISIONI DI ' . strtoupper($user['full_name']), 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->SetTextColor(0, 0, 0); // Nero
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, "DATA: Dal " . date('d.m.Y', strtotime($start_date)) . " al " . date('d.m.Y', strtotime($end_date)), 0, 1, '');
    $pdf->Cell(0, 10, "EMESSO: TRENTINO, " . strftime("%^a %d.%m.%Y", strtotime("today")), 0, 1, '');
    $pdf->Ln(5);

    // Tabella intestazione
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetFillColor(255, 255, 255); // Sfondo bianco per l'intestazione
    $pdf->Cell($columnWidths[0], 10, 'Giorni', 1, 0, 'C', true);
    $pdf->Cell($columnWidths[1], 10, 'Mattina', 1, 0, 'C', true);
    $pdf->Cell($columnWidths[2], 10, 'Pomeriggio', 1, 0, 'C', true);
    $pdf->Cell($columnWidths[3], 10, 'Temp Min/Max (°C)', 1, 1, 'C', true);

    // Riempimento della tabella
    $pdf->SetFont('helvetica', '', 10);
    $result->data_seek(0);
    while ($row = $result->fetch_assoc()) {

        $pdf->Cell($columnWidths[0], 10, strftime("%^a %d.%m.%Y", strtotime($row['date'])), 1, 0, 'C');
        // Se c'è una nota, icona note.svg
        if (!empty($row['note'])) {
            $iconX = $pdf->GetX() - $columnWidths[0]; // Torna alla posizione della cella
            $iconY = $pdf->GetY() + 1.5;
            $pdf->ImageSVG('./../assets/img/note.svg', $iconX + 2, $iconY + 2, 3, 3);
        }
        // Cella Mattina con icona centrata
        $x = $pdf->GetX();  // Posizione attuale
        $y = $pdf->GetY();  // Posizione verticale
        $pdf->Cell($columnWidths[1], 10, '', 1, 0, 'C'); // Cella vuota per icona
        if (isset($weatherIcons[$row['morning_desc']])) {
            $pdf->ImageSVG($weatherIcons[$row['morning_desc']], $x + ($columnWidths[1] / 2) - 4, $y + 1, 8, 8);
        }

        // Cella Pomeriggio con icona centrata
        $x = $pdf->GetX();  // Posizione attuale
        $y = $pdf->GetY();  // Posizione verticale
        $pdf->Cell($columnWidths[2], 10, '', 1, 0, 'C'); // Cella vuota per icona
        if (isset($weatherIcons[$row['afternoon_desc']])) {
            $pdf->ImageSVG($weatherIcons[$row['afternoon_desc']], $x + ($columnWidths[2] / 2) - 4, $y + 1, 8, 8);
        }
        // Temperatura Minima (Blu)
        $pdf->SetTextColor(0, 0, 255); 
        $pdf->Cell($columnWidths[3] / 2, 10, $row['temp_min'] . " °C", 1, 0, 'C');

        // Temperatura Massima (Rosso)
        $pdf->SetTextColor(255, 0, 0);
        $pdf->Cell($columnWidths[3] / 2, 10, $row['temp_max'] . " °C", 1, 1, 'C');

        // Aggiungi riga per la nota se presente
        if (!empty($row['note'])) {
            $pdf->SetFont('helvetica', 'I', 9);
            $pdf->SetFillColor(255, 255, 230); // Giallo chiaro
            $pdf->SetTextColor(80, 80, 80);
        
            // Larghezza totale per la nota
            $totalWidth = array_sum($columnWidths);
            $iconSize = 3;
            $iconPadding = 3;
        
            // Coordinata Y corrente
            $y = $pdf->GetY();
            $x = $pdf->GetX();
        
            // Cella vuota per l'icona
            $pdf->Cell($iconSize + $iconPadding, 8, '', 0, 0, 'L', false);
        
            // Cella nota
            $pdf->Cell($totalWidth - ($iconSize + $iconPadding), 8, "Nota: " . $row['note'], 1, 1, 'L', true);
        
            // Inserisci l'icona
            $pdf->ImageSVG('./../assets/img/note.svg', $x + 2, $y + 2, $iconSize, $iconSize, '', '', '', 0, false);
        
            // Ripristina font
            $pdf->SetFont('helvetica', '', 10);
        }
        
        // Ripristina il colore predefinito (Nero)
        $pdf->SetTextColor(0, 0, 0);
    }

    $pdf->Ln(5);

    // Se l'utente ha inserito una sintesi, aggiungila
    if (!empty($sintesi)) {
        $pdf->SetFont('helvetica', '', 12);
        $pdf->WriteHTML('<u>SINTESI:</u>');
        if (strpos($sintesi, "🌡") !== false) {
            // Rimuovi l'emoji dal testo
            $sintesi = str_replace("🌡", "", $sintesi);

            $posX = $pdf->GetX();
        
            // Stampa il testo senza emoji
            $pdf->Cell(0, 10, $sintesi, 0, 'L'); // Testo con rientro automatico
        
            // Aggiungi l'icona SVG vicino al testo
            $pdf->ImageSVG('./../assets/img/thermometer.svg', $posX + strlen($sintesi)*2, $pdf->GetY()+2, 5, 5);
        } else {
            // Se non c'è l'emoji, stampa il testo normalmente
            $pdf->Cell(0, 10, $sintesi, 0, 'L'); // Testo con rientro automatico
        }
        $pdf->Ln(5);
    }

    // Output del PDF (download)
    $pdf->Output('Previsioni_Meteo.pdf', 'D');
?>
