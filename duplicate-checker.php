<?php
// duplicate-checker.php - Owner-only tool to find duplicate & similar wallpapers
// Replaces check_owner.php + detector.php + get-wallpapers.php with one
// consolidated, DB-backed tool.
//
// WHY THE REWRITE:
// 1. SECURITY: the old detector.php gated access with a password stored in
//    localStorage (base64 "encoded" - trivially reversible, and checked
//    entirely in the browser). Anyone could bypass it via devtools. The real
//    protection was always the server-side session check in get-wallpapers.php.
//    This version checks the real PHP session up front and never renders
//    anything to non-owners - no separate client-side login screen needed.
// 2. SIMILARITY, NOT JUST EXACT MATCHES: the old scanner grouped images by
//    exact hash string equality, so two versions of the same wallpaper saved
//    at a different size/quality (different hash) would never be flagged.
//    This version compares every pair via Hamming distance on an 8x8 average
//    hash, so near-duplicates are caught too, with an adjustable threshold.
// 3. DB-BACKED: the old scanner walked the /images/ folder directly, so
//    results were just filenames with no way to act on them. This version
//    pulls from desktop_wallpapers / mobile_wallpapers so every result shows
//    its real title/category and can be deleted directly from this page.

session_start();
include('includes/db.php');

// ============================================
// REAL SERVER-SIDE AUTH — owner only
// ============================================
$isOwner = false;
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'owner') {
    $isOwner = true;
}
if (isset($_SESSION['username']) && $_SESSION['username'] === 'Ash') {
    $isOwner = true; // kept as a safety net, matching your existing convention
}
if (!$isOwner) {
    header('Location: login.php?error=unauthorized');
    exit();
}

// ============================================
// AJAX: list wallpapers (id, type, title, path, category)
// ============================================
if (isset($_GET['action']) && $_GET['action'] === 'list') {
    header('Content-Type: application/json');
    $items = [];

    $q1 = mysqli_query($conn, "SELECT w.id, w.title, w.image_path, c.name AS category_name
                                FROM desktop_wallpapers w
                                LEFT JOIN categories c ON w.category_id = c.id");
    if ($q1 && !is_bool($q1)) {
        while ($row = mysqli_fetch_assoc($q1)) {
            $items[] = [
                'id' => (int)$row['id'],
                'type' => 'desktop',
                'title' => $row['title'],
                'category' => $row['category_name'] ?? 'Uncategorized',
                'path' => $row['image_path'],
            ];
        }
    }

    $q2 = mysqli_query($conn, "SELECT w.id, w.title, w.image_path, c.name AS category_name
                                FROM mobile_wallpapers w
                                LEFT JOIN categories c ON w.category_id = c.id");
    if ($q2 && !is_bool($q2)) {
        while ($row = mysqli_fetch_assoc($q2)) {
            $items[] = [
                'id' => (int)$row['id'],
                'type' => 'mobile',
                'title' => $row['title'],
                'category' => $row['category_name'] ?? 'Uncategorized',
                'path' => $row['image_path'],
            ];
        }
    }

    echo json_encode(['success' => true, 'items' => $items]);
    exit();
}

// ============================================
// AJAX: delete a wallpaper (DB row + file)
// ============================================
if (isset($_GET['action']) && $_GET['action'] === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $id = (int)($_POST['id'] ?? 0);
    $type = ($_POST['type'] ?? '') === 'mobile' ? 'mobile' : 'desktop';
    $table = $type === 'mobile' ? 'mobile_wallpapers' : 'desktop_wallpapers';

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        exit();
    }

    $pathResult = mysqli_query($conn, "SELECT image_path FROM $table WHERE id = $id");
    if ($pathResult && !is_bool($pathResult) && mysqli_num_rows($pathResult) > 0) {
        $row = mysqli_fetch_assoc($pathResult);
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . $row['image_path'];
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    $ok = mysqli_query($conn, "DELETE FROM $table WHERE id = $id");
    echo json_encode(['success' => (bool)$ok]);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duplicate & Similar Image Detector - WallHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .stats-card { transition: transform 0.2s; }
        .stats-card:hover { transform: translateY(-2px); }
        .owner-badge { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .scanning-bar { transition: width 0.3s ease; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen">
    <div id="app"></div>

    <script>
        // ============================================
        // APP STATE
        // ============================================
        let appState = {
            items: [],          // raw wallpaper list from DB
            hashed: [],         // items with computed hash
            duplicates: [],     // pairs above the similarity threshold
            failed: [],         // items that could not be loaded/analyzed
            isScanning: false,
            scanProgress: 0,
            loadedCount: 0,
            totalCount: 0,
            message: '',
            error: '',
            threshold: 5,       // Hamming distance <= threshold counts as a match
            hasScanned: false
        };

        // ============================================
        // RENDER
        // ============================================
        function render() {
            document.getElementById('app').innerHTML = dashboardView();
        }

        function dashboardView() {
            const totalUnique = appState.hashed.length - new Set(appState.duplicates.flatMap(d => [d.a.id + '-' + d.a.type, d.b.id + '-' + d.b.type])).size;

            return `
            <div class="min-h-screen bg-slate-900">
                <nav class="sticky top-0 bg-slate-800 border-b border-slate-700 p-4 z-10">
                    <div class="max-w-7xl mx-auto flex justify-between items-center flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">🖼️</span>
                            <h1 class="text-xl font-bold text-white">Duplicate &amp; Similar Image Detector</h1>
                            <span class="text-xs owner-badge text-white px-2 py-1 rounded">👑 OWNER</span>
                        </div>
                        <span class="text-sm text-slate-400">${appState.items.length} wallpapers in library</span>
                    </div>
                </nav>

                <div class="max-w-7xl mx-auto p-6 space-y-6">

                    <!-- Stats -->
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div class="stats-card bg-slate-800 p-4 rounded-xl border border-slate-700">
                            <div class="text-2xl font-bold text-white">${appState.items.length}</div>
                            <div class="text-xs text-slate-400">Total Images</div>
                        </div>
                        <div class="stats-card bg-slate-800 p-4 rounded-xl border border-slate-700">
                            <div class="text-2xl font-bold text-blue-400">${appState.loadedCount}</div>
                            <div class="text-xs text-slate-400">Analyzed</div>
                        </div>
                        <div class="stats-card bg-slate-800 p-4 rounded-xl border border-slate-700">
                            <div class="text-2xl font-bold text-orange-500">${appState.duplicates.length}</div>
                            <div class="text-xs text-slate-400">Similar Pairs</div>
                        </div>
                        <div class="stats-card bg-slate-800 p-4 rounded-xl border border-slate-700">
                            <div class="text-2xl font-bold text-green-500">${appState.hasScanned ? totalUnique : appState.items.length}</div>
                            <div class="text-xs text-slate-400">Unique</div>
                        </div>
                        <div class="stats-card bg-slate-800 p-4 rounded-xl border border-slate-700">
                            <div class="text-2xl font-bold text-red-400">${appState.failed.length}</div>
                            <div class="text-xs text-slate-400">Could Not Analyze</div>
                        </div>
                    </div>

                    ${appState.error ? `
                        <div class="bg-red-500/10 border border-red-500/30 p-4 rounded-xl text-red-400">
                            <i class="fas fa-exclamation-circle"></i> ${appState.error}
                        </div>
                    ` : ''}

                    <!-- Threshold + Scan controls -->
                    <div class="bg-slate-800 p-6 rounded-xl border border-slate-700 space-y-4">
                        <div class="flex flex-wrap items-center gap-4 justify-between">
                            <div>
                                <label class="block text-sm text-slate-400 mb-1">Similarity sensitivity</label>
                                <select id="thresholdSelect" class="bg-slate-700 border border-slate-600 rounded-lg px-3 py-2 text-sm" ${appState.isScanning ? 'disabled' : ''}>
                                    <option value="0" ${appState.threshold === 0 ? 'selected' : ''}>Identical only (distance 0)</option>
                                    <option value="5" ${appState.threshold === 5 ? 'selected' : ''}>Near-duplicate (distance ≤ 5)</option>
                                    <option value="10" ${appState.threshold === 10 ? 'selected' : ''}>Similar (distance ≤ 10)</option>
                                    <option value="15" ${appState.threshold === 15 ? 'selected' : ''}>Loosely similar (distance ≤ 15)</option>
                                </select>
                            </div>
                            <button onclick="startScan()" ${appState.isScanning ? 'disabled' : ''} class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 px-6 py-3 rounded-xl font-semibold transition flex items-center gap-2">
                                <i class="fas fa-magnifying-glass"></i> ${appState.hasScanned ? 'Re-scan Library' : 'Start Scanning'}
                            </button>
                        </div>

                        ${appState.isScanning ? `
                            <div class="pt-2 border-t border-slate-700">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm text-slate-300">${appState.message}</span>
                                    <span class="text-sm font-mono text-blue-400">${Math.round(appState.scanProgress)}%</span>
                                </div>
                                <div class="w-full bg-slate-700 rounded-full h-2 overflow-hidden">
                                    <div class="scanning-bar h-full bg-gradient-to-r from-blue-500 to-green-500" style="width: ${appState.scanProgress}%"></div>
                                </div>
                            </div>
                        ` : ''}
                    </div>

                    <!-- Results -->
                    ${appState.duplicates.length > 0 ? `
                        <div class="bg-slate-800 p-6 rounded-xl border border-orange-500/20">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-white"><span class="text-orange-500">⚠️</span> Similar Images Found</h3>
                                <span class="text-sm bg-orange-500/20 text-orange-400 px-3 py-1 rounded-full">${appState.duplicates.length} pairs</span>
                            </div>
                            <div class="space-y-4 max-h-[700px] overflow-y-auto pr-2">
                                ${appState.duplicates.map((dup, idx) => `
                                    <div class="bg-slate-700/50 p-4 rounded-lg border border-slate-600" id="pair-${idx}">
                                        <div class="flex justify-between items-center mb-3">
                                            <span class="text-xs font-mono text-orange-400 bg-orange-500/10 px-2 py-1 rounded">#${idx + 1}</span>
                                            <span class="text-xs ${dup.similarity >= 95 ? 'text-red-400' : 'text-yellow-400'}">${dup.similarity}% similar (distance ${dup.distance})</span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            ${[dup.a, dup.b].map(item => `
                                                <div class="bg-slate-800 rounded-lg overflow-hidden">
                                                    <img src="${item.path}" alt="${item.title}" class="w-full h-36 object-cover" loading="lazy"
                                                         onerror="this.src=''; this.alt='Failed to load';">
                                                    <div class="p-2 space-y-1">
                                                        <div class="text-xs text-slate-300 truncate font-semibold">${item.title || 'Untitled'}</div>
                                                        <div class="text-[10px] text-slate-500 font-mono truncate" title="${item.path}">${filename(item.path)}</div>
                                                        <div class="flex items-center gap-2 text-[10px] text-slate-500">
                                                            <span class="bg-slate-700 px-1.5 py-0.5 rounded uppercase">${item.type}</span>
                                                            <span>${item.category}</span>
                                                        </div>
                                                        <button onclick="deleteItem(${item.id}, '${item.type}', ${idx})" class="w-full mt-1 bg-red-600/80 hover:bg-red-600 text-white text-xs py-1.5 rounded transition">
                                                            <i class="fas fa-trash"></i> Delete This One
                                                        </button>
                                                    </div>
                                                </div>
                                            `).join('')}
                                        </div>
                                        <div class="mt-3 text-xs text-slate-500 font-mono break-all bg-slate-800 p-2 rounded">
                                            <span class="text-slate-400">Path:</span> ${shortPath(dup.a.path)}
                                            <span class="text-slate-600 mx-2">↔</span>
                                            ${shortPath(dup.b.path)}
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    ` : appState.hasScanned && !appState.isScanning ? `
                        <div class="bg-green-500/10 border border-green-500/20 p-6 rounded-xl text-center">
                            <div class="text-4xl mb-2">✅</div>
                            <h3 class="text-xl font-semibold text-green-400">No Similar Images Found</h3>
                            <p class="text-slate-400 mt-1">All ${appState.loadedCount} analyzed images look unique at this threshold. Try a looser sensitivity if you expect matches.</p>
                        </div>
                    ` : ''}

                    ${appState.failed.length > 0 && appState.hasScanned && !appState.isScanning ? `
                        <details class="bg-slate-800 rounded-xl border border-red-500/20 p-6">
                            <summary class="cursor-pointer text-lg font-semibold text-white flex items-center gap-2">
                                <span class="text-red-400"><i class="fas fa-triangle-exclamation"></i></span>
                                ${appState.failed.length} images could not be analyzed
                                <span class="text-sm font-normal text-slate-400">(click to view - likely broken links or moved files)</span>
                            </summary>
                            <div class="mt-4 space-y-2 max-h-96 overflow-y-auto pr-2">
                                ${appState.failed.map(f => `
                                    <div class="flex items-center justify-between gap-4 bg-slate-700/50 px-4 py-2 rounded-lg text-sm">
                                        <div class="min-w-0">
                                            <div class="text-slate-200 truncate">${f.title || 'Untitled'} <span class="text-slate-500">(${f.type})</span></div>
                                            <div class="text-slate-500 font-mono text-xs truncate">${shortPath(f.path)}</div>
                                        </div>
                                        <span class="text-red-400 text-xs whitespace-nowrap">${f.reason}</span>
                                    </div>
                                `).join('')}
                            </div>
                        </details>
                    ` : ''}
                </div>
            </div>
            `;
        }

        // ============================================
        // PATH DISPLAY HELPERS
        // ============================================
        function filename(path) {
            return path.split('/').pop();
        }
        function shortPath(path) {
            // Strip a leading "/images/" (or just "/") so it reads like
            // "bleach/1254093_..._35544.jpg" instead of the full server path.
            return path.replace(/^\/?images\//i, '').replace(/^\//, '');
        }

        // ============================================
        // SCAN ENGINE
        // ============================================
        async function startScan() {
            const select = document.getElementById('thresholdSelect');
            if (select) appState.threshold = parseInt(select.value, 10);

            appState.isScanning = true;
            appState.scanProgress = 0;
            appState.message = 'Fetching wallpaper list...';
            appState.duplicates = [];
            appState.hashed = [];
            appState.failed = [];
            appState.loadedCount = 0;
            appState.error = '';
            render();

            try {
                const res = await fetch('duplicate-checker.php?action=list');
                const data = await res.json();

                if (!data.success || !data.items || data.items.length === 0) {
                    appState.error = 'No wallpapers found.';
                    appState.isScanning = false;
                    render();
                    return;
                }

                appState.items = data.items;
                appState.totalCount = data.items.length;
                render();

                // Load + hash in batches to keep the browser responsive
                const batchSize = 10;
                let processed = 0;

                for (let i = 0; i < appState.items.length; i += batchSize) {
                    const batch = appState.items.slice(i, i + batchSize);

                    await Promise.all(batch.map(async (item) => {
                        const attempt = () => new Promise((resolve) => {
                            const img = new Image();
                            const timeout = setTimeout(() => resolve({ ok: false, reason: 'Timed out (20s)' }), 20000);
                            img.onload = () => { clearTimeout(timeout); resolve({ ok: true, img }); };
                            img.onerror = () => { clearTimeout(timeout); resolve({ ok: false, reason: 'File not found / failed to load' }); };
                            img.src = item.path;
                        });

                        // One retry before giving up - handles transient network blips
                        let result = await attempt();
                        if (!result.ok) result = await attempt();

                        if (result.ok) {
                            try {
                                const hash = averageHash(result.img);
                                appState.hashed.push({ ...item, hash });
                                appState.loadedCount++;
                            } catch (e) {
                                appState.failed.push({ ...item, reason: 'Could not read pixel data (' + e.message + ')' });
                            }
                        } else {
                            appState.failed.push({ ...item, reason: result.reason });
                        }
                    }));

                    processed += batch.length;
                    appState.scanProgress = Math.min((processed / appState.items.length) * 85, 85);
                    appState.message = `Analyzed ${appState.loadedCount}/${appState.items.length} images`;
                    render();
                }

                appState.message = 'Comparing images for similarity...';
                appState.scanProgress = 92;
                render();

                // Yield to the render before the O(n^2) comparison pass
                await new Promise(r => setTimeout(r, 30));

                appState.duplicates = findSimilarPairs(appState.hashed, appState.threshold);
                appState.isScanning = false;
                appState.scanProgress = 100;
                appState.hasScanned = true;
                appState.message = `Complete! Found ${appState.duplicates.length} similar pairs` + (appState.failed.length ? `, ${appState.failed.length} images could not be analyzed` : '');
                render();

            } catch (error) {
                console.error('Scan error:', error);
                appState.error = 'Scan failed: ' + error.message;
                appState.isScanning = false;
                render();
            }
        }

        // ============================================
        // AVERAGE HASH (8x8 grayscale, 64-bit)
        // ============================================
        function averageHash(img) {
            const canvas = document.createElement('canvas');
            canvas.width = 8;
            canvas.height = 8;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, 8, 8);
            const data = ctx.getImageData(0, 0, 8, 8).data;

            let sum = 0;
            const lums = [];
            for (let i = 0; i < data.length; i += 4) {
                const lum = 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
                lums.push(lum);
                sum += lum;
            }
            const avg = sum / lums.length;

            return lums.map(l => (l > avg ? '1' : '0')).join('');
        }

        // ============================================
        // HAMMING DISTANCE + PAIRWISE SIMILARITY SEARCH
        // ============================================
        function hammingDistance(a, b) {
            let d = 0;
            for (let i = 0; i < a.length; i++) {
                if (a[i] !== b[i]) d++;
            }
            return d;
        }

        function findSimilarPairs(hashedItems, threshold) {
            const pairs = [];
            for (let i = 0; i < hashedItems.length; i++) {
                for (let j = i + 1; j < hashedItems.length; j++) {
                    const dist = hammingDistance(hashedItems[i].hash, hashedItems[j].hash);
                    if (dist <= threshold) {
                        pairs.push({
                            a: hashedItems[i],
                            b: hashedItems[j],
                            distance: dist,
                            similarity: Math.round(((64 - dist) / 64) * 100)
                        });
                    }
                }
            }
            // Most similar first
            pairs.sort((x, y) => x.distance - y.distance);
            return pairs;
        }

        // ============================================
        // DELETE
        // ============================================
        async function deleteItem(id, type, pairIdx) {
            if (!confirm('Delete this wallpaper permanently? This cannot be undone.')) return;

            try {
                const res = await fetch('duplicate-checker.php?action=delete', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${id}&type=${type}`
                });
                const data = await res.json();

                if (data.success) {
                    const el = document.getElementById('pair-' + pairIdx);
                    if (el) el.remove();
                    appState.duplicates = appState.duplicates.filter((_, i) => i !== pairIdx);
                } else {
                    alert('Failed to delete: ' + (data.message || 'Unknown error'));
                }
            } catch (e) {
                alert('Delete request failed.');
            }
        }

        // ============================================
        // INIT
        // ============================================
        document.addEventListener('DOMContentLoaded', render);
        window.startScan = startScan;
        window.deleteItem = deleteItem;
    </script>
</body>
</html>