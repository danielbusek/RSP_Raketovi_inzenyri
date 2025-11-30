<?php
$page_title = "Správa čísel časopisu";
require 'db_connect.php';
require 'header.php';

// Ochrana: Jen Admin nebo Šéfredaktor
$role = mb_strtolower($_SESSION['user_role'] ?? '', 'UTF-8');
if ($role !== 'admin' && $role !== 'šéfredaktor') {
    header("Location: index.php");
    exit;
}

$editMode = false;
$editData = [];

// --- ZPRACOVÁNÍ FORMULÁŘE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'status_change') {
        $id = intval($_POST['id']);
        $newStatus = $_POST['new_status'];
        $stmt = $db_connection->prepare("UPDATE issues SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $id);
        $stmt->execute();
        header("Location: manage_issues.php?msg=status_ok");
        exit;
    }
    $nazev = trim($_POST['nazev']);
    $rocnik = intval($_POST['rocnik']);
    $cislo = intval($_POST['cislo']);
    $deadline = $_POST['deadline'];
    $capacity = intval($_POST['max_capacity']);
    $id = intval($_POST['issue_id'] ?? 0);

    if ($id > 0) {
        $stmt = $db_connection->prepare("UPDATE issues SET nazev=?, rocnik=?, cislo=?, deadline=?, max_capacity=? WHERE id=?");
        $stmt->bind_param("siisii", $nazev, $rocnik, $cislo, $deadline, $capacity, $id);
    } else {
        $stmt = $db_connection->prepare("INSERT INTO issues (nazev, rocnik, cislo, deadline, max_capacity, status) VALUES (?, ?, ?, ?, ?, 'open')");
        $stmt->bind_param("siisi", $nazev, $rocnik, $cislo, $deadline, $capacity);
    }
    $stmt->execute();
    header("Location: manage_issues.php");
    exit;
}

// --- PŘÍPRAVA EDITACE ---
if (isset($_GET['edit'])) {
    $editMode = true;
    $id = intval($_GET['edit']);
    $stmt = $db_connection->prepare("SELECT * FROM issues WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
}

// --- NAČTENÍ SEZNAMU ---
$result = $db_connection->query("SELECT * FROM issues ORDER BY rocnik DESC, cislo DESC");

function getIssueBadge($status) {
    if ($status === 'open') return '<span class="badge bg-success bg-opacity-75 border border-success px-2 py-1">Otevřeno</span>';
    if ($status === 'closed') return '<span class="badge bg-danger bg-opacity-75 border border-danger px-2 py-1">Uzavřeno</span>';
    if ($status === 'published') return '<span class="badge bg-primary bg-opacity-75 border border-primary px-2 py-1">Publikováno</span>';
    return $status;
}
?>

<div class="container mt-5 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3 flex-wrap gap-3">
        <h2 class="text-white mb-0">Správa čísel časopisu</h2>
        <?php if($editMode): ?>
            <a href="manage_issues.php" class="btn btn-outline-warning btn-sm">
                <i class="fa-solid fa-xmark me-2"></i>Zrušit editaci
            </a>
        <?php endif; ?>
    </div>

    <div class="row g-4">
        
        <div class="col-lg-4 order-lg-1 order-2">
            <div class="article-card p-4 sticky-top" style="top: 100px; z-index: 1;">
                <h4 class="text-white mb-4">
                    <i class="fa-solid fa-pen-to-square me-2 text-primary"></i>
                    <?= $editMode ? 'Upravit údaje' : 'Nové číslo' ?>
                </h4>
                
                <form method="POST">
                    <input type="hidden" name="issue_id" value="<?= $editData['id'] ?? 0 ?>">

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">NÁZEV / TÉMA</label>
                        <input type="text" name="nazev" class="form-control" required 
                               placeholder="Např. AI v kosmonautice"
                               value="<?= htmlspecialchars($editData['nazev'] ?? '') ?>">
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label text-muted small fw-bold">ROČNÍK</label>
                            <input type="number" name="rocnik" class="form-control text-center" required 
                                   value="<?= $editData['rocnik'] ?? date('Y') ?>">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label text-muted small fw-bold">ČÍSLO</label>
                            <input type="number" name="cislo" class="form-control text-center" required 
                                   value="<?= $editData['cislo'] ?? 1 ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">UZÁVĚRKA</label>
                        <input type="date" name="deadline" class="form-control" required 
                               value="<?= $editData['deadline'] ?? '' ?>">
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold">KAPACITA</label>
                        <input type="number" name="max_capacity" class="form-control" required 
                               value="<?= $editData['max_capacity'] ?? 10 ?>">
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <?= $editMode ? 'Uložit změny' : '<i class="fa-solid fa-plus me-2"></i> Vytvořit' ?>
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-8 order-lg-2 order-1">
            <div class="article-card p-0 h-100">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle table-hover">
                        <thead>
                            <tr class="text-uppercase small text-secondary" style="background: rgba(255,255,255,0.02);">
                                <th class="ps-4 py-3" style="min-width: 100px;">Číslo</th>
                                <th class="py-3" style="width: 40%;">Téma</th>
                                <th class="py-3 text-center">Uzávěrka</th>
                                <th class="py-3 text-center">Stav</th>
                                <th class="pe-4 py-3 text-end">Akce</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <span class="fs-5 fw-bold text-white me-2"><?= $row['rocnik'] ?>/<?= $row['cislo'] ?></span>
                                        </div>
                                    </td>
                                    
                                    <td class="fw-bold text-light text-break">
                                        <?= htmlspecialchars($row['nazev']) ?>
                                    </td>
                                    
                                    <td class="text-center text-info small text-nowrap">
                                        <?= date("d.m.Y", strtotime($row['deadline'])) ?>
                                    </td>
                                    
                                    <td class="text-center text-nowrap">
                                        <?= getIssueBadge($row['status']) ?>
                                    </td>
                                    
                                    <td class="pe-4 text-end">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-dark border-secondary text-light dropdown-toggle px-3" type="button" data-bs-toggle="dropdown">
                                                <i class="fa-solid fa-ellipsis"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end bg-dark border-secondary shadow-lg">
                                                <li><a class="dropdown-item text-warning" href="?edit=<?= $row['id'] ?>"><i class="fa-solid fa-pen me-2"></i>Upravit</a></li>
                                                <li><hr class="dropdown-divider bg-secondary"></li>
                                                
                                                <?php if($row['status'] !== 'open'): ?>
                                                <li>
                                                    <form method="POST"><input type="hidden" name="action" value="status_change"><input type="hidden" name="id" value="<?= $row['id'] ?>"><input type="hidden" name="new_status" value="open"><button class="dropdown-item text-success">Otevřít</button></form>
                                                </li>
                                                <?php endif; ?>

                                                <?php if($row['status'] !== 'closed'): ?>
                                                <li>
                                                    <form method="POST"><input type="hidden" name="action" value="status_change"><input type="hidden" name="id" value="<?= $row['id'] ?>"><input type="hidden" name="new_status" value="closed"><button class="dropdown-item text-danger">Uzavřít</button></form>
                                                </li>
                                                <?php endif; ?>

                                                <?php if($row['status'] !== 'published'): ?>
                                                <li>
                                                    <form method="POST"><input type="hidden" name="action" value="status_change"><input type="hidden" name="id" value="<?= $row['id'] ?>"><input type="hidden" name="new_status" value="published"><button class="dropdown-item text-primary">Publikovat</button></form>
                                                </li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<?php
require 'footer.php';
require 'db_close.php';
?>