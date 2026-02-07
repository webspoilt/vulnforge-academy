"""
VulnForge Academy - Deliberately Vulnerable FastAPI Backend
WARNING: This contains INTENTIONAL security vulnerabilities for training.
NEVER deploy on production servers!
"""

from fastapi import FastAPI, Request
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import HTMLResponse
from fastapi.staticfiles import StaticFiles
from contextlib import asynccontextmanager

from database import init_db
from routers import auth, sqli, xss, idor, ssrf, upload, rce, flags, invite

@asynccontextmanager
async def lifespan(app: FastAPI):
    """Initialize database on startup"""
    await init_db()
    yield

app = FastAPI(
    title="VulnForge Academy API",
    description="""
    ⚠️ **WARNING: DELIBERATELY VULNERABLE APPLICATION**
    
    This API contains intentional security vulnerabilities for educational purposes.
    
    ## Vulnerability Categories
    - SQL Injection (Levels 1-3)
    - Cross-Site Scripting (Levels 4-6)
    - Insecure Direct Object Reference (Levels 7-9)
    - Authentication Bypass (Levels 10-12)
    - Server-Side Request Forgery (Levels 13-15)
    - File Upload Vulnerabilities (Levels 16-18)
    - Remote Code Execution (Level 19)
    - Multi-Vector Chain (Level 20)
    
    **NEVER use in production. Educational purposes only.**
    """,
    version="1.0.0",
    lifespan=lifespan
)

# CORS - Intentionally permissive for training
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # VULNERABLE: Allows any origin
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Include routers
app.include_router(auth.router, prefix="/api/auth", tags=["Authentication"])
app.include_router(invite.router, prefix="/api/invite", tags=["Invite System"])
app.include_router(sqli.router, prefix="/api/levels/sqli", tags=["SQL Injection"])
app.include_router(xss.router, prefix="/api/levels/xss", tags=["XSS"])
app.include_router(idor.router, prefix="/api/levels/idor", tags=["IDOR"])
app.include_router(ssrf.router, prefix="/api/levels/ssrf", tags=["SSRF"])
app.include_router(upload.router, prefix="/api/levels/upload", tags=["File Upload"])
app.include_router(rce.router, prefix="/api/levels/rce", tags=["RCE"])
app.include_router(flags.router, prefix="/api/flags", tags=["Flags"])

@app.get("/", response_class=HTMLResponse)
async def root():
    """Landing page"""
    return """
    <!DOCTYPE html>
    <html>
    <head>
        <title>VulnForge Academy API</title>
        <style>
            body {
                font-family: monospace;
                background: #0a0a0f;
                color: #00ff41;
                padding: 2rem;
                max-width: 800px;
                margin: 0 auto;
            }
            h1 { color: #00ff41; }
            a { color: #00d4ff; }
            .warning {
                background: rgba(255, 0, 85, 0.2);
                border: 1px solid #ff0055;
                padding: 1rem;
                border-radius: 8px;
                margin: 1rem 0;
            }
        </style>
    </head>
    <body>
        <h1>🛡️ VulnForge Academy API</h1>
        <div class="warning">
            ⚠️ <strong>WARNING:</strong> This application contains INTENTIONAL security vulnerabilities.
            <br>NEVER deploy on production servers!
        </div>
        <p>📚 <a href="/docs">Swagger Documentation</a></p>
        <p>📖 <a href="/redoc">ReDoc Documentation</a></p>
        <h2>Available Endpoints:</h2>
        <ul>
            <li>/api/auth - Authentication (intentionally weak)</li>
            <li>/api/levels/sqli - SQL Injection challenges</li>
            <li>/api/levels/xss - XSS challenges</li>
            <li>/api/levels/idor - IDOR challenges</li>
            <li>/api/levels/ssrf - SSRF challenges</li>
            <li>/api/levels/upload - File upload challenges</li>
            <li>/api/levels/rce - RCE challenges</li>
            <li>/api/flags - Flag verification</li>
        </ul>
    </body>
    </html>
    """

@app.get("/api/health")
async def health_check():
    """Health check endpoint"""
    return {"status": "vulnerable", "message": "VulnForge Academy is running"}

@app.get("/api/levels")
async def get_levels():
    """Get all available levels"""
    return {
        "levels": [
            {"id": 1, "name": "SQL Injection - Basic", "difficulty": "easy", "category": "sqli"},
            {"id": 2, "name": "SQL Injection - UNION", "difficulty": "easy", "category": "sqli"},
            {"id": 3, "name": "SQL Injection - Error-based", "difficulty": "easy", "category": "sqli"},
            {"id": 4, "name": "XSS - Reflected", "difficulty": "easy", "category": "xss"},
            {"id": 5, "name": "XSS - Stored", "difficulty": "easy", "category": "xss"},
            {"id": 6, "name": "XSS - DOM", "difficulty": "easy", "category": "xss"},
            {"id": 7, "name": "IDOR - User Profile", "difficulty": "medium", "category": "idor"},
            {"id": 8, "name": "IDOR - API", "difficulty": "medium", "category": "idor"},
            {"id": 9, "name": "IDOR - File Access", "difficulty": "medium", "category": "idor"},
            {"id": 10, "name": "Auth - Brute Force", "difficulty": "medium", "category": "auth"},
            {"id": 11, "name": "Auth - Session", "difficulty": "medium", "category": "auth"},
            {"id": 12, "name": "Auth - JWT", "difficulty": "medium", "category": "auth"},
            {"id": 13, "name": "SSRF - Basic", "difficulty": "hard", "category": "ssrf"},
            {"id": 14, "name": "SSRF - Cloud Metadata", "difficulty": "hard", "category": "ssrf"},
            {"id": 15, "name": "SSRF - Filter Bypass", "difficulty": "hard", "category": "ssrf"},
            {"id": 16, "name": "Upload - Extension", "difficulty": "hard", "category": "upload"},
            {"id": 17, "name": "Upload - Content-Type", "difficulty": "hard", "category": "upload"},
            {"id": 18, "name": "Upload - Magic Bytes", "difficulty": "hard", "category": "upload"},
            {"id": 19, "name": "RCE - Command Injection", "difficulty": "nightmare", "category": "rce"},
            {"id": 20, "name": "The Final Boss", "difficulty": "nightmare", "category": "chain"},
        ]
    }
