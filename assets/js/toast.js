document.addEventListener("DOMContentLoaded", () => {
    const container = document.createElement('div');
    container.id = 'toast-container';
    document.body.appendChild(container);

    window.showToast = function (message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;

        let iconClass = 'bi-info-circle-fill';
        if (type === 'success') iconClass = 'bi-check-circle-fill';
        if (type === 'error') iconClass = 'bi-x-circle-fill';

        toast.innerHTML = `<i class="bi ${iconClass}"></i> <span>${message}</span>`;

        document.getElementById('toast-container').appendChild(toast);

        void toast.offsetWidth;
        toast.classList.add('show');

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 400);
        }, 4000);
    }; s

    const modalHTML = `
    <div id="custom-modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(10,15,24,0.7); z-index:100000; align-items:center; justify-content:center; backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);">
        <div style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.12); padding:40px; border-radius:32px; max-width:420px; width:90%; text-align:center; box-shadow:0 40px 100px rgba(0,0,0,0.5); transform:scale(0.9); opacity:0; transition:all 0.4s cubic-bezier(0.16, 1, 0.3, 1); color:#fff;" id="custom-modal-box">
            <div id="custom-modal-icon" style="font-size:54px; color:#ef4444; margin-bottom:20px; filter: drop-shadow(0 0 15px rgba(239, 68, 68, 0.3));"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <h3 style="margin:0 0 12px; color:#fff; font-size:24px; font-weight:800; letter-spacing:-0.5px;">¿Estás seguro?</h3>
            <p id="custom-modal-message" style="color:rgba(255,255,255,0.6); margin-bottom:30px; line-height:1.6; font-size:15px;"></p>
            <div style="display:flex; gap:12px; justify-content:center;">
                <button id="custom-modal-cancel" style="padding:14px 24px; border:1px solid rgba(255,255,255,0.1); border-radius:16px; background:rgba(255,255,255,0.05); color:#fff; font-weight:700; cursor:pointer; font-size:14px; transition:all 0.2s;">Cancelar</button>
                <button id="custom-modal-confirm" style="padding:14px 24px; border:none; border-radius:16px; background:#ef4444; color:#fff; font-weight:700; cursor:pointer; font-size:14px; box-shadow: 0 10px 20px rgba(239, 68, 68, 0.2); transition:all 0.2s;">Confirmar</button>
            </div>
        </div>
    </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    window.customConfirm = function (message, onConfirm) {
        const overlay = document.getElementById('custom-modal-overlay');
        const box = document.getElementById('custom-modal-box');
        document.getElementById('custom-modal-message').innerText = message;

        overlay.style.display = 'flex';
        void overlay.offsetWidth;
        box.style.transform = 'scale(1)';
        box.style.opacity = '1';

        const cancelBtn = document.getElementById('custom-modal-cancel');
        const confirmBtn = document.getElementById('custom-modal-confirm');

        const close = () => {
            box.style.transform = 'scale(0.9)';
            box.style.opacity = '0';
            setTimeout(() => { overlay.style.display = 'none'; }, 300);
            cancelBtn.onclick = null;
            confirmBtn.onclick = null;
        };

        cancelBtn.onclick = close;
        confirmBtn.onclick = () => {
            close();
            if (onConfirm) onConfirm();
        };
    };
});
