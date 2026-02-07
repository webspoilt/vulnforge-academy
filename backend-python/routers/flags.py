"""
Flag verification endpoints
"""

from fastapi import APIRouter, HTTPException
import aiosqlite

from database import get_db
from models import FlagSubmit, FlagResponse

router = APIRouter()

@router.post("/verify", response_model=FlagResponse)
async def verify_flag(submission: FlagSubmit):
    """
    Verify a flag submission for a level
    """
    db = await get_db()
    try:
        cursor = await db.execute(
            "SELECT flag FROM flags WHERE level_id = ?",
            (submission.level_id,)
        )
        result = await cursor.fetchone()
        
        if not result:
            return FlagResponse(
                success=False,
                message=f"Level {submission.level_id} not found"
            )
        
        correct_flag = result[0]
        
        if submission.flag.strip() == correct_flag:
            return FlagResponse(
                success=True,
                message=f"🎉 Correct! Level {submission.level_id} completed!"
            )
        else:
            return FlagResponse(
                success=False,
                message="Incorrect flag. Keep trying!"
            )
    finally:
        await db.close()

@router.get("/hint/{level_id}")
async def get_hint(level_id: int):
    """
    Get a hint for a specific level
    """
    db = await get_db()
    try:
        cursor = await db.execute(
            "SELECT hint FROM flags WHERE level_id = ?",
            (level_id,)
        )
        result = await cursor.fetchone()
        
        if result:
            return {"level_id": level_id, "hint": result[0]}
        else:
            return {"error": "Level not found"}
    finally:
        await db.close()

@router.get("/all")
async def get_all_flags():
    """
    Get all flags (DEBUG ONLY - should be disabled in production)
    VULNERABILITY: Exposes all flags without authentication
    """
    db = await get_db()
    try:
        cursor = await db.execute("SELECT level_id, flag, hint FROM flags ORDER BY level_id")
        flags = await cursor.fetchall()
        
        return {
            "warning": "This endpoint should be disabled in production!",
            "flags": [
                {"level_id": f[0], "flag": f[1], "hint": f[2]}
                for f in flags
            ]
        }
    finally:
        await db.close()
