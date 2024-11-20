export type LogRecordType = {
    id: number;
    app_name: string;
    environment: string;
    channel: string;
    level_name: string;
    level: string;
    message: string;
    context: string;
    extra: string;
    user_id: string;
    logged_at: string;
    formatted_logged_at: string;
    created_at: string;
};

export type LogFiltersType = {
    app_name: string;
    environment: string;
    channel: string;
    level: string;
    date_from: string;
    date_to: string;
    page: number;
    prevCursorUrl: string;
    nextCursorUrl: string;
};

export type LogRecordSettings = {
    showId: boolean;
    showDate: boolean;
    showLevel: boolean;
}

export const PAGINATION_MODE = {
    INIT: 'init',
    NEXT: 'next',
    PREV: 'prev',
}

export type RefreshLogParamsType = {
    customFilters: LogFiltersType;
    forceScrollToBottom: boolean;
    pagination_mode: PAGINATION_MODE;
    url?: string;
};

export type PaginatorType = {
    next_page_url: null,
    prev_page_url: null,
}
