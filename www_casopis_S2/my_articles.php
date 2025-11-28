<?php
$page_title = "Moje články";
require 'db_connect.php';
require 'header.php';

// Ochrana: Přístup jen pro přihlášené s rolí 'autor'
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'autor') {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Načtení článků přihlášeného uživatele
$stmt = $db_connection->prepare("
    SELECT id, title, filename, status, created_at 
    FROM articles 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Pomocná funkce pro barvičky stavů (Bootstrap badges)
function getStatusBadge($status) {
    switch ($status) {
        case 'podano': return '<span class="badge bg-primary">Podáno</span>';
        case 'recenze': return '<span class="badge bg-warning text-dark">V recenzi</span>';
        case 'prijato': return '<span class="badge bg-success">Přijato</span>';
        case 'zamitnuto': return '<span class="badge bg-danger">Zamítnuto</span>';
        default: return '<span class="badge bg-secondary">' . htmlspecialchars($status) . '</span>';
    }
}
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Přehled mých článků</h2>
        <a href="new_article.php" class="btn btn-success">
            + Nahrát nový článek
        </a>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Datum</th>
                        <th>Název článku</th>
                        <th>Soubor</th>
                        <th>Stav</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= date("d.m.Y H:i", strtotime($row['created_at'])) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($row['title']) ?></strong>
                            </td>
                            <td>
                                <a href="uploads/<?= htmlspecialchars($row['filename']) ?>" 
                                   class="btn btn-sm btn-outline-secondary" 
                                   target="_blank">
                                   Stáhnout
                                </a>
                            </td>
                            <td>
                                <?= getStatusBadge($row['status']) ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">
            Zatím jste nenahrál(a) žádné články. 
            <a href="new_article.php" class="alert-link">Nahrát první článek</a>.
        </div>
    <?php endif; ?>

</div>

<?php
require 'footer.php';
require 'db_close.php';
?>