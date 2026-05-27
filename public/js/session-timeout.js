(function () {
    // RESPONSABLE : Melissa ABIDER
    // Perimetre : suivi cote navigateur du delai d'inactivite avant retour connexion.
    const timeout = Number(document.body.dataset.sessionTimeout || 0);
    if (!timeout) return;

    let remaining = timeout;
    console.log(`Session: expiration dans ${remaining} secondes sans navigation.`);

    const interval = window.setInterval(() => {
        remaining -= 1;
        console.log(`Session: ${remaining} seconde(s) restante(s).`);

        if (remaining <= 0) {
            window.clearInterval(interval);
            console.log('Session: delai depasse, redirection vers la connexion.');
            window.location.href = '?action=login&timeout=1';
        }
    }, 1000);
})();
