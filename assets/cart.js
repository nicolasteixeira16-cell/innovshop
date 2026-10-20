// Notification temporaire (textContent : pas d'injection HTML possible)
const notifier = (message, type = 'success') => {
    const zone = document.getElementById('notifications');
    if (!zone) {
        return;
    }
    const alerte = document.createElement('div');
    alerte.className = `alert alert-${type} shadow-sm mb-2`;
    alerte.textContent = message;
    zone.appendChild(alerte);
    setTimeout(() => alerte.remove(), 2500);
};

const majCompteur = (count) => {
    const badge = document.getElementById('cart-count');
    if (badge) {
        badge.textContent = count;
    }
};

// Sur la page panier : on recharge uniquement le tableau.
const rafraichirPanier = async () => {
    const zone = document.getElementById('cart-content');
    if (!zone) {
        return;
    }
    const reponse = await fetch(zone.dataset.url);
    const doc = new DOMParser().parseFromString(await reponse.text(), 'text/html');
    const nouvelle = doc.getElementById('cart-content');
    if (nouvelle) {
        zone.innerHTML = nouvelle.innerHTML;
    }
};

document.addEventListener('submit', async (event) => {
    const form = event.target.closest('form[data-cart-form]');
    if (!form) {
        return;
    }

    event.preventDefault();
    const bouton = form.querySelector('button');
    if (bouton) {
        bouton.disabled = true;
    }

    try {
        const reponse = await fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!reponse.ok) {
            throw new Error('Réponse invalide');
        }
        const data = await reponse.json();
        majCompteur(data.count);
        notifier(data.message);
        await rafraichirPanier();
    } catch (erreur) {
        notifier('Une erreur est survenue, veuillez réessayer.', 'danger');
    } finally {
        if (bouton) {
            bouton.disabled = false;
        }
    }
});