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
};

export type RefreshLogParamsType = {
    forceScrollToBottom: boolean;
    customFilters: LogFiltersType;
};

export const LOG_LEVEL = {
    'DEBUG': 'debug',
    'INFO': 'info',
    'NOTICE': 'notice',
    'WARNING': 'warning',
    'ERROR': 'error',
    'CRITICAL': 'critical',
    'ALERT': 'alert',
    'EMERGENCY': 'emergency',
};
