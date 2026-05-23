<?php

namespace App\Support;

/**
 * Rich fake résumé payloads for dashboard template thumbnails (Naukri-style filled previews).
 * Shape matches {@see ResumeViewData::fromEnvelope()} output used by Blade HTML templates.
 */
final class ResumeHtmlDemoData
{
    public const VARIANT_COUNT = 4;

    /**
     * @return array<string, mixed>
     */
    public static function viewProfile(int $variant): array
    {
        $variant = max(0, min(self::VARIANT_COUNT - 1, $variant));

        return match ($variant) {
            0 => self::profile0(),
            1 => self::profile1(),
            2 => self::profile2(),
            default => self::profile3(),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private static function baseShell(
        string $fullName,
        string $title,
        string $photoUrl,
        string $mobile,
        string $email,
        string $location,
        string $summary,
        string $gender,
        array $skills,
        array $languages,
        array $certs,
        array $educationList,
        array $work,
        array $internships,
        array $projects,
    ): array {
        return [
            'full_name' => $fullName,
            'professional_title' => $title,
            'summary' => $summary,
            'mobile' => $mobile,
            'email' => $email,
            'photo_url' => $photoUrl,
            'location' => $location,
            'hometown' => 'Indore, India',
            'dob' => '1998-05-14',
            'gender' => $gender,
            'residing_in_india' => true,
            'highest_qualification' => 'Post Graduate',
            'skills' => $skills,
            'languages' => $languages,
            'certifications' => $certs,
            'graduation' => [
                'course' => 'B.Tech Computer Science',
                'college' => 'NIT Trichy',
                'score' => '8.4 CGPA',
            ],
            'class_12' => [
                'board' => 'CBSE',
                'medium' => 'English',
                'year' => '2016',
                'score' => '94.2%',
            ],
            'class_10' => [
                'board' => 'CBSE',
                'medium' => 'English',
                'year' => '2014',
                'score' => '10 CGPA',
            ],
            'education_list' => $educationList,
            'internships' => $internships,
            'projects' => $projects,
            'work_experience' => $work,
            'extra_sections' => [],
            'academic_achievements' => [
                'Dean’s List 2021',
                'Smart India Hackathon — Finalist',
            ],
            'awards_honors' => [
                'Employee of the Quarter — Q2 2024',
            ],
            'competitive_exam_results' => [
                'GATE CS — 412 score',
            ],
        ];
    }

    private static function profile0(): array
    {
        $photo = 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&h=200&fit=crop&auto=format';

        return self::baseShell(
            fullName: 'Amit Jain',
            title: 'Senior Software Engineer',
            photoUrl: $photo,
            mobile: '+91 98765 43210',
            email: 'amit.jain.demo@email.com',
            location: 'Bengaluru, Karnataka, India',
            summary: 'Full-stack engineer with 5+ years building scalable web platforms, microservices, and cloud-native systems. Passionate about clean architecture, mentoring interns, and shipping products used by millions.',
            gender: 'Male',
            skills: ['Java', 'Spring Boot', 'React', 'TypeScript', 'AWS', 'Docker', 'Kubernetes', 'PostgreSQL', 'Redis', 'System design'],
            languages: ['English — Fluent', 'Hindi — Native'],
            certs: ['AWS Solutions Architect — 2024', 'Oracle Java SE Professional'],
            educationList: [
                ['title' => 'M.Tech (Software Engineering)', 'institution' => 'IISc Bangalore', 'year' => '2021', 'marks' => '8.9 CGPA', 'mode' => 'Full time'],
                ['title' => 'B.Tech Computer Science', 'institution' => 'NIT Trichy', 'year' => '2019', 'marks' => '8.4 CGPA', 'mode' => 'Full time'],
                ['title' => 'Class XII (Science)', 'institution' => 'Delhi Public School', 'year' => '2015', 'marks' => '94%', 'mode' => 'Full time'],
                ['title' => 'Class X', 'institution' => 'Delhi Public School', 'year' => '2013', 'marks' => '10 CGPA', 'mode' => 'Full time'],
            ],
            work: [
                [
                    'heading' => 'JobAllocate — Senior Software Engineer',
                    'dates' => 'Bengaluru · 2022 – Present',
                    'body' => "• Led migration of monolith to microservices; cut p95 latency by 38%.\n• Mentored 4 engineers; drove RFC process for API versioning.\n• Built CI/CD pipelines with GitHub Actions and ArgoCD.",
                ],
                [
                    'heading' => 'FinStack Payments — Software Engineer II',
                    'dates' => 'Mumbai · 2019 – 2022',
                    'body' => "• Implemented PCI-aware payment orchestration in Java.\n• Shipped real-time reconciliation dashboards in React.",
                ],
            ],
            internships: [
                [
                    'heading' => 'CloudScale Labs — SDE Intern',
                    'dates' => 'Remote · Summer 2018',
                    'body' => "• Built internal CLI for AWS cost reporting.\n• Automated IAM policy audits with Python.",
                ],
            ],
            projects: [
                [
                    'heading' => 'Distributed rate limiter (open source)',
                    'dates' => '2023',
                    'body' => 'Token-bucket service in Go with Redis; 1.2k GitHub stars; used by 3 startups.',
                ],
                [
                    'heading' => 'Campus placement portal',
                    'dates' => '2018',
                    'body' => 'End-to-end portal for 40+ companies; React + Node; served 800 students.',
                ],
            ],
        );
    }

    private static function profile1(): array
    {
        $photo = 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=200&h=200&fit=crop&auto=format';

        return self::baseShell(
            fullName: 'Varsha Nair',
            title: 'Product Designer · UX',
            photoUrl: $photo,
            mobile: '+91 91234 55678',
            email: 'varsha.nair.demo@email.com',
            location: 'Hyderabad, Telangana, India',
            summary: 'Product designer focused on design systems, accessibility, and research-driven iteration. 4+ years shipping B2B SaaS flows, design QA, and handoff pipelines with engineering.',
            gender: 'Female',
            skills: ['Figma', 'Design systems', 'User research', 'Prototyping', 'WCAG 2.1', 'Design ops', 'Mentoring'],
            languages: ['English — Fluent', 'Malayalam — Native', 'Hindi — Conversational'],
            certs: ['Google UX Design Certificate', 'NN/g UX Certification'],
            educationList: [
                ['title' => 'M.Des Interaction Design', 'institution' => 'NID Ahmedabad', 'year' => '2020', 'marks' => 'Distinction', 'mode' => 'Full time'],
                ['title' => 'B.Des Product Design', 'institution' => 'MIT Institute of Design', 'year' => '2018', 'marks' => '8.7 CGPA', 'mode' => 'Full time'],
                ['title' => 'Class XII', 'institution' => 'Kendriya Vidyalaya', 'year' => '2014', 'marks' => '92%', 'mode' => 'Full time'],
                ['title' => 'Class X', 'institution' => 'Kendriya Vidyalaya', 'year' => '2012', 'marks' => '9.6 CGPA', 'mode' => 'Full time'],
            ],
            work: [
                [
                    'heading' => 'Orbit Health — Lead Product Designer',
                    'dates' => 'Hyderabad · 2021 – Present',
                    'body' => "• Owned clinician EHR workflows; reduced task time by 22% via usability tests.\n• Built Figma → code design tokens pipeline with Storybook.",
                ],
            ],
            internships: [
                [
                    'heading' => 'BrightApps Studio — UX Intern',
                    'dates' => 'Pune · 2019',
                    'body' => 'Redesigned onboarding; improved activation by 18% in A/B test.',
                ],
            ],
            projects: [
                [
                    'heading' => 'Inclusive form pattern library',
                    'dates' => '2022',
                    'body' => 'Open Figma kit + React primitives; adopted by 2 internal product lines.',
                ],
                [
                    'heading' => 'AR campus navigation (concept)',
                    'dates' => '2017',
                    'body' => 'Student project; user testing with 30 participants; NID showcase finalist.',
                ],
            ],
        );
    }

    private static function profile2(): array
    {
        $photo = 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=200&h=200&fit=crop&auto=format';

        return self::baseShell(
            fullName: 'Rohan Kapoor',
            title: 'Data Scientist',
            photoUrl: $photo,
            mobile: '+91 99887 76655',
            email: 'rohan.kapoor.demo@email.com',
            location: 'Gurugram, Haryana, India',
            summary: 'Applied ML engineer with strong Python and SQL. Experience in forecasting, experimentation, and MLOps on GCP. Enjoys translating ambiguous business questions into measurable metrics.',
            gender: 'Male',
            skills: ['Python', 'PyTorch', 'scikit-learn', 'SQL', 'BigQuery', 'Airflow', 'Statistics', 'A/B testing', 'LLM fine-tuning'],
            languages: ['English — Fluent', 'Hindi — Native', 'Punjabi — Native'],
            certs: ['TensorFlow Developer Certificate', 'DeepLearning.AI MLOps'],
            educationList: [
                ['title' => 'M.Sc Data Science', 'institution' => 'IIT Madras', 'year' => '2021', 'marks' => '9.1 CGPA', 'mode' => 'Full time'],
                ['title' => 'B.Tech Information Technology', 'institution' => 'DTU Delhi', 'year' => '2019', 'marks' => '8.1 CGPA', 'mode' => 'Full time'],
                ['title' => 'Class XII (Science)', 'institution' => 'Modern School Barakhamba', 'year' => '2015', 'marks' => '95%', 'mode' => 'Full time'],
                ['title' => 'Class X', 'institution' => 'Modern School Barakhamba', 'year' => '2013', 'marks' => '10 CGPA', 'mode' => 'Full time'],
            ],
            work: [
                [
                    'heading' => 'RetailNext AI — Data Scientist',
                    'dates' => 'Gurugram · 2021 – Present',
                    'body' => "• Demand forecasting models for 400+ stores; MAPE reduced by 11%.\n• Partnered with PMs on causal impact analysis for promotions.",
                ],
                [
                    'heading' => 'BankSphere — Analyst (Data)',
                    'dates' => 'Noida · 2019 – 2021',
                    'body' => "• Credit risk feature store in SQL + dbt.\n• Tableau dashboards for collections team.",
                ],
            ],
            internships: [
                [
                    'heading' => 'IISc summer research — NLP',
                    'dates' => '2018',
                    'body' => 'Low-resource POS tagging; co-authored workshop paper.',
                ],
            ],
            projects: [
                [
                    'heading' => 'Kaggle competition — top 5%',
                    'dates' => '2020',
                    'body' => 'Ensemble gradient boosting + neural nets for tabular churn.',
                ],
                [
                    'heading' => 'Streamlit revenue explorer',
                    'dates' => '2019',
                    'body' => 'Interactive SQL + charts for sales ops; deployed on Cloud Run.',
                ],
            ],
        );
    }

    private static function profile3(): array
    {
        $photo = 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=200&h=200&fit=crop&auto=format';

        return self::baseShell(
            fullName: 'Sneha Iyer',
            title: 'Mobile Engineer (Flutter)',
            photoUrl: $photo,
            mobile: '+91 98123 44556',
            email: 'sneha.iyer.demo@email.com',
            location: 'Chennai, Tamil Nadu, India',
            summary: 'Mobile engineer shipping consumer apps with Flutter and native bridges. Cares about performance budgets, offline-first UX, and crash-free sessions above 99.5%.',
            gender: 'Female',
            skills: ['Flutter', 'Dart', 'Kotlin', 'Swift', 'Firebase', 'REST APIs', 'Bloc', 'CI for mobile'],
            languages: ['English — Fluent', 'Tamil — Native', 'Hindi — Conversational'],
            certs: ['Associate Android Developer', 'Flutter & Dart Google certificate'],
            educationList: [
                ['title' => 'B.E Computer Science', 'institution' => 'Anna University CEG', 'year' => '2020', 'marks' => '8.6 CGPA', 'mode' => 'Full time'],
                ['title' => 'Class XII', 'institution' => 'PSBB KK Nagar', 'year' => '2016', 'marks' => '96%', 'mode' => 'Full time'],
                ['title' => 'Class X', 'institution' => 'PSBB KK Nagar', 'year' => '2014', 'marks' => '10 CGPA', 'mode' => 'Full time'],
                ['title' => 'Online — Algorithms', 'institution' => 'Coursera / Stanford', 'year' => '2019', 'marks' => 'Audit', 'mode' => 'Part time'],
            ],
            work: [
                [
                    'heading' => 'QuickMart Consumer App — Flutter Lead',
                    'dates' => 'Chennai · 2022 – Present',
                    'body' => "• Shipped grocery app with 2M+ MAU; crash rate 0.4%.\n• Built feature flags + staged rollouts with Firebase Remote Config.",
                ],
            ],
            internships: [
                [
                    'heading' => 'RideShare Co — Mobile Intern',
                    'dates' => '2020',
                    'body' => 'Kotlin MVVM modules; maps & location edge cases.',
                ],
            ],
            projects: [
                [
                    'heading' => 'Offline-first field sales CRM',
                    'dates' => '2021',
                    'body' => 'Flutter + Isar; sync engine with conflict resolution.',
                ],
                [
                    'heading' => 'Campus bus live tracker',
                    'dates' => '2018',
                    'body' => 'Student app; Firebase realtime; 1k+ DAU during fest.',
                ],
            ],
        );
    }
}
