interface TerminalProps {
    output: string[]
    isProcessing: boolean
}

export function Terminal({ output, isProcessing }: TerminalProps) {
    return (
        <div className="max-w-4xl mx-auto">
            <div className="bg-black rounded-2xl overflow-hidden border border-[#2a2a3a] shadow-2xl">
                {/* Terminal Header */}
                <div className="bg-[#1a1a2e] px-4 py-3 flex items-center gap-2 border-b border-[#2a2a3a]">
                    <div className="w-3 h-3 rounded-full bg-[#ff5f56]"></div>
                    <div className="w-3 h-3 rounded-full bg-[#ffbd2e]"></div>
                    <div className="w-3 h-3 rounded-full bg-[#27c93f]"></div>
                    <span className="ml-4 font-mono text-sm text-[#888899]">vulnforge@academy:~</span>
                </div>

                {/* Terminal Body */}
                <div className="p-6 font-mono text-sm leading-relaxed min-h-[200px]">
                    {/* Default output */}
                    {output.length === 0 ? (
                        <div className="space-y-2">
                            <div className="opacity-0 animate-[fadeIn_0.5s_forwards]">$ initializing_vulnforge_academy...</div>
                            <div className="opacity-0 animate-[fadeIn_0.5s_0.2s_forwards]">[OK] Loading vulnerability modules...</div>
                            <div className="opacity-0 animate-[fadeIn_0.5s_0.4s_forwards]">[OK] SQL Injection training environment ready</div>
                            <div className="opacity-0 animate-[fadeIn_0.5s_0.6s_forwards]">[OK] XSS sandbox initialized</div>
                            <div className="opacity-0 animate-[fadeIn_0.5s_0.8s_forwards]">[OK] Authentication bypass modules loaded</div>
                            <div className="opacity-0 animate-[fadeIn_0.5s_1s_forwards]">[OK] 20 training levels available</div>
                            <div className="opacity-0 animate-[fadeIn_0.5s_1.2s_forwards]">
                                <span className="text-[#00d4ff]">➜</span>
                                <span className="text-[#ff0055]"> Select a level to begin exploitation...</span>
                                <span className="inline-block w-2 h-[1.2em] bg-[#00ff41] animate-pulse align-middle ml-1"></span>
                            </div>
                        </div>
                    ) : (
                        /* Dynamic output */
                        <div className="space-y-2">
                            {output.map((line, index) => (
                                <div
                                    key={index}
                                    className="animate-[fadeIn_0.3s_forwards]"
                                    style={{ animationDelay: `${index * 0.1}s` }}
                                >
                                    <span className="text-[#00d4ff]">$</span> {line}
                                </div>
                            ))}
                            {isProcessing && (
                                <div className="inline-block w-2 h-[1.2em] bg-[#00ff41] animate-pulse align-middle ml-1"></div>
                            )}
                        </div>
                    )}
                </div>
            </div>

            {/* Add fadeIn animation */}
            <style jsx>{`
        @keyframes fadeIn {
          from { opacity: 0; }
          to { opacity: 1; }
        }
      `}</style>
        </div>
    )
}
