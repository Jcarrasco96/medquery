document.addEventListener('DOMContentLoaded', () => {
    const debuggerElement = document.getElementById('my-debugger');

    if (!debuggerElement) {
        return;
    }

    const tabs = debuggerElement.querySelectorAll('[data-panel]');
    const panels = debuggerElement.querySelectorAll('.my-debugger-panel');
    const toggle = document.getElementById('my-debugger-toggle');
    const openButton = document.getElementById('my-debugger-open');

    const STORAGE_KEY = 'my-debugger-collapsed';
    const TAB_STORAGE_KEY = 'my-debugger-active-tab';

    function activatePanel(name) {
        const tabExists = Array.from(tabs).some(
            tab => tab.dataset.panel === name
        );

        if (!tabExists) {
            name = 'GET';
        }

        tabs.forEach(tab => {
            const active = tab.dataset.panel === name;

            tab.classList.toggle('active', active);
            tab.setAttribute('aria-selected', String(active));
        });

        panels.forEach(panel => {
            panel.classList.toggle('active', panel.id === `panel-${name}`);
        });

        localStorage.setItem(TAB_STORAGE_KEY, name);
    }

    function toggleDebugger(collapsed) {
        debuggerElement.classList.toggle('collapsed', collapsed);

        localStorage.setItem(STORAGE_KEY, String(collapsed));
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            activatePanel(tab.dataset.panel);
        });
    });

    toggle?.addEventListener('click', () => {
        toggleDebugger(true);
    });

    openButton?.addEventListener('click', () => {
        toggleDebugger(false);
    });

    const savedState = localStorage.getItem(STORAGE_KEY);
    const collapsed = savedState === null ? debuggerElement.classList.contains('collapsed') : savedState === 'true';

    toggleDebugger(collapsed);

    const savedTab = localStorage.getItem(TAB_STORAGE_KEY);

    activatePanel(savedTab || 'GET');
});