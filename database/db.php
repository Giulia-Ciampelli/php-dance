<?php

// attiva controllo dei tipi
declare(strict_types=1);
$envFile = dirname(__DIR__) . "/.env";

if (!is_readable($envFile)) {
    throw new RuntimeException("File .env non trovato.");
}

$envLines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// formatta le costanti per php
foreach ($envLines as $line) {
    $line = trim($line);

    // ignora righe vuote e commenti
    if ($line === "" || str_starts_with($line, "#")) {
        continue;
    }

    // dividi le costanti in due al primo = e ignora le costanti non formattate bene
    $parts = explode("=", $line, 2);

    if (count($parts) !== 2) {
        continue;
    }

    $key = trim($parts[0]);
    $value = trim($parts[1]);

    // rimuovi eventuali virgolette esterne
    if (

        // verifica se ci siano virgolette all'inizio e alla fine della stringa
        strlen($value) >= 2 &&
        (($value[0] === '"' && $value[strlen($value) - 1] === '"') ||
            ($value[0] === "'" && $value[strlen($value) - 1] === "'"))
    ) {
        $value = substr($value, 1, -1);
    }

    // crea le costanti previste
    if (in_array($key, [
        "DB_SERVERNAME",
        "DB_USERNAME",
        "DB_PASSWORD",
        "DB_NAME",
        "DB_PORT"
    ], true)) {
        define($key, $value);
    }
}

// config obbligatorie
foreach (
    [
        "DB_SERVERNAME",
        "DB_USERNAME",
        "DB_PASSWORD",
        "DB_NAME",
        "DB_PORT"
    ] as $key
) {
    if (!defined($key) || constant($key) === "") {
        throw new RuntimeException("Configurazione env mancante:" . $key);
    }
}

// apri connessione
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(DB_SERVERNAME, DB_USERNAME, DB_PASSWORD, DB_NAME, (int) DB_PORT);
    echo "Connessione riuscita al database: " . DB_NAME . ".";
} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit("Errore di connessione al database.");
}
