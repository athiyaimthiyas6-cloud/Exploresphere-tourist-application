<?php

require_once 'config/db.php';

$categories = $conn->query("SELECT * FROM categories ORDER BY category_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories — ExploreSphere</title>
    <link rel="stylesheet" href="assets/css/style.css?v=1">

    <style>

        /* ── Page Header ── */
        .page-header {
            background: linear-gradient(to right, #F0FBFF, #FFF5EE);
            padding: 40px 30px;
            border-bottom: 1px solid var(--border);
            text-align: center;
        }

        /* ── Category Cards ── */
        .cat-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
            transition: box-shadow 0.2s, transform 0.2s;
        }

        .cat-card:hover {
            box-shadow: 0 6px 20px rgba(0,0,0,0.09);
            transform: translateY(-3px);
        }

        /* Top coloured header area */
        .cat-top {
            background: var(--orange-light);
            padding: 28px 20px 20px;
            text-align: center;
        }

        .cat-top .big-icon { font-size: 2.8rem; margin-bottom: 8px; }

        .cat-top h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--dark);
        }

        .cat-top .desc {
            font-size: 0.8rem;
            color: var(--muted);
            margin-top: 4px;
        }

        /* List of places inside category */
        .cat-places {
            padding: 0 16px;
        }

        .cat-place-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
            text-decoration: none;
            color: inherit;
            font-size: 0.88rem;
        }

        .cat-place-row:last-child { border-bottom: none; }

        .cat-place-row:hover .place-name-text {
            color: var(--orange);
        }

        .place-name-text {
            font-weight: 600;
            color: var(--dark);
            transition: color 0.2s;
        }

        .place-km {
            font-size: 0.78rem;
            color: var(--teal);
            font-weight: 700;
        }

        /* Card footer with count + button */
        .cat-footer {
            padding: 14px 16px;
            background: #FAFAFA;
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .count-badge {
            background: var(--orange-light);
            color: var(--orange);
            font-size: 0.78rem;
            font-weight: 700;
            padding: 3px 12px;
            border-radius: 20px;
        }

    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="logo">Explore<span>Sphere</span></a>
    <ul>
        <li><a href="index.php">🏠 Home</a></li>
        <li><a href="places.php">🗺️ Explore</a></li>
        <li><a href="categories.php" class="active">📂 Categories</a></li>
        <li><a href="planner.php" class="btn-nav">📋 My Plan</a></li>
    </ul>
</nav>


<div class="page-header">
    <h1 style="font-family:'Poppins',sans-serif; font-size:1.8rem; color:var(--dark);">
        📂 Browse by Category
    </h1>
    <p style="color:var(--muted); margin-top:6px;">
        Find the type of place you're looking for
    </p>
</div>


<div class="section">
    <div class="grid-3">
        <?php
        $desc_map = [
            'Religious'       => 'Temples, mosques & places of worship',
            'Nature'          => 'Parks, gardens & natural spaces',
            'Historical'      => 'Museums, towers & landmarks',
            'Entertainment'   => 'Indoor fun & activity centres',
            'Food & Shopping' => 'Restaurants, cafes & malls',
            'Leisure'         => 'Beaches, promenades & relaxation spots',
        ];

        while ($cat = $categories->fetch_assoc()):
            $places = $conn->query("
                SELECT place_id, name, distance
                FROM places
                WHERE category_id = {$cat['category_id']}
                ORDER BY rating DESC
            ");
            $count = $places->num_rows;
            $desc  = $desc_map[$cat['category_name']] ?? 'Discover places in this category';
        ?>

        <div class="cat-card">

            <!-- Category header -->
            <div class="cat-top">
                <div class="big-icon"><?= $cat['icon'] ?></div>
                <h3><?= htmlspecialchars($cat['category_name']) ?></h3>
                <div class="desc"><?= $desc ?></div>
            </div>

            <!-- Places list -->
            <div class="cat-places">
                <?php while ($pl = $places->fetch_assoc()): ?>
                <a href="place.php?id=<?= $pl['place_id'] ?>" class="cat-place-row">
                    <span class="place-name-text">→ <?= htmlspecialchars($pl['name']) ?></span>
                    <span class="place-km">📍 <?= $pl['distance'] ?> km</span>
                </a>
                <?php endwhile; ?>
            </div>

            <!-- Footer -->
            <div class="cat-footer">
                <span class="count-badge"><?= $count ?> place<?= $count != 1 ? 's' : '' ?></span>
                <a href="places.php?category=<?= $cat['category_id'] ?>"
                   class="btn btn-orange btn-sm">
                    Explore All →
                </a>
            </div>

        </div>

        <?php endwhile; ?>
    </div>
</div>


<div class="footer">
    <p>© 2026 <strong>ExploreSphere</strong> · Athiya MIF · E2320129 · ITE2953</p>
</div>


</body>
</html>
<?php $conn->close(); ?>
