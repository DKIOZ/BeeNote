// Lecture à voix haute hors du mode terrain (au rucher, c'est useTerrain qui parle,
// pour que l'écoute ne prenne pas la lecture pour une commande).
export const lectureDisponible = () => typeof window !== 'undefined' && 'speechSynthesis' in window;

let enCours = null;

export function lire(texte, finie = () => {}) {
    if (!lectureDisponible()) {
        return false;
    }

    arreterLecture();
    const message = new SpeechSynthesisUtterance(texte);
    message.lang = 'fr-FR';
    message.onend = finie;
    message.onerror = finie;
    // Gardé en mémoire, sinon certains navigateurs l'oublient avant la fin.
    enCours = message;
    window.speechSynthesis.speak(message);

    return true;
}

export function arreterLecture() {
    if (lectureDisponible()) {
        window.speechSynthesis.cancel();
    }
    enCours = null;
}
