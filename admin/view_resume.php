<?php
require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/db.php";
require_admin();

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function findResumeValue(array $app): string
{
    $candidates = [
        'resume',
        'resume_path',
        'resume_file',
        'cv',
        'cv_file',
        'file',
        'attachment'
    ];

    foreach ($candidates as $key) {
        if (isset($app[$key]) && trim((string)$app[$key]) !== '') {
            return trim((string)$app[$key]);
        }
    }
    return '';
}

function resolveResumePath(string $resume): array
{
    $resume = str_replace("\\", "/", $resume);
    $resume = str_replace("../", "", $resume);
    $resume = ltrim($resume, "/");

    $baseName = basename($resume);

    $possiblePaths = [
        __DIR__ . "/../" . $resume,
        __DIR__ . "/../uploads/" . $baseName,
        __DIR__ . "/../uploads/resumes/" . $baseName,
        dirname(__DIR__) . "/uploads/" . $baseName,
        dirname(__DIR__) . "/uploads/resumes/" . $baseName,
        dirname(__DIR__) . "/" . $resume
    ];

    foreach ($possiblePaths as $path) {
        if (is_file($path)) {
            return [realpath($path), $possiblePaths];
        }
    }

    return ['', $possiblePaths];
}

$app_id = (int)($_GET['app_id'] ?? 0);
if ($app_id <= 0) {
    die("Invalid application.");
}

$stmt = $pdo->prepare("SELECT * FROM applications WHERE id = ? LIMIT 1");
$stmt->execute([$app_id]);
$app = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$app) {
    die("Application not found.");
}

$resume = findResumeValue($app);
if ($resume === '') {
    die("Resume not found in database.");
}

list($filePath, $checkedPaths) = resolveResumePath($resume);

if ($filePath === '' || !is_file($filePath)) {
    echo "<h3>Resume file not found</h3>";
    echo "<p><strong>Saved value:</strong> " . h($resume) . "</p>";
    echo "<pre>";
    print_r($checkedPaths);
    echo "</pre>";
    exit;
}

$ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

if ($ext !== 'pdf') {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Resume Viewer</title>
        <style>
            body{
                font-family:Arial,sans-serif;
                background:#f8fafc;
                padding:30px;
                color:#111827;
            }
            .box{
                background:#fff;
                border:1px solid #e5e7eb;
                border-radius:16px;
                padding:24px;
                max-width:700px;
                margin:auto;
                box-shadow:0 4px 14px rgba(0,0,0,0.05);
            }
            .title{
                font-size:22px;
                font-weight:700;
                margin-bottom:12px;
                color:#b91c1c;
            }
            .text{
                color:#374151;
                line-height:1.6;
                font-size:15px;
            }
            .note{
                margin-top:14px;
                color:#6b7280;
                font-size:14px;
            }
        </style>
    </head>
    <body>
        <div class='box'>
            <div class='title'>Preview not supported</div>
            <div class='text'>
                The uploaded resume is <strong>." . h($ext) . "</strong>.<br><br>
                Only <strong>PDF</strong> resumes can be previewed in the browser.
            </div>
            <div class='note'>
                Please upload the resume again in PDF format.
            </div>
        </div>
    </body>
    </html>";
    exit;
}

if (isset($_GET['raw']) && $_GET['raw'] === '1') {
    header("Content-Type: application/pdf");
    header("Content-Disposition: inline; filename=\"" . basename($filePath) . "\"");
    header("Content-Length: " . filesize($filePath));
    header("X-Content-Type-Options: nosniff");
    readfile($filePath);
    exit;
}

$rawUrl = "view_resume.php?app_id=" . $app_id . "&raw=1";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resume Viewer</title>
    <style>
        html, body {
            margin: 0;
            height: 100%;
            background: #f3f4f6;
        }
        .viewer-wrap {
            width: 100%;
            height: 100vh;
            background: #fff;
        }
        iframe {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
        }
    </style>
</head>
<body>
    <div class="viewer-wrap">
        <iframe src="<?php echo h($rawUrl); ?>"></iframe>
    </div>
</body>
</html>