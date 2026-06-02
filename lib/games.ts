export interface Game {
  id: string
  title: string
  category: string
  thumbnail: string
  plays: string
  rating: number
  isNew?: boolean
  isTrending?: boolean
  isFeatured?: boolean
  gameUrl: string
}

export const categories = [
  { id: 'all', name: 'Tümü', icon: '🎮' },
  { id: 'action', name: 'Aksiyon', icon: '⚔️' },
  { id: 'adventure', name: 'Macera', icon: '🗺️' },
  { id: 'puzzle', name: 'Bulmaca', icon: '🧩' },
  { id: 'racing', name: 'Yarış', icon: '🏎️' },
  { id: 'sports', name: 'Spor', icon: '⚽' },
  { id: 'shooting', name: 'Nişancı', icon: '🎯' },
  { id: 'strategy', name: 'Strateji', icon: '♟️' },
  { id: 'multiplayer', name: 'Çok Oyunculu', icon: '👥' },
  { id: 'arcade', name: 'Arcade', icon: '👾' },
  { id: 'io', name: '.io Oyunları', icon: '🌐' },
  { id: 'horror', name: 'Korku', icon: '👻' },
  { id: 'simulation', name: 'Simülasyon', icon: '🏗️' },
  { id: 'fighting', name: 'Dövüş', icon: '🥊' },
  { id: 'casual', name: 'Günlük', icon: '🎪' },
]

// GameDistribution embed URLs - these work in iframes
const GD_BASE = 'https://html5.gamedistribution.com'

export const games: Game[] = [
  // Action Games
  { id: '1', title: 'Ninja Warrior', category: 'action', thumbnail: 'https://picsum.photos/seed/ninja/400/300', plays: '2.5M', rating: 4.8, isFeatured: true, isTrending: true, gameUrl: `${GD_BASE}/rvvASMiM/b81d998ec1224e5e8f24b41282e4aa6e/` },
  { id: '2', title: 'Robot Rampage', category: 'action', thumbnail: 'https://picsum.photos/seed/robot/400/300', plays: '1.8M', rating: 4.6, isNew: true, gameUrl: `${GD_BASE}/4ecd02a61c754aa5a8e4c7b4b5f4cc55/` },
  { id: '3', title: 'Zombie Hunter', category: 'action', thumbnail: 'https://picsum.photos/seed/zombie/400/300', plays: '3.2M', rating: 4.7, isTrending: true, gameUrl: `${GD_BASE}/5c8f2b1e1d4a4e8eb7d1c6f5a4b3c2d1/` },
  { id: '4', title: 'Space Marine', category: 'action', thumbnail: 'https://picsum.photos/seed/space/400/300', plays: '1.5M', rating: 4.5, gameUrl: `${GD_BASE}/a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6/` },
  { id: '5', title: 'Dragon Slayer', category: 'action', thumbnail: 'https://picsum.photos/seed/dragon/400/300', plays: '2.1M', rating: 4.9, isFeatured: true, gameUrl: `${GD_BASE}/d5e6f7a8b9c0d1e2f3a4b5c6d7e8f9a0/` },
  { id: '6', title: 'Shadow Strike', category: 'action', thumbnail: 'https://picsum.photos/seed/shadow/400/300', plays: '980K', rating: 4.4, gameUrl: `${GD_BASE}/f8e7d6c5b4a3f2e1d0c9b8a7f6e5d4c3/` },
  { id: '7', title: 'Battle Arena', category: 'action', thumbnail: 'https://picsum.photos/seed/arena/400/300', plays: '1.2M', rating: 4.3, gameUrl: `${GD_BASE}/a9b8c7d6e5f4a3b2c1d0e9f8a7b6c5d4/` },
  { id: '8', title: 'Sword Master', category: 'action', thumbnail: 'https://picsum.photos/seed/sword/400/300', plays: '2.8M', rating: 4.7, isNew: true, gameUrl: `${GD_BASE}/c2d3e4f5a6b7c8d9e0f1a2b3c4d5e6f7/` },
  <div id="gamemonetize-video"></div>
<script type="text/javascript">
   window.VIDEO_OPTIONS = {
       gameid : "4kci7og3klgj0ivy2wz3gdvd9dth5e7n",
       width  : "100%",
       height : "480px",
       color  : "#3f007e",
       getAds  : "false"
   };
   (function (a, b, c) {
      var d = a.getElementsByTagName(b)[0];
      a.getElementById(c) || (a = a.createElement(b), a.id = c, a.src = "https://api.gamemonetize.com/video.js", d.parentNode.insertBefore(a, d))
   })(document, "script", "gamemonetize-video-api"); 
</script>  

  // Adventure Games
  { id: '9', title: 'Lost Island', category: 'adventure', thumbnail: 'https://picsum.photos/seed/island/400/300', plays: '1.9M', rating: 4.6, isFeatured: true, gameUrl: 'https://playhop.com/embed/fireboy-and-watergirl-forest-temple' },
  { id: '10', title: 'Treasure Hunt', category: 'adventure', thumbnail: 'https://picsum.photos/seed/treasure/400/300', plays: '1.4M', rating: 4.5, gameUrl: 'https://playhop.com/embed/tomb-runner' },
  { id: '11', title: 'Jungle Explorer', category: 'adventure', thumbnail: 'https://picsum.photos/seed/jungle/400/300', plays: '2.3M', rating: 4.8, isTrending: true, gameUrl: 'https://playhop.com/embed/fireboy-and-watergirl-2' },
  { id: '12', title: 'Mystery Manor', category: 'adventure', thumbnail: 'https://picsum.photos/seed/manor/400/300', plays: '890K', rating: 4.4, gameUrl: 'https://playhop.com/embed/escape-out' },
  { id: '13', title: 'Ancient Quest', category: 'adventure', thumbnail: 'https://picsum.photos/seed/ancient/400/300', plays: '1.1M', rating: 4.3, gameUrl: 'https://playhop.com/embed/temple-run' },
  { id: '14', title: 'Ocean Voyage', category: 'adventure', thumbnail: 'https://picsum.photos/seed/ocean/400/300', plays: '750K', rating: 4.2, gameUrl: 'https://playhop.com/embed/fish-eat-fish' },
  { id: '15', title: 'Desert Storm', category: 'adventure', thumbnail: 'https://picsum.photos/seed/desert/400/300', plays: '1.6M', rating: 4.6, isNew: true, gameUrl: 'https://playhop.com/embed/desert-road' },
  <div id="gamemonetize-video"></div>
<script type="text/javascript">
   window.VIDEO_OPTIONS = {
       gameid : "4kci7og3klgj0ivy2wz3gdvd9dth5e7n",
       width  : "100%",
       height : "480px",
       color  : "#3f007e",
       getAds  : "false"
   };
   (function (a, b, c) {
      var d = a.getElementsByTagName(b)[0];
      a.getElementById(c) || (a = a.createElement(b), a.id = c, a.src = "https://api.gamemonetize.com/video.js", d.parentNode.insertBefore(a, d))
   })(document, "script", "gamemonetize-video-api"); 
</script>  

  // Puzzle Games - Using embeddable HTML5 games
  { id: '16', title: 'Block Master', category: 'puzzle', thumbnail: 'https://picsum.photos/seed/block/400/300', plays: '4.2M', rating: 4.9, isFeatured: true, isTrending: true, gameUrl: 'https://www.lumpty.com/amusements/Games/Tetris/tetris.html' },
  { id: '17', title: 'Mind Twist', category: 'puzzle', thumbnail: 'https://picsum.photos/seed/mind/400/300', plays: '2.8M', rating: 4.7, gameUrl: 'https://play2048.co/' },
  { id: '18', title: 'Color Match', category: 'puzzle', thumbnail: 'https://picsum.photos/seed/color/400/300', plays: '3.5M', rating: 4.8, gameUrl: 'https://playhop.com/embed/color-switch' },
  { id: '19', title: 'Logic Pro', category: 'puzzle', thumbnail: 'https://picsum.photos/seed/logic/400/300', plays: '1.9M', rating: 4.6, gameUrl: 'https://playhop.com/embed/cut-the-rope' },
  { id: '20', title: 'Cube Puzzle', category: 'puzzle', thumbnail: 'https://picsum.photos/seed/cube/400/300', plays: '2.1M', rating: 4.5, isNew: true, gameUrl: 'https://playhop.com/embed/cubes-2048-io' },
  { id: '21', title: 'Pattern Maker', category: 'puzzle', thumbnail: 'https://picsum.photos/seed/pattern/400/300', plays: '1.3M', rating: 4.4, gameUrl: 'https://playhop.com/embed/flow-free-online' },
  { id: '22', title: 'Brain Games', category: 'puzzle', thumbnail: 'https://picsum.photos/seed/brain/400/300', plays: '2.6M', rating: 4.7, gameUrl: 'https://playhop.com/embed/brain-test' },
  
  // Racing Games
  { id: '23', title: 'Turbo Racing', category: 'racing', thumbnail: 'https://picsum.photos/seed/turbo/400/300', plays: '5.1M', rating: 4.9, isFeatured: true, isTrending: true, gameUrl: 'https://playhop.com/embed/madalin-stunt-cars-2' },
  { id: '24', title: 'Drift Master', category: 'racing', thumbnail: 'https://picsum.photos/seed/drift/400/300', plays: '3.8M', rating: 4.8, gameUrl: 'https://playhop.com/embed/drift-hunters' },
  { id: '25', title: 'Moto Madness', category: 'racing', thumbnail: 'https://picsum.photos/seed/moto/400/300', plays: '2.9M', rating: 4.7, isNew: true, gameUrl: 'https://playhop.com/embed/moto-x3m' },
  { id: '26', title: 'Street Racer', category: 'racing', thumbnail: 'https://picsum.photos/seed/street/400/300', plays: '2.4M', rating: 4.6, gameUrl: 'https://playhop.com/embed/city-car-stunt-4' },
  { id: '27', title: 'Off Road Rally', category: 'racing', thumbnail: 'https://picsum.photos/seed/offroad/400/300', plays: '1.8M', rating: 4.5, gameUrl: 'https://playhop.com/embed/offroad-racer' },
  { id: '28', title: 'F1 Champion', category: 'racing', thumbnail: 'https://picsum.photos/seed/f1/400/300', plays: '3.2M', rating: 4.8, isTrending: true, gameUrl: 'https://playhop.com/embed/formula-rush' },
  { id: '29', title: 'Boat Racing', category: 'racing', thumbnail: 'https://picsum.photos/seed/boat/400/300', plays: '1.1M', rating: 4.3, gameUrl: 'https://playhop.com/embed/jet-ski-racing' },
  <div id="gamemonetize-video"></div>
<script type="text/javascript">
   window.VIDEO_OPTIONS = {
       gameid : "4kci7og3klgj0ivy2wz3gdvd9dth5e7n",
       width  : "100%",
       height : "480px",
       color  : "#3f007e",
       getAds  : "false"
   };
   (function (a, b, c) {
      var d = a.getElementsByTagName(b)[0];
      a.getElementById(c) || (a = a.createElement(b), a.id = c, a.src = "https://api.gamemonetize.com/video.js", d.parentNode.insertBefore(a, d))
   })(document, "script", "gamemonetize-video-api"); 
</script>  

  // Sports Games
  { id: '30', title: 'Football Pro', category: 'sports', thumbnail: 'https://picsum.photos/seed/football/400/300', plays: '4.5M', rating: 4.8, isFeatured: true, gameUrl: 'https://playhop.com/embed/soccer-skills-world-cup' },
  { id: '31', title: 'Basketball Star', category: 'sports', thumbnail: 'https://picsum.photos/seed/basket/400/300', plays: '3.2M', rating: 4.7, isTrending: true, gameUrl: 'https://playhop.com/embed/basketball-stars' },
  { id: '32', title: 'Tennis Ace', category: 'sports', thumbnail: 'https://picsum.photos/seed/tennis/400/300', plays: '1.9M', rating: 4.5, gameUrl: 'https://playhop.com/embed/tennis-legends' },
  { id: '33', title: 'Golf Master', category: 'sports', thumbnail: 'https://picsum.photos/seed/golf/400/300', plays: '1.4M', rating: 4.4, gameUrl: 'https://playhop.com/embed/mini-golf-king' },
  { id: '34', title: 'Boxing Ring', category: 'sports', thumbnail: 'https://picsum.photos/seed/boxing/400/300', plays: '2.1M', rating: 4.6, isNew: true, gameUrl: 'https://playhop.com/embed/boxing-random' },
  { id: '35', title: 'Swimming Race', category: 'sports', thumbnail: 'https://picsum.photos/seed/swim/400/300', plays: '890K', rating: 4.2, gameUrl: 'https://playhop.com/embed/swimming-pro' },
  <div id="gamemonetize-video"></div>
<script type="text/javascript">
   window.VIDEO_OPTIONS = {
       gameid : "4kci7og3klgj0ivy2wz3gdvd9dth5e7n",
       width  : "100%",
       height : "480px",
       color  : "#3f007e",
       getAds  : "false"
   };
   (function (a, b, c) {
      var d = a.getElementsByTagName(b)[0];
      a.getElementById(c) || (a = a.createElement(b), a.id = c, a.src = "https://api.gamemonetize.com/video.js", d.parentNode.insertBefore(a, d))
   })(document, "script", "gamemonetize-video-api"); 
</script>  

  // Shooting Games
  { id: '36', title: 'Sniper Elite', category: 'shooting', thumbnail: 'https://picsum.photos/seed/sniper/400/300', plays: '3.8M', rating: 4.8, isFeatured: true, isTrending: true, gameUrl: 'https://playhop.com/embed/sniper-3d' },
  { id: '37', title: 'Tank Battle', category: 'shooting', thumbnail: 'https://picsum.photos/seed/tank/400/300', plays: '2.5M', rating: 4.6, gameUrl: 'https://playhop.com/embed/tank-trouble-2' },
  { id: '38', title: 'Air Combat', category: 'shooting', thumbnail: 'https://picsum.photos/seed/air/400/300', plays: '2.1M', rating: 4.5, isNew: true, gameUrl: 'https://playhop.com/embed/air-wars-2' },
  { id: '39', title: 'Cowboy Shooter', category: 'shooting', thumbnail: 'https://picsum.photos/seed/cowboy/400/300', plays: '1.7M', rating: 4.4, gameUrl: 'https://playhop.com/embed/western-shooter' },
  { id: '40', title: 'Alien Invasion', category: 'shooting', thumbnail: 'https://picsum.photos/seed/alien/400/300', plays: '2.9M', rating: 4.7, gameUrl: 'https://playhop.com/embed/alien-shooter' },
  { id: '41', title: 'War Zone', category: 'shooting', thumbnail: 'https://picsum.photos/seed/warzone/400/300', plays: '4.2M', rating: 4.9, isTrending: true, gameUrl: 'https://playhop.com/embed/warzone-mercenaries' },
  <div id="gamemonetize-video"></div>
<script type="text/javascript">
   window.VIDEO_OPTIONS = {
       gameid : "4kci7og3klgj0ivy2wz3gdvd9dth5e7n",
       width  : "100%",
       height : "480px",
       color  : "#3f007e",
       getAds  : "false"
   };
   (function (a, b, c) {
      var d = a.getElementsByTagName(b)[0];
      a.getElementById(c) || (a = a.createElement(b), a.id = c, a.src = "https://api.gamemonetize.com/video.js", d.parentNode.insertBefore(a, d))
   })(document, "script", "gamemonetize-video-api"); 
</script>  

  // Strategy Games
  { id: '42', title: 'Empire Builder', category: 'strategy', thumbnail: 'https://picsum.photos/seed/empire/400/300', plays: '2.8M', rating: 4.7, isFeatured: true, gameUrl: 'https://playhop.com/embed/goodgame-empire' },
  { id: '43', title: 'Tower Defense', category: 'strategy', thumbnail: 'https://picsum.photos/seed/tower/400/300', plays: '3.5M', rating: 4.8, isTrending: true, gameUrl: 'https://playhop.com/embed/tower-defense' },
  { id: '44', title: 'Chess Master', category: 'strategy', thumbnail: 'https://picsum.photos/seed/chess/400/300', plays: '2.1M', rating: 4.6, gameUrl: 'https://www.chess.com/play/computer' },
  { id: '45', title: 'War Strategy', category: 'strategy', thumbnail: 'https://picsum.photos/seed/war/400/300', plays: '1.9M', rating: 4.5, isNew: true, gameUrl: 'https://playhop.com/embed/war-simulator' },
  { id: '46', title: 'City Builder', category: 'strategy', thumbnail: 'https://picsum.photos/seed/city/400/300', plays: '2.4M', rating: 4.6, gameUrl: 'https://playhop.com/embed/city-builder-3d' },
  { id: '47', title: 'Kingdom Wars', category: 'strategy', thumbnail: 'https://picsum.photos/seed/kingdom/400/300', plays: '1.6M', rating: 4.4, gameUrl: 'https://playhop.com/embed/kingdom-wars' },
  <div id="gamemonetize-video"></div>
<script type="text/javascript">
   window.VIDEO_OPTIONS = {
       gameid : "4kci7og3klgj0ivy2wz3gdvd9dth5e7n",
       width  : "100%",
       height : "480px",
       color  : "#3f007e",
       getAds  : "false"
   };
   (function (a, b, c) {
      var d = a.getElementsByTagName(b)[0];
      a.getElementById(c) || (a = a.createElement(b), a.id = c, a.src = "https://api.gamemonetize.com/video.js", d.parentNode.insertBefore(a, d))
   })(document, "script", "gamemonetize-video-api"); 
</script>  

  // Multiplayer Games - Using actual .io games
  { id: '48', title: 'Battle Royale', category: 'multiplayer', thumbnail: 'https://picsum.photos/seed/royale/400/300', plays: '8.5M', rating: 4.9, isFeatured: true, isTrending: true, gameUrl: 'https://1v1.lol' },
  { id: '49', title: 'Team Fight', category: 'multiplayer', thumbnail: 'https://picsum.photos/seed/team/400/300', plays: '5.2M', rating: 4.8, gameUrl: 'https://shellshock.io' },
  { id: '50', title: 'Capture Flag', category: 'multiplayer', thumbnail: 'https://picsum.photos/seed/flag/400/300', plays: '3.1M', rating: 4.6, isNew: true, gameUrl: 'https://krunker.io' },
  { id: '51', title: 'Co-op Quest', category: 'multiplayer', thumbnail: 'https://picsum.photos/seed/coop/400/300', plays: '2.7M', rating: 4.5, gameUrl: 'https://playhop.com/embed/fireboy-and-watergirl-2' },
  { id: '52', title: 'Arena Clash', category: 'multiplayer', thumbnail: 'https://picsum.photos/seed/clash/400/300', plays: '4.3M', rating: 4.7, gameUrl: 'https://ev.io' },
  <div id="gamemonetize-video"></div>
<script type="text/javascript">
   window.VIDEO_OPTIONS = {
       gameid : "4kci7og3klgj0ivy2wz3gdvd9dth5e7n",
       width  : "100%",
       height : "480px",
       color  : "#3f007e",
       getAds  : "false"
   };
   (function (a, b, c) {
      var d = a.getElementsByTagName(b)[0];
      a.getElementById(c) || (a = a.createElement(b), a.id = c, a.src = "https://api.gamemonetize.com/video.js", d.parentNode.insertBefore(a, d))
   })(document, "script", "gamemonetize-video-api"); 
</script>  

  // Arcade Games
  { id: '53', title: 'Pac Runner', category: 'arcade', thumbnail: 'https://picsum.photos/seed/pac/400/300', plays: '3.9M', rating: 4.7, isFeatured: true, gameUrl: 'https://playhop.com/embed/pacman' },
  { id: '54', title: 'Space Invader', category: 'arcade', thumbnail: 'https://picsum.photos/seed/invader/400/300', plays: '2.8M', rating: 4.6, gameUrl: 'https://playhop.com/embed/space-invaders' },
  { id: '55', title: 'Brick Break', category: 'arcade', thumbnail: 'https://picsum.photos/seed/brick/400/300', plays: '3.2M', rating: 4.5, isNew: true, gameUrl: 'https://playhop.com/embed/brick-breaker' },
  { id: '56', title: 'Pinball Pro', category: 'arcade', thumbnail: 'https://picsum.photos/seed/pinball/400/300', plays: '1.9M', rating: 4.4, gameUrl: 'https://playhop.com/embed/pinball' },
  { id: '57', title: 'Retro Jump', category: 'arcade', thumbnail: 'https://picsum.photos/seed/retro/400/300', plays: '2.4M', rating: 4.6, isTrending: true, gameUrl: 'https://playhop.com/embed/geometry-dash' },
  <div id="gamemonetize-video"></div>
<script type="text/javascript">
   window.VIDEO_OPTIONS = {
       gameid : "4kci7og3klgj0ivy2wz3gdvd9dth5e7n",
       width  : "100%",
       height : "480px",
       color  : "#3f007e",
       getAds  : "false"
   };
   (function (a, b, c) {
      var d = a.getElementsByTagName(b)[0];
      a.getElementById(c) || (a = a.createElement(b), a.id = c, a.src = "https://api.gamemonetize.com/video.js", d.parentNode.insertBefore(a, d))
   })(document, "script", "gamemonetize-video-api"); 
</script>  

  // .io Games - These work well as they're designed for embedding
  { id: '58', title: 'Slither.io', category: 'io', thumbnail: 'https://picsum.photos/seed/slither/400/300', plays: '12.5M', rating: 4.9, isFeatured: true, isTrending: true, gameUrl: 'https://slither.io' },
  { id: '59', title: 'Agar.io', category: 'io', thumbnail: 'https://picsum.photos/seed/agar/400/300', plays: '10.2M', rating: 4.8, gameUrl: 'https://agar.io' },
  { id: '60', title: 'Paper.io', category: 'io', thumbnail: 'https://picsum.photos/seed/paper/400/300', plays: '7.8M', rating: 4.7, gameUrl: 'https://paper-io.com' },
  { id: '61', title: 'Hole.io', category: 'io', thumbnail: 'https://picsum.photos/seed/hole/400/300', plays: '6.5M', rating: 4.6, isNew: true, gameUrl: 'https://hole-io.com' },
  { id: '62', title: 'Diep.io', category: 'io', thumbnail: 'https://picsum.photos/seed/diep/400/300', plays: '5.1M', rating: 4.5, gameUrl: 'https://diep.io' },
  <div id="gamemonetize-video"></div>
<script type="text/javascript">
   window.VIDEO_OPTIONS = {
       gameid : "4kci7og3klgj0ivy2wz3gdvd9dth5e7n",
       width  : "100%",
       height : "480px",
       color  : "#3f007e",
       getAds  : "false"
   };
   (function (a, b, c) {
      var d = a.getElementsByTagName(b)[0];
      a.getElementById(c) || (a = a.createElement(b), a.id = c, a.src = "https://api.gamemonetize.com/video.js", d.parentNode.insertBefore(a, d))
   })(document, "script", "gamemonetize-video-api"); 
</script>  

  // Horror Games
  { id: '63', title: 'Haunted House', category: 'horror', thumbnail: 'https://picsum.photos/seed/haunted/400/300', plays: '2.1M', rating: 4.6, isFeatured: true, gameUrl: 'https://playhop.com/embed/granny' },
  { id: '64', title: 'Night Terror', category: 'horror', thumbnail: 'https://picsum.photos/seed/terror/400/300', plays: '1.8M', rating: 4.5, isTrending: true, gameUrl: 'https://playhop.com/embed/five-nights-at-freddys' },
  { id: '65', title: 'Scary Hospital', category: 'horror', thumbnail: 'https://picsum.photos/seed/hospital/400/300', plays: '1.5M', rating: 4.4, isNew: true, gameUrl: 'https://playhop.com/embed/horror-hospital' },
  { id: '66', title: 'Ghost Hunt', category: 'horror', thumbnail: 'https://picsum.photos/seed/ghost/400/300', plays: '1.2M', rating: 4.3, gameUrl: 'https://playhop.com/embed/ghost-hunter' },
  { id: '67', title: 'Dark Forest', category: 'horror', thumbnail: 'https://picsum.photos/seed/forest/400/300', plays: '980K', rating: 4.2, gameUrl: 'https://playhop.com/embed/slenderman' },
  <div id="gamemonetize-video"></div>
<script type="text/javascript">
   window.VIDEO_OPTIONS = {
       gameid : "4kci7og3klgj0ivy2wz3gdvd9dth5e7n",
       width  : "100%",
       height : "480px",
       color  : "#3f007e",
       getAds  : "false"
   };
   (function (a, b, c) {
      var d = a.getElementsByTagName(b)[0];
      a.getElementById(c) || (a = a.createElement(b), a.id = c, a.src = "https://api.gamemonetize.com/video.js", d.parentNode.insertBefore(a, d))
   })(document, "script", "gamemonetize-video-api"); 
</script>  

  // Simulation Games
  { id: '68', title: 'Flight Sim', category: 'simulation', thumbnail: 'https://picsum.photos/seed/flight/400/300', plays: '2.9M', rating: 4.7, isFeatured: true, gameUrl: 'https://www.geo-fs.com/geofs.php' },
  { id: '69', title: 'Farm Life', category: 'simulation', thumbnail: 'https://picsum.photos/seed/farm/400/300', plays: '3.5M', rating: 4.8, isTrending: true, gameUrl: 'https://playhop.com/embed/goodgame-big-farm' },
  { id: '70', title: 'Train Driver', category: 'simulation', thumbnail: 'https://picsum.photos/seed/train/400/300', plays: '1.8M', rating: 4.5, gameUrl: 'https://playhop.com/embed/train-simulator' },
  { id: '71', title: 'Cooking Sim', category: 'simulation', thumbnail: 'https://picsum.photos/seed/cooking/400/300', plays: '2.4M', rating: 4.6, isNew: true, gameUrl: 'https://playhop.com/embed/cooking-fever' },
  { id: '72', title: 'Business Tycoon', category: 'simulation', thumbnail: 'https://picsum.photos/seed/business/400/300', plays: '2.1M', rating: 4.5, gameUrl: 'https://playhop.com/embed/idle-startup-tycoon' },
  <div id="gamemonetize-video"></div>
<script type="text/javascript">
   window.VIDEO_OPTIONS = {
       gameid : "4kci7og3klgj0ivy2wz3gdvd9dth5e7n",
       width  : "100%",
       height : "480px",
       color  : "#3f007e",
       getAds  : "false"
   };
   (function (a, b, c) {
      var d = a.getElementsByTagName(b)[0];
      a.getElementById(c) || (a = a.createElement(b), a.id = c, a.src = "https://api.gamemonetize.com/video.js", d.parentNode.insertBefore(a, d))
   })(document, "script", "gamemonetize-video-api"); 
</script>  

  // Fighting Games
  { id: '73', title: 'Street Fighter', category: 'fighting', thumbnail: 'https://picsum.photos/seed/fighter/400/300', plays: '4.2M', rating: 4.8, isFeatured: true, isTrending: true, gameUrl: 'https://playhop.com/embed/street-fighter-2' },
  { id: '74', title: 'Mortal Match', category: 'fighting', thumbnail: 'https://picsum.photos/seed/mortal/400/300', plays: '3.5M', rating: 4.7, gameUrl: 'https://playhop.com/embed/mortal-kombat-karnage' },
  { id: '75', title: 'Tekken Style', category: 'fighting', thumbnail: 'https://picsum.photos/seed/tekken/400/300', plays: '2.8M', rating: 4.6, isNew: true, gameUrl: 'https://playhop.com/embed/mutant-fighting-cup-2' },
  { id: '76', title: 'Wrestling Pro', category: 'fighting', thumbnail: 'https://picsum.photos/seed/wrestling/400/300', plays: '1.9M', rating: 4.4, gameUrl: 'https://playhop.com/embed/wrestling-random' },
  { id: '77', title: 'Kung Fu Master', category: 'fighting', thumbnail: 'https://picsum.photos/seed/kungfu/400/300', plays: '2.3M', rating: 4.5, gameUrl: 'https://playhop.com/embed/ultimate-hero-clash' },
  <div id="gamemonetize-video"></div>
<script type="text/javascript">
   window.VIDEO_OPTIONS = {
       gameid : "4kci7og3klgj0ivy2wz3gdvd9dth5e7n",
       width  : "100%",
       height : "480px",
       color  : "#3f007e",
       getAds  : "false"
   };
   (function (a, b, c) {
      var d = a.getElementsByTagName(b)[0];
      a.getElementById(c) || (a = a.createElement(b), a.id = c, a.src = "https://api.gamemonetize.com/video.js", d.parentNode.insertBefore(a, d))
   })(document, "script", "gamemonetize-video-api"); 
</script>  

  // Casual Games
  { id: '78', title: 'Candy Crush', category: 'casual', thumbnail: 'https://picsum.photos/seed/candy/400/300', plays: '15.2M', rating: 4.9, isFeatured: true, isTrending: true, gameUrl: 'https://playhop.com/embed/candy-riddles' },
  { id: '79', title: 'Bubble Pop', category: 'casual', thumbnail: 'https://picsum.photos/seed/bubble/400/300', plays: '8.5M', rating: 4.7, gameUrl: 'https://playhop.com/embed/bubble-shooter' },
  { id: '80', title: 'Fruit Ninja', category: 'casual', thumbnail: 'https://picsum.photos/seed/fruit/400/300', plays: '6.2M', rating: 4.6, isNew: true, gameUrl: 'https://playhop.com/embed/fruit-ninja' },
  { id: '81', title: 'Jewel Match', category: 'casual', thumbnail: 'https://picsum.photos/seed/jewel/400/300', plays: '5.8M', rating: 4.5, gameUrl: 'https://playhop.com/embed/jewel-shuffle' },
  { id: '82', title: 'Solitaire', category: 'casual', thumbnail: 'https://picsum.photos/seed/solitaire/400/300', plays: '7.1M', rating: 4.6, gameUrl: 'https://www.solitr.com/' },
  { id: '83', title: 'Mahjong', category: 'casual', thumbnail: 'https://picsum.photos/seed/mahjong/400/300', plays: '4.3M', rating: 4.5, gameUrl: 'https://playhop.com/embed/mahjongg-solitaire' },
  { id: '84', title: 'Word Search', category: 'casual', thumbnail: 'https://picsum.photos/seed/word/400/300', plays: '3.9M', rating: 4.4, gameUrl: 'https://playhop.com/embed/word-search' },
  <div id="gamemonetize-video"></div>
<script type="text/javascript">
   window.VIDEO_OPTIONS = {
       gameid : "4kci7og3klgj0ivy2wz3gdvd9dth5e7n",
       width  : "100%",
       height : "480px",
       color  : "#3f007e",
       getAds  : "false"
   };
   (function (a, b, c) {
      var d = a.getElementsByTagName(b)[0];
      a.getElementById(c) || (a = a.createElement(b), a.id = c, a.src = "https://api.gamemonetize.com/video.js", d.parentNode.insertBefore(a, d))
   })(document, "script", "gamemonetize-video-api"); 
</script>  

  // More Games
  { id: '85', title: 'Stickman Run', category: 'action', thumbnail: 'https://picsum.photos/seed/stick/400/300', plays: '4.1M', rating: 4.7, gameUrl: 'https://playhop.com/embed/vex-4' },
  { id: '86', title: 'Pixel Warrior', category: 'action', thumbnail: 'https://picsum.photos/seed/pixel/400/300', plays: '2.3M', rating: 4.5, isNew: true, gameUrl: 'https://playhop.com/embed/pixel-warfare' },
  { id: '87', title: 'Mountain Climb', category: 'adventure', thumbnail: 'https://picsum.photos/seed/mountain/400/300', plays: '1.8M', rating: 4.4, gameUrl: 'https://playhop.com/embed/hill-climb-racing' },
  { id: '88', title: 'Cave Explorer', category: 'adventure', thumbnail: 'https://picsum.photos/seed/cave/400/300', plays: '1.5M', rating: 4.3, gameUrl: 'https://playhop.com/embed/spelunky' },
  { id: '89', title: 'Sudoku Pro', category: 'puzzle', thumbnail: 'https://picsum.photos/seed/sudoku/400/300', plays: '3.2M', rating: 4.6, gameUrl: 'https://sudoku.com/' },
  { id: '90', title: 'Jigsaw World', category: 'puzzle', thumbnail: 'https://picsum.photos/seed/jigsaw/400/300', plays: '2.1M', rating: 4.4, gameUrl: 'https://playhop.com/embed/jigsaw-puzzle' },
  { id: '91', title: 'Bike Racer', category: 'racing', thumbnail: 'https://picsum.photos/seed/bike/400/300', plays: '2.7M', rating: 4.5, gameUrl: 'https://playhop.com/embed/bike-racing' },
  { id: '92', title: 'Kart Racing', category: 'racing', thumbnail: 'https://picsum.photos/seed/kart/400/300', plays: '3.4M', rating: 4.7, isTrending: true, gameUrl: 'https://smashkarts.io' },
  { id: '93', title: 'Cricket World', category: 'sports', thumbnail: 'https://picsum.photos/seed/cricket/400/300', plays: '2.8M', rating: 4.5, gameUrl: 'https://playhop.com/embed/cricket-world-cup' },
  { id: '94', title: 'Volleyball', category: 'sports', thumbnail: 'https://picsum.photos/seed/volley/400/300', plays: '1.2M', rating: 4.3, gameUrl: 'https://playhop.com/embed/volley-random' },
  { id: '95', title: 'Zombie Shooter', category: 'shooting', thumbnail: 'https://picsum.photos/seed/zombieshoot/400/300', plays: '3.9M', rating: 4.7, isNew: true, gameUrl: 'https://playhop.com/embed/dead-zed' },
  { id: '96', title: 'Medieval War', category: 'strategy', thumbnail: 'https://picsum.photos/seed/medieval/400/300', plays: '1.8M', rating: 4.4, gameUrl: 'https://playhop.com/embed/epic-battle-simulator' },
  { id: '97', title: 'Among Us Style', category: 'multiplayer', thumbnail: 'https://picsum.photos/seed/among/400/300', plays: '6.8M', rating: 4.8, isTrending: true, gameUrl: 'https://betrayal.io' },
  { id: '98', title: 'Snake Game', category: 'arcade', thumbnail: 'https://picsum.photos/seed/snake/400/300', plays: '4.5M', rating: 4.6, gameUrl: 'https://playsnake.org/' },
  { id: '99', title: 'Skribbl.io', category: 'io', thumbnail: 'https://picsum.photos/seed/skribbl/400/300', plays: '5.8M', rating: 4.7, isNew: true, gameUrl: 'https://skribbl.io' },
  { id: '100', title: 'Escape Room', category: 'horror', thumbnail: 'https://picsum.photos/seed/escape/400/300', plays: '2.3M', rating: 4.5, gameUrl: 'https://playhop.com/embed/escape-room' },
]
<div id="gamemonetize-video"></div>
<script type="text/javascript">
   window.VIDEO_OPTIONS = {
       gameid : "4kci7og3klgj0ivy2wz3gdvd9dth5e7n",
       width  : "100%",
       height : "480px",
       color  : "#3f007e",
       getAds  : "false"
   };
   (function (a, b, c) {
      var d = a.getElementsByTagName(b)[0];
      a.getElementById(c) || (a = a.createElement(b), a.id = c, a.src = "https://api.gamemonetize.com/video.js", d.parentNode.insertBefore(a, d))
   })(document, "script", "gamemonetize-video-api"); 
</script>  

export function getGamesByCategory(categoryId: string): Game[] {
  if (categoryId === 'all') return games
  return games.filter(game => game.category === categoryId)
}

export function getFeaturedGames(): Game[] {
  return games.filter(game => game.isFeatured)
}

export function getTrendingGames(): Game[] {
  return games.filter(game => game.isTrending)
}

export function getNewGames(): Game[] {
  return games.filter(game => game.isNew)
}

export function searchGames(query: string): Game[] {
  const lowercaseQuery = query.toLowerCase()
  return games.filter(game => 
    game.title.toLowerCase().includes(lowercaseQuery) ||
    game.category.toLowerCase().includes(lowercaseQuery)
  )
}

export function getGameById(id: string): Game | undefined {
  return games.find(game => game.id === id)
}
