<?php
require_once 'config.php';

// Проверяем, является ли пользователь работодателем или админом
if (!auth()) {
    header("Location: login.php?redirect=register_company");
    exit;
}

$user_type_id = $_SESSION['user']['user_type_id'] ?? 0;
if ($user_type_id != 2 && $user_type_id != 4) {
    header("Location: index.php");
    exit;
}

// Проверяем, есть ли у пользователя уже компания
$existing_company = get_user_companies($_SESSION['user']['id'])[0] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $website = $_POST['website'] ?? '';
    $contact_email = $_POST['contact_email'] ?? '';
    $contact_phone = $_POST['contact_phone'] ?? '';
    
    // Валидация
    $errors = [];
    if (empty($name)) $errors[] = "Company name is required";
    if (!empty($website) && !filter_var($website, FILTER_VALIDATE_URL)) {
        $errors[] = "Please enter a valid website URL";
    }
    if (!empty($contact_email) && !filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    }
    
    if (empty($errors)) {
        try {
            if ($existing_company) {
                // Обновляем существующую компанию
                $stmt = $DB->prepare("
                    UPDATE companies 
                    SET name = ?, description = ?, website = ?, contact_email = ?, contact_phone = ?
                    WHERE user_id = ?
                ");
                
                $success = $stmt->execute([
                    $name, $description, $website, $contact_email, $contact_phone,
                    $_SESSION['user']['id']
                ]);
                
                $message = "Company information updated successfully!";
            } else {
                // Создаем новую компанию
                $stmt = $DB->prepare("
                    INSERT INTO companies 
                    (user_id, name, description, website, contact_email, contact_phone, verified) 
                    VALUES (?, ?, ?, ?, ?, ?, 0)
                ");
                
                $success = $stmt->execute([
                    $_SESSION['user']['id'], $name, $description, $website, 
                    $contact_email, $contact_phone
                ]);
                
                $message = "Company registered successfully! It will be verified by our team.";
            }
            
            if ($success) {
                // Обновляем данные о компании
                $stmt = $DB->prepare("SELECT * FROM companies WHERE user_id = ?");
                $stmt->execute([$_SESSION['user']['id']]);
                $existing_company = $stmt->fetch();
            } else {
                $error = "Failed to save company information. Please try again.";
            }
        } catch (Exception $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

jobboard_header("Register Company", "jobs");
?>
<!-- bradcam_area  -->
<div class="bradcam_area bradcam_bg_1">
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="bradcam_text">
                    <h3><?php echo $existing_company ? 'Update Company' : 'Register Company'; ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>
<!--/ bradcam_area  -->

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title text-center mb-4">
                        <?php echo $existing_company ? 'Update Company Information' : 'Register Your Company'; ?>
                    </h3>
                    
                    <?php if (isset($message)): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $err): ?>
                                    <li><?php echo htmlspecialchars($err); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="name">Company Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($_POST['name'] ?? $existing_company['name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($_POST['description'] ?? $existing_company['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="website">Website</label>
                            <input type="url" class="form-control" id="website" name="website" 
                                   value="<?php echo htmlspecialchars($_POST['website'] ?? $existing_company['website'] ?? ''); ?>"
                                   placeholder="https://example.com">
                        </div>
                        
                        <div class="form-group">
                            <label for="contact_email">Contact Email</label>
                            <input type="email" class="form-control" id="contact_email" name="contact_email" 
                                   value="<?php echo htmlspecialchars($_POST['contact_email'] ?? $existing_company['contact_email'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="contact_phone">Contact Phone</label>
                            <input type="tel" class="form-control" id="contact_phone" name="contact_phone" 
                                   value="<?php echo htmlspecialchars($_POST['contact_phone'] ?? $existing_company['contact_phone'] ?? ''); ?>">
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <?php echo $existing_company ? 'Update Company' : 'Register Company'; ?>
                            </button>
                        </div>
                    </form>
                    
                    <?php if ($existing_company): ?>
                        <div class="mt-4">
                            <h5>Company Status</h5>
                            <div class="alert <?php echo $existing_company['verified'] ? 'alert-success' : 'alert-warning'; ?>">
                                <strong>Status:</strong> 
                                <?php echo $existing_company['verified'] ? 'Verified ✓' : 'Pending verification'; ?>
                            </div>
                            <p>You can post jobs after your company is verified.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php jobboard_footer(); ?>