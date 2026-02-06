<?php
include('includes/db.php');
include('includes/header.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string(trim($_POST['name']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $user_type = $conn->real_escape_string($_POST['user_type']);
    $location = $conn->real_escape_string(trim($_POST['location']));
    $contact = $conn->real_escape_string(trim($_POST['contact']));

    $valid_user_types = ['adopter', 'center', 'admin'];
    if (!in_array($user_type, $valid_user_types)) {
        echo '<div class="message error">Please select a valid user type.</div>';
    } else {
        $check = $conn->query("SELECT id FROM users WHERE email='$email'");
        if ($check->num_rows > 0) {
            echo '<div class="message error">Email already registered.</div>';
        } else {
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, user_type, location, contact) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $name, $email, $password, $user_type, $location, $contact);
            if ($stmt->execute()) {
                echo '<div class="message success">Registration successful! <a href="login.php">Login here</a>.</div>';
            } else {
                echo '<div class="message error">Error occurred during registration.</div>';
            }
            $stmt->close();
        }
    }
}
?>

<style>
/* General Page Styling */
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #0d1b2a, #1b3b6f, #f9fafb); /* Dark blue → Light blue → White */
  color: #1b2e35;
    margin: 0;
    padding: 0;
}

/* Container Layout */
.register-wrapper {
    display: flex;
    flex-wrap: wrap;
    max-width: 1200px; /* Increased width for better layout */
    margin: 60px auto;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    background-color: #ffffffff;
}

/* Left Side (Image) */
.register-image {
    flex: 1.2; /* Slightly larger flex to make image bigger */
    background-color: #ffffffff;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 40px;
    min-width: 400px;
}
.register-image img {
    max-width: 100%;
    height: auto;
    border-radius: 10px;
}

/* Right Side (Form) */
.register-form {
    flex: 1;
    background-color: #0058ff;
    color: #fff;
    padding: 60px 50px;
    min-width: 320px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.register-form h2 {
    font-size: 2rem;
    margin-bottom: 25px;
    text-align: center;
}

.register-form label {
    font-weight: 600;
    display: block;
    margin-top: 10px;
}

.register-form input,
.register-form select {
    width: 100%;
    padding: 12px 15px;
    margin-top: 8px;
    margin-bottom: 15px;
    border-radius: 30px;
    border: none;
    outline: none;
    font-size: 15px;
    color: #333;
}

.register-form input::placeholder {
    color: #aaa;
}

/* Buttons */
.btn-container {
    display: flex;
    justify-content: space-between;
    gap: 10px;
}

.register-form .btn {
    flex: 1;
    padding: 12px;
    border-radius: 30px;
    font-weight: 600;
    font-size: 1rem;
    border: none;
    cursor: pointer;
    transition: 0.3s ease;
}

.btn-submit {
    background: linear-gradient(90deg, #ffb300, #ff8f00);
    color: #fff;
}
.btn-submit:hover {
    background: linear-gradient(90deg, #ffca28, #ff9800);
}
.btn-clear {
    background: #fff;
    color: #0058ff;
    border: 2px solid #fff;
}
.btn-clear:hover {
    background: #e8f0ff;
    color: #0040b3;
}

/* Success / Error Messages */
.message {
    margin: 15px auto;
    padding: 12px 18px;
    border-radius: 8px;
    max-width: 700px;
    text-align: center;
    font-weight: 500;
}
.message.success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
.message.error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
.message a {
    color: #155724;
    font-weight: 600;
    text-decoration: underline;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .register-wrapper {
        flex-direction: column;
    }
    .register-image, .register-form {
        width: 100%;
        min-width: auto;
    }
    .register-form {
        padding: 40px 30px;
    }
}
@media (max-width: 600px) {
    .register-form {
        padding: 30px 20px;
    }
}
</style>

<div class="register-wrapper">
    <!-- Left Illustration -->
    <div class="register-image">
        <img src="uploads/sig2.jpg" alt="Register Illustration">
    </div>

    <!-- Right Form -->
    <div class="register-form">
        <h2>Register</h2>
        <form method="POST" action="" id="registerForm">
            <label>Name</label>
            <input type="text" name="name" placeholder="Enter your name" required>

            <label>Email</label>
            <input type="email" name="email" placeholder="Enter your email" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="Enter your password" required>

            <label>User Type</label>
            <select name="user_type" required>
                <option value="">Select</option>
                <option value="adopter">Adopter</option>
                <option value="center">Adoption Center</option>
                <option value="admin">Admin</option>
            </select>

            <label>Location</label>
            <input type="text" name="location" placeholder="Enter your location">

            <label>Contact</label>
            <input type="text" name="contact" placeholder="Enter your contact number">

            <div class="btn-container">
                <button type="submit" class="btn btn-submit">Register</button>
                <button type="button" class="btn btn-clear" onclick="document.getElementById('registerForm').reset()">Clear</button>
            </div>
        </form>
    </div>
</div>

<?php include('includes/footer.php'); ?>
