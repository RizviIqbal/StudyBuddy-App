<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Typing Test | StudyBuddy</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(to right, #4A00E0, #8E2DE2);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
        }

        .container {
            background: #fff;
            padding: 30px;
            border-radius: 18px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            max-width: 850px;
            width: 95%;
        }

        h2 {
            text-align: center;
            color: #4A00E0;
            margin-bottom: 15px;
        }

        #textToType {
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 20px;
            background: #f4f4f4;
            padding: 20px;
            border-radius: 12px;
            min-height: 100px;
        }

        #textToType span.correct {
            color: green;
        }

        #textToType span.incorrect {
            color: red;
        }

        textarea {
            width: 100%;
            height: 140px;
            font-size: 18px;
            padding: 14px;
            border-radius: 12px;
            border: 1px solid #ccc;
            resize: none;
            outline: none;
            box-sizing: border-box;
        }

        .info {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
            font-weight: 600;
        }

        .results {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }

        button {
            background: #4A00E0;
            color: white;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            display: block;
            margin: 20px auto 0;
            transition: background 0.3s;
        }

        button:hover {
            background: #5b00f5;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Typing Speed Test</h2>
    <div class="info">
        <div><strong>Time Left:</strong> <span id="timeLeft">60</span> sec</div>
        <div><strong>WPM:</strong> <span id="wpm">0</span></div>
        <div><strong>Accuracy:</strong> <span id="accuracy">100</span>%</div>
    </div>

    <div id="textToType"></div>

    <textarea id="userInput" placeholder="Start typing here..." oninput="checkTyping()" onfocus="startTimer()"></textarea>

    <button id="submitBtn" onclick="finishTest()" style="display:none;">Submit Result</button>
</div>

<script>
    const sampleTexts = [
        `An ever-growing number of complex and rigid rules plus hard-to-cope-with regulations are now being legislated from state to state.`,
        `In one study of average computer users, the average rate for transcription was 33 words per minute, and 19 words per minute for composition.`,
        `Typists often handle confidential documents. Treat sensitive information with the utmost care. Follow company policies regarding data security and privacy.`,
        `As CEO of a promising tech startup, Anya was determined to secure a $1 million investment to fuel the company's growth.`,
        `A late 20th century trend in typing, primarily used with devices with small keyboards (such as PDAs and Smartphones), is thumbing or thumb typing. This can be accomplished using one or both thumbs.`,
    ];

    const sampleText = sampleTexts[Math.floor(Math.random() * sampleTexts.length)];
    const textContainer = document.getElementById('textToType');
    const userInput = document.getElementById('userInput');
    const submitBtn = document.getElementById('submitBtn');
    const timeLeftDisplay = document.getElementById('timeLeft');

    let timerStarted = false, startTime, timeLeft = 60, intervalId;

    function renderText() {
        textContainer.innerHTML = '';
        for (let i = 0; i < sampleText.length; i++) {
            const span = document.createElement('span');
            span.textContent = sampleText[i];
            textContainer.appendChild(span);
        }
    }

    renderText();

    function startTimer() {
        if (!timerStarted) {
            timerStarted = true;
            startTime = new Date();
            intervalId = setInterval(() => {
                timeLeft--;
                timeLeftDisplay.textContent = timeLeft;
                if (timeLeft <= 0) {
                    clearInterval(intervalId);
                    finishTest();
                }
            }, 1000);
        }
    }

    function checkTyping() {
        const input = userInput.value;
        const spans = textContainer.querySelectorAll('span');
        let correct = 0;

        for (let i = 0; i < spans.length; i++) {
            if (i < input.length) {
                if (input[i] === sampleText[i]) {
                    spans[i].classList.add('correct');
                    spans[i].classList.remove('incorrect');
                    correct++;
                } else {
                    spans[i].classList.add('incorrect');
                    spans[i].classList.remove('correct');
                }
            } else {
                spans[i].classList.remove('correct', 'incorrect');
            }
        }

        const elapsed = (new Date() - startTime) / 1000 / 60;
        const wpm = Math.round((input.trim().split(/\s+/).length / elapsed) || 0);
        const accuracy = Math.round((correct / input.length) * 100) || 100;

        document.getElementById('wpm').textContent = wpm;
        document.getElementById('accuracy').textContent = accuracy;

        if (input.length >= sampleText.length && !userInput.disabled) {
            submitBtn.style.display = 'block';
        }
    }

    function finishTest() {
        clearInterval(intervalId);
        userInput.disabled = true;
        submitBtn.style.display = 'block';
        checkTyping();

        const wpm = document.getElementById('wpm').textContent;
        const accuracy = document.getElementById('accuracy').textContent;

        fetch('save_typing_score.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `wpm=${wpm}&accuracy=${accuracy}`
        })
        .then(res => res.text())
        .then(alert)
        .then(() => location.href = 'leaderboard.php');
    }
</script>

</body>
</html>
