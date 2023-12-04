<?php

// Path: public/index.php
session_start(); // Start the session
require_once '../vendor/autoload.php';
require_once '../helpers.php';
require_once '../database.php';


if (!isset($_GET['params'])) {
    require_once '../controllers/UserController.php';
    $controller = new UserController();
    return $controller->index();
}

$url = explode("/", $_GET['params']);
$controllerName = ucfirst($url[0]) . "Controller";

// Check if the controller class file exists
if (!file_exists("../controllers/{$controllerName}.php")) {
    http_response_code(404);
    renderTemplate('error.twig', ['errorMessage' => "Controller '{$controllerName}' not found."]);
    exit;
}

require_once "../controllers/{$controllerName}.php";

if (isset($url[0]) && !isset($url[1])) {
    if (!class_exists($controllerName)) {
        http_response_code(404);
        renderTemplate('error.twig', ['errorMessage' => "Controller '{$controllerName}' not found."]);
        exit;
    }

    $controller = new $controllerName();
    return $controller->index();
}

if (isset($url[0]) && isset($url[1])) {
    if (!class_exists($controllerName)) {
        http_response_code(404);
        renderTemplate('error.twig', ['errorMessage' => "Controller '{$controllerName}' not found."]);
        exit;
    }

    $controller = new $controllerName();
    $method = $url[1];

    if (!method_exists($controller, $method)) {
        http_response_code(404);
        renderTemplate('error.twig', ['errorMessage' => "Method '{$method}' not found in '{$controllerName}'."]);
        exit;
    }

    // Handle other methods in your controllers
    if ($controllerName === 'UserController' && $method === 'login') {
        // If it's the login method, handle both GET and POST
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->login();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->loginPost();
        }
    } elseif ($controllerName === 'UserController' && $method === 'register') {
        // If it's the register method, handle both GET and POST
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->register();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->registerPost();
        }
    } elseif ($controllerName === 'UserController' && $method === 'showProfile') {
        // Assuming the user ID is the third part of the URL
        if (isset($url[2])) {
            $userId = $url[2];
            $controller->$method($userId);
        } else {
            // Handle the error for missing user ID
            echo "Error: User ID is missing.";
            exit;
        }
    } elseif ($controllerName === 'UserController' && $method === 'editProfile') {
        // If it's the editProfile method, handle both GET and POST
        if (isset($url[2])) {
            $userId = $url[2];
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $controller->$method($userId);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->updateProfile($userId);
            }
        } else {
            // Handle the error for missing user ID
            echo "Error: User ID is missing.";
            exit;
        }
    } elseif ($controllerName === 'FeedController' && $method === 'storePost') {
        // If it's the storePost method of FeedController, handle it
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->storePost();
        } else {
            http_response_code(405);
            renderTemplate('error.twig', ['errorMessage' => "Method not allowed."]);
            exit;
        }
    } elseif ($controllerName === 'FeedController' && $method === 'createPost') {
        // If it's the createPost method of FeedController, handle it
        $controller->createPost();
    } elseif ($controllerName === 'FeedController' && $method === 'addLike') {
        // If it's the addLike method of FeedController, handle it
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->addLike();
        } else {
            http_response_code(405);
            renderTemplate('error.twig', ['errorMessage' => "Method not allowed."]);
        }
    } elseif ($controllerName === 'FeedController' && $method === 'searchUser') {
        // If it's the searchUser method of FeedController, handle it
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->searchUser();
        } else {
            http_response_code(405);
            renderTemplate('error.twig', ['errorMessage' => "Method not allowed."]);
        }
    } elseif ($controllerName === 'FeedController' && $method === 'userFeed') {
        // If it's the userFeed method of FeedController, handle it
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Extract the user ID from the URL
            $user_id = end(explode('/', $_SERVER['REQUEST_URI']));
            $controller->userFeed($user_id);
        } else {
            http_response_code(405);
            renderTemplate('error.twig', ['errorMessage' => "Method not allowed."]);
        }
    } else {
        // For other methods, just call the method
        $controller->$method();
    }
}
