document.addEventListener("DOMContentLoaded", () => {
    const container = document.createElement('div');
    container.id = 'toast-container';
    document.body.appendChild(container);

    window.showToast = function(message, type = 'info') {
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
    };

    const modalHTML = `
    <div id="custom-modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:100000; align-items:center; justify-content:center; backdrop-filter: blur(4px);">
        <div style="background:#fff; padding:30px; border-radius:16px; max-width:400px; width:90%; text-align:center; box-shadow:0 20px 40px rgba(0,0,0,0.2); transform:scale(0.9); opacity:0; transition:all 0.3s ease;" id="custom-modal-box">
            <div id="custom-modal-icon" style="font-size:48px; color:#e74c3c; margin-bottom:15px;"><i class="bi bi-exclamation-circle"></i></div>
            <h3 style="margin:0 0 10px; color:#2c3e50; font-size:20px;">Confirmar Acción</h3>
            <p id="custom-modal-message" style="color:#64748b; margin-bottom:25px; line-height:1.5;"></p>
            <div style="display:flex; gap:15px; justify-content:center;">
                <button id="custom-modal-cancel" style="padding:10px 20px; border:none; border-radius:8px; background:#f1f5f9; color:#475569; font-weight:600; cursor:pointer;">Cancelar</button>
                <button id="custom-modal-confirm" style="padding:10px 20px; border:none; border-radius:8px; background:#e74c3c; color:#fff; font-weight:600; cursor:pointer;">Sí, continuar</button>
            </div>
        </div>
    </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    window.customConfirm = function(message, onConfirm) {
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
