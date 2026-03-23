<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Applied = 'applied';
    case Shortlisted = 'shortlisted';
    case Interview = 'interview';
    case Rejected = 'rejected';
    case Hired = 'hired';
}
