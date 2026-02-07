import { NextResponse } from 'next/server'

export async function GET() {
    const levels = [
        { id: 1, title: "Broken Authentication", desc: "Bypass login mechanisms using common credential stuffing techniques.", difficulty: "easy", tags: ["OWASP #1", "Auth"], points: 100 },
        { id: 2, title: "SQL Injection 101", desc: "Classic UNION-based SQLi in a vulnerable login form.", difficulty: "easy", tags: ["SQLi", "Database"], points: 150 },
        { id: 3, title: "XSS Reflected", desc: "Inject malicious scripts through URL parameters.", difficulty: "easy", tags: ["XSS", "JavaScript"], points: 150 },
        { id: 4, title: "IDOR Basics", desc: "Insecure Direct Object Reference in user profile endpoints.", difficulty: "easy", tags: ["IDOR", "API"], points: 200 },
        { id: 5, title: "Security Misconfig", desc: "Find and exploit default credentials and exposed configs.", difficulty: "easy", tags: ["Config", "Default Creds"], points: 200 },
        { id: 6, title: "Blind SQLi", desc: "Time-based blind SQL injection without error messages.", difficulty: "medium", tags: ["SQLi", "Blind"], points: 300 },
        { id: 7, title: "Stored XSS", desc: "Persistent cross-site scripting in comment sections.", difficulty: "medium", tags: ["XSS", "Stored"], points: 300 },
        { id: 8, title: "JWT Weakness", desc: "Crack weak JWT secrets and forge authentication tokens.", difficulty: "medium", tags: ["JWT", "Crypto"], points: 350 },
        { id: 9, title: "SSRF 101", desc: "Server-Side Request Forgery to access internal services.", difficulty: "medium", tags: ["SSRF", "Network"], points: 400 },
        { id: 10, title: "XXE Injection", desc: "XML External Entity attacks in file upload parsers.", difficulty: "medium", tags: ["XXE", "XML"], points: 400 },
        { id: 11, title: "Command Injection", desc: "OS command execution through unsanitized input fields.", difficulty: "hard", tags: ["RCE", "Command"], points: 500 },
        { id: 12, title: "Deserialization", desc: "Insecure deserialization of user-supplied data objects.", difficulty: "hard", tags: ["Deserialize", "RCE"], points: 550 },
        { id: 13, title: "Race Conditions", desc: "Time-of-check to time-of-use vulnerabilities.", difficulty: "hard", tags: ["Race", "Logic"], points: 550 },
        { id: 14, title: "NoSQL Injection", desc: "Bypass authentication in MongoDB-based applications.", difficulty: "hard", tags: ["NoSQL", "MongoDB"], points: 600 },
        { id: 15, title: "GraphQL Abuse", desc: "Introspection queries and batching attacks on GraphQL APIs.", difficulty: "hard", tags: ["GraphQL", "API"], points: 600 },
        { id: 16, title: "Advanced SSRF", desc: "Bypass filters and access cloud metadata services.", difficulty: "nightmare", tags: ["SSRF", "Cloud"], points: 800 },
        { id: 17, title: "Prototype Pollution", desc: "JavaScript prototype chain manipulation attacks.", difficulty: "nightmare", tags: ["JS", "Prototype"], points: 850 },
        { id: 18, title: "Polyglot Injection", desc: "Multi-context payload execution across different parsers.", difficulty: "nightmare", tags: ["Advanced", "Polyglot"], points: 900 },
        { id: 19, title: "Web Cache Poisoning", desc: "Poison CDN caches to serve malicious content.", difficulty: "nightmare", tags: ["Cache", "HTTP"], points: 950 },
        { id: 20, title: "The Final Boss", desc: "Chain multiple vulnerabilities for full system compromise.", difficulty: "nightmare", tags: ["Chain", "RCE", "All"], points: 1500 }
    ]

    return NextResponse.json({ levels })
}
