# 🎬 MovieZone

[![MIT License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-7%2B-blue.svg?logo=php)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-blue.svg?logo=mysql)](https://www.mysql.com/)
[![Responsive](https://img.shields.io/badge/Responsive-Yes-brightgreen.svg?logo=responsive-design)](#)

**MovieZone** is a modern PHP & MySQL-powered movie downloading website. Users can browse, search, and download movies through a clean, mobile-friendly interface. The system features both user and admin panels, all managed internally—no external APIs required.

🌐 **Live Demo:** [moviezone4you.free.nf](https://moviezone4you.free.nf)

---

## 🚀 Features

- 🔍 **Smart Search:** Find movies by name or keyword
- 🎬 **Movie Details:** View posters, genres, size, and release info
- ⏳ **Secure Downloads:** Countdown timer with safe redirection
- 📱 **Fully Responsive:** Works on desktop, tablet, and mobile
- 🛡️ **Admin Dashboard:** Add, edit, delete movies & manage content
- 💾 **Internal Database:** All data managed via MySQL (no APIs)
- 🏷️ **Genre Filters:** Quickly browse by movie genre
- 📊 **SEO Optimized:** Enhanced meta tags for better visibility

---

## 🖼️ Screenshots

> _Add your screenshots here for a more engaging README!_

| Home Page | Movie Details | Admin Panel |
|-----------|--------------|-------------|
| ![Home](assets/screenshots/home.png) | ![Details](assets/screenshots/details.png) | ![Admin](assets/screenshots/admin.png) |

---

## 🛠️ Tech Stack

| Layer        | Technology Used              |
|--------------|-----------------------------|
| **Frontend** | HTML5, CSS3, JavaScript     |
| **Backend**  | PHP (Core PHP)              |
| **Database** | MySQL                       |
| **Hosting**  | InfinityFree (free)         |
| **Admin**    | PHP-based dashboard         |

---

## ⚡ Quick Start

> **Requirements:** PHP 7+, MySQL, Apache (XAMPP/WAMP recommended)

### 1. Clone the Repository

```bash
git clone https://github.com/Dss155/MovieZone.git
cd MovieZone
```

### 2. Import the Database

- Create a MySQL database named `moviezone`.
- Import `moviezone.sql` using phpMyAdmin or MySQL CLI.

### 3. Configure Database

Edit `includes/db.php`:

```php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'moviezone';
```

### 4. Launch Locally

- Start Apache & MySQL via XAMPP/WAMP.
- Visit: [http://localhost/MovieZone/](http://localhost/MovieZone/)

---

## 🔐 Admin Panel

- **URL:** `http://localhost/MovieZone/admin/`
- **Default Login:**  
  - Username: `admin`  
  - Password: `admin123`  
  _(Change after first login!)_

**Admin Features:**
- Upload, edit, or delete movies
- Manage download links & categories
- View and organize movie listings

---

## 🚧 Roadmap

- [x] User login/signup system
- [x] Improved SEO & meta tags
- [ ] Genre-based filter enhancements
- [ ] Trailer embed support
- [ ] Mirror links & download tracking
- [ ] Pagination for large movie lists

---

## 📂 Project Structure

```
MovieZone/
├── admin/         # Admin dashboard
├── assets/        # CSS, JS, images
├── includes/      # PHP includes (DB, helpers)
├── uploads/       # Movie posters/files
├── index.php      # Main entry
├── moviezone.sql  # Database schema
└── README.md
```

---

## 🤝 Contributing

Contributions are welcome and appreciated!  
If you have ideas for new features, improvements, or bug fixes:

1. **Fork** this repository.
2. **Create a new branch** for your feature or fix.
3. **Make your changes** (please describe what and where you changed).
4. **Submit a Pull Request** with a clear description of your changes.
5. You can also open an **Issue** if you want to discuss your idea first.

*Your suggestions and code will help MovieZone grow!*

---

## 📬 Contact & Support

For feedback, suggestions, or collaboration:  
📧 [Contact via GitHub](https://github.com/Dss155)  
Or simply open an [Issue](https://github.com/Dss155/MovieZone/issues) or [Pull Request](https://github.com/Dss155/MovieZone/pulls).

---

## 🪪 License

This project is licensed under the [MIT License](LICENSE).

---

## 🙏 Acknowledgments

- [InfinityFree](https://infinityfree.net/) for free PHP hosting  
- Built for educational & portfolio purposes

---

## 👨‍💻 Author

**Divyesh**  
Full Stack Developer | PHP Enthusiast | MCA Student  
[![GitHub](https://img.shields.io/badge/GitHub-@Dss155-181717?logo=github)](https://github.com/Dss155)  
🌐 [moviezone4you.free.nf](https://moviezone4you.free.nf)

---

## 🪪 License

This project is licensed under the [MIT License](LICENSE).

---

## 🙏 Acknowledgments

- [InfinityFree](https://infinityfree.net/) for free PHP hosting
- Built for educational & portfolio purposes

---

## 📬 Contact

For feedback, suggestions, or collaboration:  
📧 [Contact via GitHub](https://github.com/Dss155)

---
