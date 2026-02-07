'use client'

import { useEffect, useRef } from 'react'

export function ParticleBackground() {
    const containerRef = useRef<HTMLDivElement>(null)

    useEffect(() => {
        const container = containerRef.current
        if (!container) return

        // Create particles
        const particleCount = 50
        const particles: HTMLDivElement[] = []

        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div')
            particle.className = 'particle'
            particle.style.cssText = `
        position: absolute;
        width: 2px;
        height: 2px;
        background: #00ff41;
        border-radius: 50%;
        opacity: 0.5;
        left: ${Math.random() * 100}%;
        animation-delay: ${Math.random() * 10}s;
        animation-duration: ${Math.random() * 10 + 10}s;
        animation: float ${Math.random() * 10 + 10}s infinite;
      `
            particles.push(particle)
            container.appendChild(particle)
        }

        return () => {
            particles.forEach(p => p.remove())
        }
    }, [])

    return (
        <>
            {/* Background Grid */}
            <div className="bg-grid"></div>

            {/* Scanline Effect */}
            <div className="scanlines"></div>

            {/* Floating Particles */}
            <div
                ref={containerRef}
                className="fixed inset-0 pointer-events-none z-0"
                style={{ animation: 'none' }}
            />

            {/* Animation keyframes */}
            <style jsx>{`
        @keyframes float {
          0%, 100% {
            transform: translateY(100vh) rotate(0deg);
            opacity: 0;
          }
          10% {
            opacity: 0.5;
          }
          90% {
            opacity: 0.5;
          }
          100% {
            transform: translateY(-100vh) rotate(720deg);
            opacity: 0;
          }
        }
      `}</style>
        </>
    )
}
