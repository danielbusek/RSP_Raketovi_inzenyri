<?php
$page_title = "Redakční Dashboard";
require 'db_connect.php';
require 'header.php';

// 1. OCHRANA PŘÍSTUPU (Redaktor, Šéfredaktor, Admin)
$role = mb_strtolower($_SESSION['user_role'] ?? '', 'UTF-8');
$allowed_roles = ['admin', 'šéfredaktor', 'redaktor'];

if (!in_array($role, $allowed_roles)) {
    echo '<div class="container mt-5"><div class="alert alert-danger">Nemáte oprávnění.</div></div>';
    require 'footer.php';
    exit;
}

// 2. ZPRACOVÁNÍ FILTRŮ
$filter_issue = isset($_GET['issue_id']) ? intval($_GET['issue_id']) : 0;
$filter_author = isset($_GET['author']) ? trim($_GET['author']) : '';

// 3. SQL DOTAZ (Složitější JOIN pro získání recenzentů)
$sql = "
    SELECT 
        a.id, a.title, a.status, a.created_at,
        u.jmeno as autor_jmeno, u.prijmeni as autor_prijmeni,
        i.nazev as issue_nazev, i.rocnik, i.cislo,
        -- Slepí jména recenzentů do jednoho řetězce (např. 'Novák, Svoboda')
        GROUP_CONCAT(CONCAT(r_user.jmeno, ' ', r_user.prijmeni) SEPARATOR ', ') as recenzenti
    FROM articles a
    JOIN users u ON a.user_id = u.id
    JOIN issues i ON a.issue_id = i.id
    LEFT JOIN reviews r ON a.id = r.article_id
    LEFT JOIN users r_user ON r.reviewer_id = r_user.id
    WHERE 1=1
";

// Aplikace filtrů
$params = [];
$types = "";

if ($filter_issue > 0) {
    $sql .= " AND a.issue_id = ?";
    $params[] = $filter_issue;
    $types .= "i";
}

if (!empty($filter_author)) {
    $sql .= " AND (u.prijmeni LIKE ? OR u.jmeno LIKE ?)";
    $params[] = "%$filter_author%";
    $params[] = "%$filter_author%";
    $types .= "ss";
}

$sql .= " GROUP BY a.id ORDER BY a.created_at DESC";

$stmt = $db_connection->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// 4. STATISTIKY PRO KARTY (Rychlé počty)
$stats = $db_connection->query("
    SELECT 
        COUNT(CASE WHEN status='podano' THEN 1 END) as nove,
        COUNT(CASE WHEN status='v_recenzi' THEN 1 END) as v_recenzi,
        COUNT(CASE WHEN status='prijato' THEN 1 END) as prijato
    FROM articles
")->fetch_assoc();

// Pomocná funkce na barvy stavů
function getStatusBadge($status) {
    switch($status) {
        case 'podano': return '<span class="badge bg-warning text-dark">Nový příspěvek</span>';
        case 'ceka_na_recenzenty': return '<span class="badge bg-info text-dark">Čeká na recenzenty</span>';
        case 'v_recenzi': return '<span class="badge bg-primary">V recenzi</span>';
        case 'prijato': return '<span class="badge bg-success">Přijato</span>';
        case 'zamitnuto': return '<span class="badge bg-danger">Zamítnuto</span>';
        default: return '<span class="badge bg-secondary">'.$status.'</span>';
    }
}
?>

<div class="container mt-5 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white mb-0">Redakční přehled</h2>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="article-card p-3 d-flex align-items-center justify-content-between" style="border-left: 4px solid #facc15;">
                <div>
                    <div class="text-muted small text-uppercase fw-bold">Nové k posouzení</div>
                    <div class="fs-3 fw-bold text-white"><?= $stats['nove'] ?></div>
                </div>
                <i class="fa-solid fa-bell fa-2x text-warning opacity-50"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="article-card p-3 d-flex align-items-center justify-content-between" style="border-left: 4px solid #00d2ff;">
                <div>
                    <div class="text-muted small text-uppercase fw-bold">Právě v recenzi</div>
                    <div class="fs-3 fw-bold text-white"><?= $stats['v_recenzi'] ?></div>
                </div>
                <i class="fa-solid fa-glasses fa-2x text-info opacity-50"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="article-card p-3 d-flex align-items-center justify-content-between" style="border-left: 4px solid #22c55e;">
                <div>
                    <div class="text-muted small text-uppercase fw-bold">Schváleno</div>
                    <div class="fs-3 fw-bold text-white"><?= $stats['prijato'] ?></div>
                </div>
                <i class="fa-solid fa-check-circle fa-2x text-success opacity-50"></i>
            </div>
        </div>
    </div>

    <div class="article-card p-4 mb-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label text-muted small fw-bold">Číslo časopisu</label>
                <select name="issue_id" class="form-control">
                    <option value="">-- Všechna čísla --</option>
                    <?php 
                    $issues = $db_connection->query("SELECT * FROM issues ORDER BY rocnik DESC, cislo DESC");
                    while($iss = $issues->fetch_assoc()):
                        $selected = ($filter_issue == $iss['id']) ? 'selected' : '';
                        echo "<option value='{$iss['id']}' $selected>{$iss['nazev']} ({$iss['rocnik']}/{$iss['cislo']})</option>";
                    endwhile; 
                    ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small fw-bold">Autor</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fa-solid fa-user"></i></span>
                    <input type="text" name="author" class="form-control" placeholder="Příjmení autora..." value="<?= htmlspecialchars($filter_author) ?>">
                </div>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="fa-solid fa-filter me-2"></i> Filtrovat
                </button>
                <?php if($filter_issue || $filter_author): ?>
                    <a href="editor_dashboard.php" class="btn btn-dark border-secondary">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="article-card p-0">
        <div class="table-responsive">
            <table class="table mb-0 align-middle table-hover">
                <thead>
                    <tr>
                        <th class="ps-4 py-3">Název článku</th>
                        <th class="py-3">Autor</th>
                        <th class="py-3">Číslo</th>
                        <th class="py-3">Stav</th>
                        <th class="py-3">Recenzenti</th>
                        <th class="pe-4 py-3 text-end">Akce</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-white">
                                    <div class="d-flex flex-column">
                                        <span><?= htmlspecialchars($row['title']) ?></span>
                                        <small class="text-muted" style="font-size: 0.75rem;">
                                            Podáno: <?= date("d.m.Y", strtotime($row['created_at'])) ?>
                                        </small>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($row['autor_jmeno'] . ' ' . $row['autor_prijmeni']) ?></td>
                                <td class="text-muted small">
                                    <?= htmlspecialchars($row['issue_nazev']) ?>
                                    <br>(<?= $row['rocnik'] ?>/<?= $row['cislo'] ?>)
                                </td>
                                <td><?= getStatusBadge($row['status']) ?></td>
                                <td>
                                    <?php if($row['recenzenti']): ?>
                                        <div class="d-flex align-items-center text-info small">
                                            <i class="fa-solid fa-user-check me-2"></i>
                                            <?= htmlspecialchars($row['recenzenti']) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small fs-italic">Nepřiřazeno</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="article_detail.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-light border-secondary">
                                        Detail <i class="fa-solid fa-arrow-right ms-1"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-folder-open fa-2x mb-3 d-block"></i>
                                Žádné články neodpovídají filtrům.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php
require 'footer.php';
require 'db_close.php';
?>