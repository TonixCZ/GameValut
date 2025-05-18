<?php

session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>About Us | GameVault</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="styles/styles.css" />
  <link rel="stylesheet" href="styles/header.css" />
  <link rel="stylesheet" href="styles/footer.css" />
  <link rel="stylesheet" href="styles/about.css" />
</head>
<body>
<?php include 'layout/header.php'; ?>

<div class="container">
  <div class="about-hero">
    <h1 class="mb-3">About GameVault</h1>
    <p class="lead mb-0">
      We are a young, passionate team of gamers who once spent countless hours searching for free and quality games. 
      Our dream was to build a place where everyone can discover, review, and share the best gaming experiences—without barriers.
    </p>
  </div>

  <div class="about-section mb-4">
    <h2>Our Story</h2>
    <p>
      GameVault was founded by <strong>Tonix</strong> (that’s me!)—a lifelong gamer and dreamer who always wanted to create a true home for players. 
      As a kid, I spent hours searching for new adventures, hidden gems, and (let’s be honest) free games that would run on my old PC. 
      I know how hard it can be to find honest reviews, real player experiences, and a friendly community that welcomes both casual and hardcore gamers.
    </p>
    <p>
      That’s why I started GameVault: a platform for everyone who loves games, whether you’re a speedrunner, a story lover, or just looking for something fun to play with friends. 
      Here, you can browse, rate, and review games, share your tips, and connect with other enthusiasts. My mission is to build the biggest and most welcoming gaming community—by gamers, for gamers.
    </p>
  </div>

  <div class="about-section mb-4">
    <h2>Our Team</h2>
    <div class="about-team" style="flex-direction: column; align-items: center;">
      <div class="team-card team-leader mb-4" style="max-width:340px;">
        <h5>Tonix</h5>
        <p>CEO, Founder, Developer & Visionary<br>
        RPG lover, indie hunter, and the heart behind GameVault.</p>
      </div>
      <div class="d-flex flex-wrap justify-content-center gap-4">
        <div class="team-card">
          <h5>Mamba</h5>
          <p>Community & Content<br>
          Always searching for new multiplayer experiences and helping shape our community spirit.</p>
        </div>
        <div class="team-card">
          <h5>Wesemir</h5>
          <p>Support & Testing<br>
          Strategy games enthusiast, always ready to help and test new features.</p>
        </div>
        <div class="team-card">
          <h5>TenCerney</h5>
          <p>Design & UX<br>
          Creative mind, pixel art enthusiast, and the one who makes GameVault look and feel great.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="about-section mb-4">
    <h2>Community Rules & Guidelines</h2>
    <ul class="rules-list">
      <li>
        <strong>Respect others:</strong> Treat all members with kindness. No hate speech, harassment, or discrimination will be tolerated.
      </li>
      <li>
        <strong>Honest reviews only:</strong> Share your real experience with the game. Do not use reviews for advertising, spam, or personal attacks.
      </li>
      <li>
        <strong>No spoilers in titles:</strong> Please avoid posting game spoilers in review titles or summaries.
      </li>
      <li>
        <strong>Stay on topic:</strong> Reviews and comments should relate to the game and your experience. Off-topic or irrelevant content may be removed.
      </li>
      <li>
        <strong>Protect your privacy:</strong> Do not share personal information (yours or others') in reviews or comments.
      </li>
      <li>
        <strong>Report abuse:</strong> If you see content that violates these rules, please report it to our team.
      </li>
      <li>
        <strong>Legal notice:</strong> GameVault is not responsible for the accuracy of user reviews. All trademarks and game names are property of their respective owners.
      </li>
    </ul>
  </div>

  <div class="about-section mb-4">
    <h2>Join Us!</h2>
    <p>
      We are always looking for new members and passionate gamers to join our community. 
      Sign up, share your reviews, and help us build the best place for game discovery on the web!
    </p>
    <div class="text-center mt-3">
      <a href="authentication.php" class="btn btn-lg btn-primary">Join GameVault</a>
    </div>
  </div>
</div>

<?php include 'layout/footer.php'; ?>
</body>
</html>