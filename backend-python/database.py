"""
Database setup with PostgreSQL (Neon) or SQLite fallback
Includes intentional vulnerabilities for training
"""

import os
from sqlalchemy import create_engine, Column, Integer, String, Float, Text, DateTime, func
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy.orm import sessionmaker
from contextlib import contextmanager

# Database URL - uses PostgreSQL if available, SQLite as fallback
DATABASE_URL = os.getenv(
    "DATABASE_URL",
    "sqlite:///./vulnforge.db"
)

# Fix for Neon/Heroku PostgreSQL URLs
if DATABASE_URL.startswith("postgres://"):
    DATABASE_URL = DATABASE_URL.replace("postgres://", "postgresql://", 1)

# Create engine
if DATABASE_URL.startswith("sqlite"):
    engine = create_engine(DATABASE_URL, connect_args={"check_same_thread": False})
else:
    engine = create_engine(DATABASE_URL)

SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)
Base = declarative_base()

# Models
class User(Base):
    __tablename__ = "users"
    
    id = Column(Integer, primary_key=True, index=True)
    username = Column(String(100), unique=True, nullable=False)
    password = Column(String(255), nullable=False)  # VULNERABLE: Plain text
    email = Column(String(255))
    role = Column(String(50), default="user")
    api_key = Column(String(100))
    balance = Column(Float, default=1000.0)

class Flag(Base):
    __tablename__ = "flags"
    
    id = Column(Integer, primary_key=True, index=True)
    level_id = Column(Integer, unique=True, nullable=False)
    flag = Column(String(255), nullable=False)
    hint = Column(Text)

class Product(Base):
    __tablename__ = "products"
    
    id = Column(Integer, primary_key=True, index=True)
    name = Column(String(255), nullable=False)
    price = Column(Float, nullable=False)
    description = Column(Text)
    owner_id = Column(Integer)

class Message(Base):
    __tablename__ = "messages"
    
    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer)
    content = Column(Text, nullable=False)  # VULNERABLE: Not sanitized
    created_at = Column(DateTime, server_default=func.now())

class File(Base):
    __tablename__ = "files"
    
    id = Column(Integer, primary_key=True, index=True)
    filename = Column(String(255), nullable=False)
    filepath = Column(String(500), nullable=False)
    uploaded_by = Column(Integer)
    uploaded_at = Column(DateTime, server_default=func.now())

# Database dependency
def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()

@contextmanager
def get_db_context():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()

async def init_db():
    """Initialize database with tables and sample data"""
    # Create all tables
    Base.metadata.create_all(bind=engine)
    
    with get_db_context() as db:
        # Check if admin exists specifically
        admin_user = db.query(User).filter_by(username="admin").first()
        if not admin_user:
            print("[DB] Admin user missing. Creating default admin...")
            admin = User(
                username="admin", 
                password="admin123", 
                email="admin@vulnforge.local", 
                role="admin", 
                api_key="ADMIN_API_KEY_12345"
            )
            db.add(admin)
            db.commit()
            print("[DB] Admin user created.")
            
        # Check if data already exists for other users
        existing_user = db.query(User).filter(User.username != "admin").first()
        if existing_user:
            print("[DB] Database already initialized with sample users")
            return
        
        print("[DB] Initializing database with remaining sample data...")
        
        # Insert sample users (passwords in plain text - intentionally vulnerable)
        sample_users = [
            User(username="user1", password="password", email="user1@vulnforge.local", role="user", api_key="USER1_API_KEY_ABCDE"),
            User(username="user2", password="123456", email="user2@vulnforge.local", role="user", api_key="USER2_API_KEY_FGHIJ"),
            User(username="guest", password="guest", email="guest@vulnforge.local", role="guest", api_key=None),
        ]
        
        for user in sample_users:
            # Check if user exists before adding
            if not db.query(User).filter_by(username=user.username).first():
                db.add(user)
        
        # Insert flags for each level
        flags_data = [
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
        
        for level_id, flag, hint in flags_data:
            db.add(Flag(level_id=level_id, flag=flag, hint=hint))
        
        # Insert sample products
        products_data = [
            ("Secret Document", 9999.99, "Contains sensitive information", 1),
            ("User Manual", 0.00, "Public documentation", None),
            ("Admin Panel Access", 99999.99, "Full system access", 1),
        ]
        
        for name, price, desc, owner_id in products_data:
            db.add(Product(name=name, price=price, description=desc, owner_id=owner_id))
        
        db.commit()
        print("[DB] Database initialized successfully!")

# For backward compatibility with async code
async def get_db_async():
    """Async database getter - returns sync session"""
    db = SessionLocal()
    return db
