import { initTheme } from './theme';
import { initPeriod } from './period';

const initFinpilot = () => {
  initTheme();
  initPeriod();
};

if (typeof window !== 'undefined') {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFinpilot);
  } else {
    initFinpilot();
  }
}

export { initFinpilot };
