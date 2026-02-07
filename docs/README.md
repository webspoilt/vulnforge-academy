<div align="center">

<img src="https://capsule-render.vercel.app/api?type=waving&color=0:FF006E,50:FF4D00,100:FFD700&height=200&section=header&text=VulnForge%20Academy&fontSize=60&fontColor=fff&animation=fadeIn&fontAlignY=35&desc=Learn%20Hacking%20by%20Hacking%20-%20Ethically&descAlignY=55&descSize=16"/>

[![PHP](https://img.shields.io/badge/PHP-92.2%25-777BB4?style=for-the-badge&logo=php&logoColor=white)]()
[![JavaScript](https://img.shields.io/badge/JavaScript-3.7%25-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)]()
[![CSS](https://img.shields.io/badge/CSS-3.7%25-1572B6?style=for-the-badge&logo=css3&logoColor=white)]()
[![Security](https://img.shields.io/badge/Security-Training-FF006E?style=for-the-badge)]()

**20 Levels | Beginner → Nightmare | OWASP Top 10**

</div>

---

## 🎯 Overview

VulnForge Academy is a **deliberately vulnerable web application** designed for cybersecurity training. It features 20 progressive levels covering OWASP Top 10 vulnerabilities, CTF-style flags, progress tracking, and a competitive leaderboard.

**Perfect for:** Ethical hackers, bug bounty hunters, security enthusiasts, and CTF players.

---

## 🎮 Levels & Vulnerabilities

| Level | Difficulty | Vulnerability | Technique |
|-------|------------|---------------|-----------|
| 1-3 | 🟢 Beginner | SQL Injection | UNION-based, Error-based |
| 4-6 | 🟢 Beginner | XSS | Stored, Reflected, DOM |
| 7-9 | 🟡 Easy | IDOR | Parameter manipulation |
| 10-12 | 🟡 Easy | Authentication | Brute force, Session flaws |
| 13-15 | 🟠 Medium | SSRF | Internal service access |
| 16-18 | 🟠 Medium | File Upload | Bypass validation |
| 19 | 🔴 Hard | RCE | Command injection |
| 20 | ⚫ Nightmare | Multi-vector | Chain exploitation |

---

## 🚀 Quick Start

### Using Docker (Recommended)
```bash
# Clone repository
git clone https://github.com/webspoilt/vulnforge-academy.git
cd vulnforge-academy

# Run with Docker Compose
docker-compose up -d

# Access the application
open http://localhost:8080
```

### Manual Installation
```bash
# Requirements: PHP 7.4+, MySQL 5.7+

# Clone repository
git clone https://github.com/webspoilt/vulnforge-academy.git
cd vulnforge-academy

# Import database
mysql -u root -p < db.sql

# Configure database connection
cp config.php.example config.php
# Edit config.php with your database credentials

# Start PHP built-in server
php -S localhost:8080
```

---

## 📊 Features

- 🏆 **CTF-Style Flags** - Capture flags to progress
- 📈 **Progress Tracking** - Save your progress across sessions
- 🏅 **Leaderboard** - Compete with other hackers
- 📚 **Hints System** - Get help when stuck
- 🎓 **Learning Resources** - Educational content for each vulnerability
- 🐳 **Docker Support** - Easy deployment

---

## 🛡️ Security Notice

> ⚠️ **WARNING**: This application contains INTENTIONAL security vulnerabilities. 
> 
> - **NEVER** deploy on production servers
> - **ONLY** run in isolated environments
> - **NEVER** use real credentials
> - **FOR EDUCATIONAL PURPOSES ONLY**

---

## 🏗️ Architecture

```
vulnforge-academy/
├── levels/              # 20 vulnerable levels
│   ├── level01/        # SQL Injection - Basic
│   ├── level02/        # SQL Injection - UNION
│   ├── ...
│   └── level20/        # Nightmare Challenge
├── admin/              # Admin panel
├── includes/           # Shared functions
├── assets/             # CSS, JS, images
└── db.sql              # Database schema
```

---

## 🤝 Contributing

Found a bug? Want to add a new level? See [CONTRIBUTING.md](CONTRIBUTING.md).

---

## 📄 License

MIT License - Educational Use Only

---

<div align="center">

**Happy Hacking! 🎯**

**Created by [webspoilt](https://github.com/webspoilt)**

<img src="https://capsule-render.vercel.app/api?type=waving&color=0:FFD700,50:FF4D00,100:FF006E&height=100&section=footer"/>

</div>
