<div align="center">

# 🖼️ WallHub

**A premium wallpaper platform with 40+ categories, smart download limits, and a full admin suite.**

[![Live Demo](https://img.shields.io/badge/demo-wallhub.online-blue?style=flat-square)](https://wallhub.online)
![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479A1?style=flat-square&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-green?style=flat-square)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap&logoColor=white)

[Features](#-features) • [Tech Stack](#%EF%B8%8F-tech-stack) • [Installation](#-installation) • [Categories](#-categories) • [Contributing](#-contributing)

</div>

---

## 📖 About

WallHub is a custom-built wallpaper website written in PHP and MySQL — no frameworks, no CMS, just a hand-rolled backend covering authentication, role-based access, download tracking, and a full admin dashboard. It serves wallpapers across 40+ categories, from anime and movies to nature and gaming.

> [!TIP]
> Check out the live site at **[wallhub.online](https://wallhub.online)**.

## ✨ Features

### 👤 For Users
- **Secure authentication** — session-based login/register with hashed passwords
- **Tiered download limits** — Guest (5/day), Member (10/day), Premium (unlimited)
- **Favorites** — save wallpapers with an AJAX-powered toggle
- **Instant search** — AJAX search with live results
- **Download history** — every download tracked and viewable
- **Fully responsive** — desktop, tablet, and mobile

### 👑 For Admins
- **Role-based access control** — Owner, Admin, Premium, Member tiers
- **Message center** — reply to user messages with notification badges
- **Wallpaper management** — upload desktop and mobile variants
- **Download analytics** — daily / weekly / monthly top wallpapers
- **Duplicate detector** — Owner-only tool for cleaning up the image library

### 🔒 Security
- Prepared statements throughout — no raw SQL injection surface
- Passwords hashed with `password_hash()`
- Session regeneration on login
- Right-click and dev-tool guards on image pages, images served through PHP rather than direct paths
- HTTPS enforced via `.htaccess`

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.4 |
| Database | MySQL 5.7+ |
| UI Framework | Bootstrap 5.3 |
| Icons | Font Awesome 6.5 |
| JS Utilities | jQuery 3.6 |

## 📁 Project Structure

```
WallHub/
├── index.php          # Homepage — stats & category grid
├── explore.php        # Universal listing page for all categories
├── login.php          # Authentication
├── register.php       # New user signup
├── profile.php        # User profile management
├── download.php        # Secure download handler
├── serve_image.php    # Protected image delivery
├── admin_messages.php # Admin reply panel
├── favorites.php      # Saved wallpapers
├── search.php         # AJAX search endpoint
├── contact.php         # Contact form
├── css/                # Stylesheets
├── js/                 # Client-side scripts
└── includes/           # Shared PHP includes
```

## 🔑 User Roles

| Role | Daily Downloads | Notable Permissions |
|---|---|---|
| Owner | Unlimited | Full access, analytics, duplicate detector |
| Admin | Unlimited | Upload wallpapers, reply to messages |
| Premium | Unlimited | Ad-free, early access, request wallpapers |
| Member | 10 | Standard features |
| Guest | 5 | Browsing & limited downloads |

## 📦 Installation

> [!IMPORTANT]
> Requires **PHP 8.0+** and **MySQL 5.7+** on Apache or Nginx.

```bash
# 1. Clone the repo
git clone https://github.com/YashPawade/WallHub.git
cd WallHub

# 2. Set up config
cp config.example.php config.php
# then edit config.php with your DB credentials

# 3. Create the database
mysql -u root -p -e "CREATE DATABASE wallhub_db;"

# 4. Import the schema
mysql -u username -p wallhub_db < database.sql

# 5. Set permissions
chmod 755 uploads/
chmod 644 .htaccess
```

Point your web server's document root at the project folder, enable `mod_rewrite` for clean URLs, then visit your domain to get started.

## 🎨 Categories

WallHub covers **40+ categories**, including:

| Group | Categories | Examples |
|---|---|---|
| Anime | One Piece, Naruto, JJK, Bleach, Dragon Ball, Demon Slayer, AOT, MHA | Luffy, Naruto, Gojo, Goku, Tanjiro |
| Movies & TV | Spider-Man, Avatar, Stranger Things, John Wick, Breaking Bad, The Witcher | Peter Parker, Jake Sully, Eleven |
| Nature | Nature, Animals, Birds, Space, Fantasy | Landscapes, wildlife, galaxies |
| Gaming | Gaming, Transformers | Various titles, Optimus Prime |
| Actresses | Indian & International | — |

URLs follow a clean pattern, e.g. `/onepiece` for the full category and `/onepiece/luffy` to filter by character.

## 🤝 Contributing

1. Fork the repo
2. Create a feature branch: `git checkout -b feature/your-feature`
3. Commit your changes: `git commit -m "Add your feature"`
4. Push and open a Pull Request

## 📄 License

Distributed under the [MIT License](LICENSE).

## 👤 Author

**Yash Pawade**
[GitHub](https://github.com/YashPawade) • [Live site](https://wallhub.online) • yashpawade5@gmail.com

<div align="center">

Built with ❤️ by Yash Pawade

</div>
