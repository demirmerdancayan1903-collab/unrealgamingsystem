<?php
require_once 'assets/includes/core.php';

if (isset($_GET["LoadedGamesNum"]) && isset($_GET["num"])) {
    $from = (int) $_GET["LoadedGamesNum"];
    $num = (int) $_GET["num"];

    $slider_data = $GameMonetizeConnect->query("SELECT * FROM ".SLIDERS." ORDER BY ordering ASC LIMIT $from, $num");
    $index = 1;
    while ($slider = $slider_data->fetch_array()) {
        if($slider['type'] == 'new'){
            // new games
            $newGames_query = $GameMonetizeConnect->query("SELECT * FROM ".GAMES." WHERE published='1' ORDER BY date_added desc, featured_sorting desc LIMIT 20");

            $data = "<div class='mb-6'>
            <div class=\"flex justify-between mb-2\">
                <div class=\"flex items-center text-sm font-bold text-white\">
                    New Games
                    <a href=\"/new-games\" class='ml-4 text-xs text-violet-500'>View more</a>
                </div>
                <div class=\"cursor-pointer splide-arrow-right lg:hidden\">
                    <svg class=\"size-5 fill-white\" viewBox=\"0 0 24 24\" focusable=\"false\" aria-hidden=\"true\" class=\"GameCarousel_doubleArrow__BGrRW double-arrow css-6qu7l6\"><path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M3.25757 2.33007C3.62757 1.92005 4.2599 1.88759 4.66993 2.25759L12.9814 9.75759C14.3395 10.9831 14.3395 13.0169 12.9814 14.2424L4.66993 21.7424C4.2599 22.1124 3.62757 22.08 3.25757 21.6699C2.88758 21.2599 2.92003 20.6276 3.33006 20.2576L11.6415 12.7576C12.1195 12.3263 12.1195 11.6737 11.6415 11.2424L3.33006 3.74243C2.92003 3.37243 2.88758 2.7401 3.25757 2.33007Z\"></path><path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M11.2576 2.33007C11.6276 1.92005 12.2599 1.88759 12.6699 2.25759L20.9814 9.75759C22.3395 10.9831 22.3395 13.0169 20.9814 14.2424L12.6699 21.7424C12.2599 22.1124 11.6276 22.08 11.2576 21.6699C10.8876 21.2599 10.92 20.6276 11.3301 20.2576L19.6415 12.7576C20.1195 12.3263 20.1195 11.6737 19.6415 11.2424L11.3301 3.74243C10.92 3.37243 10.8876 2.7401 11.2576 2.33007Z\"></path></svg>
                </div>
            </div>
            <div class=\"relative group\"><div class=\"flex max-w-full space-x-2 overflow-x-auto no-scrollbar splide-items-container\">";
                    $leadCard = getHomeSliderLeadCardHtml('New Games', siteUrl() . '/new-games', 'new-games');

            if ($leadCard !== '') {
                $data .= $leadCard;
            }
            $leadCard = getHomeSliderLeadCardHtml('Best Games', siteUrl() . '/best-games', 'best-games');

            if ($leadCard !== '') {
                $data .= $leadCard;
            }
            while ($newGames = $newGames_query->fetch_array()) {
                preg_match("/\/([a-zA-Z0-9]+)\//", $newGames['image'], $matches);
                $baseImagePath = $_SERVER['DOCUMENT_ROOT'] . '/games-image/' . $matches[1] . '/250x150.webp';
                if (file_exists($baseImagePath)) {
                    $newGames_image = str_replace($_SERVER['DOCUMENT_ROOT'], '', $baseImagePath);
                } else {
                    $newGames_image = $newGames['image'];
                }

                preg_match('/([^\/]+\.mp4)$/', $newGames['wt_video'], $matches);
                $baseVideoThumbPath = $_SERVER['DOCUMENT_ROOT'] . '/games-thumb-video/' . $matches[1];
                if (file_exists($baseVideoThumbPath)) {
                    $newGames_wt_video = str_replace($_SERVER['DOCUMENT_ROOT'], '', $baseVideoThumbPath);
                } else {
                    $newGames_wt_video = $newGames['wt_video'];
                }

                $data .= "<a href='" . siteUrl().'/game/' . slugify($newGames['name']) . "' class='relative w-32 shrink-0 lg:w-52' aria-label='" .$newGames['name'] . "' data-wt-video='". $newGames_wt_video . "'>
                    <img src='". $newGames_image . "' alt='" . $newGames['name'] . " image' class='object-cover w-full rounded-lg' loading='lazy'>
                </a>";
            }
            
            $data .= "</div><div class=\"absolute top-0 right-0 z-20 items-center justify-center hidden w-12 h-full pl-2 text-2xl text-white bg-black cursor-pointer splide-arrow-right bg-opacity-80\">
                        <i class=\"fa fa-chevron-right\" aria-hidden=\"true\"></i>
                    </div>
                    <div class=\"absolute top-0 left-0 z-20 items-center justify-center hidden w-12 h-full pl-2 text-2xl text-white bg-black cursor-pointer splide-arrow-left bg-opacity-80\">
                        <i class=\"fa fa-chevron-left\" aria-hidden=\"true\"></i>
                    </div>
                </div>
            </div>";

            echo $data;
        }

        if($slider['type'] == 'best'){
            $games_query = $GameMonetizeConnect->query("SELECT * FROM ".GAMES." WHERE published='1' ORDER BY plays DESC LIMIT 20");

            $data = "<div class='mb-6'>
            <div class=\"flex justify-between mb-2\">
                <div class=\"flex items-center text-sm font-bold text-white\">
                    Best Games
                    <a href=\"/best-games\" class='ml-4 text-xs text-violet-500'>View more</a>
                </div>
                <div class=\"cursor-pointer splide-arrow-right lg:hidden\">
                    <svg class=\"size-5 fill-white\" viewBox=\"0 0 24 24\" focusable=\"false\" aria-hidden=\"true\" class=\"GameCarousel_doubleArrow__BGrRW double-arrow css-6qu7l6\"><path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M3.25757 2.33007C3.62757 1.92005 4.2599 1.88759 4.66993 2.25759L12.9814 9.75759C14.3395 10.9831 14.3395 13.0169 12.9814 14.2424L4.66993 21.7424C4.2599 22.1124 3.62757 22.08 3.25757 21.6699C2.88758 21.2599 2.92003 20.6276 3.33006 20.2576L11.6415 12.7576C12.1195 12.3263 12.1195 11.6737 11.6415 11.2424L3.33006 3.74243C2.92003 3.37243 2.88758 2.7401 3.25757 2.33007Z\"></path><path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M11.2576 2.33007C11.6276 1.92005 12.2599 1.88759 12.6699 2.25759L20.9814 9.75759C22.3395 10.9831 22.3395 13.0169 20.9814 14.2424L12.6699 21.7424C12.2599 22.1124 11.6276 22.08 11.2576 21.6699C10.8876 21.2599 10.92 20.6276 11.3301 20.2576L19.6415 12.7576C20.1195 12.3263 20.1195 11.6737 19.6415 11.2424L11.3301 3.74243C10.92 3.37243 10.8876 2.7401 11.2576 2.33007Z\"></path></svg>
                </div>
            </div>
                        <div class=\"relative group\"><div class=\"flex max-w-full space-x-2 overflow-x-auto no-scrollbar splide-items-container\">";

            $categorySlug = !empty($category_data['category_pilot']) ? $category_data['category_pilot'] : slugify($category_data['name']);
            $sliderImg = getSliderCardImage($category_data['name'], $categorySlug);

            if ($sliderImg != '') {
                $categoryCountQuery = $GameMonetizeConnect->query("SELECT COUNT(*) FROM " . GAMES . " WHERE category='" . (int)$category_id . "' AND published='1'");
                $categoryCount = $categoryCountQuery ? (int)$categoryCountQuery->fetch_array()[0] : 0;

                $data .= "<a href='" . siteUrl() . "/category/" . $categorySlug . "' class='home-slider-lead-card' title='" . htmlspecialchars($category_data['name'], ENT_QUOTES) . "'>
                    <img src='" . $sliderImg . "' alt='" . htmlspecialchars($category_data['name'], ENT_QUOTES) . " image' width='728' height='312' loading='lazy' decoding='async'>
                    <span class='home-slider-lead-card-text'>
                        <strong>" . htmlspecialchars($category_data['name'], ENT_QUOTES) . "</strong>
                        <small>" . number_format($categoryCount) . " games</small>
                    </span>
                </a>";
            }
        
            while ($newGames = $games_query->fetch_array()) {
                preg_match("/\/([a-zA-Z0-9]+)\//", $newGames['image'], $matches);
                $baseImagePath = $_SERVER['DOCUMENT_ROOT'] . '/games-image/' . $matches[1] . '/250x150.webp';
                if (file_exists($baseImagePath)) {
                    $newGames_image = str_replace($_SERVER['DOCUMENT_ROOT'], '', $baseImagePath);
                } else {
                    $newGames_image = $newGames['image'];
                }

                preg_match('/([^\/]+\.mp4)$/', $newGames['wt_video'], $matches);
                $baseVideoThumbPath = $_SERVER['DOCUMENT_ROOT'] . '/games-thumb-video/' . $matches[1];
                if (file_exists($baseVideoThumbPath)) {
                    $newGames_wt_video = str_replace($_SERVER['DOCUMENT_ROOT'], '', $baseVideoThumbPath);
                } else {
                    $newGames_wt_video = $newGames['wt_video'];
                }

                $data .= "<a href='" . siteUrl().'/game/' . slugify($newGames['name']) . "' class='relative w-32 shrink-0 lg:w-52' aria-label='" .$newGames['name'] . "' data-wt-video='". $newGames_wt_video . "'>
                    <img src='". $newGames_image . "' alt='" . $newGames['name'] . " image' class='object-cover w-full rounded-lg' loading='lazy'>
                </a>";
            }
            
            $data .= "</div><div class=\"absolute top-0 right-0 z-20 items-center justify-center hidden w-12 h-full pl-2 text-2xl text-white bg-black cursor-pointer splide-arrow-right bg-opacity-80\">
                        <i class=\"fa fa-chevron-right\" aria-hidden=\"true\"></i>
                    </div>
                    <div class=\"absolute top-0 left-0 z-20 items-center justify-center hidden w-12 h-full pl-2 text-2xl text-white bg-black cursor-pointer splide-arrow-left bg-opacity-80\">
                        <i class=\"fa fa-chevron-left\" aria-hidden=\"true\"></i>
                    </div>
                </div>
            </div>";

            echo $data;
        }
        
        if($slider['type'] == 'featured'){
            $games_query = $GameMonetizeConnect->query("SELECT * FROM ".GAMES." WHERE published='1' AND featured='1' ORDER BY date_added DESC LIMIT 20");
        
            $data = "<div class='mb-6'>
            <div class=\"flex justify-between mb-2\">
                <div class=\"flex items-center text-sm font-bold text-white\">
                    Featured Games
                    <a href=\"/fetured-games\" class='ml-4 text-xs text-violet-500'>View more</a>
                </div>
                <div class=\"cursor-pointer splide-arrow-right lg:hidden\">
                    <svg class=\"size-5 fill-white\" viewBox=\"0 0 24 24\" focusable=\"false\" aria-hidden=\"true\" class=\"GameCarousel_doubleArrow__BGrRW double-arrow css-6qu7l6\"><path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M3.25757 2.33007C3.62757 1.92005 4.2599 1.88759 4.66993 2.25759L12.9814 9.75759C14.3395 10.9831 14.3395 13.0169 12.9814 14.2424L4.66993 21.7424C4.2599 22.1124 3.62757 22.08 3.25757 21.6699C2.88758 21.2599 2.92003 20.6276 3.33006 20.2576L11.6415 12.7576C12.1195 12.3263 12.1195 11.6737 11.6415 11.2424L3.33006 3.74243C2.92003 3.37243 2.88758 2.7401 3.25757 2.33007Z\"></path><path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M11.2576 2.33007C11.6276 1.92005 12.2599 1.88759 12.6699 2.25759L20.9814 9.75759C22.3395 10.9831 22.3395 13.0169 20.9814 14.2424L12.6699 21.7424C12.2599 22.1124 11.6276 22.08 11.2576 21.6699C10.8876 21.2599 10.92 20.6276 11.3301 20.2576L19.6415 12.7576C20.1195 12.3263 20.1195 11.6737 19.6415 11.2424L11.3301 3.74243C10.92 3.37243 10.8876 2.7401 11.2576 2.33007Z\"></path></svg>
                </div>
            </div>
                        <div class=\"relative group\"><div class=\"flex max-w-full space-x-2 overflow-x-auto no-scrollbar splide-items-container\">";

            $tagSlug = !empty($tags_data['url']) ? $tags_data['url'] : slugify($tags_data['name']);
            $sliderImg = getSliderCardImage($tags_data['name'], $tagSlug);

            if ($sliderImg != '') {
                $data .= "<a href='" . siteUrl() . "/tag/" . $tagSlug . "' class='home-slider-lead-card' title='" . htmlspecialchars($tags_data['name'], ENT_QUOTES) . "'>
                    <img src='" . $sliderImg . "' alt='" . htmlspecialchars($tags_data['name'], ENT_QUOTES) . " image' width='728' height='312' loading='lazy' decoding='async'>
                    <span class='home-slider-lead-card-text'>
                        <strong>" . htmlspecialchars($tags_data['name'], ENT_QUOTES) . "</strong>
                    </span>
                </a>";
            }
        
            while ($newGames = $games_query->fetch_array()) {
                preg_match("/\/([a-zA-Z0-9]+)\//", $newGames['image'], $matches);
                $baseImagePath = $_SERVER['DOCUMENT_ROOT'] . '/games-image/' . $matches[1] . '/250x150.webp';
                if (file_exists($baseImagePath)) {
                    $newGames_image = str_replace($_SERVER['DOCUMENT_ROOT'], '', $baseImagePath);
                } else {
                    $newGames_image = $newGames['image'];
                }

                preg_match('/([^\/]+\.mp4)$/', $newGames['wt_video'], $matches);
                $baseVideoThumbPath = $_SERVER['DOCUMENT_ROOT'] . '/games-thumb-video/' . $matches[1];
                if (file_exists($baseVideoThumbPath)) {
                    $newGames_wt_video = str_replace($_SERVER['DOCUMENT_ROOT'], '', $baseVideoThumbPath);
                } else {
                    $newGames_wt_video = $newGames['wt_video'];
                }

                $data .= "<a href='" . siteUrl().'/game/' . slugify($newGames['name']) . "' class='relative w-32 shrink-0 lg:w-52' aria-label='" .$newGames['name'] . "' data-wt-video='". $newGames_wt_video . "'>
                    <img src='". $newGames_image . "' alt='" . $newGames['name'] . " image' class='object-cover w-full rounded-lg' loading='lazy'>
                </a>";
            }
            
            $data .= "</div><div class=\"absolute top-0 right-0 z-20 items-center justify-center hidden w-12 h-full pl-2 text-2xl text-white bg-black cursor-pointer splide-arrow-right bg-opacity-80\">
                        <i class=\"fa fa-chevron-right\" aria-hidden=\"true\"></i>
                    </div>
                    <div class=\"absolute top-0 left-0 z-20 items-center justify-center hidden w-12 h-full pl-2 text-2xl text-white bg-black cursor-pointer splide-arrow-left bg-opacity-80\">
                        <i class=\"fa fa-chevron-left\" aria-hidden=\"true\"></i>
                    </div>
                </div>
            </div>";

            echo $data;
        }

        if($slider['type'] == 'played'){
            // new games
            $all_splide_item = "";
            $fav = explode(',,', $_COOKIE['playedgames']);
            // remove empty values from $fav
            if (strlen($_COOKIE['playedgames']) > 0) {
                foreach ($fav as $game_id) {
                    $resultset[] = $game_id;
                }
                $string = implode(",", $resultset);
                $str = trim($string, ",");
                $comma_separated = rtrim($str, ',');
                $games_query = $GameMonetizeConnect->query("SELECT * FROM " . GAMES . " where `game_id` IN (" . $comma_separated . ") order by date_added DESC LIMIT 20");
            
                $data = "<div class='mb-6'>
                <div class=\"flex justify-between mb-2\">
                    <div class=\"flex items-center text-sm font-bold text-white\">
                        Played Games
                        <a href=\"/played-games\" class='ml-4 text-xs text-violet-500'>View more</a>
                    </div>
                    <div class=\"cursor-pointer splide-arrow-right lg:hidden\">
                        <svg class=\"size-5 fill-white\" viewBox=\"0 0 24 24\" focusable=\"false\" aria-hidden=\"true\" class=\"GameCarousel_doubleArrow__BGrRW double-arrow css-6qu7l6\"><path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M3.25757 2.33007C3.62757 1.92005 4.2599 1.88759 4.66993 2.25759L12.9814 9.75759C14.3395 10.9831 14.3395 13.0169 12.9814 14.2424L4.66993 21.7424C4.2599 22.1124 3.62757 22.08 3.25757 21.6699C2.88758 21.2599 2.92003 20.6276 3.33006 20.2576L11.6415 12.7576C12.1195 12.3263 12.1195 11.6737 11.6415 11.2424L3.33006 3.74243C2.92003 3.37243 2.88758 2.7401 3.25757 2.33007Z\"></path><path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M11.2576 2.33007C11.6276 1.92005 12.2599 1.88759 12.6699 2.25759L20.9814 9.75759C22.3395 10.9831 22.3395 13.0169 20.9814 14.2424L12.6699 21.7424C12.2599 22.1124 11.6276 22.08 11.2576 21.6699C10.8876 21.2599 10.92 20.6276 11.3301 20.2576L19.6415 12.7576C20.1195 12.3263 20.1195 11.6737 19.6415 11.2424L11.3301 3.74243C10.92 3.37243 10.8876 2.7401 11.2576 2.33007Z\"></path></svg>
                    </div>
                </div>
                <div class=\"relative group\"><div class=\"flex max-w-full space-x-2 overflow-x-auto no-scrollbar splide-items-container\">";
            
                while ($newGames = $games_query->fetch_array()) {
                    preg_match("/\/([a-zA-Z0-9]+)\//", $newGames['image'], $matches);
                    $baseImagePath = $_SERVER['DOCUMENT_ROOT'] . '/games-image/' . $matches[1] . '/250x150.webp';
                    if (file_exists($baseImagePath)) {
                        $newGames_image = str_replace($_SERVER['DOCUMENT_ROOT'], '', $baseImagePath);
                    } else {
                        $newGames_image = $newGames['image'];
                    }

                    preg_match('/([^\/]+\.mp4)$/', $newGames['wt_video'], $matches);
                    $baseVideoThumbPath = $_SERVER['DOCUMENT_ROOT'] . '/games-thumb-video/' . $matches[1];
                    if (file_exists($baseVideoThumbPath)) {
                        $newGames_wt_video = str_replace($_SERVER['DOCUMENT_ROOT'], '', $baseVideoThumbPath);
                    } else {
                        $newGames_wt_video = $newGames['wt_video'];
                    }

                    $data .= "<a href='" . siteUrl().'/game/' . slugify($newGames['name']) . "' class='relative w-32 shrink-0 lg:w-52' aria-label='" .$newGames['name'] . "' data-wt-video='". $newGames_wt_video . "'>
                        <img src='". $newGames_image . "' alt='" . $newGames['name'] . " image' class='object-cover w-full rounded-lg' loading='lazy'>
                    </a>";
                }
                
                $data .= "</div><div class=\"absolute top-0 right-0 z-20 items-center justify-center hidden w-12 h-full pl-2 text-2xl text-white bg-black cursor-pointer splide-arrow-right bg-opacity-80\">
                            <i class=\"fa fa-chevron-right\" aria-hidden=\"true\"></i>
                        </div>
                        <div class=\"absolute top-0 left-0 z-20 items-center justify-center hidden w-12 h-full pl-2 text-2xl text-white bg-black cursor-pointer splide-arrow-left bg-opacity-80\">
                            <i class=\"fa fa-chevron-left\" aria-hidden=\"true\"></i>
                        </div>
                    </div>
                </div>";

                echo $data;
            }
        }

        if($slider['type'] == 'category'){
            // new games
            $all_splide_item = "";
            $category_id = $slider['category_tags_id'];
            $games_query = $GameMonetizeConnect->query("SELECT * FROM ".GAMES." WHERE category = '{$category_id}' AND published = '1' ORDER BY featured DESC LIMIT 20");

            $category_query = $GameMonetizeConnect->query("SELECT * FROM ".CATEGORIES." WHERE id='{$category_id}'");
            $category_data = $category_query->fetch_array();
        
            $data = "<div class='mb-6'>
            <div class=\"flex justify-between mb-2\">
                <div class=\"flex items-center text-sm font-bold text-white\">
                    ". $category_data['name'] . "
                    <a href=\"" . siteUrl() . "/category/{$category_data['category_pilot']}" . "\" class='ml-4 text-xs text-violet-500'>View more</a>
                </div>
                <div class=\"cursor-pointer splide-arrow-right lg:hidden\">
                    <svg class=\"size-5 fill-white\" viewBox=\"0 0 24 24\" focusable=\"false\" aria-hidden=\"true\" class=\"GameCarousel_doubleArrow__BGrRW double-arrow css-6qu7l6\"><path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M3.25757 2.33007C3.62757 1.92005 4.2599 1.88759 4.66993 2.25759L12.9814 9.75759C14.3395 10.9831 14.3395 13.0169 12.9814 14.2424L4.66993 21.7424C4.2599 22.1124 3.62757 22.08 3.25757 21.6699C2.88758 21.2599 2.92003 20.6276 3.33006 20.2576L11.6415 12.7576C12.1195 12.3263 12.1195 11.6737 11.6415 11.2424L3.33006 3.74243C2.92003 3.37243 2.88758 2.7401 3.25757 2.33007Z\"></path><path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M11.2576 2.33007C11.6276 1.92005 12.2599 1.88759 12.6699 2.25759L20.9814 9.75759C22.3395 10.9831 22.3395 13.0169 20.9814 14.2424L12.6699 21.7424C12.2599 22.1124 11.6276 22.08 11.2576 21.6699C10.8876 21.2599 10.92 20.6276 11.3301 20.2576L19.6415 12.7576C20.1195 12.3263 20.1195 11.6737 19.6415 11.2424L11.3301 3.74243C10.92 3.37243 10.8876 2.7401 11.2576 2.33007Z\"></path></svg>
                </div>
            </div>
                        <div class=\"relative group\"><div class=\"flex max-w-full space-x-2 overflow-x-auto no-scrollbar splide-items-container\">";

            $categorySlug = !empty($category_data['category_pilot']) ? $category_data['category_pilot'] : slugify($category_data['name']);
            $sliderImg = getSliderCardImage($category_data['name'], $categorySlug);

            if ($sliderImg != '') {
                $categoryCountQuery = $GameMonetizeConnect->query("SELECT COUNT(*) FROM " . GAMES . " WHERE category='" . (int)$category_id . "' AND published='1'");
                $categoryCount = $categoryCountQuery ? (int)$categoryCountQuery->fetch_array()[0] : 0;

                $data .= "<a href='" . siteUrl() . "/category/" . $categorySlug . "' class='home-slider-lead-card' title='" . htmlspecialchars($category_data['name'], ENT_QUOTES) . "'>
                    <img src='" . $sliderImg . "' alt='" . htmlspecialchars($category_data['name'], ENT_QUOTES) . " image' width='728' height='312' loading='lazy' decoding='async'>
                    <span class='home-slider-lead-card-text'>
                        <strong>" . htmlspecialchars($category_data['name'], ENT_QUOTES) . "</strong>
                        <small>" . number_format($categoryCount) . " games</small>
                    </span>
                </a>";
            }
        
            while ($newGames = $games_query->fetch_array()) {
                preg_match("/\/([a-zA-Z0-9]+)\//", $newGames['image'], $matches);
                $baseImagePath = $_SERVER['DOCUMENT_ROOT'] . '/games-image/' . $matches[1] . '/250x150.webp';
                if (file_exists($baseImagePath)) {
                    $newGames_image = str_replace($_SERVER['DOCUMENT_ROOT'], '', $baseImagePath);
                } else {
                    $newGames_image = $newGames['image'];
                }

                preg_match('/([^\/]+\.mp4)$/', $newGames['wt_video'], $matches);
                $baseVideoThumbPath = $_SERVER['DOCUMENT_ROOT'] . '/games-thumb-video/' . $matches[1];
                if (file_exists($baseVideoThumbPath)) {
                    $newGames_wt_video = str_replace($_SERVER['DOCUMENT_ROOT'], '', $baseVideoThumbPath);
                } else {
                    $newGames_wt_video = $newGames['wt_video'];
                }

                $data .= "<a href='" . siteUrl().'/game/' . slugify($newGames['name']) . "' class='relative w-32 shrink-0 lg:w-52' aria-label='" .$newGames['name'] . "' data-wt-video='". $newGames_wt_video . "'>
                    <img src='". $newGames_image . "' alt='" . $newGames['name'] . " image' class='object-cover w-full rounded-lg' loading='lazy'>
                </a>";
            }
            
            $data .= "</div><div class=\"absolute top-0 right-0 z-20 items-center justify-center hidden w-12 h-full pl-2 text-2xl text-white bg-black cursor-pointer splide-arrow-right bg-opacity-80\">
                        <i class=\"fa fa-chevron-right\" aria-hidden=\"true\"></i>
                    </div>
                    <div class=\"absolute top-0 left-0 z-20 items-center justify-center hidden w-12 h-full pl-2 text-2xl text-white bg-black cursor-pointer splide-arrow-left bg-opacity-80\">
                        <i class=\"fa fa-chevron-left\" aria-hidden=\"true\"></i>
                    </div>
                </div>
            </div>";

            echo $data;
        }

        if($slider['type'] == 'tags'){
            // new games
            $all_splide_item = "";
            $tags_id = $slider['category_tags_id'];
            $games_query = $GameMonetizeConnect->query("SELECT * FROM ".GAMES." WHERE tags_ids LIKE '%\"{$tags_id}\"%' AND published = '1' ORDER BY featured DESC LIMIT 20");

            $tags_query = $GameMonetizeConnect->query("SELECT * FROM ".TAGS." WHERE id='{$tags_id}'");
            $tags_data = $tags_query->fetch_array();
        
            $data = "<div class='mb-6'>
            <div class=\"flex justify-between mb-2\">
                <div class=\"flex items-center text-sm font-bold text-white\">
                    ". $tags_data['name'] . "
                    <a href=\"" . siteUrl() . "/tag/{$tags_data['url']}" . "\" class='ml-4 text-xs text-violet-500'>View more</a>
                </div>
                <div class=\"cursor-pointer splide-arrow-right lg:hidden\">
                    <svg class=\"size-5 fill-white\" viewBox=\"0 0 24 24\" focusable=\"false\" aria-hidden=\"true\" class=\"GameCarousel_doubleArrow__BGrRW double-arrow css-6qu7l6\"><path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M3.25757 2.33007C3.62757 1.92005 4.2599 1.88759 4.66993 2.25759L12.9814 9.75759C14.3395 10.9831 14.3395 13.0169 12.9814 14.2424L4.66993 21.7424C4.2599 22.1124 3.62757 22.08 3.25757 21.6699C2.88758 21.2599 2.92003 20.6276 3.33006 20.2576L11.6415 12.7576C12.1195 12.3263 12.1195 11.6737 11.6415 11.2424L3.33006 3.74243C2.92003 3.37243 2.88758 2.7401 3.25757 2.33007Z\"></path><path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M11.2576 2.33007C11.6276 1.92005 12.2599 1.88759 12.6699 2.25759L20.9814 9.75759C22.3395 10.9831 22.3395 13.0169 20.9814 14.2424L12.6699 21.7424C12.2599 22.1124 11.6276 22.08 11.2576 21.6699C10.8876 21.2599 10.92 20.6276 11.3301 20.2576L19.6415 12.7576C20.1195 12.3263 20.1195 11.6737 19.6415 11.2424L11.3301 3.74243C10.92 3.37243 10.8876 2.7401 11.2576 2.33007Z\"></path></svg>
                </div>
            </div>
                        <div class=\"relative group\"><div class=\"flex max-w-full space-x-2 overflow-x-auto no-scrollbar splide-items-container\">";

            $tagSlug = !empty($tags_data['url']) ? $tags_data['url'] : slugify($tags_data['name']);
            $sliderImg = getSliderCardImage($tags_data['name'], $tagSlug);

            if ($sliderImg != '') {
                $data .= "<a href='" . siteUrl() . "/tag/" . $tagSlug . "' class='home-slider-lead-card' title='" . htmlspecialchars($tags_data['name'], ENT_QUOTES) . "'>
                    <img src='" . $sliderImg . "' alt='" . htmlspecialchars($tags_data['name'], ENT_QUOTES) . " image' width='728' height='312' loading='lazy' decoding='async'>
                    <span class='home-slider-lead-card-text'>
                        <strong>" . htmlspecialchars($tags_data['name'], ENT_QUOTES) . "</strong>
                    </span>
                </a>";
            }
        
            while ($newGames = $games_query->fetch_array()) {
                preg_match("/\/([a-zA-Z0-9]+)\//", $newGames['image'], $matches);
                $baseImagePath = $_SERVER['DOCUMENT_ROOT'] . '/games-image/' . $matches[1] . '/250x150.webp';
                if (file_exists($baseImagePath)) {
                    $newGames_image = str_replace($_SERVER['DOCUMENT_ROOT'], '', $baseImagePath);
                } else {
                    $newGames_image = $newGames['image'];
                }

                preg_match('/([^\/]+\.mp4)$/', $newGames['wt_video'], $matches);
                $baseVideoThumbPath = $_SERVER['DOCUMENT_ROOT'] . '/games-thumb-video/' . $matches[1];
                if (file_exists($baseVideoThumbPath)) {
                    $newGames_wt_video = str_replace($_SERVER['DOCUMENT_ROOT'], '', $baseVideoThumbPath);
                } else {
                    $newGames_wt_video = $newGames['wt_video'];
                }

                $data .= "<a href='" . siteUrl().'/game/' . slugify($newGames['name']) . "' class='relative w-32 shrink-0 lg:w-52' aria-label='" .$newGames['name'] . "' data-wt-video='". $newGames_wt_video . "'>
                    <img src='". $newGames_image . "' alt='" . $newGames['name'] . " image' class='object-cover w-full rounded-lg' loading='lazy'>
                </a>";
            }
            
            $data .= "</div><div class=\"absolute top-0 right-0 z-20 items-center justify-center hidden w-12 h-full pl-2 text-2xl text-white bg-black cursor-pointer splide-arrow-right bg-opacity-80\">
                        <i class=\"fa fa-chevron-right\" aria-hidden=\"true\"></i>
                    </div>
                    <div class=\"absolute top-0 left-0 z-20 items-center justify-center hidden w-12 h-full pl-2 text-2xl text-white bg-black cursor-pointer splide-arrow-left bg-opacity-80\">
                        <i class=\"fa fa-chevron-left\" aria-hidden=\"true\"></i>
                    </div>
                </div>
            </div>";

            echo $data;
        }

        $index++;
    }
} else {
    echo "Invalid Request";
}
