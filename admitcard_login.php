<?php
session_start();

$error = '';

// If already logged in and accessing login page, redirect to admit card
if(isset($_SESSION['roll_number']) && isset($_SESSION['admit_card_login'])){
    header("Location: admitcard.php");
    exit();
}

$conn = mysqli_connect("localhost","root","password","jee");

if(!$conn){
    die("Connection Failed: ".mysqli_connect_error());
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $roll_number = trim($_POST['roll_number'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validate input
    if(empty($roll_number) || empty($password)){
        $error = "Please fill all fields!";
    } else {
        // Prepared Statement for security
        $sql = "SELECT * FROM Registered WHERE Registeration_No=?";

        $stmt = mysqli_prepare($conn,$sql);

        if(!$stmt){
            $error = "Database error. Please try again.";
        } else {
            mysqli_stmt_bind_param($stmt,"s",$roll_number);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if(mysqli_num_rows($result) > 0){
                $row = mysqli_fetch_assoc($result);

                // Secure password verification
                if(password_verify($password, $row['Password'])){
                    // Login successful - create session
                    $_SESSION['roll_number'] = $row['Registeration_No'];
                    $_SESSION['email'] = $row['Email'];
                    $_SESSION['admit_card_login'] = true;
                    
                    header("Location: admitcard.php");
                    exit();
                } else {
                    $error = "Invalid Password!";
                }
            } else {
                $error = "Roll Number Not Found!";
            }
        }
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Download Admit Card</title>

    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:"Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        body{
            min-height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
            background:linear-gradient(135deg,#0f172a,#1e3a8a,#2563eb);
            overflow:hidden;
        }

        body::before{
            content:"";
            position:absolute;
            width:450px;
            height:450px;
            background:rgba(255,255,255,0.08);
            border-radius:50%;
            top:-120px;
            left:-120px;
            filter:blur(10px);
        }

        body::after{
            content:"";
            position:absolute;
            width:350px;
            height:350px;
            background:rgba(255,255,255,0.06);
            border-radius:50%;
            bottom:-100px;
            right:-100px;
            filter:blur(10px);
        }

        .login-container{
            width:420px;
            padding:45px;
            border-radius:25px;
            background:rgba(255,255,255,0.12);
            backdrop-filter:blur(15px);
            -webkit-backdrop-filter:blur(15px);
            color:white;
            box-shadow:0 8px 32px rgba(0,0,0,0.35);
            animation:fadeIn 0.8s ease;
            position:relative;
            z-index:10;
        }

        h1{
            text-align:center;
            margin-bottom:10px;
            font-size:28px;
            text-transform:uppercase;
            letter-spacing:2px;
            text-shadow:0px 2px 10px rgba(0,0,0,0.3);
        }

        .subtitle{
            text-align:center;
            margin-bottom:30px;
            font-size:14px;
            color:#e0f2fe;
            letter-spacing:1px;
        }

        .form-group{
            margin-bottom:25px;
        }

        label{
            display:block;
            margin-bottom:10px;
            font-size:16px;
            font-weight:500;
            color:white;
        }

        input{
            width:100%;
            padding:14px 16px;
            border:none;
            outline:none;
            border-radius:12px;
            font-size:16px;
            background:rgba(255,255,255,0.92);
            color:#111827;
            transition:0.3s;
        }

        input:focus{
            transform:scale(1.02);
            box-shadow:0 0 0 4px rgba(59,130,246,0.35), 0 10px 20px rgba(0,0,0,0.25);
        }

        button{
            width:100%;
            padding:15px;
            border:none;
            border-radius:12px;
            background:linear-gradient(135deg,#38bdf8,#2563eb);
            color:white;
            font-size:18px;
            font-weight:600;
            cursor:pointer;
            transition:0.3s;
            box-shadow:0 6px 15px rgba(37,99,235,0.4);
            text-transform:uppercase;
            letter-spacing:1px;
        }

        button:hover{
            transform:translateY(-3px);
            background:linear-gradient(135deg,#0ea5e9,#1d4ed8);
            box-shadow:0 10px 25px rgba(37,99,235,0.55);
        }

        button:active{
            transform:translateY(-1px);
        }

        .error{
            margin-top:20px;
            padding:15px;
            background:rgba(220,38,38,0.15);
            border:1px solid rgba(220,38,38,0.4);
            border-radius:12px;
            color:#fca5a5;
            font-weight:bold;
            text-align:center;
            animation:slideDown 0.5s ease;
        }

        .info-box{
            margin-top:25px;
            padding:15px;
            background:rgba(59,130,246,0.15);
            border:1px solid rgba(59,130,246,0.3);
            border-radius:12px;
            color:#bfdbfe;
            font-size:14px;
            text-align:center;
            line-height:1.6;
        }

        @keyframes fadeIn{
            from{
                opacity:0;
                transform:translateY(30px);
            }
            to{
                opacity:1;
                transform:translateY(0);
            }
        }

        @keyframes slideDown{
            from{
                opacity:0;
                transform:translateY(-10px);
            }
            to{
                opacity:1;
                transform:translateY(0);
            }
        }

        @media(max-width:600px){
            .login-container{
                width:90%;
                margin:20px;
                padding:30px;
            }

            h1{
                font-size:24px;
            }
        }
    </style>

</head>
<body>

    <div class="login-container">

        <h1>ADMIT CARD LOGIN</h1>
        <p class="subtitle">Download Your Admit Card Here</p>

        <form method="POST" action="">

            <div class="form-group">
                <label for="roll_number">Roll Number:</label>
                <input 
                    type="text" 
                    id="roll_number" 
                    name="roll_number" 
                    placeholder="Enter your roll number"
                    required 
                    autocomplete="off"
                    maxlength="20"
                >
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Enter your password"
                    required 
                    autocomplete="off"
                >
            </div>

            <button type="submit">LOGIN & DOWNLOAD</button>

        </form>

        <?php
        if(!empty($error)){
            echo "<div class='error'>".htmlspecialchars($error)."</div>";
        }
        ?>

        <div class="info-box">
            <strong>⚠️ Secure Login Required</strong><br>
            Please use your registered roll number and password to access your admit card.
        </div>

    </div>

</body>
</html>
