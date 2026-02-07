"""
XSS vulnerable endpoints
WARNING: Contains intentional Cross-Site Scripting vulnerabilities
"""

from fastapi import APIRouter, HTTPException, Query, Form, Request
from fastapi.responses import HTMLResponse
import aiosqlite
from typing import Optional

from database import get_db
from models import MessageCreate

router = APIRouter()

@router.get("/level4", response_class=HTMLResponse)
async def xss_level4(name: str = Query("Guest", description="Your name")):
    """
    Level 4: Reflected XSS
    VULNERABILITY: User input directly reflected in HTML without sanitization
    
    Try: <script>alert('XSS')</script>
    """
    # VULNERABLE: Direct reflection without encoding
    return f"""
    <!DOCTYPE html>
    <html>
    <head>
        <title>Welcome</title>
        <style>
            body {{ font-family: monospace; background: #0a0a0f; color: #00ff41; padding: 2rem; }}
        </style>
    </head>
    <body>
        <h1>Welcome, {name}!</h1>
        <p>Your name is displayed above. Try entering something interesting...</p>
        <form>
            <input type="text" name="name" placeholder="Enter your name" style="padding: 0.5rem;">
            <button type="submit" style="padding: 0.5rem; background: #00ff41; border: none;">Submit</button>
        </form>
        <!-- FLAG: FLAG{{xss4_r3fl3ct3d}} is hidden when XSS triggers -->
    </body>
    </html>
    """

@router.post("/level5/post")
async def xss_level5_post(message: MessageCreate):
    """
    Level 5: Stored XSS - Post a message
    VULNERABILITY: Messages stored without sanitization
    """
    db = await get_db()
    try:
        # VULNERABLE: No sanitization before storing
        await db.execute(
            "INSERT INTO messages (content) VALUES (?)",
            (message.content,)
        )
        await db.commit()
        return {"status": "Message posted", "content": message.content}
    finally:
        await db.close()

@router.get("/level5", response_class=HTMLResponse)
async def xss_level5_view():
    """
    Level 5: Stored XSS - View messages
    VULNERABILITY: Messages displayed without encoding
    
    Post a message with XSS payload first
    """
    db = await get_db()
    try:
        cursor = await db.execute("SELECT id, content, created_at FROM messages ORDER BY id DESC LIMIT 20")
        messages = await cursor.fetchall()
        
        messages_html = ""
        for msg in messages:
            # VULNERABLE: No HTML encoding
            messages_html += f'<div class="message"><strong>#{msg[0]}</strong>: {msg[1]} <small>({msg[2]})</small></div>'
        
        return f"""
        <!DOCTYPE html>
        <html>
        <head>
            <title>Message Board</title>
            <style>
                body {{ font-family: monospace; background: #0a0a0f; color: #00ff41; padding: 2rem; }}
                .message {{ background: #12121a; padding: 1rem; margin: 0.5rem 0; border-radius: 8px; }}
                input, button {{ padding: 0.5rem; margin: 0.25rem; }}
                button {{ background: #00ff41; border: none; cursor: pointer; }}
            </style>
        </head>
        <body>
            <h1>Message Board</h1>
            <form action="/api/levels/xss/level5/post" method="POST" onsubmit="postMessage(event)">
                <input type="text" id="content" placeholder="Enter message" style="width: 300px;">
                <button type="submit">Post</button>
            </form>
            <h2>Messages:</h2>
            <div id="messages">{messages_html}</div>
            <script>
                async function postMessage(e) {{
                    e.preventDefault();
                    const content = document.getElementById('content').value;
                    await fetch('/api/levels/xss/level5/post', {{
                        method: 'POST',
                        headers: {{'Content-Type': 'application/json'}},
                        body: JSON.stringify({{content}})
                    }});
                    location.reload();
                }}
            </script>
        </body>
        </html>
        """
    finally:
        await db.close()

@router.get("/level6", response_class=HTMLResponse)
async def xss_level6():
    """
    Level 6: DOM-based XSS
    VULNERABILITY: JavaScript uses URL hash/params unsafely
    
    Try: #<img src=x onerror=alert('XSS')>
    """
    return """
    <!DOCTYPE html>
    <html>
    <head>
        <title>Search Results</title>
        <style>
            body { font-family: monospace; background: #0a0a0f; color: #00ff41; padding: 2rem; }
            #results { background: #12121a; padding: 1rem; border-radius: 8px; margin-top: 1rem; }
        </style>
    </head>
    <body>
        <h1>Search</h1>
        <input type="text" id="search" placeholder="Search..." onkeyup="search()" style="padding: 0.5rem; width: 300px;">
        <div id="results"></div>
        
        <script>
            // VULNERABLE: DOM XSS through innerHTML
            function search() {
                const query = document.getElementById('search').value;
                document.getElementById('results').innerHTML = 'Searching for: ' + query;
            }
            
            // VULNERABLE: Hash-based DOM XSS
            window.onload = function() {
                if (window.location.hash) {
                    const hash = decodeURIComponent(window.location.hash.substring(1));
                    document.getElementById('results').innerHTML = 'Results for: ' + hash;
                }
            }
        </script>
        
        <p style="margin-top: 2rem; color: #888;">
            Hint: Check the URL hash and inspect how search results are displayed.
            <br>The flag is: FLAG{xss6_d0m_m4n1pul4t10n}
        </p>
    </body>
    </html>
    """
