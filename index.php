<?php
require_once 'db.php';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $conn->query("DELETE FROM tasks WHERE id = $id");
    header('Location: index.php?msg=deleted');
    exit;
}

// Handle status toggle
if (isset($_GET['toggle'])) {
    $id = (int) $_GET['toggle'];
    $conn->query("
        UPDATE tasks
        SET status = CASE
            WHEN status = 'pending'     THEN 'in_progress'
            WHEN status = 'in_progress' THEN 'done'
            ELSE 'pending'
        END
        WHERE id = $id
    ");
    header('Location: index.php');
    exit;
}

// Filters
$filter_status   = $_GET['status']   ?? 'all';
$filter_priority = $_GET['priority'] ?? 'all';
$search          = $_GET['search']   ?? '';

$where = [];
if ($filter_status   !== 'all') $where[] = "status = '" . $conn->real_escape_string($filter_status) . "'";
if ($filter_priority !== 'all') $where[] = "priority = '" . $conn->real_escape_string($filter_priority) . "'";
if ($search !== '')             $where[] = "(title LIKE '%" . $conn->real_escape_string($search) . "%' OR description LIKE '%" . $conn->real_escape_string($search) . "%')";

$sql   = 'SELECT * FROM tasks' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY created_at DESC';
$tasks = $conn->query($sql);

// Stats
$stats = [];
foreach (['pending','in_progress','done'] as $s) {
    $r = $conn->query("SELECT COUNT(*) AS c FROM tasks WHERE status='$s'");
    $stats[$s] = $r->fetch_assoc()['c'];
}
$total = array_sum($stats);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionnaire de Tâches</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="app-container">

    <!-- Header -->
    <header class="app-header">
        <div class="header-left">
            <i class="fas fa-check-double"></i>
            <h1>Gestionnaire de Tâches</h1>
        </div>
        <a href="add_edit.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nouvelle Tâche
        </a>
    </header>

    <!-- Flash message -->
    <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-<?= $_GET['msg'] === 'saved' ? 'success' : 'error' ?>">
        <?= $_GET['msg'] === 'saved' ? '✅ Tâche enregistrée avec succès !' : '🗑️ Tâche supprimée.' ?>
    </div>
    <?php endif; ?>

    <!-- Stats cards -->
    <div class="stats-grid">
        <div class="stat-card total">
            <span class="stat-number"><?= $total ?></span>
            <span class="stat-label">Total</span>
        </div>
        <div class="stat-card pending">
            <span class="stat-number"><?= $stats['pending'] ?></span>
            <span class="stat-label">En attente</span>
        </div>
        <div class="stat-card in-progress">
            <span class="stat-number"><?= $stats['in_progress'] ?></span>
            <span class="stat-label">En cours</span>
        </div>
        <div class="stat-card done">
            <span class="stat-number"><?= $stats['done'] ?></span>
            <span class="stat-label">Terminées</span>
        </div>
    </div>

    <!-- Filters -->
    <form class="filter-bar" method="GET">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" name="search" placeholder="Rechercher une tâche..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <select name="status">
            <option value="all"     <?= $filter_status==='all'         ? 'selected':'' ?>>Tous les statuts</option>
            <option value="pending"     <?= $filter_status==='pending'     ? 'selected':'' ?>>En attente</option>
            <option value="in_progress" <?= $filter_status==='in_progress' ? 'selected':'' ?>>En cours</option>
            <option value="done"        <?= $filter_status==='done'        ? 'selected':'' ?>>Terminées</option>
        </select>
        <select name="priority">
            <option value="all"    <?= $filter_priority==='all'    ? 'selected':'' ?>>Toutes priorités</option>
            <option value="low"    <?= $filter_priority==='low'    ? 'selected':'' ?>>Faible</option>
            <option value="medium" <?= $filter_priority==='medium' ? 'selected':'' ?>>Moyenne</option>
            <option value="high"   <?= $filter_priority==='high'   ? 'selected':'' ?>>Haute</option>
        </select>
        <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filtrer</button>
        <a href="index.php" class="btn btn-ghost"><i class="fas fa-times"></i> Reset</a>
    </form>

    <!-- Task list -->
    <?php if ($tasks->num_rows === 0): ?>
    <div class="empty-state">
        <i class="fas fa-clipboard-list"></i>
        <p>Aucune tâche trouvée.</p>
        <a href="add_edit.php" class="btn btn-primary">Créer une tâche</a>
    </div>
    <?php else: ?>
    <div class="task-grid">
        <?php while ($task = $tasks->fetch_assoc()): ?>
        <div class="task-card priority-<?= $task['priority'] ?> status-<?= $task['status'] ?>">
            <div class="task-header">
                <span class="badge status-badge <?= $task['status'] ?>">
                    <?= ['pending'=>'⏳ En attente','in_progress'=>'🔄 En cours','done'=>'✅ Terminée'][$task['status']] ?>
                </span>
                <span class="badge priority-badge <?= $task['priority'] ?>">
                    <?= ['low'=>'🟢 Faible','medium'=>'🟡 Moyenne','high'=>'🔴 Haute'][$task['priority']] ?>
                </span>
            </div>
            <h3 class="task-title <?= $task['status']==='done' ? 'strikethrough':'' ?>">
                <?= htmlspecialchars($task['title']) ?>
            </h3>
            <?php if ($task['description']): ?>
            <p class="task-desc"><?= nl2br(htmlspecialchars($task['description'])) ?></p>
            <?php endif; ?>
            <?php if ($task['due_date']): ?>
            <div class="task-due <?= (strtotime($task['due_date']) < time() && $task['status']!=='done') ? 'overdue':'' ?>">
                <i class="fas fa-calendar-alt"></i>
                <?= date('d/m/Y', strtotime($task['due_date'])) ?>
            </div>
            <?php endif; ?>
            <div class="task-actions">
                <a href="?toggle=<?= $task['id'] ?>" class="btn btn-sm btn-toggle" title="Changer statut">
                    <i class="fas fa-sync-alt"></i>
                </a>
                <a href="add_edit.php?id=<?= $task['id'] ?>" class="btn btn-sm btn-edit">
                    <i class="fas fa-pen"></i> Modifier
                </a>
                <a href="?delete=<?= $task['id'] ?>" class="btn btn-sm btn-delete"
                   onclick="return confirm('Supprimer cette tâche ?')">
                    <i class="fas fa-trash"></i>
                </a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>

</div>
</body>
</html>
