<?php
$page_title = "Moje články";
require 'db_connect.php';
require 'header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'autor') {
    header("Location: index.php"); exit;
}

$user_id = $_SESSION['user_id'];
$search = $_GET['search'] ?? '';
$filter_status = $_GET['status'] ?? '';

// SQL Dotaz
$sql = "SELECT a.*, i.nazev as issue_nazev, i.rocnik, i.cislo 
        FROM articles a
        JOIN issues i ON a.issue_id = i.id
        WHERE a.user_id = ?";
$params = [$user_id];
$types = "i";

if (!empty($search)) {
    $sql .= " AND a.title LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}
if (!empty($filter_status)) {
    $sql .= " AND a.status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

$sql .= " ORDER BY a.created_at DESC";
$stmt = $db_connection->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Funkce pro badge
function getStatusBadge($status) {
    switch($status) {
        case 'podano': return '<span class="badge bg-warning text-dark">Podáno</span>';
        case 'ceka_na_recenzenty': return '<span class="badge bg-info text-dark">Čeká na recenzenty</span>';
        case 'v_recenzi': return '<span class="badge bg-primary">V recenzi</span>';
        case 'prijato': return '<span class="badge bg-success">Přijato</span>';
        case 'zamitnuto': return '<span class="badge bg-danger">Zamítnuto</span>';
        case 'vraceno_k_oprave': return '<span class="badge bg-warning text-dark border border-warning" style="box-shadow: 0 0 10px #facc15;">⚠ Vráceno k opravě</span>';
        default: return $status;
    }
}
?>

<div class="container mt-5 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white mb-0">Moje články</h2>
        <a href="new_article.php" class="btn btn-primary"><i class="fa-solid fa-plus me-2"></i>Nahrát článek</a>
    </div>

    <div class="article-card p-4 mb-4">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label text-muted small fw-bold">Hledat článek</label>
                <div class="input-group">
                    <button class="btn btn-dark border-secondary text-secondary" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                    <input type="text" name="search" class="form-control border-secondary border-start-0" placeholder="Název článku..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small fw-bold">Stav</label>
                <select name="status" class="form-control" onchange="this.form.submit()">
                    <option value="">Všechny stavy</option>
                    <option value="podano" <?= $filter_status=='podano'?'selected':'' ?>>Podáno</option>
                    <option value="vraceno_k_oprave" <?= $filter_status=='vraceno_k_oprave'?'selected':'' ?>>Vráceno k opravě</option>
                    <option value="prijato" <?= $filter_status=='prijato'?'selected':'' ?>>Přijato</option>
                </select>
            </div>
        </form>
    </div>

    <?php while($row = $result->fetch_assoc()): ?>
        <div class="article-card mb-4 p-0 overflow-hidden">
            <div class="d-flex flex-wrap">
                <div class="p-4 flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-dark border border-secondary"><?= htmlspecialchars($row['issue_nazev']) ?> (<?= $row['rocnik'] ?>/<?= $row['cislo'] ?>)</span>
                        <?= getStatusBadge($row['status']) ?>
                    </div>
                    <h4 class="text-white mb-2"><?= htmlspecialchars($row['title']) ?></h4>
                    <div class="text-muted small mb-3">
                        <i class="fa-solid fa-calendar me-2"></i><?= date("d.m.Y", strtotime($row['created_at'])) ?>
                        <span class="mx-2">|</span>
                        <i class="fa-solid fa-file me-2"></i><?= htmlspecialchars($row['filename']) ?>
                    </div>

                    <?php if($row['status'] === 'vraceno_k_oprave'): 
                        // Získat poslední zprávu
                        $msg_sql = "SELECT message, created_at FROM article_messages WHERE article_id = {$row['id']} ORDER BY created_at DESC LIMIT 1";
                        $last_msg = $db_connection->query($msg_sql)->fetch_assoc();
                    ?>
                        <div class="alert alert-warning border-warning bg-opacity-10 mt-3 mb-0">
                            <h6 class="alert-heading text-warning"><i class="fa-solid fa-comment-dots me-2"></i>Vzkaz od redakce:</h6>
                            
                            <?php if($last_msg): ?>
                                <p class="mb-2 text-white small"><?= nl2br(htmlspecialchars($last_msg['message'])) ?></p>
                                <small class="text-muted d-block mb-3">Přijato: <?= date("d.m.Y H:i", strtotime($last_msg['created_at'])) ?></small>
                            <?php else: ?>
                                <p class="mb-3 text-muted small fst-italic">Bez textového komentáře.</p>
                            <?php endif; ?>

                            <a href="author_response.php?id=<?= $row['id'] ?>" class="btn btn-warning text-dark fw-bold w-100">
                                <i class="fa-solid fa-wrench me-2"></i>Vyřešit připomínky a nahrát opravu
                            </a>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    <?php endwhile; ?>
    
    <?php if($result->num_rows == 0): ?>
        <div class="text-center text-muted py-5">Nemáte žádné články.</div>
    <?php endif; ?>
</div>

<?php require 'footer.php'; require 'db_close.php'; ?>