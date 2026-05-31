'use client'

import { GameCard } from './game-card'
import type { Game } from '@/lib/games'

interface GameGridProps {
  games: Game[]
  title?: string
  subtitle?: string
  onGameClick: (game: Game) => void
}

export function GameGrid({ games, title, subtitle, onGameClick }: GameGridProps) {
  if (games.length === 0) {
    return (
      <div className="text-center py-12">
        <div className="w-16 h-16 mx-auto mb-4 rounded-full bg-muted flex items-center justify-center">
          <span className="text-2xl">🎮</span>
        </div>
        <h3 className="text-lg font-semibold text-foreground">Oyun bulunamadı</h3>
        <p className="text-sm text-muted-foreground mt-1">
          Farklı bir arama terimi veya kategori deneyin
        </p>
      </div>
    )
  }

  return (
    <div className="mb-8">
      {(title || subtitle) && (
        <div className="mb-4">
          {title && <h2 className="text-xl font-bold text-foreground">{title}</h2>}
          {subtitle && <p className="text-sm text-muted-foreground">{subtitle}</p>}
        </div>
      )}
      <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 md:gap-4">
        {games.map((game) => (
          <GameCard key={game.id} game={game} onClick={onGameClick} />
        ))}
      </div>
    </div>
  )
}
