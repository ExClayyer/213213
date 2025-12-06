<?php
require_once 'config.php';

// Получаем данные из БД
$recent_jobs = get_active_jobs(6);
$job_categories = get_job_categories();
$total_jobs = get_total_jobs_count();

jobboard_header("Job Board - Find Your Dream Job", "home");
?>

<!-- slider_area_start -->
<div class="slider_area">
    <div class="single_slider d-flex align-items-center slider_bg_1">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7 col-md-6">
                    <div class="slider_text">
                        <h5 class="wow fadeInLeft" data-wow-duration="1s" data-wow-delay=".2s"><?php echo $total_jobs; ?>+ Jobs listed</h5>
                        <h3 class="wow fadeInLeft" data-wow-duration="1s" data-wow-delay=".3s">Find your Dream Job</h3>
                        <p class="wow fadeInLeft" data-wow-duration="1s" data-wow-delay=".4s">We provide instant access to thousands of job opportunities with quick approval that suit your term length</p>
                        <div class="sldier_btn wow fadeInLeft" data-wow-duration="1s" data-wow-delay=".5s">
                            <?php if (auth()): ?>
                                <a href="jobs.php" class="boxed-btn3">Browse All Jobs</a>
                            <?php else: ?>
                                <a href="register.php" class="boxed-btn3">Upload your Resume</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="ilstration_img wow fadeInRight d-none d-lg-block text-right" data-wow-duration="1s" data-wow-delay=".2s">
        <img src="img/banner/illustration.png" alt="">
    </div>
</div>
<!-- slider_area_end -->

<!-- catagory_area -->
<div class="catagory_area">
    <div class="container">
        <form id="searchForm" action="jobs.php" method="GET">
            <div class="row cat_search">
                <div class="col-lg-3 col-md-4">
                    <div class="single_input">
                        <input type="text" name="keyword" id="search_keyword" placeholder="Search keyword" 
                               value="<?php echo htmlspecialchars($_GET['keyword'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col-lg-3 col-md-4">
                    <div class="single_input">
                        <select class="wide" name="location" id="search_location">
                            <option value="">All Locations</option>
                            <?php
                            $cities = $DB->query("SELECT * FROM cities ORDER BY name")->fetchAll();
                            foreach ($cities as $city): 
                                $selected = isset($_GET['location']) && $_GET['location'] == $city['id'] ? 'selected' : '';
                            ?>
                                <option value="<?php echo $city['id']; ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($city['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4">
                    <div class="single_input">
                        <select class="wide" name="category" id="search_category">
                            <option value="">All Categories</option>
                            <?php foreach ($job_categories as $category): 
                                $selected = isset($_GET['category']) && $_GET['category'] == $category['id'] ? 'selected' : '';
                            ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-lg-3 col-md-12">
                    <div class="job_btn">
                        <button type="submit" class="boxed-btn3">Find Job</button>
                    </div>
                </div>
            </div>
        </form>
        <div class="row">
            <div class="col-lg-12">
                <div class="popular_search d-flex align-items-center">
                    <span>Popular Search:</span>
                    <ul>
                        <?php 
                        // Получаем популярные категории (первые 7)
                        $popular_categories = array_slice($job_categories, 0, 7);
                        foreach ($popular_categories as $category): ?>
                            <li><a href="jobs.php?category=<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<!--/ catagory_area -->

<!-- popular_catagory_area_start  -->
<div class="popular_catagory_area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section_title mb-40">
                    <h3>Popular Categories</h3>
                </div>
            </div>
        </div>
        <div class="row">
            <?php 
            // Получаем количество вакансий по категориям
            $stmt = $DB->query("
                SELECT jc.id, jc.name, jc.icon, COUNT(j.id) as job_count 
                FROM job_categories jc 
                LEFT JOIN jobs j ON jc.id = j.category_id AND j.status = 'active' AND j.expiry_date >= CURDATE() 
                GROUP BY jc.id, jc.name, jc.icon 
                ORDER BY job_count DESC 
                LIMIT 8
            ");
            $categories_with_counts = $stmt->fetchAll();
            
            if (empty($categories_with_counts)): ?>
                <div class="col-12 text-center">
                    <div class="alert alert-info">
                        <h4>No job categories available yet.</h4>
                        <p>Check back soon for new job categories.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($categories_with_counts as $category): ?>
                    <div class="col-lg-4 col-xl-3 col-md-6">
                        <div class="single_catagory">
                            <a href="jobs.php?category=<?php echo $category['id']; ?>">
                                <h4><?php echo htmlspecialchars($category['name']); ?></h4>
                            </a>
                            <p><span><?php echo $category['job_count']; ?></span> Available position<?php echo $category['job_count'] == 1 ? '' : 's'; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- popular_catagory_area_end  -->

<!-- job_listing_area_start  -->
<div class="job_listing_area">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="section_title">
                    <h3>Recent Job Listings</h3>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="brouse_job text-right">
                    <a href="jobs.php" class="boxed-btn4">Browse More Job</a>
                </div>
            </div>
        </div>
        <div class="job_lists">
            <?php if (empty($recent_jobs)): ?>
                <div class="col-12 text-center">
                    <div class="alert alert-warning">
                        <h4>No jobs available at the moment.</h4>
                        <p>Please check back later or browse other categories.</p>
                        <?php if (auth() && ($_SESSION['user']['user_type_id'] == 2 || $_SESSION['user']['user_type_id'] == 4)): ?>
                            <a href="post_job.php" class="btn btn-primary mt-2">Post a Job</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($recent_jobs as $index => $job): ?>
                        <div class="col-lg-12 col-md-12 mb-3">
                            <div class="single_jobs white-bg d-flex justify-content-between">
                                <div class="jobs_left d-flex align-items-center">
                                    <div class="thumb">
                                        <?php 
                                        // Определяем иконку по категории
                                        $icon = $job['icon'] ?? 'img/svg_icon/1.svg';
                                        if (empty($icon) || !file_exists($icon)) {
                                            $icon = 'img/svg_icon/' . (($index % 5) + 1) . '.svg';
                                        }
                                        ?>
                                        <img src="<?php echo $icon; ?>" alt="<?php echo htmlspecialchars($job['category_name'] ?? 'Job'); ?>">
                                    </div>
                                    <div class="jobs_conetent">
                                        <a href="job_details.php?id=<?php echo $job['id']; ?>">
                                            <h4><?php echo htmlspecialchars($job['title']); ?></h4>
                                        </a>
                                        <div class="links_locat d-flex align-items-center flex-wrap">
                                            <?php if ($job['city_name']): ?>
                                                <div class="location mr-3">
                                                    <p><i class="fa fa-map-marker"></i> 
                                                        <?php echo htmlspecialchars($job['city_name']); ?>
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($job['company_name']): ?>
                                                <div class="location mr-3">
                                                    <p><i class="fa fa-building"></i> 
                                                        <?php echo htmlspecialchars($job['company_name']); ?>
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                            <div class="location mr-3">
                                                <p><i class="fa fa-clock-o"></i> 
                                                    <?php 
                                                    $job_type = str_replace('-', ' ', $job['job_type'] ?? 'full-time');
                                                    echo ucwords($job_type);
                                                    ?>
                                                </p>
                                            </div>
                                            <?php if ($job['salary_from'] || $job['salary_to']): ?>
                                                <div class="location mr-3">
                                                    <p><i class="fa fa-money"></i> 
                                                        <?php if ($job['salary_from']): ?>
                                                            $<?php echo number_format($job['salary_from']); ?>
                                                        <?php endif; ?>
                                                        <?php if ($job['salary_to'] && $job['salary_from']): ?>
                                                            - $<?php echo number_format($job['salary_to']); ?>
                                                        <?php elseif ($job['salary_to']): ?>
                                                            $<?php echo number_format($job['salary_to']); ?>
                                                        <?php endif; ?>
                                                        /year
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="jobs_right">
                                    <div class="apply_now">
                                        <?php if (auth()): ?>
                                            <a class="heart_mark save-job" href="#" data-job-id="<?php echo $job['id']; ?>">
                                                <i class="ti-heart"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="job_details.php?id=<?php echo $job['id']; ?>" class="boxed-btn3">Apply Now</a>
                                    </div>
                                    <div class="date">
                                        <p>Date line: <?php echo date('d M Y', strtotime($job['expiry_date'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- job_listing_area_end  -->

<!-- featured_candidates_area_start  -->
<div class="featured_candidates_area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section_title text-center mb-40">
                    <h3>Featured Candidates</h3>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="candidate_active owl-carousel">
                    <?php 
                    // Получаем кандидатов (пользователей типа job_seeker)
                    $stmt = $DB->query("
                        SELECT u.*, r.desired_position 
                        FROM users u 
                        LEFT JOIN resumes r ON u.id = r.user_id 
                        WHERE u.user_type_id = 1 AND u.status = 'active' 
                        ORDER BY RAND() 
                        LIMIT 8
                    ");
                    $featured_candidates = $stmt->fetchAll();
                    
                    if (empty($featured_candidates)): ?>
                        <div class="col-12 text-center">
                            <div class="alert alert-info">
                                <p>No candidates available at the moment.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php 
                        $candidate_images = [
                            'img/candiateds/1.png',
                            'img/candiateds/2.png',
                            'img/candiateds/3.png',
                            'img/candiateds/4.png',
                            'img/candiateds/5.png',
                            'img/candiateds/6.png',
                            'img/candiateds/7.png',
                            'img/candiateds/8.png'
                        ];
                        
                        foreach ($featured_candidates as $index => $candidate): 
                            $image_index = $index % count($candidate_images);
                        ?>
                            <div class="single_candidates text-center">
                                <div class="thumb">
                                    <img src="<?php echo $candidate_images[$image_index]; ?>" alt="<?php echo htmlspecialchars($candidate['full_name'] ?? 'Candidate'); ?>">
                                </div>
                                <a href="candidate.php#candidate-<?php echo $candidate['id']; ?>">
                                    <h4><?php echo htmlspecialchars($candidate['full_name'] ?? 'Candidate'); ?></h4>
                                </a>
                                <p><?php echo htmlspecialchars($candidate['desired_position'] ?? 'Looking for opportunities'); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- featured_candidates_area_end  -->

<div class="top_companies_area">
    <div class="container">
        <div class="row align-items-center mb-40">
            <div class="col-lg-6 col-md-6">
                <div class="section_title">
                    <h3>Top Companies</h3>
                </div>
            </div>
            <div class="col-lg-6 col-md-6">
                <div class="brouse_job text-right">
                    <a href="jobs.php?sort=company" class="boxed-btn4">Browse All Companies</a>
                </div>
            </div>
        </div>
        <div class="row">
            <?php 
            // Получаем компании с активными вакансиями
            $stmt = $DB->query("
                SELECT c.*, COUNT(j.id) as active_jobs, c.logo
                FROM companies c 
                LEFT JOIN jobs j ON c.id = j.company_id AND j.status = 'active' AND j.expiry_date >= CURDATE() 
                WHERE c.verified = 1 
                GROUP BY c.id, c.name, c.logo
                HAVING active_jobs > 0 
                ORDER BY active_jobs DESC, c.name ASC
                LIMIT 4
            ");
            $top_companies = $stmt->fetchAll();
            
            if (empty($top_companies)): ?>
                <div class="col-12 text-center">
                    <div class="alert alert-info">
                        <h4>No companies with active jobs at the moment.</h4>
                        <p>Check back soon for company listings.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php 
                $company_icons = [
                    'img/svg_icon/5.svg' => 'Tech Company',
                    'img/svg_icon/4.svg' => 'Design Agency', 
                    'img/svg_icon/3.svg' => 'Finance Firm',
                    'img/svg_icon/1.svg' => 'Startup'
                ];
                
                foreach ($top_companies as $index => $company): 
                    // Используем логотип компании или иконку по умолчанию
                    if ($company['logo'] && file_exists($company['logo'])) {
                        $logo = $company['logo'];
                    } else {
                        $icon_keys = array_keys($company_icons);
                        $logo = $icon_keys[$index % count($icon_keys)];
                    }
                ?>
                    <div class="col-lg-4 col-xl-3 col-md-6 mb-4">
                        <div class="single_company">
                            <div class="thumb">
                                <img src="<?php echo $logo; ?>" alt="<?php echo htmlspecialchars($company['name']); ?>" 
                                     style="max-width: 60px; max-height: 60px; object-fit: contain;">
                            </div>
                            <a href="jobs.php?company=<?php echo $company['id']; ?>">
                                <h3><?php echo htmlspecialchars($company['name']); ?></h3>
                            </a>
                            <p><span><?php echo $company['active_jobs']; ?></span> Available position<?php echo $company['active_jobs'] == 1 ? '' : 's'; ?></p>
                            <?php if ($company['description']): ?>
                                <div class="company-description mt-2">
                                    <small><?php echo substr(htmlspecialchars($company['description']), 0, 100); ?>...</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- job_searcing_wrap  -->
<div class="job_searcing_wrap overlay">
    <div class="container">
        <div class="row">
            <div class="col-lg-5 offset-lg-1 col-md-6">
                <div class="searching_text">
                    <h3>Looking for a Job?</h3>
                    <p>We provide instant access to thousands of job opportunities with quick approval</p>
                    <a href="jobs.php" class="boxed-btn3">Browse Job</a>
                </div>
            </div>
            <div class="col-lg-5 offset-lg-1 col-md-6">
                <div class="searching_text">
                    <h3>Looking for an Expert?</h3>
                    <p>Post your job and find the perfect candidate for your company</p>
                    <?php if (auth()): ?>
                        <?php 
                        $user_type_id = $_SESSION['user']['user_type_id'] ?? 0;
                        if ($user_type_id == 2 || $user_type_id == 4): 
                            $companies = get_user_companies($_SESSION['user']['id']);
                            if (!empty($companies) && $companies[0]['verified']): ?>
                                <a href="post_job.php" class="boxed-btn3">Post a Job</a>
                            <?php else: ?>
                                <a href="register_company.php" class="boxed-btn3">Register Company</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="register.php?type=employer" class="boxed-btn3">Become an Employer</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="register.php?type=employer" class="boxed-btn3">Post a Job</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- job_searcing_wrap end  -->

<!-- testimonial_area  -->
<div class="testimonial_area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section_title text-center mb-40">
                    <h3>Testimonials</h3>
                </div>
            </div>
            <div class="col-xl-12">
                <div class="testmonial_active owl-carousel">
                    <?php 
                    $testimonials = $DB->query("SELECT * FROM testimonials ORDER BY id DESC LIMIT 3")->fetchAll();
                    if (empty($testimonials)): ?>
                        <div class="single_carousel">
                            <div class="row">
                                <div class="col-lg-11">
                                    <div class="single_testmonial d-flex align-items-center">
                                        <div class="thumb">
                                            <img src="img/testmonial/author.png" alt="Author">
                                            <div class="quote_icon">
                                                <i class="Flaticon flaticon-quote"></i>
                                            </div>
                                        </div>
                                        <div class="info">
                                            <p>"Working in conjunction with humanitarian aid agencies, we have supported programmes to help alleviate human suffering through animal welfare when people might depend on livestock as their only source of income or food."</p>
                                            <span>- Micky Mouse, CEO at Disney</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($testimonials as $testimonial): ?>
                            <div class="single_carousel">
                                <div class="row">
                                    <div class="col-lg-11">
                                        <div class="single_testmonial d-flex align-items-center">
                                            <div class="thumb">
                                                <img src="<?php echo htmlspecialchars($testimonial['author_image'] ?? 'img/testmonial/author.png'); ?>" alt="<?php echo htmlspecialchars($testimonial['author_name']); ?>">
                                                <div class="quote_icon">
                                                    <i class="Flaticon flaticon-quote"></i>
                                                </div>
                                            </div>
                                            <div class="info">
                                                <p>"<?php echo htmlspecialchars($testimonial['content']); ?>"</p>
                                                <span>- <?php echo htmlspecialchars($testimonial['author_name']); ?><?php echo $testimonial['position'] ? ', ' . htmlspecialchars($testimonial['position']) : ''; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /testimonial_area  -->

<script>
$(document).ready(function() {
    // Инициализация слайдеров
    if ($('.candidate_active').length) {
        $('.candidate_active').owlCarousel({
            loop: true,
            margin: 20,
            nav: true,
            dots: false,
            responsive: {
                0: { items: 1 },
                576: { items: 2 },
                768: { items: 3 },
                992: { items: 4 },
                1200: { items: 5 }
            }
        });
    }
    
    if ($('.testmonial_active').length) {
        $('.testmonial_active').owlCarousel({
            loop: true,
            margin: 0,
            items: 1,
            autoplay: true,
            nav: true,
            dots: false,
            autoplayTimeout: 5000,
            smartSpeed: 1000
        });
    }
    
    // Сохранение вакансий
    $('.save-job').click(function(e) {
        e.preventDefault();
        var jobId = $(this).data('job-id');
        var heartIcon = $(this).find('i');
        
        $.ajax({
            url: 'ajax_save_job.php',
            type: 'POST',
            data: { job_id: jobId },
            success: function(response) {
                var data = JSON.parse(response);
                if (data.success) {
                    if (data.action === 'saved') {
                        heartIcon.removeClass('ti-heart').addClass('ti-heart-broken');
                        $(this).addClass('saved');
                    } else {
                        heartIcon.removeClass('ti-heart-broken').addClass('ti-heart');
                        $(this).removeClass('saved');
                    }
                }
            }
        });
    });
    
    // Автозаполнение поиска
    $('#search_keyword').autocomplete({
        source: function(request, response) {
            $.ajax({
                url: 'ajax_search_suggestions.php',
                type: 'GET',
                data: { term: request.term },
                success: function(data) {
                    response(JSON.parse(data));
                }
            });
        },
        minLength: 2
    });
});
</script>

<?php jobboard_footer(); ?>