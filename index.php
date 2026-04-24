<?php
require_once 'config/db.php';

$categories = $conn->query("SELECT * FROM categories ORDER BY category_id");

$featured_places = $conn->query("
    SELECT places.*, categories.category_name, categories.icon
    FROM places
    JOIN categories ON places.category_id = categories.category_id
    ORDER BY places.rating DESC
    LIMIT 6
");

$total = $conn->query("SELECT COUNT(*) AS cnt FROM places")->fetch_assoc()['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExploreSphere — Tourist Guide for Makola</title>
    <link rel="stylesheet" href="assets/css/style.css?v=1">
    <style>

        /* ── Hero Section ── */
        .hero {
            background: url('assets/images/hero.jpg');
            padding: 70px 30px 60px;
            text-align: center;
            border-bottom: 1px solid var(--border);
            background-size: cover;       
            background-position: center;   
            background-repeat: no-repeat;

    width: 100%;
    min-height: 400px;            
    display: flex;
    flex-direction: column;
    justify-content: center;

    text-align: center;
    color: black;
}
    
        .hero h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 2.6rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 14px;
        }

        .hero h1 span {
            color: var(--orange);
        }

        .hero p {
            font-size: 1rem;
            font-weight: bold;
            color: var(--white);
            max-width: 480px;
            margin: 0 auto 30px;

        }

        .hero-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* ── Stats Bar ── */
        .stats-bar {
            background: white;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 24px 30px;
            display: flex;
            justify-content: space-around;
            gap: 20px;
            max-width: 700px;
            margin: -30px auto 50px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            position: relative;
            z-index: 5;
        }

        .stat-item {
            text-align: center;
        }

        .stat-item .num {
            font-family: 'Poppins', sans-serif;
            font-size: 1.9rem;
            font-weight: 700;
            color: var(--orange);
        }

        .stat-item .label {
            font-size: 0.8rem;
            color: var(--muted);
            font-weight: 600;
        }

        /* ── Category Cards ── */
        .cat-section {
            background:url('assets/images/category-bg.jpg');
            background-size: cover;      
            background-position: center;   
            background-repeat: no-repeat;

            width: 100%;
            min-height: 400px;         

            display: flex;
            flex-direction: column;
            justify-content: center;

            text-align: center;
            color: white;
        }

        .cat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 14px;
            max-width: 1100px;
            margin: 0 auto;
        }

        .cat-card {
            background: white;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            padding: 22px 14px;
            text-align: center;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s;
            cursor: pointer;
        }

        .cat-card:hover {
            border-color: var(--orange);
            background: var(--orange-light);
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.08);
        }

        .cat-card .cat-icon  { font-size: 2.2rem; margin-bottom: 8px; }
        .cat-card .cat-name  { font-size: 0.85rem; font-weight: 700; color: var(--dark); }
        .cat-card .cat-count { font-size: 0.75rem; color: var(--muted); margin-top: 3px; }

        /* ── Place Cards ── */
        .place-card-body {
            padding: 16px 18px;
        }

        .place-card-title {
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            color: var(--dark);
            margin: 8px 0 6px;
        }

        .place-card-desc {
            font-size: 0.84rem;
            color: var(--muted);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .place-card-footer {
            padding: 12px 18px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.82rem;
            color: var(--muted);
            background: #FAFAFA;
        }

        .place-card-footer .dist { color: var(--teal); font-weight: 700; }
        .place-card-footer .rate { color: #F59E0B; font-weight: 700; }

        /* ── Bottom Banner ── */
        .cta-banner {
            background: linear-gradient(135deg, var(--orange), #FF8C5A);
            color: white;
            text-align: center;
            padding: 55px 30px;
            margin-top: 0;
        }

        .cta-banner h2 {
            font-family: 'Poppins', sans-serif;
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        .cta-banner p {
            opacity: 0.9;
            margin-bottom: 24px;
        }

        .btn-white {
            background: white;
            color: var(--orange);
            font-weight: 700;
        }

        .btn-white:hover {
            background: #fff0ea;
        }

    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="logo">Explore<span>Sphere</span></a>
    <ul>
        <li><a href="index.php" class="active">🏠 Home</a></li>
        <li><a href="places.php">🗺️ Explore</a></li>
        <li><a href="categories.php">📂 Categories</a></li>
        <li><a href="planner.php" class="btn-nav">📋 My Plan</a></li>
    </ul>
</nav>


<div class="hero">
    <h1>Discover Places Near <span>Makola</span></h1>
    <p>Explore tourist attractions within 25 km of Makola — temples, parks, museums, restaurants and more.</p>
    <div class="hero-buttons">
        <a href="places.php" class="btn btn-orange">🔍 Explore All Places</a>
        <a href="planner.php" class="btn btn-outline">📋 Plan My Day</a>
    </div>
</div>


<div class="section" style="padding-top: 0; padding-bottom: 0;">
    <div class="stats-bar">
        <div class="stat-item">
            <div class="num"><?= $total ?>+</div>
            <div class="label">Tourist Places</div>
        </div>
        <div class="stat-item">
            <div class="num">6</div>
            <div class="label">Categories</div>
        </div>
        <div class="stat-item">
            <div class="num">25km</div>
            <div class="label">Radius</div>
        </div>
        <div class="stat-item">
            <div class="num">Free</div>
            <div class="label">To Browse</div>
        </div>
    </div>
</div>


<div class="cat-section">
    <div style="max-width: 1100px; margin: 0 auto;">
        <div class="flex-between mb-24">
            <div>
                <div class="section-title">Browse by Category</div>
                <div class="section-sub">Pick a type of place you want to visit</div>
            </div>
            <a href="categories.php" class="btn btn-outline btn-sm">View All →</a>
        </div>

        <div class="cat-grid">
            <?php
            while ($cat = $categories->fetch_assoc()) {
                $count = $conn->query("SELECT COUNT(*) AS cnt FROM places WHERE category_id = " . $cat['category_id'])->fetch_assoc()['cnt'];
            ?>
            <a href="places.php?category=<?= $cat['category_id'] ?>" class="cat-card">
                <div class="cat-icon"><?= $cat['icon'] ?></div>
                <div class="cat-name"><?= htmlspecialchars($cat['category_name']) ?></div>
                <div class="cat-count"><?= $count ?> place<?= $count != 1 ? 's' : '' ?></div>
            </a>
            <?php } ?>
        </div>
    </div>
</div>


<div class="section">

    <div class="flex-between mb-24">
        <div>
            <div class="section-title">⭐ Top Rated Places</div>
            <div class="section-sub">Most loved spots by visitors</div>
        </div>
        <a href="places.php" class="btn btn-outline btn-sm">See All →</a>
    </div>

    <div class="grid-3">
        <?php
        while ($place = $featured_places->fetch_assoc()) {
        ?>
        <a href="place.php?id=<?= $place['place_id'] ?>" class="card" style="text-decoration: none; color: inherit;">

            <div class="card-img-box">
                
                <?php if (!empty($place['image']) && file_exists("assets/images/" . $place['image'])): ?>
                    <img src="assets/images/<?= htmlspecialchars($place['image']) ?>"
                         alt="<?= htmlspecialchars($place['name']) ?>">
                <?php else: ?>
                    <div class="card-img-placeholder">
                        <?= $place['icon'] ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="place-card-body">
                <span class="badge" style="background: var(--orange-light); color: var(--orange);">
                    <?= $place['icon'] ?> <?= htmlspecialchars($place['category_name']) ?>
                </span>
                <div class="place-card-title"><?= htmlspecialchars($place['name']) ?></div>
                <div class="place-card-desc"><?= htmlspecialchars($place['description']) ?></div>
            </div>

            <div class="place-card-footer">
                <span class="dist">📍 <?= $place['distance'] ?> km</span>
                <span class="rate">⭐ <?= number_format($place['rating'], 1) ?></span>
            </div>

        </a>
        <?php } ?>
    </div>

</div>


<div class="cta-banner">
    <h2>Plan Your Perfect Day Trip 🗺️</h2>
    <p>Choose your favourite places and create a personalised one-day itinerary.</p>
    <a href="planner.php" class="btn btn-white">Start Planning →</a>
</div>


<div class="footer">
    <p>© 2026 <strong>ExploreSphere</strong> · Athiya MIF · E2320129 · ITE2953 · University of Moratuwa</p>
</div>


</body>
</html>
<?php $conn->close(); ?>


