<div align="center">
    <img src="docs/assets/logo.png" alt="WallHub Logo" width="140" />
    <h1><b>WallHub</b></h1>
    <p><b>The Ultimate Wallpaper Universe — Curated in 4K.</b></p>
    <a href="https://wallhub.online"><img src="https://img.shields.io/badge/demo-wallhub.online-1e90ff?style=for-the-badge" alt="Live Demo" /></a>
</div>

<div align="center">
  <p>
    <a href="https://github.com/YashPawade/WallHub/stargazers">
      <img src="https://img.shields.io/github/stars/YashPawade/WallHub?style=for-the-badge&color=yellow" alt="Stars" />
    </a>
    <a href="https://github.com/YashPawade/WallHub/network/members">
      <img src="https://img.shields.io/github/forks/YashPawade/WallHub?style=for-the-badge&color=orange" alt="Forks" />
    </a>
    <a href="https://github.com/YashPawade/WallHub/issues">
      <img src="https://img.shields.io/github/issues/YashPawade/WallHub?style=for-the-badge&color=blue" alt="Issues" />
    </a>
    <a href="https://github.com/YashPawade/WallHub/blob/main/LICENSE">
      <img src="https://img.shields.io/badge/License-MIT-brightgreen?style=for-the-badge" alt="License" />
    </a>
    <img src="https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP" />
    <img src="https://img.shields.io/badge/MySQL-5.7%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL" />
  </p>

  <h3>
    <a href="#-features">Features</a>
    <span> | </span>
    <a href="#-tech-stack">Tech Stack</a>
    <span> | </span>
    <a href="#-installation">Installation</a>
    <span> | </span>
    <a href="#-categories">Categories</a>
    <span> | </span>
    <a href="#-contributing">Contributing</a>
  </h3>
</div>

---

<div align="center">

### 📊 By the Numbers

| 🖼️ Wallpapers | ⬇️ Downloads | 🗂️ Categories |
| :---: | :---: | :---: |
| **2K+** | **9M+** | **10+** |

</div>

---

## 📖 About WallHub

**WallHub** is a custom-built wallpaper platform written in PHP and MySQL — no frameworks, no CMS, just a hand-rolled backend covering authentication, role-based access, download tracking, and a full admin dashboard. With **2,000+ wallpapers**, **9 million+ downloads**, and **10+ categories** spanning anime, movies, nature, and gaming, WallHub has grown into a genuinely large-scale wallpaper hub.

> [!TIP]
> Check out the live site at **[wallhub.online](https://wallhub.online)**.

---

## <a id="-features"></a>🚀 Features

<table width="100%">
  <tr>
    <td width="50%" valign="top">
      <h3>👤 For Users</h3>
      <ul>
        <li><b>Secure Authentication:</b> Session-based login/register with hashed passwords.</li>
        <li><b>Tiered Downloads:</b> Guest (5/day), Member (10/day), Premium (unlimited).</li>
        <li><b>Favorites:</b> Save wallpapers with an AJAX-powered toggle.</li>
        <li><b>Instant Search:</b> AJAX search with live results.</li>
        <li><b>Download History:</b> Every download tracked and viewable.</li>
        <li><b>Fully Responsive:</b> Desktop, tablet, and mobile.</li>
      </ul>
    </td>
    <td width="50%" valign="top">
      <h3>👑 For Admins</h3>
      <ul>
        <li><b>Role-Based Access:</b> Owner, Admin, Premium, Member tiers.</li>
        <li><b>Message Center:</b> Reply to user messages with notification badges.</li>
        <li><b>Wallpaper Management:</b> Upload desktop and mobile variants.</li>
        <li><b>Download Analytics:</b> Daily / weekly / monthly top wallpapers.</li>
        <li><b>Duplicate Detector:</b> Owner-only tool for cleaning up the image library.</li>
      </ul>
    </td>
  </tr>
</table>

---

## 🔒 Security

> [!IMPORTANT]
> Every request path is hardened — prepared statements, hashed credentials, and protected image delivery.

- Prepared statements throughout — no raw SQL injection surface
- Passwords hashed with `password_hash()`
- Session regeneration on login
- Right-click and dev-tool guards on image pages; images served through PHP rather than direct paths
- HTTPS enforced via `.htaccess`

---

## <a id="-tech-stack"></a>🛠 Tech Stack

<div align="center">

| Category | Technologies |
| :--- | :--- |
| **Backend** | ![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat-square&logo=php&logoColor=white) |
| **Database** | ![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat-square&logo=mysql&logoColor=white) |
| **UI Framework** | ![Bootstrap](https://img.shields.io/badge/Bootstrap-7952B3?style=flat-square&logo=bootstrap&logoColor=white) |
| **Icons** | Font Awesome 6.5 |
| **JS Utilities** | ![jQuery](https://img.shields.io/badge/jQuery-0769AD?style=flat-square&logo=jquery&logoColor=white) |

</div>

<details>
<summary>📁 Project structure</summary>

```
WallHub/
├── index.php          # Homepage — stats & category grid
├── explore.php        # Universal listing page for all categories
├── login.php          # Authentication
├── register.php       # New user signup
├── profile.php        # User profile management
├── download.php       # Secure download handler
├── serve_image.php    # Protected image delivery
├── admin_messages.php # Admin reply panel
├── favorites.php      # Saved wallpapers
├── search.php         # AJAX search endpoint
├── contact.php        # Contact form
├── css/                # Stylesheets
├── js/                 # Client-side scripts
└── includes/            # Shared PHP includes
```

</details>

---

## 🔑 User Roles

<div align="center">

| Role | Daily Downloads | Notable Permissions |
| :---: | :---: | :--- |
| **Owner** | Unlimited | Full access, analytics, duplicate detector |
| **Admin** | Unlimited | Upload wallpapers, reply to messages |
| **Premium** | Unlimited | Ad-free, early access, request wallpapers |
| **Member** | 10 | Standard features |
| **Guest** | 5 | Browsing & limited downloads |

</div>

---

## <a id="-installation"></a>📥 Installation

> [!NOTE]
> Requires **PHP 8.0+** and **MySQL 5.7+** on Apache or Nginx.

1. **Clone the repository**
   ```bash
   git clone https://github.com/YashPawade/WallHub.git
   cd WallHub
   ```
2. **Set up config**
   ```bash
   cp config.example.php config.php
   # then edit config.php with your DB credentials
   ```
3. **Create the database**
   ```bash
   mysql -u root -p -e "CREATE DATABASE wallhub_db;"
   ```
4. **Import the schema**
   ```bash
   mysql -u username -p wallhub_db < database.sql
   ```
5. **Set permissions**
   ```bash
   chmod 755 uploads/
   chmod 644 .htaccess
   ```

Point your web server's document root at the project folder, enable `mod_rewrite` for clean URLs, then visit your domain to get started.

---

## <a id="-categories"></a>🎨 Categories

WallHub covers **10+ categories**, including:

<div align="center">

| Group | Categories | Examples |
| :--- | :--- | :--- |
| **Anime** | One Piece, Naruto, JJK, Bleach, Dragon Ball, Demon Slayer, AOT, MHA | Luffy, Naruto, Gojo, Goku, Tanjiro |
| **Movies & TV** | Spider-Man, Avatar, Stranger Things, John Wick, Breaking Bad, The Witcher | Peter Parker, Jake Sully, Eleven |
| **Nature** | Nature, Animals, Birds, Space, Fantasy | Landscapes, wildlife, galaxies |
| **Gaming** | Gaming, Transformers | Various titles, Optimus Prime |
| **Actresses** | Indian & International | — |

</div>

URLs follow a clean pattern, e.g. `/onepiece` for the full category and `/onepiece/luffy` to filter by character.

---

## <a id="-contributing"></a>🤝 Contributing

<details>
<summary>How to contribute</summary>

1. **Fork** the repository
2. **Create your feature branch:** `git checkout -b feature/your-feature`
3. **Commit your changes:** `git commit -m "Add your feature"`
4. **Push to the branch:** `git push origin feature/your-feature`
5. **Open a Pull Request**

</details>

---

## ⚖️ License

Distributed under the [MIT License](LICENSE).

---

<div align="center">
  <b>Built with ❤️ by Yash Pawade</b><br/>
  <a href="https://github.com/YashPawade">GitHub</a> •
  <a href="https://wallhub.online">Live Site</a> •
  <a href="mailto:yashpawade5@gmail.com">Contact</a>
</div>
