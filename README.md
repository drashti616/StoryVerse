# StoryVerse 📚✨

StoryVerse is a web-based audiobook platform where users can browse and listen to audiobooks, while admins manage and upload all audiobook content.

---

## 🚀 Features

- **User Authentication**: Secure login and registration for both regular users and admins.
- **Admin Dashboard**: Manage books, book parts, users, and overall site settings.
- **Audiobook Management**: Upload and organize audiobooks with metadata and audio parts/episodes.
- **Interactive Listening**: Seamless playback for users to enjoy their favorite stories.
- **Profile Customization**: Users can update their profile pictures.
- **Responsive Design**: Fully responsive layout optimized across desktop, tablet, and mobile screens.

## 🛠️ Technology Stack

- **Backend**: PHP (using PDO for secure database interactions)
- **Frontend**: HTML5, CSS3 (Custom Responsive CSS), JavaScript
- **Database**: MySQL
- **Environment**: XAMPP / WAMP local hosting or Apache production servers

## 📦 Getting Started

### Prerequisites

- [XAMPP](https://www.apachefriends.org/index.html) or [WAMP](https://www.wampserver.com/en/) installed on your machine.
- A GitHub account for version control.

### Installation

1. **Clone the repository**:
   ```bash
   git clone https://github.com/drashti616/StoryVerse.git
   ```

2. **Move the project**:
   Copy the project folder to your local server's root directory (e.g., `C:\xampp\htdocs\`).

3. **Set up the Database**:
   - Open [phpMyAdmin](http://localhost/phpmyadmin/).
   - Create a new database named `audiobooks`.
   - Import the SQL schema from `database/audiobooks.sql`.

4. **Add Initial Data**:
   - **Create an Admin Account**: Passwords are securely hashed, so you need a script to create the first admin. Create a temporary file named `create_admin.php` in your project folder with the following code:
     ```php
     <?php
     require 'includes/db_config.php';
     $username = 'admin';
     $email = 'admin@example.com';
     $password = password_hash('admin123', PASSWORD_DEFAULT);
     $stmt = $pdo->prepare("INSERT INTO admins (username, email, password_hash, role) VALUES (?, ?, ?, 'admin')");
     if($stmt->execute([$username, $email, $password])) { echo "Admin created successfully!"; }
     ?>
     ```
     Visit `http://localhost/StoryVerse/create_admin.php` in your browser to create the admin. 
     ⚠️ **Important:** Delete this file immediately after use for security!
     
   - **Create User Accounts**: Regular users can simply sign up using the `register.php` page on the website.
   - **Add Books and Audio**: Log in as the admin (`admin` / `admin123`) and use the Admin Dashboard to add new books, upload cover images, and add audio parts.

5. **Configuration**:
   - Navigate to `includes/db_config.php`.
   - Update the database credentials (`DB_USERNAME`, `DB_PASSWORD`, `DB_NAME`) to match your local or live hosting setup.

6. **Run the Application**:
   Open your browser and navigate to `http://localhost/StoryVerse/` (or visit the live deployment at [https://storyverse.infinityfreeapp.com/]).

## 📂 Project Structure

```text
StoryVerse/
├── database/
│   └── audiobooks.sql               # Core database SQL schema
├── includes/
│   ├── admin_footer.php             # Shared admin footer layout
│   ├── admin_header.php             # Shared admin navigation header layout
│   ├── db_config.php                # Database connection & PDO configuration
│   ├── footer.php                   # Shared user footer layout
│   ├── header.php                   # Shared user navigation header layout
│   └── responsive.css               # Shared responsive CSS stylesheet across all pages
├── profile_pictures/                # User uploaded profile pictures directory (.gitkeep)
├── saved_audio/                     # Audio files storage directory (.gitkeep)
├── saved_image/                     # Book cover images storage directory (.gitkeep)
├── about.php                        # About project & platform info page
├── admin_book_parts.php             # Admin manager for audiobook parts & episodes
├── admin_delete_user.php            # Admin handler to delete user accounts
├── admin_panel.php                  # Central Admin Dashboard overview
├── admin_profile.php                # Admin profile settings & updates
├── browse.php                       # Audiobook browsing & search interface
├── contact.php                      # Contact support & inquiry page
├── delete_account.php               # User account deletion script
├── edit_book.php                    # Admin edit page for existing audiobooks
├── existing_audiobooks.php          # Admin list of all registered audiobooks
├── index.php                        # Application entry landing page
├── login.php                        # User & Admin login authentication page
├── logout.php                       # Session termination & logout handler
├── manage_audiobooks.php            # Admin dashboard for adding/managing audiobooks
├── manage_users.php                 # Admin dashboard for managing registered users
├── message_detail.php               # Message viewer page for user/admin inquiries
├── play.php                         # Interactive audio player for audiobooks
├── profile.php                      # User profile management page
├── register.php                     # User registration page
├── upload_profile_picture.php       # Handler for user profile picture uploads
└── user_messages.php                # User inbox / sent messages management
```

> **Note on Media Folders:** All media folders are included in the repository as empty folders using hidden `.gitkeep` files. The actual `.mp3` and `.jpg/.png` files are ignored by Git for security and performance purposes. Users deploying this project should upload their own media via the Admin Panel.

## 🤝 Contributing

Contributions are welcome! If you find any issues or have suggestions for improvements, please open an issue or submit a pull request.

## 📄 License

Copyright (c) 2026 Drashti Rathod. All Rights Reserved. See the [LICENSE](LICENSE) file for details.

---
Made by [Drashti616](https://github.com/drashti616)
