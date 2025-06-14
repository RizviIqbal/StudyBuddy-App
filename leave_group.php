<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "studybuddy");

$user_id = $_SESSION['user_id'];
$leaveError = $successMsg = "";

// Handle POST leave request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['group_id'])) {
    $group_id = intval($_POST['group_id']);

    // Check if user is part of the group
    $check = $conn->prepare("SELECT * FROM group_members WHERE group_id = ? AND user_id = ?");
    $check->bind_param("ii", $group_id, $user_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $delete = $conn->prepare("DELETE FROM group_members WHERE group_id = ? AND user_id = ?");
        $delete->bind_param("ii", $group_id, $user_id);
        if ($delete->execute()) {
            $successMsg = "You have successfully left the group.";
        } else {
            $leaveError = "Failed to leave the group. Please try again.";
        }
        $delete->close();
    } else {
        $leaveError = "You are not a member of this group.";
    }

    $check->close();
}

// Get the list of groups the user is a member of
$groupsStmt = $conn->prepare("
    SELECT g.id, g.name 
    FROM groups g
    JOIN group_members gm ON g.id = gm.group_id
    WHERE gm.user_id = ?
");
$groupsStmt->bind_param("i", $user_id);
$groupsStmt->execute();
$groups = $groupsStmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Leave Group | StudyBuddy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f6f9;
            font-family: 'Inter', sans-serif;
        }
        .card {
            margin-top: 60px;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .btn-danger {
            background: linear-gradient(135deg, #ff416c, #ff4b2b);
            border: none;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card p-4">
                <h3 class="mb-4 text-center">Leave a Group</h3>

                <?php if ($leaveError): ?>
                    <div class="alert alert-danger"><?php echo $leaveError; ?></div>
                <?php elseif ($successMsg): ?>
                    <div class="alert alert-success"><?php echo $successMsg; ?></div>
                <?php endif; ?>

                <?php if ($groups->num_rows > 0): ?>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="group_id" class="form-label">Select Group</label>
                            <select name="group_id" id="group_id" class="form-select" required>
                                <option value="">-- Select a group --</option>
                                <?php while ($row = $groups->fetch_assoc()): ?>
                                    <option value="<?php echo $row['id']; ?>">
                                        <?php echo htmlspecialchars($row['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger">Leave Group</button>
                        </div>
                    </form>
                <?php else: ?>
                    <p class="text-muted text-center">You haven't joined any groups yet.</p>
                <?php endif; ?>

                <div class="text-center mt-3">
                    <a href="groups.php" class="text-decoration-none">← Back to Groups</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
