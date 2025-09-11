document.addEventListener('DOMContentLoaded', () => {
    const eventsWrap      = document.querySelector('.events');
    const slipBody        = document.querySelector('.betslip-body');
    const slipFooterBtn   = document.querySelector('.betslip-footer button');

    const walletForm      = document.getElementById('walletForm');
    const walletSelect    = document.getElementById('walletCur');
    const walletBalanceEl = document.getElementById('walletBalance');


    let selectedOutcomeEl = null;

    //POST helper
    async function post(url, data) {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(data)
        });
        const json = await res.json().catch(() => ({}));
        if (!res.ok || json.ok === false) {
            throw new Error(json.error || ('HTTP ' + res.status));
        }
        return json;
    }

    const getCurrency = () =>
        (walletSelect && walletSelect.value) || 'EUR';

    function renderSlip({ title, outcome, date, time, odds }) {
        slipBody.classList.add('filled');
        const cur = getCurrency();

        slipBody.innerHTML = `
                      <button class="betslip-close" aria-label="Close">×</button>
                      <div class="bet-item">
                        <div class="bet-head">
                          <div class="bet-title">${title}</div>
                          <div class="bet-meta">${date} ${time}</div>
                        </div>
                        <div class="bet-meta" style="margin-bottom:8px;">
                          <strong>${outcome}</strong> @ <strong class="js-odds">${Number(odds).toFixed(2)}</strong>
                        </div>
                        <div class="bet-stake">
                          <input class="js-stake" type="number" min="1" max="500" step="0.01" placeholder="Enter stake (1–500)">
                          <span style="opacity:.8;">${cur ? 'in ' + cur : ''}</span>
                        </div>
                        <div class="bet-meta" style="margin-top:8px;">
                          Possible win: <strong class="js-win">0.00</strong> <span class="js-cur">${cur}</span>
                        </div>
                      </div>
                    `;

        slipFooterBtn.disabled = false;

        const input = slipBody.querySelector('.js-stake');
        const oddsEl = slipBody.querySelector('.js-odds');
        const winEl  = slipBody.querySelector('.js-win');

        const recalc = () => {
            const stake = Number(input.value);
            const k     = Number(oddsEl.textContent);
            if (!isFinite(stake) || stake <= 0) {
                winEl.textContent = '0.00';
                return;
            }
            const clamped = Math.min(500, Math.max(1, stake));
            if (clamped !== stake) {
                input.value = clamped.toFixed(2);
            }
            winEl.textContent = (clamped * k).toFixed(2);
        };

        input.addEventListener('input', recalc);
        input.addEventListener('change', recalc);
        input.focus();
    }

    function clearSlip() {
        slipBody.classList.remove('filled');
        slipBody.innerHTML = `<div class="empty-betslip">Your betslip is empty</div>`;
        slipFooterBtn.disabled = true;
        if (selectedOutcomeEl) {
            selectedOutcomeEl.classList.remove('selected');
            selectedOutcomeEl = null;
        }
    }

    // rate click
    eventsWrap.addEventListener('click', (e) => {
        const el = e.target.closest('.outcome');
        if (!el) return;

        if (selectedOutcomeEl && selectedOutcomeEl !== el) {
            selectedOutcomeEl.classList.remove('selected');
        }
        el.classList.add('selected');
        selectedOutcomeEl = el;

        renderSlip({
            title:   el.dataset.eventTitle || '',
            outcome: el.dataset.outcomeLabel || '',
            date:    el.dataset.date || '',
            time:    el.dataset.time || '',
            odds:    el.dataset.odds || ''
        });
    });


    slipBody.addEventListener('click', (e) => {
        if (e.target.closest('.betslip-close')) {
            clearSlip();
        }
    });

    // wallet change
    if (walletSelect) {
        walletSelect.addEventListener('change', async () => {
            try {
                const csrf = walletForm?.querySelector('input[name="_csrf"]')?.value || '';
                const json  = await post('/api/balance', { _csrf: csrf, currency: walletSelect.value });
                console.log(walletBalanceEl)
                if (walletBalanceEl) {

                    walletBalanceEl.textContent = json.wallet;
                }

                /*if (walletCurLabel) {
                    walletCurLabel.textContent  = json.currency;
                }*/

                const placeSpan = slipBody.querySelector('.bet-stake span');
                const curSpan   = slipBody.querySelector('.js-cur');

                if (placeSpan) {
                    placeSpan.textContent = json.currency ? ('in ' + json.currency) : '';
                }

                if (curSpan) {
                    curSpan.textContent   = json.currency;
                }
            } catch (err) {
                console.error(err);
            }
        });
    }

    // place bet
    function getSelectedOutcomeData() {
        if (!selectedOutcomeEl) {
            return null;
        }
        return {
            event_id:     selectedOutcomeEl.dataset.eventId || '',
            event_title:  selectedOutcomeEl.dataset.eventTitle || '',
            outcome:      selectedOutcomeEl.dataset.outcomeLabel || '',
            outcome_key:  selectedOutcomeEl.dataset.outcomeKey || '',
            odds:         selectedOutcomeEl.dataset.odds || '',
            date:         selectedOutcomeEl.dataset.date || '',
            time:         selectedOutcomeEl.dataset.time || ''
        };
    }

    const betNowBtn = document.querySelector('.betslip-footer button');
    betNowBtn.addEventListener('click', async () => {
        try {

            const stakeInput = slipBody.querySelector('.js-stake');
            if (!stakeInput) {
                return;
            }

            const stake = Number(stakeInput.value);
            if (!isFinite(stake) || stake < 1 || stake > 500) {
                console.log('enter valid stake');
                showToast('Enter valid stake!', 'error');
                return;
            }

            const sel = getSelectedOutcomeData();
            console.log(sel)
            if (!sel) {
                console.log('select outcome first');
                showToast('Select outcome first!', 'error');
                return;
            }

            const currency = getCurrency();
            const csrf = walletForm?.querySelector('input[name="_csrf"]')?.value || '';

            betNowBtn.disabled = true;


            const payload = {
                _csrf:   csrf,
                currency: currency,
                stake:    stake.toFixed(2),
                event_id: sel.event_id,
                outcome:  sel.outcome,
                outcome_key:  sel.outcome_key,
                event_title: sel.event_title,
                odds:        sel.odds
            };

            const json = await post('/api/bet/place', payload);

            console.log(json);

            if (walletBalanceEl && typeof json.balance_minor !== 'undefined') {
                walletBalanceEl.textContent = json.balance_minor;
            }

            clearSlip();

            showToast('Bet placed successfully!', 'success');

        } catch (err) {
            console.log(err.message);
            showToast(err.message, 'error');
        } finally {
            betNowBtn.disabled = false;
        }
    });

    const showToast = (message, type = 'info', timeout = 3000) => {
        const container = document.getElementById('toastContainer');
        if (!container) {
            return;
        }

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        container.appendChild(toast);

        // animation
        setTimeout(() => toast.classList.add('show'), 50);

        // delete toast
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 400);
        }, timeout);
    }

    /*dropdown menu*/
    const dropdown = document.querySelector('.user-dropdown');
    if (!dropdown) {
        return;
    }

    const btn = dropdown.querySelector('.user-btn');

    btn?.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.classList.toggle('open');
    });

    // click outside
    document.addEventListener('pointerdown', (e) => {
        if (!e.target.closest('.user-dropdown')) {
            dropdown.classList.remove('open');
        }
    });

    // on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') dropdown.classList.remove('open');
    });


});

// mobile nav
(() => {
    const body = document.body;
    const toggle = document.querySelector('.toggle-nav');
    const backdrop = document.querySelector('.nav-backdrop');
    const left = document.querySelector('.left-menu');
    const right = document.querySelector('.right-menu');

    if (!toggle || !backdrop || !left || !right) {
        return;
    }

    const open = () => {
        body.classList.add('nav-open');
        toggle.setAttribute('aria-expanded','true');
        backdrop.hidden = false;
    };
    const close = () => {
        body.classList.remove('nav-open');
        toggle.setAttribute('aria-expanded','false');
        backdrop.hidden = true;
    };

    toggle.addEventListener('click', () => {
        body.classList.contains('nav-open') ? close() : open();
    });
    backdrop.addEventListener('click', close);

    // close on escape
    document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
})();

