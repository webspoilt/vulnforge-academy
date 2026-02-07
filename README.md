<div align="center">

<img src="https://capsule-render.vercel.app/api?type=waving&color=0:FF006E,50:FF4D00,100:FFD700&height=200&section=header&text=VulnForge%20Academy&fontSize=60&fontColor=fff&animation=fadeIn&fontAlignY=35&desc=Learn%20Hacking%20by%20Hacking%20-%20Ethically&descAlignY=55&descSize=16"/>

[![PHP](https://img.shields.io/badge/PHP-92.2%25-777BB4?style=for-the-badge&logo=php&logoColor=white)]()
[![Next.js](https://img.shields.io/badge/Next.js-16-black?style=for-the-badge&logo=next.js&logoColor=white)]()
[![TypeScript](https://img.shields.io/badge/TypeScript-5-3178C6?style=for-the-badge&logo=typescript&logoColor=white)]()
[![Security](https://img.shields.io/badge/Security-Training-FF006E?style=for-the-badge)]()

**20 Levels | Beginner → Nightmare | OWASP Top 10**

</div>

---

## 🎯 Overview

VulnForge Academy is a **deliberately vulnerable web application** designed for cybersecurity training. It features 20 progressive levels covering OWASP Top 10 vulnerabilities, CTF-style flags, progress tracking, and a competitive leaderboard.

**Perfect for:** Ethical hackers, bug bounty hunters, security enthusiasts, and CTF players.

---

## 📁 Project Structure

```
vulnforge-academy/
├── backend/                # PHP Backend Application
│   ├── admin/              # Admin panel
│   ├── assets/             # CSS & JavaScript
│   ├── includes/           # Shared PHP utilities
│   ├── levels/             # 20 vulnerability challenges
│   │   ├── easy/
│   │   └── moderate/
│   ├── config.php          # Configuration
│   ├── index.php           # Homepage
│   ├── login.php           # Authentication
│   └── ...                 # Other pages
├── frontend/               # Next.js 16 Frontend (Modern UI)
│   ├── src/
│   │   ├── app/            # App Router pages
│   │   └── components/     # React components (shadcn/ui)
│   └── package.json
├── database/               # SQL Schemas
│   ├── db.sql              # Main database schema
│   └── security_database_schema.sql
├── docs/                   # Documentation
├── docker/                 # Docker configurations
├── .env.example            # Environment template
└── Procfile
```

---

## 🚀 Quick Start

### Backend (PHP)

```bash
# 1. Copy environment config
cp .env.example .env
# Edit .env with your database credentials

# 2. Import database
mysql -u root -p < database/db.sql
mysql -u root -p < database/security_database_schema.sql

# 3. Start PHP server
cd backend
php -S localhost:8080

# 4. Open http://localhost:8080
```

### Frontend (Next.js)

```bash
cd frontend

# Install dependencies
bun install

# Start development server
bun run dev

# Open http://localhost:3000
```

### Using Docker

```bash
cd docker
docker-compose up -d

# Access: http://localhost:8080
```

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

## 📊 Features

- 🏆 **CTF-Style Flags** - Capture flags to progress
- 📈 **Progress Tracking** - Save your progress across sessions
- 🏅 **Leaderboard** - Compete with other hackers
- 📚 **Hints System** - Get help when stuck
- 🎓 **Learning Resources** - Educational content for each vulnerability
- 🐳 **Docker Support** - Easy deployment
- ✨ **Modern UI** - Next.js frontend with shadcn/ui components

---

## 🛡️ Security Notice

> ⚠️ **WARNING**: This application contains INTENTIONAL security vulnerabilities. 
> 
> - **NEVER** deploy on production servers
> - **ONLY** run in isolated environments
> - **NEVER** use real credentials
> - **FOR EDUCATIONAL PURPOSES ONLY**

---

## 🤝 Contributing

Found a bug? Want to add a new level? See [CONTRIBUTING.md](docs/CONTRIBUTING.md).

---

## 📄 License

MIT License - Educational Use Only

---

<div align="center">

**Happy Hacking! 🎯**

**Created by [webspoilt](https://github.com/webspoilt)**

<img src="https://capsule-render.vercel.app/api?type=waving&color=0:FFD700,50:FF4D00,100:FF006E&height=100&section=footer"/>

</div>
