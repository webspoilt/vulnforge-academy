'use client'

import { Shield, Lock, Target, Trophy, BookOpen, Flame, Zap, AlertTriangle, ChevronRight, Github, ExternalLink, Terminal, Database, Code2, Network, FileText } from 'lucide-react'
import { useState, useEffect } from 'react'

const levels = [
  { id: 1, difficulty: 'Beginner', vulnerability: 'SQL Injection - Basic', color: 'from-green-500 to-emerald-600', icon: Database },
  { id: 2, difficulty: 'Beginner', vulnerability: 'SQL Injection - UNION', color: 'from-green-500 to-emerald-600', icon: Database },
  { id: 3, difficulty: 'Beginner', vulnerability: 'SQL Injection - Error-based', color: 'from-green-500 to-emerald-600', icon: Database },
  { id: 4, difficulty: 'Beginner', vulnerability: 'XSS - Stored', color: 'from-green-500 to-emerald-600', icon: Code2 },
  { id: 5, difficulty: 'Beginner', vulnerability: 'XSS - Reflected', color: 'from-green-500 to-emerald-600', icon: Code2 },
  { id: 6, difficulty: 'Beginner', vulnerability: 'XSS - DOM', color: 'from-green-500 to-emerald-600', icon: Code2 },
  { id: 7, difficulty: 'Easy', vulnerability: 'IDOR - Parameter Manipulation', color: 'from-yellow-500 to-orange-500', icon: Lock },
  { id: 8, difficulty: 'Easy', vulnerability: 'IDOR - ID Prediction', color: 'from-yellow-500 to-orange-500', icon: Lock },
  { id: 9, difficulty: 'Easy', vulnerability: 'IDOR - Access Control', color: 'from-yellow-500 to-orange-500', icon: Lock },
  { id: 10, difficulty: 'Easy', vulnerability: 'Authentication - Brute Force', color: 'from-yellow-500 to-orange-500', icon: Shield },
  { id: 11, difficulty: 'Easy', vulnerability: 'Authentication - Session Flaws', color: 'from-yellow-500 to-orange-500', icon: Shield },
  { id: 12, difficulty: 'Easy', vulnerability: 'Authentication - Weak Passwords', color: 'from-yellow-500 to-orange-500', icon: Shield },
  { id: 13, difficulty: 'Medium', vulnerability: 'SSRF - Internal Service Access', color: 'from-orange-500 to-red-500', icon: Network },
  { id: 14, difficulty: 'Medium', vulnerability: 'SSRF - Cloud Metadata', color: 'from-orange-500 to-red-500', icon: Network },
  { id: 15, difficulty: 'Medium', vulnerability: 'SSRF - Local File Access', color: 'from-orange-500 to-red-500', icon: Network },
  { id: 16, difficulty: 'Medium', vulnerability: 'File Upload - Bypass Validation', color: 'from-orange-500 to-red-500', icon: FileText },
  { id: 17, difficulty: 'Medium', vulnerability: 'File Upload - Malicious Files', color: 'from-orange-500 to-red-500', icon: FileText },
  { id: 18, difficulty: 'Medium', vulnerability: 'File Upload - Path Traversal', color: 'from-orange-500 to-red-500', icon: FileText },
  { id: 19, difficulty: 'Hard', vulnerability: 'RCE - Command Injection', color: 'from-red-600 to-rose-700', icon: Terminal },
  { id: 20, difficulty: 'Nightmare', vulnerability: 'Multi-vector Chain Exploitation', color: 'from-purple-700 to-pink-700', icon: Zap },
]

const features = [
  {
    icon: Trophy,
    title: 'CTF-Style Flags',
    description: 'Capture flags to progress through levels and prove your hacking skills',
    color: 'text-amber-500',
    bgColor: 'bg-amber-500/10'
  },
  {
    icon: BookOpen,
    title: 'Learning Resources',
    description: 'Comprehensive educational content for each vulnerability type',
    color: 'text-emerald-500',
    bgColor: 'bg-emerald-500/10'
  },
  {
    icon: Target,
    title: 'Progress Tracking',
    description: 'Save your progress across sessions and continue where you left off',
    color: 'text-orange-500',
    bgColor: 'bg-orange-500/10'
  },
  {
    icon: Zap,
    title: 'Leaderboard',
    description: 'Compete with hackers worldwide and climb the rankings',
    color: 'text-pink-500',
    bgColor: 'bg-pink-500/10'
  },
  {
    icon: Flame,
    title: '20 Progressive Levels',
    description: 'From beginner to nightmare difficulty, constantly challenging',
    color: 'text-red-500',
    bgColor: 'bg-red-500/10'
  },
  {
    icon: Shield,
    title: 'OWASP Top 10 Coverage',
    description: 'Master the most critical web application security risks',
    color: 'text-purple-500',
    bgColor: 'bg-purple-500/10'
  }
]

export default function Home() {
  const [scrolled, setScrolled] = useState(false)
  const [activeLevel, setActiveLevel] = useState<number | null>(null)

  useEffect(() => {
    const handleScroll = () => setScrolled(window.scrollY > 50)
    window.addEventListener('scroll', handleScroll)
    return () => window.removeEventListener('scroll', handleScroll)
  }, [])

  return (
    <div className="min-h-screen flex flex-col bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950">
      {/* Animated Background */}
      <div className="fixed inset-0 overflow-hidden pointer-events-none">
        <div className="absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(255,0,110,0.08),transparent_50%)]" />
        <div className="absolute inset-0 bg-[radial-gradient(circle_at_70%_80%,rgba(255,77,0,0.08),transparent_50%)]" />
        <div className="absolute inset-0 bg-[radial-gradient(circle_at_50%_50%,rgba(255,215,0,0.05),transparent_50%)]" />
      </div>

      {/* Navigation */}
      <nav className={`fixed top-0 left-0 right-0 z-50 transition-all duration-300 ${
        scrolled ? 'bg-slate-950/80 backdrop-blur-xl border-b border-slate-800/50' : ''
      }`}>
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-between h-16">
            <div className="flex items-center gap-3">
              <div className="relative">
                <Shield className="w-8 h-8 text-pink-500" />
                <Lock className="w-4 h-4 text-amber-500 absolute -bottom-1 -right-1" />
              </div>
              <span className="text-xl font-bold bg-gradient-to-r from-pink-500 via-orange-500 to-amber-500 bg-clip-text text-transparent">
                VulnForge Academy
              </span>
            </div>
            <div className="flex items-center gap-4">
              <a
                href="https://github.com/webspoilt/vulnforge-academy"
                target="_blank"
                rel="noopener noreferrer"
                className="flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-800/50 hover:bg-slate-700/50 border border-slate-700/50 hover:border-pink-500/50 transition-all duration-300 text-sm font-medium"
              >
                <Github className="w-4 h-4" />
                <span className="hidden sm:inline">View on GitHub</span>
              </a>
            </div>
          </div>
        </div>
      </nav>

      {/* Hero Section */}
      <section className="relative pt-32 pb-20 px-4 sm:px-6 lg:px-8 overflow-hidden">
        <div className="max-w-7xl mx-auto">
          <div className="text-center">
            <div className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gradient-to-r from-pink-500/10 via-orange-500/10 to-amber-500/10 border border-slate-700/50 mb-8">
              <Flame className="w-4 h-4 text-orange-500" />
              <span className="text-sm font-medium text-slate-300">Learn Hacking by Hacking - Ethically</span>
            </div>

            <h1 className="text-5xl sm:text-6xl lg:text-7xl font-bold mb-6">
              <span className="block mb-2">
                <span className="bg-gradient-to-r from-pink-500 via-orange-500 to-amber-500 bg-clip-text text-transparent">
                  VulnForge
                </span>
                <span className="text-slate-100"> Academy</span>
              </span>
            </h1>

            <p className="text-lg sm:text-xl text-slate-400 max-w-3xl mx-auto mb-8 leading-relaxed">
              A deliberately vulnerable web application designed for cybersecurity training.
              Master <span className="text-pink-400 font-semibold">20 progressive levels</span> covering
              OWASP Top 10, SQL Injection, XSS, IDOR, SSRF, RCE & more.
            </p>

            <div className="flex flex-col sm:flex-row items-center justify-center gap-4 mb-12">
              <a
                href="https://github.com/webspoilt/vulnforge-academy"
                target="_blank"
                rel="noopener noreferrer"
                className="group flex items-center gap-2 px-8 py-4 rounded-xl bg-gradient-to-r from-pink-600 to-orange-600 hover:from-pink-500 hover:to-orange-500 text-white font-semibold shadow-lg shadow-pink-500/25 hover:shadow-pink-500/40 transition-all duration-300"
              >
                Get Started
                <ChevronRight className="w-5 h-5 group-hover:translate-x-1 transition-transform duration-300" />
              </a>
              <a
                href="#levels"
                className="flex items-center gap-2 px-8 py-4 rounded-xl bg-slate-800/50 hover:bg-slate-700/50 border border-slate-700/50 hover:border-orange-500/50 text-slate-300 hover:text-white font-semibold transition-all duration-300"
              >
                Explore Levels
              </a>
            </div>

            {/* Stats */}
            <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 max-w-4xl mx-auto">
              {[
                { value: '20', label: 'Progressive Levels' },
                { value: '4', label: 'Difficulty Tiers' },
                { value: '10+', label: 'Vulnerability Types' },
                { value: '∞', label: 'Learning Potential' }
              ].map((stat) => (
                <div key={stat.label} className="p-6 rounded-2xl bg-slate-900/50 border border-slate-800/50 hover:border-pink-500/30 transition-all duration-300">
                  <div className="text-3xl sm:text-4xl font-bold bg-gradient-to-r from-pink-500 to-orange-500 bg-clip-text text-transparent mb-1">
                    {stat.value}
                  </div>
                  <div className="text-sm text-slate-400">{stat.label}</div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section className="relative py-20 px-4 sm:px-6 lg:px-8">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-16">
            <h2 className="text-3xl sm:text-4xl font-bold text-slate-100 mb-4">
              Powerful Learning Features
            </h2>
            <p className="text-slate-400 max-w-2xl mx-auto">
              Everything you need to master web application security through hands-on practice
            </p>
          </div>

          <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {features.map((feature, index) => (
              <div
                key={feature.title}
                className="group p-6 rounded-2xl bg-slate-900/50 border border-slate-800/50 hover:border-slate-700/50 transition-all duration-300 hover:transform hover:-translate-y-1"
                style={{ animationDelay: `${index * 100}ms` }}
              >
                <div className={`inline-flex p-3 rounded-xl ${feature.bgColor} mb-4`}>
                  <feature.icon className={`w-6 h-6 ${feature.color}`} />
                </div>
                <h3 className="text-xl font-semibold text-slate-100 mb-2">
                  {feature.title}
                </h3>
                <p className="text-slate-400 leading-relaxed">
                  {feature.description}
                </p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Levels Section */}
      <section id="levels" className="relative py-20 px-4 sm:px-6 lg:px-8">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-16">
            <h2 className="text-3xl sm:text-4xl font-bold text-slate-100 mb-4">
              20 Progressive Levels
            </h2>
            <p className="text-slate-400 max-w-2xl mx-auto">
              From beginner to nightmare - master every vulnerability type
            </p>
          </div>

          <div className="grid sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            {levels.map((level) => (
              <div
                key={level.id}
                className={`group relative p-6 rounded-2xl bg-gradient-to-br ${level.color} hover:shadow-2xl transition-all duration-300 hover:transform hover:-translate-y-1 cursor-pointer overflow-hidden`}
                onMouseEnter={() => setActiveLevel(level.id)}
                onMouseLeave={() => setActiveLevel(null)}
              >
                <div className="absolute inset-0 bg-gradient-to-br from-black/0 to-black/20 group-hover:from-black/0 group-hover:to-black/10 transition-all duration-300" />
                <div className="relative">
                  <div className="flex items-start justify-between mb-4">
                    <div className="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm">
                      <level.icon className="w-6 h-6 text-white" />
                    </div>
                    <div className="px-3 py-1 rounded-full bg-white/20 backdrop-blur-sm text-xs font-medium text-white">
                      {level.difficulty}
                    </div>
                  </div>

                  <div className="text-3xl font-bold text-white mb-2">
                    #{level.id.toString().padStart(2, '0')}
                  </div>

                  <h3 className="text-white font-medium text-sm leading-snug mb-2">
                    {level.vulnerability}
                  </h3>

                  <div className="flex items-center gap-2 text-white/80 text-xs">
                    <Target className="w-3 h-3" />
                    <span>CTF Challenge</span>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Difficulty Breakdown */}
      <section className="relative py-20 px-4 sm:px-6 lg:px-8">
        <div className="max-w-7xl mx-auto">
          <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            {[
              {
                tier: 'Beginner',
                levels: '1-6',
                description: 'Perfect for newcomers. Learn basic SQL Injection and XSS fundamentals.',
                color: 'from-green-500 to-emerald-600',
                icon: Target
              },
              {
                tier: 'Easy',
                levels: '7-12',
                description: 'Build on your skills with IDOR and authentication attacks.',
                color: 'from-yellow-500 to-orange-500',
                icon: Zap
              },
              {
                tier: 'Medium',
                levels: '13-18',
                description: 'Challenge yourself with SSRF and advanced file upload exploits.',
                color: 'from-orange-500 to-red-500',
                icon: Flame
              },
              {
                tier: 'Hard',
                levels: '19-20',
                description: 'Face the ultimate challenge: RCE and multi-vector chain exploitation.',
                color: 'from-purple-700 to-pink-700',
                icon: AlertTriangle
              }
            ].map((tier) => (
              <div
                key={tier.tier}
                className="p-6 rounded-2xl bg-slate-900/50 border border-slate-800/50 hover:border-slate-700/50 transition-all duration-300"
              >
                <div className={`inline-flex p-3 rounded-xl bg-gradient-to-br ${tier.color} mb-4`}>
                  <tier.icon className="w-6 h-6 text-white" />
                </div>
                <div className="flex items-center gap-3 mb-3">
                  <h3 className="text-xl font-bold text-slate-100">{tier.tier}</h3>
                  <span className="px-3 py-1 rounded-full bg-slate-800 text-xs font-medium text-slate-400">
                    Levels {tier.levels}
                  </span>
                </div>
                <p className="text-slate-400 text-sm leading-relaxed">
                  {tier.description}
                </p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Getting Started Section */}
      <section className="relative py-20 px-4 sm:px-6 lg:px-8">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-16">
            <h2 className="text-3xl sm:text-4xl font-bold text-slate-100 mb-4">
              Quick Start
            </h2>
            <p className="text-slate-400 max-w-2xl mx-auto">
              Get up and running in minutes with Docker
            </p>
          </div>

          <div className="max-w-4xl mx-auto">
            <div className="bg-slate-900/80 rounded-2xl border border-slate-800/50 overflow-hidden">
              <div className="flex items-center gap-2 px-4 py-3 bg-slate-800/50 border-b border-slate-700/50">
                <div className="flex gap-2">
                  <div className="w-3 h-3 rounded-full bg-red-500" />
                  <div className="w-3 h-3 rounded-full bg-yellow-500" />
                  <div className="w-3 h-3 rounded-full bg-green-500" />
                </div>
                <div className="flex-1 text-center">
                  <span className="text-sm text-slate-400">Terminal</span>
                </div>
              </div>
              <div className="p-6 space-y-4">
                <div className="space-y-2">
                  <div className="flex items-center gap-2 text-sm text-slate-500 mb-2">
                    <span className="text-pink-500">$</span>
                    <span className="text-slate-400"># Clone the repository</span>
                  </div>
                  <code className="block p-4 rounded-xl bg-slate-950 text-sm text-emerald-400 font-mono overflow-x-auto">
                    git clone https://github.com/webspoilt/vulnforge-academy.git<br />
                    cd vulnforge-academy
                  </code>
                </div>
                <div className="space-y-2">
                  <div className="flex items-center gap-2 text-sm text-slate-500 mb-2">
                    <span className="text-pink-500">$</span>
                    <span className="text-slate-400"># Start with Docker Compose</span>
                  </div>
                  <code className="block p-4 rounded-xl bg-slate-950 text-sm text-emerald-400 font-mono overflow-x-auto">
                    docker-compose up -d
                  </code>
                </div>
                <div className="space-y-2">
                  <div className="flex items-center gap-2 text-sm text-slate-500 mb-2">
                    <span className="text-pink-500">$</span>
                    <span className="text-slate-400"># Access the application</span>
                  </div>
                  <code className="block p-4 rounded-xl bg-slate-950 text-sm text-emerald-400 font-mono overflow-x-auto">
                    open http://localhost:8080
                  </code>
                </div>
              </div>
            </div>

            <div className="mt-8 text-center">
              <a
                href="https://github.com/webspoilt/vulnforge-academy"
                target="_blank"
                rel="noopener noreferrer"
                className="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-slate-800/50 hover:bg-slate-700/50 border border-slate-700/50 hover:border-pink-500/50 text-slate-300 hover:text-white font-medium transition-all duration-300"
              >
                <ExternalLink className="w-4 h-4" />
                View Full Documentation
              </a>
            </div>
          </div>
        </div>
      </section>

      {/* Security Notice Section */}
      <section className="relative py-20 px-4 sm:px-6 lg:px-8">
        <div className="max-w-4xl mx-auto">
          <div className="relative p-8 rounded-2xl bg-gradient-to-br from-red-950/50 to-orange-950/50 border border-red-900/50 overflow-hidden">
            <div className="absolute inset-0 bg-[radial-gradient(circle_at_30%_30%,rgba(239,68,68,0.1),transparent_70%)]" />
            <div className="relative">
              <div className="flex items-start gap-4 mb-6">
                <div className="flex-shrink-0 p-3 rounded-xl bg-red-500/20">
                  <AlertTriangle className="w-6 h-6 text-red-500" />
                </div>
                <div>
                  <h3 className="text-xl font-bold text-red-400 mb-2">
                    Security Notice
                  </h3>
                  <p className="text-red-300/80 font-medium mb-4">
                    This application contains INTENTIONAL security vulnerabilities
                  </p>
                </div>
              </div>

              <ul className="space-y-3">
                {[
                  'NEVER deploy on production servers',
                  'ONLY run in isolated environments',
                  'NEVER use real credentials',
                  'FOR EDUCATIONAL PURPOSES ONLY'
                ].map((notice) => (
                  <li key={notice} className="flex items-start gap-3">
                    <div className="flex-shrink-0 w-6 h-6 rounded-full bg-red-500/20 flex items-center justify-center mt-0.5">
                      <AlertTriangle className="w-4 h-4 text-red-500" />
                    </div>
                    <span className="text-red-200/80">{notice}</span>
                  </li>
                ))}
              </ul>
            </div>
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="relative mt-auto py-12 px-4 sm:px-6 lg:px-8 border-t border-slate-800/50">
        <div className="max-w-7xl mx-auto">
          <div className="grid md:grid-cols-3 gap-8 mb-8">
            <div>
              <div className="flex items-center gap-3 mb-4">
                <Shield className="w-8 h-8 text-pink-500" />
                <span className="text-xl font-bold text-slate-100">VulnForge Academy</span>
              </div>
              <p className="text-slate-400 text-sm leading-relaxed">
                A deliberately vulnerable web application designed for cybersecurity training and education.
              </p>
            </div>

            <div>
              <h4 className="text-sm font-semibold text-slate-100 uppercase tracking-wider mb-4">
                Resources
              </h4>
              <ul className="space-y-3">
                <li>
                  <a
                    href="https://github.com/webspoilt/vulnforge-academy"
                    target="_blank"
                    rel="noopener noreferrer"
                    className="flex items-center gap-2 text-slate-400 hover:text-pink-400 transition-colors text-sm"
                  >
                    <Github className="w-4 h-4" />
                    GitHub Repository
                  </a>
                </li>
                <li>
                  <a
                    href="https://owasp.org/www-project-top-ten/"
                    target="_blank"
                    rel="noopener noreferrer"
                    className="flex items-center gap-2 text-slate-400 hover:text-pink-400 transition-colors text-sm"
                  >
                    <ExternalLink className="w-4 h-4" />
                    OWASP Top 10
                  </a>
                </li>
              </ul>
            </div>

            <div>
              <h4 className="text-sm font-semibold text-slate-100 uppercase tracking-wider mb-4">
                Legal
              </h4>
              <ul className="space-y-3">
                <li>
                  <span className="text-slate-400 text-sm">MIT License - Educational Use Only</span>
                </li>
                <li>
                  <span className="text-slate-400 text-sm">Created by webspoilt</span>
                </li>
              </ul>
            </div>
          </div>

          <div className="pt-8 border-t border-slate-800/50">
            <div className="flex flex-col md:flex-row items-center justify-between gap-4">
              <p className="text-slate-500 text-sm">
                © 2025 VulnForge Academy. Educational use only.
              </p>
              <p className="text-slate-500 text-sm flex items-center gap-2">
                Happy Hacking!
                <Flame className="w-4 h-4 text-orange-500" />
              </p>
            </div>
          </div>
        </div>
      </footer>
    </div>
  )
}
