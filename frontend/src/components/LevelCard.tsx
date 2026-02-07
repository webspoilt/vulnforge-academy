interface LevelCardProps {
    level: {
        id: number
        title: string
        desc: string
        difficulty: 'easy' | 'medium' | 'hard' | 'nightmare'
        tags: string[]
        points: number
    }
    isLocked: boolean
    isCompleted: boolean
    onClick: () => void
}

export function LevelCard({ level, isLocked, isCompleted, onClick }: LevelCardProps) {
    const badgeColors = {
        easy: 'bg-[rgba(0,255,65,0.2)] text-[#00ff41] border-[#00ff41]',
        medium: 'bg-[rgba(255,204,0,0.2)] text-[#ffcc00] border-[#ffcc00]',
        hard: 'bg-[rgba(255,0,85,0.2)] text-[#ff0055] border-[#ff0055]',
        nightmare: 'bg-[rgba(153,0,255,0.2)] text-[#9900ff] border-[#9900ff]'
    }

    return (
        <div
            onClick={isLocked ? undefined : onClick}
            className={`
        group relative bg-[#12121a] border rounded-2xl p-6 overflow-hidden
        transition-all duration-300 cursor-pointer
        ${isLocked
                    ? 'opacity-50 grayscale pointer-events-none'
                    : 'hover:-translate-y-1 hover:border-[#00ff41] hover:shadow-[0_0_20px_rgba(0,255,65,0.3)]'
                }
        ${isCompleted
                    ? 'border-[#00ff41] bg-gradient-to-br from-[rgba(0,255,65,0.05)] to-[#12121a]'
                    : 'border-[#2a2a3a]'
                }
      `}
            role="button"
            tabIndex={isLocked ? -1 : 0}
            aria-disabled={isLocked}
            aria-label={`Level ${level.id}: ${level.title} - ${level.difficulty} difficulty, ${level.points} points${isLocked ? ' (locked)' : isCompleted ? ' (completed)' : ''}`}
            onKeyDown={(e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    if (!isLocked) onClick()
                }
            }}
        >
            {/* Animated top border on hover */}
            {!isLocked && (
                <div className="absolute top-0 left-0 w-full h-[3px] bg-gradient-to-r from-[#00ff41] to-[#00d4ff] scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></div>
            )}

            {/* Level Header */}
            <div className="flex justify-between items-start mb-4">
                <span className="text-4xl font-bold text-[#00ff41] opacity-30 font-mono">
                    {String(level.id).padStart(2, '0')}
                </span>
                <span className={`px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border ${badgeColors[level.difficulty]}`}>
                    {level.difficulty}
                </span>
            </div>

            {/* Title */}
            <h3 className="text-xl font-bold text-[#e0e0e0] mb-2">
                {level.title}
            </h3>

            {/* Description */}
            <p className="text-[#888899] text-sm mb-4 leading-relaxed">
                {level.desc}
            </p>

            {/* Tags */}
            <div className="flex gap-2 flex-wrap mb-4">
                {level.tags.map(tag => (
                    <span
                        key={tag}
                        className="bg-[#1a1a2e] px-2 py-1 rounded text-xs font-mono text-[#00d4ff] border border-[#2a2a3a]"
                    >
                        {tag}
                    </span>
                ))}
            </div>

            {/* Footer */}
            <div className="flex justify-between items-center pt-4 border-t border-[#2a2a3a]">
                <span className="font-mono font-bold text-[#00d4ff]">
                    {level.points} PTS
                </span>
                <div className="w-6 h-6 rounded-full flex items-center justify-center text-sm">
                    {isLocked ? (
                        <span className="bg-[#1a1a2e] text-[#888899]">🔒</span>
                    ) : isCompleted ? (
                        <span className="bg-[#00ff41] text-[#0a0a0f] rounded-full w-6 h-6 flex items-center justify-center">✓</span>
                    ) : (
                        <span className="bg-[rgba(0,255,65,0.2)] text-[#00ff41] border border-[#00ff41] rounded-full w-6 h-6 flex items-center justify-center">▶</span>
                    )}
                </div>
            </div>
        </div>
    )
}
