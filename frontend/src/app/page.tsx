'use client';

import Link from 'next/link';

export default function LandingPage() {
    return (
        <>
            <style jsx global>{`
        :root {
            --primary: #00ff41;
            --primary-dark: #00cc33;
            --secondary: #ff0055;
            --accent: #00d4ff;
            --warning: #ffcc00;
            --bg-dark: #0a0a0f;
            --bg-card: #12121a;
            --bg-elevated: #1a1a2e;
            --text-primary: #e0e0e0;
            --text-secondary: #888899;
            --border: #2a2a3a;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-dark);
            color: var(--text-primary);
            line-height: 1.6;
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 8rem 5% 4rem;
            background: linear-gradient(135deg, rgba(0,255,65,0.05) 0%, transparent 50%);
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(rgba(0, 255, 65, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 255, 65, 0.03) 1px, transparent 1px);
            background-size: 60px 60px;
            pointer-events: none;
        }

        .hero-stats {
            display: flex;
            gap: 3rem;
            margin-top: 3rem;
        }

        .hero-stat-value {
            font-family: 'JetBrains Mono', monospace;
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary);
        }

        .section {
            padding: 6rem 5%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .info-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 2rem;
            transition: all 0.3s;
        }

        .info-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }

        .info-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
        }

        .steps-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            max-width: 1000px;
            margin: 0 auto;
        }

        .step-number {
            width: 50px;
            height: 50px;
            background: var(--bg-elevated);
            border: 2px solid var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary);
            margin: 0 auto 1rem;
        }

        .curriculum-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .curriculum-item {
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s;
        }

        .curriculum-item:hover {
            border-color: var(--accent);
            transform: translateX(10px);
        }

        @media (max-width: 768px) {
            .hero-stats { flex-direction: column; gap: 1.5rem; }
            .steps-grid { grid-template-columns: 1fr; }
        }
      `}</style>

            {/* Navigation */}
            <nav className="fixed top-0 w-full p-4 px-[5%] bg-[rgba(10,10,15,0.95)] backdrop-blur-md border-b border-[#2a2a3a] z-[1000] flex justify-between items-center">
                <a href="#" className="font-mono text-2xl font-bold text-[#00ff41] no-underline flex items-center gap-2">
                    <span>⚡</span>
                    VulnForge Academy
                </a>
                <ul className="hidden md:flex gap-8 list-none">
                    <li><a href="#what-is-it" className="text-[#888899] hover:text-[#00ff41] transition-colors">What is it?</a></li>
                    <li><a href="#curriculum" className="text-[#888899] hover:text-[#00ff41] transition-colors">Curriculum</a></li>
                    <li><a href="#how-it-works" className="text-[#888899] hover:text-[#00ff41] transition-colors">How it Works</a></li>
                    <li><Link href="/docs" className="text-[#888899] hover:text-[#00ff41] transition-colors">Docs</Link></li>
                </ul>
                <div className="flex gap-4">
                    <a href="/login.html" className="hidden md:block px-6 py-3 border border-[#2a2a3a] rounded-lg text-[#e0e0e0] hover:border-[#00ff41] hover:text-[#00ff41] transition-colors">Login</a>
                    <a href="/invite.html" className="px-6 py-3 bg-[#00ff41] text-[#0a0a0f] font-bold rounded-lg hover:bg-[#00cc33] hover:shadow-[0_0_30px_rgba(0,255,65,0.4)] transition-all">Start Hacking</a>
                </div>
            </nav>

            {/* Hero */}
            <section className="hero">
                <div className="relative z-10 max-w-[700px]">
                    <h1 className="text-5xl md:text-6xl font-extrabold leading-tight mb-6 bg-gradient-to-br from-[#00ff41] to-[#00d4ff] bg-clip-text text-transparent">
                        Learn Cybersecurity by Breaking Things
                    </h1>
                    <p className="text-xl text-[#888899] mb-8 max-w-[600px]">
                        VulnForge Academy is a safe, legal training ground where you hack deliberately vulnerable applications to master security. From SQL injection to advanced SSRF—learn by doing.
                    </p>
                    <div className="flex gap-4">
                        <a href="/invite.html" className="px-8 py-4 bg-[#00ff41] text-[#0a0a0f] text-lg font-bold rounded-lg hover:bg-[#00cc33] transition-all">
                            🚀 Hack The Invite Code
                        </a>
                        <a href="#what-is-it" className="px-8 py-4 border border-[#2a2a3a] text-[#e0e0e0] text-lg font-bold rounded-lg hover:border-[#00ff41] hover:text-[#00ff41] transition-all">
                            📖 Learn More
                        </a>
                    </div>

                    <div className="hero-stats">
                        <div className="text-center">
                            <div className="hero-stat-value">20</div>
                            <div className="text-[#888899] text-sm uppercase tracking-wider">Hands-on Labs</div>
                        </div>
                        <div className="text-center">
                            <div className="hero-stat-value">OWASP</div>
                            <div className="text-[#888899] text-sm uppercase tracking-wider">Top 10 Covered</div>
                        </div>
                        <div className="text-center">
                            <div className="hero-stat-value">0x1337</div>
                            <div className="text-[#888899] text-sm uppercase tracking-wider">Active Learners</div>
                        </div>
                    </div>
                </div>
            </section>

            {/* What Is It */}
            <section className="section" id="what-is-it">
                <h2 className="text-4xl font-extrabold text-center mb-4">What is VulnForge Academy?</h2>
                <p className="text-center text-[#888899] text-xl mb-16">A comprehensive platform designed to transform beginners into security professionals</p>

                <div className="info-grid">
                    <div className="info-card">
                        <div className="info-icon">🛡️</div>
                        <h3 className="text-xl font-bold mb-4">Safe Environment</h3>
                        <p className="text-[#888899] leading-relaxed">All vulnerabilities are simulated in isolated containers. You can exploit freely without legal consequences or harming real systems.</p>
                    </div>

                    <div className="info-card">
                        <div className="info-icon">🎯</div>
                        <h3 className="text-xl font-bold mb-4">CTF-Style Learning</h3>
                        <p className="text-[#888899] leading-relaxed">Each level contains hidden flags. Your goal: find and exploit vulnerabilities to capture them. Gamified progression makes learning addictive.</p>
                    </div>

                    <div className="info-card">
                        <div className="info-icon">📈</div>
                        <h3 className="text-xl font-bold mb-4">Skill Progression</h3>
                        <p className="text-[#888899] leading-relaxed">Structured curriculum from Beginner (Basic SQLi) to Nightmare (Advanced chaining). Track your growth with detailed analytics.</p>
                    </div>
                </div>
            </section>

            {/* How It Works */}
            <section className="section bg-[#12121a] border-y border-[#2a2a3a]" id="how-it-works">
                <h2 className="text-4xl font-extrabold text-center mb-4">How It Works</h2>
                <p className="text-center text-[#888899] text-xl mb-16">Your journey from novice to security expert</p>

                <div className="steps-grid">
                    <div className="text-center relative">
                        <div className="step-number">1</div>
                        <h4 className="font-bold mb-2">Crack the Invite</h4>
                        <p className="text-[#888899] text-sm">Prove your potential by hacking our invite code generator to register.</p>
                    </div>
                    <div className="text-center relative">
                        <div className="step-number">2</div>
                        <h4 className="font-bold mb-2">Learn</h4>
                        <p className="text-[#888899] text-sm">Study theory, read about vulnerability types, and understand defenses</p>
                    </div>
                    <div className="text-center relative">
                        <div className="step-number">3</div>
                        <h4 className="font-bold mb-2">Practice</h4>
                        <p className="text-[#888899] text-sm">Exploit 20 levels covering OWASP Top 10 and advanced techniques</p>
                    </div>
                    <div className="text-center relative">
                        <div className="step-number">4</div>
                        <h4 className="font-bold mb-2">Master</h4>
                        <p className="text-[#888899] text-sm">Capture flags, earn points, get certified, and join the community</p>
                    </div>
                </div>
            </section>

            {/* Curriculum */}
            <section className="section" id="curriculum">
                <h2 className="text-4xl font-extrabold text-center mb-4">What You'll Learn</h2>
                <p className="text-center text-[#888899] text-xl mb-16">Comprehensive coverage of modern web vulnerabilities</p>

                <div className="curriculum-grid">
                    <div className="curriculum-item">
                        <div className="text-3xl">💉</div>
                        <div>
                            <h4 className="font-bold mb-1">SQL Injection</h4>
                            <span className="text-[#888899] text-sm">Union-based, Blind, Time-based, NoSQL</span>
                        </div>
                    </div>
                    <div className="curriculum-item">
                        <div className="text-3xl">📝</div>
                        <div>
                            <h4 className="font-bold mb-1">Cross-Site Scripting</h4>
                            <span className="text-[#888899] text-sm">Reflected, Stored, DOM-based, CSP bypass</span>
                        </div>
                    </div>
                    <div className="curriculum-item">
                        <div className="text-3xl">🔓</div>
                        <div>
                            <h4 className="font-bold mb-1">Authentication</h4>
                            <span className="text-[#888899] text-sm">JWT attacks, Session hijacking, OAuth flaws</span>
                        </div>
                    </div>
                    <div className="curriculum-item">
                        <div className="text-3xl">🌐</div>
                        <div>
                            <h4 className="font-bold mb-1">Server-Side Attacks</h4>
                            <span className="text-[#888899] text-sm">SSRF, RCE, XXE, Deserialization</span>
                        </div>
                    </div>
                </div>
            </section>

            {/* CTA */}
            <section className="py-24 px-[5%] text-center bg-gradient-to-br from-[rgba(0,255,65,0.05)] to-[rgba(0,212,255,0.05)]">
                <h2 className="text-4xl font-extrabold mb-4">Ready to Become a Security Expert?</h2>
                <p className="text-[#888899] text-lg max-w-[600px] mx-auto mb-8">
                    Join thousands of learners mastering cybersecurity through hands-on practice.
                    No credit card required. No legal risks. Just pure learning.
                </p>
                <div className="flex justify-center gap-4 flex-wrap">
                    <a href="/invite.html" className="px-10 py-4 bg-[#00ff41] text-[#0a0a0f] text-lg font-bold rounded-lg hover:bg-[#00cc33] transition-all">
                        🎯 Start Hacking
                    </a>
                    <a href="#curriculum" className="px-10 py-4 border border-[#2a2a3a] text-[#e0e0e0] text-lg font-bold rounded-lg hover:border-[#00ff41] hover:text-[#00ff41] transition-all">
                        📚 Browse Curriculum
                    </a>
                </div>
            </section>

            {/* Footer */}
            <footer className="py-12 px-[5%] text-center border-t border-[#2a2a3a] text-[#888899]">
                <div className="flex justify-center gap-8 mb-8 flex-wrap">
                    <a href="/docs" className="text-[#888899] hover:text-[#00ff41] transition-colors">Documentation</a>
                    <a href="https://github.com/webspoilt/vulnforge-academy" className="text-[#888899] hover:text-[#00ff41] transition-colors">GitHub</a>
                    <a href="#" className="text-[#888899] hover:text-[#00ff41] transition-colors">Discord</a>
                    <a href="#" className="text-[#888899] hover:text-[#00ff41] transition-colors">Report Bug</a>
                </div>
                <p>© 2024 VulnForge Academy. Open source security education.</p>
                <p className="mt-2 text-sm">
                    ⚠️ This platform is for educational purposes only. Always practice responsible disclosure.
                </p>
            </footer>
        </>
    );
}
