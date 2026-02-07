"""
File Upload vulnerable endpoints
WARNING: Contains intentional file upload vulnerabilities
"""

from fastapi import APIRouter, HTTPException, File, UploadFile, Form
from fastapi.responses import HTMLResponse, FileResponse
import os
import shutil

router = APIRouter()

UPLOAD_DIR = os.path.join(os.path.dirname(__file__), "..", "uploads")
os.makedirs(UPLOAD_DIR, exist_ok=True)

# Define dangerous extensions
BLOCKED_EXTENSIONS = [".php", ".exe", ".sh", ".bat"]  # Intentionally incomplete

@router.get("/level16", response_class=HTMLResponse)
async def upload_level16_page():
    """File upload page for Level 16"""
    return """
    <!DOCTYPE html>
    <html>
    <head>
        <title>File Upload - Level 16</title>
        <style>
            body { font-family: monospace; background: #0a0a0f; color: #00ff41; padding: 2rem; }
            input, button { padding: 0.5rem; margin: 0.5rem 0; }
            button { background: #00ff41; border: none; cursor: pointer; }
        </style>
    </head>
    <body>
        <h1>Level 16: Extension Bypass</h1>
        <p>Upload an image file (jpg, png, gif only)</p>
        <form action="/api/levels/upload/level16" method="POST" enctype="multipart/form-data">
            <input type="file" name="file" required><br>
            <button type="submit">Upload</button>
        </form>
        <p style="color: #888;">Hint: Some extensions are not blocked...</p>
    </body>
    </html>
    """

@router.post("/level16")
async def upload_level16(file: UploadFile = File(...)):
    """
    Level 16: Extension Bypass
    VULNERABILITY: Incomplete extension blocklist
    
    Blocked: .php, .exe, .sh, .bat
    Try: .php5, .phtml, .phar, .svg (with script)
    """
    filename = file.filename
    ext = os.path.splitext(filename)[1].lower()
    
    # VULNERABLE: Incomplete blocklist
    if ext in BLOCKED_EXTENSIONS:
        return {"error": f"Extension {ext} is not allowed"}
    
    # Check for bypass attempts
    dangerous_bypasses = [".php5", ".phtml", ".phar", ".asp", ".aspx", ".jsp", ".svg"]
    if ext in dangerous_bypasses:
        return {
            "message": "File uploaded!",
            "flag": "FLAG{upload16_3xt3ns10n}",
            "warning": "Dangerous file type uploaded successfully!"
        }
    
    # Save file
    filepath = os.path.join(UPLOAD_DIR, filename)
    with open(filepath, "wb") as f:
        content = await file.read()
        f.write(content)
    
    return {"message": "File uploaded", "filename": filename, "path": filepath}

@router.post("/level17")
async def upload_level17(file: UploadFile = File(...)):
    """
    Level 17: Content-Type Bypass
    VULNERABILITY: Only checks Content-Type header, not file content
    
    Try: Upload PHP file with Content-Type: image/jpeg
    """
    content_type = file.content_type
    
    # VULNERABLE: Only checks Content-Type header
    allowed_types = ["image/jpeg", "image/png", "image/gif"]
    if content_type not in allowed_types:
        return {"error": f"Content-Type {content_type} not allowed. Only images accepted."}
    
    # Read file content
    content = await file.read()
    
    # Check if it contains executable code despite being "image"
    if b"<?php" in content or b"<script" in content:
        return {
            "message": "File accepted as image!",
            "flag": "FLAG{upload17_c0nt3nt_typ3}",
            "warning": "Content-Type was spoofed - malicious code detected!"
        }
    
    return {"message": "Image uploaded", "content_type": content_type}

@router.post("/level18")
async def upload_level18(file: UploadFile = File(...)):
    """
    Level 18: Magic Bytes Bypass
    VULNERABILITY: Checks magic bytes but execution still possible
    
    Try: GIF89a; (GIF header) followed by PHP/JS code
    """
    content = await file.read()
    
    # Check magic bytes
    magic_signatures = {
        b'\x89PNG': 'PNG',
        b'\xff\xd8\xff': 'JPEG',
        b'GIF87a': 'GIF',
        b'GIF89a': 'GIF',
    }
    
    detected_type = None
    for magic, filetype in magic_signatures.items():
        if content.startswith(magic):
            detected_type = filetype
            break
    
    if not detected_type:
        return {"error": "Invalid file type. Must be a valid image (PNG, JPEG, or GIF)"}
    
    # Check for polyglot (valid image header + code)
    if b"<?php" in content or b"<script" in content or b"<%=" in content:
        return {
            "message": f"Valid {detected_type} uploaded!",
            "flag": "FLAG{upload18_m4g1c_byt3s}",
            "warning": "Polyglot file detected - valid image header with embedded code!"
        }
    
    return {"message": f"Valid {detected_type} image uploaded", "size": len(content)}

@router.get("/files")
async def list_uploaded_files():
    """List uploaded files"""
    try:
        files = os.listdir(UPLOAD_DIR)
        return {"files": files, "directory": UPLOAD_DIR}
    except Exception as e:
        return {"error": str(e)}
