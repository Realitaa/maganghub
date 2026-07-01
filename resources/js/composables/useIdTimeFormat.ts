import { useDateFormat, useTimeAgoIntl } from '@vueuse/core';

/**
 * Composable for formatting dates and times in Indonesian format.
 */
export function useIdTimeFormat() {
    const options = { locales: 'id-ID' };

    /**
     * Formats a date to 'D MMMM YYYY' format (e.g., '28 Juni 2026').
     */
    function formatDate(date: string | Date | number): string {
        return useDateFormat(date, 'D MMMM YYYY', options).value;
    }

    /**
     * Formats a date to 'D MMMM YYYY, HH:mm' format (e.g., '28 Juni 2026, 12:00').
     */
    function formatDateTime(date: string | Date | number): string {
        return useDateFormat(date, 'D MMMM YYYY, HH:mm', options).value;
    }

    /**
     * Formats a date to show time relative to now (e.g., '5 menit yang lalu', '1 jam yang lalu', '2 hari yang lalu').
     */
    function formatTimeAgo(date: string | Date | number): string {
        return useTimeAgoIntl(new Date(date), { locale: 'id-ID' }).value;
    }

    /**
     * Formats a date to show 'Hari Ini, HH:mm' if today, 'Kemarin, HH:mm' if yesterday,
     * otherwise 'D MMMM YYYY, HH:mm' (e.g., 'Hari Ini, 12:00' or '28 Juni 2026, 12:00').
     */
    function formatFAT(date: string | Date | number): string {
        const inputDate = new Date(date);

        if (isNaN(inputDate.getTime())) {
            return '';
        }

        const today = new Date();

        // Compare dates based on day boundaries (local time)
        const inputYear = inputDate.getFullYear();
        const inputMonth = inputDate.getMonth();
        const inputDay = inputDate.getDate();

        const todayYear = today.getFullYear();
        const todayMonth = today.getMonth();
        const todayDay = today.getDate();

        const inputStartOfDay = new Date(
            inputYear,
            inputMonth,
            inputDay,
        ).getTime();
        const todayStartOfDay = new Date(
            todayYear,
            todayMonth,
            todayDay,
        ).getTime();

        const diffMs = todayStartOfDay - inputStartOfDay;
        const oneDayMs = 24 * 60 * 60 * 1000;
        const diffDays = Math.round(diffMs / oneDayMs);

        const hours = String(inputDate.getHours()).padStart(2, '0');
        const minutes = String(inputDate.getMinutes()).padStart(2, '0');
        const timeStr = `${hours}:${minutes}`;

        if (diffDays === 0) {
            return `Hari Ini, ${timeStr}`;
        } else if (diffDays === 1) {
            return `Kemarin, ${timeStr}`;
        } else {
            return useDateFormat(inputDate, 'D MMMM YYYY, HH:mm', {
                locales: 'id-ID',
            }).value;
        }
    }

    return {
        formatDate,
        formatDateTime,
        formatTimeAgo,
        formatFAT,
    };
}
