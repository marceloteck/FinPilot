const STORAGE_KEY = 'finpilot_period_v1';

const getCurrentYear = () => new Date().getFullYear();
const getCurrentMonth = () => `${new Date().getMonth() + 1}`.padStart(2, '0');

const defaults = {
  active_year: getCurrentYear(),
  active_month: 'all',
};

const safeJsonParse = (value) => {
  try {
    return JSON.parse(value);
  } catch (error) {
    return null;
  }
};

const loadPeriod = () => {
  const raw = localStorage.getItem(STORAGE_KEY);
  if (!raw) return { ...defaults };
  const parsed = safeJsonParse(raw);
  if (!parsed || typeof parsed !== 'object') {
    return { ...defaults };
  }
  return { ...defaults, ...parsed };
};

const savePeriod = (period) => {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(period));
};

let cachedPeriod = typeof window === 'undefined' ? { ...defaults } : loadPeriod();

const emitChange = () => {
  window.dispatchEvent(new CustomEvent('finpilot:period-changed', { detail: { ...cachedPeriod } }));
};

const getPeriod = () => ({ ...cachedPeriod });

const setYear = (year) => {
  cachedPeriod.active_year = Number(year) || getCurrentYear();
  savePeriod(cachedPeriod);
  emitChange();
};

const setMonth = (month) => {
  cachedPeriod.active_month = month || 'all';
  savePeriod(cachedPeriod);
  emitChange();
};

const resetToToday = () => {
  cachedPeriod.active_year = getCurrentYear();
  cachedPeriod.active_month = getCurrentMonth();
  savePeriod(cachedPeriod);
  emitChange();
};

const initPeriod = () => {
  if (typeof window === 'undefined') return;
  cachedPeriod = loadPeriod();
  emitChange();

  const yearSelect = document.getElementById('periodYearSelect');
  const monthSelect = document.getElementById('periodMonthSelect');
  const todayButton = document.getElementById('periodTodayBtn');

  if (yearSelect) {
    yearSelect.value = `${cachedPeriod.active_year}`;
    yearSelect.addEventListener('change', () => setYear(yearSelect.value));
  }

  if (monthSelect) {
    monthSelect.value = cachedPeriod.active_month;
    monthSelect.addEventListener('change', () => setMonth(monthSelect.value));
  }

  if (todayButton) {
    todayButton.addEventListener('click', () => resetToToday());
  }
};

export { initPeriod, getPeriod, setYear, setMonth, resetToToday };
