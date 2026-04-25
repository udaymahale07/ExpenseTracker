# Expense Tracker

A beautiful, responsive web application for tracking your daily expenses, built with PHP, MySQL, HTML, CSS, and vanilla JavaScript.

## Features
- User Authentication: Secure registration and login system.
- Dashboard: View your expenses at a glance with interactive UI.
- Expense Management: Add, view, and categorize your daily expenses seamlessly.
- Profile Management: Update your personal details and avatar.
- Modern UI: Dark/Light mode theme switching with a sleek, responsive design.

## Technologies Used
- Frontend: HTML5, CSS3, JavaScript, Lucide Icons
- Backend: PHP (PDO for secure database interactions)
- Database: MySQL

## Setup Instructions

1. Prerequisites
   - Install [XAMPP](https://www.apachefriends.org/index.html) (or any other local server environment with PHP and MySQL).
   - Ensure the Apache and MySQL modules are running in your XAMPP Control Panel.

2. Clone the Repository
   - Clone this project into your `htdocs` directory (e.g., `C:\xampp\htdocs\ExpenseTracker`).

3. Database Setup
   - Open phpMyAdmin (usually `http://localhost/phpmyadmin`).
   - Create a new database named `ExpenseTracker`.
   - Import the `database.sql` file provided in this repository to automatically create the required `users` and `expenses` tables.

4. Configuration
   - Open `db_connect.php` in a text editor.
   - Verify that the database credentials match your local MySQL setup (default for XAMPP is usually user: `root` and password: `''`).

5. Run the Application
   - Open your web browser and navigate to `http://localhost/ExpenseTracker/login.html` to start using the app.
"# ExpenseTracker" 
