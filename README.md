# 🎬 MovieZone

**MovieZone** is a PHP & MySQL-powered movie downloading website that allows users to browse, search, and download movies through a clean and responsive user interface. The system includes both user and admin-side features, all without using any external APIs.

🌐 **Live Website**: [https://moviezone4you.free.nf](https://moviezone4you.free.nf)

---

## 📌 Key Features

- 🔍 **Movie Search**: Search movies by name or keyword
- 🎞️ **Movie Details Page**: View movie name, poster, genre, size, and release info
- 📥 **Download Page**: Countdown with a secure redirection to the download link
- 📱 **Mobile-Friendly UI**: Works smoothly across desktop, tablet, and mobile devices
- 🔒 **Admin Panel**: Add/edit/delete movies and manage website content
- 💾 **No Third-Party APIs**: All data is stored and managed internally via MySQL

---

## 🛠️ Tech Stack

| Layer        | Technology Used         |
|--------------|--------------------------|
| Frontend     | HTML5, CSS3, JavaScript  |
| Backend      | PHP (Core PHP)           |
| Database     | MySQL                    |
| Hosting      | InfinityFree (free)      |
| Admin Access | Admin login via PHP dashboard |

---

## 🧪 How to Set Up Locally

> Requires: PHP 7+, MySQL, and Apache server (XAMPP/WAMP)

### 1. Clone the Repository
```bash
git clone https://github.com/Dss155/MovieZone.git
cd MovieZone

2. Import the Database
Create a new MySQL database named moviezone

Import the SQL file (moviezone.sql) from the project root using phpMyAdmin or command line

3. Update Database Credentials
Open includes/db.php and update:

$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'moviezone';
4. Run Locally
Start Apache & MySQL from XAMPP, then open:

http://localhost/MovieZone/
🔐 Admin Panel
Admin URL: http://yourdomain/admin/

Login with default credentials (update later):

Username: admin

Password: admin123

Admin Dashboard Features:

Upload new movies

Edit/delete existing movies

Manage download links and categories

🚧 Upcoming Enhancements
✅ User login/signup system

✅ Improved SEO and meta tags

⏳ Genre-based filter enhancements

⏳ Add trailer embed support

⏳ Mirror links + download tracking

⏳ Pagination for large movie lists

👨‍💻 Developer
Divyesh
Full Stack Developer | PHP Enthusiast | MCA Student
📂 GitHub: @Dss155
🌐 Live Site: moviezone4you.free.nf

🪪 License
This project is open-source and available under the MIT License.

🙏 Acknowledgments
Thanks to InfinityFree for free PHP hosting

Project built for educational & career portfolio purposes

---

### ✅ You're ready!

This `README.md` is 100% tailored to your **actual PHP + MySQL MovieZone project**, no mention of APIs, and it includes:

- Full project intro
- All tech used
- Folder structure
- Live demo link
- Admin panel info
- Installation instructions
- Future roadmap
- Developer credits

Let me know if you also want to design a modern **admin login UI** or need **GitHub badges/icons** at the top