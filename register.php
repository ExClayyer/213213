<?php
require_once 'config.php';

if (auth()) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $account_type = $_POST['account_type'] ?? 'job_seeker';
    
    if ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Check if email exists
        $stmt = $DB->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = "Email already registered";
        } else {
            // Get user type ID
            $type_stmt = $DB->prepare("SELECT id FROM user_types WHERE type_name = ?");
            $type_stmt->execute([$account_type]);
            $user_type = $type_stmt->fetch();
            
            if (!$user_type) {
                $error = "Invalid account type";
            } else {
                // Start transaction
                $DB->beginTransaction();
                
                try {
                    // Insert user
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $DB->prepare("INSERT INTO users (email, password, full_name) VALUES (?, ?, ?)");
                    
                    if (!$stmt->execute([$email, $hashed_password, $full_name])) {
                        throw new Exception("Failed to create user");
                    }
                    
                    $user_id = $DB->lastInsertId();
                    
                    // Add role
                    $role_stmt = $DB->prepare("INSERT INTO user_roles (user_id, user_type_id) VALUES (?, ?)");
                    $role_stmt->execute([$user_id, $user_type['id']]);
                    
                    $DB->commit();
                    
                    // Auto login
                    $user = get_user($user_id);
                    $_SESSION['user'] = $user;
                    
                    // Redirect
                    if ($account_type === 'employer') {
                        header("Location: register_company.php");
                    } else {
                        header("Location: index.php");
                    }
                    exit;
                    
                } catch (Exception $e) {
                    $DB->rollBack();
                    $error = "Registration failed. Please try again.";
                }
            }
        }
    }
}

jobboard_header("Register", "home");
?>
<!-- bradcam_area  -->
<div class="bradcam_area bradcam_bg_1">
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="bradcam_text">
                    <h3>Register</h3>
                </div>
            </div>
        </div>
    </div>
</div>
<!--/ bradcam_area  -->

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title text-center mb-4">Create Account</h3>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="account_type">Account Type</label>
                            <select class="form-control" id="account_type" name="account_type">
                                <option value="job_seeker">Job Seeker</option>
                                <option value="employer">Employer</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">Register</button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <p>Already have an account? <a href="login.php">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php jobboard_footer(); ?>