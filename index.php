<?php
// ============================================================
// WALLHUB — INDEX PAGE (MONOCHROME PREMIUM)
// ============================================================

// STEP 1: Include database connection FIRST
require_once 'includes/db.php';

// STEP 2: Check if connection is valid
$db_connected = (isset($conn) && $conn && !is_bool($conn));

// STEP 3: Set page variables
$pageTitle       = "WallHub — Cinematic Wallpapers";
$pageDescription = "Discover breathtaking anime & art wallpapers in 4K";
$pageKeywords    = "wallpapers, anime, HD, 4K, one piece, naruto, bleach";

// STEP 4: Include header
include 'header.php';

// STEP 5: Initialize all variables with defaults
$totalWallpapers = 0;
$totalDownloads = 0;
$top_downloads = [];
$top_views = [];
$newest = [];

// STEP 6: Only run queries if database is connected
if ($db_connected) {
    // Get total wallpapers
    $desktopCount = mysqli_query($conn, "SELECT COUNT(*) as count FROM desktop_wallpapers");
    $totalDesktop = ($desktopCount && !is_bool($desktopCount)) ? mysqli_fetch_assoc($desktopCount)['count'] : 0;

    $mobileCount = mysqli_query($conn, "SELECT COUNT(*) as count FROM mobile_wallpapers");
    $totalMobile = ($mobileCount && !is_bool($mobileCount)) ? mysqli_fetch_assoc($mobileCount)['count'] : 0;
    $totalWallpapers = $totalDesktop + $totalMobile;

    // Get total downloads
    $desktopDownloads = mysqli_query($conn, "SELECT COALESCE(SUM(downloads), 0) as total FROM desktop_wallpapers");
    $totalDesktopDownloads = ($desktopDownloads && !is_bool($desktopDownloads)) ? mysqli_fetch_assoc($desktopDownloads)['total'] : 0;

    $mobileDownloads = mysqli_query($conn, "SELECT COALESCE(SUM(downloads), 0) as total FROM mobile_wallpapers");
    $totalMobileDownloads = ($mobileDownloads && !is_bool($mobileDownloads)) ? mysqli_fetch_assoc($mobileDownloads)['total'] : 0;
    $totalDownloads = $totalDesktopDownloads + $totalMobileDownloads;

    // Get top downloads
    $sql_dl = "SELECT w.id, w.title, w.image_path, w.downloads, w.views,
                      w.character_name, w.resolution, w.created_at,
                      c.name AS category_name
               FROM desktop_wallpapers w
               LEFT JOIN categories c ON w.category_id = c.id
               ORDER BY w.downloads DESC LIMIT 8";
    $res = mysqli_query($conn, $sql_dl);
    if ($res && !is_bool($res)) {
        while ($row = mysqli_fetch_assoc($res)) {
            $top_downloads[] = $row;
        }
    }

    // Get top views
    $sql_vw = "SELECT w.id, w.title, w.image_path, w.downloads, w.views,
                      w.character_name, w.resolution, w.created_at,
                      c.name AS category_name
               FROM desktop_wallpapers w
               LEFT JOIN categories c ON w.category_id = c.id
               ORDER BY w.views DESC LIMIT 8";
    $res2 = mysqli_query($conn, $sql_vw);
    if ($res2 && !is_bool($res2)) {
        while ($row = mysqli_fetch_assoc($res2)) {
            $top_views[] = $row;
        }
    }

    // Get newest
    $sql_new = "SELECT w.id, w.title, w.image_path, w.downloads, w.views,
                       w.character_name, w.resolution, w.created_at,
                       c.name AS category_name
                FROM desktop_wallpapers w
                LEFT JOIN categories c ON w.category_id = c.id
                ORDER BY w.id DESC LIMIT 10";
    $res3 = mysqli_query($conn, $sql_new);
    if ($res3 && !is_bool($res3)) {
        while ($row = mysqli_fetch_assoc($res3)) {
            $newest[] = $row;
        }
    }
}

// Format numbers function
function fmtNum(int $n): string {
    if ($n >= 1000000) return round($n/1000000,1).'M';
    if ($n >= 1000) return round($n/1000,1).'K';
    return (string)$n;
}

// Filmstrip reel — pulls from the whole desktop library (not just the
// homepage's top-10/top-8 lists), newest first. FILMSTRIP_LIMIT caps how
// much of the library goes into the reel — the strip is duplicated once
// for a seamless loop, so this is effectively x2 <img> tags on the
// homepage. Set to null to include literally every wallpaper (fine once
// the library is a few hundred; at ~2,000 it will noticeably slow the
// homepage's initial HTML payload, even with lazy-loaded images).
define('FILMSTRIP_LIMIT', 120);

$filmstrip_items = [];
if ($db_connected) {
    $limitSql = FILMSTRIP_LIMIT !== null ? ' LIMIT ' . (int)FILMSTRIP_LIMIT : '';
    // ORDER BY RAND() so the reel is a random spread across the whole
    // library (id 1, id 435, id 1934, ...) instead of always the same
    // "most recent" run. Re-randomizes on every page load.
    $sql_film = "SELECT id, title, image_path FROM desktop_wallpapers ORDER BY RAND()" . $limitSql;
    $resFilm = mysqli_query($conn, $sql_film);
    if ($resFilm && !is_bool($resFilm)) {
        while ($row = mysqli_fetch_assoc($resFilm)) {
            $filmstrip_items[] = $row;
        }
    }
}

// Keep the marquee's visual speed consistent regardless of how many
// items are in it — more items need a longer duration to scroll at the
// same pace, or it'd feel like it's flying past.
$filmstripCount = max(count($filmstrip_items), 1);
$filmstripDuration = max(30, round($filmstripCount * 3.2));

// Categories — icon grid, as in the original design
$categories = [
    ['name'=>'Phone',   'icon'=>'mobile-alt',   'link'=>'mobile',  'color'=>'#818cf8','glow'=>'rgba(129,140,248,0.4)'],
    ['name'=>'Anime',   'icon'=>'robot',         'link'=>'anime',   'color'=>'#f472b6','glow'=>'rgba(244,114,182,0.4)'],
    ['name'=>'Actress', 'icon'=>'venus',         'link'=>'actress', 'color'=>'#fb7185','glow'=>'rgba(251,113,133,0.4)'],
    ['name'=>'Movies',  'icon'=>'film',          'link'=>'movies',  'color'=>'#fbbf24','glow'=>'rgba(251,191,36,0.4)'],
    ['name'=>'Nature',  'icon'=>'mountain',      'link'=>'nature',  'color'=>'#34d399','glow'=>'rgba(52,211,153,0.4)'],
    ['name'=>'Space',   'icon'=>'space-shuttle', 'link'=>'space',   'color'=>'#38bdf8','glow'=>'rgba(56,189,248,0.4)'],
    ['name'=>'Animal',  'icon'=>'paw',           'link'=>'animal',  'color'=>'#fb923c','glow'=>'rgba(251,146,60,0.4)'],
    ['name'=>'Birds',   'icon'=>'dove',          'link'=>'bird',    'color'=>'#c084fc','glow'=>'rgba(192,132,252,0.4)'],
    ['name'=>'Fantasy', 'icon'=>'dragon',        'link'=>'fantasy', 'color'=>'#f87171','glow'=>'rgba(248,113,113,0.4)'],
    ['name'=>'Cars',    'icon'=>'car',           'link'=>'vehicle', 'color'=>'#a3e635','glow'=>'rgba(163,230,53,0.4)'],
    ['name'=>'Gaming',  'icon'=>'gamepad',       'link'=>'gaming',  'color'=>'#facc15','glow'=>'rgba(250,204,21,0.4)'],
];
$totalCategories = count($categories);

// Featured rotation for the hero frame
$hero_features = [
    ['img' => '/images/Yo.jpg', 'title' => 'One Piece', 'tag' => 'Anime · Gear 5', 'link' => 'explore.php?slug=onepiece'],
    ['img' => '/images/mobile/space/1780630406_vefcdsx.jpg', 'title' => 'Deep Field', 'tag' => 'Space · 4K', 'link' => 'explore.php?slug=space'],
    ['img' => '/images/mobile/nature/1780630894_fd.jpg', 'title' => 'Coastline', 'tag' => 'Nature · HD', 'link' => 'explore.php?slug=nature'],
];
?>

<!-- Display error if database connection failed -->
<?php if (!$db_connected): ?>
<div style="background: rgba(198,161,91,0.06); border: 1px solid rgba(198,161,91,0.4); border-radius: 2px; padding: 24px; margin: 120px 20px 20px; text-align: center;">
    <i class="fas fa-database" style="font-size: 32px; color: #c6a15b; margin-bottom: 14px; display: inline-block;"></i>
    <h3 style="color: #f2f1ed; font-weight:500;">Database connection error</h3>
    <p style="color: #9a9a96;">Unable to connect to the database. Please try again shortly.</p>
    <p style="color: #575753; font-size: 12px; margin-top: 10px;">Contact hosting support to check the MySQL service.</p>
</div>
<?php endif; ?>

<!—— ═══════════════════════════════════════════════════════════
     GOOGLE FONTS
     ═══════════════════════════════════════════════════════════ ——>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,400;0,9..144,500;0,9..144,600;1,9..144,400;1,9..144,500&family=Inter:wght@300;400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">

<style>
/* ═══════════════════════════════════════════════════════════════
   DESIGN TOKENS — WALLHUB · MONOCHROME PREMIUM
   ═══════════════════════════════════════════════════════════════ */
:root {
  --ink:        #08080a;
  --ink-2:      #0e0e11;
  --ink-3:      #17171b;
  --line:       rgba(245,245,240,0.09);
  --line-hi:    rgba(245,245,240,0.2);
  --paper:      #f5f4f0;
  --text:       #efeee9;
  --text-dim:   #9a9a96;
  --text-faint: #57574f;
  --brass:      #c6a15b;
  --brass-soft: rgba(198,161,91,0.5);
  --brass-wash: rgba(198,161,91,0.08);
  --r:          2px;
  --ease:       cubic-bezier(0.16,1,0.3,1);
  --font-display: 'Fraunces', serif;
  --font-body:    'Inter', system-ui, sans-serif;
  --font-mono:    'IBM Plex Mono', monospace;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html {
  scroll-behavior: smooth;
  overflow-x: hidden;
  width: 100%;
}

body {
  font-family: var(--font-body);
  background: var(--ink);
  color: var(--text);
  -webkit-font-smoothing: antialiased;
  overflow-x: hidden;
  width: 100%;
  max-width: 100vw;
  position: relative;
}

/* Quiet paper-grain texture - ties the whole page to the print/editorial feel */
body::before {
  content: '';
  position: fixed;
  inset: 0;
  z-index: 2500;
  pointer-events: none;
  opacity: 0.025;
  mix-blend-mode: soft-light;
  background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='160' height='160'><filter id='n'><feTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='2' stitchTiles='stitch'/></filter><rect width='100%25' height='100%25' filter='url(%23n)'/></svg>");
}

a { text-decoration: none; color: inherit; }
img { display: block; pointer-events: none; user-select: none; -webkit-user-drag: none; }

.container { max-width: 1360px; margin: 0 auto; padding: 0 32px; }
.sec { padding: 130px 0; }
.sec--alt { background: var(--ink-2); border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); }

/* ───────────────────────────────────────────────
   SECTION HEADERS
   ─────────────────────────────────────────────── */
.sh {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 24px;
  margin-bottom: 64px;
  flex-wrap: wrap;
}
.sh__eyebrow {
  display: flex;
  align-items: center;
  gap: 12px;
  font-family: var(--font-mono);
  font-size: 0.7rem;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--brass);
  margin-bottom: 18px;
}
.sh__eyebrow-line { width: 28px; height: 1px; background: var(--brass-soft); }
.sh__h {
  font-family: var(--font-display);
  font-weight: 400;
  font-size: clamp(2.4rem, 4vw, 3.6rem);
  line-height: 1.05;
  letter-spacing: -0.01em;
  color: var(--text);
}
.sh__h em { font-style: italic; font-weight: 400; color: var(--brass); }
.sh__sub {
  font-size: 0.92rem;
  color: var(--text-dim);
  margin-top: 12px;
  font-weight: 300;
  max-width: 40ch;
}

.view-all {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-family: var(--font-mono);
  font-size: 0.72rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--text-dim);
  padding-bottom: 4px;
  border-bottom: 1px solid var(--line-hi);
  transition: color 0.3s var(--ease), border-color 0.3s var(--ease), gap 0.3s var(--ease);
}
.view-all:hover { color: var(--brass); border-color: var(--brass); gap: 16px; }


/* ═══════════════════════════════════════════════════════════════
   HERO — ASYMMETRIC EDITORIAL PLATE
   ═══════════════════════════════════════════════════════════════ */
.hero {
  position: relative;
  min-height: 100vh;
  display: grid;
  grid-template-columns: 0.92fr 1.08fr;
  align-items: stretch;
  background: var(--ink);
  padding-top: 88px;
}

.hero__copy {
  position: relative;
  z-index: 2;
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 60px 56px 60px max(56px, calc((100vw - 1360px) / 2 + 32px));
  border-right: 1px solid var(--line);
}

.hero__kicker {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-family: var(--font-mono);
  font-size: 0.68rem;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: var(--text-dim);
  margin-bottom: 34px;
  opacity: 0;
  animation: fadeUp 0.7s var(--ease) 0.1s forwards;
}
.hero__kicker-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--brass); }

.hero__h1 {
  font-family: var(--font-display);
  font-weight: 400;
  font-size: clamp(4rem, 6.4vw, 6.6rem);
  line-height: 0.94;
  letter-spacing: -0.01em;
  color: var(--text);
  margin-bottom: 32px;
}
.hero__h1 .line { display: block; overflow: hidden; }
.hero__h1 .line span {
  display: block;
  opacity: 0;
  transform: translateY(105%);
  animation: revealLine 0.8s var(--ease) forwards;
}
.hero__h1 .line:nth-child(1) span { animation-delay: 0.18s; }
.hero__h1 .line:nth-child(2) span { animation-delay: 0.3s; font-style: italic; color: var(--brass); }
@keyframes revealLine { to { opacity: 1; transform: translateY(0); } }
@keyframes fadeUp { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }

.hero__desc {
  font-size: 1.02rem;
  font-weight: 300;
  line-height: 1.7;
  color: var(--text-dim);
  max-width: 400px;
  margin-bottom: 44px;
  opacity: 0;
  animation: fadeUp 0.7s var(--ease) 0.55s forwards;
}

.hero__rule { width: 100%; height: 1px; background: var(--line); margin-bottom: 36px; opacity: 0; animation: fadeUp 0.7s var(--ease) 0.65s forwards; }

.hero__stats {
  display: flex;
  gap: 44px;
  margin-bottom: 46px;
  opacity: 0;
  animation: fadeUp 0.7s var(--ease) 0.75s forwards;
}
.hero__stat-num {
  display: block;
  font-family: var(--font-display);
  font-size: 2.2rem;
  font-weight: 400;
  color: var(--text);
  line-height: 1;
}
.hero__stat-lbl {
  display: block;
  font-size: 0.66rem;
  font-family: var(--font-mono);
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--text-faint);
  margin-top: 8px;
}

.hero__actions {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
  opacity: 0;
  animation: fadeUp 0.7s var(--ease) 0.9s forwards;
}


/* Right — single framed feature plate */
.hero__plate {
  position: relative;
  height: 100%;
  min-height: 560px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 64px 64px 64px 40px;
  perspective: 1400px;
}
.hero__frame {
  position: relative;
  width: 100%;
  max-width: 620px;
  aspect-ratio: 4 / 5;
  border: 1px solid var(--line-hi);
  padding: 14px;
  transition: transform 0.4s var(--ease);
  transform-style: preserve-3d;
  will-change: transform;
}
.hero__frame::before, .hero__frame::after,
.hero__frame-inner .corner {
  content: '';
}
.hero__frame-inner {
  position: relative;
  width: 100%;
  height: 100%;
  overflow: hidden;
}
.hero__slide {
  position: absolute;
  inset: 0;
  opacity: 0;
  pointer-events: none;
  z-index: 1;
  transition: opacity 1.1s var(--ease);
}
.hero__slide.active { opacity: 1; pointer-events: auto; z-index: 2; }
.hero__slide img { width: 100%; height: 100%; object-fit: cover; filter: grayscale(0.08) contrast(1.03); }
.hero__slide::after {
  content: '';
  position: absolute; inset: 0;
  background: linear-gradient(to top, rgba(8,8,10,0.85) 0%, transparent 45%);
}
.hero__slide-cap {
  position: absolute;
  left: 20px; bottom: 18px;
  z-index: 2;
  font-family: var(--font-mono);
  font-size: 0.68rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--paper);
}
.hero__slide-title {
  position: absolute;
  left: 20px; bottom: 40px;
  z-index: 2;
  font-family: var(--font-display);
  font-style: italic;
  font-size: 1.5rem;
  color: var(--paper);
}

.hero__frame-index {
  position: absolute;
  top: -1px; right: -1px;
  background: var(--ink);
  border: 1px solid var(--line-hi);
  padding: 8px 14px;
  font-family: var(--font-mono);
  font-size: 0.7rem;
  color: var(--brass);
  letter-spacing: 0.08em;
  z-index: 3;
}

.hero__plate-tag {
  position: absolute;
  bottom: 28px;
  left: 64px;
  font-family: var(--font-mono);
  font-size: 0.66rem;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: var(--text-faint);
  writing-mode: vertical-lr;
}

@media (prefers-reduced-motion: reduce) {
  .hero__h1 .line span, .hero__kicker, .hero__desc, .hero__rule, .hero__stats, .hero__actions { animation: none; opacity: 1; transform: none; }
}


/* ═══════════════════════════════════════════════════════════════
   FILMSTRIP — full-bleed infinite scrolling wallpaper reel
   ═══════════════════════════════════════════════════════════════ */
.filmstrip { width: 100%; overflow: hidden; background: var(--ink-2); border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); padding: 26px 0; }
.filmstrip__track { display: flex; gap: 16px; width: max-content; animation: filmScroll linear infinite; animation-duration: var(--film-dur, 46s); }
.filmstrip__track:hover { animation-play-state: paused; }
@keyframes filmScroll { from { transform: translateX(0); } to { transform: translateX(-50%); } }
.filmstrip__item {
  position: relative; flex: 0 0 auto;
  width: 240px; aspect-ratio: 16 / 10;
  overflow: hidden; border: 1px solid var(--line);
  text-decoration: none;
}
.filmstrip__item img { width: 100%; height: 100%; object-fit: cover; filter: grayscale(0.65); transition: filter 0.5s, transform 0.6s var(--ease); }
.filmstrip__item:hover img { filter: grayscale(0); transform: scale(1.06); }
.filmstrip__item::after {
  content: ''; position: absolute; inset: 0;
  background: linear-gradient(to top, rgba(8,8,10,0.8) 0%, transparent 55%);
  pointer-events: none;
}
.filmstrip__cap {
  position: absolute; left: 10px; bottom: 8px; z-index: 2;
  font-family: var(--font-mono); font-size: 0.6rem; letter-spacing: 0.06em; text-transform: uppercase;
  color: var(--paper); opacity: 0; transition: opacity 0.3s;
}
.filmstrip__item:hover .filmstrip__cap { opacity: 0.9; }

/* ═══════════════════════════════════════════════════════════════
   TICKER — scrolling text band
   ═══════════════════════════════════════════════════════════════ */
.ticker { width: 100%; overflow: hidden; border-bottom: 1px solid var(--line); padding: 15px 0; background: var(--ink); }
.ticker__track { display: flex; width: max-content; animation: tickerScroll 24s linear infinite; }
.ticker__track:hover { animation-play-state: paused; }
@keyframes tickerScroll { from { transform: translateX(0); } to { transform: translateX(-50%); } }
.ticker__item {
  display: flex; align-items: center; gap: 36px;
  font-family: var(--font-display); font-style: italic; font-weight: 400;
  font-size: 1.05rem; color: var(--text-faint); white-space: nowrap;
  padding-right: 36px;
}
.ticker__item i { color: var(--brass); font-size: 0.6rem; font-style: normal; }

@media (prefers-reduced-motion: reduce) {
  .filmstrip__track, .ticker__track { animation: none; }
}
@media (max-width: 640px) {
  .filmstrip__item { width: 180px; }
}


/* ═══════════════════════════════════════════════════════════════
   BUTTONS
   ═══════════════════════════════════════════════════════════════ */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 15px 30px;
  border-radius: var(--r);
  font-size: 0.82rem;
  font-weight: 500;
  letter-spacing: 0.02em;
  border: 1px solid transparent;
  cursor: pointer;
  transition: all 0.3s var(--ease);
  font-family: var(--font-body);
}
.btn--primary { background: var(--paper); color: var(--ink); }
.btn--primary:hover { background: var(--brass); }
.btn--outline { background: transparent; color: var(--text); border-color: var(--line-hi); }
.btn--outline:hover { border-color: var(--brass); color: var(--brass); }
.btn--text {
  padding: 0; background: none; border: none; color: var(--text);
  border-bottom: 1px solid var(--line-hi);
  border-radius: 0; padding-bottom: 4px;
}
.btn--text:hover { color: var(--brass); border-color: var(--brass); }


/* ═══════════════════════════════════════════════════════════════
   TRUST STRIP — hairline-divided, text only
   ═══════════════════════════════════════════════════════════════ */
.trust-sec { border-bottom: 1px solid var(--line); }
.trust-bar {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
}
.trust-item {
  padding: 30px 32px;
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.trust-item + .trust-item { border-left: 1px solid var(--line); }
.trust-num { font-family: var(--font-display); font-size: 1.7rem; color: var(--text); }
.trust-lbl { font-family: var(--font-mono); font-size: 0.62rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-faint); }
@media (max-width: 900px) {
  .trust-bar { grid-template-columns: repeat(2, 1fr); }
  .trust-item:nth-child(3), .trust-item:nth-child(4) { border-top: 1px solid var(--line); }
  .trust-item:nth-child(3) { border-left: none; }
}
@media (max-width: 480px) {
  .trust-bar { grid-template-columns: 1fr; }
  .trust-item { border-left: none !important; border-top: 1px solid var(--line); }
  .trust-item:first-child { border-top: none; }
}


/* ═══════════════════════════════════════════════════════════════
   SPOTLIGHT — MAGAZINE SPREAD (breaks the grid rhythm)
   ═══════════════════════════════════════════════════════════════ */
.spotlight-sec { padding: 130px 0; border-bottom: 1px solid var(--line); }
.spotlight-inner { display: grid; grid-template-columns: 0.95fr 1.05fr; gap: 72px; align-items: center; }
.spotlight-media { position: relative; aspect-ratio: 4 / 5; overflow: hidden; border: 1px solid var(--line-hi); }
.spotlight-media img { width: 100%; height: 100%; object-fit: cover; filter: grayscale(0.12); transition: filter 0.5s, transform 0.9s var(--ease); }
.spotlight-media:hover img { filter: grayscale(0); transform: scale(1.035); }
.spotlight-index {
  position: absolute; top: -1px; left: -1px;
  background: var(--ink); border: 1px solid var(--line-hi);
  padding: 8px 14px; font-family: var(--font-mono); font-size: 0.7rem;
  color: var(--brass); letter-spacing: 0.08em; z-index: 2;
}
.spotlight-quote {
  font-family: var(--font-display); font-style: italic; font-weight: 400;
  font-size: clamp(1.5rem, 2.4vw, 2rem); line-height: 1.35;
  color: var(--text); margin: 22px 0 32px;
}
.spotlight-stats { display: flex; gap: 40px; margin-bottom: 36px; }
.spotlight-stat-num { display: block; font-family: var(--font-display); font-size: 1.7rem; color: var(--text); }
.spotlight-stat-lbl { display: block; font-family: var(--font-mono); font-size: 0.62rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-faint); margin-top: 6px; }
@media (max-width: 900px) {
  .spotlight-inner { grid-template-columns: 1fr; gap: 40px; }
}


/* ═══════════════════════════════════════════════════════════════
   CATEGORIES — ICON GRID (restored)
   ═══════════════════════════════════════════════════════════════ */
.cat-sec { padding: 130px 0; }
.cat-strip {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 16px;
  perspective: 1200px;
}
.cat-item {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 30px 12px;
  border: 1px solid var(--line);
  background: var(--ink-2);
  cursor: pointer;
  transition: transform 0.25s var(--ease), border-color 0.35s var(--ease), box-shadow 0.35s var(--ease), color 0.35s var(--ease);
  transform-style: preserve-3d;
  overflow: hidden;
  text-decoration: none;
  color: var(--text-dim);
  opacity: 0;
  animation: catIn 0.6s var(--ease) both;
  animation-delay: var(--delay, 0ms);
}
@keyframes catIn { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
.cat-item::before {
  content: '';
  position: absolute; inset: 0;
  background: radial-gradient(circle at 50% 100%, var(--c-glow, transparent), transparent 70%);
  opacity: 0; transition: opacity 0.4s;
}
.cat-item:hover {
  border-color: var(--c, var(--line-hi));
  box-shadow: 0 16px 34px rgba(0,0,0,0.5), 0 0 24px var(--c-glow, transparent);
  color: var(--text);
}
.cat-item:hover::before { opacity: 1; }
.cat-item__icon {
  font-size: 1.6rem;
  color: var(--c, var(--text-dim));
  transition: transform 0.3s var(--ease);
  filter: drop-shadow(0 0 8px var(--c-glow, transparent));
}
.cat-item:hover .cat-item__icon { transform: scale(1.12) translateY(-2px); }
.cat-item__name {
  font-family: var(--font-display);
  font-size: 1rem;
  font-weight: 400;
  letter-spacing: 0.01em;
  text-align: center;
}
.cat-item__arrow {
  position: absolute;
  top: 12px; right: 12px;
  font-size: 0.62rem;
  color: var(--c, var(--text-faint));
  opacity: 0;
  transform: translate(-4px, 4px);
  transition: all 0.3s var(--ease);
}
.cat-item:hover .cat-item__arrow { opacity: 0.85; transform: translate(0, 0); }
.cat-item__index {
  position: absolute;
  top: 12px; left: 12px;
  font-family: var(--font-mono);
  font-size: 0.62rem;
  letter-spacing: 0.05em;
  color: var(--text-faint);
  transition: color 0.3s var(--ease);
}
.cat-item:hover .cat-item__index { color: var(--c, var(--text-dim)); }
@media (prefers-reduced-motion: reduce) { .cat-item { animation: none; opacity: 1; } }


/* ═══════════════════════════════════════════════════════════════
   TABS
   ═══════════════════════════════════════════════════════════════ */
.tabs { display: inline-flex; gap: 28px; border-bottom: 1px solid var(--line); }
.tab-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 0 0 16px;
  border: none;
  background: transparent;
  color: var(--text-faint);
  font-size: 0.78rem;
  font-family: var(--font-mono);
  letter-spacing: 0.08em;
  text-transform: uppercase;
  cursor: pointer;
  border-bottom: 1px solid transparent;
  margin-bottom: -1px;
  transition: color 0.25s var(--ease), border-color 0.25s var(--ease);
}
.tab-btn:hover { color: var(--text-dim); }
.tab-btn.active { color: var(--brass); border-color: var(--brass); }
.tab-panel { display: none; animation: fadeSlide 0.4s var(--ease); }
.tab-panel.active { display: block; }
@keyframes fadeSlide { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }


/* ═══════════════════════════════════════════════════════════════
   WALLPAPER GRID
   ═══════════════════════════════════════════════════════════════ */
.wgrid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1px; background: var(--line); border: 1px solid var(--line); }
.wcard {
  position: relative;
  background: var(--ink);
  text-decoration: none;
  color: var(--text);
  display: flex;
  flex-direction: column;
  animation: cardIn 0.5s var(--ease) both;
  animation-delay: var(--delay, 0ms);
}
@keyframes cardIn { from{opacity:0;transform:translateY(18px)} to{opacity:1;transform:translateY(0)} }

.wcard__thumb { aspect-ratio: 16 / 9; overflow: hidden; position: relative; flex-shrink: 0; }
.wcard__thumb img { width: 100%; height: 100%; object-fit: cover; filter: grayscale(0.05); transition: transform 0.8s var(--ease), filter 0.5s; }
.wcard:hover .wcard__thumb img { transform: scale(1.045); filter: grayscale(0); }
.wcard__thumb::after {
  content: '';
  position: absolute; inset: 0;
  background: linear-gradient(to top, rgba(8,8,10,0.75) 0%, transparent 45%);
  pointer-events: none;
}

.wcard__overlay {
  position: absolute; inset: 0;
  background: rgba(8,8,10,0.88);
  display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px;
  opacity: 0; transition: opacity 0.3s;
  z-index: 3;
}
.wcard:hover .wcard__overlay { opacity: 1; }
.wcard__overlay-label {
  font-family: var(--font-mono); font-size: 0.72rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--paper);
  border-bottom: 1px solid var(--brass); padding-bottom: 4px;
}

.wcard__rank {
  position: absolute; top: 14px; left: 14px;
  font-family: var(--font-display); font-style: italic; font-size: 1.4rem; color: var(--paper);
  z-index: 2; text-shadow: 0 2px 10px rgba(0,0,0,0.6);
}
.wcard__rank--1 { color: var(--brass); }

.wcard__res {
  position: absolute; bottom: 12px; left: 14px;
  font-family: var(--font-mono); font-size: 0.6rem; letter-spacing: 0.1em; text-transform: uppercase;
  color: var(--paper); z-index: 2; opacity: 0.85;
}
.wcard__cat {
  position: absolute; top: 14px; right: 14px;
  font-family: var(--font-mono); font-size: 0.6rem; letter-spacing: 0.1em; text-transform: uppercase;
  color: var(--text-dim); z-index: 2;
}

.wcard__bar { position: absolute; bottom: 0; left: 0; right: 0; height: 1px; background: rgba(245,245,240,0.1); z-index: 2; }
.wcard__bar-fill { height: 100%; width: var(--p, 0%); background: var(--brass); transition: width 1.2s var(--ease); }

.wcard__body { padding: 16px 18px 18px; border-top: 1px solid var(--line); display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.wcard__title { font-family: var(--font-display); font-size: 1rem; font-weight: 400; margin-bottom: 5px; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
.wcard__char { font-size: 0.72rem; color: var(--text-faint); font-family: var(--font-mono); }
.wcard__stats { display: flex; gap: 16px; }
.wstat { display: flex; align-items: center; gap: 5px; font-size: 0.72rem; color: var(--text-faint); font-family: var(--font-mono); }
.wstat i { font-size: 0.65rem; }

@media (max-width: 1200px) { .wgrid { grid-template-columns: 1fr; } }


/* ═══════════════════════════════════════════════════════════════
   MOSAIC — NEW ARRIVALS
   ═══════════════════════════════════════════════════════════════ */
.mosaic { display: grid; grid-template-columns: repeat(5, 1fr); grid-template-rows: repeat(2, 230px); gap: 1px; background: var(--line); border: 1px solid var(--line); }
.mosaic-item { position: relative; overflow: hidden; background: var(--ink); cursor: pointer; text-decoration: none; animation: cardIn 0.5s var(--ease) both; animation-delay: var(--delay, 0ms); }
.mosaic-item img { width: 100%; height: 100%; object-fit: cover; filter: grayscale(0.15); transition: transform 0.7s var(--ease), filter 0.5s; }
.mosaic-item:hover img { transform: scale(1.06); filter: grayscale(0); }
.mosaic-item__overlay {
  position: absolute; inset: 0;
  background: linear-gradient(to top, rgba(8,8,10,0.92) 0%, transparent 55%);
  display: flex; flex-direction: column; justify-content: flex-end; padding: 16px;
  opacity: 0; transition: opacity 0.3s;
}
.mosaic-item:hover .mosaic-item__overlay { opacity: 1; }
.mosaic-item__title { font-family: var(--font-display); font-size: 0.95rem; margin-bottom: 6px; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
.mosaic-item__stats { display: flex; gap: 12px; }
.mosaic-item__new-badge {
  position: absolute; top: 10px; left: 10px;
  font-family: var(--font-mono); font-size: 0.58rem; letter-spacing: 0.12em; text-transform: uppercase;
  color: var(--brass); z-index: 2;
}
@media (max-width: 1200px) { .mosaic { grid-template-columns: repeat(4, 1fr); grid-template-rows: repeat(3, 190px); } }
@media (max-width: 768px) { .mosaic { grid-template-columns: repeat(2, 1fr); grid-template-rows: auto; } .mosaic-item { height: 190px; } }
@media (max-width: 480px) { .mosaic { grid-template-columns: 1fr; } }


/* ═══════════════════════════════════════════════════════════════
   NEWSLETTER
   ═══════════════════════════════════════════════════════════════ */
.nl-sec { padding: 120px 0; border-top: 1px solid var(--line); }
.nl-inner { display: grid; grid-template-columns: 1fr 1fr; gap: 80px; align-items: end; }
.nl-form { display: flex; flex-direction: column; gap: 20px; }
.nl-row { display: flex; align-items: flex-end; gap: 20px; border-bottom: 1px solid var(--line-hi); padding-bottom: 14px; }
.nl-row:focus-within { border-color: var(--brass); }
.nl-input {
  flex: 1; background: transparent; border: none; outline: none;
  color: var(--text); font-size: 1.1rem; font-family: var(--font-display);
  padding: 6px 0;
}
.nl-input::placeholder { color: var(--text-faint); font-style: italic; }
.nl-note { font-size: 0.68rem; color: var(--text-faint); font-family: var(--font-mono); letter-spacing: 0.08em; }


/* ═══════════════════════════════════════════════════════════════
   EMPTY STATE
   ═══════════════════════════════════════════════════════════════ */
.empty { text-align: center; padding: 90px 20px; color: var(--text-faint); border: 1px solid var(--line); }
.empty i { font-size: 2rem; display: block; margin-bottom: 14px; opacity: 0.4; }
.empty p { font-size: 0.9rem; font-family: var(--font-mono); }


/* ═══════════════════════════════════════════════════════════════
   RESPONSIVE — HERO / GLOBAL
   ═══════════════════════════════════════════════════════════════ */
@media (max-width: 1100px) {
  .hero { grid-template-columns: 1fr; min-height: auto; }
  .hero__copy { padding: 60px 32px 40px; border-right: none; border-bottom: 1px solid var(--line); }
  .hero__plate { padding: 40px 32px 60px; min-height: 420px; }
  .hero__plate-tag { display: none; }
}
@media (max-width: 640px) {
  .sec, .cat-sec, .nl-sec { padding: 80px 0; }
  .hero__h1 { font-size: 3.4rem; }
  .sh__h { font-size: 2rem; }
  .nl-inner { grid-template-columns: 1fr; gap: 36px; }
  .hero__stats { gap: 28px; }
}
</style>


<!-- ═══════════════════════════════════════════════════════════
     HERO
     ═══════════════════════════════════════════════════════════ -->
<section class="hero">
  <div class="hero__copy">
    <div class="hero__kicker">
      <span class="hero__kicker-dot"></span>
      <?php echo number_format($totalWallpapers); ?>+ wallpapers · updated daily
    </div>

    <h1 class="hero__h1">
      <span class="line"><span>Wall</span></span>
      <span class="line"><span>Hub</span></span>
    </h1>

    <p class="hero__desc">
      The internet's finest anime &amp; art wallpapers — meticulously curated, categorised, free to download in stunning 4K.
    </p>

    <div class="hero__rule"></div>

    <div class="hero__stats">
      <div class="hero__stat">
        <span class="hero__stat-num ctr__num" data-target="<?php echo $totalWallpapers; ?>">0</span>
        <span class="hero__stat-lbl">Wallpapers</span>
      </div>
      <div class="hero__stat">
        <span class="hero__stat-num ctr__num" data-target="<?php echo $totalDownloads; ?>">0</span>
        <span class="hero__stat-lbl">Downloads</span>
      </div>
      <div class="hero__stat">
        <span class="hero__stat-num ctr__num" data-target="<?php echo $totalCategories; ?>">0</span>
        <span class="hero__stat-lbl">Categories</span>
      </div>
    </div>

    <div class="hero__actions">
      <a href="trending" class="btn btn--primary">Explore Trending</a>
      <a href="categories.php" class="btn btn--outline">Browse All</a>
    </div>
  </div>

  <div class="hero__plate">
    <div class="hero__frame">
      <div class="hero__frame-index" id="heroFrameIndex">01 / 03</div>
      <div class="hero__frame-inner" id="heroSlides">
        <?php foreach ($hero_features as $i => $f): ?>
        <a href="<?= htmlspecialchars($f['link']) ?>" class="hero__slide<?= $i===0 ? ' active' : '' ?>" data-slide>
          <img src="<?= htmlspecialchars($f['img']) ?>" alt="<?= htmlspecialchars($f['title']) ?>" loading="eager">
          <span class="hero__slide-title"><?= htmlspecialchars($f['title']) ?></span>
          <span class="hero__slide-cap"><?= htmlspecialchars($f['tag']) ?></span>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <span class="hero__plate-tag">Featured Selection</span>
  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════
     TICKER
     ═══════════════════════════════════════════════════════════ -->
<div class="ticker">
  <div class="ticker__track">
    <?php for ($r = 0; $r < 2; $r++): ?>
      <div class="ticker__item"><i class="fas fa-circle"></i> 4K Ultra HD</div>
      <div class="ticker__item"><i class="fas fa-circle"></i> New Daily</div>
      <div class="ticker__item"><i class="fas fa-circle"></i> <?= number_format($totalWallpapers) ?>+ Wallpapers</div>
      <div class="ticker__item"><i class="fas fa-circle"></i> Always Free</div>
      <div class="ticker__item"><i class="fas fa-circle"></i> <?= $totalCategories ?> Categories</div>
      <div class="ticker__item"><i class="fas fa-circle"></i> Anime &amp; Art</div>
    <?php endfor; ?>
  </div>
</div>


<!-- ═══════════════════════════════════════════════════════════
     FILMSTRIP
     ═══════════════════════════════════════════════════════════ -->
<?php if (!empty($filmstrip_items)): ?>
<div class="filmstrip">
  <div class="filmstrip__track" style="--film-dur: <?= $filmstripDuration ?>s;">
    <?php for ($r = 0; $r < 2; $r++): ?>
      <?php foreach ($filmstrip_items as $fi): ?>
      <a href="download.php?id=<?= $fi['id'] ?>&type=desktop" class="filmstrip__item">
        <img src="<?= htmlspecialchars($fi['image_path']) ?>" alt="<?= htmlspecialchars($fi['title']) ?>" loading="lazy">
        <span class="filmstrip__cap"><?= htmlspecialchars($fi['title']) ?></span>
      </a>
      <?php endforeach; ?>
    <?php endfor; ?>
  </div>
</div>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════════════════
     TRUST STRIP
     ═══════════════════════════════════════════════════════════ -->
<section class="trust-sec">
  <div class="container">
    <div class="trust-bar">
      <div class="trust-item">
        <span class="trust-num"><?php echo number_format($totalWallpapers); ?>+</span>
        <span class="trust-lbl">Wallpapers</span>
      </div>
      <div class="trust-item">
        <span class="trust-num">4K</span>
        <span class="trust-lbl">Ultra HD Quality</span>
      </div>
      <div class="trust-item">
        <span class="trust-num"><?php echo $totalCategories; ?></span>
        <span class="trust-lbl">Categories</span>
      </div>
      <div class="trust-item">
        <span class="trust-num">Free</span>
        <span class="trust-lbl">Always, No Catch</span>
      </div>
    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════
     SPOTLIGHT — FEATURED WALLPAPER
     ═══════════════════════════════════════════════════════════ -->
<?php if (!empty($top_downloads)): $spot = $top_downloads[0]; ?>
<section class="spotlight-sec">
  <div class="container">
    <div class="spotlight-inner">
      <div class="spotlight-media">
        <span class="spotlight-index">01</span>
        <img src="<?= htmlspecialchars($spot['image_path']) ?>" alt="<?= htmlspecialchars($spot['title']) ?>" loading="lazy">
      </div>
      <div>
        <div class="sh__eyebrow"><span class="sh__eyebrow-line"></span>Most downloaded</div>
        <h2 class="sh__h">This week's <em>spotlight</em></h2>
        <p class="spotlight-quote">&ldquo;<?= htmlspecialchars($spot['title']) ?>&rdquo;<?php if ($spot['character_name']): ?> — <?= htmlspecialchars($spot['character_name']) ?><?php endif; ?></p>
        <div class="spotlight-stats">
          <div>
            <span class="spotlight-stat-num"><?= fmtNum((int)$spot['downloads']) ?></span>
            <span class="spotlight-stat-lbl">Downloads</span>
          </div>
          <div>
            <span class="spotlight-stat-num"><?= fmtNum((int)$spot['views']) ?></span>
            <span class="spotlight-stat-lbl">Views</span>
          </div>
          <div>
            <span class="spotlight-stat-num"><?= htmlspecialchars($spot['resolution'] ?: '4K') ?></span>
            <span class="spotlight-stat-lbl">Resolution</span>
          </div>
        </div>
        <a href="download.php?id=<?= $spot['id'] ?>&type=desktop" class="btn btn--primary">Download This Wallpaper</a>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════════════════
     CATEGORIES — INDEX LIST
     ═══════════════════════════════════════════════════════════ -->
<section class="cat-sec">
  <div class="container">
    <div class="sh">
      <div>
        <div class="sh__eyebrow"><span class="sh__eyebrow-line"></span>Explore</div>
        <h2 class="sh__h">Browse by <em>category</em></h2>
      </div>
    </div>
    <div class="cat-strip">
      <?php foreach ($categories as $i => $cat): ?>
      <a href="<?= htmlspecialchars($cat['link']) ?>" class="cat-item"
         style="--c:<?= $cat['color'] ?>;--c-glow:<?= $cat['glow'] ?>;--delay:<?= $i*60 ?>ms">
        <span class="cat-item__index"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
        <i class="fas fa-arrow-right cat-item__arrow"></i>
        <div class="cat-item__icon"><i class="fas fa-<?= $cat['icon'] ?>"></i></div>
        <span class="cat-item__name"><?= htmlspecialchars($cat['name']) ?></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════
     TRENDING & POPULAR
     ═══════════════════════════════════════════════════════════ -->
<section class="sec">
  <div class="container">
    <div class="sh">
      <div>
        <div class="sh__eyebrow"><span class="sh__eyebrow-line"></span>Live rankings</div>
        <h2 class="sh__h">Trending <em>&amp;</em> popular</h2>
        <p class="sh__sub">Real-time community rankings, refreshed continuously.</p>
      </div>
      <div class="tabs">
        <button class="tab-btn active" data-tab="dl"><i class="fas fa-download"></i> Most Downloaded</button>
        <button class="tab-btn" data-tab="vw"><i class="fas fa-eye"></i> Most Viewed</button>
      </div>
    </div>

    <!-- Downloads Panel -->
    <div class="tab-panel active" id="panel-dl">
      <?php if (empty($top_downloads)): ?>
        <div class="empty"><i class="fas fa-image"></i><p>No wallpapers yet.</p></div>
      <?php else: ?>
      <div class="wgrid">
        <?php foreach ($top_downloads as $i => $w):
          $mx  = (int)($top_downloads[0]['downloads'] ?? 1);
          $pct = $mx > 0 ? round($w['downloads'] / $mx * 100) : 0;
        ?>
        <a href="download.php?id=<?= $w['id'] ?>&type=desktop" class="wcard" style="--delay:<?= $i*55 ?>ms">
          <div class="wcard__thumb">
            <img src="<?= htmlspecialchars($w['image_path']) ?>" alt="<?= htmlspecialchars($w['title']) ?>" loading="lazy">
            <div class="wcard__rank <?= $i===0?'wcard__rank--1':'' ?>"><?= str_pad($i+1, 2, '0', STR_PAD_LEFT) ?></div>
            <div class="wcard__cat"><?= htmlspecialchars($w['category_name'] ?? 'Anime') ?></div>
            <?php if ($w['resolution']): ?>
            <div class="wcard__res"><?= htmlspecialchars($w['resolution']) ?></div>
            <?php endif; ?>
            <div class="wcard__bar"><div class="wcard__bar-fill" style="--p:<?= $pct ?>%"></div></div>
            <div class="wcard__overlay"><div class="wcard__overlay-label">Download</div></div>
          </div>
          <div class="wcard__body">
            <div>
              <div class="wcard__title"><?= htmlspecialchars($w['title']) ?></div>
              <?php if ($w['character_name']): ?>
              <div class="wcard__char"><?= htmlspecialchars($w['character_name']) ?></div>
              <?php endif; ?>
            </div>
            <div class="wcard__stats">
              <span class="wstat"><i class="fas fa-download"></i> <?= fmtNum((int)$w['downloads']) ?></span>
              <span class="wstat"><i class="fas fa-eye"></i> <?= fmtNum((int)$w['views']) ?></span>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- Views Panel -->
    <div class="tab-panel" id="panel-vw">
      <?php if (empty($top_views)): ?>
        <div class="empty"><i class="fas fa-image"></i><p>No wallpapers yet.</p></div>
      <?php else: ?>
      <div class="wgrid">
        <?php foreach ($top_views as $i => $w):
          $mx  = (int)($top_views[0]['views'] ?? 1);
          $pct = $mx > 0 ? round($w['views'] / $mx * 100) : 0;
        ?>
        <a href="download.php?id=<?= $w['id'] ?>&type=desktop" class="wcard" style="--delay:<?= $i*55 ?>ms">
          <div class="wcard__thumb">
            <img src="<?= htmlspecialchars($w['image_path']) ?>" alt="<?= htmlspecialchars($w['title']) ?>" loading="lazy">
            <div class="wcard__rank <?= $i===0?'wcard__rank--1':'' ?>"><?= str_pad($i+1, 2, '0', STR_PAD_LEFT) ?></div>
            <div class="wcard__cat"><?= htmlspecialchars($w['category_name'] ?? 'Anime') ?></div>
            <?php if ($w['resolution']): ?>
            <div class="wcard__res"><?= htmlspecialchars($w['resolution']) ?></div>
            <?php endif; ?>
            <div class="wcard__bar"><div class="wcard__bar-fill" style="--p:<?= $pct ?>%"></div></div>
            <div class="wcard__overlay"><div class="wcard__overlay-label">Download</div></div>
          </div>
          <div class="wcard__body">
            <div>
              <div class="wcard__title"><?= htmlspecialchars($w['title']) ?></div>
              <?php if ($w['character_name']): ?>
              <div class="wcard__char"><?= htmlspecialchars($w['character_name']) ?></div>
              <?php endif; ?>
            </div>
            <div class="wcard__stats">
              <span class="wstat"><i class="fas fa-eye"></i> <?= fmtNum((int)$w['views']) ?></span>
              <span class="wstat"><i class="fas fa-download"></i> <?= fmtNum((int)$w['downloads']) ?></span>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════
     NEW ARRIVALS — MOSAIC
     ═══════════════════════════════════════════════════════════ -->
<?php if (!empty($newest)): ?>
<section class="sec sec--alt">
  <div class="container">
    <div class="sh">
      <div>
        <div class="sh__eyebrow"><span class="sh__eyebrow-line"></span>Just added</div>
        <h2 class="sh__h">New <em>arrivals</em></h2>
        <p class="sh__sub">Fresh drops, just uploaded.</p>
      </div>
      <a href="categories.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="mosaic">
      <?php foreach ($newest as $i => $w): ?>
      <a href="download.php?id=<?= $w['id'] ?>&type=desktop" class="mosaic-item" style="--delay:<?= $i*60 ?>ms">
        <img src="<?= htmlspecialchars($w['image_path']) ?>" alt="<?= htmlspecialchars($w['title']) ?>" loading="lazy">
        <span class="mosaic-item__new-badge">New</span>
        <div class="mosaic-item__overlay">
          <div class="mosaic-item__title"><?= htmlspecialchars($w['title']) ?></div>
          <div class="mosaic-item__stats">
            <span class="wstat"><i class="fas fa-download"></i> <?= fmtNum((int)$w['downloads']) ?></span>
            <span class="wstat"><i class="fas fa-eye"></i> <?= fmtNum((int)$w['views']) ?></span>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>



<!-- ═══════════════════════════════════════════════════════════
     JAVASCRIPT
     ═══════════════════════════════════════════════════════════ -->
<script>
document.addEventListener('DOMContentLoaded', () => {

  /* ── Disable right-click / drag on images ──────────────── */
  document.addEventListener('contextmenu', e => { if (e.target.tagName === 'IMG') e.preventDefault(); });
  document.querySelectorAll('img').forEach(img => img.addEventListener('dragstart', e => e.preventDefault()));

  /* ── Animated counters ─────────────────────────────────── */
  const obs = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      const el = entry.target;
      const target = parseInt(el.dataset.target);
      const dur = 1800, step = 16;
      const inc = target / (dur / step);
      let cur = 0;
      const t = setInterval(() => {
        cur = Math.min(cur + inc, target);
        el.textContent = Math.floor(cur).toLocaleString();
        if (cur >= target) clearInterval(t);
      }, step);
      obs.unobserve(el);
    });
  }, { threshold: 0.5 });
  document.querySelectorAll('.ctr__num').forEach(el => obs.observe(el));

  /* ── Scroll reveal ─────────────────────────────────────── */
  const revObs = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) { e.target.style.animationPlayState = 'running'; revObs.unobserve(e.target); }
    });
  }, { threshold: 0.08, rootMargin: '0px 0px -30px 0px' });
  document.querySelectorAll('.wcard, .mosaic-item').forEach(el => { el.style.animationPlayState = 'paused'; revObs.observe(el); });

  /* ── Hero frame parallax tilt ──────────────────────────── */
  const heroFrame = document.querySelector('.hero__frame');
  const heroPlate = document.querySelector('.hero__plate');
  if (heroFrame && heroPlate && window.matchMedia('(min-width: 1101px)').matches
      && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    heroPlate.addEventListener('mousemove', e => {
      const r = heroFrame.getBoundingClientRect();
      const px = (e.clientX - r.left) / r.width - 0.5;
      const py = (e.clientY - r.top) / r.height - 0.5;
      heroFrame.style.transform = `rotateY(${px * 6}deg) rotateX(${py * -6}deg)`;
    });
    heroPlate.addEventListener('mouseleave', () => {
      heroFrame.style.transform = '';
    });
  }

  /* ── Hero feature crossfade ────────────────────────────── */
  const slides = document.querySelectorAll('#heroSlides [data-slide]');
  const indexEl = document.getElementById('heroFrameIndex');
  if (slides.length > 1) {
    let cur = 0;
    setInterval(() => {
      slides[cur].classList.remove('active');
      cur = (cur + 1) % slides.length;
      slides[cur].classList.add('active');
      if (indexEl) indexEl.textContent = String(cur + 1).padStart(2, '0') + ' / ' + String(slides.length).padStart(2, '0');
    }, 4500);
  }

  /* ── Tabs ──────────────────────────────────────────────── */
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = 'panel-' + btn.dataset.tab;
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      const panel = document.getElementById(id);
      if (panel) {
        panel.classList.add('active');
        panel.querySelectorAll('.wcard__bar-fill').forEach(bar => {
          const p = getComputedStyle(bar).getPropertyValue('--p');
          bar.style.setProperty('--p', '0%');
          requestAnimationFrame(() => requestAnimationFrame(() => bar.style.setProperty('--p', p)));
        });
      }
    });
  });

  /* ── Newsletter form ───────────────────────────────────── */
  const nlForm = document.getElementById('nlForm');
  if (nlForm) {
    nlForm.addEventListener('submit', e => {
      e.preventDefault();
      const input = nlForm.querySelector('input[type="email"]');
      const btn = nlForm.querySelector('button[type="submit"]');
      if (input.value && input.value.includes('@')) {
        const orig = btn.textContent;
        btn.textContent = 'Subscribed ✓';
        input.value = '';
        setTimeout(() => { btn.textContent = orig; }, 3000);
      }
    });
  }

});
</script>

<?php include 'footer.php'; ?>