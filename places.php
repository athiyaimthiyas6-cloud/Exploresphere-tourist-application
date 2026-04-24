<?php

require_once 'config/db.php';

$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search_term     = isset($_GET['search'])   ? trim($_GET['search'])   : '';

$where = "WHERE 1=1";  

if ($category_filter > 0) {
    $where .= " AND places.category_id = $category_filter";
}

if ($search_term !== '') {
    $safe = $conn->real_escape_string($search_term);
    $where .= " AND (places.name LIKE '%$safe%' OR places.description LIKE '%$safe%')";
}

$places = $conn->query("
    SELECT places.*, categories.category_name, categories.icon
    FROM places
    JOIN categories ON places.category_id = categories.category_id
    $where
    ORDER BY places.rating DESC
");

$all_categories = $conn->query("SELECT * FROM categories ORDER BY category_id");

$active_cat_name = '';
if ($category_filter > 0) {
    $r = $conn->query("SELECT category_name FROM categories WHERE category_id = $category_filter");
    $active_cat_name = $r->fetch_assoc()['category_name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Places — ExploreSphere</title>
    <link rel="stylesheet" href="assets/css/style.css?v=1">

    <style>

        /* ── Page Header ── */
        .page-header {
            background: linear-gradient(to right, #FFF5EE, #FFF9F5);
            padding: 40px 30px;
            border-bottom: 1px solid var(--border);
        }

        .page-header h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 1.7rem;
            font-weight: 700;
            color: var(--dark);
        }

        .page-header p {
            color: var(--muted);
            font-size: 0.9rem;
            margin-top: 4px;
        }

        /* ── Search Bar ── */
        .search-form {
            display: flex;
            gap: 8px;
            margin-top: 16px;
            max-width: 440px;
        }

        .search-form input {
            flex: 1;
        }

        /* ── Filter Chips (Category Buttons) ── */
        .filter-bar {
            background: white;
            padding: 14px 30px;
            border-bottom: 1px solid var(--border);
            /* Horizontal scroll on small screens */
            overflow-x: auto;
            white-space: nowrap;
        }

        .filter-chip {
            display: inline-block;
            padding: 7px 16px;
            border-radius: 20px;
            border: 1.5px solid var(--border);
            background: white;
            font-size: 0.84rem;
            font-weight: 600;
            color: var(--text);
            cursor: pointer;
            text-decoration: none;
            margin-right: 8px;
            transition: all 0.2s;
        }

        .filter-chip:hover {
            border-color: var(--orange);
            color: var(--orange);
        }

        /* Highlighted active chip */
        .filter-chip.active {
            background: var(--orange);
            color: white;
            border-color: var(--orange);
        }

        /* ── Results Info Bar ── */
        .results-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .results-bar .count {
            font-size: 0.9rem;
            color: var(--muted);
        }

        .results-bar .count strong {
            color: var(--dark);
        }

        /* ── Place Cards (same style as homepage) ── */
        .place-card-body  { padding: 16px 18px; }
        .place-card-title {
            font-family: 'Poppins', sans-serif;
            font-size: 0.98rem;
            font-weight: 600;
            color: var(--dark);
            margin: 8px 0 5px;
        }
        .place-card-desc {
            font-size: 0.83rem;
            color: var(--muted);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .place-card-footer {
            padding: 11px 18px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            font-size: 0.82rem;
            color: var(--muted);
            background: #FAFAFA;
        }
        .place-card-footer .dist { color: var(--teal); font-weight: 700; }
        .place-card-footer .rate { color: #F59E0B; font-weight: 700; }

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


<div class="page-header">
    <h1>
        <?php if ($active_cat_name): ?>
            <?= htmlspecialchars($active_cat_name) ?> Places
        <?php elseif ($search_term): ?>
            Results for "<?= htmlspecialchars($search_term) ?>"
        <?php else: ?>
            🗺️ Explore All Places
        <?php endif; ?>
    </h1>
    <p>Tourist attractions within 25 km of Makola</p>

    <!-- Search Form -->
    <form action="places.php" method="GET" class="search-form">
        <?php if ($category_filter): ?>
            <!-- Keep the category filter when searching -->
            <input type="hidden" name="category" value="<?= $category_filter ?>">
        <?php endif; ?>
        <input type="text" name="search" placeholder="Search places..."
               value="<?= htmlspecialchars($search_term) ?>">
        <button type="submit" class="btn btn-orange btn-sm">Search</button>
    </form>
</div>


<div class="filter-bar">

    <!-- "All" chip -->
    <a href="places.php<?= $search_term ? '?search='.urlencode($search_term) : '' ?>"
       class="filter-chip <?= !$category_filter ? 'active' : '' ?>">
       🌟 All
    </a>

    <?php
    // One chip per category
    while ($cat = $all_categories->fetch_assoc()) {
        $is_active = ($category_filter == $cat['category_id']);
        $url = 'places.php?category=' . $cat['category_id'];
        if ($search_term) $url .= '&search=' . urlencode($search_term);
    ?>
    <a href="<?= $url ?>" class="filter-chip <?= $is_active ? 'active' : '' ?>">
        <?= $cat['icon'] ?> <?= htmlspecialchars($cat['category_name']) ?>
    </a>
    <?php } ?>

</div>


<div class="section">

    <div class="results-bar">
        <div class="count">
            Showing <strong><?= $places->num_rows ?></strong> place<?= $places->num_rows != 1 ? 's' : '' ?>
            <?= $active_cat_name ? "in <strong>$active_cat_name</strong>" : '' ?>
            <?= $search_term ? " for &quot;" . htmlspecialchars($search_term) . "&quot;" : '' ?>
        </div>
        <?php if ($category_filter || $search_term): ?>
            <a href="places.php" class="btn btn-outline btn-sm">✕ Clear Filters</a>
        <?php endif; ?>
    </div>

    <?php if ($places->num_rows === 0): ?>
    <div class="empty-state">
        <div class="icon">🔭</div>
        <h3>No places found</h3>
        <p>Try a different search or browse all places.</p>
        <a href="places.php" class="btn btn-orange mt-16">Browse All</a>
    </div>

    <?php else: ?>
    <div class="grid-3">
        <?php while ($place = $places->fetch_assoc()): ?>

        <a href="place.php?id=<?= $place['place_id'] ?>" class="card" style="text-decoration:none; color:inherit;">

            <div class="card-img-box">
                <?php if (!empty($place['image']) && file_exists("assets/images/" . $place['image'])): ?>
                    <img src="assets/images/<?= htmlspecialchars($place['image']) ?>"
                         alt="<?= htmlspecialchars($place['name']) ?>">
                <?php else: ?>
                    <div class="card-img-placeholder" style="background: #FFF0EA;">
                        <?= $place['icon'] ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ── PLACE INFO ── -->
            <div class="place-card-body">
                <span class="badge" style="background:var(--orange-light); color:var(--orange);">
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

        <?php endwhile; ?>
    </div>
    <?php endif; ?>

</div>


<div class="footer">
    <p>© 2026 <strong>ExploreSphere</strong> · Athiya MIF · E2320129 · ITE2953</p>
</div>


</body>
</html>
<?php $conn->close(); ?>
