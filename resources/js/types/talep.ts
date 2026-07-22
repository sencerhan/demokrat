export type TalepStatus = 'pending' | 'reviewed' | 'accepted' | 'rejected';

export type VoteDirection = 'up' | 'down';

export type TalepSortMode =
    'random' | 'most_votes' | 'most_supported' | 'least_supported';

export type TalepAuthor = {
    id: number | null;
    name: string | null;
};

export type TalepAbilities = {
    update: boolean;
    update_status: boolean;
    delete: boolean;
};

export type Talep = {
    id: number;
    body: string;
    status: TalepStatus;
    author: TalepAuthor;
    upvotes_count: number;
    downvotes_count: number;
    total_votes_count: number;
    score: number;
    created_at: string | null;
    my_vote?: VoteDirection | null;
    can: TalepAbilities;
};
