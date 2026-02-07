# VulnForge Academy - Python Backend

A deliberately vulnerable FastAPI backend for cybersecurity training.

## ⚠️ WARNING

This application contains **INTENTIONAL security vulnerabilities** for educational purposes.

- **NEVER** deploy on production servers
- **ONLY** run in isolated environments  
- **NEVER** use real credentials
- **FOR EDUCATIONAL PURPOSES ONLY**

## Setup

### Prerequisites
- Python 3.9+
- pip or uv

### Installation

```bash
cd backend-python
pip install -r requirements.txt
```

### Run Development Server

```bash
uvicorn main:app --reload --port 8000
```

### API Documentation

Once running, visit:
- Swagger UI: http://localhost:8000/docs
- ReDoc: http://localhost:8000/redoc

## Vulnerable Endpoints

| Level | Vulnerability | Endpoint |
|-------|--------------|----------|
| 1-3 | SQL Injection | `/api/levels/sqli/*` |
| 4-6 | XSS | `/api/levels/xss/*` |
| 7-9 | IDOR | `/api/levels/idor/*` |
| 10-12 | Authentication | `/api/levels/auth/*` |
| 13-15 | SSRF | `/api/levels/ssrf/*` |
| 16-18 | File Upload | `/api/levels/upload/*` |
| 19 | RCE | `/api/levels/rce/*` |
| 20 | Multi-vector | `/api/levels/nightmare/*` |

## Project Structure

```
backend-python/
├── main.py              # FastAPI app entry point
├── database.py          # SQLite database setup
├── models.py            # Pydantic models
├── requirements.txt     # Dependencies
└── routers/
    ├── auth.py          # Authentication (weak)
    ├── sqli.py          # SQL Injection levels
    ├── xss.py           # XSS levels
    ├── idor.py          # IDOR levels
    ├── ssrf.py          # SSRF levels
    ├── upload.py        # File upload levels
    ├── rce.py           # RCE levels
    └── flags.py         # Flag verification
```

## License

MIT License - Educational use only.
