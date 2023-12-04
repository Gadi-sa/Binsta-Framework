<?php

// Path: controllers/UserController.php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/BaseController.php';

use RedBeanPHP\R as R;

class UserController extends BaseController
{
    public function index()
    {
        // Redirect if user is not logged in
        if (!$this->userIsLoggedIn()) {
            $this->redirect('user/login');
            return;
        }
    }

    public function login()
    {
        // Check if the user is already logged in
        if ($this->userIsLoggedIn()) {
            header('Location: /feed');
            exit;
        }

        // Render the login form view
        renderTemplate('users/login.twig', [
            'error_message' => $_SESSION['error_message'] ?? '',
            'register_link' => '/user/register'
        ]);
        unset($_SESSION['error_message']); // Clear the error after display
    }

    public function loginPost()
    {
        // Check if the user is already logged in
        if ($this->userIsLoggedIn()) {
            header('Location: /feed');
            exit;
        }

        // Check the validity of login details here
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        // Fetch the user from the database by username
        $user = R::findOne('users', 'username = ?', [$username]);

        if (!$user || !password_verify($password, $user->password)) {
            $_SESSION['error_message'] = "Invalid username or password";
            header('Location: /user/login'); // Redirect with error message
            exit;
        }

        // Valid login, save the user's information in the session
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_username'] = $user->username;
        $_SESSION['profile_picture'] = $user->profile_picture;

        // Redirect to the feed page
        header('Location: /feed');
        exit;
    }

    public function register()
    {
        // Render the registration form view
        renderTemplate('users/register.twig', [
            'error_message' => $_SESSION['error_message'] ?? '',
            'login_link' => '/user/login'
        ]);
        unset($_SESSION['error_message']);
    }

    public function registerPost()
    {
        // Check the validity of registration details here
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        // Check if the username is already taken
        $existingUser = R::findOne('users', 'username = ?', [$username]);
        if ($existingUser) {
            $_SESSION['error_message'] = "Username already taken";
            renderTemplate('users/register.twig', [
                'error_message' => $_SESSION['error_message'],
                'login_link' => '/user/login'
            ]);
            unset($_SESSION['error_message']); // Clear the error message after displaying it
            return; // End execution 
        }

        // Hash the password before saving it to the database
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Dispense a new 'users' bean and set the username and password
        $user = R::dispense('users');
        $user->username = $username;
        $user->password = $hashedPassword;

        // Store the user bean in the database
        R::store($user);

        // Prepare the success message
        $_SESSION['success_message'] = "User registered successfully";

        // Render the registration form again with the success message
        renderTemplate('users/register.twig', [
            'success_message' => $_SESSION['success_message'],
            'login_link' => '/user/login'
        ]);
        unset($_SESSION['success_message']); // Clear the success message after displaying it
    }


    public function showProfile($id)
    {
        // Fetch the user with the given ID
        $user = R::load('users', $id);

        // If the user isn't found, redirect to the feed
        if (!$user) {
            $this->redirect('/feed');
            return;
        }

        // Render the profile view with the user
        $this->render('users/profile.twig', [
            'user' => $user,
        ]);
    }

    public function editProfile()
    {
        // Get the current user's profile
        $user = R::load('users', $_SESSION['user_id']);

        // Pass the success and error messages if they are set in the session
        $this->render('users/edit_profile.twig', [
            'user' => $user,
            'error' => $_SESSION['error_message'] ?? null,
            'success' => $_SESSION['success_message'] ?? null,
        ]);
        // Clear the messages after displaying them
        unset($_SESSION['error_message']);
        unset($_SESSION['success_message']);
    }



    public function updateProfile()
    {
        // Get the current user's profile
        $user = R::load('users', $_SESSION['user_id']);

        // Sanitize and validate the username
        $username = trim(htmlspecialchars($_POST['username']));
        if (strlen($username) > 50) {
            $_SESSION['error_message'] = "Username can't exceed 50 characters.";
            $this->redirect('/user/editProfile/' . $_SESSION['user_id']);
            return;
        }

        // Sanitize and validate the biography
        $biography = trim(htmlspecialchars($_POST['biography']));
        if (strlen($biography) > 300) {
            $_SESSION['error_message'] = "Biography can't exceed 300 characters.";
            $this->redirect('/user/editProfile/' . $_SESSION['user_id']);
            return;
        }

        // Check if the username or email already exists
        $existingUser = R::findOne('users', 'username = ? OR email = ?', [$_POST['username'], $_POST['email']]);
        error_log('Existing user: ' . print_r($existingUser, true)); // Add this line

        if ($existingUser && $existingUser->id != $user->id) {
            $_SESSION['error_message'] = "Username or email already exists.";
            $this->redirect('/user/editProfile/' . $_SESSION['user_id']);
            return;
        }

        // Check if the password fields are not empty
        if (!empty($_POST['password']) && !empty($_POST['confirm_password'])) {
            // Check if the password and confirm password fields match
            if ($_POST['password'] == $_POST['confirm_password']) {
                // Hash the new password and update it in the database
                $user->password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            } else {
                $_SESSION['error_message'] = "Passwords do not match.";
                $this->redirect('/user/editProfile/' . $_SESSION['user_id']);
                return;
            }
        }

        // Update the username and biography from the form input
        $user->username = $_POST['username'] ?? $user->username;
        $user->biography = $_POST['biography'] ?? $user->biography;

        // Initialize error flag
        $errorOccurred = false;

        // Check if a file was uploaded and if the upload was successful
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
            // Validate the file type here
            $fileInfo = getimagesize($_FILES['profile_picture']['tmp_name']);
            if ($fileInfo === false) {
                $_SESSION['error_message'] = "Invalid file type. Only image files are allowed.";
                $errorOccurred = true;
            } else {
                // Define the directory where the uploaded files will be moved to
                $uploads_dir = __DIR__ . '/../public/images/profiles';

                // Get the temporary file path of the uploaded file
                $tmp_name = $_FILES['profile_picture']['tmp_name'];

                // Generate a unique name for the uploaded file to avoid overwriting existing files
                $name = uniqid() . '-' . basename($_FILES['profile_picture']['name']);

                // Construct the final path for the uploaded file
                $uploaded_path = $uploads_dir . '/' . $name;

                // Try to move the uploaded file to the uploads directory
                if (move_uploaded_file($tmp_name, $uploaded_path)) {
                    // If the file was moved successfully, update the profile picture
                    $user->profile_picture = $name;
                } else {
                    // If the file could not be moved, set an error message
                    $_SESSION['error_message'] = "File upload failed";
                    $errorOccurred = true;
                }
            }
        }

        // Update the email address if there was no error
        if (!$errorOccurred) {
            $user->email = $_POST['email'] ?? $user->email; // Add validation as needed

            // Store the updated user profile in the database
            R::store($user);

            // Set a success message
            $_SESSION['success_message'] = "Profile updated successfully";
        }

        $_SESSION['user_username'] = $user['username'];
        $_SESSION['profile_picture'] = $user->profile_picture;


        // Redirect to the edit profile page
        $this->redirect('/user/editProfile/' . $_SESSION['user_id']);
    }


    public function logout()
    {
        // Log out the user by destroying the session
        session_unset();  // Unset all session variables
        session_destroy(); // Destroy the session data

        // Redirect to the login page
        header('Location: /user/login');
        exit;
    }
}
