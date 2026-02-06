<?php
include('includes/db.php');
include('includes/header.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $message = $conn->real_escape_string($_POST['message']);

    // Insert into database
    $sql = "INSERT INTO contact (name, email, message) VALUES ('$name', '$email', '$message')";
    if ($conn->query($sql)) {
        echo '<div class="container mt-4 alert alert-success text-center" style="max-width: 600px;">
                Thank you for contacting us, ' . htmlspecialchars($name) . '!
              </div>';
    } else {
        echo '<div class="container mt-4 alert alert-danger text-center" style="max-width: 600px;">
                Error: ' . $conn->error . '
              </div>';
    }
}
?>

<style>
body {
    background: linear-gradient(135deg, #0d1b2a, #1b3b6f, #f9fafb);
    color: #1b2e35;
    font-family: 'Poppins', sans-serif;
}

.contact-wrapper {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    align-items: stretch;
    max-width: 1000px;
    margin: 80px auto;
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.contact-form {
    flex: 1;
    background-color: #1a2b6d;
    color: #fff;
    padding: 50px 40px;
    min-width: 320px;
}

.contact-form h2 {
    margin-bottom: 25px;
    font-size: 28px;
}

.contact-form label {
    font-weight: 600;
    margin-top: 10px;
    display: block;
}

.contact-form input,
.contact-form textarea {
    width: 100%;
    padding: 12px 15px;
    margin-top: 8px;
    border: none;
    border-radius: 8px;
    outline: none;
    font-size: 15px;
    color: #333;
}

.contact-form button {
    background-color: #ff5722;
    color: white;
    padding: 12px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    width: 100%;
    margin-top: 20px;
    transition: background 0.3s ease;
}

.contact-form button:hover {
    background-color: #e64a19;
}

.contact-image {
    flex: 1;
    background-color: #ffffffff;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 40px;
    min-width: 320px;
}

.contact-image img {
    max-width: 100%;
    height: auto;
}

/* Responsive Design */
@media (max-width: 768px) {
    .contact-wrapper {
        flex-direction: column;
    }
    .contact-form, .contact-image {
        width: 100%;
    }
}
</style>

<div class="contact-wrapper">
    <div class="contact-form">
        <h2>Contact Us</h2>
        <form method="POST" action="">
            <label>Name</label>
            <input type="text" name="name" required>

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Message</label>
            <textarea name="message" rows="4" required></textarea>

            <button type="submit">Submit</button>
        </form>
    </div>

    <div class="contact-image">
        <img src="uploads/con.jpg" alt="Contact Illustration">
    </div>
</div>

<?php include('includes/footer.php'); ?>
