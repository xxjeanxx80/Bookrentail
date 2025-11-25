<?php
require('header.php');

// ============================================================================  
// CUSTOMER LOGIN - CLEAN CODE GIẢI THÍCH TẠI SAU
// ============================================================================

// Giải thích: Redirect nếu user đã đăng nhập
// NGHIỆP VỤ: Ngăn user truy cập login page khi đã logged in
if (isset($_SESSION['USER_LOGIN'])) {
    echo "<script>window.top.location='index.php';</script>";
    exit;
}

$msg = $passwordTemp = '';
if (isset($_POST['submit'])) {
    // Xử lý login form submission
    // Giải thích: Validate email và password từ form
    // NGHIỆP VỤ: Xác thực user credentials và tạo session
    $email = getSafeValue($con, $_POST['email']);
    $passwordTemp = getSafeValue($con, $_POST['password']);
    $password = md5($passwordTemp);
    
    // Query user từ database
    // LOGIC: SELECT users với email và password hash
    $sql = "select * from users where email='$email' and password='$password'";
    $res = pg_query($con, $sql);
    $row = pg_fetch_assoc($res);
    $count = pg_num_rows($res);
    
    if ($count > 0) {
        // Tạo session cho user đã đăng nhập
        // Giải thích: Lưu thông tin user vào PHP session
        $_SESSION['USER_LOGIN'] = 'yes';
        $_SESSION['USER_ID'] = $row['id'];
        $_SESSION['USER_NAME'] = $row['name'];
        
        // Set remember me với database tokens
        if (isset($_POST['remember_me']) && $_POST['remember_me'] == '1') {
            // Generate secure random token
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', time() + (86400 * 30)); // 30 ngày
            $created_at = date('Y-m-d H:i:s');
            
            // Insert token vào user_tokens table
            $insertTokenSql = "INSERT INTO user_tokens (user_id, token, expires_at, created_at) 
                              VALUES ('{$row['id']}', '$token', '$expires_at', '$created_at')";
            pg_query($con, $insertTokenSql);
            
            // Set cookie với token (bảo mật hơn old method)
            $cookie_name = 'user_auth';
            $cookie_value = base64_encode($row['id'] . '|' . $token);
            setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/");
        }
        
        // Redirect logic
        // Giải thích: Nếu user đang checkout -> redirect về checkout page
        // NGHIỆP VỤ: Giữ lại shopping cart sau login
        if (isset($_SESSION['BeforeCheckoutLogin'])) {
            $checkoutAfterLogin = $_SESSION['BeforeCheckoutLogin'];
            echo "<script>window.top.location='$checkoutAfterLogin';</script>";
        } else {
            echo "<script>window.top.location='index.php';</script>";
            exit;
        }
    } else {
        // Login failed
        $msg = "Invalid Username/Password";
    }
}
?>
<script>
document.title = "Login | Book Rental";
</script>
<br><br>
<br>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-12 col-xl-11">
            <div class="card-body p-md-5">
                <div class="row justify-content-center align-content-center">
                    <div class="col-md-10 col-lg-6 col-xl-5 order-2 order-lg-1">
                        <div class="d-flex justify-content-center mt-3 mb-3 mb-lg-4">
                            <h2>Login</h2>
                        </div>
                        <form class="mx-1 mx-md-4" method="post">
                            <div class="d-flex align-items-center mb-4">
                                <i class="fas fa-envelope fa-lg me-3 fa-fw"></i>
                                <div class="form-floating flex-fill">
                                    <input type="email" name="email" class="form-control" id="email"
                                        placeholder="name@example.com" required />
                                    <label for="email">Email address</label>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-1">
                                <i class="fas fa-lock fa-lg me-3 fa-fw"></i>
                                <div class="form-floating flex-fill">
                                    <input type="password" name="password" class="form-control" id="Password"
                                        placeholder="Password" required />
                                    <label for="Password">Password</label>
                                </div>
                            </div>
                            <div id="error" class="text-center mb-3" style="color: red">
                                <?php echo $msg ?>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3 mb-3 mb-lg-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember_me" value="1" id="rememberMe">
                                    <label class="form-check-label" for="rememberMe">
                                        Remember me for 30 days
                                    </label>
                                </div>
                                <button type="submit" name="submit" class="btn btn-primary  ">
                                    Login
                                </button>
                            </div>
                            <div class="text-center mt-2">
                                <a href="register.php" class="text-decoration-none text-black">
                                    New to Book Rental?
                                    <span style="color: rgb(138, 110, 253)">Register</span></a>
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