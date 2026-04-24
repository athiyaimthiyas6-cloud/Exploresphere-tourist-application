<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['plan'])) {
    $_SESSION['plan'] = [];
}

if (isset($_POST['remove'])) {
    $remove_id = (int)$_POST['remove'];
    $_SESSION['plan'] = array_values(
        array_filter($_SESSION['plan'], function($item) use ($remove_id) {
            return $item['place_id'] != $remove_id;
        })
    );
    header('Location: planner.php');
    exit;
}

if (isset($_POST['reset'])) {
    $_SESSION['plan'] = [];
    header('Location: planner.php');
    exit;
}

if (isset($_POST['move_up'])) {
    $i = (int)$_POST['move_up'];
    if ($i > 0) {
        $temp = $_SESSION['plan'][$i - 1];
        $_SESSION['plan'][$i - 1] = $_SESSION['plan'][$i];
        $_SESSION['plan'][$i] = $temp;
    }
    header('Location: planner.php');
    exit;
}

if (isset($_POST['move_down'])) {
    $i = (int)$_POST['move_down'];
    if (isset($_SESSION['plan'][$i + 1])) {
        $temp = $_SESSION['plan'][$i + 1];
        $_SESSION['plan'][$i + 1] = $_SESSION['plan'][$i];
        $_SESSION['plan'][$i] = $temp;
    }
    header('Location: planner.php');
    exit;
}

if (isset($_POST['add_place']) && !empty($_POST['place_id'])) {
    $new_id = (int)$_POST['place_id'];

    $already = false;
    foreach ($_SESSION['plan'] as $item) {
        if ($item['place_id'] == $new_id) {
            $already = true;
            break;
        }
    }

    if (!$already) {
        $p = $conn->query("
            SELECT places.place_id, places.name, categories.icon
            FROM places
            JOIN categories ON places.category_id = categories.category_id
            WHERE places.place_id = $new_id
        ")->fetch_assoc();

        if ($p) {
            $_SESSION['plan'][] = [
                'place_id' => $p['place_id'],
                'name'     => $p['name'],
                'icon'     => $p['icon']
            ];
        }
    }
    header('Location: planner.php');
    exit;
}

$plan_details = [];
foreach ($_SESSION['plan'] as $item) {
    $pid = (int)$item['place_id'];
    $row = $conn->query("
        SELECT places.*, categories.category_name, categories.icon
        FROM places
        JOIN categories ON places.category_id = categories.category_id
        WHERE places.place_id = $pid
    ")->fetch_assoc();
    if ($row) $plan_details[] = $row;
}

$all_places = $conn->query("
    SELECT places.place_id, places.name, categories.category_name, categories.icon
    FROM places
    JOIN categories ON places.category_id = categories.category_id
    ORDER BY categories.category_name, places.name
");

$plan_ids = array_column($_SESSION['plan'], 'place_id');
$stop_count = count($plan_details);
$est_hours  = $stop_count * 1.5;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Day Planner — ExploreSphere</title>
    <link rel="stylesheet" href="assets/css/style.css?v=1">

    <style>

        /* ── Page Header ── */
        .page-header {
            background: linear-gradient(to right, #f7ba8f, #FFFDF8);
            padding: 40px 30px;
            border-bottom: 1px solid var(--border);
        }

        /* ── Two-column layout ── */
        .planner-layout {
            display: grid;
            grid-template-columns: 1fr 280px;
            gap: 28px;
            max-width: 1100px;
            margin: 0 auto;
            padding: 36px 30px;
        }

        /* ── Empty State ── */
        .plan-empty {
            background: white;
            border: 2px dashed var(--border);
            border-radius: 14px;
            padding: 60px 30px;
            text-align: center;
            color: var(--muted);
        }

        /* ── Plan Stop Card ── */
        .stop-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 12px;
            margin-bottom: 14px;
            display: flex;
            overflow: hidden;
        }

        /* Left coloured stripe */
        .stop-stripe {
            width: 5px;
            background: var(--orange);
            flex-shrink: 0;
        }

        /* Step number circle */
        .stop-num {
            width: 36px;
            height: 36px;
            background: var(--orange);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.88rem;
            flex-shrink: 0;
            margin: auto 0 auto 14px;
        }

        /* Main info area */
        .stop-info {
            flex: 1;
            padding: 14px 16px;
        }

        .stop-name {
            font-family: 'Poppins', sans-serif;
            font-size: 0.96rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .stop-meta {
            font-size: 0.8rem;
            color: var(--muted);
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }

        /* Action buttons on the right */
        .stop-actions {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 10px 12px;
            border-left: 1px solid var(--border);
            background: #FAFAFA;
        }

        .icon-btn {
            background: white;
            border: 1.5px solid var(--border);
            border-radius: 6px;
            width: 28px;
            height: 28px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.15s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-btn:hover { background: var(--orange-light); border-color: var(--orange); }
        .icon-btn.remove:hover { background: #fee2e2; border-color: #dc2626; }

        /* Connector line between stops */
        .stop-connector {
            margin: 0 0 14px 30px;
            font-size: 0.76rem;
            color: var(--muted);
            padding: 4px 12px;
            background: #FAFAFA;
            border: 1px solid var(--border);
            border-radius: 20px;
            display: inline-block;
        }

        /* ── Sidebar Boxes ── */
        .sidebar-box {
            background: white;
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 16px;
        }

        .sidebar-box .box-head {
            background: #FAFAFA;
            padding: 13px 16px;
            border-bottom: 1px solid var(--border);
            font-weight: 700;
            font-size: 0.88rem;
            color: var(--dark);
        }

        .sidebar-box .box-body { padding: 16px; }

        /* Summary stats */
        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 9px 0;
            border-bottom: 1px solid var(--border);
            font-size: 0.88rem;
        }

        .summary-row:last-child { border-bottom: none; }
        .summary-row .label { color: var(--muted); }
        .summary-row .val   { font-weight: 700; color: var(--dark); }

        .print-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
            font-size: 0.85rem;
        }

        .print-table th {
            background: var(--orange-light);
            color: var(--dark);
            padding: 8px 12px;
            text-align: left;
            border: 1px solid var(--border);
        }

        .print-table td {
            padding: 8px 12px;
            border: 1px solid var(--border);
        }

        /* Print styles */
        @media print {
            .navbar, .sidebar-box, .stop-actions, .icon-btn, .btn { display: none !important; }
            .planner-layout { grid-template-columns: 1fr; }
        }

        @media (max-width: 800px) {
            .planner-layout { grid-template-columns: 1fr; }
        }

    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="logo">Explore<span>Sphere</span></a>
    <ul>
        <li><a href="index.php">🏠 Home</a></li>
        <li><a href="places.php">🗺️ Explore</a></li>
        <li><a href="categories.php">📂 Categories</a></li>
        <li><a href="planner.php" class="btn-nav active">📋 My Plan</a></li>
    </ul>
</nav>


<div class="page-header">
    <h1 style="font-family:'Poppins',sans-serif; font-size:1.7rem; color:var(--dark);">
        📋 My Day Planner
    </h1>
    <p style="color:var(--muted); font-size:0.9rem; margin-top:4px;">
        Build your one-day itinerary around Makola
    </p>
</div>


<div class="planner-layout">

    <div>

        <div class="flex-between mb-24">
            <div style="font-size:0.9rem; color:var(--muted);">
                <strong style="color:var(--dark);"><?= $stop_count ?></strong>
                stop<?= $stop_count != 1 ? 's' : '' ?> in your plan
            </div>
            <?php if ($stop_count > 0): ?>
            <div style="display:flex; gap:8px;">
                <button onclick="window.print()" class="btn btn-outline btn-sm">🖨️ Print</button>
                <form method="POST" style="display:inline;"
                      onsubmit="return confirm('Are you sure you want to clear the entire plan?');">
                    <button type="submit" name="reset" class="btn btn-red btn-sm">🗑️ Clear All</button>
                </form>
            </div>
            <?php endif; ?>
        </div>


        <?php if ($stop_count === 0): ?>
        <div class="plan-empty">
            <div style="font-size:3rem; margin-bottom:14px;">🗺️</div>
            <h3 style="color:var(--dark); margin-bottom:8px;">Your plan is empty</h3>
            <p style="margin-bottom:20px;">Browse places and click "Add to Day Plan" on any place page,<br>or use the panel on the right to add one now.</p>
            <a href="places.php" class="btn btn-orange">🔍 Browse Places</a>
        </div>

        <?php else: ?>
        <?php foreach ($plan_details as $index => $p): ?>

            <!-- ── STOP CARD ── -->
            <div class="stop-card">
                <!-- Orange stripe on left -->
                <div class="stop-stripe"></div>

                <!-- Step number -->
                <div class="stop-num"><?= $index + 1 ?></div>

                <!-- Place info -->
                <div class="stop-info">
                    <div class="stop-name">
                        <?= $p['icon'] ?> <?= htmlspecialchars($p['name']) ?>
                    </div>
                    <div class="stop-meta">
                        <span>📍 <?= $p['distance'] ?> km</span>
                        <span>🕐 <?= htmlspecialchars($p['opening_hours']) ?></span>
                        <span>🎫 <?= htmlspecialchars($p['entry_fee']) ?></span>
                        <a href="place.php?id=<?= $p['place_id'] ?>"
                           style="color:var(--orange); font-weight:700;">View Details →</a>
                    </div>
                </div>

                <div class="stop-actions">
                    <?php if ($index > 0): ?>
                    <form method="POST">
                        <input type="hidden" name="move_up" value="<?= $index ?>">
                        <button type="submit" class="icon-btn" title="Move Up">▲</button>
                    </form>
                    <?php endif; ?>

                    <form method="POST"
                          onsubmit="return confirm('Remove <?= htmlspecialchars($p['name']) ?> from your plan?');">
                        <input type="hidden" name="remove" value="<?= $p['place_id'] ?>">
                        <button type="submit" class="icon-btn remove" title="Remove">✕</button>
                    </form>

                    <?php if ($index < $stop_count - 1): ?>
                    <form method="POST">
                        <input type="hidden" name="move_down" value="<?= $index ?>">
                        <button type="submit" class="icon-btn" title="Move Down">▼</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($index < $stop_count - 1): ?>
            <div class="stop-connector">🚗 Travel to next stop</div>
            <?php endif; ?>

        <?php endforeach; ?>


        <!-- ── PRINTABLE SUMMARY TABLE ── -->
        <div style="margin-top:36px;">
            <div class="section-title" style="font-size:1.1rem; margin-bottom:4px;">
                📄 Full Plan Summary
            </div>
            <p style="font-size:0.84rem; color:var(--muted); margin-bottom:12px;">
                Print this page for a physical copy of your itinerary.
            </p>
            <table class="print-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Place</th>
                        <th>Category</th>
                        <th>Opens</th>
                        <th>Closes</th>
                        <th>Entry Fee</th>
                        <th>Distance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($plan_details as $i => $p): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= $p['icon'] ?> <?= htmlspecialchars($p['name']) ?></strong></td>
                        <td><?= htmlspecialchars($p['category_name']) ?></td>
                        <td><?= htmlspecialchars($p['opening_hours']) ?></td>
                        <td><?= htmlspecialchars($p['closing_hours']) ?></td>
                        <td><?= htmlspecialchars($p['entry_fee']) ?></td>
                        <td><?= $p['distance'] ?> km</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php endif; ?>
    </div>


    <div>

        <!-- Trip Summary -->
        <div class="sidebar-box">
            <div class="box-head">📊 Trip Summary</div>
            <div class="box-body">
                <div class="summary-row">
                    <span class="label">📍 Total Stops</span>
                    <span class="val"><?= $stop_count ?></span>
                </div>
                <div class="summary-row">
                    <span class="label">⏱️ Estimated Time</span>
                    <span class="val"><?= number_format($est_hours, 1) ?> hrs</span>
                </div>
                <div class="summary-row">
                    <span class="label">📏 Farthest Place</span>
                    <span class="val">
                        <?= $stop_count > 0 ? max(array_column($plan_details, 'distance')) . ' km' : '—' ?>
                    </span>
                </div>
                <div class="summary-row">
                    <span class="label">📂 Categories</span>
                    <span class="val">
                        <?= $stop_count > 0 ? count(array_unique(array_column($plan_details, 'category_name'))) : '0' ?>
                    </span>
                </div>
                <div class="summary-row">
                    <span class="label">📍 Starting Point</span>
                    <span class="val" style="font-size:0.82rem;">Makola, Gampaha</span>
                </div>
            </div>
        </div>

        <!-- Add Place -->
        <div class="sidebar-box">
            <div class="box-head">➕ Add a Place</div>
            <div class="box-body">
                <form method="POST">
                    <select name="place_id" required style="margin-bottom:10px;">
                        <option value="">— Select a place —</option>
                        <?php
                        $current_cat = '';
                        while ($ap = $all_places->fetch_assoc()):
                            // Group by category
                            if ($ap['category_name'] !== $current_cat) {
                                if ($current_cat !== '') echo '</optgroup>';
                                echo '<optgroup label="' . $ap['icon'] . ' ' . htmlspecialchars($ap['category_name']) . '">';
                                $current_cat = $ap['category_name'];
                            }
                            $added = in_array($ap['place_id'], $plan_ids);
                        ?>
                        <option value="<?= $ap['place_id'] ?>" <?= $added ? 'disabled' : '' ?>>
                            <?= $ap['icon'] ?> <?= htmlspecialchars($ap['name']) ?>
                            <?= $added ? ' ✓' : '' ?>
                        </option>
                        <?php endwhile;
                        if ($current_cat !== '') echo '</optgroup>'; ?>
                    </select>
                    <button type="submit" name="add_place" class="btn btn-orange btn-full">
                        ➕ Add to Plan
                    </button>
                </form>
                <p style="text-align:center; font-size:0.78rem; color:var(--muted); margin-top:10px;">
                    Or <a href="places.php">browse all places</a> and add from there
                </p>
            </div>
        </div>

    </div>

</div>


<div class="footer">
    <p>© 2026 <strong>ExploreSphere</strong> · Athiya MIF · E2320129 · ITE2953</p>
</div>


</body>
</html>
<?php $conn->close(); ?>
