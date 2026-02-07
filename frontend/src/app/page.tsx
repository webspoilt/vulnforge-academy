'use client'

import { useState, useEffect, useCallback } from 'react'
import { ParticleBackground } from '@/components/ParticleBackground'
import { Terminal } from '@/components/Terminal'
import { LevelCard } from '@/components/LevelCard'

interface Level {
  id: number
  title: string
  desc: string
  difficulty: 'easy' | 'medium' | 'hard' | 'nightmare'
  tags: string[]
  points: number
}

const defaultLevels: Level[] = [
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

export default function Home() {
  const [levels] = useState<Level[]>(defaultLevels)
  const [completedLevels, setCompletedLevels] = useState<number[]>([])
  const [terminalOutput, setTerminalOutput] = useState<string[]>([])
  const [isProcessing, setIsProcessing] = useState(false)
  const [progress, setProgress] = useState(0)

  // Load completed levels from localStorage
  useEffect(() => {
    try {
      const saved = localStorage.getItem('vulnforge_completed')
      if (saved) {
        const parsed = JSON.parse(saved)
        if (Array.isArray(parsed)) {
          setCompletedLevels(parsed)
        }
      }
    } catch (e) {
      console.error('Error loading progress:', e)
    }
  }, [])

  // Calculate progress
  useEffect(() => {
    setProgress((completedLevels.length / levels.length) * 100)
  }, [completedLevels, levels.length])

  // Save progress to localStorage
  useEffect(() => {
    try {
      localStorage.setItem('vulnforge_completed', JSON.stringify(completedLevels))
    } catch (e) {
      console.error('Error saving progress:', e)
    }
  }, [completedLevels])

  const startLevel = useCallback((level: Level) => {
    setIsProcessing(true)
    setTerminalOutput([
      `Initializing level ${level.id}: ${level.title}...`,
      `[INFO] Difficulty: ${level.difficulty.toUpperCase()}`,
      `[INFO] Points: ${level.points}`,
      `[LOAD] Loading vulnerable environment...`,
    ])

    // Simulate processing
    setTimeout(() => {
      setTerminalOutput(prev => [
        ...prev,
        `[OK] Environment ready`,
        `[ACTION] Navigate to /levels/${level.id} to begin`,
        ``,
        `Good luck, hacker! 🎯`
      ])
      setIsProcessing(false)
    }, 1500)
  }, [])

  return (
    <div className="min-h-screen bg-[#0a0a0f] text-[#e0e0e0] relative overflow-x-hidden">
      {/* Particle Background */}
      <ParticleBackground />

      {/* Hero Section */}
      <section className="relative z-10 min-h-screen flex flex-col items-center justify-center px-4 py-20">
        {/* Glitch Title */}
        <h1 className="glitch-text text-5xl md:text-7xl font-bold font-mono mb-4 text-center" data-text="VulnForge Academy">
          <span className="text-[#00ff41]">VulnForge</span> Academy
        </h1>

        <p className="text-[#888899] text-lg md:text-xl font-mono mb-8 text-center max-w-2xl">
          Master ethical hacking through 20 deliberately vulnerable levels
        </p>

        {/* CTA Buttons */}
        <div className="flex flex-col sm:flex-row gap-4 mb-12">
          <a
            href="#levels"
            className="px-8 py-4 bg-[#00ff41] text-[#0a0a0f] font-bold font-mono rounded-lg hover:bg-[#00d4ff] transition-colors duration-300 text-center"
          >
            Start Training →
          </a>
          <a
            href="https://github.com/webspoilt/vulnforge-academy"
            target="_blank"
            rel="noopener noreferrer"
            className="px-8 py-4 border-2 border-[#00ff41] text-[#00ff41] font-bold font-mono rounded-lg hover:bg-[#00ff41] hover:text-[#0a0a0f] transition-colors duration-300 text-center"
          >
            View on GitHub
          </a>
        </div>

        {/* Stats */}
        <div className="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-4xl">
          {[
            { value: '20', label: 'Levels' },
            { value: '4', label: 'Difficulties' },
            { value: '10K+', label: 'Points Available' },
            { value: '∞', label: 'Learning' }
          ].map(stat => (
            <div key={stat.label} className="text-center p-4 bg-[#12121a] rounded-xl border border-[#2a2a3a]">
              <div className="text-3xl font-bold text-[#00ff41] font-mono">{stat.value}</div>
              <div className="text-[#888899] text-sm">{stat.label}</div>
            </div>
          ))}
        </div>
      </section>

      {/* Legend Section */}
      <section className="py-8 px-4 bg-[#12121a] border-t border-b border-[#2a2a3a] relative z-10">
        <div className="max-w-4xl mx-auto flex flex-wrap justify-center gap-6">
          {[
            { color: 'dot-easy', label: 'Beginner (1-5)' },
            { color: 'dot-medium', label: 'Intermediate (6-10)' },
            { color: 'dot-hard', label: 'Advanced (11-15)' },
            { color: 'dot-nightmare', label: 'Nightmare (16-20)' }
          ].map(item => (
            <div key={item.label} className="flex items-center gap-2 font-mono text-sm">
              <div className={`legend-dot ${item.color} w-3 h-3 rounded-full`}></div>
              <span>{item.label}</span>
            </div>
          ))}
        </div>
      </section>

      {/* Progress Section */}
      <section className="py-8 px-4 bg-[#1a1a2e] border-t border-[#2a2a3a] relative z-10">
        <div className="max-w-4xl mx-auto">
          <div className="flex justify-between items-center mb-2">
            <span className="font-mono font-bold">Overall Progress</span>
            <span className="font-mono text-[#00ff41]">{Math.round(progress)}%</span>
          </div>
          <div className="w-full h-2 bg-[#12121a] rounded-full overflow-hidden">
            <div
              className="h-full bg-gradient-to-r from-[#00ff41] to-[#00d4ff] rounded-full transition-all duration-1000 shadow-[0_0_20px_rgba(0,255,65,0.3)] relative"
              style={{ width: `${progress}%` }}
            >
              <div className="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent animate-shimmer"></div>
            </div>
          </div>
        </div>
      </section>

      {/* Levels Section */}
      <section className="py-16 px-4 max-w-7xl mx-auto relative z-10" id="levels">
        <h2 className="section-title text-2xl font-mono mb-12 flex items-center gap-4">
          <span className="text-[#00ff41] animate-pulse">&gt;</span>
          Training Levels
        </h2>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          {levels.map((level, index) => {
            const isLocked = index > 0 && !completedLevels.includes(levels[index - 1].id)
            const isCompleted = completedLevels.includes(level.id)

            return (
              <LevelCard
                key={level.id}
                level={level}
                isLocked={isLocked}
                isCompleted={isCompleted}
                onClick={() => startLevel(level)}
              />
            )
          })}
        </div>
      </section>

      {/* Terminal Section */}
      <section className="py-16 px-4 bg-[#12121a] border-t border-[#2a2a3a] relative z-10">
        <Terminal
          output={terminalOutput}
          isProcessing={isProcessing}
        />
      </section>

      {/* Footer */}
      <footer className="py-12 px-4 text-center border-t border-[#2a2a3a] text-[#888899] relative z-10">
        <div className="flex justify-center gap-8 mb-6 flex-wrap">
          {[
            { label: 'Documentation', href: '#' },
            { label: 'GitHub', href: 'https://github.com/webspoilt/vulnforge-academy' },
            { label: 'Report Bug', href: 'https://github.com/webspoilt/vulnforge-academy/issues' },
          ].map(link => (
            <a
              key={link.label}
              href={link.href}
              target={link.href.startsWith('http') ? '_blank' : undefined}
              rel={link.href.startsWith('http') ? 'noopener noreferrer' : undefined}
              className="text-[#888899] no-underline hover:text-[#00ff41] transition-colors"
            >
              {link.label}
            </a>
          ))}
        </div>
        <p>© {new Date().getFullYear()} VulnForge Academy. Educational purposes only. Hack responsibly.</p>
      </footer>
    </div>
  )
}
