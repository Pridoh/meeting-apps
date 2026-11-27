@once
    <script>
        (() => {
            const root = document.documentElement;
            const system = window.matchMedia('(prefers-color-scheme: dark)');
            const storageKey = 'rapatin-theme';
            let preference;

            try {
                const saved = localStorage.getItem(storageKey);
                if (saved === 'light' || saved === 'dark') preference = saved;
            } catch {
                // The theme can still change for this page if storage is unavailable.
            }

            const applyTheme = () => {
                const theme = preference ?? (system.matches ? 'dark' : 'light');
                root.dataset.theme = theme;
                document.querySelectorAll('[data-theme-toggle]').forEach(button => {
                    button.setAttribute('aria-pressed', String(theme === 'dark'));
                });
            };

            applyTheme();
            document.addEventListener('DOMContentLoaded', applyTheme, { once: true });
            system.addEventListener('change', applyTheme);

            document.addEventListener('click', event => {
                if (!event.target.closest('[data-theme-toggle]')) return;
                preference = root.dataset.theme === 'dark' ? 'light' : 'dark';
                applyTheme();

                try {
                    localStorage.setItem(storageKey, preference);
                } catch {
                    // Keep the selected theme for the current page.
                }
            });
        })();
    </script>
@endonce
