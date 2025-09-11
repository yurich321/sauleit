document.addEventListener('DOMContentLoaded', () => {

    //TODO: create class for this
    //POST helper
    async function post(url, data) {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams(data)
        });
        const json = await res.json().catch(() => ({}));
        if (!res.ok || json.ok === false) {
            throw new Error(json.error || ('HTTP ' + res.status));
        }
        return json;
    }

    //TODO: create class for this
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


    document.addEventListener('click', async (e) => {

        const btn = e.target.closest('.js-settle');
        if (!btn) {
            return;
        }

        const form = btn.closest('form.settle-form');
        if (!form || form.dataset.busy === '1') {
            return;
        }

        const formData = new FormData(form);
        const result = formData.get('result');

        if (!result) {
            showToast('Choose result first', 'error');
            return;
        }
        const data = Object.fromEntries(formData.entries());

        // block for double submit
        form.dataset.busy = '1';
        btn.disabled = true;

        try {
            const response = await post(form.action, data);

            if(response.ok) {
                showToast('Updated!', 'success');
            }

            if (response?.wallet) {
                const currency = response.wallet.wallet_cur;
                const balanceCell = document.querySelector(`[data-wallet="${currency}"]`);
                if(balanceCell) {
                    balanceCell.textContent = response.wallet.balance_text;
                }
            }

            const row = form.closest('.row');
            const actionsCell = row.lastElementChild;
            const statusCell  = actionsCell.previousElementSibling;

            const result = (response.result || '').toUpperCase();
            statusCell.textContent = result || 'SETTLED';
            actionsCell.textContent = '—';

        } catch (err) {
            showToast(err.message || 'Error', 'error');
            form.dataset.busy = '0';
            btn.disabled = false;
        }
    });




});