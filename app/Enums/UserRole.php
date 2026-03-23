<?php

namespace App\Enums;

enum UserRole: string
{
    case JobSeeker = 'job_seeker';
    case Company = 'company';
    case SuperAdmin = 'super_admin';
}
