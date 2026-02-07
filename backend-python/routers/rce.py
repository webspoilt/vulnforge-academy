"""
RCE vulnerable endpoints
WARNING: Contains intentional Remote Code Execution vulnerabilities
NEVER deploy on production servers!
"""

from fastapi import APIRouter, HTTPException, Query
import subprocess
import os

router = APIRouter()

@router.get("/level19")
async def rce_level19(host: str = Query(..., description="Host to ping")):
    """
    Level 19: Command Injection
    VULNERABILITY: Direct command execution with user input
    
    Try: 127.0.0.1; cat /etc/passwd
    Try on Windows: 127.0.0.1 & type C:\\Windows\\System.ini
    """
    # VULNERABLE: Direct command injection
    try:
        # Detect OS for appropriate command
        if os.name == 'nt':  # Windows
            command = f"ping -n 1 {host}"
        else:  # Linux/Mac
            command = f"ping -c 1 {host}"
        
        # VULNERABLE: shell=True allows command chaining
        result = subprocess.run(
            command,
            shell=True,
            capture_output=True,
            text=True,
            timeout=10
        )
        
        output = result.stdout + result.stderr
        
        # Check if command injection was successful
        injection_indicators = ["/bin/", "root:", "FLAG", "Windows", "System32", ";", "|", "&"]
        for indicator in injection_indicators:
            if indicator in host and len(output) > 100:
                return {
                    "command": command,
                    "output": output,
                    "flag": "FLAG{rce19_c0mm4nd_1nj}",
                    "warning": "Command injection successful!"
                }
        
        return {
            "command": command,
            "output": output,
            "hint": "Try injecting additional commands..."
        }
        
    except subprocess.TimeoutExpired:
        return {"error": "Command timed out"}
    except Exception as e:
        return {"error": str(e), "command": command}

@router.get("/level19/eval")
async def rce_eval(expression: str = Query(..., description="Math expression to evaluate")):
    """
    Level 19 Bonus: Python eval() injection
    VULNERABILITY: Using eval() on user input
    
    Try: __import__('os').system('whoami')
    """
    try:
        # VULNERABLE: eval() with user input
        result = eval(expression)
        return {"expression": expression, "result": str(result)}
    except Exception as e:
        return {"error": str(e), "expression": expression}

@router.get("/level20")
async def nightmare_level(
    action: str = Query(..., description="Action to perform"),
    target: str = Query("", description="Target parameter"),
    data: str = Query("", description="Additional data")
):
    """
    Level 20: The Final Boss - Multi-Vector Attack
    VULNERABILITY: Combines multiple vulnerabilities
    
    Chain SQLi + SSRF + RCE for full system compromise
    """
    result = {"action": action, "target": target, "data": data}
    
    if action == "query":
        # SQLi component
        result["hint"] = "This action is vulnerable to SQL injection"
        result["query"] = f"SELECT * FROM users WHERE username = '{target}'"
        
    elif action == "fetch":
        # SSRF component
        result["hint"] = "This action can fetch internal resources"
        result["url"] = target
        
    elif action == "process":
        # RCE component
        result["hint"] = "This action processes data unsafely"
        try:
            # VULNERABLE: eval on user data
            processed = eval(data) if data else None
            result["processed"] = str(processed)
        except:
            result["error"] = "Processing failed"
    
    elif action == "chain":
        # Full chain exploit
        if "sqli" in target and "ssrf" in target and "rce" in target:
            result["flag"] = "FLAG{nightmare20_ch41n_m4st3r}"
            result["message"] = "Congratulations! You've conquered VulnForge Academy!"
    
    return result
