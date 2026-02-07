'use client'

import { useState, useEffect, useCallback } from 'react'
import { ParticleBackground } from '@/components/ParticleBackground'
import { Terminal } from '@/components/Terminal'
import { LevelCard } from '@/components/LevelCard'
import { API_BASE_URL, getLevels, checkHealth, getHint } from '@/lib/api'

interface Level {
  id: number
  name: string
  difficulty: string
  category: string
}

interface LevelDisplay {
  id: number
  title: string
  desc: string
  difficulty: 'easy' | 'medium' | 'hard' | 'nightmare'
  tags: string[]
  points: number
}

// Map API levels to display format
function mapLevelToDisplay(level: Level): LevelDisplay {
  const difficultyMap: Record<string, 'easy' | 'medium' | 'hard' | 'nightmare'> = {
    'easy': 'easy',
    'medium': 'medium',
    'hard': 'hard',
    'nightmare': 'nightmare'
  }

  const pointsMap: Record<string, number> = {
    'easy': 100,
    'medium': 300,
    'hard': 500,
    'nightmare': 1000
  }

  return {
    id: level.id,
    title: level.name,
    desc: `Complete the ${level.category.toUpperCase()} challenge`,
    difficulty: difficultyMap[level.difficulty] || 'easy',
    tags: [level.category.toUpperCase()],
    points: pointsMap[level.difficulty] || 100
  }
}

const defaultLevels: LevelDisplay[] = [
  { id: 1, title: "SQL Injection - Basic", desc: "Classic SQL injection in login form", difficulty: "easy", tags: ["SQLi"], points: 100 },
  { id: 2, title: "SQL Injection - UNION", desc: "UNION-based SQLi to extract data", difficulty: "easy", tags: ["SQLi"], points: 150 },
  { id: 3, title: "SQL Injection - Error", desc: "Error-based blind injection", difficulty: "easy", tags: ["SQLi"], points: 150 },
  { id: 4, title: "XSS - Reflected", desc: "Reflected cross-site scripting", difficulty: "easy", tags: ["XSS"], points: 100 },
  { id: 5, title: "XSS - Stored", desc: "Persistent XSS in message board", difficulty: "easy", tags: ["XSS"], points: 150 },
  { id: 6, title: "XSS - DOM", desc: "DOM-based XSS exploitation", difficulty: "easy", tags: ["XSS"], points: 150 },
  { id: 7, title: "IDOR - User Profile", desc: "Access other users' profiles", difficulty: "medium", tags: ["IDOR"], points: 300 },
  { id: 8, title: "IDOR - API", desc: "Predictable API object references", difficulty: "medium", tags: ["IDOR"], points: 300 },
  { id: 9, title: "IDOR - File Access", desc: "Path traversal file access", difficulty: "medium", tags: ["IDOR"], points: 350 },
  { id: 10, title: "Auth - Brute Force", desc: "Weak password authentication", difficulty: "medium", tags: ["Auth"], points: 300 },
  { id: 11, title: "Auth - Session", desc: "Session management flaws", difficulty: "medium", tags: ["Auth"], points: 350 },
  { id: 12, title: "Auth - JWT", desc: "Weak JWT implementation", difficulty: "medium", tags: ["JWT"], points: 400 },
  { id: 13, title: "SSRF - Basic", desc: "Server-side request forgery", difficulty: "hard", tags: ["SSRF"], points: 500 },
  { id: 14, title: "SSRF - Cloud Metadata", desc: "Access cloud instance metadata", difficulty: "hard", tags: ["SSRF"], points: 550 },
  { id: 15, title: "SSRF - Filter Bypass", desc: "Bypass URL filters", difficulty: "hard", tags: ["SSRF"], points: 600 },
  { id: 16, title: "Upload - Extension", desc: "Bypass extension filters", difficulty: "hard", tags: ["Upload"], points: 500 },
  { id: 17, title: "Upload - Content-Type", desc: "Spoof content-type headers", difficulty: "hard", tags: ["Upload"], points: 550 },
  { id: 18, title: "Upload - Magic Bytes", desc: "Polyglot file upload", difficulty: "hard", tags: ["Upload"], points: 600 },
  { id: 19, title: "RCE - Command Injection", desc: "OS command execution", difficulty: "nightmare", tags: ["RCE"], points: 1000 },
  { id: 20, title: "The Final Boss", desc: "Chain all vulnerabilities", difficulty: "nightmare", tags: ["Chain"], points: 1500 }
]

export default function Home() {
  const [levels, setLevels] = useState<LevelDisplay[]>(defaultLevels)
  const [completedLevels, setCompletedLevels] = useState<number[]>([])
  const [terminalOutput, setTerminalOutput] = useState<string[]>([
    `[SYSTEM] VulnForge Academy v1.0`,
    `[INFO] API: ${API_BASE_URL}`,
    `[STATUS] Checking backend connection...`,
  ])
  const [isProcessing, setIsProcessing] = useState(false)
  const [progress, setProgress] = useState(0)
  const [apiStatus, setApiStatus] = useState<'checking' | 'online' | 'offline'>('checking')

  // Check backend health on mount
  useEffect(() => {
    const checkBackend = async () => {
      try {
        await checkHealth()
        setApiStatus('online')
        setTerminalOutput(prev => [...prev, `[OK] Backend connected successfully!`, ``])

        // Fetch levels from API
        try {
          const data = await getLevels()
          if (data.levels && data.levels.length > 0) {
            setLevels(data.levels.map(mapLevelToDisplay))
            setTerminalOutput(prev => [...prev, `[OK] Loaded ${data.levels.length} levels from API`])
          }
        } catch {
          setTerminalOutput(prev => [...prev, `[WARN] Using default levels`])
        }
      } catch {
        setApiStatus('offline')
        setTerminalOutput(prev => [...prev, `[WARN] Backend offline - using demo mode`, ``])
      }
    }
    checkBackend()
  }, [])

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

  const startLevel = useCallback(async (level: LevelDisplay) => {
    setIsProcessing(true)
    setTerminalOutput([
      `[INIT] Loading Level ${level.id}: ${level.title}`,
      `[INFO] Difficulty: ${level.difficulty.toUpperCase()}`,
      `[INFO] Points: ${level.points}`,
      `[LOAD] Connecting to vulnerable environment...`,
    ])

    // Get hint from API
    if (apiStatus === 'online') {
      try {
        const hintData = await getHint(level.id)
        setTerminalOutput(prev => [
          ...prev,
          `[HINT] ${hintData.hint}`,
        ])
      } catch {
        // No hint available
      }
    }

    setTimeout(() => {
      const endpoint = getChallengeEndpoint(level.id)
      setTerminalOutput(prev => [
        ...prev,
        `[OK] Environment ready`,
        ``,
        `[TARGET] ${API_BASE_URL}${endpoint}`,
        ``,
        `Good luck, hacker! 🎯`
      ])
      setIsProcessing(false)
    }, 1500)
  }, [apiStatus])

  return (
    <div className="min-h-screen bg-[#0a0a0f] text-[#e0e0e0] relative overflow-x-hidden">
      {/* Particle Background */}
      <ParticleBackground />

      {/* API Status Badge */}
      <div className="fixed top-4 right-4 z-50">
        <div className={`px-3 py-1 rounded-full font-mono text-xs flex items-center gap-2 ${apiStatus === 'online' ? 'bg-green-900/50 text-green-400 border border-green-600' :
            apiStatus === 'offline' ? 'bg-red-900/50 text-red-400 border border-red-600' :
              'bg-yellow-900/50 text-yellow-400 border border-yellow-600'
          }`}>
          <span className={`w-2 h-2 rounded-full ${apiStatus === 'online' ? 'bg-green-400 animate-pulse' :
              apiStatus === 'offline' ? 'bg-red-400' :
                'bg-yellow-400 animate-pulse'
            }`}></span>
          {apiStatus === 'online' ? 'API Online' : apiStatus === 'offline' ? 'Demo Mode' : 'Connecting...'}
        </div>
      </div>

      {/* Hero Section */}
      <section className="relative z-10 min-h-screen flex flex-col items-center justify-center px-4 py-20">
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
            href={`${API_BASE_URL}/docs`}
            target="_blank"
            rel="noopener noreferrer"
            className="px-8 py-4 border-2 border-[#00ff41] text-[#00ff41] font-bold font-mono rounded-lg hover:bg-[#00ff41] hover:text-[#0a0a0f] transition-colors duration-300 text-center"
          >
            API Docs 📚
          </a>
        </div>

        {/* Stats */}
        <div className="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-4xl">
          {[
            { value: '20', label: 'Levels' },
            { value: '4', label: 'Difficulties' },
            { value: '10K+', label: 'Points Available' },
            { value: apiStatus === 'online' ? '🟢' : '🔴', label: 'API Status' }
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
            { color: 'dot-easy', label: 'Beginner (1-6)' },
            { color: 'dot-medium', label: 'Intermediate (7-12)' },
            { color: 'dot-hard', label: 'Advanced (13-18)' },
            { color: 'dot-nightmare', label: 'Nightmare (19-20)' }
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
            { label: 'API Docs', href: `${API_BASE_URL}/docs` },
            { label: 'GitHub', href: 'https://github.com/webspoilt/vulnforge-academy' },
            { label: 'Report Bug', href: 'https://github.com/webspoilt/vulnforge-academy/issues' },
          ].map(link => (
            <a
              key={link.label}
              href={link.href}
              target="_blank"
              rel="noopener noreferrer"
              className="text-[#888899] no-underline hover:text-[#00ff41] transition-colors"
            >
              {link.label}
            </a>
          ))}
        </div>
        <p>© {new Date().getFullYear()} VulnForge Academy. Educational purposes only.</p>
        <p className="text-xs mt-2 text-[#555566]">
          Frontend: vulnforge-academy.vercel.app | Backend: vulnforge-academy.onrender.com
        </p>
      </footer>
    </div>
  )
}

// Helper to get challenge endpoint for each level
function getChallengeEndpoint(levelId: number): string {
  const endpoints: Record<number, string> = {
    1: '/api/levels/sqli/level1?username=',
    2: '/api/levels/sqli/level2?id=',
    3: '/api/levels/sqli/level3?order=',
    4: '/api/levels/xss/level4?name=',
    5: '/api/levels/xss/level5',
    6: '/api/levels/xss/level6',
    7: '/api/levels/idor/level7/user/1',
    8: '/api/levels/idor/level8/order/1',
    9: '/api/levels/idor/level9/file?filename=',
    10: '/api/auth/login',
    11: '/api/auth/me',
    12: '/api/auth/login',
    13: '/api/levels/ssrf/level13?url=',
    14: '/api/levels/ssrf/level14?url=',
    15: '/api/levels/ssrf/level15?url=',
    16: '/api/levels/upload/level16',
    17: '/api/levels/upload/level17',
    18: '/api/levels/upload/level18',
    19: '/api/levels/rce/level19?host=',
    20: '/api/levels/rce/level20?action=',
  }
  return endpoints[levelId] || '/api/levels'
}
