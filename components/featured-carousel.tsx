'use client'

import { useRef } from 'react'
import { ChevronLeft, ChevronRight, Star, Play } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import type { Game } from '@/lib/games'
import Image from 'next/image'

interface FeaturedCarouselProps {
  games: Game[]
  onGameClick: (game: Game) => void
}

export function FeaturedCarousel({ games, onGameClick }: FeaturedCarouselProps) {
  const scrollRef = useRef<HTMLDivElement>(null)

  const scroll = (direction: 'left' | 'right') => {
    if (scrollRef.current) {
      const scrollAmount = direction === 'left' ? -400 : 400
      scrollRef.current.scrollBy({ left: scrollAmount, behavior: 'smooth' })
    }
  }

  return (
    <div className="relative mb-8">
      <div className="flex items-center justify-between mb-4">
        <div>
          <h2 className="text-2xl font-bold text-foreground">Öne Çıkan Oyunlar</h2>
          <p className="text-sm text-muted-foreground">En popüler ve beğenilen oyunlar</p>
        </div>
        <div className="flex gap-2">
          <Button variant="outline" size="icon" onClick={() => scroll('left')}>
            <ChevronLeft className="w-4 h-4" />
          </Button>
          <Button variant="outline" size="icon" onClick={() => scroll('right')}>
            <ChevronRight className="w-4 h-4" />
          </Button>
        </div>
      </div>

      <div
        ref={scrollRef}
        className="flex gap-4 overflow-x-auto scrollbar-hide snap-x snap-mandatory pb-4"
        style={{ scrollbarWidth: 'none', msOverflowStyle: 'none' }}
      >
        {games.map((game) => (
          <Card
            key={game.id}
            className="shrink-0 w-[320px] md:w-[400px] cursor-pointer group overflow-hidden bg-card border-border hover:border-primary/50 transition-all duration-300 snap-start"
            onClick={() => onGameClick(game)}
          >
            <CardContent className="p-0">
              <div className="relative aspect-video overflow-hidden">
                <Image
                  src={game.thumbnail}
                  alt={game.title}
                  fill
                  className="object-cover transition-transform duration-500 group-hover:scale-110"
                  sizes="(max-width: 768px) 320px, 400px"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent" />
                
                {/* Play Button */}
                <div className="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                  <div className="w-16 h-16 rounded-full bg-primary flex items-center justify-center transform scale-75 group-hover:scale-100 transition-transform duration-300">
                    <Play className="w-8 h-8 text-primary-foreground fill-current ml-1" />
                  </div>
                </div>
                
                {/* Featured Badge */}
                <Badge className="absolute top-3 left-3 bg-primary text-primary-foreground">
                  Öne Çıkan
                </Badge>

                {/* Game Info */}
                <div className="absolute bottom-0 left-0 right-0 p-4">
                  <h3 className="text-xl font-bold text-white mb-1">{game.title}</h3>
                  <div className="flex items-center gap-3">
                    <div className="flex items-center gap-1">
                      <Star className="w-4 h-4 text-chart-4 fill-chart-4" />
                      <span className="text-sm text-white/90">{game.rating}</span>
                    </div>
                    <span className="text-sm text-white/70">{game.plays} oynama</span>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        ))}
      </div>
    </div>
  )
}
