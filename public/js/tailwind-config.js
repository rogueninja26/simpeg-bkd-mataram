tailwind.config = {
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'sans-serif'],
                display: ['Plus Jakarta Sans', 'sans-serif'],
            },
            colors: {
                primary: {
                    DEFAULT: '#0f2942', // Maritime Navy
                    dark: '#001428',
                    light: '#314863',
                },
                secondary: {
                    DEFAULT: '#0d9488', // Mataram Emerald Teal
                    dark: '#006a61',
                    light: '#ccfbf1',
                },
                tertiary: {
                    DEFAULT: '#d97706', // Sasak Amber Gold
                    dark: '#b45309',
                    light: '#fef3c7',
                },
                neutral: {
                    DEFAULT: '#334155', // Slate Carbon
                    surface: '#f8fafc', // Canvas Base
                    border: '#cbd5e1',
                }
            },
            borderRadius: {
                DEFAULT: '0.25rem', // 4px untuk tombol, input, badge
                card: '0.5rem',     // 8px untuk card & panel
                modal: '0.75rem',   // 12px untuk modal
            }
        }
    }
}