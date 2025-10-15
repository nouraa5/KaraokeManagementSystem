🎤 Karaoke Management System

A PHP-based web application for managing karaoke events — built to handle song requests, queue management, and event control for both users and administrators.

The system allows users to request songs in real-time, while administrators manage the event queue, approve or reject song requests, and track performance history.

📁 Project Structure
karaoke/
│
├── .venv/                  # Python virtual environment (for QR generation)
│
├── assets/                 # CSS, JS, images, and static resources
│
├── includes/               # Reusable PHP modules
│   ├── db.php              # Database connection configuration
│   └── functions.php       # Helper functions (CRUD operations, validation, etc.)
│
├── public/                 # Main application pages (PHP)
│   ├── admin.php           # Admin dashboard (song queue, management)
│   ├── dashboard.php       # Overview for admins or users
│   ├── history.php         # Displays past performances
│   ├── home.php            # Landing page
│   ├── index.php           # Entry point / login redirect
│   ├── login.php           # Login page for users/admins
│   ├── logout.php          # Session logout script
│   ├── navbar.php          # Shared navigation bar
│   ├── navbar-admin.php    # Admin-specific navigation bar
│   ├── navbar-user.php     # User-specific navigation bar
│   ├── queue.php           # Live queue view (real-time song order)
│   ├── requests.php        # Displays all user requests
│   ├── songs.php           # Manage song list (CRUD)
│   ├── status.php          # Updates or checks current event status
│   ├── tables.php          # Displays data tables with search/filter
│   └── footer.php          # Common footer for all pages
│
├── qr_out/                 # Auto-generated QR images
│
├── qr_codes.pdf            # Combined QR export (generated via qr-codes.py)
├── qr-codes.py             # Python script for batch QR generation
│
└── README.md               # You are here

⚙️ Features
🧍‍♀️ User Functions

Browse and search songs available in the system.

Submit song requests with their name and table number.

View personal request history and queue status.

Logout safely from the session.

👩‍💼 Admin Functions

Manage song catalog (add, edit, delete).

Review, approve, or reject user requests.

Monitor the live queue and reorder songs if needed.

Track performance history and generate reports.

Access admin dashboard with secure login credentials.

🧩 Additional Tools

QR Code Generator (qr-codes.py): Generates unique QR codes for each table or participant and compiles them into a single PDF (qr_codes.pdf).

Reusable PHP includes: For clean modular code and better maintainability.

💻 Technologies Used
Type	Technology
Frontend	HTML5, CSS3, Bootstrap, JavaScript
Backend	PHP (Procedural)
Database	MySQL (via XAMPP)
Server	Apache (localhost or deployment server)
Additional Tools	Python (for QR generation), FPDF / qrcode libraries
DataTables / AJAX	For dynamic table updates and filtering
🗃️ Database Configuration

Database file: includes/db.php

Example connection:

<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "karaoke_db";

$conn = mysqli_connect($servername, $username, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>


Tables (example):

users → manages login info and roles (admin/user)

songs → list of available songs

requests → stores user song requests

history → archives completed performances

🚀 Installation (Local Setup)

Install XAMPP and start Apache & MySQL.

Copy the karaoke folder into:

C:\xampp\htdocs\


Create a database named karaoke_db in phpMyAdmin.

Import the SQL schema if available (or create manually based on tables above).

Access the app via browser:

http://localhost/karaoke/public/index.php


(Optional) Run the QR generator:

python qr-codes.py

🧾 QR Code Generator Usage

Generate QR codes for tables or participants:

python qr-codes.py


Output:

Individual PNGs in qr_out/

Combined PDF file: qr_codes.pdf

🧠 Future Enhancements

Live notifications for new song requests (AJAX / WebSocket)

Role-based access control (multi-admin support)

Enhanced analytics for top songs & singers

Modernized UI with Vue.js or React frontend

Export queue data as Excel or CSV

👩‍💻 Author

Noura El Achkar
Master’s in Web Development – Lebanese University
📧 nouraachkar2002@gmail.com

🔗 LinkedIn
