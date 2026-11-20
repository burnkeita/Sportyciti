/**
 * SportyCiti front-end helpers. Alpine.js is provided by Livewire.
 */

// Remember the visitor's timezone so the server can compute local day boundaries for fixtures.
(function rememberTimezone() {
    try {
        const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
        if (tz && !document.cookie.includes('tz=' + encodeURIComponent(tz))) {
            document.cookie = 'tz=' + encodeURIComponent(tz) + ';path=/;max-age=31536000;SameSite=Lax';
        }
    } catch (e) {
        /* ignore */
    }
})();

/**
 * Render <time data-local="time|date|datetime|day"> elements in the visitor's own timezone.
 * The server always outputs ISO-8601 UTC in the datetime attribute.
 */
function localiseTimes(root = document) {
    const formats = {
        time: { hour: '2-digit', minute: '2-digit' },
        date: { day: 'numeric', month: 'short', year: 'numeric' },
        day: { weekday: 'short', day: 'numeric', month: 'short' },
        datetime: { weekday: 'short', day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' },
    };

    root.querySelectorAll('time[data-local]').forEach((el) => {
        const value = el.getAttribute('datetime');
        if (!value) return;
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return;
        const format = formats[el.dataset.local] || formats.datetime;
        el.textContent = new Intl.DateTimeFormat(undefined, format).format(date);
        el.title = date.toLocaleString(undefined, { dateStyle: 'full', timeStyle: 'short' });
    });
}

window.localiseTimes = localiseTimes;
document.addEventListener('DOMContentLoaded', () => localiseTimes());
document.addEventListener('livewire:navigated', () => localiseTimes());

// Re-localise whenever the DOM changes (Livewire polling, filters, morphing).
let localiseQueued = false;
new MutationObserver(() => {
    if (localiseQueued) return;
    localiseQueued = true;
    requestAnimationFrame(() => {
        localiseQueued = false;
        localiseTimes();
    });
}).observe(document.documentElement, { childList: true, subtree: true });

// Dark mode toggle (initial state is set inline in <head> to avoid a flash).
window.toggleTheme = function toggleTheme() {
    const isDark = document.documentElement.classList.toggle('dark');
    try {
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
    } catch (e) {
        /* storage unavailable */
    }
};

// Native share sheet with copy-link fallback.
window.shareArticle = async function shareArticle(title, url) {
    if (navigator.share) {
        try {
            await navigator.share({ title, url });
            return 'shared';
        } catch (e) {
            return 'cancelled';
        }
    }
    try {
        await navigator.clipboard.writeText(url);
        return 'copied';
    } catch (e) {
        return 'failed';
    }
};
