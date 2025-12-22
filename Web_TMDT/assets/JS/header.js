// Mega Menu Hover
const megaMenu = document.getElementById('megaMenu');
if (megaMenu) {
    megaMenu.addEventListener('mouseenter', () => megaMenu.classList.add('show'));
    megaMenu.addEventListener('mouseleave', () => megaMenu.classList.remove('show'));
}

// Tìm kiếm gợi ý như Shopee
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const searchDropdown = document.getElementById('searchDropdown');
    let timeout;

    searchInput.addEventListener('input', function () {
        const query = this.value.trim();
        clearTimeout(timeout);

        if (query.length < 2) {
            searchDropdown.classList.remove('show');
            return;
        }

        timeout = setTimeout(() => {
            fetch(`search_suggestions.php?query=${encodeURIComponent(query)}`)
                .then(r => r.json())
                .then(data => {
                    if (data.length > 0) {
                        searchDropdown.innerHTML = data.map(item => `
                            <div class="search-suggestion" onclick="selectSuggestion(${item.id})">
                                <img src="assets/images/${item.image}" alt="${item.name}" onerror="this.src='assets/images/default-product.jpg'">
                                <div class="search-suggestion-info">
                                    <h6>${item.name}</h6>
                                    <p>${new Intl.NumberFormat('vi-VN').format(item.price)}₫</p>
                                </div>
                            </div>
                        `).join('');
                        searchDropdown.classList.add('show');
                    } else {
                        searchDropdown.innerHTML = '<div class="search-no-results">Không tìm thấy sản phẩm nào</div>';
                        searchDropdown.classList.add('show');
                    }
                })
                .catch(() => {
                    searchDropdown.innerHTML = '<div class="search-no-results">Lỗi tải gợi ý</div>';
                    searchDropdown.classList.add('show');
                });
        }, 300);
    });

    // Ẩn khi click ngoài
    document.addEventListener('click', e => {
        if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
            searchDropdown.classList.remove('show');
        }
    });

    // Chọn sản phẩm
    window.selectSuggestion = function (id) {
        window.location.href = `product.php?id=${id}`;
    };

    // Enter để tìm kiếm
    searchInput.addEventListener('keypress', e => {
        if (e.key === 'Enter' && !document.querySelector('.search-suggestion:hover')) {
            searchInput.form.submit();
        }
    });
});