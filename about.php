<?php
include('includes/header.php');
?>

<style>
/* ---------- RESET & BASE ---------- */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Poppins', sans-serif;
  background: linear-gradient(135deg, #0d1b2a, #1b3b6f, #f9fafb); /* Dark blue → Light blue → White */
  color: #1b2e35;
}

/* ---------- PAGE CONTAINER ---------- */
.about-section {
  padding: 80px 8%;
  text-align: center;
}

/* ---------- HEADING SECTION ---------- */
.about-section h1 {
  font-size: 2.5rem;
  font-weight: 700;
  color: #fff;
  margin-bottom: 15px;
  text-shadow: 0 2px 5px rgba(0,0,0,0.3);
}

.about-section p.intro {
  font-size: 1.1rem;
  color: #e0e0e0;
  max-width: 750px;
  margin: 0 auto 60px;
  line-height: 1.6;
  text-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

/* ---------- INFO CONTAINER ---------- */
.info-container {
  background: rgba(255, 255, 255, 0.95);
  border-radius: 15px;
  display: flex;
  flex-direction: column; /* Stack everything vertically */
  align-items: center;
  padding: 50px;
  box-shadow: 0 4px 25px rgba(0, 0, 0, 0.15);
}

/* ---------- TEXT SECTION ---------- */
.info-text {
  width: 100%;
  max-width: 800px;
  text-align: left;
  margin-bottom: 40px;
}

.info-text h2 {
  font-size: 1.8rem;
  font-weight: 700;
  color: #1b2e35;
  margin-bottom: 20px;
}

.info-text h2 span {
  color: #2ba84a;
}

.info-text h3 {
  font-size: 1.3rem;
  color: #333;
  margin-top: 25px;
  margin-bottom: 10px;
}

.info-text p, 
.info-text li {
  color: #444;
  font-size: 1rem;
  line-height: 1.7;
  margin-bottom: 10px;
}

.info-text ul {
  padding-left: 20px;
  list-style-type: disc;
}

/* ---------- IMAGE SECTION (STACKED) ---------- */
.info-images {
  width: 100%;
  display: flex;
  flex-direction: column; /* Stack images vertically */
  gap: 30px; /* space between images */
  align-items: center;
}

.info-images img {
  width: 100%;
  max-width: 600px; /* bigger image */
  border-radius: 15px;
  object-fit: cover;
  box-shadow: 0 6px 20px rgba(0,0,0,0.15);
  transition: transform 0.3s;
}

.info-images img:hover {
  transform: scale(1.03);
}

/* ---------- CONTACT LINK ---------- */
.info-text a {
  color: #2ba84a;
  font-weight: 600;
  text-decoration: none;
  border-bottom: 2px solid transparent;
  transition: 0.3s;
}
.info-text a:hover {
  border-color: #2ba84a;
}

/* ---------- RESPONSIVE DESIGN ---------- */
@media (max-width: 900px) {
  .info-text {
    text-align: center;
    padding: 0 10px;
    margin-bottom: 30px;
  }

  .info-images img {
    max-width: 90%;
  }
}
@media (max-width: 600px) {
  .info-images img {
    max-width: 100%;
  }
}
</style>

<section class="about-section">
  <h1>About PawConnect</h1>
  <p class="intro">
    Welcome to <strong>PawConnect</strong>, your friendly platform dedicated to connecting 
    loving adopters with pets in need of a home.
  </p>

  <div class="info-container">
    <div class="info-text">
      <h2>We’re here to get as many <span>pets connected</span> as possible.</h2>

      <h3>Our Mission</h3>
      <p>
        To reduce pet overpopulation and abandonment by providing a simple, accessible online 
        platform that brings shelters and adopters together.
      </p>

      <h3>Features</h3>
      <ul>
        <li>Browse pets by type, age, and location</li>
        <li>Submit and manage adoption requests</li>
        <li>Adoption center management dashboard</li>
        <li>Reports and analytics for admins</li>
        <li>Email notifications and updates</li>
        <li>User feedback and review system</li>
      </ul>

      <h3>Contact Us</h3>
      <p>
        Have questions or want to get involved? Visit our 
        <a href="contact.php">Contact Page</a> to reach out.
      </p>
    </div>

    <div class="info-images">
      <img src="uploads/pet.jpg" alt="Pet Image 1">
      <img src="uploads/pet1.jpg" alt="Pet Image 2">
    </div>
  </div>
</section>

<?php
include('includes/footer.php');
?>
