<?php
session_start();

if (!isset($_SESSION['roll_number'])) {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect("localhost", "root", "password", "jee");

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

$roll_number = $_SESSION['roll_number'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_form'])) {

    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');

    $dob = trim($_POST['dob'] ?? '');
    $gender = trim($_POST['gender'] ?? '');

    $fathers_name = trim($_POST['fathers_name'] ?? '');
    $mothers_name = trim($_POST['mothers_name'] ?? '');

    $category = trim($_POST['category'] ?? '');
    $nationality = trim($_POST['nationality'] ?? '');

    $aadhar_number = trim($_POST['aadhar_number'] ?? '');
    $mobile_number = trim($_POST['mobile_number'] ?? '');

    $email = trim($_POST['email'] ?? '');
    $confirm_email = trim($_POST['confirm_email'] ?? '');

    $school_name_10 = trim($_POST['school_name_10'] ?? '');
    $board_10 = trim($_POST['board_10'] ?? '');
    $year_of_passing = trim($_POST['year_of_passing'] ?? '');
    $percentage = trim($_POST['percentage'] ?? '');

    $school_name_12 = trim($_POST['school_name_12'] ?? '');
    $board_12 = trim($_POST['board_12'] ?? '');
    $year_of_passing_12 = trim($_POST['year_of_passing_12'] ?? '');
    $percentage_12 = trim($_POST['percentage_12'] ?? '');

    $flat_number = trim($_POST['flat_number'] ?? '');
    $street = trim($_POST['street'] ?? '');
    $area = trim($_POST['area'] ?? '');
    $landmark = trim($_POST['landmark'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');

    if ($email !== $confirm_email) {
        $form_error = "Emails do not match!";
    }
    else {

        $upload_dir = "uploads/";

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $photo = '';
        $signature = '';
        $aadhar_card = '';
        $class_10_marksheet = '';
        $class_12_marksheet = '';

        $files = [
            'photo',
            'signature',
            'aadhar_card',
            'class_10_marksheet',
            'class_12_marksheet'
        ];

        $upload_success = true;

        foreach ($files as $file_key) {

            if (isset($_FILES[$file_key]) &&
                $_FILES[$file_key]['error'] == 0) {

                $filename = uniqid() . '_' .
                basename($_FILES[$file_key]['name']);

                $filepath = $upload_dir . $filename;

                if (move_uploaded_file(
                    $_FILES[$file_key]['tmp_name'],
                    $filepath
                )) {

                    $$file_key = $filepath;
                }
                else {
                    $upload_success = false;
                    break;
                }
            }
        }

        if ($upload_success) {

            mysqli_begin_transaction($conn);

            try {

                $sql_app = "INSERT INTO applications (
                first_name,
                middle_name,
                last_name,
                dob,
                gender,
                father_name,
                mother_name,
                category,
                nationality,
                aadhar_number,
                mobile_number,
                email,
                application_id   
                )

                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt_app = mysqli_prepare($conn, $sql_app);

                $gender_cap = ucfirst(strtolower($gender));
                $category_cap = ucfirst(strtolower($category));

                mysqli_stmt_bind_param(
                    $stmt_app,
                    "ssssssssssssi",
                    $first_name,
                    $middle_name,
                    $last_name,
                    $dob,
                    $gender_cap,
                    $fathers_name,
                    $mothers_name,
                    $category_cap,
                    $nationality,
                    $aadhar_number,
                    $mobile_number,
                    $email,
                    $roll_number
                );

                mysqli_stmt_execute($stmt_app);

                $application_id = mysqli_insert_id($conn);

                $sql_addr = "INSERT INTO address_details (
                application_id,
                house_number,
                street,
                area,
                landmark,
                district,
                city,
                state,
                pincode
                )

                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt_addr = mysqli_prepare($conn, $sql_addr);

                mysqli_stmt_bind_param(
                    $stmt_addr,
                    "issssssss",
                    $application_id,
                    $flat_number,
                    $street,
                    $area,
                    $landmark,
                    $district,
                    $city,
                    $state,
                    $pincode
                );

                mysqli_stmt_execute($stmt_addr);

                $sql_edu = "INSERT INTO education_details (
                application_id,
                school_name_10,
                board_10,
                passing_year_10,
                percentage_10,
                school_name_12,
                board_12,
                passing_year_12,
                percentage_12
                )

                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt_edu = mysqli_prepare($conn, $sql_edu);

                mysqli_stmt_bind_param(
                    $stmt_edu,
                    "issidssid",
                    $application_id,
                    $school_name_10,
                    $board_10,
                    $year_of_passing,
                    $percentage,
                    $school_name_12,
                    $board_12,
                    $year_of_passing_12,
                    $percentage_12
                );

                mysqli_stmt_execute($stmt_edu);

                $sql_doc = "INSERT INTO documents (
                application_id,
                photo_path,
                signature_path,
                aadhar_card_path,
                class_10_marksheet_path,
                class_12_marksheet_path
                )

                VALUES (?, ?, ?, ?, ?, ?)";

                $stmt_doc = mysqli_prepare($conn, $sql_doc);

                mysqli_stmt_bind_param(
                    $stmt_doc,
                    "isssss",
                    $application_id,
                    $photo,
                    $signature,
                    $aadhar_card,
                    $class_10_marksheet,
                    $class_12_marksheet
                );

                mysqli_stmt_execute($stmt_doc);

                mysqli_commit($conn);

                $form_success =
                "Application Submitted Successfully! Application ID: "
                . $application_id;

            }
            catch (Exception $e) {

                mysqli_rollback($conn);

                $form_error = $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<title>JEE Application Form</title>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:"Segoe UI",sans-serif;
}

body{
    background:linear-gradient(135deg,#0f172a,#1e3a8a,#2563eb);
    min-height:100vh;
    padding:20px;
    color:white;
}

h1{
    text-align:center;
    margin-bottom:25px;
    font-size:34px;
}

.progress-container{
    width:90%;
    height:12px;
    background:rgba(255,255,255,0.2);
    border-radius:20px;
    margin:auto;
    margin-bottom:30px;
}

.progress-bar{
    width:25%;
    height:100%;
    border-radius:20px;
    background:linear-gradient(90deg,#38bdf8,#0ea5e9);
    transition:0.5s;
}

.NAVBAR{
    width:90%;
    margin:auto;
    display:flex;
    justify-content:center;
    gap:15px;
    flex-wrap:wrap;
    margin-bottom:25px;
}

.section{
    padding:14px 25px;
    background:rgba(255,255,255,0.12);
    border-radius:12px;
    cursor:pointer;
    font-weight:600;
    transition:0.3s;
}

.section:hover{
    background:white;
    color:#1e3a8a;
}

.content{
    width:90%;
    margin:auto;
    background:rgba(255,255,255,0.12);
    backdrop-filter:blur(15px);
    border-radius:20px;
    padding:30px;
    display:none;
}

#personal{
    display:block;
}

.content h2{
    margin-bottom:25px;
    border-left:5px solid #38bdf8;
    padding-left:15px;
}

label{
    display:inline-block;
    width:190px;
    margin-top:15px;
    font-size:18px;
}

input,
select{
    width:320px;
    padding:12px;
    border:none;
    border-radius:10px;
    margin-bottom:18px;
    font-size:17px;
}

input:focus,
select:focus{
    outline:none;
    transform:scale(1.02);
}

button{
    margin-top:20px;
    padding:14px 30px;
    border:none;
    border-radius:10px;
    background:#38bdf8;
    color:white;
    cursor:pointer;
    font-size:17px;
    transition:0.3s;
}

button:hover{
    background:#0ea5e9;
}

.input-error{
    border:3px solid #ef4444 !important;
}

.input-success{
    border:3px solid #22c55e !important;
}

.warning-box{
    position:fixed;
    top:50%;
    left:50%;
    transform:translate(-50%,-50%);
    background:white;
    color:black;
    padding:25px 40px;
    border-radius:18px;
    font-size:20px;
    font-weight:600;
    z-index:9999;
    display:none;
}

.success-box{
    width:90%;
    margin:auto;
    margin-bottom:25px;
    padding:20px;
    border-radius:15px;
    background:rgba(34,197,94,0.2);
    border:2px solid #22c55e;
    text-align:center;
}

.error-box{
    width:90%;
    margin:auto;
    margin-bottom:25px;
    padding:20px;
    border-radius:15px;
    background:rgba(239,68,68,0.2);
    border:2px solid #ef4444;
    text-align:center;
}

@media(max-width:1200px){

    label{
        width:100%;
    }

    input,
    select{
        width:100%;
    }
}

</style>

</head>

<body>

<h1>
JOINT ENTRANCE EXAMINATION (2026-27)
</h1>

<div class="progress-container">
    <div class="progress-bar" id="progressBar"></div>
</div>

<?php
if(isset($form_success)){
    echo "<div class='success-box'>$form_success</div>";
}

if(isset($form_error)){
    echo "<div class='error-box'>$form_error</div>";
}
?>

<div class="NAVBAR">

<div class="section">Personal Details</div>
<div class="section">Educational Details</div>
<div class="section">Residential Details</div>
<div class="section">Upload Documents</div>

</div>

<form method="post" enctype="multipart/form-data">

<div id="personal" class="content">

<h2>Personal Details</h2>

<label>First Name:</label>
<input type="text" id="first_name" name="first_name" required>

<label>Middle Name:</label>
<input type="text" id="middle_name" name="middle_name">

<label>Last Name:</label>
<input type="text" id="last_name" name="last_name" required>

<br>

<label>Date of Birth:</label>
<input type="date" id="dob" name="dob" required>

<label>Gender:</label>
<select id="gender" name="gender" required>
<option value="">Select Gender</option>
<option value="male">Male</option>
<option value="female">Female</option>
<option value="other">Other</option>
</select>

<br>

<label>Father's Name:</label>
<input type="text" id="fathers_name" name="fathers_name" required>

<label>Mother's Name:</label>
<input type="text" id="mothers_name" name="mothers_name" required>

<br>

<label>Category:</label>
<select id="category" name="category" required>
<option value="">Select Category</option>
<option value="general">General</option>
<option value="obc">OBC</option>
<option value="sc">SC</option>
<option value="st">ST</option>
</select>

<label>Nationality:</label>
<input type="text" id="nationality" name="nationality" required>

<br>

<label>Aadhar Number:</label>
<input type="text" id="aadhar_number" name="aadhar_number" required>

<label>Mobile Number:</label>
<input type="text" id="mobile_number" name="mobile_number" required>

<br>

<label>Email:</label>
<input type="email" id="email" name="email" required>

<label>Confirm Email:</label>
<input type="email" id="confirm_email" name="confirm_email" required>

<br>

<button type="button" onclick="validatePersonal()">
NEXT
</button>

</div>

<div id="educational" class="content">

<h2>Educational Details</h2>

<label>Class X School:</label>
<input type="text" id="school_name_10" name="school_name_10" required>

<label>Board:</label>
<input type="text" id="board_10" name="board_10" required>

<br>

<label>Passing Year:</label>
<input type="number" id="year_of_passing" name="year_of_passing" required>

<label>Percentage:</label>
<input type="number" step="0.01" id="percentage" name="percentage" required>

<br>

<label>Class XII School:</label>
<input type="text" id="school_name_12" name="school_name_12" required>

<label>Board:</label>
<input type="text" id="board_12" name="board_12" required>

<br>

<label>Passing Year:</label>
<input type="number" id="year_of_passing_12" name="year_of_passing_12" required>

<label>Percentage:</label>
<input type="number" step="0.01" id="percentage_12" name="percentage_12" required>

<br>

<button type="button" onclick="validateEducation()">
NEXT
</button>

</div>

<div id="residential" class="content">

<h2>Residential Details</h2>

<label>House Number:</label>
<input type="text" id="flat_number" name="flat_number" required>

<label>Street:</label>
<input type="text" id="street" name="street" required>

<br>

<label>Area:</label>
<input type="text" id="area" name="area" required>

<label>Landmark:</label>
<input type="text" id="landmark" name="landmark">

<br>

<label>District:</label>
<input type="text" id="district" name="district" required>

<label>City:</label>
<input type="text" id="city" name="city" required>

<br>

<label>State:</label>
<input type="text" id="state" name="state" required>

<label>Pincode:</label>
<input type="text" id="pincode" name="pincode" required>

<br>

<button type="button" onclick="validateResidential()">
NEXT
</button>

</div>

<div id="documents" class="content">

<h2>Upload Documents</h2>

<label>Photograph:</label>
<input type="file" id="photo" name="photo" required>

<br>

<label>Signature:</label>
<input type="file" id="signature" name="signature" required>

<br>

<label>Aadhar Card:</label>
<input type="file" id="aadhar_card" name="aadhar_card" required>

<br>

<label>10th Marksheet:</label>
<input type="file" id="class_10_marksheet" name="class_10_marksheet" required>

<br>

<label>12th Marksheet:</label>
<input type="file" id="class_12_marksheet" name="class_12_marksheet" required>

<br>

<input type="hidden" name="submit_form" value="1">

<button type="submit">
SUBMIT APPLICATION
</button>

</div>

</form>

<div class="warning-box" id="warningBox"></div>

<script>

function showWarning(message){

    const box =
    document.getElementById("warningBox");

    box.innerHTML = message;

    box.style.display = "block";

    setTimeout(() => {
        box.style.display = "none";
    }, 2500);
}

function showSection(sectionId){

    const sections =
    document.querySelectorAll('.content');

    sections.forEach(section => {
        section.style.display = 'none';
    });

    document.getElementById(sectionId)
    .style.display = 'block';

    updateProgress(sectionId);
}

function updateProgress(sectionId){

    const bar =
    document.getElementById("progressBar");

    if(sectionId == "personal"){
        bar.style.width = "25%";
    }

    if(sectionId == "educational"){
        bar.style.width = "50%";
    }

    if(sectionId == "residential"){
        bar.style.width = "75%";
    }

    if(sectionId == "documents"){
        bar.style.width = "100%";
    }
}

function validatePersonal(){

    const fields = [

        "first_name",
        "last_name",
        "dob",
        "gender",
        "fathers_name",
        "mothers_name",
        "category",
        "nationality",
        "aadhar_number",
        "mobile_number",
        "email",
        "confirm_email"
    ];

    let valid = true;

    fields.forEach(id => {

        const field = document.getElementById(id);

        field.classList.remove("input-error");

        if(field.value.trim() == ""){

            field.classList.add("input-error");

            valid = false;
        }
        else{
            field.classList.add("input-success");
        }
    });

    const email =
    document.getElementById("email").value;

    const confirm =
    document.getElementById("confirm_email").value;

    if(email != confirm){

        showWarning("Emails do not match!");
        return;
    }

    const aadhar =
    document.getElementById("aadhar_number").value;

    if(!/^[0-9]{12}$/.test(aadhar)){

        showWarning(
        "Aadhar must contain 12 digits!"
        );

        return;
    }

    const mobile =
    document.getElementById("mobile_number").value;

    if(!/^[6-9][0-9]{9}$/.test(mobile)){

        showWarning(
        "Enter valid mobile number!"
        );

        return;
    }

    if(!valid){

        showWarning(
        "Please fill all required fields!"
        );

        return;
    }

    showSection('educational');
}

function validateEducation(){

    showSection('residential');
}

function validateResidential(){

    showSection('documents');
}

</script>

</body>
</html>
