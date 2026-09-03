<?php

namespace App\Enums;

enum OfferStatus: string
{
    case Sent = 'sent';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Expired = 'expired';
    case Done = 'done';
}
