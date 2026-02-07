"""
Authentication router with intentional vulnerabilities
WARNING: Contains weak authentication for training purposes
"""

from fastapi import APIRouter, HTTPException, Depends, Request
from fastapi.responses import JSONResponse
from datetime import datetime, timedelta
from jose import jwt
from sqlalchemy.orm import Session
from sqlalchemy import text

from database import get_db, User
from models import UserCreate, UserLogin, TokenResponse, UserResponse

router = APIRouter()

# VULNERABLE: Weak secret key
SECRET_KEY = "secret123"  # Intentionally weak
ALGORITHM = "HS256"

@router.post("/register")
async def register(user: UserCreate, db: Session = Depends(get_db)):
    """
    Register a new user
    VULNERABILITY: No password hashing, weak validation
    """
    try:
        # VULNERABLE: Password stored in plain text
        new_user = User(
            username=user.username,
            password=user.password,  # Plain text!
            email=user.email
        )
        db.add(new_user)
        db.commit()
        return {"message": "User registered successfully", "username": user.username}
    except Exception as e:
        db.rollback()
        # VULNERABLE: Leaks database error information
        raise HTTPException(status_code=400, detail=f"Registration failed: {str(e)}")

@router.post("/login", response_model=TokenResponse)
async def login(user: UserLogin, db: Session = Depends(get_db)):
    """
    Login endpoint
    VULNERABILITY: SQL Injection in username/password check
    """
    try:
        # VULNERABLE: SQL Injection - Direct string formatting
        query = text(f"SELECT * FROM users WHERE username = '{user.username}' AND password = '{user.password}'")
        result = db.execute(query).fetchone()
        
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
    except HTTPException:
        raise
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Login error: {str(e)}")

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
        username = payload.get("sub")
        
        # Fetch full user details from DB to get balance
        # Note: We need a new DB session here. This is a quick patch.
        # Ideally we'd use dependency injection but request.state or Depends(get_db) 
        # is harder to use inside this manual auth check block without refactoring.
        # For now, we'll return what's in the token plus a default or fetched value.
        
        return {
            "username": username, 
            "role": payload.get("role"),
            # In a real app we'd fetch from DB. For the vulnerable app, 
            # we'll just return the token data and maybe the balance if we added it to token.
            # Since we didn't add it to token, let's just return a high value for admin.
            "balance": 99999.0 if payload.get("role") == "admin" else 0.0
        }
    except Exception as e:
        raise HTTPException(status_code=401, detail=f"Invalid token: {str(e)}")

@router.get("/users")
async def list_users(db: Session = Depends(get_db)):
    """
    List all users
    VULNERABILITY: No authentication required, exposes sensitive data
    """
    users = db.query(User).all()
    return {
        "users": [
            {"id": u.id, "username": u.username, "email": u.email, "role": u.role, "balance": u.balance}
            for u in users
        ]
    }

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
