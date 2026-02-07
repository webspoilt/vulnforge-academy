<?php
/**
 * LEVEL 1: BASIC SQL INJECTION
 * Difficulty: EASY
 *
 * The simplest SQL injection - no filtering, clear feedback
 * Perfect for absolute beginners
 *
 * Solution: ' OR '1'='1' --
 * Or search for: ' UNION SELECT 1,2,admin_notes,4,5 FROM products--
 */
require_once '../../config.php';

$results = [];
$search = $_GET['search'] ?? '';
$error = '';
$success = '';

$conn = getDbConnection();

if ($search) {
    // VULNERABLE: Direct string concatenation - no escaping!
    $query = "SELECT id, name, description, price, category FROM products WHERE name LIKE '%$search%' OR category LIKE '%$search%'";

    // Show the query to help beginners understand
    $showQuery = true;

    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
    } else {
        $error = "SQL Error: " . $conn->error; // Helpful error for learning
    }
}

// Flag submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['flag'])) {
    if (checkFlag(1, $_POST['flag'])) {
        if (isset($_SESSION['user_id'])) {
            saveProgress($_SESSION['user_id'], 1, $_POST['flag']);
        }
        $success = "Correct! Level 1 completed!";
    } else {
        $error = "Incorrect flag. Keep trying!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 1: Basic SQL Injection - VulnForge</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="../../index.php">VulnForge Academy</a></div>
        <div class="nav-links">
            <a href="../../dashboard.php">Dashboard</a>
        </div>
    </nav>

    <main class="container">
        <div class="level-header">
            <h1>Level 1: Basic SQL Injection</h1>
            <span class="difficulty easy">EASY - 10 Points</span>
        </div>

        <div class="scenario-box">
            <h3>Scenario: Product Search</h3>
            <p>A simple product search that's vulnerable to SQL injection.</p>
            <p><strong>Objective:</strong> Use SQL injection to find the hidden flag in the admin_notes column.</p>

            <div class="hints-box">
                <h4>Hints for Beginners:</h4>
                <ul>
                    <li>Try adding a single quote (') to the search to see what happens</li>
                    <li>SQL injection lets you modify the database query</li>
                    <li>UNION SELECT can combine results from different queries</li>
                    <li>The products table has columns: id, name, description, price, category, admin_notes</li>
                </ul>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="GET" class="search-form">
            <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <?php if ($search && isset($showQuery)): ?>
        <div class="query-display" style="background: #1a1a25; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <strong>Query executed:</strong>
            <code style="display: block; margin-top: 10px; word-break: break-all;"><?php echo htmlspecialchars($query); ?></code>
        </div>
        <?php endif; ?>

        <?php if (!empty($results)): ?>
        <table class="data-table">
            <thead>
                <tr><th>ID</th><th>Name</th><th>Description</th><th>Price</th><th>Category</th></tr>
            </thead>
            <tbody>
                <?php foreach ($results as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['name'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['description'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['price'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['category'] ?? ''); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php elseif ($search): ?>
            <p>No products found.</p>
        <?php endif; ?>

        <div class="solution-hint" style="margin-top: 30px; padding: 20px; background: rgba(0,255,102,0.1); border: 1px solid #00ff66; border-radius: 5px;">
            <h4>Stuck? Try these payloads:</h4>
            <code>' OR '1'='1</code> - Returns all products<br>
            <code>' UNION SELECT 1,2,3,4,admin_notes FROM products--</code> - Shows admin notes
        </div>

        <form method="POST" class="flag-form">
            <input type="text" name="flag" placeholder="Enter flag (FLAG{...})" required>
            <button type="submit" class="btn btn-primary">Submit Flag</button>
        </form>
    </main>
</body>
</html>
