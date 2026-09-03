
<style>
*{ box-sizing:border-box; }

body{
    margin:0;
    background:#f8fafc;
    font-family:'Segoe UI', sans-serif;
    color:#1f2937;
}

.content{
    margin-left:290px;
    width:calc(100% - 290px);
    padding:30px 24px;
}

/* HEADER */
.dashboard-header{
    background:linear-gradient(135deg,#f97316,#ea580c);
    color:#fff;
    padding:25px;
    border-radius:18px;
    margin-bottom:25px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.dashboard-header-content h2{
    margin:0;
    font-size:26px;
    font-weight:700;
}

.dashboard-header-content p{
    margin:5px 0 0;
    font-size:14px;
}

.dashboard-header h2{
    margin:0;
    font-size:26px;
    font-weight:700;
}

.dashboard-header p{
    margin:5px 0 0;
    font-size:14px;
}

.view-offers-btn{
    padding:10px 16px;
    background:#fff;
    color:#f97316;
    border:none;
    border-radius:8px;
    font-size:14px;
    font-weight:600;
    cursor:pointer;
    transition:all 0.3s ease;
    white-space:nowrap;
    text-decoration:none;
    display:inline-block;
}

.view-offers-btn:hover{
    background:#fff7ed;
    transform:translateY(-2px);
    box-shadow:0 4px 12px rgba(255,255,255,0.3);
}

/* CARDS */
.summary-cards{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:16px;
    margin-bottom:25px;
}

.summary-card{
    background:#fff;
    border-radius:16px;
    padding:20px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
    display:flex;
    align-items:center;
    gap:12px;
}

.icon{
    width:40px;
    height:40px;
    border-radius:10px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:18px;
}

.orange{ background:#fff7ed; color:#f97316; }

.card-value{
    font-size:24px;
    font-weight:700;
}

.card-label{
    font-size:13px;
    color:#6b7280;
}

/* PANELS */
.dashboard-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:16px;
}

.panel{
    background:#fff;
    border-radius:16px;
    padding:22px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}

.panel-title{
    font-weight:700;
    margin-bottom:15px;
}

.status-row{
    display:flex;
    justify-content:space-between;
    margin-bottom:10px;
    font-size:14px;
}

.red{ color:#ef4444; }
.green{ color:#16a34a; }

.big{
    font-size:30px;
    font-weight:700;
    margin-bottom:5px;
}

.panel-text{
    font-size:13px;
    color:#6b7280;
}

/* BUTTON */
.view-btn{
    margin-top:12px;
    display:flex;
    justify-content:center;
    align-items:center;
    height:36px;
    border-radius:8px;
    background:#fff7ed;
    color:#f97316;
    text-decoration:none;
    font-weight:600;
    font-size:13px;
    transition:.3s;
}

.view-btn:hover{
    background:#f97316;
    color:#fff;
}

/* RESPONSIVE */
@media(max-width:1200px){
    .summary-cards{
        grid-template-columns:repeat(3,1fr);
    }
}

@media(max-width:1000px){
    .summary-cards{
        grid-template-columns:repeat(2,1fr);
    }
}

@media(max-width:768px){
    .content{
        margin-left:0;
        width:100%;
    }

    .summary-cards,
    .dashboard-grid{
        grid-template-columns:1fr;
    }

    .dashboard-header{
        flex-direction:column;
        align-items:flex-start;
        gap:15px;
    }

    .view-offers-btn{
        align-self:flex-start;
    }
}
</style>

<div class="content">

<div class="dashboard-header">
    <div class="dashboard-header-content">
        <h2>Welcome, <?php 
echo htmlspecialchars($fullname);
?></h2>
        <p>Manage your applications and track your progress.</p>
    </div>

</div>

<div class="summary-cards">

    <div class="summary-card">
        <div class="icon orange">▣</div>
        <div>
            <div class="card-value"><?php 
echo number_format($totalApplications);
?></div>
            <div class="card-label">Applications</div>
        </div>
    </div>

    <div class="summary-card">
        <div class="icon orange">◷</div>
        <div>
            <div class="card-value"><?php 
echo number_format($pendingApplications);
?></div>
            <div class="card-label">Pending</div>
        </div>
    </div>

    <div class="summary-card">
        <div class="icon orange">☷</div>
        <div>
            <div class="card-value"><?php 
echo number_format($upcomingInterviews);
?></div>
            <div class="card-label">Interviews</div>
        </div>
    </div>

    <div class="summary-card">
        <div class="icon orange">🎁</div>
        <div>
            <div class="card-value"><?php 
echo number_format($totalJobOffers);
?></div>
            <div class="card-label">Job Offers</div>
        </div>
    </div>

    <div class="summary-card">
        <div class="icon orange">✓</div>
        <div>
            <div class="card-value"><?php 
echo number_format($acceptedJobOffers);
?></div>
            <div class="card-label">Accepted</div>
        </div>
    </div>

    <div class="summary-card">
        <div class="icon orange">⌛</div>
        <div>
            <div class="card-value"><?php 
echo number_format($pendingJobOffers);
?></div>
            <div class="card-label">Pending Offers</div>
        </div>
    </div>

</div>

<div class="dashboard-grid">

    <div class="panel">
        <div class="panel-title">Applications</div>

        <div class="status-row">
            <span>Pending</span>
            <strong><?php 
echo number_format($pendingApplications);
?></strong>
        </div>

        <div class="status-row">
            <span>Hired</span>
            <strong class="green"><?php 
echo number_format($hiredApplications);
?></strong>
        </div>

        <div class="status-row">
            <span>Rejected</span>
            <strong class="red"><?php 
echo number_format($rejectedApplications);
?></strong>
        </div>

        <a href="<?php 
echo \url('');
?>/alumni/my_applications.php" class="view-btn">
            View all →
        </a>
    </div>

    <div class="panel">
        <div class="panel-title">Upcoming Interviews</div>

        <div class="big"><?php 
echo number_format($upcomingInterviews);
?></div>
        <div class="panel-text">Scheduled interviews</div>

        <a href="<?php 
echo \url('');
?>/alumni/my_applications.php" class="view-btn">
            View all →
        </a>
    </div>

    <div class="panel">
        <div class="panel-title">Job Offers</div>

        <div class="status-row">
            <span>Accepted</span>
            <strong class="green"><?php 
echo number_format($acceptedJobOffers);
?></strong>
        </div>

        <div class="status-row">
            <span>Pending</span>
            <strong><?php 
echo number_format($pendingJobOffers);
?></strong>
        </div>

        <div class="status-row">
            <span>Declined</span>
            <strong class="red"><?php 
echo number_format($declinedJobOffers);
?></strong>
        </div>

        <a href="<?php 
echo \url('');
?>/alumni/job_offers.php" class="view-btn">
            View all →
        </a>
    </div>

</div>

</div>

<?php 
echo \gc_partial('footer', \get_defined_vars());