<?php
$page_title = "Správa uživatelů";
require 'db_connect.php';
require "header.php";
require_admin();

// Počet záznamů na stránku
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// ---------------------------------------------------------------
//  VYHLEDÁVÁNÍ
// ---------------------------------------------------------------
$search = $_GET['search'] ?? '';
$searchQuery = '';
$params = [];
$paramTypes = '';

if (!empty($search)) {
    $searchQuery = "WHERE users.email LIKE ? ";
    $params[] = "%" . $search . "%";
    $paramTypes .= "s";
}

// ---------------------------------------------------------------
// DOTAZ PRO VÝPIS
// ---------------------------------------------------------------
$sql = "
    SELECT users.id, users.jmeno, users.prijmeni, users.email, role.role, users.active
    FROM users
    JOIN role ON users.id_role = role.id_role
    $searchQuery
    ORDER BY users.id ASC
    LIMIT ? OFFSET ?
";

$stmt = $db_connection->prepare($sql);
$paramTypes .= "ii";
$params[] = $limit;
$params[] = $offset;
$stmt->bind_param($paramTypes, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// ---------------------------------------------------------------
// POČTY PRO STRÁNKOVÁNÍ
// ---------------------------------------------------------------
if (!empty($search)) {
    $countStmt = $db_connection->prepare("SELECT COUNT(*) AS total FROM users WHERE email LIKE ?");
    $countStmt->bind_param("s", $params[0]);
} else {
    $countStmt = $db_connection->prepare("SELECT COUNT(*) AS total FROM users");
}
$countStmt->execute();
$total_users = $countStmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_users / $limit);
?>

<div class="container mt-5 mb-5">

    <h2 class="text-center mb-4 text-white">Správa uživatelů</h2>

    <div class="d-flex flex-column align-items-center mb-5 gap-3">
        
        <form method="GET" style="width: 100%; max-width: 500px;">
            <div class="input-group">
                <button class="btn btn-dark border-secondary text-secondary" type="submit">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
                <input type="text" name="search" class="form-control border-secondary border-start-0" 
                       placeholder="Hledat podle e-mailu..." 
                       value="<?= htmlspecialchars($search) ?>">
            </div>
        </form>

        <a href="add_user.php" class="btn btn-primary px-5 py-2">
            <i class="fa-solid fa-user-plus me-2"></i> Přidat nového uživatele
        </a>
    </div>

    <div class="article-card p-0" style="width: 100%; min-width: auto;">
        <div class="table-responsive">
            <table class="table mb-0 align-middle table-hover">
                <thead>
                    <tr>
                        <th class="ps-4 py-3 text-center">ID</th>
                        <th class="py-3">Jméno a Příjmení</th>
                        <th class="py-3">E-mail</th>
                        <th class="py-3 text-center">Role</th>
                        <th class="pe-4 py-3 text-end">Akce</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-4 text-center text-muted fw-bold">#<?= htmlspecialchars($user['id']); ?></td>
                            
                            <td class="fw-bold text-white">
                                <?= htmlspecialchars($user['jmeno'] . ' ' . $user['prijmeni']); ?>
                            </td>
                            
                            <td class="text-info small">
                                <?= htmlspecialchars($user['email']); ?>
                            </td>
                            
                            <td class="text-center">
                                <span class="badge bg-dark border border-secondary text-light">
                                    <?= htmlspecialchars($user['role']); ?>
                                </span>
                            </td>
                            
                            <td class="pe-4 text-end">
                                <div class="btn-group" role="group">
                                    <a href="edit_user.php?id=<?= $user['id']; ?>" class="btn btn-sm btn-warning" title="Upravit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <a href="reset_password_admin.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-secondary" 
                                       title="Reset hesla" onclick="return confirm('Resetovat heslo?');">
                                        <i class="fa-solid fa-key"></i>
                                    </a>
                                    <a href="toggle_active.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-info" 
                                       title="<?= $user['active'] ? 'Deaktivovat' : 'Aktivovat' ?>">
                                        <i class="fa-solid <?= $user['active'] ? 'fa-toggle-on' : 'fa-toggle-off' ?>"></i>
                                    </a>
                                    <a href="delete_user.php?id=<?= $user['id']; ?>" class="btn btn-sm btn-danger" 
                                       title="Smazat" onclick="return confirm('Opravdu smazat?');">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($total_pages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            
            <li class="page-item <?= $page == 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>
            </li>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>

            <li class="page-item <?= $page == $total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            </li>
            
        </ul>
    </nav>
    <?php endif; ?>

</div>

<?php
require "footer.php";
require "db_close.php";
?>