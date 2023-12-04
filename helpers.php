<?php

// Path: helpers.php
if (!function_exists('renderTemplate')) {
    require_once __DIR__ . '/vendor/autoload.php';

    function renderTemplate($templateName, $data = [])
    {
        $loader = new \Twig\Loader\FilesystemLoader("../views");
        $twig = new \Twig\Environment($loader);

        // Ensure that session data is always passed to Twig templates
        $mergedData = array_merge(['session' => $_SESSION], $data);
        
        echo $twig->render($templateName, $mergedData);
    }

    function error($errorNumber, $errorMessage)
    {
        http_response_code($errorNumber);
        renderTemplate('error.twig', ['errorMessage' => $errorMessage]);
        exit;
    }
}
