# 📝 LearnBlog – A Simple Blogging Platform

LearnBlog is a **PHP + MySQL** based web application that allows users to **write, read, and share blogs** easily.  
It includes user authentication, comment management, and a clean, responsive design for an enjoyable reading and writing experience.

---

## 🚀 Live Demo
👉 **[Visit LearnBlog](https://learnblog.infinityfreeapp.com/)**

---

## ✨ Features

✅ **User Authentication**
- Register and log in securely.  
- Session-based access control.

✅ **Blog Management**
- Create, edit, view, and delete blog posts.  
- Each post includes a title, content, author, and date.

✅ **Comments System**
- Users can comment on blogs.  
- Admin can manage or remove inappropriate comments.

✅ **Responsive Design**
- Mobile-friendly UI built with HTML, CSS, and JavaScript.

✅ **Dynamic Content**
- Fetch and display blogs from a MySQL database.  
- RESTful PHP backend with JSON-based API responses.

---

## 🧠 Tech Stack

| Layer | Technology |
|:------|:------------|
| 💻 Frontend | HTML, CSS, JavaScript |
| ⚙️ Backend | PHP |
| 🗄️ Database | MySQL |
| 🌍 Hosting | InfinityFree |
| 🔐 Version Control | Git & GitHub |

---

## 🛠️ Setup & Installation

Follow these steps to run the project locally:


## 🛠️ Setup & Installation

Follow these steps to run the project locally:

### 1️⃣ Clone the Repository
```bash
git clone https://github.com/yourusername/learnblog.git
cd learnblog
2️⃣ Import the Database
Open phpMyAdmin (via XAMPP or your hosting panel).

Create a new database (e.g., learnblog_db).

Import the provided learnblog.sql file.

3️⃣ Configure the Database Connection
Edit the db_connect.php file and update your credentials:

php
Copy code
$servername = "localhost";
$username = "root";
$password = "";
$database = "learnblog_db";
4️⃣ Run the Project
Move the project folder into your local server directory (e.g., htdocs in XAMPP).

Start Apache & MySQL.

Visit:
👉 http://localhost/learnblog

📂 Project Structure
cpp
Copy code
learnblog/
├── backend/
│   ├── db_connect.php
│   ├── register.php
│   ├── login.php
│   ├── get_blogs.php
│   ├── get_single_blog.php
│   └── ...
├── assets/
│   ├── css/
│   └── js/
├── script.js
├── index.php
├── view_blog.php
└── README.md

👩‍💻 Author

Dewmini Weerapperuma
🎓 Undergraduate at University of Moratuwa (IT Faculty – ITM Hons)
💻 Passionate about front-end development, backend integration, and web technologies.
