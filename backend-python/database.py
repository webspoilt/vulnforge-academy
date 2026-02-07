"""
Database setup with intentional vulnerabilities
"""

import aiosqlite
import os

DATABASE_URL = "vulnforge.db"

async def init_db():
    """Initialize database with sample data"""
    async with aiosqlite.connect(DATABASE_URL) as db:
        # Create users table
        await db.execute("""
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,  -- VULNERABLE: Stored in plain text
                email TEXT,
                role TEXT DEFAULT 'user',
                api_key TEXT,
                balance REAL DEFAULT 1000.0
            )
        """)
        
        # Create flags table
        await db.execute("""
            CREATE TABLE IF NOT EXISTS flags (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                level_id INTEGER UNIQUE NOT NULL,
                flag TEXT NOT NULL,
                hint TEXT
            )
        """)
        
        # Create products table (for IDOR)
        await db.execute("""
            CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                price REAL NOT NULL,
                description TEXT,
                owner_id INTEGER
            )
        """)
        
        # Create messages table (for stored XSS)
        await db.execute("""
            CREATE TABLE IF NOT EXISTS messages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                content TEXT NOT NULL,  -- VULNERABLE: Not sanitized
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        """)
        
        # Create files table (for file upload)
        await db.execute("""
            CREATE TABLE IF NOT EXISTS files (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                filename TEXT NOT NULL,
                filepath TEXT NOT NULL,
                uploaded_by INTEGER,
                uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        """)
        
        # Insert sample users (passwords in plain text - intentionally vulnerable)
        sample_users = [
            ("admin", "admin123", "admin@vulnforge.local", "admin", "ADMIN_API_KEY_12345"),
            ("user1", "password", "user1@vulnforge.local", "user", "USER1_API_KEY_ABCDE"),
            ("user2", "123456", "user2@vulnforge.local", "user", "USER2_API_KEY_FGHIJ"),
            ("guest", "guest", "guest@vulnforge.local", "guest", None),
        ]
        
        for user in sample_users:
            try:
                await db.execute(
                    "INSERT OR IGNORE INTO users (username, password, email, role, api_key) VALUES (?, ?, ?, ?, ?)",
                    user
                )
            except:
                pass
        
        # Insert flags for each level
        flags = [
            (1, "FLAG{sql1_b4s1c_1nj3ct10n}", "Try adding a single quote"),
            (2, "FLAG{sql2_un10n_s3l3ct}", "UNION SELECT is your friend"),
            (3, "FLAG{sql3_3rr0r_b4s3d}", "Make the database throw an error"),
            (4, "FLAG{xss4_r3fl3ct3d}", "Check the URL parameters"),
            (5, "FLAG{xss5_st0r3d_p3rs1st}", "What you post stays forever"),
            (6, "FLAG{xss6_d0m_m4n1pul4t10n}", "The DOM is your playground"),
            (7, "FLAG{idor7_us3r_pr0f1l3}", "Try changing the user ID"),
            (8, "FLAG{idor8_4p1_3xp0s3d}", "API endpoints reveal secrets"),
            (9, "FLAG{idor9_f1l3_4cc3ss}", "Path traversal perhaps?"),
            (10, "FLAG{auth10_brut3_f0rc3}", "Try common passwords"),
            (11, "FLAG{auth11_s3ss10n_h1j4ck}", "Sessions can be stolen"),
            (12, "FLAG{auth12_jwt_w34k}", "The secret is weak"),
            (13, "FLAG{ssrf13_1nt3rn4l}", "Access internal services"),
            (14, "FLAG{ssrf14_cl0ud_m3t4}", "169.254.169.254 knows secrets"),
            (15, "FLAG{ssrf15_f1lt3r_byp4ss}", "Bypass the filters"),
            (16, "FLAG{upload16_3xt3ns10n}", "Not all extensions are blocked"),
            (17, "FLAG{upload17_c0nt3nt_typ3}", "Content-Type can be spoofed"),
            (18, "FLAG{upload18_m4g1c_byt3s}", "File magic matters"),
            (19, "FLAG{rce19_c0mm4nd_1nj}", "Escape the command"),
            (20, "FLAG{nightmare20_ch41n_m4st3r}", "Chain them all together"),
        ]
        
        for flag in flags:
            try:
                await db.execute(
                    "INSERT OR IGNORE INTO flags (level_id, flag, hint) VALUES (?, ?, ?)",
                    flag
                )
            except:
                pass
        
        # Insert sample products
        products = [
            ("Secret Document", 9999.99, "Contains sensitive information", 1),
            ("User Manual", 0.00, "Public documentation", None),
            ("Admin Panel Access", 99999.99, "Full system access", 1),
        ]
        
        for product in products:
            try:
                await db.execute(
                    "INSERT OR IGNORE INTO products (name, price, description, owner_id) VALUES (?, ?, ?, ?)",
                    product
                )
            except:
                pass
        
        await db.commit()

async def get_db():
    """Get database connection"""
    return await aiosqlite.connect(DATABASE_URL)
