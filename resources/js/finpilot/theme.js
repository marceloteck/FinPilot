const STORAGE_KEY = 'finpilot_prefs_v1';

const defaults = {
  theme: 'system',
  font: 'md',
  contrast: 'normal',
};

const safeJsonParse = (value) => {
  try {
    return JSON.parse(value);
  } catch (error) {
    return null;
  }
};

const loadPrefs = () => {
  const raw = localStorage.getItem(STORAGE_KEY);
  if (!raw) return { ...defaults };
  const parsed = safeJsonParse(raw);
  if (!parsed || typeof parsed !== 'object') {
    return { ...defaults };
  }
  return { ...defaults, ...parsed };
};

const savePrefs = (prefs) => {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(prefs));
};

const applyPrefs = (prefs) => {
  const html = document.documentElement;
  html.setAttribute('data-theme', prefs.theme);
  html.setAttribute('data-font', prefs.font);
  html.setAttribute('data-contrast', prefs.contrast);
};

const wireSelect = (id, value, onChange) => {
  const el = document.getElementById(id);
  if (!el) return null;
  el.value = value;
  el.addEventListener('change', () => onChange(el.value));
  return el;
};

const initTheme = () => {
  if (typeof window === 'undefined') return;
  const prefs = loadPrefs();
  applyPrefs(prefs);

  wireSelect('themeSelect', prefs.theme, (value) => {
    prefs.theme = value;
    applyPrefs(prefs);
    savePrefs(prefs);
  });

  wireSelect('fontSelect', prefs.font, (value) => {
    prefs.font = value;
    applyPrefs(prefs);
    savePrefs(prefs);
  });

  wireSelect('contrastSelect', prefs.contrast, (value) => {
    prefs.contrast = value;
    applyPrefs(prefs);
    savePrefs(prefs);
  });

  window.addEventListener('keydown', (event) => {
    const target = event.target;
    if (target && ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName)) return;
    if (event.key.toLowerCase() !== 't') return;

    const order = ['system', 'light', 'dark'];
    const index = order.indexOf(prefs.theme);
    prefs.theme = order[(index + 1) % order.length];
    applyPrefs(prefs);
    savePrefs(prefs);

    const themeSelect = document.getElementById('themeSelect');
    if (themeSelect) themeSelect.value = prefs.theme;
  });
};

export { initTheme, loadPrefs };
