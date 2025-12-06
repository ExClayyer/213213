<?php
// Database connection for job board project
$DB = new PDO("mysql:dbname=jobboard;host=lamp-mysql8", "root", "tiger", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);
session_start();

if (!function_exists('dd')) {
    function dd(...$vars) {
        echo "<pre>";
        foreach ($vars as $var) {
            print_r($var);
        }
        echo "</pre>";
        exit;
    }
}

function check_auth() {
    if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
        header("Location: index.php?login=true");
        exit;
    }
}

// ========== НОВЫЕ ФУНКЦИИ ДЛЯ РОЛЕЙ ==========

// Получить все роли пользователя
function get_user_roles($user_id) {
    global $DB;
    $stmt = $DB->prepare("
        SELECT ut.* 
        FROM user_roles ur
        JOIN user_types ut ON ur.user_type_id = ut.id
        WHERE ur.user_id = ?
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// Получить массив названий ролей пользователя
function get_user_role_names($user_id) {
    global $DB;
    $stmt = $DB->prepare("
        SELECT ut.type_name 
        FROM user_roles ur
        JOIN user_types ut ON ur.user_type_id = ut.id
        WHERE ur.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
    return $roles ?: [];
}

// Проверить, имеет ли пользователь определенную роль
function has_role($user_id, $role_name) {
    $roles = get_user_role_names($user_id);
    return in_array($role_name, $roles);
}

// Проверить, имеет ли пользователь хотя бы одну из указанных ролей
function has_any_role($user_id, $role_names) {
    $user_roles = get_user_role_names($user_id);
    return !empty(array_intersect($user_roles, $role_names));
}

// Получить все типы пользователей
function get_user_types() {
    global $DB;
    return $DB->query("SELECT * FROM user_types ORDER BY id")->fetchAll();
}

// Добавить роль пользователю
function add_user_role($user_id, $user_type_id) {
    global $DB;
    try {
        $stmt = $DB->prepare("INSERT INTO user_roles (user_id, user_type_id) VALUES (?, ?)");
        return $stmt->execute([$user_id, $user_type_id]);
    } catch (PDOException $e) {
        // Игнорируем ошибку дублирования (UNIQUE constraint)
        if ($e->getCode() == 23000) {
            return false;
        }
        throw $e;
    }
}

// Удалить роль у пользователя
function remove_user_role($user_id, $user_type_id) {
    global $DB;
    $stmt = $DB->prepare("DELETE FROM user_roles WHERE user_id = ? AND user_type_id = ?");
    return $stmt->execute([$user_id, $user_type_id]);
}

// ========== ОБНОВЛЕННЫЕ СУЩЕСТВУЮЩИЕ ФУНКЦИИ ==========

// Authentication function
function auth() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']);
}

// Admin check function - теперь проверяет через роли
function is_admin() {
    if (!auth()) return false;
    $user_id = $_SESSION['user']['id'];
    return has_role($user_id, 'admin');
}

// Проверить, является ли пользователь работодателем
function is_employer() {
    if (!auth()) return false;
    $user_id = $_SESSION['user']['id'];
    return has_role($user_id, 'employer');
}

// Проверить, является ли пользователь соискателем
function is_job_seeker() {
    if (!auth()) return false;
    $user_id = $_SESSION['user']['id'];
    return has_role($user_id, 'job_seeker');
}

// Проверить, является ли пользователь модератором
function is_moderator() {
    if (!auth()) return false;
    $user_id = $_SESSION['user']['id'];
    return has_role($user_id, 'moderator');
}

// Проверить, является ли пользователь редактором
function is_editor() {
    if (!auth()) return false;
    $user_id = $_SESSION['user']['id'];
    return has_role($user_id, 'editor');
}

// Проверить, является ли пользователь менеджером поддержки
function is_support_manager() {
    if (!auth()) return false;
    $user_id = $_SESSION['user']['id'];
    return has_role($user_id, 'support_manager');
}

// User login function - обновлено с загрузкой ролей
function login_user($email, $password) {
    global $DB;
    
    $stmt = $DB->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        // Получаем роли пользователя
        $roles = get_user_roles($user['id']);
        $user['roles'] = $roles;
        $user['role_names'] = get_user_role_names($user['id']);
        
        $_SESSION['user'] = $user;
        return true;
    }
    
    return false;
}

// Получить пользователя по ID с ролями
function get_user($id) {
    global $DB;
    
    $stmt = $DB->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    
    if ($user) {
        $user['roles'] = get_user_roles($id);
        $user['role_names'] = get_user_role_names($id);
    }
    
    return $user;
}

// Получить пользователя по email с ролями
function get_user_by_email($email) {
    global $DB;
    $stmt = $DB->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        $user['roles'] = get_user_roles($user['id']);
        $user['role_names'] = get_user_role_names($user['id']);
    }
    
    return $user;
}

// ========== ФУНКЦИИ ДЛЯ ПРОВЕРКИ ПРАВ ==========

// Может ли пользователь публиковать вакансии
function can_post_jobs($user_id) {
    if (is_admin()) return true;
    
    // Проверяем, является ли пользователь работодателем
    if (!is_employer()) return false;
    
    // Проверяем, есть ли у него верифицированная компания
    $companies = get_user_companies($user_id);
    foreach ($companies as $company) {
        if ($company['verified']) {
            return true;
        }
    }
    
    return false;
}

// Может ли пользователь управлять вакансиями
function can_manage_jobs($user_id) {
    return is_admin() || is_moderator() || is_employer();
}

// Может ли пользователь управлять компаниями
function can_manage_companies($user_id) {
    return is_admin() || is_moderator();
}

// Может ли пользователь управлять пользователями
function can_manage_users($user_id) {
    return is_admin();
}

// Может ли пользователь управлять блогом
function can_manage_blog($user_id) {
    return is_admin() || is_editor();
}

// Может ли пользователь управлять контактами
function can_manage_contacts($user_id) {
    return is_admin() || is_support_manager();
}

// ========== ОСТАЛЬНЫЕ ФУНКЦИИ (без изменений) ==========

// Job Board specific header function
function jobboard_header($title = "Job Board", $active_page = "home") {
    ?>
    <!DOCTYPE html>
    <html class="no-js" lang="zxx">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title><?php echo htmlspecialchars($title); ?></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" type="image/x-icon" href="img/favicon.png">
        <link rel="stylesheet" href="css/bootstrap.min.css">
        <link rel="stylesheet" href="css/owl.carousel.min.css">
        <link rel="stylesheet" href="css/magnific-popup.css">
        <link rel="stylesheet" href="css/font-awesome.min.css">
        <link rel="stylesheet" href="css/themify-icons.css">
        <link rel="stylesheet" href="css/nice-select.css">
        <link rel="stylesheet" href="css/flaticon.css">
        <link rel="stylesheet" href="css/gijgo.css">
        <link rel="stylesheet" href="css/animate.min.css">
        <link rel="stylesheet" href="css/slicknav.css">
        <link rel="stylesheet" href="css/style.css">
        <style>
            .user-menu {
                display: flex;
                align-items: center;
                gap: 1rem;
            }
            .user-info {
                color: #fff;
                font-weight: bold;
            }
            .logout-btn {
                color: #fff;
                text-decoration: none;
                padding: 0.5rem 1rem;
                background: #ff5e3a;
                border-radius: 5px;
            }
            .logout-btn:hover {
                background: #ff3b1a;
            }
            .role-badge {
                display: inline-block;
                padding: 2px 8px;
                font-size: 0.7rem;
                border-radius: 10px;
                margin-left: 5px;
                background: #4aff88;
                color: #000;
            }
            .admin-role { background: #ff5e3a; color: white; }
            .employer-role { background: #3498db; color: white; }
            .seeker-role { background: #2ecc71; color: white; }
            .moderator-role { background: #9b59b6; color: white; }
            .editor-role { background: #e67e22; color: white; }
            .support-role { background: #1abc9c; color: white; }
        </style>
    </head>
    <body>
        <!--[if lte IE 9]>
            <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>
        <![endif]-->

        <!-- header-start -->
        <header>
            <div class="header-area">
                <div id="sticky-header" class="main-header-area">
                    <div class="container-fluid">
                        <div class="header_bottom_border">
                            <div class="row align-items-center">
                                <div class="col-xl-3 col-lg-2">
                                    <div class="logo">
                                        <a href="index.php">
                                            <img src="img/logo.png" alt="Job Board Logo">
                                        </a>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-7">
                                    <div class="main-menu d-none d-lg-block">
                                        <nav>
                                            <ul id="navigation">
                                                <li><a href="index.php" <?php echo $active_page == 'home' ? 'class="active"' : ''; ?>>home</a></li>
                                                <li><a href="jobs.php" <?php echo $active_page == 'jobs' ? 'class="active"' : ''; ?>>Browse Job</a></li>
                                                <li><a href="#" <?php echo in_array($active_page, ['candidate', 'job_details', 'elements']) ? 'class="active"' : ''; ?>>pages <i class="ti-angle-down"></i></a>
                                                    <ul class="submenu">
                                                        <li><a href="candidate.php">Candidates</a></li>
                                                        <li><a href="job_details.php">job details</a></li>
                                                        <li><a href="elements.php">elements</a></li>
                                                    </ul>
                                                </li>
                                                <li><a href="#" <?php echo in_array($active_page, ['blog', 'single-blog']) ? 'class="active"' : ''; ?>>blog <i class="ti-angle-down"></i></a>
                                                    <ul class="submenu">
                                                        <li><a href="blog.php">blog</a></li>
                                                        <li><a href="single-blog.php">single-blog</a></li>
                                                    </ul>
                                                </li>
                                                <li><a href="contact.php" <?php echo $active_page == 'contact' ? 'class="active"' : ''; ?>>Contact</a></li>
                                                
                                                <?php if (auth() && (is_admin() || is_moderator())): ?>
                                                <li><a href="#" <?php echo in_array($active_page, ['admin', 'moderator']) ? 'class="active"' : ''; ?>>Admin <i class="ti-angle-down"></i></a>
                                                    <ul class="submenu">
                                                        <?php if (is_admin()): ?>
                                                        <li><a href="admin_users.php">Manage Users</a></li>
                                                        <li><a href="admin_companies.php">Manage Companies</a></li>
                                                        <?php endif; ?>
                                                        <li><a href="admin_jobs.php">Manage Jobs</a></li>
                                                        <li><a href="admin_contacts.php">Manage Contacts</a></li>
                                                    </ul>
                                                </li>
                                                <?php endif; ?>
                                            </ul>
                                        </nav>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 d-none d-lg-block">
                                    <div class="Appointment">
                                        <div class="user-menu">
                                            <?php if (auth()): ?>
                                                <div class="user-info">
                                                    Welcome, <?php echo htmlspecialchars($_SESSION['user']['full_name'] ?? 'User'); ?>
                                                    <?php if (isset($_SESSION['user']['role_names'])): ?>
                                                        <?php foreach ($_SESSION['user']['role_names'] as $role): ?>
                                                            <span class="role-badge <?php echo $role; ?>-role"><?php echo $role; ?></span>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </div>
                                                <a href="logout.php" class="logout-btn">Log out</a>
                                            <?php else: ?>
                                                <div class="phone_num d-none d-xl-block">
                                                    <a href="login.php">Log in</a>
                                                </div>
                                                <div class="d-none d-lg-block">
                                                    <a class="boxed-btn3" href="register.php">Register</a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="mobile_menu d-block d-lg-none"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <!-- header-end -->
    <?php
}

// Job Board specific footer function
function jobboard_footer() {
    ?>
        <!-- footer start -->
        <footer class="footer">
            <div class="footer_top">
                <div class="container">
                    <div class="row">
                        <div class="col-xl-3 col-md-6 col-lg-3">
                            <div class="footer_widget wow fadeInUp" data-wow-duration="1s" data-wow-delay=".3s">
                                <div class="footer_logo">
                                    <a href="#">
                                        <img src="img/logo.png" alt="">
                                    </a>
                                </div>
                                <p>
                                    jobboard@support.com <br>
                                    +10 873 672 6782 <br>
                                    600/D, Green road, NewYork
                                </p>
                                <div class="socail_links">
                                    <ul>
                                        <li>
                                            <a href="#">
                                                <i class="ti-facebook"></i>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <i class="fa fa-google-plus"></i>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <i class="fa fa-twitter"></i>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <i class="fa fa-instagram"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-6 col-lg-2">
                            <div class="footer_widget wow fadeInUp" data-wow-duration="1.1s" data-wow-delay=".4s">
                                <h3 class="footer_title">
                                    Company
                                </h3>
                                <ul>
                                    <li><a href="#">About</a></li>
                                    <li><a href="#">Pricing</a></li>
                                    <li><a href="#">Carrier Tips</a></li>
                                    <li><a href="#">FAQ</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 col-lg-3">
                            <div class="footer_widget wow fadeInUp" data-wow-duration="1.2s" data-wow-delay=".5s">
                                <h3 class="footer_title">
                                    Category
                                </h3>
                                <ul>
                                    <li><a href="#">Design & Art</a></li>
                                    <li><a href="#">Engineering</a></li>
                                    <li><a href="#">Sales & Marketing</a></li>
                                    <li><a href="#">Finance</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6 col-lg-4">
                            <div class="footer_widget wow fadeInUp" data-wow-duration="1.3s" data-wow-delay=".6s">
                                <h3 class="footer_title">
                                    Subscribe
                                </h3>
                                <form action="#" class="newsletter_form">
                                    <input type="text" placeholder="Enter your mail">
                                    <button type="submit">Subscribe</button>
                                </form>
                                <p class="newsletter_text">Esteem spirit temper too say adieus who direct esteem esteems luckily.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="copy-right_text wow fadeInUp" data-wow-duration="1.4s" data-wow-delay=".3s">
                <div class="container">
                    <div class="footer_border"></div>
                    <div class="row">
                        <div class="col-xl-12">
                            <p class="copy_right text-center">
                                &copy; <?php echo date("Y"); ?> Job Board. All rights reserved.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <!--/ footer end  -->

        <!-- JS here -->
        <script src="js/vendor/modernizr-3.5.0.min.js"></script>
        <script src="js/vendor/jquery-1.12.4.min.js"></script>
        <script src="js/popper.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <script src="js/owl.carousel.min.js"></script>
        <script src="js/isotope.pkgd.min.js"></script>
        <script src="js/ajax-form.js"></script>
        <script src="js/waypoints.min.js"></script>
        <script src="js/jquery.counterup.min.js"></script>
        <script src="js/imagesloaded.pkgd.min.js"></script>
        <script src="js/scrollIt.js"></script>
        <script src="js/jquery.scrollUp.min.js"></script>
        <script src="js/wow.min.js"></script>
        <script src="js/nice-select.min.js"></script>
        <script src="js/jquery.slicknav.min.js"></script>
        <script src="js/jquery.magnific-popup.min.js"></script>
        <script src="js/plugins.js"></script>
        <script src="js/gijgo.min.js"></script>
        <script src="js/contact.js"></script>
        <script src="js/jquery.ajaxchimp.min.js"></script>
        <script src="js/jquery.form.js"></script>
        <script src="js/jquery.validate.min.js"></script>
        <script src="js/mail-script.js"></script>
        <script src="js/main.js"></script>
    </body>
    </html>
    <?php
}

// Redirect back function
function back() {
    $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    header("Location: " . $referer);
    exit;
}

// Get user companies
function get_user_companies($user_id) {
    global $DB;
    $stmt = $DB->prepare("SELECT * FROM companies WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// Get all countries
function get_countries() {
    global $DB;
    return $DB->query("SELECT * FROM countries ORDER BY name")->fetchAll();
}

// Get cities by country
function get_cities_by_country($country_id) {
    global $DB;
    $stmt = $DB->prepare("SELECT * FROM cities WHERE country_id = ? ORDER BY name");
    $stmt->execute([$country_id]);
    return $stmt->fetchAll();
}

// Get total job count
function get_total_jobs_count() {
    global $DB;
    $stmt = $DB->query("SELECT COUNT(*) as total FROM jobs WHERE status = 'active' AND expiry_date >= CURDATE()");
    $result = $stmt->fetch();
    return $result ? $result['total'] : 0;
}

// Get job by ID with all details
function get_job_details($id) {
    global $DB;
    $stmt = $DB->prepare("
        SELECT j.*, c.name as company_name, c.description as company_description, 
               c.website as company_website, ct.name as city_name, 
               co.name as country_name, jc.name as category_name,
               u.email as contact_email
        FROM jobs j 
        LEFT JOIN companies c ON j.company_id = c.id 
        LEFT JOIN cities ct ON j.city_id = ct.id 
        LEFT JOIN countries co ON ct.country_id = co.id
        LEFT JOIN job_categories jc ON j.category_id = jc.id 
        LEFT JOIN users u ON c.user_id = u.id
        WHERE j.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Apply for job
function apply_for_job($user_id, $job_id, $cover_letter, $resume_file = null) {
    global $DB;
    try {
        $stmt = $DB->prepare("
            INSERT INTO applications (user_id, job_id, cover_letter, resume_file, status)
            VALUES (?, ?, ?, ?, 'pending')
        ");
        return $stmt->execute([$user_id, $job_id, $cover_letter, $resume_file]);
    } catch (Exception $e) {
        return false;
    }
}

// Check if user already applied for job
function has_user_applied($user_id, $job_id) {
    global $DB;
    $stmt = $DB->prepare("SELECT id FROM applications WHERE user_id = ? AND job_id = ?");
    $stmt->execute([$user_id, $job_id]);
    return $stmt->fetch() !== false;
}

// Get user applications
function get_user_applications($user_id) {
    global $DB;
    $stmt = $DB->prepare("
        SELECT a.*, j.title as job_title, c.name as company_name, j.company_id
        FROM applications a
        JOIN jobs j ON a.job_id = j.id
        JOIN companies c ON j.company_id = c.id
        WHERE a.user_id = ?
        ORDER BY a.applied_date DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function get_active_jobs($limit = null, $category_id = null, $company_id = null, $location_id = null, $keyword = null) {
    global $DB;
    
    $sql = "SELECT j.*, c.name as company_name, ct.name as city_name, jc.name as category_name, jc.icon
            FROM jobs j 
            LEFT JOIN companies c ON j.company_id = c.id 
            LEFT JOIN cities ct ON j.city_id = ct.id 
            LEFT JOIN job_categories jc ON j.category_id = jc.id 
            WHERE j.status = 'active' AND j.expiry_date >= CURDATE()";
    
    $params = [];
    
    if ($category_id) {
        $sql .= " AND j.category_id = ?";
        $params[] = $category_id;
    }
    
    if ($company_id) {
        $sql .= " AND j.company_id = ?";
        $params[] = $company_id;
    }
    
    if ($location_id) {
        $sql .= " AND j.city_id = ?";
        $params[] = $location_id;
    }
    
    if ($keyword) {
        $sql .= " AND (j.title LIKE ? OR j.description LIKE ? OR c.name LIKE ?)";
        $keyword_param = "%$keyword%";
        $params[] = $keyword_param;
        $params[] = $keyword_param;
        $params[] = $keyword_param;
    }
    
    $sql .= " ORDER BY j.published_date DESC";
    
    if ($limit) {
        $sql .= " LIMIT " . intval($limit);
    }
    
    if ($params) {
        $stmt = $DB->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } else {
        return $DB->query($sql)->fetchAll();
    }
}

// Get job by ID
function get_job($id) {
    global $DB;
    
    $stmt = $DB->prepare("SELECT j.*, c.name as company_name, c.description as company_description, 
                         c.website as company_website, ct.name as city_name, 
                         jc.name as category_name 
                         FROM jobs j 
                         LEFT JOIN companies c ON j.company_id = c.id 
                         LEFT JOIN cities ct ON j.city_id = ct.id 
                         LEFT JOIN job_categories jc ON j.category_id = jc.id 
                         WHERE j.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Get job categories
function get_job_categories() {
    global $DB;
    return $DB->query("SELECT * FROM job_categories ORDER BY name")->fetchAll();
}

// Get blog posts
function get_blog_posts($limit = null) {
    global $DB;
    
    $sql = "SELECT bp.*, bc.name as category_name, u.full_name as author_name 
            FROM blog_posts bp 
            LEFT JOIN blog_categories bc ON bp.category_id = bc.id 
            LEFT JOIN users u ON bp.user_id = u.id 
            WHERE bp.publish_date <= CURDATE() 
            ORDER BY bp.publish_date DESC";
    
    if ($limit) {
        $sql .= " LIMIT " . intval($limit);
    }
    
    return $DB->query($sql)->fetchAll();
}

// Get recent candidates
function get_recent_candidates($limit = 8) {
    global $DB;
    
    $sql = "SELECT u.*, r.desired_position 
            FROM users u 
            LEFT JOIN resumes r ON u.id = r.user_id 
            WHERE u.id IN (SELECT DISTINCT user_id FROM user_roles ur JOIN user_types ut ON ur.user_type_id = ut.id WHERE ut.type_name = 'job_seeker') 
            AND u.status = 'active' 
            ORDER BY u.id DESC 
            LIMIT " . intval($limit);
    
    return $DB->query($sql)->fetchAll();
}