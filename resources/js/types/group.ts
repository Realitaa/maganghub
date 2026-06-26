import type { MemberDetail } from './auth';
import type { SubmissionMembership } from './submission';

export type Member = {
    id: number;
    name: string;
    email: string;
    nim?: string;
    major?: string;
};

export type JoinRequest = {
    id: number;
    user: Member;
    status: string;
    created_at: string;
};

export type Submission = {
    id: number;
    company_name: string;
    company_address: string;
    company_contact: string;
    division: string;
    field_of_interest: string;
    start_date: string;
    end_date: string;
    status: string;
    company_response_path: string | null;
    updated_at: string;
    group: {
        id: number;
        code: string;
        leader_id: number;
        memberships_count: number;
        leader: MemberDetail;
    };
    submission_memberships: SubmissionMembership[];
};

export type Timeline = {
    id: number;
    title: string;
    created_at: string;
};

export type Group = {
    id: number;
    code: string;
    status: string;
    leader_id: number;
    leader: Member;
    banner_url?: string | null;
    invite_url?: string | null;
    og_image_url?: string | null;
    memberships: Array<{ id: number; user: Member }>;
    join_requests: JoinRequest[];
    active_submission?: Submission | null;
    activeSubmission?: Submission | null;
    timelines?: Timeline[];
    computed_status?:
        | 'segera_magang'
        | 'melaksanakan_magang'
        | 'selesai_magang';
};

export type PendingJoinRequest = {
    id: number;
    status: string;
    group: {
        id: number;
        code: string;
        status: string;
        leader: Member;
    };
};
