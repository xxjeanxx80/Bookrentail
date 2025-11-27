<?php
require(__DIR__ . '/../includes/header.php');
if (isset($_SESSION['USER_LOGIN'])) {
    header('Location: index.php');
    exit;
}

$msg = '';
if (isset($_POST['submit'])) {
    $email = trim($_POST['email']);
    $password = md5(trim($_POST['password']));
    $rememberMe = isset($_POST['remember_me']) ? true : false;
    
    $email = mysqli_real_escape_string($con, $email);
    $sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
    $res = mysqli_query($con, $sql);
    
    if ($res && mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        $_SESSION['USER_LOGIN'] = 'yes';
        $_SESSION['USER_ID'] = $row['id'];
        $_SESSION['USER_NAME'] = $row['name'];
        
        // Nếu chọn Remember Me, lưu token
        if ($rememberMe) {
            saveRememberToken($con, $row['id']);
        }
        
        $redirect = $_SESSION['BeforeCheckoutLogin'] ?? 'index.php';
        unset($_SESSION['BeforeCheckoutLogin']);
        header("Location: $redirect");
        exit;
    } else {
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
                            <div class="d-flex align-items-center mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember_me" id="remember_me" value="1">
                                    <label class="form-check-label" for="remember_me">
                                        Remember Me
                                    </label>
                                </div>
                            </div>
                            <div id="error" class="text-center mb-3" style="color: red">
                                <?php echo $msg ?>
                            </div>
                            <div class="d-flex justify-content-center mt-3 mb-3 mb-lg-4">
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