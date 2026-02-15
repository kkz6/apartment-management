export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
}

export interface PaginationData<T> {
    data: T[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number | null
    to: number | null
    links: Array<{ url: string | null; label: string; active: boolean }>
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
};
