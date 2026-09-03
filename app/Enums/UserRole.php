<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Alumni = 'alumni';
    case Employer = 'employer';
    case AlumniOfficer = 'alumni_officer';
}
