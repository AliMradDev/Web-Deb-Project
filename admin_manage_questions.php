<?php
session_start();
require_once 'database.php';

$pdo = getDbConnection();

// Protect this page - only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Handle form submissions for Add, Edit, Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_question'])) {
        // Add new question
        $q = $_POST['question_text'] ?? '';
        $a = $_POST['option_a'] ?? '';
        $b = $_POST['option_b'] ?? '';
        $c = $_POST['option_c'] ?? '';
        $d = $_POST['option_d'] ?? '';
        $correct = $_POST['correct_option'] ?? '';

        $stmt = $pdo->prepare("INSERT INTO exam (question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$q, $a, $b, $c, $d, $correct]);

        header("Location: admin_manage_questions.php");
        exit();

    } elseif (isset($_POST['edit_question'])) {
        // Edit existing question
        $id = $_POST['id'] ?? 0;
        $q = $_POST['question_text'] ?? '';
        $a = $_POST['option_a'] ?? '';
        $b = $_POST['option_b'] ?? '';
        $c = $_POST['option_c'] ?? '';
        $d = $_POST['option_d'] ?? '';
        $correct = $_POST['correct_option'] ?? '';

        $stmt = $pdo->prepare("UPDATE exam SET question_text=?, option_a=?, option_b=?, option_c=?, option_d=?, correct_option=? WHERE id=?");
        $stmt->execute([$q, $a, $b, $c, $d, $correct, $id]);

        header("Location: admin_manage_questions.php");
        exit();

    } elseif (isset($_POST['delete_question'])) {
        // Delete question
        $id = $_POST['id'] ?? 0;
        $stmt = $pdo->prepare("DELETE FROM exam WHERE id=?");
        $stmt->execute([$id]);

        header("Location: admin_manage_questions.php");
        exit();
    }
}

// Fetch all questions
$stmt = $pdo->query("SELECT * FROM exam ORDER BY id ASC");
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Manage Exam Questions</title>
<style>
    body { font-family: Arial, sans-serif; max-width: 900px; margin: 30px auto; background: #f9fafb; }
    h1 { color: #4b5563; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
    th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; }
    th { background: #e5e7eb; }
    form { margin-bottom: 40px; background: white; padding: 15px; border-radius: 6px; box-shadow: 0 2px 8px rgb(0 0 0 / 0.1); }
    label { display: block; margin-top: 10px; font-weight: bold; color: #374151; }
    input[type=text], textarea, select { width: 100%; padding: 6px; margin-top: 4px; border: 1px solid #9ca3af; border-radius: 4px; }
    button { margin-top: 15px; padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 6px; cursor: pointer; }
    button:hover { background: #2563eb; }
    .actions form { display: inline-block; margin: 0 5px; }
</style>
</head>
<body>

<h1>Manage Exam Questions</h1>

<!-- Add New Question -->
<form method="post" action="">
    <h2>Add New Question</h2>
    <label for="question_text">Question</label>
    <textarea id="question_text" name="question_text" rows="3" required></textarea>

    <label for="option_a">Option A</label>
    <input type="text" id="option_a" name="option_a" required>

    <label for="option_b">Option B</label>
    <input type="text" id="option_b" name="option_b" required>

    <label for="option_c">Option C</label>
    <input type="text" id="option_c" name="option_c" required>

    <label for="option_d">Option D</label>
    <input type="text" id="option_d" name="option_d" required>

    <label for="correct_option">Correct Option</label>
    <select id="correct_option" name="correct_option" required>
        <option value="">-- Select --</option>
        <option value="a">Option A</option>
        <option value="b">Option B</option>
        <option value="c">Option C</option>
        <option value="d">Option D</option>
    </select>

    <button type="submit" name="add_question">Add Question</button>
</form>

<!-- List Questions -->
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Question</th>
            <th>Option A</th>
            <th>Option B</th>
            <th>Option C</th>
            <th>Option D</th>
            <th>Correct</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($questions as $q): ?>
        <tr>
            <td><?= htmlspecialchars($q['id']) ?></td>
            <td><?= htmlspecialchars($q['question_text']) ?></td>
            <td><?= htmlspecialchars($q['option_a']) ?></td>
            <td><?= htmlspecialchars($q['option_b']) ?></td>
            <td><?= htmlspecialchars($q['option_c']) ?></td>
            <td><?= htmlspecialchars($q['option_d']) ?></td>
            <td><?= strtoupper(htmlspecialchars($q['correct_option'])) ?></td>
            <td class="actions">
                <!-- Edit Form -->
                <form method="post" action="" style="display:inline-block;">
                    <input type="hidden" name="id" value="<?= $q['id'] ?>">
                    <input type="hidden" name="question_text" value="<?= htmlspecialchars($q['question_text']) ?>">
                    <input type="hidden" name="option_a" value="<?= htmlspecialchars($q['option_a']) ?>">
                    <input type="hidden" name="option_b" value="<?= htmlspecialchars($q['option_b']) ?>">
                    <input type="hidden" name="option_c" value="<?= htmlspecialchars($q['option_c']) ?>">
                    <input type="hidden" name="option_d" value="<?= htmlspecialchars($q['option_d']) ?>">
                    <input type="hidden" name="correct_option" value="<?= htmlspecialchars($q['correct_option']) ?>">
                    <button type="button" onclick="openEditModal(<?= $q['id'] ?>)">Edit</button>
                </form>

                <!-- Delete Form -->
                <form method="post" action="" onsubmit="return confirm('Delete this question?');" style="display:inline-block;">
                    <input type="hidden" name="id" value="<?= $q['id'] ?>">
                    <button type="submit" name="delete_question" style="background:#ef4444;">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Edit Modal (hidden by default) -->
<div id="editModal" style="display:none; position:fixed; top:50%; left:50%; transform: translate(-50%, -50%);
 background:white; border-radius: 8px; box-shadow: 0 2px 10px rgb(0 0 0 / 0.2); padding: 20px; width: 90%; max-width: 600px; z-index: 1000;">
    <h2>Edit Question</h2>
    <form method="post" action="">
        <input type="hidden" name="id" id="edit_id">
        
        <label for="edit_question_text">Question</label>
        <textarea id="edit_question_text" name="question_text" rows="3" required></textarea>

        <label for="edit_option_a">Option A</label>
        <input type="text" id="edit_option_a" name="option_a" required>

        <label for="edit_option_b">Option B</label>
        <input type="text" id="edit_option_b" name="option_b" required>

        <label for="edit_option_c">Option C</label>
        <input type="text" id="edit_option_c" name="option_c" required>

        <label for="edit_option_d">Option D</label>
        <input type="text" id="edit_option_d" name="option_d" required>

        <label for="edit_correct_option">Correct Option</label>
        <select id="edit_correct_option" name="correct_option" required>
            <option value="">-- Select --</option>
            <option value="a">Option A</option>
            <option value="b">Option B</option>
            <option value="c">Option C</option>
            <option value="d">Option D</option>
        </select>

        <button type="submit" name="edit_question">Save Changes</button>
        <button type="button" onclick="closeEditModal()" style="background:#ef4444; margin-left: 10px;">Cancel</button>
    </form>
</div>

<script>
// Open edit modal and fill inputs
function openEditModal(id) {
    // Find question row data
    const row = [...document.querySelectorAll('tbody tr')].find(tr => tr.children[0].textContent == id);
    if (!row) return alert('Question not found');

    document.getElementById('edit_id').value = id;
    document.getElementById('edit_question_text').value = row.children[1].textContent.trim();
    document.getElementById('edit_option_a').value = row.children[2].textContent.trim();
    document.getElementById('edit_option_b').value = row.children[3].textContent.trim();
    document.getElementById('edit_option_c').value = row.children[4].textContent.trim();
    document.getElementById('edit_option_d').value = row.children[5].textContent.trim();
    document.getElementById('edit_correct_option').value = row.children[6].textContent.trim().toLowerCase();

    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}
</script>

</body>
</html>
