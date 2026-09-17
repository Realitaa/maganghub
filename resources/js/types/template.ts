export interface TemplateStatus {
    exists: boolean;
    size: string | null;
    updatedAt: string | null;
}

export interface RecentSubmissionGroup {
    id: number;
    code: string;
    leader_name: string;
    company_name: string;
    submission_id: number;
    label: string;
}

export interface TemplatePlaceholder {
    key: string;
    label: string;
    description: string;
    example: string;
}
