"""
SSRF vulnerable endpoints
WARNING: Contains intentional Server-Side Request Forgery vulnerabilities
"""

from fastapi import APIRouter, HTTPException, Query
from fastapi.responses import JSONResponse
import httpx
import asyncio
from urllib.parse import urlparse

router = APIRouter()

# Simulated internal services
INTERNAL_SERVICES = {
    "admin-panel": {"status": "running", "secret": "FLAG{ssrf13_1nt3rn4l}", "users": 42},
    "database": {"status": "running", "connection_string": "mysql://root:password123@localhost:3306/vulnforge"},
    "cache": {"status": "running", "type": "redis", "keys": 1337},
}

@router.get("/level13")
async def ssrf_level13(url: str = Query(..., description="URL to fetch")):
    """
    Level 13: Basic SSRF
    VULNERABILITY: No URL validation, can access internal services
    
    Try: http://localhost:8000/api/levels/ssrf/internal/admin-panel
    """
    try:
        # VULNERABLE: No URL validation
        async with httpx.AsyncClient(timeout=5.0) as client:
            response = await client.get(url)
            return {
                "url": url,
                "status_code": response.status_code,
                "content": response.text[:1000],
                "headers": dict(response.headers)
            }
    except Exception as e:
        return {"error": str(e), "url": url}

@router.get("/level14")
async def ssrf_level14(url: str = Query(..., description="URL to fetch")):
    """
    Level 14: SSRF to Cloud Metadata
    VULNERABILITY: Can access cloud metadata endpoints
    
    Try: http://169.254.169.254/latest/meta-data/
    """
    # Simulate cloud metadata response
    if "169.254.169.254" in url:
        if "meta-data" in url:
            return {
                "metadata": {
                    "instance-id": "i-1234567890abcdef0",
                    "ami-id": "ami-0123456789abcdef",
                    "iam": {
                        "security-credentials": {
                            "admin-role": {
                                "AccessKeyId": "AKIAIOSFODNN7EXAMPLE",
                                "SecretAccessKey": "wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY",
                                "Token": "FLAG{ssrf14_cl0ud_m3t4}"
                            }
                        }
                    }
                }
            }
    
    try:
        async with httpx.AsyncClient(timeout=5.0) as client:
            response = await client.get(url)
            return {"content": response.text[:500], "status": response.status_code}
    except Exception as e:
        return {"error": str(e)}

@router.get("/level15")
async def ssrf_level15(url: str = Query(..., description="URL to fetch")):
    """
    Level 15: SSRF with Filter Bypass
    VULNERABILITY: Weak blocklist can be bypassed
    
    Blocked: localhost, 127.0.0.1, internal
    Try: http://127.1/ or http://0x7f000001/ or http://localhost.attacker.com/
    """
    # Weak blocklist
    blocked = ["localhost", "127.0.0.1", "internal", "169.254"]
    
    # VULNERABLE: Easy to bypass blocklist
    url_lower = url.lower()
    for block in blocked:
        if block in url_lower:
            return {"error": f"Blocked: {block} is not allowed", "hint": "Try alternative representations..."}
    
    # Check for bypass attempts and reward
    parsed = urlparse(url)
    bypass_indicators = ["127.1", "0x7f", "2130706433", "017700000001", "[::1]", "localtest.me"]
    
    for indicator in bypass_indicators:
        if indicator in url.lower():
            return {
                "message": "Filter bypassed!",
                "flag": "FLAG{ssrf15_f1lt3r_byp4ss}",
                "content": "You successfully bypassed the blocklist!"
            }
    
    try:
        async with httpx.AsyncClient(timeout=5.0) as client:
            response = await client.get(url)
            return {"content": response.text[:500], "status": response.status_code}
    except Exception as e:
        return {"error": str(e)}

@router.get("/internal/{service}")
async def internal_service(service: str):
    """
    Internal service endpoint (should not be directly accessible)
    """
    if service in INTERNAL_SERVICES:
        return {"service": service, "data": INTERNAL_SERVICES[service]}
    else:
        return {"error": "Service not found", "available": list(INTERNAL_SERVICES.keys())}
