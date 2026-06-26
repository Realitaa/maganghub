export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    nim: string;
    phone: string;
    address?: string | null;
    role: string;
    gender: 'L' | 'P';
    is_active: boolean;
    password_changed_at: string | null;
    semester: number | null;
    student_class_id: number | null;
    student_class?: { id: number; name: string } | null;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
    requirements: {
        password_changed: boolean;
        profile_completed: boolean;
    } | null;
};

export type MemberDetail = {
    id: number;
    name: string;
    email: string;
    nim?: string;
    phone?: string;
    address?: string;
    gender?: string;
    semester?: number;
};

/* @chisel-passkeys */
export type Passkey = {
    id: number;
    name: string;
    authenticator: string | null;
    created_at_diff: string;
    last_used_at_diff: string | null;
};
/* @end-chisel-passkeys */
