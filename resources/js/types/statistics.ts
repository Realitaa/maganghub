export type CompanyStatistics = {
    multinational: number;
    national: number;
    startup: number;
    havenot: number;
};

export type PublicStatisticsData = {
    total_students: number;
    total_groups: number;
    total_companies: number;
    student_internship_status: StudentInternshipStatusItem[];
    pie_chart: CompanyStatistics;
    updated_at: string;
};

export type DashboardSummary = {
    total_students: number;
    total_groups: number;
    pending_submissions: number;
    total_companies: number;
};

export type GroupStatusDistributionItem = {
    status: string;
    name: string;
    y: number;
    color: string;
};

export type StudentInternshipStatusItem = {
    status: string;
    name: string;
    y: number;
    color: string;
};

export type DashboardOperational = {
    pending_submissions: number;
    waiting_preparations: number;
    group_status_distribution: GroupStatusDistributionItem[];
    student_internship_status: StudentInternshipStatusItem[];
    total_groups: number;
};
