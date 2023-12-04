<?php

// Path: controllers/FeedController.php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/BaseController.php'; // Require BaseController

use RedBeanPHP\R as R;

class FeedController extends BaseController
{
    public function index()
    {
        // Check if the user is logged in
        if (!$this->userIsLoggedIn()) {
            $this->redirect('/user/login');
            return;
        }

        // Fetch the snippets along with the user who posted each snippet
        $snippets = R::find('snippets', ' ORDER BY created_at DESC');

        foreach ($snippets as $snippet) {
            $snippet->user = R::findOne('users', ' id = ? ', [$snippet->user_id]);

            // Fetch the likes for the snippet

            $snippet->likeCount = $this->countLikes($snippet->id);

            // Fetch the comments for the snippet
            $comments = $this->getComments($snippet->id);

            // Fetch the user for each comment
            foreach ($comments as $comment) {
                $comment->user = R::load('users', $comment->user_id);
            }

            // Add the comments to the snippet
            $snippet->comments = $comments;
        }

        // Render the feed view with the snippets
        $this->render('posts/feed.twig', [
            'snippets' => $snippets,
        ]);

        // Unset the error message
        if (isset($_SESSION['error'])) {
            unset($_SESSION['error']);
        }
    }



    public function createPost()
    {

        // Check if the user is logged in
        if (!$this->userIsLoggedIn()) {
            $this->redirect('/user/login');
            return;
        }

        // Render the create post view
        $this->render('posts/create_post.twig');
    }

    public function storePost()
    {
        // Check if the user is logged in
        if (!$this->userIsLoggedIn()) {
            $this->redirect('/user/login');
            return;
        }

        // If the form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize and validate the input
            $code = trim(htmlspecialchars($_POST['code']));
            $language = trim(htmlspecialchars($_POST['language']));
            $caption = trim(htmlspecialchars($_POST['caption']));

            if (empty($code) || empty($caption)) {
                $_SESSION['error'] = "Code and caption cannot be empty.";
                $this->redirect('/feed/createPost');
                return;
            }

            // Check the length of code and caption
            if (strlen($code) > 500 || strlen($caption) > 50) {
                $_SESSION['error'] = "Code can't exceed 500 characters and caption can't exceed 50 characters.";
                $this->redirect('/feed/createPost');
                return;
            }

            // If the input is valid, create a new code snippet
            $snippet = R::dispense('snippets');
            $snippet->code = $code;
            $snippet->language = $language;
            $snippet->caption = $caption;
            $snippet->user_id = $_SESSION['user_id'];
            $snippet->created_at = date('Y-m-d H:i:s'); // Add this line
            R::store($snippet);

            // Redirect to the feed page
            $this->redirect('/feed');
        }
    }
    public function addLike()
    {
        // Check if the user is logged in
        if (!$this->userIsLoggedIn()) {
            $this->redirect('/user/login');
            return;
        }

        // Get the post id from the request
        $post_id = $_POST['post_id'];

        // Check if a like already exists for this user and post
        $like = R::findOne('likes', ' user_id = ? AND snippet_id = ?', [$_SESSION['user_id'], $post_id]);

        if ($like) {
            // If a like exists, remove it
            R::trash($like);
        } else {
            // If a like doesn't exist, create and save a new like
            $like = R::dispense('likes');
            $like->user_id = $_SESSION['user_id'];
            $like->snippet_id = $post_id;
            R::store($like);
        }

        // Redirect back to the feed
        $this->redirect('/feed');
    }

    public function countLikes($snippet_id)
    {
        // Fetch the likes for the snippet
        $likes = R::count('likes', ' snippet_id = ? ', [$snippet_id]);

        return $likes;
    }

    public function addComment()
    {
        // Check if the user is logged in
        if (!$this->userIsLoggedIn()) {
            $this->redirect('/user/login');
            return;
        }

        // Check if the request method is POST and the necessary data is present
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['snippet_id'], $_POST['text'])) {
            // Sanitize the input
            $snippet_id = filter_var($_POST['snippet_id'], FILTER_SANITIZE_NUMBER_INT);
            $text = htmlspecialchars($_POST['text'], ENT_QUOTES, 'UTF-8');

            // Validate the input
            if (strlen($text) > 500) {
                $_SESSION['error'] = 'Comment is too long. Maximum length is 500 characters.';
                $this->redirect('/feed');
                return;
            }

            if (trim($text) === '') {
                $_SESSION['error'] = 'Comment cannot be empty or only spaces.';
                $this->redirect('/feed');
                return;
            }

            $comment = R::dispense('comments');
            $comment->user_id = $_SESSION['user_id'];
            $comment->snippet_id = $snippet_id; // Use the sanitized $snippet_id here
            $comment->text = $text;
            $comment->created_at = date('Y-m-d H:i:s');
            R::store($comment);
        }

        // Redirect back to the feed
        $this->redirect('/feed');
    }

    // Method to fetch comments for a snippet
    public function getComments($snippet_id)
    {
        return R::find('comments', ' snippet_id = ? ORDER BY created_at DESC', [$snippet_id]);
    }


    public function searchUser()
    {
        // Get the search query from the request parameters
        $query = $_GET['query'];

        // Query the database for users whose username matches the search query
        $users = R::find('users', ' username LIKE ? ', ["%$query%"]);

        // Log the number of users found
        error_log('Number of users found: ' . count($users));

        // Log the users found
        foreach ($users as $user) {
            error_log('User: ' . $user->username);
        }

        // Render a view with the search results
        $this->render('users/search_results.twig', [
            'users' => $users,
        ]);
    }

    public function userFeed($user_id)
    {
        // Fetch snippets with the given ID
        $snippets = R::find('snippets', ' user_id = ? ORDER BY created_at DESC', [$user_id]);

        // Fetch the user from the database
        $user = R::load('users', $user_id);

        // Fetch likes and comments for each snippet
        foreach ($snippets as $snippet) {
            $snippet->likeCount = R::count('likes', ' snippet_id = ?', [$snippet->id]);
            $snippet->comments = R::find('comments', ' snippet_id = ? ORDER BY created_at DESC', [$snippet->id]);

            // Fetch the user for each comment
            foreach ($snippet->comments as $comment) {
                $commentUser = R::load('users', $comment->user_id);
                $comment->username = $commentUser->username;
                $comment->profile_picture = $commentUser->profile_picture;
            }
        }

        // Render a view with the snippets, the user, likes and comments
        $this->render('users/user_feed.twig', [
            'snippets' => $snippets,
            'user' => $user,
        ]);
    }
}
