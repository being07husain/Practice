<?php

$conn = mysqli_connect("localhost", "root", "password", "jee");

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($name) && !empty($email) && !empty($password)) {

        // CHECK EMAIL EXISTS

        $check_sql = "SELECT * FROM Registered WHERE EMAIL=?";

        $check_stmt = mysqli_prepare($conn, $check_sql);

        mysqli_stmt_bind_param($check_stmt, "s", $email);

        mysqli_stmt_execute($check_stmt);

        $result = mysqli_stmt_get_result($check_stmt);

        if (mysqli_num_rows($result) > 0) {

            $error = "Email already registered!";

        } else {

            // GENERATE UNIQUE ROLL NUMBER

            do {

                $roll_number = rand(100000, 999999);

                $roll_sql = "SELECT * FROM Registered WHERE Registeration_No=?";

                $roll_stmt = mysqli_prepare($conn, $roll_sql);

                mysqli_stmt_bind_param($roll_stmt, "s", $roll_number);

                mysqli_stmt_execute($roll_stmt);

                $roll_result = mysqli_stmt_get_result($roll_stmt);

            } while (mysqli_num_rows($roll_result) > 0);

            // HASH PASSWORD

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // INSERT USER

            $insert_sql = "INSERT INTO Registered
            (Registeration_No, NAME, EMAIL, PASSWORD)

            VALUES (?, ?, ?, ?)";

            $insert_stmt = mysqli_prepare($conn, $insert_sql);

            mysqli_stmt_bind_param(
                $insert_stmt,
                "isss",
                $roll_number,
                $name,
                $email,
                $hashed_password
            );

            if (mysqli_stmt_execute($insert_stmt)) {

                $success = "Registration Successful!";
                $generated_roll = $roll_number;

            } else {

                $error = "Registration Failed!";
            }
        }

    } else {

        $error = "Please fill all fields!";
    }
}
?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Register</title>
<style>
    /* GOOGLE FONT */

@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

/* RESET */

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

/* BODY */
body{
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    flex-direction:column;
    background:
    linear-gradient(
    135deg,
    #0f172a,
    #1e3a8a,
    #2563eb);
    position:relative;
}

/* ANIMATED BACKGROUND CIRCLES */

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
.success-box{

    margin-top:25px;

    width:450px;

    padding:25px;

    border-radius:20px;

    background:rgba(34,197,94,0.15);

    border:1px solid rgba(34,197,94,0.4);

    backdrop-filter:blur(10px);

    text-align:center;

    color:white;

    animation:fadeIn 0.5s ease;

    z-index:10;
}

.success-box h1{

    font-size:28px;

    margin-top:10px;

    margin-bottom:10px;

    color:#4ade80;
}

.success-box h2{

    margin-top:10px;

    font-size:24px;

    color:white;
}
/* MAIN HEADING */

h1{
    color:white;

    font-size:34px;

    margin-bottom:35px;

    text-align:center;

    text-transform:uppercase;

    letter-spacing:1px;

    text-shadow:0px 4px 10px rgba(0,0,0,0.4);

    z-index:10;
}

/* FORM CARD */

form{

    width:450px;

    padding:45px;

    border-radius:25px;

    background:rgba(255,255,255,0.12);

    backdrop-filter:blur(15px);

    -webkit-backdrop-filter:blur(15px);

    border:1px solid rgba(255,255,255,0.2);

    box-shadow:
    0 8px 32px rgba(0,0,0,0.35);

    z-index:10;

    animation:fadeIn 0.8s ease;
}

/* LABELS */

label{
    display:block;

    color:white;

    margin-bottom:10px;

    font-size:18px;

    font-weight:500;
}

/* INPUT FIELDS */

input[type="text"],
input[type="email"],
input[type="password"]{

    width:100%;

    padding:14px 16px;

    border:none;

    outline:none;

    border-radius:12px;

    background:rgba(255,255,255,0.92);

    color:#111827;

    font-size:16px;

    margin-bottom:25px;

    transition:0.3s;
}

/* INPUT FOCUS EFFECT */

input[type="text"]:focus,
input[type="email"]:focus,
input[type="password"]:focus{

    transform:scale(1.02);

    box-shadow:
    0 0 0 4px rgba(59,130,246,0.35),
    0 10px 20px rgba(0,0,0,0.25);
}

/* SUBMIT BUTTON */

input[type="submit"]{

    width:100%;

    padding:15px;

    border:none;

    border-radius:12px;

    background:linear-gradient(
    135deg,
    #38bdf8,
    #2563eb);

    color:white;

    font-size:18px;

    font-weight:600;

    cursor:pointer;

    transition:0.3s;

    box-shadow:
    0 6px 15px rgba(37,99,235,0.4);
}

/* BUTTON HOVER */

input[type="submit"]:hover{

    transform:translateY(-3px);

    background:linear-gradient(
    135deg,
    #0ea5e9,
    #1d4ed8);

    box-shadow:
    0 10px 25px rgba(37,99,235,0.55);
}

/* LINKS */

h3{
    margin-top:25px;

    text-align:center;

    color:#e5e7eb;

    font-size:15px;

    font-weight:400;
}

a{
    color:#38bdf8;

    text-decoration:none;

    font-weight:600;

    transition:0.3s;
}

a:hover{
    color:white;

    text-decoration:underline;
}

/* ERROR / SUCCESS MESSAGES */

h2{
    margin-top:20px;

    text-align:center;

    font-size:20px;

    z-index:10;
}

/* ANIMATION */

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

/* RESPONSIVE */

@media(max-width:600px){

    form{
        width:90%;
        padding:30px;
    }

    h1{
        font-size:24px;
        padding:0 10px;
    }
}
</style>
</head>

<body>

<h1>
REGISTER FOR JOINT ENTRANCE EXAMINATION (2026-27)
</h1>

<form method="post">

    <label for="name">Name:</label>

    <input type="text"
           id="name"
           name="name"
           required>

    <br><br>

    <label for="email">Email:</label>

    <input type="email"
           id="email"
           name="email"
           required>

    <br><br>

    <label for="password">Password:</label>

    <input type="password"
           id="password"
           name="password"
           required>

    <br><br>

    <input type="submit" value="Register">

    <h3>
        Already have an account?
        <a href="Login.php">Login here</a>
    </h3>

</form>

<?php

if (isset($error)) {

    echo "<h2 style='color:red;'>$error</h2>";
}
if (isset($success)) {

    echo "
    <div class='success-box'>

        <h2>$success</h2>

        <h1>YOUR ROLL NUMBER</h1>

        <h2>$generated_roll</h2>

    </div>
    ";
}
?>

</body>
</html>