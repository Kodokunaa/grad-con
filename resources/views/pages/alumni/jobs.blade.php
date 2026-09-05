
<style>
*{
    box-sizing:border-box;
}

body{
    background:#f8fafc;
    overflow-x:hidden;
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.content{
    margin-left:290px;
    width:calc(100% - 290px);
    min-height:100vh;
    padding:32px 28px;
}

/* HERO */
.jobs-hero{
    background:linear-gradient(135deg,#f97316,#ea580c);
    border-radius:22px;
    padding:28px;
    color:#ffffff;
    margin-bottom:22px;
    box-shadow:0 12px 28px rgba(249,115,22,0.22);
}

.page-title{
    font-size:30px;
    font-weight:800;
    margin:0 0 8px 0;
}

.page-subtitle{
    margin:0;
    font-size:15px;
    opacity:.95;
}

/* FILTER BAR */
.filter-card{
    background:#ffffff;
    border:1px solid #e5e7eb;
    border-radius:18px;
    padding:18px;
    margin-bottom:22px;
    box-shadow:0 6px 18px rgba(0,0,0,0.05);
}

.filter-top{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:14px;
    margin-bottom:14px;
}

.course-badge{
    display:inline-flex;
    align-items:center;
    background:#fff7ed;
    color:#ea580c;
    border:1px solid #fdba74;
    padding:8px 13px;
    border-radius:999px;
    font-size:13px;
    font-weight:700;
}

.job-count{
    font-size:13px;
    color:#6b7280;
    font-weight:600;
}

.search-form{
    display:flex;
    gap:10px;
    width:100%;
}

.search-input-wrap{
    position:relative;
    flex:1;
}

.search-icon{
    position:absolute;
    left:14px;
    top:50%;
    transform:translateY(-50%);
    color:#9ca3af;
    font-size:15px;
}

.search-input{
    width:100%;
    border:1px solid #d1d5db;
    border-radius:12px;
    padding:13px 14px 13px 40px;
    font-size:14px;
    outline:none;
    transition:.25s ease;
}

.search-input:focus{
    border-color:#f97316;
    box-shadow:0 0 0 4px rgba(249,115,22,0.14);
}

.search-btn,
.clear-btn{
    border-radius:12px;
    padding:13px 18px;
    font-size:14px;
    font-weight:700;
    text-decoration:none;
    border:none;
    cursor:pointer;
    transition:.25s ease;
    white-space:nowrap;
}

.search-btn{
    background:#f97316;
    color:#ffffff;
}

.search-btn:hover{
    background:#ea580c;
}

.clear-btn{
    background:#fff7ed;
    color:#ea580c;
    border:1px solid #fdba74;
}

.clear-btn:hover{
    background:#f97316;
    color:#ffffff;
}

.search-result-text{
    margin-top:12px;
    color:#6b7280;
    font-size:13px;
}

/* JOB GRID */
.jobs-grid{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:18px;
}

.job-card{
    background:#ffffff;
    border:1px solid #e5e7eb;
    border-radius:20px;
    padding:24px;
    box-shadow:0 6px 18px rgba(0,0,0,0.05);
    transition:.25s ease;
    height:100%;
    display:flex;
    flex-direction:column;
}

.job-card:hover{
    transform:translateY(-4px);
    box-shadow:0 14px 28px rgba(0,0,0,0.08);
    border-color:#fdba74;
}

.card-header{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:12px;
    margin-bottom:14px;
}

.match-badge{
    display:inline-flex;
    align-items:center;
    background:#fff7ed;
    color:#ea580c;
    border:1px solid #fdba74;
    padding:6px 11px;
    border-radius:999px;
    font-size:12px;
    font-weight:700;
}

.job-title{
    font-size:20px;
    font-weight:800;
    color:#111827;
    margin:0 0 8px 0;
    line-height:1.3;
}

.job-company{
    color:#4b5563;
    font-size:14px;
    margin-bottom:8px;
    font-weight:600;
}

.job-meta{
    display:flex;
    flex-wrap:wrap;
    gap:8px;
    margin-bottom:14px;
}

.meta-pill{
    background:#f9fafb;
    border:1px solid #e5e7eb;
    color:#6b7280;
    padding:6px 10px;
    border-radius:999px;
    font-size:12px;
    font-weight:600;
}

.job-description{
    color:#4b5563;
    font-size:14px;
    line-height:1.6;
    margin-bottom:20px;
    flex:1;
}

.card-footer{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    border-top:1px solid #f3f4f6;
    padding-top:16px;
}

.btn-orange{
    background:#f97316;
    color:#ffffff;
    text-decoration:none;
    border:none;
    padding:11px 18px;
    border-radius:12px;
    font-size:14px;
    font-weight:700;
    transition:.25s ease;
    display:inline-flex;
    align-items:center;
    justify-content:center;
}

.btn-orange:hover{
    background:#16a34a;
    color:#ffffff;
}

.view-details{
    color:#6b7280;
    font-size:13px;
    font-weight:600;
}

/* EMPTY */
.empty-card{
    background:#ffffff;
    border:1px solid #e5e7eb;
    border-radius:18px;
    padding:28px;
    color:#6b7280;
    box-shadow:0 6px 18px rgba(0,0,0,0.05);
    text-align:center;
}

.empty-title{
    color:#111827;
    font-weight:800;
    font-size:18px;
    margin-bottom:6px;
}

.back-link{
    margin-top:24px;
    display:inline-flex;
    color:#f97316;
    text-decoration:none;
    font-weight:700;
    font-size:14px;
    transition:.25s ease;
}

.back-link:hover{
    color:#16a34a;
}

/* RESPONSIVE */
@media(max-width:991.98px){
    .content{
        margin-left:0;
        width:100%;
        padding:22px 16px;
    }

    .page-title{
        font-size:25px;
    }

    .jobs-grid{
        grid-template-columns:1fr;
    }
}

@media(max-width:576px){
    .jobs-hero{
        padding:22px;
    }

    .filter-top{
        align-items:flex-start;
        flex-direction:column;
    }

    .search-form{
        flex-direction:column;
    }

    .search-btn,
    .clear-btn{
        width:100%;
        text-align:center;
    }

    .card-footer{
        flex-direction:column;
        align-items:stretch;
    }

    .btn-orange{
        width:100%;
    }
}
</style>

<div class="content">

    <div class="jobs-hero">
        <h4 class="page-title">Open Jobs</h4>
        <p class="page-subtitle">
            Browse available job opportunities and find the position that matches your skills.
        </p>
    </div>

    <div class="filter-card">

        <div class="filter-top">
            <div>
                <?php 
if ($alumniCourse !== '') {
    ?>
                    <div class="course-badge">
                        Your course: <?php 
    echo htmlspecialchars($alumniCourse);
    ?>
                    </div>
                <?php 
}
?>
            </div>

            <div class="job-count">
                <?php 
echo count($jobs);
?> open job(s) found
            </div>
        </div>

        <form method="GET" action="" class="search-form">
            <div class="search-input-wrap">
                <span class="search-icon">⌕</span>
                <input 
                    type="text" 
                    name="search" 
                    class="search-input"
                    placeholder="Search by job title, company, location, job type, or keyword..."
                    value="<?php 
echo htmlspecialchars($search);
?>"
                >
            </div>

            <button type="submit" class="search-btn">Search</button>

            <?php 
if ($search !== '') {
    ?>
                <a href="<?php 
    echo \url('');
    ?>/alumni/jobs" class="clear-btn">Clear</a>
            <?php 
}
?>
        </form>

        <?php 
if ($search !== '') {
    ?>
            <div class="search-result-text">
                Showing results for: <strong><?php 
    echo htmlspecialchars($search);
    ?></strong>
            </div>
        <?php 
}
?>

    </div>

    <?php 
if (count($jobs) === 0) {
    ?>
        <div class="empty-card">
            <div class="empty-title">No jobs found</div>
            <?php 
    if ($search !== '') {
        ?>
                No jobs matched your search for "<?php 
        echo htmlspecialchars($search);
        ?>".
            <?php 
    } else {
        ?>
                No open jobs available yet.
            <?php 
    }
    ?>
        </div>
    <?php 
} else {
    ?>

        <div class="jobs-grid">
            <?php 
    foreach ($jobs as $j) {
        ?>
                <div class="job-card">

                    <div class="card-header">
                        <div class="match-badge">Open for All Alumni</div>
                    </div>

                    <h3 class="job-title">
                        <?php 
        echo htmlspecialchars($j['title']);
        ?>
                    </h3>

                    <div class="job-company">
                        <?php 
        echo htmlspecialchars($j['company']);
        ?>
                    </div>

                    <div class="job-meta">
                        <?php 
        if (!empty($j['location'])) {
            ?>
                            <span class="meta-pill">
                                <?php 
            echo htmlspecialchars($j['location']);
            ?>
                            </span>
                        <?php 
        }
        ?>

                        <?php 
        if (!empty($j['job_type'])) {
            ?>
                            <span class="meta-pill">
                                <?php 
            echo htmlspecialchars($j['job_type']);
            ?>
                            </span>
                        <?php 
        }
        ?>
                    </div>

                    <div class="job-description">
                        <?php 
        echo htmlspecialchars(mb_strimwidth($j['description'], 0, 160, "..."));
        ?>
                    </div>

                    <div class="card-footer">
                        <span class="view-details">Review the details before applying</span>

                        <a class="btn-orange" href="<?php 
        echo \url('');
        ?>/alumni/apply?job_id=<?php 
        echo (int) $j['id'];
        ?>">
                            Apply Now
                        </a>
                    </div>

                </div>
            <?php 
    }
    ?>
        </div>

    <?php 
}
?>

    <a class="back-link" href="<?php 
echo \url('');
?>/alumni/feed">← Back to Feed</a>

</div>

<?php 
echo view('partials.footer', \get_defined_vars());
