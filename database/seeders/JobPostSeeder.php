<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\CompanyVerificationStatus;
use App\Enums\JobPostStatus;
use App\Models\Company;
use App\Models\JobPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class JobPostSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create a Company User
        $user = User::query()->updateOrCreate(
            ['email' => 'employer@corp.com'],
            [
                'name' => 'Employer Corp Admin',
                'password' => Hash::make('password'),
                'phone' => '9876543211',
                'role' => UserRole::Company->value,
                'is_active' => true,
            ]
        );

        // 2. Create Company
        $company = Company::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'name' => 'Employer Corp Solutions',
                'company_kind' => 'company',
                'slug' => 'employer-corp-solutions',
                'gst_number' => '27ABCDE1234F1Z5',
                'industry' => 'Information Technology',
                'website' => 'https://employer-corp-solutions.com',
                'verification_status' => CompanyVerificationStatus::Verified,
                'state' => 'Karnataka',
                'district' => 'Bengaluru Urban',
                'city' => 'Bengaluru',
                'location' => 'Bengaluru, Karnataka',
            ]
        );

        // 3. Define Job Roles & Skills across Industries
        $jobsData = [
            // IT / Software
            [
                'title' => 'Software Developer',
                'industry_type' => 'software_engineering_it',
                'skills' => ['Java', 'Python', 'JavaScript', 'React', 'Node.js', 'SQL', 'Git'],
                'description' => 'We are seeking a Software Developer to design and develop scalable web applications.',
                'requirements' => 'Bachelor\'s in Computer Science, 1+ years of coding experience.',
            ],
            [
                'title' => 'Frontend Developer',
                'industry_type' => 'software_engineering_it',
                'skills' => ['HTML', 'CSS', 'JavaScript', 'React', 'Vue', 'Webpack', 'TailwindCSS'],
                'description' => 'Looking for a Frontend Developer to build clean, responsive, and intuitive web interfaces.',
                'requirements' => 'Proficiency in JavaScript frameworks, CSS layout models, and UI design principles.',
            ],
            [
                'title' => 'Backend Developer',
                'industry_type' => 'software_engineering_it',
                'skills' => ['Node.js', 'Express', 'Python', 'Django', 'PostgreSQL', 'Redis', 'Docker'],
                'description' => 'Join our team as a Backend Developer managing database architectures and API integrations.',
                'requirements' => 'Experience with REST APIs, databases, and microservices.',
            ],
            [
                'title' => 'Full Stack Developer',
                'industry_type' => 'software_engineering_it',
                'skills' => ['MongoDB', 'Express', 'React', 'Node.js', 'Git', 'AWS'],
                'description' => 'Seeking a Full Stack Developer capable of handling end-to-end development of features.',
                'requirements' => 'Ability to work across both frontend client side and backend server side logic.',
            ],
            [
                'title' => 'QA Tester',
                'industry_type' => 'software_engineering_it',
                'skills' => ['Selenium', 'Jest', 'Postman', 'Cypress', 'JIRA', 'Manual Testing'],
                'description' => 'Looking for a QA Tester to write test suites and ensure high-quality software releases.',
                'requirements' => 'Detail-oriented, strong debugging skills, experience with automation tools.',
            ],

            // Design / Creative
            [
                'title' => 'UI/UX Designer',
                'industry_type' => 'design_ux_creative',
                'skills' => ['Figma', 'Adobe XD', 'Photoshop', 'Wireframing', 'Prototyping', 'User Research'],
                'description' => 'Seeking a UI/UX Designer to design layout blueprints and create interactive user flows.',
                'requirements' => 'Stunning portfolio, proficiency in Figma/Adobe Creative suite.',
            ],

            // Sales / Business Development
            [
                'title' => 'Sales Executive',
                'industry_type' => 'sales_business_development',
                'skills' => ['Lead Generation', 'Communication', 'Negotiation', 'CRM', 'Cold Calling'],
                'description' => 'We are seeking a Sales Executive to acquire new customers and handle client relations.',
                'requirements' => 'Excellent communication and negotiation skills.',
            ],
            [
                'title' => 'Business Development Executive',
                'industry_type' => 'sales_business_development',
                'skills' => ['B2B Sales', 'Cold Calling', 'Presentation', 'Relationship Management'],
                'description' => 'Looking for a Business Development Executive to identify sales leads and pitch services.',
                'requirements' => 'Results-driven mindset and willingness to travel.',
            ],
            [
                'title' => 'Sales Manager',
                'industry_type' => 'sales_business_development',
                'skills' => ['Team Leadership', 'Sales Strategy', 'Account Management', 'Forecasting'],
                'description' => 'Seeking a Sales Manager to direct sales teams and implement strategic growth initiatives.',
                'requirements' => '3+ years experience in a sales lead or managerial role.',
            ],

            // Finance / Accounting
            [
                'title' => 'Accountant',
                'industry_type' => 'accountants',
                'skills' => ['Tally', 'GST', 'Excel', 'Bookkeeping', 'Tax Compliance'],
                'description' => 'Looking for an Accountant to manage daily ledger records and prepare tax files.',
                'requirements' => 'Degree in Commerce, familiarity with Tally Prime and Excel.',
            ],
            [
                'title' => 'Financial Analyst',
                'industry_type' => 'banking_finance',
                'skills' => ['Financial Modeling', 'Valuation', 'Excel', 'Python', 'Reporting'],
                'description' => 'Join our corporate finance team as a Financial Analyst to prepare growth forecast models.',
                'requirements' => 'Strong analytical skills, finance degree, Excel wizard.',
            ],
            [
                'title' => 'Auditor',
                'industry_type' => 'banking_finance',
                'skills' => ['Auditing', 'Compliance', 'Risk Assessment', 'Tax', 'Internal Controls'],
                'description' => 'Seeking an Auditor to examine financial reports for accuracy and compliance.',
                'requirements' => 'CPA or equivalent certification, 2+ years audit experience.',
            ],

            // BPO / Telecaller
            [
                'title' => 'BPO Telecaller',
                'industry_type' => 'bpo_telecaller',
                'skills' => ['Customer Support', 'Voice Process', 'Active Listening', 'English Fluency'],
                'description' => 'Hiring BPO Telecallers to handle customer queries and tele-calling campaigns.',
                'requirements' => 'Good verbal communication, high school graduate minimum.',
            ],
        ];

        // 4. Seed Job Posts
        foreach ($jobsData as $index => $jd) {
            $slug = Str::slug($jd['title']) . '-' . $index;
            JobPost::query()->updateOrCreate(
                [
                    'company_id' => $company->id,
                    'slug' => $slug,
                ],
                [
                    'title' => $jd['title'],
                    'location' => 'Bengaluru, Karnataka',
                    'employment_type' => 'full_time',
                    'experience_level' => 'mid_level',
                    'salary_min' => 30000,
                    'salary_max' => 60000,
                    'currency' => 'INR',
                    'description' => $jd['description'],
                    'requirements' => $jd['requirements'],
                    'skills' => $jd['skills'],
                    'status' => JobPostStatus::Published,
                    'industry_type' => $jd['industry_type'],
                    'published_at' => now(),
                ]
            );
        }
    }
}
