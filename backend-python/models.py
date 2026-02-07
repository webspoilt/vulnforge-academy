"""
Pydantic models for request/response validation
"""

from pydantic import BaseModel
from typing import Optional, List
from datetime import datetime

class UserCreate(BaseModel):
    username: str
    password: str
    email: Optional[str] = None

class UserLogin(BaseModel):
    username: str
    password: str

class UserResponse(BaseModel):
    id: int
    username: str
    email: Optional[str]
    role: str
    balance: float

class TokenResponse(BaseModel):
    access_token: str
    token_type: str = "bearer"

class MessageCreate(BaseModel):
    content: str

class MessageResponse(BaseModel):
    id: int
    user_id: Optional[int]
    content: str  # Not sanitized - intentionally vulnerable
    created_at: Optional[str]

class ProductResponse(BaseModel):
    id: int
    name: str
    price: float
    description: Optional[str]
    owner_id: Optional[int]

class FlagSubmit(BaseModel):
    level_id: int
    flag: str

class FlagResponse(BaseModel):
    success: bool
    message: str

class LevelResponse(BaseModel):
    id: int
    name: str
    difficulty: str
    category: str

class SSRFRequest(BaseModel):
    url: str

class CommandRequest(BaseModel):
    command: str
