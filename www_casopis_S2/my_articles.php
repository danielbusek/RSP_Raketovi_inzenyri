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

// --- 1. ZÍSKÁNÍ PARAMETRŮ Z URL (FILTRY) ---
$search = $_GET['search'] ?? '';
$filter_status = $_GET['status'] ?? '';
$sort = $_GET['sort'] ?? 'date_desc';

// --- 2. SESTAVENÍ SQL DOTAZU ---
$sql = "
    SELECT 
        a.id, 
        a.title, 
        a.filename, 
        a.status, 
        a.created_at,
        i.nazev AS issue_name
    FROM articles a
    LEFT JOIN issues i ON a.issue_id = i.id
    WHERE a.user_id = ?
";

// Pole pro parametry do bind_param
$params = [$user_id];
$types = "i"; // První parametr je integer (user_id)

// A) Přidání vyhledávání (podle názvu)
if (!empty($search)) {
    $sql .= " AND a.title LIKE ?";
    $params[] = "%" . $search . "%";
    $types .= "s";
}

// B) Přidání filtru stavu
if (!empty($filter_status)) {
    $sql .= " AND a.status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

// C) Přidání řazení (Whitelist proti SQL injection)
switch ($sort) {
    case 'date_asc':
        $sql .= " ORDER BY a.created_at ASC";
        break;
    case 'title_asc':
        $sql .= " ORDER BY a.title ASC";
        break;
    case 'title_desc':
        $sql .= " ORDER BY a.title DESC";
        break;
    default: // date_desc
        $sql .= " ORDER BY a.created_at DESC";
        break;
}

// --- 3. PROVEDENÍ DOTAZU ---
$stmt = $db_connection->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Funkce pro hezké zobrazení stavů
function getStatusBadge($status)
{
    switch ($status) {
        case 'podano':
            return '<span class="badge bg-primary bg-opacity-75 border border-primary">Podáno</span>';
        case 'recenze':
            return '<span class="badge bg-warning text-dark bg-opacity-75 border border-warning">V recenzním řízení</span>';
        case 'oprava':
            return '<span class="badge bg-info text-dark bg-opacity-75 border border-info">Čeká na opravu</span>';
        case 'prijato':
            return '<span class="badge bg-success bg-opacity-75 border border-success">Přijato</span>';
        case 'zamitnuto':
            return '<span class="badge bg-danger bg-opacity-75 border border-danger">Zamítnuto</span>';
        default:
            return '<span class="badge bg-secondary">' . htmlspecialchars($status) . '</span>';
    }
}
?>

<div class="container mt-5 mb-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white mb-0">Přehled mých článků</h2>
        <a href="new_article.php" class="btn btn-primary">
            <i class="fa-solid fa-plus me-2"></i> Nahrát nový
        </a>
    </div>

    <div class="article-card p-4 mb-4">
        <form method="GET" class="row g-3 align-items-end">

            <div class="col-md-4">
                <label class="form-label text-muted small text-uppercase fw-bold">Hledat článek</label>
                <div class="input-group">
                    <button class="btn btn-dark border-secondary text-secondary" type="submit" title="Hledat">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>

                    <input type="text" name="search" class="form-control border-secondary border-start-0"
                        placeholder="Název článku..."
                        value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label text-muted small text-uppercase fw-bold">Stav</label>
                <select name="status" class="form-select">
                    <option value="">Všechny stavy</option>
                    <option value="podano" <?= $filter_status == 'podano' ? 'selected' : '' ?>>Podáno</option>
                    <option value="recenze" <?= $filter_status == 'recenze' ? 'selected' : '' ?>>V recenzi</option>
                    <option value="oprava" <?= $filter_status == 'oprava' ? 'selected' : '' ?>>K opravě</option>
                    <option value="prijato" <?= $filter_status == 'prijato' ? 'selected' : '' ?>>Přijato</option>
                    <option value="zamitnuto" <?= $filter_status == 'zamitnuto' ? 'selected' : '' ?>>Zamítnuto</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label text-muted small text-uppercase fw-bold">Řazení</label>
                <select name="sort" class="form-select">
                    <option value="date_desc" <?= $sort == 'date_desc' ? 'selected' : '' ?>>Nejnovější nejdříve</option>
                    <option value="date_asc" <?= $sort == 'date_asc' ? 'selected' : '' ?>>Nejstarší nejdříve</option>
                    <option value="title_asc" <?= $sort == 'title_asc' ? 'selected' : '' ?>>Podle abecedy (A-Z)</option>
                    <option value="title_desc" <?= $sort == 'title_desc' ? 'selected' : '' ?>>Podle abecedy (Z-A)</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary w-100">
                    Filtrovat
                </button>
                <?php if ($search || $filter_status || $sort !== 'date_desc'): ?>
                    <a href="my_articles.php" class="btn btn-outline-secondary" title="Zrušit filtry">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="article-card p-0" style="width: 100%; min-width: auto;">
        <?php if ($result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table mb-0 align-middle table-hover">
                    <thead>
                        <tr>
                            <th class="ps-4 py-3">Datum</th>
                            <th class="py-3">Název článku</th>
                            <th class="py-3">Číslo časopisu</th>
                            <th class="py-3">Stav</th>
                            <th class="pe-4 py-3 text-end">Soubor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4 text-muted small">
                                    <?= date("d.m.Y", strtotime($row['created_at'])) ?><br>
                                    <?= date("H:i", strtotime($row['created_at'])) ?>
                                </td>

                                <td class="fw-bold text-white">
                                    <?= htmlspecialchars($row['title']) ?>
                                </td>

                                <td class="text-info small">
                                    <?= htmlspecialchars($row['issue_name'] ?? 'Neurčeno') ?>
                                </td>

                                <td>
                                    <?= getStatusBadge($row['status']) ?>
                                </td>

                                <td class="pe-4 text-end">
                                    <a href="uploads/<?= htmlspecialchars($row['filename']) ?>"
                                        class="btn btn-sm btn-dark border border-secondary text-light"
                                        target="_blank">
                                        <i class="fa-solid fa-download me-1"></i> PDF
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-5 text-center text-muted">
                <i class="fa-solid fa-magnifying-glass fa-3x mb-3 opacity-50"></i>
                <p class="mb-3 lead">Nebyly nalezeny žádné články.</p>
                <?php if ($search || $filter_status): ?>
                    <p class="small">Zkuste upravit filtry nebo <a href="my_articles.php">zobrazit vše</a>.</p>
                <?php else: ?>
                    <a href="new_article.php" class="btn btn-sm btn-outline-primary">Nahrát první článek</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require 'footer.php';
require 'db_close.php';
?>