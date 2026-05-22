<?php
session_start();

// SECURITY CHECK: Verify user is logged in via admit card login page
if(!isset($_SESSION['roll_number']) || !isset($_SESSION['admit_card_login'])){
    header("Location: admitcard_login.php");
    exit();
}

// Session timeout check (30 minutes)
if(isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 1800)){
    session_destroy();
    header("Location: admitcard_login.php?timeout=1");
    exit();
}

// Update last activity time
$_SESSION['login_time'] = time();

$conn = mysqli_connect("localhost","root","password","jee");

if(!$conn){
    die("Connection Failed: ".mysqli_connect_error());
}

$roll_number = $_SESSION['roll_number'];

$sql = "
SELECT
    r.Registeration_No,
    r.Name as reg_name,
    r.Email as reg_email,

    app.application_id,
    app.first_name,
    app.middle_name,
    app.last_name,
    app.dob,
    app.gender,
    app.father_name,
    app.mother_name,
    app.category,
    app.mobile_number,
    app.email as app_email,

    addr.house_number,
    addr.street,
    addr.area,
    addr.landmark,
    addr.district,
    addr.city,
    addr.state,
    addr.pincode,

    edu.school_name_10,
    edu.board_10,
    edu.percentage_10,
    edu.school_name_12,
    edu.board_12,
    edu.percentage_12,

    doc.photo_path,
    doc.signature_path,

    ac.exam_center,
    ac.exam_date,
    ac.exam_time,
    ac.reporting_time

FROM Registered r
LEFT JOIN applications app ON app.application_id = r.Registeration_No
LEFT JOIN address_details addr ON app.application_id = addr.application_id
LEFT JOIN education_details edu ON app.application_id = edu.application_id
LEFT JOIN documents doc ON app.application_id = doc.application_id
LEFT JOIN admit_cards ac ON ac.application_id = app.application_id

WHERE r.Registeration_No = ?
";

$stmt = mysqli_prepare($conn, $sql);

if(!$stmt){
    die("Query Preparation Error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $roll_number);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result) == 0){
    echo "<div style='width:100%; padding:20px; text-align:center; background:#fee2e2; color:#dc2626; border-radius:12px; margin-top:30px;'>";
    echo "<h2>No Admit Card Found!</h2>";
    echo "<p>Please check your Roll Number and try again.</p>";
    echo "<a href='admitcard_login.php' style='color:#dc2626; text-decoration:underline;'>Back to Login</a>";
    echo "</div>";
    exit();
}

$data = mysqli_fetch_assoc($result);

if(!$data['application_id']){
    echo "<div style='width:100%; padding:20px; text-align:center; background:#fee2e2; color:#dc2626; border-radius:12px; margin-top:30px;'>";
    echo "<h2>Application Not Complete!</h2>";
    echo "<p>Your application is not yet submitted. Please complete your application first.</p>";
    echo "<a href='form.php' style='color:#dc2626; text-decoration:underline;'>Go to Application Form</a>";
    echo "</div>";
    exit();
}

if(!$data['photo_path']){
    echo "<div style='width:100%; padding:20px; text-align:center; background:#fee2e2; color:#dc2626; border-radius:12px; margin-top:30px;'>";
    echo "<h2>Documents Not Uploaded!</h2>";
    echo "<p>Please upload all required documents to generate your admit card.</p>";
    echo "<a href='form.php' style='color:#dc2626; text-decoration:underline;'>Upload Documents</a>";
    echo "</div>";
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>

<title>Admit Card</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:"Segoe UI",sans-serif;
}

body{
    background:#dbeafe;
    padding:30px;
}

.admit-card{
    width:1000px;
    margin:auto;
    background:white;
    border:8px solid #1e3a8a;
    padding:35px;
    box-shadow:0 10px 35px rgba(0,0,0,0.3);
}

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    border-bottom:4px solid #1e3a8a;
    padding-bottom:20px;
}

.logo{
    width:100px;
}

.heading{
    text-align:center;
    flex:1;
}

.heading h1{
    color:#1e3a8a;
    font-size:36px;
    margin-bottom:10px;
}

.heading h2{
    color:#374151;
    font-size:22px;
}

.photo-box{
    width:150px;
    text-align:center;
}

.photo-box img{
    width:130px;
    height:150px;
    border:3px solid #1e3a8a;
    object-fit:cover;
}

.section{
    margin-top:30px;
}

.section-title{
    background:#1e3a8a;
    color:white;
    padding:12px 20px;
    font-size:22px;
    border-radius:8px;
    margin-bottom:20px;
}

table{
    width:100%;
    border-collapse:collapse;
}

table td{
    border:2px solid #cbd5e1;
    padding:14px;
    font-size:18px;
}

.label{
    width:30%;
    background:#eff6ff;
    font-weight:700;
}

.exam-box{
    margin-top:25px;
    background:#f8fafc;
    padding:20px;
    border-radius:12px;
    border:2px dashed #1e3a8a;
}

.exam-box h3{
    margin-bottom:15px;
    color:#1e3a8a;
}

.signature-area{
    display:flex;
    justify-content:space-between;
    margin-top:50px;
}

.sign-box{
    text-align:center;
}

.sign-box img{
    width:160px;
    height:70px;
    object-fit:contain;
}

.footer{
    margin-top:40px;
    text-align:center;
    color:#374151;
    font-size:16px;
}

.print-btn{
    display:block;
    width:250px;
    margin:30px auto;
    padding:15px;
    background:#2563eb;
    color:white;
    border:none;
    border-radius:12px;
    font-size:20px;
    cursor:pointer;
}

.print-btn:hover{
    background:#1d4ed8;
}

@media print{

    .print-btn{
        display:none;
    }

    body{
        background:white;
    }
}

</style>

</head>
<body>

<div class="admit-card">

<div class="header">

<img src="https://upload.wikimedia.org/wikipedia/commons/8/8d/Emblem_of_India.svg"
class="logo">

<div class="heading">

<h1>
JOINT ENTRANCE EXAMINATION
</h1>

<h2>
ADMIT CARD 2026-27
</h2>

</div>

<div class="photo-box">

<img src="<?php echo htmlspecialchars($data['photo_path']); ?>" alt="Candidate Photo">

</div>

</div>


<div class="section">

<div class="section-title">
Candidate Details
</div>

<table>

<tr>
<td class="label">Candidate Name</td>
<td>
<?php
echo htmlspecialchars(trim($data['first_name'] . ' ' . ($data['middle_name'] ?? '') . ' ' . $data['last_name']));
?>
</td>
</tr>

<tr>
<td class="label">Roll Number</td>
<td><?php echo htmlspecialchars($data['Registeration_No']); ?></td>
</tr>

<tr>
<td class="label">Date of Birth</td>
<td><?php echo htmlspecialchars($data['dob']); ?></td>
</tr>

<tr>
<td class="label">Gender</td>
<td><?php echo htmlspecialchars($data['gender']); ?></td>
</tr>

<tr>
<td class="label">Category</td>
<td><?php echo htmlspecialchars($data['category']); ?></td>
</tr>

<tr>
<td class="label">Father's Name</td>
<td><?php echo htmlspecialchars($data['father_name']); ?></td>
</tr>

<tr>
<td class="label">Mother's Name</td>
<td><?php echo htmlspecialchars($data['mother_name']); ?></td>
</tr>

<tr>
<td class="label">Mobile Number</td>
<td><?php echo htmlspecialchars($data['mobile_number']); ?></td>
</tr>

<tr>
<td class="label">Email</td>
<td><?php echo htmlspecialchars($data['app_email']); ?></td>
</tr>

<tr>
<td class="label">Address</td>
<td>
<?php
echo htmlspecialchars(
    $data['house_number'] . ', ' .
    $data['street'] . ', ' .
    $data['area'] . ', ' .
    $data['city'] . ', ' .
    $data['state'] . ' - ' .
    $data['pincode']
);

?>
</td>
</tr>

</table>

</div>


<div class="section">

<div class="section-title">
Examination Details
</div>

<div class="exam-box">

<h3>Examination Center</h3>

<?php
if($data['exam_center'] && $data['exam_date']){
    echo "<p><b>Center Name:</b> " . htmlspecialchars($data['exam_center']) . "</p>";
    echo "<p><b>Exam Date:</b> " . htmlspecialchars($data['exam_date']) . "</p>";
    echo "<p><b>Reporting Time:</b> " . htmlspecialchars($data['reporting_time']) . "</p>";
    echo "<p><b>Exam Timing:</b> " . htmlspecialchars($data['exam_time']) . "</p>";
} else {
    echo "<p><b>Center Name:</b> Delhi Public Examination Center</p>";
    echo "<p><b>Center Code:</b> DEL-1025</p>";
    echo "<p><b>Exam Date:</b> 15 January 2027</p>";
    echo "<p><b>Reporting Time:</b> 7:00 AM</p>";
    echo "<p><b>Gate Closing Time:</b> 8:30 AM</p>";
    echo "<p><b>Exam Timing:</b> 9:00 AM to 12:00 PM</p>";
}
?>

</div>

</div>


<div class="section">

<div class="section-title">
Educational Details
</div>

<table>

<tr>
<td class="label">10th School</td>
<td><?php echo htmlspecialchars($data['school_name_10'] ?? 'N/A'); ?></td>
</tr>

<tr>
<td class="label">10th Board</td>
<td><?php echo htmlspecialchars($data['board_10'] ?? 'N/A'); ?></td>
</tr>

<tr>
<td class="label">10th Percentage</td>
<td><?php echo htmlspecialchars(($data['percentage_10'] ?? 'N/A') . '%'); ?></td>
</tr>

<tr>
<td class="label">12th School</td>
<td><?php echo htmlspecialchars($data['school_name_12'] ?? 'N/A'); ?></td>
</tr>

<tr>
<td class="label">12th Board</td>
<td><?php echo htmlspecialchars($data['board_12'] ?? 'N/A'); ?></td>
</tr>

<tr>
<td class="label">12th Percentage</td>
<td><?php echo htmlspecialchars(($data['percentage_12'] ?? 'N/A') . '%'); ?></td>
</tr>

</table>

</div>


<div class="signature-area">

<div class="sign-box">

<img src="<?php echo htmlspecialchars($data['signature_path']); ?>" alt="Candidate Signature">

<p>Candidate Signature</p>

</div>

<div class="sign-box">

<img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5f/Signature_of_Narendra_Modi.svg/2560px-Signature_of_Narendra_Modi.svg.png">

<p>Authorized Signature</p>

</div>

</div>


<div class="footer">

<p>
Carry this Admit Card along with valid ID proof.
</p>

<p>
Electronic gadgets are strictly prohibited inside the examination hall.
</p>

</div>

<button class="print-btn" onclick="window.print()">
PRINT ADMIT CARD
</button>

</div>

</body>
</html>