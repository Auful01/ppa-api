import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';

dayjs.extend(utc);
dayjs.extend(timezone);

const USER_TIMEZONE = Intl.DateTimeFormat().resolvedOptions().timeZone;
const SERVER_TIMEZONE = 'Asia/Makassar'; // karena waktu dari DB = WITA

export function parseFromServer(dateString) {
  if (!dateString) return null;
  return dayjs.tz(dateString, SERVER_TIMEZONE).tz(USER_TIMEZONE).toDate();
}

export function formatForServer(dateObject) {
  if (!dateObject) return null;
  return dayjs(dateObject).tz(SERVER_TIMEZONE).format('YYYY-MM-DD HH:mm:ss');
}