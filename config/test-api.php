<?php
?>
<!DOCTYPE html>
<html>
<head>
    <title>API Test</title>
</head>
<body>
    <h2>Test Registration API</h2>
    
    <input type="text" id="name" placeholder="Name" value="Test User"><br><br>
    <input type="email" id="email" placeholder="Email" value="test@example.com"><br><br>
    <input type="password" id="password" placeholder="Password" value="123456"><br><br>
    
    <button onclick="register()">Register</button>
    <button onclick="login()">Login</button>
    
    <h3>Response:</h3>
    <pre id="result" style="background:#f0f0f0; padding:10px;"></pre>
    
    <script>
        async function register() {
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            const response = await fetch('/face-attendance-system/backend/api/auth/register', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({name, email, password})
            });
            
            const data = await response.json();
            document.getElementById('result').innerText = JSON.stringify(data, null, 2);
        }
        
        async function login() {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            const response = await fetch('/face-attendance-system/backend/api/auth/login', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({email, password})
            });
            
            const data = await response.json();
            document.getElementById('result').innerText = JSON.stringify(data, null, 2);
        }
    </script>
</body>
</html>