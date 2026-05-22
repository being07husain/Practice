<?php
session_start();

$conn = mysqli_connect("localhost", "root", "password", "jee");

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $roll_number = trim($_POST['roll_number']);
    $password = trim($_POST['password']);

    // Prepared Statement
    $sql = "SELECT * FROM Registered WHERE Registeration_No=?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "s", $roll_number);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {

        $row = mysqli_fetch_assoc($result);

        // SECURE PASSWORD CHECK
        if (password_verify($password, $row['Password'])) {

            $_SESSION['roll_number'] = $row['Registeration_No'];

            header("Location: form.php");
            exit();

        } else {
            $error = "Invalid Password!";
        }

    } else {
        $error = "Roll Number Not Found!";
    }
}
?>

<html>
<head>
    <title>Login</title>
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

    overflow:hidden;

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

<h1>LOGIN TO YOUR ACCOUNT</h1>

<form method="post">

    <label for="roll_number">Roll Number:</label>

    <input type="text"
           id="roll_number"
           name="roll_number"
           required>

    <br><br>

    <label for="password">Password:</label>

    <input type="password"
           id="password"
           name="password"
           required>

    <br><br>

    <input type="submit" value="Login">

    <h3>
        Don't have an account?
        <a href="Register.php">Register here</a>
    </h3>

</form>

<?php
if (isset($error)) {
    echo "<h2 style='color:red;'>$error</h2>";
}
?>

</body>
</html>