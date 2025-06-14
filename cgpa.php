<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CGPA Predictor | StudyBuddy</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    :root {
      --bg: #f4f4f9;
      --card: #ffffff;
      --text: #222;
      --accent: #4A00E0;
    }
    body.dark {
      --bg: #0e0f13;
      --card: #1a1c24;
      --text: #f1f1f1;
    }
    body {
      background: var(--bg);
      color: var(--text);
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 700px;
      margin: 40px auto;
      background: var(--card);
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }
    h2 {
      margin-bottom: 20px;
      color: var(--accent);
      text-align: center;
    }
    label {
      display: block;
      margin: 15px 0 5px;
      font-weight: bold;
    }
    input {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 8px;
    }
    button {
      margin-top: 20px;
      width: 100%;
      background: var(--accent);
      color: #fff;
      padding: 12px;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
    }
    .result, .back-btn {
      margin-top: 25px;
      text-align: center;
    }
    .back-btn a {
      text-decoration: none;
      display: inline-block;
      margin-top: 15px;
      background: #444;
      color: white;
      padding: 10px 20px;
      border-radius: 8px;
    }
    .theme-toggle {
      position: fixed;
      top: 20px;
      right: 20px;
      background: #FFC107;
      color: #000;
      border: none;
      padding: 10px 15px;
      font-weight: bold;
      border-radius: 10px;
      cursor: pointer;
    }
  </style>
</head>
<body>
  <button class="theme-toggle" onclick="toggleTheme()">🌙 Toggle Theme</button>

  <div class="container">
    <h2>CGPA Predictor</h2>
    <form id="predictorForm">
      <label for="currentCgpa">Current CGPA:</label>
      <input type="number" id="currentCgpa" step="0.01" min="0" max="4.0" required>

      <label for="earnedCredits">Earned Credits:</label>
      <input type="number" id="earnedCredits" min="1" required>

      <label for="targetCgpa">Target CGPA:</label>
      <input type="number" id="targetCgpa" step="0.01" min="0" max="4.0" required>

      <label>Remaining Courses:</label>
      <input type="number" id="cr3" placeholder="3-credit courses" min="0" value="0">
      <input type="number" id="cr4" placeholder="4-credit courses" min="0" value="0">

      <button type="submit">Calculate Prediction</button>
    </form>

    <div class="result" id="result"></div>

    <div class="back-btn">
      <a href="dashboard.php">← Back to Dashboard</a>
    </div>
  </div>

<script>
  function toggleTheme() {
    document.body.classList.toggle('dark');
    localStorage.setItem('theme', document.body.classList.contains('dark') ? 'dark' : 'light');
  }

  window.addEventListener('DOMContentLoaded', () => {
    if (localStorage.getItem('theme') === 'dark') {
      document.body.classList.add('dark');
    }
  });

  document.getElementById('predictorForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const currentCgpa = parseFloat(document.getElementById('currentCgpa').value);
    const earnedCredits = parseFloat(document.getElementById('earnedCredits').value);
    const targetCgpa = parseFloat(document.getElementById('targetCgpa').value);


    const cr3 = parseInt(document.getElementById('cr3').value || 0);
    const cr4 = parseInt(document.getElementById('cr4').value || 0);

    const remainingCredits = cr3*3 + cr4*4;
    const totalCredits = earnedCredits + remainingCredits;

    const totalRequiredPoints = targetCgpa * totalCredits;
    const earnedPoints = currentCgpa * earnedCredits;
    const pointsNeeded = totalRequiredPoints - earnedPoints;

    let avgGpaNeeded = pointsNeeded / remainingCredits;
    avgGpaNeeded = Math.max(0, Math.min(4, avgGpaNeeded)); // clamp

    let result = `<h3>📊 Result</h3>`;
    result += `<p>You need an <strong>average GPA of ${avgGpaNeeded.toFixed(2)}</strong> in your remaining courses to reach your target CGPA of ${targetCgpa}.</p>`;

    if (avgGpaNeeded > 4) {
      result += `<p style="color:red;"><strong>Warning:</strong> Your goal is not achievable with regular grading scale.</p>`;
    } else {
      result += `<p><strong>Suggested Minimum GPA for Courses:</strong></p><ul>`;
      if (cr3 > 0) result += `<li>3-credit: ${avgGpaNeeded.toFixed(2)}</li>`;
      if (cr4 > 0) result += `<li>4-credit: ${avgGpaNeeded.toFixed(2)}</li>`;
      result += `</ul>`;
    }

    document.getElementById('result').innerHTML = result;
  });
</script>
</body>
</html>
