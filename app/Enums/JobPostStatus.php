<?php

namespace App\Enums;

enum JobPostStatus: string
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case Published = 'published';
    case Closed = 'closed';
    case Rejected = 'rejected';
}
