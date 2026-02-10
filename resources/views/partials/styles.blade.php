<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    pink: {
                        50: '#fff1f2',
                        400: '#ff4d6d',
                        500: '#fd1e4b',
                        600: '#e1123d',
                        700: '#c00f35',
                    }
                }
            }
        }
    }
</script>

<style>
    [x-cloak] { display: none !important; }
    .sidebar-scroll::-webkit-scrollbar { width: 4px; }
    .sidebar-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .swiper-slide { display: flex !important; height: 100% !important; }
    .mainSlider { display: block !important; }
    .swiper-wrapper { display: flex !important; flex-direction: row !important; }
    .swiper-slide { width: 100% !important; flex-shrink: 0 !important; }
    .menu-text { font-size: 15px !important; }
    .sidebar-transition { transition: all 0.5s ease-in-out !important; }
    .menu-row:hover { background-color: #fff1f2 !important; color: #fd1e4b !important; }
    :root { --primary-color: #fd1e4b; }
    .bg-pink-600 { background-color: #fd1e4b !important; }
    .text-pink-600 { color: #fd1e4b !important; }
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />