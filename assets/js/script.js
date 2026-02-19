let timeout;
function filterChocolates() {
    clearTimeout(timeout);

    timeout = setTimeout(() => {
        let searchTerm = document.getElementById('searchInput').value.toLowerCase();
        let noResultMessage = document.getElementById("noResultMessage");
        
        let cards = document.getElementsByClassName('choco-card');
        
        for (let i = 0; i < cards.length; i++) {
            let card = cards[i];
            let chocolateName = card.getAttribute('data-name').toLowerCase();
            
            if (chocolateName.includes(searchTerm)) {
                card.style.display = '';
                noResultMessage.style.display = "none";
            } else {
                card.style.display = 'none';
                noResultMessage.style.display = "block";
            }
        }
    }, 300);
}