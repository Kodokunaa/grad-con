<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Support\PageResponse;
use Illuminate\Http\Request;

final class AdminForwardToCompanyController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('admin');
            $app_id = (int) (\gc_context()->query['app_id'] ?? 0);
            $stmt = $pdo->prepare("\r\n  SELECT a.*, u.fullname, j.title\r\n  FROM applications a\r\n  JOIN users u ON u.id=a.alumni_id\r\n  JOIN jobs j ON j.id=a.job_id\r\n  WHERE a.id=?\r\n");
            $stmt->execute([$app_id]);
            $app = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (! $app) {
                \gc_finish('Not found.');
            }
            $resumePath = \storage_path('app/private/files/uploads/resumes/'.$app['resume_file']);
            $msg = '';
            $error = '';
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST') {
                $companyEmail = \gc_context()->post['company_email'];
                try {
                    $mail = \gc_make_mailer();
                    $mail->addAddress($companyEmail);
                    $mail->Subject = 'Applicant Resume - '.$app['fullname'];
                    $mail->Body = "\r\n            <p>Please see attached resume of ".$app['fullname']."</p>\r\n            <p>Position: ".$app['title']."</p>\r\n        ";
                    $mail->addAttachment($resumePath);
                    $mail->send();
                    $msg = 'Resume sent to company!';
                } catch (\Exception $e) {
                    if ($e instanceof PageResponse) {
                        throw $e;
                    }
                    $error = 'Email failed.';
                }
            }
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.forward_to_company', get_defined_vars());
        });
    }
}
