<?php
$page_title = "Detail článku a řízení recenzí";
require 'db_connect.php';
require 'header.php';

// 1. Získání ID článku
$article_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($article_id === 0) {
    echo '<div class="container mt-5"><div class="alert alert-danger">Neplatné ID článku.</div></div>';
    require 'footer.php'; exit;
}

// 2. Kontrola práv
$user_id = $_SESSION['user_id'] ?? 0;
$user_role = mb_strtolower($_SESSION['user_role'] ?? '', 'UTF-8');
$is_editor = in_array($user_role, ['admin', 'šéfredaktor', 'redaktor']);
$is_chief = in_array($user_role, ['admin', 'šéfredaktor']); // Právo měnit redaktora

// 3. Načtení dat o článku
$stmt = $db_connection->prepare("
    SELECT a.*, u.jmeno, u.prijmeni, u.email, i.nazev as issue_nazev 
    FROM articles a
    JOIN users u ON a.user_id = u.id
    JOIN issues i ON a.issue_id = i.id
    WHERE a.id = ?
");
$stmt->bind_param("i", $article_id);
$stmt->execute();
$article = $stmt->get_result()->fetch_assoc();

if (!$article) {
    echo '<div class="container mt-5"><div class="alert alert-danger">Článek nenalezen.</div></div>';
    require 'footer.php'; exit;
}

if (!$is_editor && $article['user_id'] !== $user_id) {
    echo '<div class="container mt-5"><div class="alert alert-danger">Nemáte oprávnění.</div></div>';
    require 'footer.php'; exit;
}

$msg = '';

// ---------------------------------------------------------
// LOGIKA FORMULÁŘŮ
// ---------------------------------------------------------
if ($is_editor && $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // A) PŘIŘAZENÍ RECENZENTA
    if (isset($_POST['assign_reviewer'])) {
        $reviewer_id = intval($_POST['reviewer_id']);
        $deadline = $_POST['deadline'];
        
        $check = $db_connection->query("SELECT id FROM reviews WHERE article_id=$article_id AND reviewer_id=$reviewer_id");
        if ($check->num_rows == 0) {
            $stmt = $db_connection->prepare("INSERT INTO reviews (article_id, reviewer_id, deadline, status) VALUES (?, ?, ?, 'pending')");
            $stmt->bind_param("iis", $article_id, $reviewer_id, $deadline);
            if ($stmt->execute()) {
                if ($article['status'] === 'podano' || $article['status'] === 'vraceno_k_oprave') {
                    $db_connection->query("UPDATE articles SET status='ceka_na_recenzenty' WHERE id=$article_id");
                    $article['status'] = 'ceka_na_recenzenty';
                }
                $rev_email = $db_connection->query("SELECT email FROM users WHERE id=$reviewer_id")->fetch_assoc()['email'];
                $msg = '<div class="alert alert-success">Recenzent přiřazen. Email odeslán na: <strong>'.htmlspecialchars($rev_email).'</strong></div>';
            }
        } else {
            $msg = '<div class="alert alert-warning">Tento recenzent už je přiřazen.</div>';
        }
    }

    // B) ODEBRÁNÍ RECENZENTA
    if (isset($_POST['remove_reviewer'])) {
        $review_id = intval($_POST['review_id']);
        $db_connection->query("DELETE FROM reviews WHERE id=$review_id");
        $msg = '<div class="alert alert-info">Recenzent byl odebrán.</div>';
    }

    // C) VRÁCENÍ AUTOROVI K OPRAVĚ
    if (isset($_POST['return_to_author'])) {
        $message_text = trim($_POST['message']);
        if (!empty($message_text)) {
            // 1. Uložit zprávu
            $stmt = $db_connection->prepare("INSERT INTO article_messages (article_id, sender_id, message) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $article_id, $user_id, $message_text);
            $stmt->execute();

            // 2. Změnit stav článku
            $stmt = $db_connection->prepare("UPDATE articles SET status = 'vraceno_k_oprave' WHERE id = ?");
            $stmt->bind_param("i", $article_id);
            $stmt->execute();
            $article['status'] = 'vraceno_k_oprave'; 

            // 3. Simulace emailu
            $msg = '
            <div class="alert alert-warning border-warning shadow-lg">
                <h5 class="alert-heading"><i class="fa-solid fa-envelope me-2"></i>Odesláno autorovi</h5>
                <p class="mb-0">Článek byl vrácen k přepracování. Autor ('.htmlspecialchars($article['email']).') obdržel notifikaci.</p>
            </div>';
        }
    }

    // D) PŘIŘAZENÍ ODPOVĚDNÉHO REDAKTORA (Novinka pro Dashboard)
    if (isset($_POST['assign_editor']) && $is_chief) {
        $new_editor_id = intval($_POST['editor_id']);
        // Pokud je vybráno "Nepřiřazeno" (value 0), nastavíme NULL
        $sql_editor = ($new_editor_id > 0) ? "$new_editor_id" : "NULL";
        
        $db_connection->query("UPDATE articles SET editor_id = $sql_editor WHERE id = $article_id");
        $article['editor_id'] = $new_editor_id; // Aktualizace pro zobrazení
        $msg = '<div class="alert alert-success">Odpovědný redaktor byl aktualizován.</div>';
    }
}

// Načtení dat pro zobrazení
$reviews_result = $db_connection->query("SELECT r.*, u.jmeno, u.prijmeni FROM reviews r JOIN users u ON r.reviewer_id = u.id WHERE r.article_id = $article_id");
$all_reviewers = $db_connection->query("SELECT u.id, u.jmeno, u.prijmeni, u.email FROM users u JOIN role r ON u.id_role = r.id_role WHERE r.role LIKE '%recenzent%'");
$messages_result = $db_connection->query("SELECT m.*, u.jmeno, u.prijmeni FROM article_messages m JOIN users u ON m.sender_id = u.id WHERE m.article_id = $article_id ORDER BY m.created_at DESC");

// Pomocné funkce
function getReviewStatusBadge($status) {
    switch($status) {
        case 'pending': return '<span class="badge bg-warning text-dark">Čeká se</span>';
        case 'accepted': return '<span class="badge bg-info text-dark">Přijato</span>';
        case 'completed': return '<span class="badge bg-success">Odevzdáno</span>';
        case 'rejected': return '<span class="badge bg-danger">Odmítnuto</span>';
        default: return $status;
    }
}
?>

<div class="container mt-5 mb-5">
    <?= $msg ?>

    <div class="row">
        <div class="col-lg-7">
            <div class="article-card p-4 mb-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-info text-uppercase small fw-bold"><?= htmlspecialchars($article['issue_nazev']) ?></span>
                    <span class="text-muted small">ID: #<?= $article['id'] ?></span>
                </div>
                <h2 class="text-white mb-3"><?= htmlspecialchars($article['title']) ?></h2>
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-dark rounded-circle d-flex justify-content-center align-items-center border border-secondary" style="width:40px; height:40px;">
                        <i class="fa-solid fa-user text-secondary"></i>
                    </div>
                    <div class="ms-3">
                        <div class="text-white fw-bold"><?= htmlspecialchars($article['jmeno'] . ' ' . $article['prijmeni']) ?></div>
                        <div class="text-muted small"><?= htmlspecialchars($article['email']) ?></div>
                    </div>
                </div>
                <div class="bg-dark p-3 rounded border border-secondary d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase">Soubor článku</div>
                        <div class="text-white fw-bold"><?= htmlspecialchars($article['filename']) ?></div>
                    </div>
                    <a href="uploads/<?= htmlspecialchars($article['filename']) ?>" class="btn btn-sm btn-outline-primary" download>
                        <i class="fa-solid fa-download me-2"></i>Stáhnout
                    </a>
                </div>
            </div>

            <?php if ($messages_result->num_rows > 0): ?>
            <div class="article-card p-4 mb-4">
                <h5 class="text-white mb-3"><i class="fa-solid fa-comments me-2"></i>Historie komunikace</h5>
                <div class="d-flex flex-column gap-3">
                    <?php while($msg_row = $messages_result->fetch_assoc()): ?>
                        <div class="bg-dark p-3 rounded border border-secondary">
                            <div class="d-flex justify-content-between mb-1">
                                <strong class="text-info"><?= htmlspecialchars($msg_row['jmeno'] . ' ' . $msg_row['prijmeni']) ?></strong>
                                <small class="text-muted"><?= date("d.m.Y H:i", strtotime($msg_row['created_at'])) ?></small>
                            </div>
                            <p class="text-white mb-0 small"><?= nl2br(htmlspecialchars($msg_row['message'])) ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($is_editor): ?>
            <div class="article-card p-4">
                <h4 class="text-white mb-4">Stav recenzního řízení</h4>
                <?php if ($reviews_result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table align-middle table-hover">
                            <thead>
                                <tr class="text-muted small text-uppercase">
                                    <th>Recenzent</th>
                                    <th>Termín</th>
                                    <th>Stav</th>
                                    <th class="text-end">Akce</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($rev = $reviews_result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="fw-bold text-white"><?= htmlspecialchars($rev['jmeno'] . ' ' . $rev['prijmeni']) ?></td>
                                        <td class="text-info small"><?= date("d.m.Y", strtotime($rev['deadline'])) ?></td>
                                        <td><?= getReviewStatusBadge($rev['status']) ?></td>
                                        <td class="text-end">
                                            <form method="POST" onsubmit="return confirm('Odebrat?');">
                                                <input type="hidden" name="review_id" value="<?= $rev['id'] ?>">
                                                <button type="submit" name="remove_reviewer" class="btn btn-sm btn-dark text-danger border-secondary"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center">Zatím bez recenzentů.</p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($is_editor): ?>
        <div class="col-lg-5">
            
            <?php if ($is_chief): ?>
            <div class="article-card p-4 mb-4" style="border-left: 4px solid #6e2bf9;">
                <h5 class="text-white mb-3">Odpovědný redaktor</h5>
                <form method="POST" class="d-flex gap-2">
                    <select name="editor_id" class="form-control form-control-sm">
                        <option value="0">-- Nepřiřazeno --</option>
                        <?php 
                        // Načtení seznamu redaktorů
                        $editors = $db_connection->query("SELECT u.id, u.jmeno, u.prijmeni FROM users u JOIN role r ON u.id_role=r.id_role WHERE r.role IN ('redaktor', 'šéfredaktor')");
                        while($ed = $editors->fetch_assoc()):
                            $sel = ($article['editor_id'] == $ed['id']) ? 'selected' : '';
                            echo "<option value='{$ed['id']}' $sel>{$ed['prijmeni']} {$ed['jmeno']}</option>";
                        endwhile;
                        ?>
                    </select>
                    <button type="submit" name="assign_editor" class="btn btn-sm btn-light">Uložit</button>
                </form>
            </div>
            <?php endif; ?>

            <div class="article-card p-4 mb-4">
                <h5 class="text-white mb-3">Přiřadit experty</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Recenzent</label>
                        <select name="reviewer_id" class="form-control" required>
                            <?php while($r_user = $all_reviewers->fetch_assoc()): ?>
                                <option value="<?= $r_user['id'] ?>"><?= htmlspecialchars($r_user['jmeno'] . ' ' . $r_user['prijmeni']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Deadline</label>
                        <input type="date" name="deadline" class="form-control" required value="<?= date('Y-m-d', strtotime('+14 days')) ?>">
                    </div>
                    <button type="submit" name="assign_reviewer" class="btn btn-primary w-100"><i class="fa-solid fa-paper-plane me-2"></i>Přiřadit</button>
                </form>
            </div>

            <div class="article-card p-4" style="border-top: 4px solid #facc15;">
                <h5 class="text-white mb-3">Formální kontrola</h5>
                <p class="text-muted small">Pokud článek nesplňuje formální náležitosti, můžete ho vrátit autorovi s komentářem.</p>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Zpráva pro autora</label>
                        <textarea name="message" class="form-control" rows="4" required placeholder="Např: Prosím o doplnění citací dle normy ISO 690..."></textarea>
                    </div>
                    <button type="submit" name="return_to_author" class="btn btn-warning w-100 text-dark fw-bold">
                        <i class="fa-solid fa-rotate-left me-2"></i>Vrátit k přepracování
                    </button>
                </form>
            </div>

        </div>
        <?php endif; ?>
    </div>
</div>

<?php require 'footer.php'; require 'db_close.php'; ?>