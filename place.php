<?php
session_start();
require_once 'config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: places.php');
    exit;
}

$result = $conn->query("
    SELECT places.*, categories.category_name, categories.icon
    FROM places
    JOIN categories ON places.category_id = categories.category_id
    WHERE places.place_id = $id
");

if ($result->num_rows === 0) {
    header('Location: places.php');
    exit;
}

$place = $result->fetch_assoc();

if (isset($_POST['add_to_plan'])) {
    if (!isset($_SESSION['plan'])) {
        $_SESSION['plan'] = [];
    }

    $already = false;
    foreach ($_SESSION['plan'] as $item) {
        if ($item['place_id'] == $id) {
            $already = true;
            break;
        }
    }

    if (!$already) {
        $_SESSION['plan'][] = [
            'place_id' => $id,
            'name'     => $place['name'],
            'icon'     => $place['icon']
        ];
    }

    header("Location: place.php?id=$id&added=1");
    exit;
}

$in_plan = false;
if (isset($_SESSION['plan'])) {
    foreach ($_SESSION['plan'] as $item) {
        if ($item['place_id'] == $id) {
            $in_plan = true;
            break;
        }
    }
}

$just_added = isset($_GET['added']);

$related = $conn->query("
    SELECT places.*, categories.icon, categories.category_name
    FROM places
    JOIN categories ON places.category_id = categories.category_id
    WHERE places.category_id = {$place['category_id']}
      AND places.place_id != $id
    LIMIT 3
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($place['name']) ?> — ExploreSphere</title>
    <link rel="stylesheet" href="assets/css/style.css?v=1">
    <style>

        .place-hero {
            width: 100%;
            height: 320px;
            background: var(--orange-light);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .place-hero img {
            width: 100%;
            height: 100%;
            object-fit: cover;  
        }

        .place-hero .emoji-placeholder {
            font-size: 7rem;
        }

        .back-link {
            position: absolute;
            top: 16px;
            left: 20px;
            background: white;
            padding: 7px 16px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text);
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.10);
        }

        .back-link:hover {
            background: var(--orange-light);
            color: var(--orange);
        }

        .detail-layout {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 30px;
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px 30px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin: 24px 0;
        }

        .info-box {
            background: white;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 16px;
        }

        .info-box .info-label {
            font-size: 0.74rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--muted);
            margin-bottom: 5px;
        }

        .info-box .info-value {
            font-size: 0.92rem;
            font-weight: 700;
            color: var(--dark);
        }

        .sidebar {
            position: sticky;
            top: 80px;
            align-self: start;
        }

        .sidebar-box {
            background: white;
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 16px;
        }

        .sidebar-box .box-header {
            background: #FAFAFA;
            padding: 14px 18px;
            border-bottom: 1px solid var(--border);
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--dark);
        }

        .sidebar-box .box-body {
            padding: 18px;
        }

        .map-frame {
            width: 100%;
            height: 220px;
            border: none;
            border-radius: 8px;
            display: block;
        }

        .toast {
            position: fixed;
            top: 80px;
            right: 20px;
            background: #16a34a;
            color: white;
            padding: 12px 20px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.9rem;
            z-index: 999;
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-top: 16px;
        }

        .related-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s;
        }

        .related-card:hover {
            box-shadow: 0 4px 14px rgba(0,0,0,0.10);
            transform: translateY(-2px);
        }

        .related-img-box {
            height: 110px;
            background: var(--orange-light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            overflow: hidden;
        }

        .related-img-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .related-info {
            padding: 10px 12px;
            font-size: 0.84rem;
            font-weight: 700;
            color: var(--dark);
        }

        .related-dist {
            font-size: 0.76rem;
            color: var(--teal);
            font-weight: 600;
        }

        @media (max-width: 800px) {
            .detail-layout { grid-template-columns: 1fr; }
            .related-grid  { grid-template-columns: 1fr 1fr; }
            .info-grid     { grid-template-columns: 1fr; }
        }

    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="logo">Explore<span>Sphere</span></a>
    <ul>
        <li><a href="index.php">🏠 Home</a></li>
        <li><a href="places.php" class="active">🗺️ Explore</a></li>
        <li><a href="categories.php">📂 Categories</a></li>
        <li><a href="planner.php" class="btn-nav">📋 My Plan</a></li>
    </ul>
</nav>


<div class="place-hero">

    <?php if (!empty($place['image']) && file_exists("assets/images/" . $place['image'])): ?>

        <img src="assets/images/<?= htmlspecialchars($place['image']) ?>"
             alt="<?= htmlspecialchars($place['name']) ?>">
    <?php else: ?>
        <div class="emoji-placeholder"><?= $place['icon'] ?></div>
    <?php endif; ?>

    <a href="places.php?category=<?= $place['category_id'] ?>" class="back-link">
        ← <?= htmlspecialchars($place['category_name']) ?>
    </a>
</div>


<?php if ($just_added): ?>
<div class="toast" id="toast">✅ Added to your day plan!</div>
<script>
    setTimeout(function() {
        var t = document.getElementById('toast');
        if (t) t.style.display = 'none';
    }, 3000);
</script>
<?php endif; ?>


<div class="detail-layout">

    <div>

        <!-- Category badge -->
        <span class="badge" style="background:var(--orange-light); color:var(--orange);">
            <?= $place['icon'] ?> <?= htmlspecialchars($place['category_name']) ?>
        </span>

        <!-- Place Name -->
        <h1 style="font-family:'Poppins',sans-serif; font-size:1.8rem; color:var(--dark); margin:10px 0 6px;">
            <?= htmlspecialchars($place['name']) ?>
        </h1>

        <!-- Address and Rating -->
        <p style="color:var(--muted); font-size:0.9rem; margin-bottom:18px;">
            📍 <?= htmlspecialchars($place['address']) ?> &nbsp;|&nbsp;
            📏 <?= $place['distance'] ?> km from Makola &nbsp;|&nbsp;
            ⭐ <?= number_format($place['rating'], 1) ?> / 5.0
        </p>

        <!-- Description -->
        <p style="font-size:0.96rem; line-height:1.8; color:var(--text); margin-bottom:10px;">
            <?= nl2br(htmlspecialchars($place['description'])) ?>
        </p>

        <div class="info-grid">
            <div class="info-box">
                <div class="info-label">🕐 Opens</div>
                <div class="info-value"><?= htmlspecialchars($place['opening_hours']) ?></div>
            </div>
            <div class="info-box">
                <div class="info-label">🕙 Closes</div>
                <div class="info-value"><?= htmlspecialchars($place['closing_hours']) ?></div>
            </div>
            <div class="info-box">
                <div class="info-label">🎫 Entry Fee</div>
                <div class="info-value"><?= htmlspecialchars($place['entry_fee']) ?></div>
            </div>
            <div class="info-box">
                <div class="info-label">📏 Distance</div>
                <div class="info-value"><?= $place['distance'] ?> km from Makola</div>
            </div>
        </div>

        
        <!-- ── VISITOR TIPS ── -->
        <div style="background:#FFF9F5; border:1px solid #FFD8C0; border-radius:10px; padding:18px; margin-top:10px;">
            <div style="font-weight:700; color:var(--dark); margin-bottom:10px;">💡 Visitor Tips</div>
            <ul style="list-style:none; display:flex; flex-direction:column; gap:7px; font-size:0.88rem;">
                <li>✅ Arrive early in the morning to avoid large crowds.</li>
                <li>✅ Opening hours may differ on public holidays — check before visiting.</li>
                <li>✅ Wear comfortable shoes and carry water.</li>
                <li>✅Follow location-specific rules (especially in religious or cultural sites).</li>
                <li>✅Keep your belongings safe and avoid carrying unnecessary valuables.</li>
                <li>✅Respect the environment and dispose of waste properly.</li>
                <li>✅Use online maps or navigation for easier travel planning.</li>
                <li>✅Be mindful of photography restrictions in certain areas.</li>
            </ul>
        </div>

    </div>

    <div class="sidebar">

        <!-- MAP -->
        <div class="sidebar-box">
            <div class="box-header">📍 Location on Map</div>
            <div class="box-body" style="padding:12px;">
                <?php if ($place['latitude'] && $place['longitude']): ?>
                    <iframe class="map-frame"
                        src="https://maps.google.com/maps?q=<?= $place['latitude'] ?>,<?= $place['longitude'] ?>&z=15&output=embed"
                        allowfullscreen loading="lazy">
                    </iframe>
                    <a href="https://maps.google.com/?q=<?= $place['latitude'] ?>,<?= $place['longitude'] ?>"
                       target="_blank"
                       class="btn btn-teal btn-sm btn-full mt-10">
                       🗺️ Open in Google Maps
                    </a>
                <?php else: ?>
                    <p style="color:var(--muted); text-align:center; padding:20px;">
                        Map not available yet.
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <div class="sidebar-box">
            <div class="box-header">📋 Day Planner</div>
            <div class="box-body">
                <?php if ($in_plan || $just_added): ?>
                    <div class="notice notice-green mb-10">✅ Already in your plan!</div>
                    <a href="planner.php" class="btn btn-outline btn-full">View My Plan →</a>
                <?php else: ?>
                    <form method="POST">
                        <button type="submit" name="add_to_plan" class="btn btn-orange btn-full">
                            ➕ Add to Day Plan
                        </button>
                    </form>
                    <p style="font-size:0.78rem; color:var(--muted); text-align:center; margin-top:8px;">
                        Adds this place to your one-day itinerary
                    </p>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>


<?php if ($related && $related->num_rows > 0): ?>
<div class="section" style="padding-top: 0; border-top: 1px solid var(--border);">
    <div class="section-title">More <?= htmlspecialchars($place['category_name']) ?> Places</div>
    <div class="section-sub">You might also like these</div>

    <div class="related-grid">
        <?php while ($rel = $related->fetch_assoc()): ?>
        <a href="place.php?id=<?= $rel['place_id'] ?>" class="related-card">

            <div class="related-img-box">
                <?php if (!empty($rel['image']) && file_exists("assets/images/" . $rel['image'])): ?>
                    <img src="assets/images/<?= htmlspecialchars($rel['image']) ?>"
                         alt="<?= htmlspecialchars($rel['name']) ?>">
                <?php else: ?>
                    <?= $rel['icon'] ?>
                <?php endif; ?>
            </div>

            <div class="related-info">
                <?= htmlspecialchars($rel['name']) ?>
                <div class="related-dist">📍 <?= $rel['distance'] ?> km</div>
            </div>

        </a>
        <?php endwhile; ?>
    </div>
</div>
<?php endif; ?>

<div class="footer">
    <p>© 2026 <strong>ExploreSphere</strong> · Athiya MIF · E2320129 · ITE2953</p>
</div>


</body>
</html>
<?php $conn->close(); ?>
