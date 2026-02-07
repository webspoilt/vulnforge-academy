"""
Authentication router with intentional vulnerabilities
WARNING: Contains weak authentication for training purposes
"""

from fastapi import APIRouter, HTTPException, Depends, Request
from fastapi.responses import JSONResponse
from datetime import datetime, timedelta
from jose import jwt
import aiosqlite

from database import get_db
from models import UserCreate, UserLogin, TokenResponse, UserResponse

router = APIRouter()

# VULNERABLE: Weak secret key
SECRET_KEY = "secret123"  # Intentionally weak
ALGORITHM = "HS256"

@router.post("/register")
async def register(user: UserCreate):
    """
    Register a new user
    VULNERABILITY: No password hashing, weak validation
    """
    db = await get_db()
    try:
        # VULNERABLE: Password stored in plain text
        await db.execute(
            "INSERT INTO users (username, password, email) VALUES (?, ?, ?)",
            (user.username, user.password, user.email)
        )
        await db.commit()
        return {"message": "User registered successfully", "username": user.username}
    except Exception as e:
        # VULNERABLE: Leaks database error information
        raise HTTPException(status_code=400, detail=f"Registration failed: {str(e)}")
    finally:
        await db.close()

@router.post("/login", response_model=TokenResponse)
async def login(user: UserLogin):
    """
    Login endpoint
    VULNERABILITY: SQL Injection in username/password check
    """
    db = await get_db()
    try:
        # VULNERABLE: SQL Injection - Direct string formatting
        query = f"SELECT * FROM users WHERE username = '{user.username}' AND password = '{user.password}'"
        cursor = await db.execute(query)
        result = await cursor.fetchone()
        
        if result:
            # Create JWT token
            token_data = {
                "sub": result[1],  # username
                "role": result[4],  # role
                "exp": datetime.utcnow() + timedelta(hours=24)
            }
            token = jwt.encode(token_data, SECRET_KEY, algorithm=ALGORITHM)
            return TokenResponse(access_token=token)
        else:
            raise HTTPException(status_code=401, detail="Invalid credentials")
    finally:
        await db.close()

@router.get("/me")
async def get_current_user(request: Request):
    """
    Get current user from token
    VULNERABILITY: Weak JWT verification
    """
    auth_header = request.headers.get("Authorization")
    if not auth_header or not auth_header.startswith("Bearer "):
        raise HTTPException(status_code=401, detail="Not authenticated")
    
    token = auth_header.split(" ")[1]
    try:
        # VULNERABLE: Using weak secret
        payload = jwt.decode(token, SECRET_KEY, algorithms=[ALGORITHM])
        return {"username": payload.get("sub"), "role": payload.get("role")}
    except Exception as e:
        raise HTTPException(status_code=401, detail=f"Invalid token: {str(e)}")

@router.get("/users")
async def list_users():
    """
    List all users
    VULNERABILITY: No authentication required, exposes sensitive data
    """
    db = await get_db()
    try:
        cursor = await db.execute("SELECT id, username, email, role, balance FROM users")
        users = await cursor.fetchall()
        return {
            "users": [
                {"id": u[0], "username": u[1], "email": u[2], "role": u[3], "balance": u[4]}
                for u in users
            ]
        }
    finally:
        await db.close()

@router.get("/admin")
async def admin_panel(request: Request):
    """
    Admin panel
    VULNERABILITY: Easily bypassed role check
    """
    # VULNERABLE: Role from header can be spoofed
    role = request.headers.get("X-User-Role", "guest")
    
    if role != "admin":
        raise HTTPException(status_code=403, detail="Admin access required")
    
    return {
        "message": "Welcome to admin panel!",
        "flag": "FLAG{auth_byp4ss_r0l3_sp00f1ng}",
        "secrets": ["Database credentials", "API keys", "User passwords"]
    }
