"""
Flag verification endpoints
"""

from fastapi import APIRouter, HTTPException, Depends
from sqlalchemy.orm import Session

from database import get_db, Flag
from models import FlagSubmit, FlagResponse

router = APIRouter()

@router.post("/verify", response_model=FlagResponse)
async def verify_flag(submission: FlagSubmit, db: Session = Depends(get_db)):
    """
    Verify a flag submission for a level
    """
    flag = db.query(Flag).filter(Flag.level_id == submission.level_id).first()
    
    if not flag:
        return FlagResponse(
            success=False,
            message=f"Level {submission.level_id} not found"
        )
    
    if submission.flag.strip() == flag.flag:
        return FlagResponse(
            success=True,
            message=f"🎉 Correct! Level {submission.level_id} completed!"
        )
    else:
        return FlagResponse(
            success=False,
            message="Incorrect flag. Keep trying!"
        )

@router.get("/hint/{level_id}")
async def get_hint(level_id: int, db: Session = Depends(get_db)):
    """
    Get a hint for a specific level
    """
    flag = db.query(Flag).filter(Flag.level_id == level_id).first()
    
    if flag:
        return {"level_id": level_id, "hint": flag.hint}
    else:
        return {"error": "Level not found"}

@router.get("/all")
async def get_all_flags(db: Session = Depends(get_db)):
    """
    Get all flags (DEBUG ONLY - should be disabled in production)
    VULNERABILITY: Exposes all flags without authentication
    """
    flags = db.query(Flag).order_by(Flag.level_id).all()
    
    return {
        "warning": "This endpoint should be disabled in production!",
        "flags": [
            {"level_id": f.level_id, "flag": f.flag, "hint": f.hint}
            for f in flags
        ]
    }
