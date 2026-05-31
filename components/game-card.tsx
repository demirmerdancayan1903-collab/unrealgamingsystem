'use client'

import Image from 'next/image'
import { Play, Star, TrendingUp, Sparkles } from 'lucide-react'
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import type { Game } from '@/lib/games'

interface GameCardProps {
  game: Game
  onClick: (game: Game) => void
}

export function GameCard({ game, onClick }: GameCardProps) {
  return (
    <Card 
      className="group cursor-pointer overflow-hidden bg-card border-border hover:border-primary/50 transition-all duration-300 hover:scale-[1.02] hover:shadow-lg hover:shadow-primary/10"
      onClick={() => onClick(game)}
    >
      <CardContent className="p-0">
        <div className="relative aspect-[4/3] overflow-hidden">
          <Image
            src={game.thumbnail}
            alt={game.title}
            fill
            className="object-cover transition-transform duration-300 group-hover:scale-110"
            sizes="(max-width: 640px) 50vw, (max-width: 1024px) 33vw, 20vw"
          />
          
          {/* Overlay on hover */}
          <div className="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
            <div className="w-14 h-14 rounded-full bg-primary flex items-center justify-center transform scale-75 group-hover:scale-100 transition-transform duration-300">
              <Play className="w-7 h-7 text-primary-foreground fill-current ml-1" />
            </div>
          </div>
          
          {/* Badges */}
          <div className="absolute top-2 left-2 flex flex-col gap-1">
            {game.isNew && (
              <Badge className="bg-accent text-accent-foreground text-xs px-2 py-0.5">
                <Sparkles className="w-3 h-3 mr-1" />
                Yeni
              </Badge>
            )}
            {game.isTrending && (
              <Badge className="bg-chart-5 text-primary-foreground text-xs px-2 py-0.5">
                <TrendingUp className="w-3 h-3 mr-1" />
                Trend
              </Badge>
            )}
          </div>
        </div>
        
        <div className="p-3">
          <h3 className="font-semibold text-sm text-foreground truncate group-hover:text-primary transition-colors">
            {game.title}
          </h3>
          <div className="flex items-center justify-between mt-1.5">
            <span className="text-xs text-muted-foreground">{game.plays} oynama</span>
            <div className="flex items-center gap-1">
              <Star className="w-3 h-3 text-chart-4 fill-chart-4" />
              <span className="text-xs text-muted-foreground">{game.rating}</span>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>
  )
}
