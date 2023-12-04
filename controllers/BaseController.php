<?php

// Path: controllers/BaseController.php
class BaseController
{
    protected $twig;

    public function __construct()
    {
        $loader = new \Twig\Loader\FilesystemLoader('../views');
        $this->twig = new \Twig\Environment($loader);
    }

    protected function render($template, $data = [])
    {
        // Ensure that session data is always passed to Twig templates
        $mergedData = array_merge(['session' => $_SESSION], $data);

        echo $this->twig->render($template, $mergedData);
    }

    protected function redirect($url)
    {
        header('Location: ' . $url);
        exit;
    }

    // Add a method to check if a user is logged in
    protected function userIsLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    // Add a method to check if a user is an admin
    protected function userIsAdmin()
    {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
    }
}