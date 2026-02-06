<?php
session_start();
include('includes/db.php');
include('includes/header.php');

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['reset_email'])) {
        $reset_email = $conn->real_escape_string($_POST['reset_email']);
        $result = $conn->query("SELECT * FROM users WHERE email='$reset_email'");
        $message = ($result && $result->num_rows == 1)
            ? "Please check your Gmail to reset your password."
            : "Email not found.";
    } else {
        $email = $conn->real_escape_string($_POST['email']);
        $password = $_POST['password'];
        $role = $_POST['role'];

        $result = $conn->query("SELECT * FROM users WHERE email='$email' AND user_type='$role'");
        if ($result && $result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_type'] = $user['user_type'];

                if ($role === 'admin') {
                    header("Location: dashboard/admin_dashboard.php");
                } elseif ($role === 'center') {
                    header("Location: dashboard/center_dashboard.php");
                } else {
                    header("Location: dashboard/adopter_dashboard.php");
                }
                exit;
            } else {
                $message = "Invalid password.";
            }
        } else {
            $message = "No account found for this role.";
        }
    }
}
?>

<style>
/* ---------- PAGE & BACKGROUND ---------- */
body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: url('uploads/pet10.jpg') no-repeat center center fixed;
    background-size: cover;
    color: #333;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

/* Overlay for readability */
body::before {
    content: "";
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.4);
    z-index: 0;
}

/* Main content */
main {
    flex: 1;
    display: flex;
    justify-content: flex-start; /* Align left */
    align-items: center;
    padding: 40px 10px;
    position: relative;
    z-index: 1;
}

/* Container for cards */
.container {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-start; /* Left alignment */
    gap: 20px;
    max-width: 1000px;
    margin-left: 20px; /* Shift slightly left from center */
}

/* Card Base */
.card {
    width: 270px;
    height: 360px;
    perspective: 1000px;
}
.card-inner {
    position: relative;
    width: 100%;
    height: 100%;
    transition: transform 0.8s;
    transform-style: preserve-3d;
}
.card:hover .card-inner {
    transform: rotateY(180deg);
}

/* Front / Back */
.card-front, .card-back {
    position: absolute;
    width: 100%;
    height: 100%;
    backface-visibility: hidden;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
}

/* Front Faces */
.card-front {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    color: white;
    font-weight: 600;
    text-align: center;
    padding: 20px;
}
.card-front i {
    font-size: 48px;
    margin-bottom: 10px;
}

/* Back Faces */
.card-back {
    background: #f9f9f9;
    transform: rotateY(180deg);
    padding: 20px;
}
.card-back h3 {
    text-align: center;
    margin-bottom: 10px;
    color: #222;
}
.card-back input {
    width: 100%;
    padding: 9px;
    margin: 6px 0;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 0.95rem;
}
.card-back button {
    width: 100%;
    padding: 10px;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    color: white;
    cursor: pointer;
    transition: 0.3s;
    margin-top: 5px;
}
.card-back .clear-btn {
    background-color: #888;
}
.card-back .clear-btn:hover {
    background-color: #555;
}
.card-back a {
    display: block;
    text-align: center;
    margin-top: 8px;
    font-size: 0.85rem;
    color: #007bff;
    text-decoration: none;
}
.card-back a:hover { text-decoration: underline; }

/* Role Colors */
.admin .card-front { background: linear-gradient(135deg, #0a2a6c, #0066cc); }
.center .card-front { background: linear-gradient(135deg, #0b6623, #28a745); }
.adopter .card-front { background: linear-gradient(135deg, #cc5500, #ff9933); }

.admin button { background-color: #003366; }
.center button { background-color: #1E8449; }
.adopter button { background-color: #FF7F00; }

/* Message */
.message {
    position: fixed;
    top: 20px;
    background: rgba(255,255,255,0.95);
    color: #333;
    padding: 10px 18px;
    border-radius: 5px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    z-index: 10;
}

/* Footer */
footer.site-footer {
    background-color: #111;
    color: #eee;
    text-align: center;
    padding: 12px 0;
    font-size: 0.9rem;
    margin-top: auto;
}
footer.site-footer a {
    color: #5c8df6;
    text-decoration: none;
    margin: 0 6px;
}
footer.site-footer a:hover {
    text-decoration: underline;
}

/* Responsive */
@media (max-width: 900px) {
    .card {
        width: 260px;
        height: 340px;
    }
    .container {
        margin-left: 10px;
    }
}
@media (max-width: 600px) {
    .card {
        width: 240px;
        height: 320px;
    }
    .container {
        margin-left: 0;
    }
}
</style>

<main>
    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="container">
        <!-- Admin -->
        <div class="card admin">
            <div class="card-inner">
                <div class="card-front">
                    <i>👩‍💼</i>
                    <h3>Admin</h3>
                    <p>System Management</p>
                </div>
                <div class="card-back">
                    <h3>Admin Login</h3>
                    <form method="POST">
                        <input type="hidden" name="role" value="admin">
                        <input type="email" name="email" placeholder="Email" required>
                        <input type="password" name="password" placeholder="Password" required>
                        <button type="submit">Login</button>
                        <button type="button" class="clear-btn" onclick="clearForm(this)">Clear</button>
                        <a href="#" onclick="openEmailReset(event, this)">Forgot Password?</a>
                    </form>
                </div>
            </div>
        </div>

        <!-- Center -->
        <div class="card center">
            <div class="card-inner">
                <div class="card-front">
                    <i>🏠</i>
                    <h3>Adoption Center</h3>
                    <p>Manage adoptions & pets</p>
                </div>
                <div class="card-back">
                    <h3>Center Login</h3>
                    <form method="POST">
                        <input type="hidden" name="role" value="center">
                        <input type="email" name="email" placeholder="Email" required>
                        <input type="password" name="password" placeholder="Password" required>
                        <button type="submit">Login</button>
                        <button type="button" class="clear-btn" onclick="clearForm(this)">Clear</button>
                        <a href="#" onclick="openEmailReset(event, this)">Forgot Password?</a>
                    </form>
                </div>
            </div>
        </div>

        <!-- Adopter -->
        <div class="card adopter">
            <div class="card-inner">
                <div class="card-front">
                    <i>🐾</i>
                    <h3>Adopter</h3>
                    <p>Find your perfect pet</p>
                </div>
                <div class="card-back">
                    <h3>Adopter Login</h3>
                    <form method="POST">
                        <input type="hidden" name="role" value="adopter">
                        <input type="email" name="email" placeholder="Email" required>
                        <input type="password" name="password" placeholder="Password" required>
                        <button type="submit">Login</button>
                        <button type="button" class="clear-btn" onclick="clearForm(this)">Clear</button>
                        <a href="#" onclick="openEmailReset(event, this)">Forgot Password?</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function openEmailReset(e, link) {
    e.preventDefault();
    const form = link.closest('form');
    const emailField = form.querySelector('input[type="email"]');
    const email = emailField.value || 'support@pawconnect.com';
    const subject = encodeURIComponent("Password Reset Request");
    const body = encodeURIComponent("Hi,\n\nI would like to reset my password for the account: " + email + "\n\nThanks.");
    window.location.href = `mailto:${email}?subject=${subject}&body=${body}`;
    form.reset();
}

function clearForm(button) {
    const form = button.closest('form');
    form.reset();
}
</script>

<?php include('includes/footer.php'); ?>
