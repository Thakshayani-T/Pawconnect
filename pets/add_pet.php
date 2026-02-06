<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'center') {
    header("Location: ../login.php");
    exit;
}

include('../includes/db.php');

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $age = trim($_POST['age'] ?? '');
    $sale_status = trim($_POST['sale_status'] ?? 'available');
    $description = trim($_POST['description'] ?? '');
    $center_id = (int)$_SESSION['user_id'];

    // Handle image upload
    $imageFileName = null;
    if (!empty($_FILES['image']['name'])) {
        $targetDir = '../uploads/pets/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $imageFileName = time() . '_' . basename($_FILES['image']['name']);
        $targetFilePath = $targetDir . $imageFileName;

        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileType, $allowedTypes)) {
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
                $errors[] = "Sorry, there was an error uploading your image.";
            }
        } else {
            $errors[] = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        }
    }

    // Validation
    if ($name === '') {
        $errors[] = "Pet name is required.";
    }
    if ($type === '') {
        $errors[] = "Pet type is required.";
    }
    if ($age === '' || !is_numeric($age)) {
        $errors[] = "Valid age is required.";
    }
    if (!in_array($sale_status, ['available', 'sold'])) {
        $sale_status = 'available';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO pets (name, type, age, sale_status, description, image, added_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssisssi", $name, $type, $age, $sale_status, $description, $imageFileName, $center_id);
        if ($stmt->execute()) {
            $success = "Pet added successfully.";
            $name = $type = $age = $description = '';
            $sale_status = 'available';
            $imageFileName = null;
        } else {
            $errors[] = "Error adding pet: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Add New Pet - PawConnect</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f0f4f8;
  }
  .content-container {
    max-width: 600px;
    margin: 3rem auto;
    background: white;
    padding: 2rem 2.5rem;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
  }
  .back-btn {
    margin-bottom: 1.5rem;
  }
</style>
</head>
<body>

<div class="content-container">
  <a href="/pawconnect/dashboard/center_dashboard.php" class="btn btn-secondary back-btn">&larr; Back to Dashboard</a>

  <h3>Add New Pet</h3>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <ul>
        <?php foreach ($errors as $error): ?>
          <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="POST" action="" enctype="multipart/form-data">
    <div class="mb-3">
      <label for="name" class="form-label">Pet Name</label>
      <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($name ?? '') ?>" required />
    </div>

    <div class="mb-3">
      <label for="type" class="form-label">Pet Type</label>
      <input type="text" id="type" name="type" class="form-control" value="<?= htmlspecialchars($type ?? '') ?>" required />
    </div>

    <div class="mb-3">
      <label for="age" class="form-label">Age (years)</label>
      <input type="number" min="0" id="age" name="age" class="form-control" value="<?= htmlspecialchars($age ?? '') ?>" required />
    </div>

    <div class="mb-3">
      <label for="sale_status" class="form-label">Sale Status</label>
      <select id="sale_status" name="sale_status" class="form-select">
        <option value="available" <?= (isset($sale_status) && $sale_status === 'available') ? 'selected' : '' ?>>Available</option>
        <option value="sold" <?= (isset($sale_status) && $sale_status === 'sold') ? 'selected' : '' ?>>Sold</option>
      </select>
    </div>

    <div class="mb-3">
      <label for="description" class="form-label">Description</label>
      <textarea id="description" name="description" class="form-control" rows="4"><?= htmlspecialchars($description ?? '') ?></textarea>
    </div>

    <div class="mb-3">
      <label for="image" class="form-label">Upload Pet Image</label>
      <input type="file" id="image" name="image" class="form-control" accept=".jpg,.jpeg,.png,.gif" />
    </div>

    <button type="submit" class="btn btn-primary">Add Pet</button>
  </form>
</div>

</body>
</html>
