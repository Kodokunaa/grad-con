<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Support\PageResponse;
use Illuminate\Http\Request;

final class AlumniJobOffersController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role();
            if (\gc_context()->session['user']['role'] !== 'alumni') {
                \gc_header('Location: '.\url('').'/');
                \gc_finish();
            }
            $id = (int) \gc_context()->session['user']['id'];
            $error = '';
            $msg = '';
            // Handle accept/decline from email links
            $emailToken = trim((string) (\gc_context()->query['accept'] ?? \gc_context()->query['decline'] ?? ''));
            if ($emailToken !== '') {
                $action = isset(\gc_context()->query['accept']) ? 'accept' : 'decline';
                try {
                    // Get the offer by token
                    $offerStmt = $pdo->prepare('SELECT * FROM job_offers WHERE offer_token = ? LIMIT 1');
                    $offerStmt->execute([$emailToken]);
                    $offer = $offerStmt->fetch(\PDO::FETCH_ASSOC);
                    if (! $offer) {
                        $error = 'Offer not found. It may have expired.';
                    } elseif ($offer['alumni_id'] !== $id) {
                        $error = 'This offer was not sent to you.';
                    } elseif ($offer['status'] !== 'sent') {
                        $error = 'This offer has already been '.($offer['status'] === 'accepted' ? 'accepted' : 'declined').'.';
                    } else {
                        $newStatus = $action === 'accept' ? 'accepted' : 'declined';
                        $timestampCol = $action === 'accept' ? 'accepted_at' : 'declined_at';
                        $updateStmt = $pdo->prepare("UPDATE job_offers SET status = ?, {$timestampCol} = NOW() WHERE id = ?");
                        $updateStmt->execute([$newStatus, $offer['id']]);
                        if ($action === 'accept') {
                            $msg = '✓ Offer accepted successfully! Please wait for the employer to set an interview schedule.';
                        } else {
                            $msg = 'Offer declined successfully!';
                        }
                        // Send notification email to employer if accepted
                        if ($action === 'accept') {
                            \gc_alumni_job_offers_send_offer_acceptance_notification($pdo, $offer, \gc_context()->session['user']);
                        }
                    }
                } catch (\Throwable $e) {
                    if ($e instanceof PageResponse) {
                        throw $e;
                    }
                    $error = 'An error occurred: '.\gc_public_error($e);
                }
            }
            // Handle accept/decline action from form submission
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST' && isset(\gc_context()->post['offer_action'])) {
                $offerId = (int) (\gc_context()->post['offer_id'] ?? 0);
                $action = trim((string) (\gc_context()->post['offer_action'] ?? ''));
                if (! in_array($action, ['accept', 'decline'], true)) {
                    $error = 'Invalid action.';
                } elseif ($offerId <= 0) {
                    $error = 'Invalid offer ID.';
                } else {
                    try {
                        // Get the offer
                        $offerStmt = $pdo->prepare('SELECT * FROM job_offers WHERE id = ? AND alumni_id = ? LIMIT 1');
                        $offerStmt->execute([$offerId, $id]);
                        $offer = $offerStmt->fetch(\PDO::FETCH_ASSOC);
                        if (! $offer) {
                            $error = 'Offer not found.';
                        } elseif ($offer['status'] !== 'sent') {
                            $error = 'This offer has already been '.($offer['status'] === 'accepted' ? 'accepted' : 'declined').'.';
                        } else {
                            $newStatus = $action === 'accept' ? 'accepted' : 'declined';
                            $timestampCol = $action === 'accept' ? 'accepted_at' : 'declined_at';
                            $updateStmt = $pdo->prepare("UPDATE job_offers SET status = ?, {$timestampCol} = NOW() WHERE id = ?");
                            $updateStmt->execute([$newStatus, $offerId]);
                            if ($action === 'accept') {
                                $msg = '✓ Offer accepted successfully! Please wait for the employer to set an interview schedule.';
                            } else {
                                $msg = 'Offer declined successfully!';
                            }
                            // Send notification email to employer
                            if ($action === 'accept') {
                                \gc_alumni_job_offers_send_offer_acceptance_notification($pdo, $offer, \gc_context()->session['user']);
                            }
                        }
                    } catch (\Throwable $e) {
                        if ($e instanceof PageResponse) {
                            throw $e;
                        }
                        $error = 'An error occurred: '.\gc_public_error($e);
                    }
                }
            }
            // Fetch all offers for this alumni
            $offersStmt = $pdo->prepare("\r\n    SELECT jo.*, u.fullname as employer_name, u.email as employer_email\r\n    FROM job_offers jo\r\n    JOIN users u ON jo.employer_id = u.id\r\n    WHERE jo.alumni_id = ?\r\n    ORDER BY jo.created_at DESC\r\n");
            $offersStmt->execute([$id]);
            $offers = $offersStmt->fetchAll(\PDO::FETCH_ASSOC);
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('alumni_sidebar', \get_defined_vars());

            return $this->pageView('pages.alumni.job_offers', get_defined_vars());
        });
    }
}
