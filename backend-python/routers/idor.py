"""
IDOR vulnerable endpoints
WARNING: Contains intentional Insecure Direct Object Reference vulnerabilities
"""

from fastapi import APIRouter, HTTPException, Query, Path, Request
from fastapi.responses import FileResponse, JSONResponse
import aiosqlite
import os

from database import get_db

router = APIRouter()

@router.get("/level7/user/{user_id}")
async def idor_level7(user_id: int = Path(..., description="User ID")):
    """
    Level 7: Basic IDOR - User Profile
    VULNERABILITY: No authorization check, any user ID accessible
    
    Try: Change the user_id to access other users' data
    """
    db = await get_db()
    try:
        # VULNERABLE: No authorization check
        cursor = await db.execute(
            "SELECT id, username, email, role, balance, api_key FROM users WHERE id = ?",
            (user_id,)
        )
        user = await cursor.fetchone()
        
        if user:
            return {
                "user": {
                    "id": user[0],
                    "username": user[1],
                    "email": user[2],
                    "role": user[3],
                    "balance": user[4],
                    "api_key": user[5],  # VULNERABLE: Exposing sensitive data
                },
                "flag": "FLAG{idor7_us3r_pr0f1l3}" if user_id == 1 else None
            }
        else:
            raise HTTPException(status_code=404, detail="User not found")
    finally:
        await db.close()

@router.get("/level8/order/{order_id}")
async def idor_level8(order_id: int = Path(..., description="Order ID")):
    """
    Level 8: API IDOR - Order Details
    VULNERABILITY: Predictable order IDs, no ownership verification
    """
    # Simulated orders database
    orders = {
        1: {"id": 1, "user_id": 2, "product": "Basic Plan", "amount": 9.99, "status": "completed"},
        2: {"id": 2, "user_id": 2, "product": "Pro Plan", "amount": 29.99, "status": "pending"},
        3: {"id": 3, "user_id": 1, "product": "Enterprise", "amount": 99.99, "status": "completed", "secret": "FLAG{idor8_4p1_3xp0s3d}"},
        1000: {"id": 1000, "user_id": 1, "product": "Admin License", "amount": 0, "status": "internal", "admin_notes": "Contains all flags"},
    }
    
    # VULNERABLE: No ownership check
    if order_id in orders:
        return {"order": orders[order_id]}
    else:
        raise HTTPException(status_code=404, detail="Order not found")

@router.get("/level9/file")
async def idor_level9(filename: str = Query(..., description="Filename to download")):
    """
    Level 9: File Access IDOR / Path Traversal
    VULNERABILITY: No path validation, allows directory traversal
    
    Try: ../../../etc/passwd or ..\\..\\..\\windows\\system.ini
    """
    # Create a dummy files directory
    base_path = os.path.join(os.path.dirname(__file__), "..", "uploads")
    os.makedirs(base_path, exist_ok=True)
    
    # Create dummy files
    dummy_files = {
        "readme.txt": "Welcome to VulnForge Academy!",
        "public.txt": "This is a public file.",
        "secret.txt": "FLAG{idor9_f1l3_4cc3ss}\nThis file contains sensitive data.",
        ".htpasswd": "admin:$apr1$xyz$hashedpassword",
    }
    
    for fname, content in dummy_files.items():
        fpath = os.path.join(base_path, fname)
        if not os.path.exists(fpath):
            with open(fpath, "w") as f:
                f.write(content)
    
    # VULNERABLE: Direct path concatenation without validation
    file_path = os.path.join(base_path, filename)
    
    # Check if trying to access flag file
    if "secret" in filename.lower():
        try:
            with open(file_path, "r") as f:
                return {"content": f.read(), "path": file_path}
        except Exception as e:
            return {"error": str(e)}
    
    # For path traversal attempts
    try:
        # VULNERABLE: No path validation
        if os.path.exists(file_path):
            with open(file_path, "r") as f:
                content = f.read()[:500]  # Limit content
                return {"content": content, "path": file_path}
        else:
            return {"error": "File not found", "attempted_path": file_path}
    except Exception as e:
        return {"error": str(e), "attempted_path": file_path}

@router.get("/level9/files")
async def list_files():
    """List available files in the uploads directory"""
    base_path = os.path.join(os.path.dirname(__file__), "..", "uploads")
    try:
        files = os.listdir(base_path)
        return {"files": files, "hint": "Try accessing files outside this directory..."}
    except:
        return {"files": [], "error": "Directory not found"}
