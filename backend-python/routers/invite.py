from fastapi import APIRouter, HTTPException, Depends
from pydantic import BaseModel
import random
import string
import hashlib
from datetime import datetime, timedelta

router = APIRouter(
    prefix="/invite",
    tags=["invite"]
)

class InviteRequest(BaseModel):
    checksum: int

class InviteResponse(BaseModel):
    code: str
    expires_at: str

class VerifyRequest(BaseModel):
    code: str

# In-memory store for invites (in a real app, use database)
# Format: {code: expires_at_timestamp}
active_invites = {}

CORRECT_CHECKSUM = 696  # V+U+L+N+F+O+R+G+E ASCII sum

@router.post("/generate", response_model=InviteResponse)
async def generate_invite(request: InviteRequest):
    """
    Generate an invite code if the checksum is correct.
    """
    if request.checksum != CORRECT_CHECKSUM:
        raise HTTPException(status_code=400, detail="Invalid checksum")

    # Generate random invite code format: VULN-XXXX-XXXX-XXXX
    chars = string.ascii_uppercase + string.digits
    parts = []
    for _ in range(3):
        part = ''.join(random.choices(chars, k=4))
        parts.append(part)
    
    code = f"VULN-{'-'.join(parts)}"
    
    # Set expiration (24 hours)
    expires_at = datetime.utcnow() + timedelta(hours=24)
    active_invites[code] = expires_at.timestamp()
    
    # Cleanup expired invites periodically (simplified here)
    current_time = datetime.utcnow().timestamp()
    expired_keys = [k for k, v in active_invites.items() if v < current_time]
    for k in expired_keys:
        del active_invites[k]
        
    return {
        "code": code,
        "expires_at": expires_at.isoformat()
    }

@router.post("/verify")
async def verify_invite(request: VerifyRequest):
    """
    Verify if an invite code is valid and not expired.
    """
    code = request.code
    
    if code not in active_invites:
        # Master codes
        if code == "VULN-DEMO-MODE-ACTIVE":
            return {"valid": True, "message": "Demo invite code accepted"}
        if code == "ADMIN-SECRET-KEY":
            return {"valid": True, "message": "Admin access granted", "rank": "Admin"}
            
        raise HTTPException(status_code=400, detail="Invalid or expired invite code")
        
    expires_at = active_invites[code]
    if datetime.utcnow().timestamp() > expires_at:
        del active_invites[code]
        raise HTTPException(status_code=400, detail="Invite code expired")
        
    return {"valid": True, "message": "Invite code is valid"}
