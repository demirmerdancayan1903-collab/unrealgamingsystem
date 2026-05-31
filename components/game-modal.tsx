'use client'

import { X, Star, Play, Share2, Heart, Maximize2, Minimize2, Volume2, VolumeX, RotateCcw, ExternalLink } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import type { Game } from '@/lib/games'
import Image from 'next/image'
import { useState, useRef, useEffect } from 'react'

interface GameModalProps {
  game: Game | null
  isOpen: boolean
  onClose: () => void
}

export function GameModal({ game, isOpen, onClose }: GameModalProps) {
  const [isPlaying, setIsPlaying] = useState(false)
  const [isFullscreen, setIsFullscreen] = useState(false)
  const [isMuted, setIsMuted] = useState(false)
  const [isLoading, setIsLoading] = useState(false)
  const [loadError, setLoadError] = useState(false)
  const iframeRef = useRef<HTMLIFrameElement>(null)
  const containerRef = useRef<HTMLDivElement>(null)
  const loadTimeoutRef = useRef<ReturnType<typeof setTimeout> | null>(null)

  useEffect(() => {
    if (!isOpen) {
      setIsPlaying(false)
      setIsFullscreen(false)
      setIsLoading(false)
      setLoadError(false)
      if (loadTimeoutRef.current) {
        clearTimeout(loadTimeoutRef.current)
      }
    }
  }, [isOpen])

  useEffect(() => {
    const handleFullscreenChange = () => {
      setIsFullscreen(!!document.fullscreenElement)
    }
    document.addEventListener('fullscreenchange', handleFullscreenChange)
    return () => document.removeEventListener('fullscreenchange', handleFullscreenChange)
  }, [])

  if (!game) return null

  const handlePlay = () => {
    setIsLoading(true)
    setLoadError(false)
    setIsPlaying(true)
    // Set a timeout to show error if game doesn't load
    loadTimeoutRef.current = setTimeout(() => {
      if (isLoading) {
        setLoadError(true)
        setIsLoading(false)
      }
    }, 8000)
  }

  const handleIframeLoad = () => {
    setIsLoading(false)
    if (loadTimeoutRef.current) {
      clearTimeout(loadTimeoutRef.current)
    }
  }

  const handleOpenInNewTab = () => {
    window.open(game.gameUrl, '_blank', 'noopener,noreferrer')
  }

  const toggleFullscreen = async () => {
    if (!containerRef.current) return
    
    if (!document.fullscreenElement) {
      await containerRef.current.requestFullscreen()
    } else {
      await document.exitFullscreen()
    }
  }

  const handleRestart = () => {
    if (iframeRef.current) {
      iframeRef.current.src = game.gameUrl
    }
  }

  const handleShare = async () => {
    if (navigator.share) {
      await navigator.share({
        title: game.title,
        text: `${game.title} oynuyorum! Unreal Gaming System'de sen de oyna!`,
        url: window.location.href,
      })
    } else {
      await navigator.clipboard.writeText(window.location.href)
      alert('Link kopyalandı!')
    }
  }

  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent className={`${isFullscreen ? 'max-w-[100vw] w-[100vw] h-[100vh] rounded-none' : 'max-w-6xl w-[95vw]'} p-0 bg-card border-border overflow-hidden`}>
        <DialogHeader className="sr-only">
          <DialogTitle>{game.title}</DialogTitle>
        </DialogHeader>
        
        <div ref={containerRef} className={`relative ${isFullscreen ? 'h-screen' : ''}`}>
          {/* Game Display Area */}
          <div className={`relative ${isFullscreen ? 'h-full' : 'aspect-video'} bg-black`}>
            {!isPlaying ? (
              <>
                <Image
                  src={game.thumbnail}
                  alt={game.title}
                  fill
                  className="object-cover"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent flex items-center justify-center">
                  <div className="text-center">
                    <div className="flex flex-col sm:flex-row gap-3 items-center justify-center">
                      <Button
                        size="lg"
                        className="bg-primary hover:bg-primary/90 text-primary-foreground gap-3 text-xl px-10 py-8 rounded-full shadow-2xl shadow-primary/30 transition-all hover:scale-105"
                        onClick={handlePlay}
                      >
                        <Play className="w-8 h-8 fill-current" />
                        Oyna
                      </Button>
                      <Button
                        size="lg"
                        variant="outline"
                        className="bg-white/10 hover:bg-white/20 text-white border-white/30 gap-2 rounded-full backdrop-blur-sm"
                        onClick={handleOpenInNewTab}
                      >
                        <ExternalLink className="w-5 h-5" />
                        Yeni Sekmede
                      </Button>
                    </div>
                    <p className="text-white/70 text-sm mt-4">Oyunu oynamak icin tiklayin</p>
                  </div>
                </div>
              </>
            ) : (
              <>
                {isLoading && (
                  <div className="absolute inset-0 z-10 flex items-center justify-center bg-background">
                    <div className="text-center">
                      <div className="w-16 h-16 mx-auto mb-4 rounded-full border-4 border-primary/30 border-t-primary animate-spin" />
                      <p className="text-lg font-semibold text-foreground">{game.title}</p>
                      <p className="text-sm text-muted-foreground mt-1">Oyun yükleniyor...</p>
                    </div>
                  </div>
                )}
                {loadError && (
                  <div className="absolute inset-0 z-10 flex items-center justify-center bg-background">
                    <div className="text-center px-4">
                      <div className="w-16 h-16 mx-auto mb-4 rounded-full bg-muted flex items-center justify-center">
                        <ExternalLink className="w-8 h-8 text-muted-foreground" />
                      </div>
                      <p className="text-lg font-semibold text-foreground mb-2">Oyun burada yüklenemedi</p>
                      <p className="text-sm text-muted-foreground mb-4">Bazı oyunlar guvenlik nedeniyle yeni sekmede acilmali</p>
                      <Button onClick={handleOpenInNewTab} className="gap-2">
                        <ExternalLink className="w-4 h-4" />
                        Yeni Sekmede Ac
                      </Button>
                    </div>
                  </div>
                )}
                <iframe
                  ref={iframeRef}
                  src={game.gameUrl}
                  className="absolute inset-0 w-full h-full border-0"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
                  allowFullScreen
                  onLoad={handleIframeLoad}
                />
              </>
            )}
            
            {/* Top Controls */}
            <div className="absolute top-3 left-3 right-3 flex justify-between items-center z-20">
              <div className="flex items-center gap-2">
                {isPlaying && (
                  <>
                    <Button 
                      variant="secondary" 
                      size="icon" 
                      onClick={handleRestart}
                      className="bg-black/60 hover:bg-black/80 border-0 backdrop-blur-sm"
                      title="Yeniden Başlat"
                    >
                      <RotateCcw className="w-4 h-4 text-white" />
                    </Button>
                    <Button 
                      variant="secondary" 
                      size="icon" 
                      onClick={() => setIsMuted(!isMuted)}
                      className="bg-black/60 hover:bg-black/80 border-0 backdrop-blur-sm"
                      title={isMuted ? 'Sesi Aç' : 'Sesi Kapat'}
                    >
                      {isMuted ? <VolumeX className="w-4 h-4 text-white" /> : <Volume2 className="w-4 h-4 text-white" />}
                    </Button>
                  </>
                )}
              </div>
              <div className="flex gap-2">
                <Button 
                  variant="secondary" 
                  size="icon" 
                  onClick={toggleFullscreen} 
                  className="bg-black/60 hover:bg-black/80 border-0 backdrop-blur-sm"
                  title={isFullscreen ? 'Tam Ekrandan Çık' : 'Tam Ekran'}
                >
                  {isFullscreen ? <Minimize2 className="w-4 h-4 text-white" /> : <Maximize2 className="w-4 h-4 text-white" />}
                </Button>
                <Button 
                  variant="secondary" 
                  size="icon" 
                  onClick={onClose} 
                  className="bg-black/60 hover:bg-black/80 border-0 backdrop-blur-sm"
                  title="Kapat"
                >
                  <X className="w-4 h-4 text-white" />
                </Button>
              </div>
            </div>
          </div>

          {/* Game Info - Only show when not fullscreen */}
          {!isFullscreen && (
            <div className="p-6 bg-card">
              <div className="flex items-start justify-between gap-4">
                <div>
                  <div className="flex items-center gap-2 mb-2 flex-wrap">
                    <h2 className="text-2xl font-bold text-foreground">{game.title}</h2>
                    {game.isNew && <Badge className="bg-accent text-accent-foreground">Yeni</Badge>}
                    {game.isTrending && <Badge className="bg-chart-5 text-primary-foreground">Trend</Badge>}
                  </div>
                  <div className="flex items-center gap-4 text-sm text-muted-foreground flex-wrap">
                    <div className="flex items-center gap-1">
                      <Star className="w-4 h-4 text-chart-4 fill-chart-4" />
                      <span>{game.rating}</span>
                    </div>
                    <span>{game.plays} oynama</span>
                    <Badge variant="outline">{game.category}</Badge>
                  </div>
                </div>
                <div className="flex gap-2">
                  <Button variant="outline" size="icon" title="Favorilere Ekle">
                    <Heart className="w-4 h-4" />
                  </Button>
                  <Button variant="outline" size="icon" onClick={handleShare} title="Paylaş">
                    <Share2 className="w-4 h-4" />
                  </Button>
                </div>
              </div>

              <div className="mt-4 pt-4 border-t border-border">
                <h3 className="font-semibold text-foreground mb-2">Nasıl Oynanır?</h3>
                <p className="text-sm text-muted-foreground">
                  Klavye ok tuşları veya WASD ile hareket edin. Fare ile hedef alın ve tıklayarak etkileşime geçin. 
                  Mobil cihazlarda ekran kontrollerini kullanın. Tam ekran modu için sağ üstteki butonu kullanın.
                </p>
              </div>
            </div>
          )}
        </div>
      </DialogContent>
    </Dialog>
  )
}
