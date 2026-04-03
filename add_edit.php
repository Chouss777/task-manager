<?php
require_once 'db.php';

$task = ['id'=>'','title'=>'','description'=>'','status'=>'pending','priority'=>'medium','due_date'=>''];
$errors = [];
$is_edit = isset($_GET['id']);

if ($is_edit) {
    $id = (int) $_GET['id'];
    $res = $conn->query("SELECT * FROM tasks WHERE id = $id");
    if ($res->num_rows) {
        $task = $res->fetch_assoc();
    } else {
        header('Location: index.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status      = $_POST['status']   ?? 'pending';
    $priority    = $_POST['priority'] ?? 'medium';
    $due_date    = $_POST['due_date'] ?? '';

    if ($title === '') $errors[] = 'Le titre est obligatoire.';

    if (empty($errors)) {
        $t  = $conn->real_escape_string($title);
        $d  = $conn->real_escape_string($description);
        $s  = $conn->real_escape_string($status);
        $p  = $conn->real_escape_string($priority);
        $dd = $due_date ? "'" . $conn->real_escape_string($due_date) . "'" : 'NULL';

        if ($is_edit) {
            $id = (int) $_POST['id'];
            $conn->query("UPDATE tasks SET title='$t', description='$d', status='$s', priority='$p', due_date=$dd WHERE id=$id");
        } else {
            $conn->query("INSERT INTO tasks (title, description, status, priority, due_date) VALUES ('$t','$d','$s','$p',$dd)");
        }
        header('Location: index.php?msg=saved');
        exit;
    }
    // Re-populate form on error
    $task = array_merge($task, compact('title','description','status','priority','due_date'));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $is_edit ? 'Modifier' : 'Nouvelle' ?> Tâche</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="app-container form-page">

    <header class="app-header">
        <div class="header-left">
            <a href="index.php" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Retour</a>
            <h1><?= $is_edit ? '<i class="fas fa-pen"></i> Modifier la Tâche' : '<i class="fas fa-plus"></i> Nouvelle Tâche' ?></h1>
        </div>
    </header>

    <?php if ($errors): ?>
    <div class="alert alert-error">
        <?php foreach ($errors as $e): ?><p>⚠️ <?= htmlspecialchars($e) ?></p><?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" action="add_edit.php<?= $is_edit ? '?id='.$task['id'] : '' ?>">
            <?php if ($is_edit): ?>
            <input type="hidden" name="id" value="<?= $task['id'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="title"><i class="fas fa-heading"></i> Titre <span class="required">*</span></label>
                <input type="text" id="title" name="title" class="form-control"
                       value="<?= htmlspecialchars($task['title']) ?>" placeholder="Titre de la tâche..." required>
            </div>

            <div class="form-group">
                <label for="description"><i class="fas fa-align-left"></i> Description</label>
                <textarea id="description" name="description" class="form-control" rows="4"
                          placeholder="Décrivez la tâche..."><?= htmlspecialchars($task['description']) ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="status"><i class="fas fa-tasks"></i> Statut</label>
                    <select id="status" name="status" class="form-control">
                        <option value="pending"     <?= $task['status']==='pending'     ? 'selected':'' ?>>⏳ En attente</option>
                        <option value="in_progress" <?= $task['status']==='in_progress' ? 'selected':'' ?>>🔄 En cours</option>
                        <option value="done"        <?= $task['status']==='done'        ? 'selected':'' ?>>✅ Terminée</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="priority"><i class="fas fa-flag"></i> Priorité</label>
                    <select id="priority" name="priority" class="form-control">
                        <option value="low"    <?= $task['priority']==='low'    ? 'selected':'' ?>>🟢 Faible</option>
                        <option value="medium" <?= $task['priority']==='medium' ? 'selected':'' ?>>🟡 Moyenne</option>
                        <option value="high"   <?= $task['priority']==='high'   ? 'selected':'' ?>>🔴 Haute</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="due_date"><i class="fas fa-calendar-alt"></i> Date limite</label>
                    <input type="date" id="due_date" name="due_date" class="form-control"
                           value="<?= htmlspecialchars($task['due_date']) ?>">
                </div>
            </div>

            <div class="form-actions">
                <a href="index.php" class="btn btn-ghost"><i class="fas fa-times"></i> Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?= $is_edit ? 'Mettre à jour' : 'Enregistrer' ?>
                </button>
            </div>
        </form>
    </div>

</div>
</body>
</html>
