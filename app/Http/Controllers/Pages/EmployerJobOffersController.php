<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Support\PageResponse;
use Illuminate\Http\Request;

final class EmployerJobOffersController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('employer');
            $id = (int) \gc_context()->session['user']['id'];
            $error = '';
            $msg = '';
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST' && isset(\gc_context()->post['offer_action'], \gc_context()->post['offer_id'])) {
                $offerId = (int) (\gc_context()->post['offer_id'] ?? 0);
                $offerAction = trim((string) (\gc_context()->post['offer_action'] ?? ''));
                if ($offerId > 0 && in_array($offerAction, ['done', 'remove'], true)) {
                    try {
                        $offerStmt = $pdo->prepare('SELECT * FROM job_offers WHERE id = ? AND employer_id = ? LIMIT 1');
                        $offerStmt->execute([$offerId, $id]);
                        $offer = $offerStmt->fetch(\PDO::FETCH_ASSOC);
                        if ($offer) {
                            if ($offerAction === 'done') {
                                try {
                                    \gc_context()->schemaChange($pdo, "ALTER TABLE job_offers MODIFY COLUMN status ENUM('sent', 'accepted', 'declined', 'expired', 'done') DEFAULT 'sent'");
                                } catch (\PDOException $e) {
                                    if ($e instanceof PageResponse) {
                                        throw $e;
                                    }
                                    // ignore if column already includes done or cannot be altered
                                }
                                $updateOffer = $pdo->prepare("UPDATE job_offers SET status = 'done' WHERE id = ? AND employer_id = ?");
                                $updateOffer->execute([$offerId, $id]);
                                $msg = 'Offer marked as done.';
                            } elseif ($offerAction === 'remove') {
                                $deleteOffer = $pdo->prepare('DELETE FROM job_offers WHERE id = ? AND employer_id = ?');
                                $deleteOffer->execute([$offerId, $id]);
                                $msg = 'Offer removed successfully.';
                            }
                        } else {
                            $error = 'Offer not found or you do not have permission.';
                        }
                    } catch (\Throwable $e) {
                        if ($e instanceof PageResponse) {
                            throw $e;
                        }
                        $error = 'An error occurred: '.\gc_public_error($e);
                    }
                }
            }
            // Fetch all job offers sent by this employer
            $offersStmt = $pdo->prepare("\r\n    SELECT jo.*, u.fullname as alumni_name, u.email as alumni_email, u.course\r\n    FROM job_offers jo\r\n    JOIN users u ON jo.alumni_id = u.id\r\n    WHERE jo.employer_id = ?\r\n    ORDER BY jo.created_at DESC\r\n");
            $offersStmt->execute([$id]);
            $offers = $offersStmt->fetchAll(\PDO::FETCH_ASSOC);
            // Calculate statistics
            $stats = ['total' => 0, 'sent' => 0, 'accepted' => 0, 'declined' => 0, 'done' => 0, 'expired' => 0];
            foreach ($offers as $offer) {
                $stats['total']++;
                if (isset($stats[$offer['status']])) {
                    $stats[$offer['status']]++;
                }
            }
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('employer_sidebar', \get_defined_vars());

            return $this->pageView('pages.employer.job_offers', get_defined_vars());
        });
    }
}
