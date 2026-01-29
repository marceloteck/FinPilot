(() => {
  const STORAGE_KEY = "finpilot_prefs_v1";

  const defaults = {
    theme: "system",    // "light" | "dark" | "system"
    font: "md",         // "md" | "lg" | "xl"
    contrast: "normal", // "normal" | "high"
  };

  function loadPrefs() {
    try {
      const raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) return { ...defaults };
      const obj = JSON.parse(raw);
      return { ...defaults, ...obj };
    } catch {
      return { ...defaults };
    }
  }

  function savePrefs(prefs) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(prefs));
  }

  function applyPrefs(prefs) {
    const html = document.documentElement;

    html.setAttribute("data-theme", prefs.theme);
    // Acessibilidade
    html.setAttribute("data-font", prefs.font);
    html.setAttribute("data-contrast", prefs.contrast);
  }

  function wireSelect(id, value, onChange) {
    const el = document.getElementById(id);
    if (!el) return null;
    el.value = value;
    el.addEventListener("change", () => onChange(el.value));
    return el;
  }

  // Init
  const prefs = loadPrefs();
  applyPrefs(prefs);

  // Wire UI (se existir)
  wireSelect("themeSelect", prefs.theme, (val) => {
    prefs.theme = val;
    applyPrefs(prefs);
    savePrefs(prefs);
  });

  wireSelect("fontSelect", prefs.font, (val) => {
    prefs.font = val;
    applyPrefs(prefs);
    savePrefs(prefs);
  });

  wireSelect("contrastSelect", prefs.contrast, (val) => {
    prefs.contrast = val;
    applyPrefs(prefs);
    savePrefs(prefs);
  });

  // Atalho opcional: pressionar "T" alterna tema (system -> light -> dark)
  window.addEventListener("keydown", (e) => {
    if (e.target && (e.target.tagName === "INPUT" || e.target.tagName === "TEXTAREA")) return;
    if (e.key.toLowerCase() !== "t") return;

    const order = ["system", "light", "dark"];
    const idx = order.indexOf(prefs.theme);
    prefs.theme = order[(idx + 1) % order.length];
    applyPrefs(prefs);
    savePrefs(prefs);

    const themeSelect = document.getElementById("themeSelect");
    if (themeSelect) themeSelect.value = prefs.theme;
  });
})();