"""
SQL Injection vulnerable endpoints
WARNING: Contains intentional SQL Injection vulnerabilities
"""

from fastapi import APIRouter, HTTPException, Query
from fastapi.responses import HTMLResponse
import aiosqlite

from database import get_db

router = APIRouter()

@router.get("/level1")
async def sqli_level1(username: str = Query(..., description="Username to search")):
    """
    Level 1: Basic SQL Injection
    VULNERABILITY: Direct string concatenation in SQL query
    
    Try: ' OR '1'='1
    """
    db = await get_db()
    try:
        # VULNERABLE: Direct string formatting
        query = f"SELECT id, username, email FROM users WHERE username = '{username}'"
        cursor = await db.execute(query)
        results = await cursor.fetchall()
        
        if results:
            users = [{"id": r[0], "username": r[1], "email": r[2]} for r in results]
            return {"query": query, "results": users}
        else:
            return {"query": query, "results": [], "message": "No users found"}
    except Exception as e:
        # VULNERABLE: Exposes database errors
        return {"error": str(e), "query": query}
    finally:
        await db.close()

@router.get("/level2")
async def sqli_level2(id: str = Query(..., description="Product ID")):
    """
    Level 2: UNION-based SQL Injection
    VULNERABILITY: Allows UNION injection to extract data from other tables
    
    Try: 1 UNION SELECT id, username, password FROM users--
    """
    db = await get_db()
    try:
        # VULNERABLE: UNION injection possible
        query = f"SELECT id, name, description FROM products WHERE id = {id}"
        cursor = await db.execute(query)
        results = await cursor.fetchall()
        
        products = [{"col1": r[0], "col2": r[1], "col3": r[2]} for r in results]
        return {"query": query, "results": products}
    except Exception as e:
        return {"error": str(e), "query": query}
    finally:
        await db.close()

@router.get("/level3")
async def sqli_level3(order: str = Query("id", description="Order by column")):
    """
    Level 3: Error-based SQL Injection
    VULNERABILITY: Order by injection with verbose errors
    
    Try: id; SELECT * FROM flags--
    """
    db = await get_db()
    try:
        # VULNERABLE: ORDER BY injection
        query = f"SELECT id, username, role FROM users ORDER BY {order}"
        cursor = await db.execute(query)
        results = await cursor.fetchall()
        
        users = [{"id": r[0], "username": r[1], "role": r[2]} for r in results]
        return {"query": query, "results": users}
    except Exception as e:
        # VULNERABLE: Detailed error messages
        return {
            "error": str(e),
            "error_type": type(e).__name__,
            "query": query,
            "hint": "The database is SQLite. Error messages can reveal structure."
        }
    finally:
        await db.close()

@router.get("/search")
async def search_users(q: str = Query("", description="Search query")):
    """
    Bonus: Search with LIKE injection
    VULNERABILITY: LIKE clause injection
    """
    db = await get_db()
    try:
        # VULNERABLE: LIKE injection
        query = f"SELECT id, username, email FROM users WHERE username LIKE '%{q}%'"
        cursor = await db.execute(query)
        results = await cursor.fetchall()
        
        return {
            "query": query,
            "results": [{"id": r[0], "username": r[1], "email": r[2]} for r in results]
        }
    except Exception as e:
        return {"error": str(e)}
    finally:
        await db.close()

@router.get("/blind")
async def blind_sqli(id: str = Query(..., description="User ID")):
    """
    Bonus: Blind SQL Injection (Boolean-based)
    VULNERABILITY: Different responses reveal data
    """
    db = await get_db()
    try:
        query = f"SELECT id FROM users WHERE id = {id}"
        cursor = await db.execute(query)
        result = await cursor.fetchone()
        
        if result:
            return {"exists": True, "message": "User found"}
        else:
            return {"exists": False, "message": "User not found"}
    except Exception as e:
        return {"exists": False, "error": "Query failed"}
    finally:
        await db.close()
