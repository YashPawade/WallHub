<?php // footer.php – WallHub Redesigned Footer ?>

<footer id="wh-footer">

    <!-- ░░ DEPTH LAYERS ░░ -->
    <canvas id="wh-stars" aria-hidden="true"></canvas>
    <div class="wh-aurora">
        <div class="ab ab1"></div>
        <div class="ab ab2"></div>
        <div class="ab ab3"></div>
    </div>
    <div class="wh-noise"></div>

    <!-- ░░ DIAGONAL SLASH ENTRY ░░ -->
    <div class="wh-slash" aria-hidden="true">
        <svg viewBox="0 0 1440 90" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="slashFill" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%"   stop-color="#05050d"/>
                    <stop offset="50%"  stop-color="#07071a"/>
                    <stop offset="100%" stop-color="#05050d"/>
                </linearGradient>
            </defs>
            <polygon points="0,90 1440,18 1440,90" fill="url(#slashFill)"/>
            <line x1="0" y1="90" x2="1440" y2="18" stroke="rgba(99,102,241,0.18)" stroke-width="1"/>
        </svg>
    </div>

    <div class="wh-wrap">

        <!-- ░░ TICKER ░░ -->
        <div class="wh-ticker" aria-hidden="true">
            <div class="ticker-fade ticker-fade--l"></div>
            <div class="ticker-track">
                <?php
                $items = ['ONE PIECE','NARUTO','BLEACH','JUJUTSU KAISEN','MY HERO ACADEMIA','DRAGON BALL','DEMON SLAYER','ATTACK ON TITAN','CHAINSAW MAN','HUNTER × HUNTER','SOLO LEVELING','VINLAND SAGA','JOHN WICK','STRANGER THINGS','BREAKING BAD','CYBERPUNK'];
                $ticker = implode(' <span class="tdot">◆</span> ', array_merge($items,$items));
                ?>
                <span class="ti"><?= $ticker ?></span>
                <span class="ti" aria-hidden="true"><?= $ticker ?></span>
            </div>
            <div class="ticker-fade ticker-fade--r"></div>
        </div>

        <!-- ░░ HERO BRAND ROW ░░ -->
        <div class="wh-brand-hero">
            <!-- ========================================== -->
            <!-- LOGO - IMAGE VERSION - 120px              -->
            <!-- ========================================== -->
            <a href="index" class="wh-logo" aria-label="WallHub home">
                <img src="/images/aaaaa.png" 
                     alt="WallHub - Premium Wallpapers" 
                     class="footer-logo-img"
                     style="height: 10000px; width: auto; display: block;">
                     <strong>Premium Wallpapers</strong>
                <span class="logo-pulse"></span>
            </a>

            <p class="brand-desc">
                Anime · Movies · Gaming · Nature · Fantasy · Space<br>
                <strong>The ultimate wallpaper universe — curated in 4K.</strong>
            </p>

            <div class="social-row">
                <a href="telegram" class="soc-btn soc-btn--tg" aria-label="Join Telegram">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                    Join Channel
                </a>
            </div>
        </div>

        <!-- ░░ MAIN GRID ░░ -->
        <div class="wh-grid">

            <!-- NAVIGATE -->
            <div class="wh-col">
                <h4 class="col-ttl"><span class="bar"></span>Navigate</h4>
                <nav class="nav-list">
                    <a href="support"  class="nl-item"><span class="nl-icon"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><circle cx="12" cy="17" r="1" fill="currentColor"/></svg></span>Support</a>
                    <a href="telegram" class="nl-item"><span class="nl-icon"><svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg></span>Telegram</a>
                    <a href="privacy"  class="nl-item"><span class="nl-icon"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></span>Privacy</a>
                    <a href="terms"    class="nl-item"><span class="nl-icon"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></span>Terms</a>
                    <a href="cookies"  class="nl-item"><span class="nl-icon"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/></svg></span>Cookies</a>
                    <a href="dmca"     class="nl-item"><span class="nl-icon"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg></span>DMCA</a>
                </nav>
            </div>

            <!-- CATEGORIES -->
            <div class="wh-col">
                <h4 class="col-ttl"><span class="bar"></span>Categories</h4>
                <div class="cat-grid">
                    <a href="/anime" class="cat-pill cat-anime">Anime</a>
                    <a href="/movies" class="cat-pill cat-movie">Movies</a>
                    <a href="/mobile" class="cat-pill cat-fantasy">Mobile</a>
                    <a href="/gaming" class="cat-pill cat-gaming">Gaming</a>
                    <a href="/animal" class="cat-pill cat-space">Animal</a>
                    <a href="/nature" class="cat-pill cat-nature">Nature</a>
                </div>
            </div>

            <!-- CONTACT -->
            <div class="wh-col">
                <h4 class="col-ttl"><span class="bar"></span>Get In Touch</h4>
                <div class="cc-stack">
                    <div class="cc">
                        <div class="cc-icon-wrap cc-purple">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        </div>
                        <div class="cc-body">
                            <span class="cc-lbl">Email support</span>
                            <a href="mailto:support@wallhub.online" class="cc-val">support@wallhub.online</a>
                        </div>
                    </div>
                    <div class="cc">
                        <div class="cc-icon-wrap cc-green">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <div class="cc-body">
                            <span class="cc-lbl">Response time</span>
                            <span class="cc-val cc-val--green">Under 24 hours</span>
                        </div>
                    </div>
                    <div class="cc cc--stat">
                        <div class="stat-item">
                            <span class="stat-num">2K+</span>
                            <span class="stat-label">Wallpapers</span>
                        </div>
                        <div class="stat-div"></div>
                        <div class="stat-item">
                            <span class="stat-num">4K</span>
                            <span class="stat-label">Resolution</span>
                        </div>
                        <div class="stat-div"></div>
                        <div class="stat-item">
                            <span class="stat-num">Free</span>
                            <span class="stat-label">Always</span>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /wh-grid -->

        <!-- ░░ BOTTOM BAR ░░ -->
        <div class="wh-bottom">
            <p class="copy">© <?= date('Y') ?> WallHub · All rights reserved</p>
            <div class="bottom-badges">
                <span class="badge"><span class="badge-dot badge-dot--green"></span>All systems operational</span>
            </div>
            <span class="made-with">Made with <span class="hrt">♥</span> for fans worldwide</span>
        </div>

    </div><!-- /wh-wrap -->
</footer>

<!-- ═══════════════ CSS ═══════════════ -->
<style>
/* TOKENS */
#wh-footer {
    --f-bg:      #05050d;
    --f-bg2:     #08081a;
    --f-purple:  #6366f1;
    --f-violet:  #8b5cf6;
    --f-gold:    #f59e0b;
    --f-teal:    #0ea5e9;
    --f-green:   #10b981;
    --f-red:     #ef4444;
    --f-text:    #e2e8f0;
    --f-muted:   #94a3b8;
    --f-dim:     #475569;
    --f-border:  rgba(255,255,255,.06);
    --f-card:    rgba(255,255,255,.03);
    --f-glass:   rgba(255,255,255,.05);
    font-family: 'Poppins', 'Segoe UI', system-ui, sans-serif;
}

/* WRAPPER */
#wh-footer {
    position: relative;
    background: var(--f-bg);
    color: var(--f-text);
    overflow: hidden;
    margin-top: 0;
}

/* STAR CANVAS */
#wh-stars {
    position: absolute; inset: 0; z-index: 0;
    width: 100%; height: 100%;
    pointer-events: none; opacity: .6;
}

/* AURORA */
.wh-aurora { position: absolute; inset: 0; z-index: 0; pointer-events: none; }
.ab {
    position: absolute; border-radius: 50%;
    filter: blur(110px); opacity: .15;
}
.ab1 {
    width: 800px; height: 320px;
    background: radial-gradient(ellipse, #6366f1, #8b5cf6 60%, transparent);
    top: -100px; left: -180px;
    animation: afloat 20s ease-in-out infinite;
}
.ab2 {
    width: 500px; height: 260px;
    background: radial-gradient(ellipse, #f59e0b, #ef4444 60%, transparent);
    bottom: 60px; right: -80px;
    animation: afloat 25s ease-in-out infinite reverse;
    opacity: .09;
}
.ab3 {
    width: 380px; height: 200px;
    background: radial-gradient(ellipse, #0ea5e9, #10b981 60%, transparent);
    top: 55%; left: 45%;
    animation: afloat 16s ease-in-out infinite 6s;
    opacity: .08;
}
@keyframes afloat {
    0%,100%{transform:translate(0,0) scale(1)}
    40%{transform:translate(50px,-40px) scale(1.08)}
    70%{transform:translate(-30px,25px) scale(.96)}
}

/* NOISE OVERLAY */
.wh-noise {
    position: absolute; inset: 0; z-index: 1; pointer-events: none;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");
    background-size: 200px 200px;
    opacity: .4;
}

/* SLASH DIVIDER */
.wh-slash {
    position: relative; z-index: 2;
    line-height: 0; margin-bottom: -2px;
}
.wh-slash svg { width: 100%; height: 80px; display: block; }

/* WRAP */
.wh-wrap {
    position: relative; z-index: 3;
    max-width: 1280px; margin: 0 auto;
    padding: 0 32px 52px;
}

/* TICKER */
.wh-ticker {
    position: relative;
    overflow: hidden;
    border-top: 1px solid var(--f-border);
    border-bottom: 1px solid var(--f-border);
    padding: 13px 0;
    margin-bottom: 72px;
    background: linear-gradient(90deg, rgba(99,102,241,.06), rgba(139,92,246,.04), rgba(99,102,241,.06));
}
.ticker-fade {
    position: absolute; top: 0; bottom: 0; width: 100px; z-index: 2; pointer-events: none;
}
.ticker-fade--l { left: 0; background: linear-gradient(90deg, var(--f-bg), transparent); }
.ticker-fade--r { right: 0; background: linear-gradient(-90deg, var(--f-bg), transparent); }
.ticker-track { display: flex; white-space: nowrap; }
.ti {
    display: inline-block;
    animation: tick 40s linear infinite;
    font-size: .68rem; font-weight: 800;
    letter-spacing: .2em; text-transform: uppercase;
    color: var(--f-dim); padding-right: 48px;
}
.ti:nth-child(2) { animation-delay: -20s; }
.tdot { color: var(--f-purple); opacity: .7; }
@keyframes tick { from{transform:translateX(0)} to{transform:translateX(-100%)} }

/* ========================================== */
/* BRAND HERO - UPDATED WITH IMAGE LOGO     */
/* ========================================== */
.wh-brand-hero {
    display: flex; flex-direction: column; align-items: center; text-align: center;
    margin-bottom: 72px;
}

/* ========================================== */
/* LOGO - IMAGE VERSION - 120px              */
/* ========================================== */
.wh-logo {
    display: inline-flex !important;
    flex-direction: column !important;
    align-items: center !important;
    text-decoration: none !important;
    margin-bottom: 20px;
    gap: 8px !important;
}

.footer-logo-img {
    height: 120px !important;
    width: auto !important;
    display: block !important;
    filter: brightness(0) invert(1) !important;
    transition: transform .35s cubic-bezier(.34,1.56,.64,1) !important;
}

.wh-logo:hover .footer-logo-img {
    transform: scale(1.08) !important;
}

/* Hide old logo elements */
.logo-icon {
    display: none !important;
}

.logo-text {
    display: none !important;
}

.logo-tag {
    font-size: .6rem !important;
    font-weight: 500 !important;
    letter-spacing: .24em !important;
    text-transform: uppercase !important;
    color: rgba(255,255,255,.25) !important;
    margin-top: 4px !important;
}

.logo-pulse {
    width: 9px !important;
    height: 9px !important;
    border-radius: 50% !important;
    background: #ff6b35 !important;
    flex-shrink: 0 !important;
    box-shadow: 0 0 14px 5px rgba(255,107,53,.55) !important;
    animation: pulse 2.4s ease-in-out infinite !important;
}

@keyframes pulse {
    0%,100%{opacity:1;transform:scale(1);box-shadow:0 0 14px 5px rgba(255,107,53,.55)}
    50%{opacity:.35;transform:scale(.45);box-shadow:0 0 4px 2px rgba(255,107,53,.2)}
}

.brand-desc {
    color: var(--f-muted); font-size: .92rem; line-height: 1.8;
    max-width: 440px; margin-bottom: 28px;
}
.brand-desc strong { color: var(--f-text); font-weight: 600; }

/* SOCIAL ROW */
.social-row { display: flex; gap: 12px; flex-wrap: wrap; justify-content: center; }
.soc-btn {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 9px 20px; border-radius: 40px;
    font-size: .82rem; font-weight: 700; text-decoration: none;
    letter-spacing: .03em; transition: all .25s;
}
.soc-btn--tg {
    background: linear-gradient(135deg, #229ED9, #1a8cbd);
    color: #fff;
    box-shadow: 0 4px 20px rgba(34,158,217,.35);
}
.soc-btn--tg:hover { transform: translateY(-3px); box-shadow: 0 8px 28px rgba(34,158,217,.5); }

/* SEPARATOR */
.wh-sep {
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--f-purple) 30%, var(--f-violet) 70%, transparent);
    margin-bottom: 64px; opacity: .25;
}

/* MAIN GRID */
.wh-grid {
    display: grid;
    grid-template-columns: 1.2fr 1fr 1.4fr;
    gap: 48px;
    margin-bottom: 64px;
    padding-bottom: 60px;
    border-bottom: 1px solid var(--f-border);
}

/* COL TITLE */
.col-ttl {
    display: flex; align-items: center; gap: 10px;
    font-size: .68rem; font-weight: 800; letter-spacing: .14em;
    text-transform: uppercase; color: var(--f-dim);
    margin: 0 0 22px;
}
.col-ttl .bar {
    display: inline-block; width: 22px; height: 2px; border-radius: 1px; flex-shrink: 0;
    background: linear-gradient(90deg, var(--f-purple), var(--f-gold));
}

/* NAV LIST */
.nav-list { display: flex; flex-direction: column; gap: 4px; }
.nl-item {
    display: flex; align-items: center; gap: 10px;
    color: var(--f-muted); text-decoration: none;
    font-size: .87rem; padding: 8px 12px; border-radius: 10px;
    border: 1px solid transparent;
    transition: all .2s;
}
.nl-icon {
    width: 26px; height: 26px; border-radius: 8px; flex-shrink: 0;
    background: var(--f-card); display: flex; align-items: center; justify-content: center;
    color: var(--f-dim); transition: all .2s;
}
.nl-item:hover {
    color: var(--f-text);
    background: rgba(99,102,241,.08);
    border-color: rgba(99,102,241,.2);
    padding-left: 16px;
}
.nl-item:hover .nl-icon {
    background: rgba(99,102,241,.18);
    color: var(--f-purple);
}

/* CATEGORY PILLS */
.cat-grid { display: flex; flex-wrap: wrap; gap: 8px; }
.cat-pill {
    padding: 6px 14px; border-radius: 30px;
    font-size: .75rem; font-weight: 700; letter-spacing: .04em;
    text-decoration: none; transition: all .22s;
    border: 1px solid transparent;
}
.cat-anime   { background: rgba(139,92,246,.12); color: #a78bfa; border-color: rgba(139,92,246,.25); }
.cat-gaming  { background: rgba(16,185,129,.1);  color: #6ee7b7; border-color: rgba(16,185,129,.25); }
.cat-space   { background: rgba(99,102,241,.12); color: #818cf8; border-color: rgba(99,102,241,.25); }
.cat-nature  { background: rgba(34,197,94,.1);   color: #86efac; border-color: rgba(34,197,94,.25); }
.cat-fantasy { background: rgba(245,158,11,.1);  color: #fcd34d; border-color: rgba(245,158,11,.25); }
.cat-movie   { background: rgba(239,68,68,.1);   color: #fca5a5; border-color: rgba(239,68,68,.25); }
.cat-4k      { background: rgba(14,165,233,.1);  color: #7dd3fc; border-color: rgba(14,165,233,.25); }
.cat-minimal { background: rgba(148,163,184,.08); color: #94a3b8; border-color: rgba(148,163,184,.2); }
.cat-pill:hover { transform: translateY(-2px) scale(1.05); filter: brightness(1.15); }

/* CONTACT CARDS */
.cc-stack { display: flex; flex-direction: column; gap: 10px; }
.cc {
    display: flex; align-items: center; gap: 14px;
    background: var(--f-glass);
    border: 1px solid var(--f-border);
    border-radius: 14px; padding: 14px 16px;
    backdrop-filter: blur(6px);
    transition: border-color .2s, transform .2s, background .2s;
}
.cc:hover { border-color: rgba(99,102,241,.3); background: rgba(99,102,241,.06); transform: translateX(4px); }
.cc-icon-wrap {
    width: 38px; height: 38px; border-radius: 11px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
}
.cc-purple { background: rgba(99,102,241,.15); color: #818cf8; }
.cc-green  { background: rgba(16,185,129,.15); color: #34d399; }
.cc-lbl { display: block; font-size: .62rem; text-transform: uppercase; letter-spacing: .1em; color: var(--f-dim); margin-bottom: 3px; }
.cc-val { display: block; font-size: .85rem; font-weight: 600; color: var(--f-text); text-decoration: none; transition: color .2s; }
.cc-val:hover { color: var(--f-purple); }
.cc-val--green { color: #34d399 !important; }

/* STATS CARD */
.cc--stat {
    flex-direction: row; justify-content: space-around; align-items: center;
    background: linear-gradient(135deg, rgba(99,102,241,.08), rgba(139,92,246,.06));
    border-color: rgba(99,102,241,.18);
    padding: 16px;
}
.cc--stat:hover { transform: none; }
.stat-item { text-align: center; }
.stat-num { display: block; font-size: 1.1rem; font-weight: 800; color: var(--f-text); font-family: 'Orbitron', sans-serif; }
.stat-label { display: block; font-size: .6rem; text-transform: uppercase; letter-spacing: .1em; color: var(--f-dim); margin-top: 3px; }
.stat-div { width: 1px; height: 32px; background: var(--f-border); }

/* BOTTOM BAR */
.wh-bottom {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 16px;
}
.copy { color: var(--f-dim); font-size: .82rem; margin: 0; }
.made-with { font-size: .82rem; color: var(--f-dim); }
.hrt { color: #ef4444; display: inline-block; animation: hbeat 1.6s infinite; }
@keyframes hbeat {
    0%,100%{transform:scale(1)} 15%{transform:scale(1.35)} 30%{transform:scale(1)} 45%{transform:scale(1.2)}
}

.bottom-badges { display: flex; gap: 8px; }
.badge {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: .72rem; color: var(--f-dim);
    background: var(--f-card); border: 1px solid var(--f-border);
    padding: 4px 12px; border-radius: 20px;
}
.badge-dot {
    width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0;
    animation: pulse 2s infinite;
}
.badge-dot--green { background: #10b981; box-shadow: 0 0 6px rgba(16,185,129,.6); }

/* RESPONSIVE */
@media (max-width: 960px) {
    .wh-grid { grid-template-columns: 1fr 1fr; }
    .wh-col:last-child { grid-column: span 2; }
}
@media (max-width: 640px) {
    .wh-wrap { padding: 0 18px 40px; }
    .wh-grid { grid-template-columns: 1fr; gap: 36px; }
    .wh-col:last-child { grid-column: span 1; }
    .wh-bottom { flex-direction: column; align-items: center; text-align: center; }
    .footer-logo-img { height: 80px !important; } /* Slightly smaller on mobile but still big */
    .cc--stat { gap: 8px; }
}
@media (prefers-reduced-motion: reduce) {
    .ab,.ti,.logo-pulse,.hrt { animation: none; }
}
</style>

<!-- ═══════════════ JS ═══════════════ -->
<script>
(function(){
    const canvas = document.getElementById('wh-stars');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    let stars = [], raf;

    function resize() {
        canvas.width  = canvas.offsetWidth;
        canvas.height = canvas.offsetHeight;
        stars = Array.from({ length: 150 }, () => ({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            r: Math.random() * 1.4 + .15,
            a: Math.random(),
            s: (Math.random() - .5) * .004,
            color: Math.random() > .85
                ? `rgba(${[99+Math.random()*60|0},${102+Math.random()*40|0},241,`
                : 'rgba(255,255,255,'
        }));
    }

    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        for (const s of stars) {
            s.a = Math.max(.04, Math.min(1, s.a + s.s));
            if (s.a <= .04 || s.a >= 1) s.s *= -1;
            ctx.beginPath();
            ctx.arc(s.x, s.y, s.r, 0, Math.PI * 2);
            ctx.fillStyle = s.color + s.a + ')';
            ctx.fill();
        }
        raf = requestAnimationFrame(draw);
    }

    const ro = new ResizeObserver(resize);
    ro.observe(canvas.parentElement);
    resize(); draw();

    const io = new IntersectionObserver(([e]) => {
        if (e.isIntersecting) { if (!raf) draw(); }
        else { cancelAnimationFrame(raf); raf = null; }
    });
    io.observe(canvas.parentElement);
})();
</script>

<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-966S46KZ4J"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-966S46KZ4J');
</script>

</body>
</html>