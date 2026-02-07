"""
SQL Injection vulnerable endpoints
WARNING: Contains intentional SQL Injection vulnerabilities
"""

from fastapi import APIRouter, HTTPException, Query, Depends
from fastapi.responses import HTMLResponse
from sqlalchemy.orm import Session
from sqlalchemy import text

from database import get_db, User, Product

router = APIRouter()

@router.get("/level1")
async def sqli_level1(
    username: str = Query(..., description="Username to search"),
    db: Session = Depends(get_db)
):
    """
    Level 1: Basic SQL Injection
    VULNERABILITY: Direct string concatenation in SQL query
    
    Try: ' OR '1'='1
    """
    try:
        # VULNERABLE: Direct string formatting
        query = text(f"SELECT id, username, email FROM users WHERE username = '{username}'")
        results = db.execute(query).fetchall()
        
        if results:
            users = [{"id": r[0], "username": r[1], "email": r[2]} for r in results]
            return {"query": str(query), "results": users}
        else:
            return {"query": str(query), "results": [], "message": "No users found"}
    except Exception as e:
        # VULNERABLE: Exposes database errors
        return {"error": str(e), "query": f"SELECT id, username, email FROM users WHERE username = '{username}'"}

@router.get("/level2")
async def sqli_level2(
    id: str = Query(..., description="Product ID"),
    db: Session = Depends(get_db)
):
    """
    Level 2: UNION-based SQL Injection
    VULNERABILITY: Allows UNION injection to extract data from other tables
    
    Try: 1 UNION SELECT id, username, password FROM users--
    """
    try:
        # VULNERABLE: UNION injection possible
        query = text(f"SELECT id, name, description FROM products WHERE id = {id}")
        results = db.execute(query).fetchall()
        
        products = [{"col1": r[0], "col2": r[1], "col3": r[2]} for r in results]
        return {"query": str(query), "results": products}
    except Exception as e:
        return {"error": str(e), "query": f"SELECT id, name, description FROM products WHERE id = {id}"}

@router.get("/level3")
async def sqli_level3(
    order: str = Query("id", description="Order by column"),
    db: Session = Depends(get_db)
):
    """
    Level 3: Error-based SQL Injection
    VULNERABILITY: Order by injection with verbose errors
    
    Try: id; SELECT * FROM flags--
    """
    try:
        # VULNERABLE: ORDER BY injection
        query = text(f"SELECT id, username, role FROM users ORDER BY {order}")
        results = db.execute(query).fetchall()
        
        users = [{"id": r[0], "username": r[1], "role": r[2]} for r in results]
        return {"query": str(query), "results": users}
    except Exception as e:
        # VULNERABLE: Detailed error messages
        return {
            "error": str(e),
            "error_type": type(e).__name__,
            "query": f"SELECT id, username, role FROM users ORDER BY {order}",
            "hint": "Error messages can reveal database structure."
        }

@router.get("/search")
async def search_users(
    q: str = Query("", description="Search query"),
    db: Session = Depends(get_db)
):
    """
    Bonus: Search with LIKE injection
    VULNERABILITY: LIKE clause injection
    """
    try:
        # VULNERABLE: LIKE injection
        query = text(f"SELECT id, username, email FROM users WHERE username LIKE '%{q}%'")
        results = db.execute(query).fetchall()
        
        return {
            "query": str(query),
            "results": [{"id": r[0], "username": r[1], "email": r[2]} for r in results]
        }
    except Exception as e:
        return {"error": str(e)}

@router.get("/blind")
async def blind_sqli(
    id: str = Query(..., description="User ID"),
    db: Session = Depends(get_db)
):
    """
    Bonus: Blind SQL Injection (Boolean-based)
    VULNERABILITY: Different responses reveal data
    """
    try:
        query = text(f"SELECT id FROM users WHERE id = {id}")
        result = db.execute(query).fetchone()
        
        if result:
            return {"exists": True, "message": "User found"}
        else:
            return {"exists": False, "message": "User not found"}
    except Exception as e:
        return {"exists": False, "error": "Query failed"}
