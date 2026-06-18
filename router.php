<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// --- AJOUT SUCKLESS : Redirection index.html vers / ---
// Si l'utilisateur tape explicitement "index.html", on le redirige proprement en 301
if ($uri === '/index.html') {
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: /");
    exit;
}

$file = __DIR__ . '/dist' . $uri;

// Si le chemin ciblé est un dossier existant, on cherche un index.html à l'intérieur
if (is_dir($file)) {
    $file = rtrim($file, '/') . '/index.html';
}

if (is_file($file)) {
    // Correction pour le type MIME du CSS/JS
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    if ($ext === 'css') {
        header('Content-Type: text/css');
    } elseif ($ext === 'js') {
        header('Content-Type: application/javascript');
    } else {
        $mime = mime_content_type($file);
        header('Content-Type: ' . $mime);
    }
    
    readfile($file);
    exit;
}

http_response_code(404);
include __DIR__ . '/dist/404.html';
