# Binsta - A Social Media Platform for Developers

Binsta is an engaging social media platform designed with a modern flair, specifically tailored for developers and tech enthusiasts. It's a space where you can share code snippets, connect with others, and discover a world of programming.

## Key Features

- User Authentication
- Sleek User Interface
- Code Snippets Sharing
- Profile Customization
- Interactive Feed
- Search and Connect
- Personalized User Profiles

## Tech Stack

This project is built with the following technologies:

- PHP: Server-side scripting language
- RedBeanPHP: An easy to use ORM for PHP
- MySQL: Database
- Twig: Template engine for PHP
- Tailwind CSS: A utility-first CSS framework
- HTML/CSS/JavaScript: Frontend technologies

This project is a full-stack application, meaning it includes both frontend and backend development.

## Getting Started

## 1. Clone the repository
```shell
 git clone git@bitlab.bit-academy.nl:1094686e-4796-4d01-9e9d-aa213d7182d6/59ea2f94-eae0-11ea-b861-cec41367f4e7/Binsta-Framework.git
```

## 2. Navigate to the project root and install dependencies
```shell
composer install
```

## 3. Configure your web server
### Ensure that your web server is properly configured to serve PHP files from the public directory.

## 4. Create the 'binsta' database
```shell
R::setup('mysql:host=localhost;dbname=binsta', 'root', '');
```
### Create a MySQL database named 'binsta' before proceeding with the next step.

## 5. Seed the database
```shell
php seeder.php
```

## 6. Configure your web server
### If you're using XAMPP, open the Apache( `httpd.conf`) file and change the `DocumentRoot` and the corresponding `<Directory>` directive to point to the `public` directory of your project. 

For example:
```shell
DocumentRoot "C:/Binsta-Framework/public"
<Directory "C:/Binsta-Framework/public">
```

## 6. Access the application
### Navigate to http://localhost in your web browser to access the application.
