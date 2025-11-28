<?php
$page_title = "Správa uživatelů";
require 'db_connect.php';
require "header.php";
require_admin();

// Počet záznamů na stránku
$limit = 10;

// Aktuální stránka
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// Výpočet offsetu
$offset = ($page - 1) * $limit;

// ---------------------------------------------------------------
//  VYHLEDÁVÁNÍ PODLE EMAILU
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
// DOTAZ PRO VÝPIS UŽIVATELŮ (s vyhledáváním i stránkováním)
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

// typy parametrů
$paramTypes .= "ii";
$params[] = $limit;
$params[] = $offset;

// předat parametry bind_param() dynamicky
$stmt->bind_param($paramTypes, ...$params);

$stmt->execute();
$result = $stmt->get_result();

// ---------------------------------------------------------------
// CELKOVÝ POČET UŽIVATELŮ (kvůli stránkování)
// ---------------------------------------------------------------
if (!empty($search)) {
    // hledáme podle emailu
    $countStmt = $db_connection->prepare("
        SELECT COUNT(*) AS total FROM users WHERE email LIKE ?
    ");
    $countStmt->bind_param("s", $params[0]);
} else {
    // celkový počet bez hledání
    $countStmt = $db_connection->prepare("SELECT COUNT(*) AS total FROM users");
}

$countStmt->execute();
$total_users = $countStmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_users / $limit);
?>

<div class="container mt-5">

    <h2 class="text-center mb-4">Správa uživatelů</h2>

    <!--  Vyhledávání -->
    <form method="GET" class="mb-4 text-center">
        <div class="input-group" style="max-width: 400px; margin: 0 auto;">
            <input type="text" name="search" class="form-control" placeholder="Hledat podle e-mailu"
                value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-primary">Hledat</button>
        </div>
    </form>

    <div class="text-center mb-4">
        <a href="add_user.php" class="btn btn-success">
            Přidat nového uživatele
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Jméno</th>
                    <th>Příjmení</th>
                    <th>E-mail</th>
                    <th>Role</th>
                    <th>Akce</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']); ?></td>
                        <td><?= htmlspecialchars($user['jmeno']); ?></td>
                        <td><?= htmlspecialchars($user['prijmeni']); ?></td>
                        <td><?= htmlspecialchars($user['email']); ?></td>
                        <td><?= htmlspecialchars($user['role']); ?></td>
                        <td>
                            <a href="edit_user.php?id=<?= urlencode($user['id']); ?>" class="btn btn-sm btn-warning">
                                Upravit
                            </a>
                            <a href="delete_user.php?id=<?= urlencode($user['id']); ?>" class="btn btn-sm btn-danger"
                                onclick="return confirm('Opravdu chcete smazat tohoto uživatele?');">
                                Smazat
                            </a>
                            <a href="reset_password_admin.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-secondary"
                                onclick="return confirm('Opravdu chcete resetovat heslo tohoto uživatele?');">
                                Reset hesla
                            </a>
                            <a href="toggle_active.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-info">
                                <?= $user['active'] ? 'Deaktivovat' : 'Aktivovat' ?>
                            </a>

                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Stránkování -->
    <nav>
        <ul class="pagination justify-content-center">

            <!-- Předchozí stránka -->
            <li class="page-item <?= $page == 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">Předchozí</a>
            </li>

            <!-- Čísla stránek -->
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>

            <!-- Další stránka -->
            <li class="page-item <?= $page == $total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">Další</a>
            </li>

        </ul>
    </nav>

</div>

<?php
require "footer.php";
require "db_close.php";
?>