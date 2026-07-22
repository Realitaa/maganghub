import type { MemberDetail } from './auth';

export type SubmissionMembership = {
    id: number;
    user: MemberDetail;
    status: string;
    rejection_note?: string | null;
};

export type SubmissionListItem = {
    id: number;
    company_name: string;
    leader_name: string;
    members_count: number;
    submitted_at: string;
    status: string;
};

export type SubmissionDetail = {
    id: number;
    company_name: string;
    company_address: string;
    company_contact: string;
    company_leader?: string | null;
    division: string;
    field_of_interest: string;
    start_date: string;
    end_date: string;
    status: string;
    rejection_note?: string;
    group: {
        id: number;
        leader: MemberDetail;
    };
    submission_memberships: SubmissionMembership[];
};
