<?php
require_once 'config.php';

if (auth()) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password";
    } else {
        if (login_user($email, $password)) {
            // Check if user has roles
            if (empty($_SESSION['user']['role_names'])) {
                $error = "Your account has no assigned roles. Please contact administrator.";
                session_destroy();
            } else {
                // Redirect based on role
                $redirect_to = 'index.php';
                
                if (is_admin() || is_moderator()) {
                    $redirect_to = 'admin_dashboard.php';
                }
                elseif (is_employer()) {
                    $companies = get_user_companies($_SESSION['user']['id']);
                    if (empty($companies)) {
                        $redirect_to = 'register_company.php';
                    } else {
                        $redirect_to = 'employer_dashboard.php';
                    }
                }
                elseif (is_job_seeker()) {
                    $redirect_to = 'jobs.php';
                }
                
                header("Location: " . $redirect_to);
                exit;
            }
        } else {
            $error = "Invalid email or password";
        }
    }
}

jobboard_header("Login", "home");
?>

<div class="bradcam_area bradcam_bg_1">
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="bradcam_text">
                    <h3>Login</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title text-center mb-4">Login to Job Board</h3>
                    
                    <?php if (isset($error) && $error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['registered']) && $_GET['registered'] == 'true'): ?>
                        <div class="alert alert-success">
                            Registration successful! Please login to continue.
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">Login</button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <p>Don't have an account? <a href="register.php">Register here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php jobboard_footer(); ?>