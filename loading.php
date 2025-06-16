<style>
     /* Loading界面样式 */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.9);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        transition: opacity 0.5s ease-out;
    }

    .loading-spinner {
        width: 50px;
        height: 50px;
        border: 5px solid #f3f3f3;
        border-top: 5px solid #0d6efd;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .loading-text {
        margin-top: 20px;
        color: #0d6efd;
        font-size: 18px;
        font-weight: bold;
    }

    .loading-container {
        text-align: center;
    }
</style>

<!-- Loading界面 -->
<div class="loading-overlay">
    <div class="loading-container">
        <div class="loading-spinner"></div>
        <div class="loading-text">Loading...</div>
    </div>
</div>

<script>
     document.addEventListener('DOMContentLoaded', function() {
        // 隐藏loading界面
        const loadingOverlay = document.querySelector('.loading-overlay');
        if (loadingOverlay) {
            loadingOverlay.style.opacity = '0';
            setTimeout(() => {
                loadingOverlay.style.display = 'none';
            }, 500);
        }

        initSignaturePad();
    });

    // 在页面加载完成前显示loading界面
    window.addEventListener('load', function() {
        const loadingOverlay = document.querySelector('.loading-overlay');
        if (loadingOverlay) {
            loadingOverlay.style.opacity = '0';
            setTimeout(() => {
                loadingOverlay.style.display = 'none';
            }, 500);
        }
    });
</script>