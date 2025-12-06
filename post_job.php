<?php
require_once 'config.php';

// Проверяем, является ли пользователь работодателем или админом
if (!auth()) {
    header("Location: login.php?redirect=post_job");
    exit;
}

$user_type_id = $_SESSION['user']['user_type_id'] ?? 0;
if ($user_type_id != 2 && $user_type_id != 4) {
    header("Location: index.php");
    exit;
}

// Получаем данные для формы
$categories = get_job_categories();
$countries = get_countries();
$cities = $DB->query("SELECT * FROM cities ORDER BY name")->fetchAll();

$companies = get_user_companies($_SESSION['user']['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $company_id = $_POST['company_id'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $city_id = $_POST['city_id'] ?? '';
    $address = $_POST['address'] ?? '';
    $postal_code = $_POST['postal_code'] ?? '';
    $job_type = $_POST['job_type'] ?? 'full-time';
    $salary_from = $_POST['salary_from'] ?? '';
    $salary_to = $_POST['salary_to'] ?? '';
    $requirements = $_POST['requirements'] ?? '';
    $expiry_date = $_POST['expiry_date'] ?? date('Y-m-d', strtotime('+30 days'));
    
    // Валидация
    $errors = [];
    if (empty($title)) $errors[] = "Title is required";
    if (empty($description)) $errors[] = "Description is required";
    if (empty($company_id)) $errors[] = "Company is required";
    if (empty($category_id)) $errors[] = "Category is required";
    
    // Проверяем, есть ли у компании вакансии
    $company_stmt = $DB->prepare("SELECT verified FROM companies WHERE id = ? AND user_id = ?");
    $company_stmt->execute([$company_id, $_SESSION['user']['id']]);
    $company = $company_stmt->fetch();
    
    if (!$company) {
        $errors[] = "Company not found or you don't have permission to post jobs for this company";
    } elseif (!$company['verified']) {
        $errors[] = "Your company needs to be verified before posting jobs";
    }
    
    if (empty($errors)) {
        try {
            $stmt = $DB->prepare("
                INSERT INTO jobs 
                (company_id, title, description, city_id, address, postal_code, job_type, salary_from, salary_to, requirements, category_id, expiry_date, published_date, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), 'pending')
            ");
            
            $success = $stmt->execute([
                $company_id, $title, $description, $city_id, $address, $postal_code, $job_type,
                $salary_from ?: null, $salary_to ?: null, $requirements, $category_id, $expiry_date
            ]);
            
            if ($success) {
                $message = "Job posted successfully! It will be reviewed by our moderators.";
                $_POST = []; // Очищаем форму
            } else {
                $error = "Failed to post job. Please try again.";
            }
        } catch (Exception $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

jobboard_header("Post a Job", "jobs");
?>
<!-- bradcam_area  -->
<div class="bradcam_area bradcam_bg_1">
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="bradcam_text">
                    <h3>Post a Job</h3>
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
                    <h3 class="card-title text-center mb-4">Post a New Job Vacancy</h3>
                    
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
                            <label for="title">Job Title *</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="company_id">Company *</label>
                            <select class="form-control" id="company_id" name="company_id" required>
                                <option value="">Select Company</option>
                                <?php foreach ($companies as $company): ?>
                                    <option value="<?php echo $company['id']; ?>" 
                                        <?php echo ($_POST['company_id'] ?? '') == $company['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($company['name']); ?>
                                        <?php echo $company['verified'] ? ' (Verified)' : ' (Pending)'; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($companies)): ?>
                                <div class="alert alert-warning mt-2">
                                    You need to register a company first. 
                                    <a href="register_company.php" class="alert-link">Register Company</a>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="category_id">Category *</label>
                            <select class="form-control" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"
                                        <?php echo ($_POST['category_id'] ?? '') == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="country_id">Country</label>
                                    <select class="form-control" id="country_id" name="country_id">
                                        <option value="">Select Country</option>
                                        <?php foreach ($countries as $country): ?>
                                            <option value="<?php echo $country['id']; ?>"
                                                <?php echo ($_POST['country_id'] ?? '') == $country['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($country['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="city_id">City</label>
                                    <select class="form-control" id="city_id" name="city_id">
                                        <option value="">Select City</option>
                                        <?php foreach ($cities as $city): ?>
                                            <option value="<?php echo $city['id']; ?>"
                                                <?php echo ($_POST['city_id'] ?? '') == $city['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($city['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <input type="text" class="form-control" id="address" name="address" 
                                           value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>"
                                           placeholder="Street address">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="postal_code">Postal Code</label>
                                    <input type="text" class="form-control" id="postal_code" name="postal_code" 
                                           value="<?php echo htmlspecialchars($_POST['postal_code'] ?? ''); ?>"
                                           placeholder="12345">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="job_type">Job Type</label>
                            <select class="form-control" id="job_type" name="job_type">
                                <option value="full-time" <?php echo ($_POST['job_type'] ?? 'full-time') == 'full-time' ? 'selected' : ''; ?>>Full-time</option>
                                <option value="part-time" <?php echo ($_POST['job_type'] ?? '') == 'part-time' ? 'selected' : ''; ?>>Part-time</option>
                                <option value="contract" <?php echo ($_POST['job_type'] ?? '') == 'contract' ? 'selected' : ''; ?>>Contract</option>
                                <option value="remote" <?php echo ($_POST['job_type'] ?? '') == 'remote' ? 'selected' : ''; ?>>Remote</option>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="salary_from">Salary From ($/year)</label>
                                    <input type="number" class="form-control" id="salary_from" name="salary_from" 
                                           value="<?php echo htmlspecialchars($_POST['salary_from'] ?? ''); ?>"
                                           min="0" step="1000">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="salary_to">Salary To ($/year)</label>
                                    <input type="number" class="form-control" id="salary_to" name="salary_to" 
                                           value="<?php echo htmlspecialchars($_POST['salary_to'] ?? ''); ?>"
                                           min="0" step="1000">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="expiry_date">Expiry Date</label>
                            <input type="date" class="form-control" id="expiry_date" name="expiry_date" 
                                   value="<?php echo htmlspecialchars($_POST['expiry_date'] ?? date('Y-m-d', strtotime('+30 days'))); ?>"
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Job Description *</label>
                            <textarea class="form-control" id="description" name="description" rows="6" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="requirements">Requirements</label>
                            <textarea class="form-control" id="requirements" name="requirements" rows="4"><?php echo htmlspecialchars($_POST['requirements'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg">Post Job</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Динамическая загрузка городов при выборе страны
    $('#country_id').change(function() {
        var countryId = $(this).val();
        if (countryId) {
            $.ajax({
                url: 'ajax_get_cities.php',
                type: 'GET',
                data: { country_id: countryId },
                success: function(data) {
                    $('#city_id').html('<option value="">Select City</option>' + data);
                }
            });
        } else {
            $('#city_id').html('<option value="">Select City</option>');
        }
    });
});
</script>

<?php jobboard_footer(); ?>