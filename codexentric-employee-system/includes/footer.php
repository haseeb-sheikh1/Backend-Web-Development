<!-- Global Visual Footer Logic -->

<style>
/* Bulletproof Global Custom Select Framework */
.c-select-container {
    position: relative;
    display: block;
    width: 100%;
}

.c-select-trigger {
    width: 100%;
    height: 44px;
    padding: 0 16px;
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 22px;
    font-family: inherit;
    font-size: 13.5px;
    font-weight: 500;
    color: #1e293b;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    box-sizing: border-box;
    user-select: none;
}

.c-select-trigger:hover {
    border-color: #cbd5e1;
    background: #fcfdfd;
    box-shadow: 0 2px 8px rgba(0,0,0,0.02);
}

.c-select-container.open .c-select-trigger {
    border-color: #186D55; /* Global Brand Green */
    background: #ffffff;
    box-shadow: 0 0 0 4px rgba(24, 109, 85, 0.12), 0 4px 12px rgba(24, 109, 85, 0.05);
}

.c-select-trigger span {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.c-select-trigger svg {
    width: 14px;
    height: 14px;
    color: #64748b;
    flex-shrink: 0;
    margin-left: 8px;
    transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.c-select-container.open .c-select-trigger svg {
    transform: rotate(180deg);
    color: #186D55;
}

/* Floating Dropdown Menu - Detached Context */
.c-floating-dropdown {
    position: fixed;
    background: #ffffff;
    border: 1px solid rgba(0,0,0,0.08);
    border-radius: 12px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1), 0 5px 15px rgba(0, 0, 0, 0.04);
    z-index: 999999; /* Absolute top-level layering */
    padding: 6px;
    margin: 0;
    list-style: none;
    box-sizing: border-box;
    max-height: 260px;
    overflow-y: auto;
    font-family: inherit;
    pointer-events: auto;
    display: none;
    opacity: 0;
    transform: translateY(-6px);
    transition: transform 0.15s ease, opacity 0.15s ease;
}

.c-floating-dropdown.visible {
    display: block;
    opacity: 1;
    transform: translateY(0);
}

.c-floating-dropdown li {
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 13.5px;
    font-weight: 500;
    color: #475569;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
}

.c-floating-dropdown li:hover {
    background: #f1f5f9;
    color: #1e293b;
}

.c-floating-dropdown li.selected-active {
    background: #186D55 !important; /* Forcing absolute brand green highlight requested by user */
    color: #ffffff !important;
}

/* Scoped scrollbar for floating dropdown */
.c-floating-dropdown::-webkit-scrollbar {
    width: 5px;
}
.c-floating-dropdown::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
</style>

<!-- Production Grade Global JavaScript Assets for Framework Modules -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Locate all selects globally EXCEPT if opt-out class is set
    const allSelects = document.querySelectorAll('select:not(.no-custom-style)');
    
    // Track global active menu to properly clear references
    let activeMenuNode = null;
    let activeContainerRef = null;

    function closeActiveDropdown() {
        if (activeMenuNode) {
            activeMenuNode.classList.remove('visible');
            // Allow transition out
            const nodeToRemove = activeMenuNode;
            setTimeout(() => { 
               if(nodeToRemove && nodeToRemove.parentNode) nodeToRemove.parentNode.removeChild(nodeToRemove); 
            }, 160);
            activeMenuNode = null;
        }
        if (activeContainerRef) {
            activeContainerRef.classList.remove('open');
            activeContainerRef = null;
        }
    }

    allSelects.forEach(origSelect => {
        // 1. Silently mask legacy element
        origSelect.style.display = 'none';

        // 2. Build Wrapper context inline
        const customContainer = document.createElement('div');
        customContainer.className = 'c-select-container';
        origSelect.parentNode.insertBefore(customContainer, origSelect.nextSibling);

        // 3. Create Clickable Trigger
        const triggerNode = document.createElement('div');
        triggerNode.className = 'c-select-trigger';
        
        const valSpan = document.createElement('span');
        const currentOption = origSelect.options[origSelect.selectedIndex];
        valSpan.innerText = currentOption ? currentOption.innerText : 'Select...';
        
        const arrowIcon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        arrowIcon.setAttribute('viewBox', '0 0 24 24');
        arrowIcon.setAttribute('fill', 'none');
        arrowIcon.setAttribute('stroke', 'currentColor');
        arrowIcon.innerHTML = '<polyline points="6 9 12 15 18 9" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></polyline>';

        triggerNode.appendChild(valSpan);
        triggerNode.appendChild(arrowIcon);
        customContainer.appendChild(triggerNode);

        // 4. Bind Open Flow
        triggerNode.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // Toggle logic if re-clicking same
            const isSelfOpen = customContainer.classList.contains('open');
            
            // Clean slate cleanup before rebuilding
            closeActiveDropdown();

            if (isSelfOpen) return; // User just toggled it closed

            // Activate container context
            customContainer.classList.add('open');
            activeContainerRef = customContainer;

            // Dynamically generate FLOATING dropdown attached to root body context
            const menuUL = document.createElement('ul');
            menuUL.className = 'c-floating-dropdown';
            
            Array.from(origSelect.options).forEach((opt, idx) => {
                // Skip hidden/disabled metadata options that shouldn't be clickable
                if(opt.disabled && opt.value === "") return;
                
                const li = document.createElement('li');
                li.innerText = opt.innerText;
                li.setAttribute('data-index', idx);
                if (origSelect.selectedIndex === idx) {
                    li.classList.add('selected-active');
                }

                li.addEventListener('click', function(evt) {
                    evt.stopPropagation();
                    origSelect.selectedIndex = idx;
                    valSpan.innerText = opt.innerText;
                    // Force fire core change event for any external listener frameworks bound to standard SELECT
                    origSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    closeActiveDropdown();
                });

                menuUL.appendChild(li);
            });

            document.body.appendChild(menuUL);
            activeMenuNode = menuUL;

            // 5. PRECISION PLACEMENT ENGINE
            const triggerBox = triggerNode.getBoundingClientRect();
            
            menuUL.style.width = triggerBox.width + 'px';
            menuUL.style.left = triggerBox.left + 'px';
            
            // Detect optimal rendering direction (Standard below vs Reverse above)
            const viewportH = window.innerHeight;
            const gap = 6;
            const spaceBelow = viewportH - triggerBox.bottom;
            const approximateMenuHeight = Math.min(origSelect.options.length * 40, 260);
            
            if (spaceBelow < approximateMenuHeight && triggerBox.top > approximateMenuHeight) {
                // Flip upward if no space below
                menuUL.style.top = 'auto';
                menuUL.style.bottom = (viewportH - triggerBox.top + gap) + 'px';
                menuUL.style.transformOrigin = 'bottom center';
            } else {
                // Default float below
                menuUL.style.bottom = 'auto';
                menuUL.style.top = (triggerBox.bottom + gap) + 'px';
                menuUL.style.transformOrigin = 'top center';
            }

            // Wait for parse tick and animate in
            requestAnimationFrame(() => {
                menuUL.classList.add('visible');
            });
        });
    });

    // ── GLOBAL CALENDAR ENGINE ACTIVATION ──
    // Automate high-grade calendar deployment to all detected standard date components
    flatpickr("input[type='date']", {
        disableMobile: true, // Neutralize native OS interference to protect green theme
        dateFormat: "Y-m-d",
        animate: true
    });

    // Automate Month-Specific rendering for reporting engines
    if (typeof monthSelectPlugin !== 'undefined') {
        flatpickr("input[type='month']", {
            disableMobile: true,
            plugins: [
                new monthSelectPlugin({
                    shorthand: false,
                    dateFormat: "Y-m",
                    altFormat: "F Y", // "January 2026" style presentation
                    theme: "light"
                })
            ]
        });
    }

    // GLOBAL DISMISSAL TRIGGERS
    window.addEventListener('click', closeActiveDropdown);
    window.addEventListener('resize', closeActiveDropdown);
    window.addEventListener('scroll', function(e) {
        // Only collapse menu on page/container scroll, IGNORE scrolls intrinsic to the active dropdown itself
        if (activeMenuNode && activeMenuNode.contains(e.target)) return;
        closeActiveDropdown();
    }, true);
});
</script>

</body>
</html>
