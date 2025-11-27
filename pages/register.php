<?php
require(__DIR__ . '/../includes/header.php');
if (isset($_SESSION['USER_LOGIN'])) {
  echo "<script>window.top.location='index.php';</script>";
  exit;
}
?>
<?php
$msg = $nameErr = $emailErr = $mobileErr = '';

if (isset($_POST['submit'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($name)) {
        $nameErr = "Please enter a name";
    } elseif (!preg_match("/^[a-zA-Z-' ]*$/", $name)) {
        $nameErr = "Only letters and white space allowed";
    } elseif (empty($email)) {
        $emailErr = "Please enter email address";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailErr = "Please enter valid email address";
    } elseif (empty($password)) {
        $msg = "Please enter a password";
    } else {
        // Escape for SQL
        $name = mysqli_real_escape_string($con, $name);
        $email = mysqli_real_escape_string($con, $email);
        $mobile = mysqli_real_escape_string($con, $mobile);
        
        // Check if email exists
        $check = mysqli_query($con, "SELECT id FROM users WHERE email='$email'");
        if (mysqli_num_rows($check) > 0) {
            $msg = "Email already exists. Please login";
        } else {
            // Register user
            $passwordHash = md5($password);
            date_default_timezone_set('Asia/Kolkata');
            $doj = date('Y-m-d H:i:s');
            
            $sql = "INSERT INTO users(name, email, mobile, password, doj) 
                    VALUES ('$name', '$email', '$mobile', '$passwordHash', '$doj')";
            if (mysqli_query($con, $sql)) {
                header('Location: SignIn.php');
                exit;
            } else {
                $msg = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<script>
document.title = "Register | Book Rental";
</script>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-12 col-xl-11">
            <div class="card-body p-md-5">
                <div class="row justify-content-center align-content-center">
                    <div class="col-md-10 col-lg-6 col-xl-5 order-2 order-lg-1">
                        <div class="d-flex justify-content-center mb-3 mb-lg-4">
                            <h2>Registration</h2>
                        </div>
                        <form class="mx-1 mx-md-4" method="post">
                            <div class="d-flex align-items-center mb-4">
                                <i class="fas fa-user fa-lg me-3 fa-fw"></i>
                                <div class="form-floating flex-fill">
                                    <input type="text" class="form-control" id="name" name="name" placeholder="name1234"
                                        required />
                                    <label for="name">Name</label>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-4">
                                <i class="fas fa-envelope fa-lg me-3 fa-fw"></i>
                                <div class="form-floating flex-fill">
                                    <input type="email" class="form-control" id="email" name="email"
                                        placeholder="name@example.com" required />
                                    <label for="email">Email address</label>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-4">
                                <i class="fas fa-envelope fa-lg me-3 fa-fw"></i>
                                <div class="form-floating flex-fill">
                                    <input type="number" min="1111111111" max="9999999999" class="form-control"
                                        id="mobile" name="mobile" placeholder="number" required />
                                    <label for="mobile">Mobile Number(Without +91)</label>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-4">
                                <i class="fas fa-key fa-lg me-3 fa-fw"></i>
                                <div class="form-floating flex-fill">
                                    <input type="password" class="form-control" id="password" name="password"
                                        placeholder="Password" required />
                                    <label for="password">Password</label>
                                </div>
                            </div>
                            <div id="error" class="text-center mb-3">
                                <?php
                echo $msg . "\n";
                echo $nameErr . "\n";
                echo $emailErr . "\n";
                echo $mobileErr . "\n";
                ?>
                            </div>
                            <div class="d-flex justify-content-center mb-3 mb-lg-4">
                                <button type="submit" name="submit" id="submit" class="btn btn-primary">
                                    Register
                                </button>
                            </div>
                            <div style="text-align: center; margin-top: 30px">
                                <a href="SignIn.php" class="text-decoration-none text-black">
                                    Already have an account?
                                    <span style="color: rgb(138, 110, 253)">Login</span></a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--------------------------------------------------DARK MODE BUTTON----------------------------------------------------------->
<div id="dark-btn">
    <button onclick="DarkMode()" id="dark-btn" title="Toggle Light/Dark Mode">
        <span><i class="fas fa-adjust fa-lg text-white"></i></span>
    </button>
    <script>
    //Dark Mode
    function DarkMode() {
        let element = document.body;
        element.classList.toggle("dark-mode");
    }
    </script>
</div>