<?php
/**
 * LEVEL 1: BLIND SQL INJECTION NIGHTMARE
 * Difficulty: HARD
 *
 * WAF blocks: UNION, OR 1=1, single quotes, comments
 * Required technique: Time-based blind SQLi with WAF bypass
 *
 * Bypass methods:
 * - Use /*!50000UNION*/ (MySQL version comments)
 * - Use LIKE instead of =
 * - Hex encode strings: 0x61646d696e = 'admin'
 * - Use BENCHMARK() or SLEEP() with obfuscation
 * - Case variation with inline comments: Un/**/Ion
 *
 * Hidden vulnerability: The 'order' parameter is injectable
 * but only accepts specific column names... or does it?
 *
 * Solution hint: id`;(SELECT(SLEEP(5)))-- -
 */
require_once '../config.php';
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }

$conn = getDbConnection();
$error = '';
$results = [];

// Extended WAF for this level
$level1_waf = [
    '/union/i', '/select.*from/i', '/or\s+\d+\s*=\s*\d+/i',
    '/and\s+\d+\s*=\s*\d+/i', '/sleep\s*\(\s*\d+\s*\)/i',
    '/benchmark\s*\(/i', '/--\s*$/i', '/#\s*$/',
    '/\/\*.*\*\//s', '/\bor\b/i', '/\band\b/i'
];

function level1Waf($input) {
    global $level1_waf;
    $decoded = urldecode(urldecode(strtolower($input)));

    foreach ($level1_waf as $pattern) {
        if (preg_match($pattern, $decoded)) {
            return false;
        }
    }
    return true;
}

$search = $_GET['q'] ?? '';
$order = $_GET['order'] ?? 'id';
$dir = $_GET['dir'] ?? 'ASC';

// Red herring: search parameter looks injectable but is properly escaped
if ($search && level1Waf($search)) {
    $search = $conn->real_escape_string($search);
    $searchQuery = " WHERE name LIKE '%$search%'";
} else if ($search) {
    $error = "Blocked by WAF";
    $searchQuery = "";
} else {
    $searchQuery = "";
}

// THE REAL VULNERABILITY: order parameter
// WAF checks it, but there's a bypass using backticks and subqueries
$allowedOrders = ['id', 'name', 'price', 'stock'];
$orderSafe = in_array(strtolower($order), $allowedOrders);

// BUT WAIT - there's a hidden debug mode that disables the check
$debug = isset($_GET['_debug']) && $_GET['_debug'] === md5('vulnforge_debug_2024');

if ($orderSafe || $debug) {
    // VULNERABLE when debug mode is active
    // Payload: ?order=id`,(SELECT(SLEEP(5)))&_debug=<md5hash>
    // The backtick closes the column name, allowing injection
    $query = "SELECT id, name, price, stock FROM products $searchQuery ORDER BY `$order` $dir";
} else {
    $order = 'id';
    $query = "SELECT id, name, price, stock FROM products $searchQuery ORDER BY id $dir";
}

// Another hidden vuln: The error message leaks query structure
try {
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
    }
} catch (Exception $e) {
    if ($debug) {
        $error = "Query error: " . $conn->error; // Info disclosure in debug mode
    }
}

// Flag is hidden in a product only accessible via SQLi
// Product ID 999 contains the flag but isn't shown in normal queries
$flag_check = $_POST['flag'] ?? '';
if ($flag_check === $GLOBALS['FLAGS'][1]) {
    $conn->query("INSERT INTO user_progress (user_id, level_id, flag) VALUES ({$_SESSION['user_id']}, 1, '$flag_check')");
    header('Location: ../dashboard.php?success=1');
    exit;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 1: Blind SQLi Nightmare</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="../index.php">VulnForge Academy</a></div>
        <div class="nav-links">
            <a href="../dashboard.php">Dashboard</a>
            <a href="../logout.php">Logout</a>
        </div>
    </nav>

    <main class="container">
        <div class="level-header">
            <h1>Level 1: Blind SQL Injection Nightmare</h1>
            <span class="difficulty hard">HARD</span>
        </div>

        <div class="scenario-box">
            <h3>Scenario: Product Database</h3>
            <p>You've found a product search page. The developers think they've blocked all SQL injection attempts with their WAF. They're wrong.</p>
            <p><strong>Objective:</strong> Extract the flag from the hidden product record.</p>
        </div>

        <div class="hint-box collapsed" id="hints">
            <button onclick="toggleHints()">Show Hints (Costs Points)</button>
            <div class="hints-content" style="display:none;">
                <p>Hint 1: The search parameter is a red herring - it's properly escaped.</p>
                <p>Hint 2: Look at ALL parameters in the URL...</p>
                <p>Hint 3: There's a debug mode. How would developers access it?</p>
                <p>Hint 4: The debug parameter needs a specific MD5 hash...</p>
                <p>Hint 5: Time-based blind injection. Think backticks.</p>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="GET" class="search-form">
            <input type="text" name="q" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="order">
                <option value="id" <?php echo $order === 'id' ? 'selected' : ''; ?>>Sort by ID</option>
                <option value="name" <?php echo $order === 'name' ? 'selected' : ''; ?>>Sort by Name</option>
                <option value="price" <?php echo $order === 'price' ? 'selected' : ''; ?>>Sort by Price</option>
            </select>
            <button type="submit" class="btn">Search</button>
        </form>

        <table class="data-table">
            <thead>
                <tr><th>ID</th><th>Name</th><th>Price</th><th>Stock</th></tr>
            </thead>
            <tbody>
                <?php foreach ($results as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td>$<?php echo htmlspecialchars($row['price']); ?></td>
                    <td><?php echo htmlspecialchars($row['stock']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <form method="POST" class="flag-form">
            <input type="text" name="flag" placeholder="Enter flag..." required>
            <button type="submit" class="btn btn-primary">Submit Flag</button>
        </form>
    </main>

    <script>
    function toggleHints() {
        var content = document.querySelector('.hints-content');
        content.style.display = content.style.display === 'none' ? 'block' : 'none';
    }
    </script>
</body>
</html>
