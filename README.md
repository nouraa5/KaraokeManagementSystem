# 🎤 Karaoke Management System

A **PHP-based web application** for managing karaoke events — handling song requests, queue management, and event control for both **users** and **administrators**.

Users can submit song requests in real time, while admins manage the live queue, approve or reject requests, and track event history.

---

## 📁 Project Structure

```
karaoke/
├─ .venv/                  # Python venv (QR generator)
├─ assets/                 # CSS, JS, images
├─ includes/
│  ├─ db.php               # DB connection
│  └─ functions.php        # Helpers (CRUD, validation)
├─ public/
│  ├─ admin.php            # Admin dashboard
│  ├─ dashboard.php
│  ├─ history.php
│  ├─ home.php
│  ├─ index.php            # Entry / routing
│  ├─ login.php
│  ├─ logout.php
│  ├─ navbar.php
│  ├─ navbar-admin.php
│  ├─ navbar-user.php
│  ├─ queue.php            # Live queue view
│  ├─ requests.php         # All requests
│  ├─ songs.php            # Catalog CRUD
│  ├─ status.php
│  └─ tables.php
├─ qr_out/                 # Generated QR images
├─ qr_codes.pdf            # Combined QR export
├─ qr-codes.py             # QR generator
└─ README.md
```

---

## ⚙️ Features

### User
- Browse and search songs  
- Submit a song request (name/table number, optional note)  
- View request status and history  
- Logout securely  

### Admin
- Approve or reject requests  
- Reorder queue and mark *Singing* / *Done*  
- Manage song catalog (add/edit/delete)  
- Track history and generate reports  
- Access admin area via secure login  

### Extras
- Batch **QR code generation** (`qr-codes.py`)  
  - Creates PNGs in `qr_out/`  
  - Combines them into `qr_codes.pdf`

---

## 💻 Technologies Used

- **Frontend:** HTML5, CSS3, Bootstrap, JavaScript (ES6), DataTables  
- **Backend:** PHP (Procedural)  
- **Database:** MySQL (via XAMPP)  
- **Server:** Apache  
- **Utilities:** Python (QR generation), FPDF, qrcode libraries  
- **Data Handling:** AJAX  

---

## 🗃️ Database Configuration

**File:** `includes/db.php`

```php
<?php
$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "karaoke_db";

$conn = mysqli_connect($servername, $username, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
```

### Main Tables
- `users` – login/roles  
- `songs` – catalog  
- `requests` – user song requests  
- `history` – completed performances  

---

## 🚀 Installation (Local Setup)

1. Install **XAMPP** and start **Apache** + **MySQL**  
2. Copy the folder to:  
   ```
   C:\xampp\htdocs\karaoke
   ```
3. Create a database `karaoke_db` in **phpMyAdmin**  
4. Import your SQL schema (or create tables manually)  
5. Open in your browser:  
   ```
   http://localhost/karaoke/public/index.php
   ```
6. (Optional) Generate QR codes:  
   ```
   python qr-codes.py
   ```

---

## 🧾 QR Code Generator

**Run:**
```
python qr-codes.py
```

**Outputs:**
- PNG files in `qr_out/`  
- Combined PDF in `qr_codes.pdf`

---

## 🧠 Roadmap

- Real-time updates with WebSockets  
- Role-based permissions  
- Analytics (top songs, wait times)  
- CSV/Excel export  
- Dark mode support  
- Modern frontend (React/Vue)

---

## 👩‍💻 Author

**Noura El Achkar**  
📧 nouraachkar2002@gmail.com  
🔗 [LinkedIn](https://www.linkedin.com/in/nouraelachkar)
