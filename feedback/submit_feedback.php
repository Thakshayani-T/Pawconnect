<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include('../includes/db.php');
include('../includes/header.php');

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $user_id = $_SESSION['user_id'];
    $user_type = $_SESSION['user_type'];

    if (empty($subject)) {
        $errors[] = "Subject is required.";
    }
    if (empty($message)) {
        $errors[] = "Message is required.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO feedback (user_id, user_type, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $user_type, $subject, $message);
        if ($stmt->execute()) {
            $success = "Thank you for your feedback!";
        } else {
            $errors[] = "Failed to submit feedback. Please try again.";
        }
        $stmt->close();
    }
}
?>

<div class="container mt-5" style="max-width: 600px;">
    <h2>Submit Feedback</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
        <a href="../dashboard/index.php" class="btn btn-primary">Back to Dashboard</a>
    <?php else: ?>
        <?php foreach ($errors as $error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endforeach; ?>

        <form method="POST">
            <div class="mb-3">
                <label>Subject</label>
                <input type="text" name="subject" class="form-control" required value="<?= isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : '' ?>">
            </div>
            <div class="mb-3">
                <label>Message</label>
                <textarea name="message" class="form-control" rows="5" required><?= isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '' ?></textarea>
            </div>
            <button type="submit" class="btn btn-success">Submit Feedback</button>
        </form>
    <?php endif; ?>
</div>

<?php include('../includes/footer.php'); ?>
