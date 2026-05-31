'use client'

import { useState, useMemo } from 'react'
import { Header } from '@/components/header'
import { Sidebar } from '@/components/sidebar'
import { FeaturedCarousel } from '@/components/featured-carousel'
import { GameGrid } from '@/components/game-grid'
import { GameModal } from '@/components/game-modal'
import { Footer } from '@/components/footer'
import { 
  games, 
  categories, 
  getFeaturedGames, 
  getTrendingGames, 
  getNewGames, 
  searchGames 
} from '@/lib/games'
import type { Game } from '@/lib/games'

export default function HomePage() {
  const [selectedCategory, setSelectedCategory] = useState('all')
  const [searchQuery, setSearchQuery] = useState('')
  const [selectedGame, setSelectedGame] = useState<Game | null>(null)
  const [isModalOpen, setIsModalOpen] = useState(false)

  const filteredGames = useMemo(() => {
    let result = games

    // Apply search filter
    if (searchQuery.trim()) {
      result = searchGames(searchQuery)
    }

    // Apply category filter
    if (selectedCategory !== 'all') {
      result = result.filter(game => game.category === selectedCategory)
    }

    return result
  }, [selectedCategory, searchQuery])

  const featuredGames = useMemo(() => getFeaturedGames(), [])
  const trendingGames = useMemo(() => getTrendingGames(), [])
  const newGames = useMemo(() => getNewGames(), [])

  const handleGameClick = (game: Game) => {
    setSelectedGame(game)
    setIsModalOpen(true)
  }

  const handleCloseModal = () => {
    setIsModalOpen(false)
    setSelectedGame(null)
  }

  const currentCategory = categories.find(cat => cat.id === selectedCategory)
  const isFiltering = searchQuery.trim() || selectedCategory !== 'all'

  return (
    <div className="min-h-screen bg-background">
      <Header
        searchQuery={searchQuery}
        setSearchQuery={setSearchQuery}
        selectedCategory={selectedCategory}
        setSelectedCategory={setSelectedCategory}
      />

      <div className="max-w-[1800px] mx-auto px-4 py-6">
        <div className="flex gap-6">
          <Sidebar
            selectedCategory={selectedCategory}
            setSelectedCategory={setSelectedCategory}
          />

          <main className="flex-1 min-w-0">
            {/* Show featured carousel only on home (no search, all categories) */}
            {!isFiltering && (
              <>
                <FeaturedCarousel games={featuredGames} onGameClick={handleGameClick} />
                
                <GameGrid
                  games={trendingGames}
                  title="🔥 Trend Oyunlar"
                  subtitle="Şu an en çok oynanan oyunlar"
                  onGameClick={handleGameClick}
                />

                <GameGrid
                  games={newGames}
                  title="✨ Yeni Eklenenler"
                  subtitle="En son eklenen oyunlar"
                  onGameClick={handleGameClick}
                />
              </>
            )}

            {/* Category or Search Results */}
            <GameGrid
              games={filteredGames}
              title={
                searchQuery.trim()
                  ? `"${searchQuery}" için sonuçlar`
                  : currentCategory?.name === 'Tümü'
                  ? '🎮 Tüm Oyunlar'
                  : `${currentCategory?.icon || ''} ${currentCategory?.name || ''}`
              }
              subtitle={
                searchQuery.trim()
                  ? `${filteredGames.length} oyun bulundu`
                  : `${filteredGames.length} oyun`
              }
              onGameClick={handleGameClick}
            />
          </main>
        </div>
      </div>

      <Footer />

      <GameModal
        game={selectedGame}
        isOpen={isModalOpen}
        onClose={handleCloseModal}
      />
    </div>
  )
}
