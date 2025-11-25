<?php
require('header.php');

// ============================================================================
// CUSTOMER REGISTRATION - CLEAN CODE GIẢI THÍCH TẠI SAU
// ============================================================================

// Giải thích: Redirect nếu user đã đăng nhập
// NGHIỆP VỤ: Ngăn user truy cập register page khi đã có account
if (isset($_SESSION['USER_LOGIN'])) {
    echo "<script>window.top.location='index.php';</script>";
    exit;
}

// Initialize variables cho validation
$msg = '';
$nameErr = $emailErr = $mobileErr = $passwordErr = "";
$nameTemp = $emailTemp = $mobileTemp = $passwordTemp = "";

// Xử lý registration form submission
if (isset($_POST['submit'])) {
    
    // ============================================================================
    // STEP 1: VALIDATE NAME
    // ============================================================================
    // Giải thích: Kiểm tra tên user có hợp lệ không
    // NGHIỆP VỤ: Đảm bảo tên chỉ chứa chữ cái và whitespace
    if (empty($_POST["name"])) {
        $nameErr = "Please enter a name";
    } else {
        $nameTemp = getSafeValue($con, $_POST['name']);
        if (preg_match("/^[a-zA-Z-' ]*$/", $nameTemp)) {
            $name = getSafeValue($con, $_POST['name']);
            
            // ============================================================================
            // STEP 2: VALIDATE EMAIL
            // ============================================================================
            // Giải thích: Kiểm tra email format và uniqueness
            // NGHIỆP VỤ: Đảm bảo email valid và chưa được đăng ký
            if (empty($_POST["email"])) {
                $emailErr = "Please enter Email address";
            } else {
                $emailTemp = getSafeValue($con, $_POST['email']);
                if (filter_var($emailTemp, FILTER_VALIDATE_EMAIL)) {
                    $email = getSafeValue($con, $_POST['email']);
                    
                    // ============================================================================
                    // STEP 3: VALIDATE MOBILE (Currently Disabled)
                    // ============================================================================
                    // Giải thích: Mobile validation bị comment-out tạm thời
                    // LƯU Ý: Trong production nên enable mobile validation
                    $mobile = getSafeValue($con, $_POST['mobile']);
                    // Mobile validation code bị comment-out
                    
                    // ============================================================================
                    // STEP 4: VALIDATE PASSWORD
                    // ============================================================================
                    // Giải thích: Kiểm tra password có được nhập không
                    // NGHIỆP VỤ: Password là required field
                    if (empty($_POST["password"])) {
                        $passwordErr = "Please enter a password";
                    } else {
                        $passwordTemp = getSafeValue($con, $_POST['password']);
                    }
                    
                    // ============================================================================
                    // STEP 5: PROCESS REGISTRATION
                    // ============================================================================
                    // Giải thích: Nếu tất cả validation pass -> tạo user account
                    // NGHIỆP VỤ: Lưu user vào database và redirect login
                    $password = md5($passwordTemp);
                    
                    // Set timezone cho Vietnam
                    date_default_timezone_set('Asia/Ho_Chi_Minh');
                    $doj = date('Y-m-d H:i:s');
                    
                    // Check email uniqueness
                    // Giải thích: Đảm bảo email chưa tồn tại trong database
                    $check_res = pg_query($con, "select * from users where email='$email'");
                    $check_user = pg_num_rows($check_res);
                    
                    if ($check_user > 0) {
                        $msg = "Email ID already exists please login";
                    } else {
                        // Insert new user vào database
                        // Giải thích: Tạo user record với thông tin đã validated
                        $sql = "insert into users(name, email, mobile, password, doj)
                                values('$name', '$email', '$mobile', '$password', '$doj')";
                        
                        if (pg_query($con, $sql)) {
                            // Registration success -> redirect login
                            echo "<script>window.top.location='SignIn.php';</script>";
                        } else {
                            $msg = "Registration failed. Please try again.";
                        }
                    }
                } else {
                    $emailErr = "Please enter valid Email address";
                }
            }
        } else {
            $nameErr = "Only letters and white space allowed in Name";
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
                                    <input type="number" min="111111111" max="999999999" class="form-control"
                                        id="mobile" name="mobile" placeholder="number" required />
                                    <label for="mobile">Mobile Number(Without +84)</label>
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