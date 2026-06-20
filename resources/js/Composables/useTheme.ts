import { ref } from 'vue';

const isDark = ref(false);

// ponytail: Minimal theme switcher composable (zero-dependency, localStorage + OS scheme sync)
export function useTheme() {
    const initTheme = () => {
        if (typeof window === 'undefined') return;
        const storedTheme = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        if (storedTheme === 'dark' || (!storedTheme && prefersDark)) {
            isDark.value = true;
            document.documentElement.classList.add('dark');
        } else {
            isDark.value = false;
            document.documentElement.classList.remove('dark');
        }
    };

    const toggleTheme = () => {
        if (typeof window === 'undefined') return;
        isDark.value = !isDark.value;
        if (isDark.value) {
            localStorage.setItem('theme', 'dark');
            document.documentElement.classList.add('dark');
        } else {
            localStorage.setItem('theme', 'light');
            document.documentElement.classList.remove('dark');
        }
    };

    return {
        isDark,
        initTheme,
        toggleTheme
    };
}
