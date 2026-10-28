# StudySpace — Smart Study Planner

A beginner-friendly full-stack web app built with HTML, CSS, JavaScript, PHP, and MySQL.

## Features
- Register and log in securely with password hashing
- Add, complete, search, filter, and delete study tasks
- Set task subject, due date, priority, and notes
- Dashboard counts for total, completed, due-soon, and overdue tasks
- Track overall completion progress
- Add and delete weekly timetable slots
- Browser-side 25-minute focus timer
- Responsive layout for laptop and mobile
- Each user's tasks and timetable are separated by account

## Requirements
- Windows
- XAMPP with Apache, MySQL, and PHP
- VS Code (or any text editor)
- Browser

## Installation (XAMPP on Windows)
1. Install and open XAMPP Control Panel.
2. Start **Apache** and **MySQL**.
3. Copy the entire `smart_study_planner` folder into `C:\xampp\htdocs\`.
4. Open `http://localhost/phpmyadmin`.
5. Select the **Import** tab, choose `database.sql` from this project folder, then click **Import/Go**. Alternatively, open the SQL tab and run the file's contents.
6. If your MySQL root account has a password, update `$db_password` in `config.php`. The default XAMPP setup usually uses an empty password.
7. Open `http://localhost/smart_study_planner/register.php`.
8. Create an account, then add tasks and timetable slots.

## Project file structure
- `config.php` — database connection, session, login guard, HTML escaping helper
- `database.sql` — database and tables
- `register.php` — account creation
- `login.php` — authentication
- `logout.php` — sign out
- `index.php` — dashboard, tasks, timetable, progress, focus timer UI
- `style.css` — responsive styles
- `script.js` — search/filter and focus timer

## Troubleshooting
- **Database connection failed:** check MySQL is running and the credentials in `config.php`.
- **Unknown database:** import `database.sql` in phpMyAdmin.
- **404 Not Found:** ensure the folder is inside `C:\xampp\htdocs\smart_study_planner`.
- **PHP code appears as text:** open it through `http://localhost/...`, not by double-clicking the PHP file.
- **Port conflict:** if Apache cannot start, check XAMPP's port settings or another application using port 80.

## Notes
This is a learning/demo project. For public deployment, add CSRF protection, stricter server-side validation, rate limiting, HTTPS, and production database credentials. The focus timer works while the dashboard page remains open; it does not send push/email notifications.
